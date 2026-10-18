<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosApiController extends Controller
{
    /**
     * Get active products for desktop POS sync
     */
    public function getProducts()
    {
        $products = Product::where('is_active', true)
            ->orderBy('nama_barang')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'data' => $products
        ]);
    }

    /**
     * Get active inmates with templates for matching & fast search
     */
    public function getInmates()
    {
        $inmates = Inmate::where('status', 'aktif')
            ->select('id', 'no_registrasi', 'nama', 'blok_kamar', 'saldo', 'limit_harian', 'va_number', 'fingerprint_template')
            ->get()
            ->map(function ($wbp) {
                return [
                    'id' => $wbp->id,
                    'no_registrasi' => $wbp->no_registrasi,
                    'nama' => $wbp->nama,
                    'blok_kamar' => $wbp->blok_kamar,
                    'saldo' => (float)$wbp->saldo,
                    'limit_harian' => (float)$wbp->limit_harian,
                    'remaining_limit' => (float)$wbp->remaining_daily_limit,
                    'has_fingerprint' => !empty($wbp->fingerprint_template),
                    'template' => $wbp->fingerprint_template
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $inmates->count(),
            'data' => $inmates
        ]);
    }

    /**
     * Lookup single inmate by ID or No Registrasi
     */
    public function lookupInmate($identifier)
    {
        $inmate = Inmate::where('id', $identifier)
            ->orWhere('no_registrasi', $identifier)
            ->orWhere('va_number', $identifier)
            ->first();

        if (!$inmate) {
            return response()->json([
                'success' => false,
                'message' => 'Warga Binaan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $inmate->id,
                'no_registrasi' => $inmate->no_registrasi,
                'nama' => $inmate->nama,
                'lapas' => $inmate->lapas_asal,
                'blok_kamar' => $inmate->blok_kamar,
                'status' => $inmate->status,
                'saldo' => (float)$inmate->saldo,
                'limit_harian' => (float)$inmate->limit_harian,
                'today_spent' => (float)$inmate->today_spent,
                'remaining_limit' => (float)$inmate->remaining_daily_limit,
                'has_fingerprint' => !empty($inmate->fingerprint_template),
                'template' => $inmate->fingerprint_template,
                'fingerprint_image' => $inmate->fingerprint_image,
                'va_number' => $inmate->va_number,
                'recent_transactions' => $inmate->transactions()->take(5)->get()
            ]
        ]);
    }

    /**
     * Process checkout transaction from Desktop POS
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'nullable|string',
            'cashier_name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $inmate = Inmate::lockForUpdate()->find($request->inmate_id);

            if ($inmate->status !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status WBP sedang ' . strtoupper($inmate->status) . '. Transaksi ditolak.'
                ], 422);
            }

            // Calculate Total Amount
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $itemReq) {
                $product = Product::lockForUpdate()->find($itemReq['product_id']);

                if (!$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang "' . $product->nama_barang . '" sedang tidak aktif.'
                    ], 422);
                }

                if ($product->stok < $itemReq['qty']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok "' . $product->nama_barang . '" tidak mencukupi (sisa: ' . $product->stok . ').'
                    ], 422);
                }

                $subtotal = $product->harga * $itemReq['qty'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product' => $product,
                    'qty' => $itemReq['qty'],
                    'price' => $product->harga,
                    'subtotal' => $subtotal
                ];
            }

            // 1. Cek Saldo WBP
            if ($inmate->saldo < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi. Sisa saldo: Rp ' . number_format($inmate->saldo, 0, ',', '.') . ', Total tagihan: Rp ' . number_format($totalAmount, 0, ',', '.')
                ], 422);
            }

            // 2. Cek Limit Belanja Harian Lapas
            $remainingLimit = $inmate->remaining_daily_limit;
            if ($totalAmount > $remainingLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi melebihi batas limit belanja harian Lapas! Sisa limit hari ini: Rp ' . number_format($remainingLimit, 0, ',', '.') . ', Tagihan: Rp ' . number_format($totalAmount, 0, ',', '.')
                ], 422);
            }

            // 3. Potong Saldo & Kurangi Stok
            $balanceBefore = $inmate->saldo;
            $balanceAfter = $balanceBefore - $totalAmount;
            $inmate->saldo = $balanceAfter;
            $inmate->save();

            // 4. Catat Transaksi
            $invoiceNo = 'TRX-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'inmate_id' => $inmate->id,
                'type' => 'purchase',
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => $request->payment_method ?? 'fingerprint',
                'status' => 'success',
                'cashier_name' => $request->cashier_name ?? 'Kasir Koperasi',
                'notes' => $request->notes ?? 'Otorisasi Sidik Jari U.are.U 4500 Sukses',
            ]);

            // 5. Simpan Rincian Belanja & Potong Stok
            foreach ($itemsData as $it) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $it['product']->id,
                    'nama_barang' => $it['product']->nama_barang,
                    'harga_satuan' => $it['price'],
                    'qty' => $it['qty'],
                    'subtotal' => $it['subtotal']
                ]);

                $it['product']->decrement('stok', $it['qty']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembayaran berhasil.',
                'data' => [
                    'invoice_no' => $invoiceNo,
                    'wbp_nama' => $inmate->nama,
                    'no_registrasi' => $inmate->no_registrasi,
                    'blok_kamar' => $inmate->blok_kamar,
                    'total_amount' => (float)$totalAmount,
                    'balance_before' => (float)$balanceBefore,
                    'balance_after' => (float)$balanceAfter,
                    'remaining_daily_limit' => (float)$inmate->remaining_daily_limit,
                    'items_count' => count($itemsData),
                    'created_at' => $transaction->created_at->format('d/m/Y H:i:s')
                ]
            ]);
        });
    }

    /**
     * Save/Update fingerprint template for an inmate
     */
    public function saveFingerprint(Request $request, $id)
    {
        $request->validate([
            'fingerprint_template' => 'required|string',
            'fingerprint_image' => 'nullable|string',
        ]);

        $inmate = Inmate::findOrFail($id);
        $inmate->fingerprint_template = $request->fingerprint_template;
        if ($request->has('fingerprint_image')) {
            $inmate->fingerprint_image = $request->fingerprint_image;
        }
        $inmate->save();

        return response()->json([
            'success' => true,
            'message' => 'Template sidik jari WBP ' . $inmate->nama . ' berhasil disimpan di server.'
        ]);
    }
}

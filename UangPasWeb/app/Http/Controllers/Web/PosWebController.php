<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\UptSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosWebController extends Controller
{
    /**
     * Halaman Kasir POS Modern Berbasis CSS
     */
    public function index()
    {
        $setting = UptSetting::current();
        $products = Product::where('is_active', true)->orderBy('nama_barang')->get();
        $inmates = Inmate::where('status', 'aktif')->orderBy('nama')->get();

        return view('pos.index', compact('setting', 'products', 'inmates'));
    }

    /**
     * Checkout Transaksi Kasir POS (Fingerprint / Manual / NFC)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'cart' => 'required|array|min:1',
            'payment_method' => 'required|string', // fingerprint, nfc_card, barcode_card, manual
        ]);

        $setting = UptSetting::current();
        $inmate = Inmate::findOrFail($request->inmate_id);

        if ($inmate->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Status WBP sedang ' . strtoupper($inmate->status) . ' (tidak diizinkan bertransaksi).',
            ], 422);
        }

        // Hitung total dan validasi stok
        $totalAmount = 0;
        $itemsToSave = [];

        foreach ($request->cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ID ' . $item['product_id'] . ' tidak ditemukan.',
                ], 422);
            }

            $qty = intval($item['qty']);
            if ($qty <= 0) continue;

            if ($product->stok < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok barang '{$product->nama_barang}' tidak mencukupi (sisa {$product->stok} pcs).",
                ], 422);
            }

            $subtotal = $product->harga * $qty;
            $totalAmount += $subtotal;

            $itemsToSave[] = [
                'product' => $product,
                'qty' => $qty,
                'harga' => $product->harga,
                'subtotal' => $subtotal,
            ];
        }

        if ($totalAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja kosong.',
            ], 422);
        }

        // Cek Saldo WBP
        if ($inmate->saldo < $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo WBP tidak mencukupi! Saldo saat ini: Rp ' . number_format($inmate->saldo, 0, ',', '.') . ', Total Belanja: Rp ' . number_format($totalAmount, 0, ',', '.'),
            ], 422);
        }

        // Cek Limit Belanja Harian
        if ($inmate->remaining_daily_limit < $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi melampaui limit belanja harian! Sisa limit hari ini: Rp ' . number_format($inmate->remaining_daily_limit, 0, ',', '.'),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $balanceBefore = $inmate->saldo;
            $balanceAfter = $balanceBefore - $totalAmount;

            $invoiceNo = 'INV-' . $setting->upt_code . '-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'upt_code' => $setting->upt_code,
                'inmate_id' => $inmate->id,
                'type' => 'purchase',
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => $request->payment_method,
                'reference_id' => $request->reference_id ?? 'POS-WEB',
                'status' => 'success',
                'cashier_name' => $request->cashier_name ?? 'Kasir Koperasi ' . $setting->upt_name,
                'notes' => 'Pembayaran Belanja Kasir POS Modern',
                'sync_status' => 'pending',
            ]);

            foreach ($itemsToSave as $it) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $it['product']->id,
                    'nama_barang' => $it['product']->nama_barang,
                    'harga_satuan' => $it['harga'],
                    'qty' => $it['qty'],
                    'subtotal' => $it['subtotal'],
                ]);

                // Kurangi stok produk
                $it['product']->decrement('stok', $it['qty']);
            }

            // Kurangi saldo WBP
            $inmate->saldo = $balanceAfter;
            $inmate->sync_status = 'pending';
            $inmate->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'invoice_no' => $invoiceNo,
                'total' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'inmate_name' => $inmate->nama,
                'inmate_reg' => $inmate->no_registrasi,
                'created_at' => $transaction->created_at->format('d/m/Y H:i:s'),
                'items' => array_map(function ($it) {
                    return [
                        'nama' => $it['product']->nama_barang,
                        'qty' => $it['qty'],
                        'subtotal' => $it['subtotal'],
                    ];
                }, $itemsToSave),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}

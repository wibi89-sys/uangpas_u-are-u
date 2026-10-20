<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\UptSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EdcTerminalController extends Controller
{
    /**
     * Tampilan Terminal EDC Mandiri (Quick Debit)
     */
    public function index()
    {
        $setting = UptSetting::current();

        $inmates = Inmate::where('status', 'aktif')
            ->select('id', 'no_registrasi', 'register_d_number', 'nama', 'blok_kamar', 'saldo', 'limit_harian', 'va_number', 'fingerprint_template', 'fingerprint_image')
            ->orderBy('nama')
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'no_registrasi' => $w->no_registrasi,
                    'register_d_number' => $w->register_d_number ?? ('REG-D-' . $w->id),
                    'nama' => $w->nama,
                    'blok_kamar' => $w->blok_kamar,
                    'saldo' => (float)$w->saldo,
                    'limit_harian' => (float)$w->limit_harian,
                    'remaining_limit' => (float)$w->remaining_daily_limit,
                    'has_fingerprint' => !empty($w->fingerprint_template),
                    'fingerprint_template' => $w->fingerprint_template,
                    'fingerprint_image' => $w->fingerprint_image,
                ];
            });

        // Transaksi EDC Terkini Hari Ini
        $recentTransactions = Transaction::with('inmate')
            ->whereIn('category', ['debit_edc', 'debit_canteen'])
            ->orWhere(function ($q) {
                $q->where('type', 'purchase')->whereNull('category');
            })
            ->whereDate('created_at', today())
            ->latest()
            ->take(10)
            ->get();

        return view('admin.edc.index', compact('setting', 'inmates', 'recentTransactions'));
    }

    /**
     * Eksekusi Pemotongan Saldo Cepat (Charge EDC)
     */
    public function charge(Request $request)
    {
        $request->validate([
            'inmate_id' => 'required|exists:inmates,id',
            'amount' => 'required|numeric|min:500',
            'merchant_name' => 'nullable|string|max:100',
            'reference_id' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|in:fingerprint,barcode_card,nfc_card,manual',
            'notes' => 'nullable|string|max:255',
        ]);

        $amount = (float)$request->amount;
        $setting = UptSetting::current();

        return DB::transaction(function () use ($request, $amount, $setting) {
            $inmate = Inmate::lockForUpdate()->find($request->inmate_id);

            // 1. Validasi Status WBP
            if ($inmate->status !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status WBP sedang ' . strtoupper($inmate->status) . ' (Register F / Isolasi). Transaksi belanja DITOLAK sistem demi disiplin Lapas.',
                ], 422);
            }

            // 2. Cek Kecukupan Saldo
            if ($inmate->saldo < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo uang titipan WBP tidak mencukupi! Sisa saldo saat ini: Rp ' . number_format($inmate->saldo, 0, ',', '.') . ', Tagihan: Rp ' . number_format($amount, 0, ',', '.'),
                    'current_balance' => (float)$inmate->saldo,
                ], 422);
            }

            // 3. Cek Limit Belanja Harian
            $remainingLimit = (float)$inmate->remaining_daily_limit;
            if ($remainingLimit < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi melampaui limit belanja harian Lapas! Sisa limit belanja hari ini: Rp ' . number_format($remainingLimit, 0, ',', '.') . ', Tagihan: Rp ' . number_format($amount, 0, ',', '.'),
                    'remaining_limit' => $remainingLimit,
                ], 422);
            }

            // 4. Eksekusi Pemotongan Saldo
            $balanceBefore = (float)$inmate->saldo;
            $balanceAfter = $balanceBefore - $amount;
            $inmate->saldo = $balanceAfter;
            $inmate->save();

            $invoiceNo = 'EDC-' . $setting->upt_code . '-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
            $merchant = $request->merchant_name ?? 'Kantin Lapas';
            $refId = $request->reference_id ?? ('EDC-' . date('ymd') . '-' . rand(100, 999));

            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'upt_code' => $setting->upt_code,
                'inmate_id' => $inmate->id,
                'type' => 'purchase',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => $request->payment_method ?? 'fingerprint',
                'category' => 'debit_edc',
                'reference_id' => $refId,
                'status' => 'success',
                'cashier_name' => auth()->user()->name ?? 'Kasir EDC Kantin',
                'notes' => $request->notes ?? ('Pembayaran Tagihan ' . $merchant . ' (Ref: ' . $refId . ')'),
            ]);

            // Catat summary item agar kompatibel dengan laporan finansial
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => null,
                'nama_barang' => $request->notes ?? ($merchant . ' (Non-Tunai)'),
                'harga_satuan' => $amount,
                'qty' => 1,
                'subtotal' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Debit Berhasil!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $invoiceNo,
                    'amount' => $amount,
                    'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'balance_before' => $balanceBefore,
                    'balance_before_formatted' => 'Rp ' . number_format($balanceBefore, 0, ',', '.'),
                    'balance_after' => $balanceAfter,
                    'balance_after_formatted' => 'Rp ' . number_format($balanceAfter, 0, ',', '.'),
                    'inmate_id' => $inmate->id,
                    'inmate_nama' => $inmate->nama,
                    'inmate_reg' => $inmate->no_registrasi,
                    'inmate_reg_d' => $inmate->register_d_number ?? ('REG-D-' . $inmate->id),
                    'inmate_kamar' => $inmate->blok_kamar,
                    'remaining_limit' => (float)$inmate->remaining_daily_limit,
                    'remaining_limit_formatted' => 'Rp ' . number_format($inmate->remaining_daily_limit, 0, ',', '.'),
                    'merchant' => $merchant,
                    'reference_id' => $refId,
                    'payment_method' => $transaction->payment_method,
                    'timestamp' => $transaction->created_at->format('d/m/Y H:i:s'),
                ]
            ]);
        });
    }

    /**
     * Struk Mini Debit Thermal (58mm / 80mm)
     */
    public function receipt($id)
    {
        $transaction = Transaction::with('inmate')->findOrFail($id);
        $setting = UptSetting::current();

        return view('admin.edc.receipt', compact('transaction', 'setting'));
    }
}

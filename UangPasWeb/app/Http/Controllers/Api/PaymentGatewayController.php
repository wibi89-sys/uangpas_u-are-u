<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    /**
     * Webhook Callback for Bank Virtual Account / QRIS
     * Compatible with Midtrans, Xendit, Duitku, & Direct Bank APIs
     */
    public function handleWebhook(Request $request)
    {
        // Support common gateway parameter names
        $vaNumber = $request->input('va_number') ?? $request->input('account_number') ?? $request->input('order_id');
        $amount = (float)($request->input('gross_amount') ?? $request->input('amount') ?? 0);
        $status = strtolower($request->input('transaction_status') ?? $request->input('status') ?? 'settlement');
        $refId = $request->input('transaction_id') ?? $request->input('reference_id') ?? ('BANK-' . strtoupper(Str::random(8)));
        $bankName = $request->input('bank') ?? 'Bank Transfer';

        if (!$vaNumber || $amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter VA number atau amount tidak valid.'
            ], 400);
        }

        // Only process successful settlements
        if (!in_array($status, ['settlement', 'capture', 'success', 'paid', 'completed'])) {
            return response()->json([
                'success' => true,
                'message' => 'Status transaksi ' . $status . ' diabaikan.'
            ]);
        }

        return DB::transaction(function () use ($vaNumber, $amount, $refId, $bankName) {
            $inmate = Inmate::lockForUpdate()->where('va_number', $vaNumber)->first();

            if (!$inmate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor Virtual Account ' . $vaNumber . ' tidak terdaftar pada WBP mana pun.'
                ], 404);
            }

            // Check idempotent transaction
            $existing = Transaction::where('reference_id', $refId)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi sudah pernah diproses sebelumnya.'
                ]);
            }

            $before = $inmate->saldo;
            $after = $before + $amount;
            $inmate->saldo = $after;
            $inmate->save();

            $invoiceNo = 'TOPUP-VA-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
            $trx = Transaction::create([
                'invoice_no' => $invoiceNo,
                'inmate_id' => $inmate->id,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'payment_method' => 'bank_va',
                'reference_id' => $refId,
                'status' => 'success',
                'cashier_name' => 'Gateway ' . strtoupper($bankName),
                'notes' => 'Top Up Virtual Account dari Keluarga via ' . strtoupper($bankName) . ' (Ref: ' . $refId . ')',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Top Up berhasil dikreditkan ke saldo WBP.',
                'data' => [
                    'invoice_no' => $invoiceNo,
                    'wbp_nama' => $inmate->nama,
                    'no_registrasi' => $inmate->no_registrasi,
                    'va_number' => $inmate->va_number,
                    'amount' => $amount,
                    'new_balance' => (float)$after
                ]
            ]);
        });
    }
}

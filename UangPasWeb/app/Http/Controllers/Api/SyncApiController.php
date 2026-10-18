<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\InmateTransfer;
use App\Models\UptSetting;
use Illuminate\Support\Facades\DB;

class SyncApiController extends Controller
{
    /**
     * Health check / ping test
     */
    public function ping()
    {
        $setting = UptSetting::current();
        return response()->json([
            'success' => true,
            'message' => 'UangPas Sync Service Online',
            'upt_code' => $setting->upt_code,
            'upt_name' => $setting->upt_name,
            'server_role' => $setting->server_role,
            'time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Menerima payload backup & sync dari UPT lokal
     */
    public function receivePush(Request $request)
    {
        $payload = $request->all();
        $uptCode = $payload['upt_code'] ?? 'UNKNOWN';

        DB::beginTransaction();
        try {
            // 1. Sync Inmates
            if (!empty($payload['inmates'])) {
                foreach ($payload['inmates'] as $inmateData) {
                    Inmate::updateOrCreate(
                        ['no_registrasi' => $inmateData['no_registrasi']],
                        [
                            'nik' => $inmateData['nik'] ?? null,
                            'nama' => $inmateData['nama'],
                            'lapas_asal' => $inmateData['lapas_asal'],
                            'upt_code' => $inmateData['upt_code'] ?? $uptCode,
                            'blok_kamar' => $inmateData['blok_kamar'],
                            'status' => $inmateData['status'],
                            'saldo' => $inmateData['saldo'],
                            'va_number' => $inmateData['va_number'] ?? null,
                            'limit_harian' => $inmateData['limit_harian'] ?? 100000,
                            'fingerprint_template' => $inmateData['fingerprint_template'] ?? null,
                            'fingerprint_image' => $inmateData['fingerprint_image'] ?? null,
                            'kontak_keluarga' => $inmateData['kontak_keluarga'] ?? null,
                            'sync_status' => 'synced',
                            'synced_at' => now(),
                        ]
                    );
                }
            }

            // 2. Sync Transactions & Items
            if (!empty($payload['transactions'])) {
                foreach ($payload['transactions'] as $trx) {
                    $inmate = Inmate::where('no_registrasi', $trx['inmate']['no_registrasi'] ?? '')->first();
                    $inmateId = $inmate ? $inmate->id : ($trx['inmate_id'] ?? 1);

                    $savedTrx = Transaction::updateOrCreate(
                        ['invoice_no' => $trx['invoice_no']],
                        [
                            'upt_code' => $trx['upt_code'] ?? $uptCode,
                            'inmate_id' => $inmateId,
                            'type' => $trx['type'],
                            'amount' => $trx['amount'],
                            'balance_before' => $trx['balance_before'],
                            'balance_after' => $trx['balance_after'],
                            'payment_method' => $trx['payment_method'],
                            'reference_id' => $trx['reference_id'] ?? null,
                            'status' => $trx['status'],
                            'cashier_name' => $trx['cashier_name'],
                            'notes' => $trx['notes'] ?? null,
                            'sync_status' => 'synced',
                            'synced_at' => now(),
                            'created_at' => $trx['created_at'],
                        ]
                    );

                    if (!empty($trx['items'])) {
                        foreach ($trx['items'] as $it) {
                            TransactionItem::firstOrCreate(
                                [
                                    'transaction_id' => $savedTrx->id,
                                    'nama_barang' => $it['nama_barang'],
                                    'qty' => $it['qty'],
                                ],
                                [
                                    'product_id' => $it['product_id'] ?? null,
                                    'harga_satuan' => $it['harga_satuan'],
                                    'subtotal' => $it['subtotal'],
                                ]
                            );
                        }
                    }
                }
            }

            // 3. Sync Transfers
            if (!empty($payload['transfers'])) {
                foreach ($payload['transfers'] as $tf) {
                    $inmate = Inmate::where('no_registrasi', $tf['inmate']['no_registrasi'] ?? '')->first();
                    if ($inmate) {
                        InmateTransfer::firstOrCreate(
                            [
                                'inmate_id' => $inmate->id,
                                'tanggal_mutasi' => $tf['tanggal_mutasi'],
                                'lapas_tujuan' => $tf['lapas_tujuan'],
                            ],
                            [
                                'lapas_asal' => $tf['lapas_asal'],
                                'upt_code_asal' => $tf['upt_code_asal'] ?? $uptCode,
                                'upt_code_tujuan' => $tf['upt_code_tujuan'] ?? null,
                                'blok_asal' => $tf['blok_asal'] ?? null,
                                'blok_tujuan' => $tf['blok_tujuan'] ?? null,
                                'saldo_dibawa' => $tf['saldo_dibawa'],
                                'petugas_mutasi' => $tf['petugas_mutasi'],
                                'alasan' => $tf['alasan'] ?? null,
                                'sync_status' => 'synced',
                                'synced_at' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data UPT ' . $uptCode . ' berhasil disimpan di Server Pusat',
                'synced_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses sync: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint untuk UPT tujuan menarik WBP yang dimutasikan kepadanya
     */
    public function pullMutatedInmates(Request $request)
    {
        $search = trim($request->input('search', ''));
        $targetUpt = trim($request->input('target_upt_code', ''));

        $query = Inmate::where(function ($q) use ($search) {
            $q->where('no_registrasi', $search)
              ->orWhere('nik', $search)
              ->orWhere('nama', 'like', "%{$search}%");
        });

        // WBP yang berstatus mutasi
        $inmate = $query->first();

        if ($inmate) {
            return response()->json([
                'success' => true,
                'found' => true,
                'inmate' => $inmate,
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'message' => 'WBP tidak ditemukan di arsip mutasi pusat.',
        ]);
    }
}

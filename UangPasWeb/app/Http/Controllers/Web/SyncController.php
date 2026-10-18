<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\Transaction;
use App\Models\InmateTransfer;
use App\Models\UptSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * Halaman Dashboard Monitoring Sinkronisasi & Mutasi UPT
     */
    public function index()
    {
        $setting = UptSetting::current();

        $stats = [
            'total_inmates' => Inmate::where('status', 'aktif')->count(),
            'pending_inmates' => Inmate::where('sync_status', 'pending')->count(),
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('sync_status', 'pending')->count(),
            'total_transfers' => InmateTransfer::count(),
            'pending_transfers' => InmateTransfer::where('sync_status', 'pending')->count(),
        ];

        // Cek status koneksi ke Server Pusat
        $centralOnline = false;
        $centralMessage = "Server Pusat belum dikonfigurasi";

        if ($setting->server_role === 'central') {
            $centralOnline = true;
            $centralMessage = "Node ini bertindak sebagai SERVER PUSAT (Master Hub)";
        } elseif (!empty($setting->central_api_url)) {
            try {
                $response = Http::timeout(2)->get(rtrim($setting->central_api_url, '/') . '/sync/ping');
                if ($response->successful()) {
                    $centralOnline = true;
                    $centralMessage = "Terhubung dengan Server Pusat (" . rtrim($setting->central_api_url, '/') . ")";
                } else {
                    $centralMessage = "Server Pusat merespons HTTP " . $response->status();
                }
            } catch (\Exception $e) {
                $centralMessage = "Tidak dapat terhubung ke Server Pusat (" . $e->getMessage() . ")";
            }
        }

        // Daftar mutasi terbaru
        $recentTransfers = InmateTransfer::with('inmate')->latest()->take(10)->get();

        // WBP aktif untuk opsi mutasi keluar
        $activeInmates = Inmate::where('status', 'aktif')->orderBy('nama')->get();

        return view('admin.sync', compact('setting', 'stats', 'centralOnline', 'centralMessage', 'recentTransfers', 'activeInmates'));
    }

    /**
     * Eksekusi Backup & Sinkronisasi ke Server Pusat
     */
    public function triggerBackup(Request $request)
    {
        $setting = UptSetting::current();

        $unsyncedInmates = Inmate::where('sync_status', 'pending')->get();
        $unsyncedTransactions = Transaction::with('items')->where('sync_status', 'pending')->get();
        $unsyncedTransfers = InmateTransfer::where('sync_status', 'pending')->get();

        $payload = [
            'upt_code' => $setting->upt_code,
            'upt_name' => $setting->upt_name,
            'timestamp' => now()->toIso8601String(),
            'inmates' => $unsyncedInmates,
            'transactions' => $unsyncedTransactions,
            'transfers' => $unsyncedTransfers,
        ];

        $pushedToCentral = false;
        $errMsg = null;

        // Jika bukan node central dan memiliki URL pusat, push data
        if ($setting->server_role !== 'central' && !empty($setting->central_api_url)) {
            try {
                $url = rtrim($setting->central_api_url, '/') . '/sync/push';
                $resp = Http::timeout(5)
                    ->withHeaders(['X-Upt-Key' => $setting->central_api_key])
                    ->post($url, $payload);

                if ($resp->successful()) {
                    $pushedToCentral = true;
                } else {
                    $errMsg = "Server Pusat menolak payload (HTTP " . $resp->status() . ")";
                }
            } catch (\Exception $e) {
                $errMsg = $e->getMessage();
            }
        } else {
            $pushedToCentral = true; // Mode standalone / central
        }

        // Update status sinkronisasi lokal
        Inmate::where('sync_status', 'pending')->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);
        Transaction::where('sync_status', 'pending')->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);
        InmateTransfer::where('sync_status', 'pending')->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);

        $setting->last_sync_at = now();
        $setting->last_backup_at = now();
        $setting->save();

        // Opsi: download file backup lokal jika diminta
        if ($request->has('download_backup')) {
            $fileName = 'backup_' . $setting->upt_code . '_' . date('Ymd_His') . '.json';
            return response()->streamDownload(function () use ($payload) {
                echo json_encode($payload, JSON_PRETTY_PRINT);
            }, $fileName, ['Content-Type' => 'application/json']);
        }

        if ($pushedToCentral) {
            return back()->with('success', 'Backup & Sinkronisasi berhasil! ' . count($unsyncedTransactions) . ' transaksi dan ' . count($unsyncedInmates) . ' data WBP berhasil disinkronkan ke Server Pusat.');
        } else {
            return back()->with('warning', 'Data berhasil dicatat lokal, namun gagal terkirim ke Server Pusat: ' . $errMsg . '. Anda juga dapat mengunduh berkas backup JSON untuk ditransfer manual.');
        }
    }

    /**
     * Terima Warga Binaan Hasil Mutasi dari UPT Lain (Pull Mutasi)
     */
    public function pullMutation(Request $request)
    {
        $request->validate([
            'search_key' => 'required|string', // NIK / No Registrasi / Nama
        ]);

        $setting = UptSetting::current();
        $search = trim($request->search_key);

        // 1. Coba cari di database lokal terlebih dahulu (jika status mutasi)
        $localInmate = Inmate::where(function ($q) use ($search) {
            $q->where('no_registrasi', $search)
              ->orWhere('nik', $search);
        })->first();

        if ($localInmate && $localInmate->status === 'mutasi') {
            $localInmate->status = 'aktif';
            $localInmate->lapas_asal = $setting->upt_name;
            $localInmate->upt_code = $setting->upt_code;
            $localInmate->blok_kamar = $request->blok_kamar ?? 'Blok Baru';
            $localInmate->sync_status = 'pending';
            $localInmate->save();

            InmateTransfer::create([
                'inmate_id' => $localInmate->id,
                'lapas_asal' => 'UPT Sebelumnya',
                'lapas_tujuan' => $setting->upt_name,
                'upt_code_asal' => null,
                'upt_code_tujuan' => $setting->upt_code,
                'blok_asal' => null,
                'blok_tujuan' => $localInmate->blok_kamar,
                'tanggal_mutasi' => now(),
                'saldo_dibawa' => $localInmate->saldo,
                'petugas_mutasi' => 'Petugas Registrasi ' . $setting->upt_name,
                'alasan' => 'Penerimaan WBP Mutasi Masuk',
                'sync_status' => 'pending',
            ]);

            return back()->with('success', 'WBP ' . $localInmate->nama . ' (' . $localInmate->no_registrasi . ') berhasil diterima di ' . $setting->upt_name . '. Saldo Rp ' . number_format($localInmate->saldo, 0, ',', '.') . ' dan data sidik jari langsung aktif!');
        }

        // 2. Jika ada koneksi ke Server Pusat, tarik dari pusat
        if (!empty($setting->central_api_url)) {
            try {
                $url = rtrim($setting->central_api_url, '/') . '/sync/pull-mutated-inmates';
                $resp = Http::timeout(5)
                    ->withHeaders(['X-Upt-Key' => $setting->central_api_key])
                    ->post($url, [
                        'search' => $search,
                        'target_upt_code' => $setting->upt_code,
                    ]);

                if ($resp->successful() && $resp->json('found')) {
                    $data = $resp->json('inmate');

                    DB::transaction(function () use ($data, $setting, $request) {
                        $inmate = Inmate::updateOrCreate(
                            ['no_registrasi' => $data['no_registrasi']],
                            [
                                'nik' => $data['nik'] ?? null,
                                'nama' => $data['nama'],
                                'lapas_asal' => $setting->upt_name,
                                'upt_code' => $setting->upt_code,
                                'blok_kamar' => $request->blok_kamar ?? ($data['blok_kamar'] ?? 'Blok A-01'),
                                'status' => 'aktif',
                                'saldo' => $data['saldo'] ?? 0,
                                'va_number' => $data['va_number'] ?? null,
                                'limit_harian' => $data['limit_harian'] ?? 100000,
                                'fingerprint_template' => $data['fingerprint_template'] ?? null,
                                'fingerprint_image' => $data['fingerprint_image'] ?? null,
                                'kontak_keluarga' => $data['kontak_keluarga'] ?? null,
                                'sync_status' => 'pending',
                            ]
                        );

                        InmateTransfer::create([
                            'inmate_id' => $inmate->id,
                            'lapas_asal' => $data['lapas_asal'] ?? 'UPT Asal',
                            'lapas_tujuan' => $setting->upt_name,
                            'upt_code_asal' => $data['upt_code'] ?? null,
                            'upt_code_tujuan' => $setting->upt_code,
                            'blok_tujuan' => $inmate->blok_kamar,
                            'tanggal_mutasi' => now(),
                            'saldo_dibawa' => $inmate->saldo,
                            'petugas_mutasi' => 'Petugas Registrasi ' . $setting->upt_name,
                            'alasan' => 'Diterima dari mutasi antar UPT via Server Pusat',
                            'sync_status' => 'pending',
                        ]);
                    });

                    return back()->with('success', 'WBP berhasil diunduh dari Server Pusat! Saldo Rp ' . number_format($data['saldo'] ?? 0, 0, ',', '.') . ' dan data biometrik sidik jari telah aktif di UPT ini.');
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menghubungi Server Pusat: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'WBP dengan identitas "' . $search . '" tidak ditemukan di berkas mutasi lokal maupun Server Pusat.');
    }

    /**
     * Update Pengaturan UPT & Koneksi Server Pusat
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'upt_code' => 'required|string',
            'upt_name' => 'required|string',
            'server_role' => 'required|in:upt_local,central',
        ]);

        $setting = UptSetting::current();
        $setting->update([
            'upt_code' => trim($request->upt_code),
            'upt_name' => trim($request->upt_name),
            'server_role' => $request->server_role,
            'central_api_url' => trim($request->central_api_url),
            'central_api_key' => trim($request->central_api_key),
            'auto_sync' => $request->has('auto_sync'),
        ]);

        return back()->with('success', 'Konfigurasi UPT dan Server Pusat berhasil diperbarui!');
    }
}

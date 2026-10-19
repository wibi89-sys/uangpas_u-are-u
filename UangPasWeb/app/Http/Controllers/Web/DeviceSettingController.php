<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UptSetting;
use App\Models\Inmate;
use App\Services\SolutionFingerprintService;

class DeviceSettingController extends Controller
{
    protected SolutionFingerprintService $solutionService;

    public function __construct(SolutionFingerprintService $solutionService)
    {
        $this->solutionService = $solutionService;
    }

    /**
     * Halaman Pengaturan Perangkat Biometrik & Integrasi Hardware
     */
    public function index()
    {
        $setting = UptSetting::current();
        $inmatesCount = Inmate::where('status', 'aktif')->count();
        $inmatesZkCount = Inmate::where('status', 'aktif')->whereNotNull('fingerprint_zk_template')->count();
        $inmatesDpCount = Inmate::where('status', 'aktif')->whereNotNull('fingerprint_template')->count();

        return view('admin.settings.devices', compact('setting', 'inmatesCount', 'inmatesZkCount', 'inmatesDpCount'));
    }

    /**
     * Simpan Perubahan Pengaturan Perangkat Solution X100C
     */
    public function update(Request $request)
    {
        $request->validate([
            'solution_device_ip' => 'required|string|max:50',
            'solution_device_port' => 'required|integer|min:1|max:65535',
            'solution_comkey' => 'required|integer|min:0',
            'solution_device_location' => 'required|string|max:100',
        ]);

        $setting = UptSetting::current();
        $setting->update([
            'solution_device_ip' => trim($request->solution_device_ip),
            'solution_device_port' => (int)$request->solution_device_port,
            'solution_comkey' => (int)$request->solution_comkey,
            'solution_device_location' => trim($request->solution_device_location),
            'solution_device_enabled' => $request->has('solution_device_enabled'),
        ]);

        return redirect()->route('admin.settings.devices')
            ->with('success', 'Pengaturan perangkat Solution X100C berhasil disimpan.');
    }

    /**
     * Test Koneksi Langsung ke Mesin Solution X100C via AJAX
     */
    public function testConnection(Request $request)
    {
        $ip = $request->input('ip') ?: UptSetting::current()->solution_device_ip;
        $port = (int)($request->input('port') ?: UptSetting::current()->solution_device_port ?: 80);

        if (empty($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat IP perangkat belum ditentukan.',
            ], 422);
        }

        $result = $this->solutionService->testConnection($ip, $port);

        return response()->json($result);
    }

    /**
     * Sinkronisasi Daftar WBP ke Mesin Solution X100C (Push PIN & Nama)
     */
    public function syncInmates(Request $request)
    {
        $setting = UptSetting::current();
        $ip = $setting->solution_device_ip;
        $port = $setting->solution_device_port ?: 80;
        $comKey = $setting->solution_comkey ?: 0;

        // Test connection first
        $conn = $this->solutionService->testConnection($ip, $port);
        if (!$conn['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi mesin: ' . $conn['message'],
            ], 400);
        }

        $inmates = Inmate::where('status', 'aktif')->get();
        $synced = 0;
        $failed = 0;

        foreach ($inmates as $inmate) {
            // Gunakan ID atau nomor registrasi numerik sebagai PIN mesin Solution
            $pin = $inmate->id;
            $name = $inmate->nama;

            $ok = $this->solutionService->setUserInfo($ip, $pin, $name, $comKey, $port);
            if ($ok) {
                $synced++;
                // Jika memiliki template ZK di DB, kirimkan juga ke mesin
                if (!empty($inmate->fingerprint_zk_template)) {
                    $this->solutionService->setUserTemplate($ip, $pin, 0, $inmate->fingerprint_zk_template, $comKey, $port);
                }
            } else {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sinkronisasi selesai! {$synced} WBP berhasil didaftarkan ke mesin Solution X100C." . ($failed > 0 ? " ({$failed} gagal)" : ""),
            'synced_count' => $synced,
            'failed_count' => $failed,
        ]);
    }

    /**
     * Tarik Log Sidik Jari Terakhir dari Mesin Solution X100C
     */
    public function pullLogs(Request $request)
    {
        $setting = UptSetting::current();
        $ip = $setting->solution_device_ip;
        $port = $setting->solution_device_port ?: 80;
        $comKey = $setting->solution_comkey ?: 0;

        $logs = $this->solutionService->getAttendanceLogs($ip, $comKey, null, $port);

        // Enrich with inmate information
        $enrichedLogs = [];
        foreach ($logs as $log) {
            $pin = $log['pin'];
            // Match with inmate id or no_registrasi
            $inmate = Inmate::where('id', $pin)
                ->orWhere('no_registrasi', $pin)
                ->first();

            $enrichedLogs[] = [
                'pin' => $pin,
                'datetime' => $log['datetime'],
                'inmate_nama' => $inmate ? $inmate->nama : 'Belum Terdaftar di UangPas',
                'inmate_kamar' => $inmate ? $inmate->blok_kamar : '-',
                'inmate_saldo' => $inmate ? 'Rp ' . number_format($inmate->saldo, 0, ',', '.') : '-',
                'is_registered' => (bool)$inmate,
            ];
        }

        return response()->json([
            'success' => true,
            'count' => count($enrichedLogs),
            'logs' => array_reverse($enrichedLogs), // Newest first
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inmate;
use App\Models\UptSetting;
use App\Services\SolutionFingerprintService;
use Carbon\Carbon;

class KioskController extends Controller
{
    protected SolutionFingerprintService $solutionService;

    public function __construct(SolutionFingerprintService $solutionService)
    {
        $this->solutionService = $solutionService;
    }

    /**
     * Halaman Kiosk Cek Saldo Mandiri WBP (Layar Penuh / Touchscreen / TV)
     */
    public function index()
    {
        $setting = UptSetting::current();
        
        // Ambil 10 WBP aktif untuk opsi simulasi uji coba di layar
        $sampleInmates = Inmate::where('status', 'aktif')
            ->select('id', 'no_registrasi', 'nama', 'blok_kamar')
            ->limit(10)
            ->get();

        return view('kiosk.index', compact('setting', 'sampleInmates'));
    }

    /**
     * Lookup WBP berdasarkan PIN Mesin / No Registrasi / Barcode Kartu
     */
    public function lookup(Request $request)
    {
        $query = trim($request->input('query'));
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan nomor registrasi atau ID WBP.',
            ], 422);
        }

        // Cari berdasarkan ID, No Registrasi, atau NIK
        $inmate = Inmate::where('id', $query)
            ->orWhere('no_registrasi', $query)
            ->orWhere('nik', $query)
            ->first();

        if (!$inmate) {
            return response()->json([
                'success' => false,
                'message' => "Data Warga Binaan dengan ID / No. Reg '{$query}' tidak ditemukan.",
            ], 404);
        }

        // Cek status WBP
        if ($inmate->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => "Warga Binaan {$inmate->nama} saat ini berstatus: " . strtoupper($inmate->status),
                'inmate' => [
                    'nama' => $inmate->nama,
                    'no_registrasi' => $inmate->no_registrasi,
                    'status' => $inmate->status,
                ],
            ], 403);
        }

        // Riwayat 5 Transaksi Terakhir
        $recentTransactions = $inmate->transactions()
            ->with('items.product')
            ->limit(5)
            ->get()
            ->map(function ($trx) {
                return [
                    'invoice_no' => $trx->invoice_no,
                    'type' => $trx->type,
                    'amount' => (float)$trx->amount,
                    'amount_formatted' => 'Rp ' . number_format($trx->amount, 0, ',', '.'),
                    'status' => $trx->status,
                    'date' => Carbon::parse($trx->created_at)->translatedFormat('d M Y H:i'),
                    'items_summary' => $trx->items->map(function ($item) {
                        return $item->product ? "{$item->product->nama_barang} ({$item->quantity}x)" : "Item ({$item->quantity}x)";
                    })->implode(', '),
                ];
            });

        return response()->json([
            'success' => true,
            'inmate' => [
                'id' => $inmate->id,
                'nama' => $inmate->nama,
                'no_registrasi' => $inmate->no_registrasi,
                'register_d_number' => $inmate->register_d_number ?: ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 5, '0', STR_PAD_LEFT)),
                'blok_kamar' => $inmate->blok_kamar ?: 'Blok Umum',
                'lapas_asal' => $inmate->lapas_asal ?: 'Lapas Pemasyarakatan',
                'saldo' => (float)$inmate->saldo,
                'saldo_formatted' => 'Rp ' . number_format($inmate->saldo, 0, ',', '.'),
                'limit_harian' => (float)$inmate->limit_harian,
                'limit_harian_formatted' => 'Rp ' . number_format($inmate->limit_harian, 0, ',', '.'),
                'today_spent' => (float)$inmate->today_spent,
                'today_spent_formatted' => 'Rp ' . number_format($inmate->today_spent, 0, ',', '.'),
                'remaining_limit' => (float)$inmate->remaining_daily_limit,
                'remaining_limit_formatted' => 'Rp ' . number_format($inmate->remaining_daily_limit, 0, ',', '.'),
                'has_fingerprint_uareu' => !empty($inmate->fingerprint_template),
                'has_fingerprint_zk' => !empty($inmate->fingerprint_zk_template),
            ],
            'recent_transactions' => $recentTransactions,
            'timestamp' => Carbon::now()->translatedFormat('l, d F Y H:i:s'),
        ]);
    }

    /**
     * Poll dari Solution X100C untuk mendeteksi tap sidik jari terbaru secara otomatis
     */
    public function pollDevice(Request $request)
    {
        $setting = UptSetting::current();
        if (!$setting->solution_device_enabled || empty($setting->solution_device_ip)) {
            return response()->json([
                'active' => false,
                'message' => 'Perangkat Solution X100C belum diaktifkan di pengaturan.',
            ]);
        }

        $ip = $setting->solution_device_ip;
        $port = $setting->solution_device_port ?: 80;
        $comKey = $setting->solution_comkey ?: 0;

        $logs = $this->solutionService->getAttendanceLogs($ip, $comKey, null, $port);
        if (empty($logs)) {
            return response()->json([
                'active' => true,
                'has_tap' => false,
            ]);
        }

        // Ambil log paling akhir
        $latestLog = end($logs);
        $tapTime = Carbon::parse($latestLog['datetime']);

        // Jika tap terjadi dalam 10 detik terakhir
        if ($tapTime->diffInSeconds(Carbon::now()) <= 15) {
            $pin = $latestLog['pin'];
            $inmate = Inmate::where('id', $pin)
                ->orWhere('no_registrasi', $pin)
                ->first();

            if ($inmate && $inmate->status === 'aktif') {
                return response()->json([
                    'active' => true,
                    'has_tap' => true,
                    'pin' => $pin,
                    'inmate_id' => $inmate->id,
                    'datetime' => $latestLog['datetime'],
                ]);
            }
        }

        return response()->json([
            'active' => true,
            'has_tap' => false,
        ]);
    }
}

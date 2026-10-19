<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UptSetting;

class GeneralSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Aplikasi Keseluruhan
     */
    public function index()
    {
        $setting = UptSetting::current();
        return view('admin.settings.general', compact('setting'));
    }

    /**
     * Simpan Perubahan Pengaturan Aplikasi
     */
    public function update(Request $request)
    {
        $request->validate([
            'upt_name' => 'required|string|max:255',
            'upt_code' => 'required|string|max:50',
            'kanwil_name' => 'nullable|string|max:255',
            'kepala_nama' => 'nullable|string|max:255',
            'kepala_nip' => 'nullable|string|max:50',
            'alamat_lengkap' => 'nullable|string',
            'kontak_telepon' => 'nullable|string|max:100',
            'kontak_email' => 'nullable|email|max:150',
            'koperasi_name' => 'nullable|string|max:255',
            'default_limit_harian' => 'required|numeric|min:1000',
            'min_topup_amount' => 'required|numeric|min:0',
            'max_balance_limit' => 'required|numeric|min:10000',
            'jam_buka_pos' => 'required|string|max:10',
            'jam_tutup_pos' => 'required|string|max:10',
            'kiosk_poll_interval' => 'required|integer|min:1|max:60',
            'server_role' => 'required|string|in:upt_local,cloud_central',
            'central_api_url' => 'nullable|string|max:255',
            'central_api_key' => 'nullable|string|max:255',
        ]);

        $setting = UptSetting::current();
        $setting->update([
            'upt_name' => $request->upt_name,
            'upt_code' => strtoupper($request->upt_code),
            'kanwil_name' => $request->kanwil_name,
            'kepala_nama' => $request->kepala_nama,
            'kepala_nip' => $request->kepala_nip,
            'alamat_lengkap' => $request->alamat_lengkap,
            'kontak_telepon' => $request->kontak_telepon,
            'kontak_email' => $request->kontak_email,
            'koperasi_name' => $request->koperasi_name,
            'default_limit_harian' => $request->default_limit_harian,
            'min_topup_amount' => $request->min_topup_amount,
            'max_balance_limit' => $request->max_balance_limit,
            'jam_buka_pos' => $request->jam_buka_pos,
            'jam_tutup_pos' => $request->jam_tutup_pos,
            'kiosk_poll_interval' => $request->kiosk_poll_interval,
            'enable_biometric_mandatory' => $request->has('enable_biometric_mandatory'),
            'server_role' => $request->server_role,
            'central_api_url' => $request->central_api_url,
            'central_api_key' => $request->central_api_key,
            'auto_sync' => $request->has('auto_sync'),
        ]);

        return back()->with('success', 'Konfigurasi aplikasi & profil UPT ' . $setting->upt_name . ' berhasil diperbarui.');
    }
}

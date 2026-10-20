@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi & Profil UPT')
@section('page_title', 'Pengaturan Aplikasi Secara Keseluruhan')
@section('page_desc', 'Konfigurasi Identitas Satuan Kerja, Kebijakan Saldo Non-Tunai, Jam Kasir & Arsitektur Server')

@section('content')
<form action="{{ route('admin.settings.general.update') }}" method="POST">
    @csrf

    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
            <span>💾</span> Simpan Seluruh Pengaturan
        </button>
    </div>

    <!-- Section 0: Mode Fokus Aplikasi (E-Wallet vs Full POS) -->
    <div class="card" style="margin-bottom: 24px; border: 2px solid #0284c7;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(2, 132, 199, 0.15); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                🎯
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">Mode Fokus Aplikasi (Feature Mode)</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Pilih fokus operasional sistem di Lapas saat ini</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <label style="border: 2px solid {{ ($setting->app_mode ?? 'wallet_only') === 'wallet_only' ? '#0284c7' : '#e2e8f0' }}; background: {{ ($setting->app_mode ?? 'wallet_only') === 'wallet_only' ? '#f0f9ff' : '#fff' }}; border-radius: 12px; padding: 18px; cursor: pointer; display: flex; gap: 14px; align-items: flex-start;">
                <input type="radio" name="app_mode" value="wallet_only" {{ ($setting->app_mode ?? 'wallet_only') === 'wallet_only' ? 'checked' : '' }} style="width: auto; margin-top: 4px;">
                <div>
                    <strong style="font-size: 14.5px; color: #0284c7; display: block;">⭐ Mode Dompet Digital & Buku Register D (Direkomendasikan)</strong>
                    <p style="font-size: 12px; color: #475569; margin-top: 4px; line-height: 1.4;">
                        Fokus pada penghapusan uang tunai (Zero Halinar), Buku Register D Digital, Loket Top-Up titipan keluarga, dan Terminal EDC potong saldo biometrik. Menu ritel toko (katalog stok/opname) disembunyikan karena kantin sudah memakai program POS sendiri.
                    </p>
                </div>
            </label>

            <label style="border: 2px solid {{ ($setting->app_mode ?? 'wallet_only') === 'full_pos' ? '#0284c7' : '#e2e8f0' }}; background: {{ ($setting->app_mode ?? 'wallet_only') === 'full_pos' ? '#f0f9ff' : '#fff' }}; border-radius: 12px; padding: 18px; cursor: pointer; display: flex; gap: 14px; align-items: flex-start;">
                <input type="radio" name="app_mode" value="full_pos" {{ ($setting->app_mode ?? 'wallet_only') === 'full_pos' ? 'checked' : '' }} style="width: auto; margin-top: 4px;">
                <div>
                    <strong style="font-size: 14.5px; color: #0f172a; display: block;">Mode Lengkap (E-Wallet + Ritel Kasir POS Terpadu)</strong>
                    <p style="font-size: 12px; color: #475569; margin-top: 4px; line-height: 1.4;">
                        Menampilkan seluruh modul termasuk manajemen stok barang, barcode produk, pembelian restock supplier, dan kasir penjualan barang koperasi terpadu.
                    </p>
                </div>
            </label>
        </div>
    </div>

    <!-- Section 1: Identitas UPT & Satker -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(2, 132, 199, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                🏛️
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">1. Identitas & Profil Satuan Kerja (UPT)</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Data instansi legalitas tanda tangan, kop laporan, dan identifikasi sistem</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Nama Satuan Kerja / Lapas / Rutan *</label>
                <input type="text" name="upt_name" value="{{ old('upt_name', $setting->upt_name) }}" required placeholder="Contoh: Lembaga Pemasyarakatan Kelas I Tangerang">
            </div>
            <div class="form-group">
                <label>Kode Satuan Kerja (UPT) *</label>
                <input type="text" name="upt_code" value="{{ old('upt_code', $setting->upt_code) }}" required placeholder="Contoh: LPS-01 / W10.PAS.PAS.1" style="font-family: monospace; font-weight: 700; text-transform: uppercase;">
            </div>
        </div>

        <div class="form-group">
            <label>Kantor Wilayah (Kanwil)</label>
            <input type="text" name="kanwil_name" value="{{ old('kanwil_name', $setting->kanwil_name) }}" placeholder="Contoh: Kantor Wilayah Kementerian Hukum dan HAM Banten">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Nama Kepala Satker (Kalapas / Karutan)</label>
                <input type="text" name="kepala_nama" value="{{ old('kepala_nama', $setting->kepala_nama) }}" placeholder="Nama Lengkap & Gelar Pimpinan">
            </div>
            <div class="form-group">
                <label>NIP Kepala Satker</label>
                <input type="text" name="kepala_nip" value="{{ old('kepala_nip', $setting->kepala_nip) }}" placeholder="1980xxxxxxxxxxxx">
            </div>
        </div>

        <div class="form-group">
            <label>Alamat Lengkap Satuan Kerja</label>
            <textarea name="alamat_lengkap" rows="2" placeholder="Alamat fisik instansi">{{ old('alamat_lengkap', $setting->alamat_lengkap) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Nomor Telepon / Hotline UPT</label>
                <input type="text" name="kontak_telepon" value="{{ old('kontak_telepon', $setting->kontak_telepon) }}" placeholder="(021) xxxxxxx / 08xxxxxxxx">
            </div>
            <div class="form-group">
                <label>Alamat Email Dinas Resmi</label>
                <input type="email" name="kontak_email" value="{{ old('kontak_email', $setting->kontak_email) }}" placeholder="uptlapas@kemenkumham.go.id">
            </div>
        </div>
    </div>

    <!-- Section 2: Kebijakan Finansial & Zero Cash Rules -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                🪙
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">2. Kebijakan Saldo & Belanja Bebas Tunai (Zero Cash)</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Pengendalian peredaran uang dan limitasi transaksi harian warga binaan</span>
            </div>
        </div>

        <div class="form-group">
            <label>Nama Badan Pengelola Koperasi *</label>
            <input type="text" name="koperasi_name" value="{{ old('koperasi_name', $setting->koperasi_name) }}" required placeholder="Contoh: Koperasi Konsumen Pengayoman Pegawai Lapas">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Default Limit Belanja Harian (Rp) *</label>
                <input type="number" name="default_limit_harian" value="{{ old('default_limit_harian', (int)$setting->default_limit_harian) }}" min="1000" step="5000" required>
                <span style="font-size: 11px; color: var(--text-muted);">Maksimal belanja WBP per hari</span>
            </div>
            <div class="form-group">
                <label>Minimal Setor / Top-Up (Rp) *</label>
                <input type="number" name="min_topup_amount" value="{{ old('min_topup_amount', (int)$setting->min_topup_amount) }}" min="0" step="1000" required>
                <span style="font-size: 11px; color: var(--text-muted);">Batas minimal sekali titip uang</span>
            </div>
            <div class="form-group">
                <label>Maksimal Saldo Mengendap (Rp) *</label>
                <input type="number" name="max_balance_limit" value="{{ old('max_balance_limit', (int)$setting->max_balance_limit) }}" min="10000" step="50000" required>
                <span style="font-size: 11px; color: var(--text-muted);">Plafon maksimal saldo per WBP</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 6px;">
            <div class="form-group">
                <label>Jam Buka Kasir Toko Koperasi</label>
                <input type="text" name="jam_buka_pos" value="{{ old('jam_buka_pos', $setting->jam_buka_pos) }}" placeholder="08:00" style="text-align: center; font-weight: 700;">
            </div>
            <div class="form-group">
                <label>Jam Tutup Kasir Toko Koperasi</label>
                <input type="text" name="jam_tutup_pos" value="{{ old('jam_tutup_pos', $setting->jam_tutup_pos) }}" placeholder="16:00" style="text-align: center; font-weight: 700;">
            </div>
            <div class="form-group">
                <label>Polling Cek Saldo Kiosk (Detik)</label>
                <input type="number" name="kiosk_poll_interval" value="{{ old('kiosk_poll_interval', $setting->kiosk_poll_interval) }}" min="1" max="60" required style="text-align: center; font-weight: 700;">
            </div>
        </div>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px;">
            <h4 style="font-size: 14px; font-weight: 800; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span>📖</span> Pengelola Buku Register D & Rekening Titipan Lapas (Escrow)
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Nama Petugas Pengelola Register D</label>
                    <input type="text" name="register_d_officer_name" value="{{ old('register_d_officer_name', $setting->register_d_officer_name) }}" placeholder="Nama Petugas Pengelola Register D">
                </div>
                <div class="form-group">
                    <label>NIP Petugas Pengelola Register D</label>
                    <input type="text" name="register_d_officer_nip" value="{{ old('register_d_officer_nip', $setting->register_d_officer_nip) }}" placeholder="1988xxxxxxxxxxxx">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Nama Ka. KPLP / Penanggung Jawab Keamanan</label>
                    <input type="text" name="kplp_nama" value="{{ old('kplp_nama', $setting->kplp_nama) }}" placeholder="Nama Lengkap Ka. KPLP">
                </div>
                <div class="form-group">
                    <label>NIP Ka. KPLP</label>
                    <input type="text" name="kplp_nip" value="{{ old('kplp_nip', $setting->kplp_nip) }}" placeholder="1982xxxxxxxxxxxx">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Nama Bank Penampung Uang Titipan (Rekening Giro UPT)</label>
                    <input type="text" name="bank_name_escrow" value="{{ old('bank_name_escrow', $setting->bank_name_escrow) }}" placeholder="Contoh: Bank BNI - Rekening Giro Titipan Uang Lapas">
                </div>
                <div class="form-group">
                    <label>Nomor Rekening Giro Titipan</label>
                    <input type="text" name="bank_account_escrow" value="{{ old('bank_account_escrow', $setting->bank_account_escrow) }}" placeholder="Contoh: 0123-4567-8901" style="font-family: monospace; font-weight: 700;">
                </div>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="font-size: 13.5px; color: #0f172a; display: block;">Wajibkan Sidik Jari Biometrik Kasir POS (Zero Pin)</strong>
                <span style="font-size: 12px; color: var(--text-muted);">WBP wajib menempelkan jari pada sensor U.are.U untuk setiap pembayaran di kasir</span>
            </div>
            <label style="position: relative; display: inline-block; width: 48px; height: 26px; margin-bottom: 0;">
                <input type="checkbox" name="enable_biometric_mandatory" value="1" {{ $setting->enable_biometric_mandatory ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                <span style="position: absolute; cursor: pointer; inset: 0; background-color: {{ $setting->enable_biometric_mandatory ? '#10b981' : '#cbd5e1' }}; border-radius: 26px; transition: .3s;" onclick="this.style.backgroundColor = this.previousElementSibling.checked ? '#cbd5e1' : '#10b981'"></span>
            </label>
        </div>
    </div>

    <!-- Section 3: Arsitektur Server & Multi-UPT Cloud Sync -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                📡
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">3. Arsitektur Jaringan, Sinkronisasi & Cloud Hosting</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Konfigurasi interkoneksi data saat dipasang di server lokal UPT maupun hosting cloud</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Peran Server Aplikasi Ini *</label>
                <select name="server_role" required>
                    <option value="upt_local" {{ $setting->server_role === 'upt_local' ? 'selected' : '' }}>🖥️ Server Lokal UPT (Offline-Ready Local Network)</option>
                    <option value="cloud_central" {{ $setting->server_role === 'cloud_central' ? 'selected' : '' }}>☁️ Server Cloud / Web Hosting Kanwil (Multi-UPT Centralized)</option>
                </select>
            </div>
            <div class="form-group">
                <label>URL API Server Pusat / Cloud Hosting</label>
                <input type="text" name="central_api_url" value="{{ old('central_api_url', $setting->central_api_url) }}" placeholder="https://uangpas.kemenkumham.go.id/api/v1">
            </div>
        </div>

        <div class="form-group">
            <label>Kunci Rahasia API UPT (Secret Token)</label>
            <input type="text" name="central_api_key" value="{{ old('central_api_key', $setting->central_api_key) }}" placeholder="Kunci token enkripsi antar-server">
        </div>

        <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
            <input type="checkbox" name="auto_sync" id="chkAutoSync" value="1" {{ $setting->auto_sync ? 'checked' : '' }} style="width: auto;">
            <label for="chkAutoSync" style="margin-bottom: 0; cursor: pointer;">
                Aktifkan sinkronisasi otomatis berkala data mutasi WBP antar-UPT
            </label>
        </div>
    </div>

    <!-- Bottom Save Button -->
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px; font-weight: 800;">
            <span>💾</span> Simpan Seluruh Perubahan Aplikasi
        </button>
    </div>
</form>
@endsection

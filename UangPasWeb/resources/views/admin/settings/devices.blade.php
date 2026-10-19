@extends('layouts.admin')

@section('title', 'Perangkat Biometrik IP')
@section('page_title', 'Pengaturan Perangkat Biometrik & Solution X100C')
@section('page_desc', 'Konfigurasi Mesin Absensi/Biometrik Standalone Berbasis IP LAN (Solution X100C / ZKTeco) untuk Kiosk Cek Saldo Mandiri WBP')

@section('content')
@if(session('success'))
<div class="card" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
    <span>✅</span> {{ session('success') }}
</div>
@endif

<!-- Ringkasan Status Hardware -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; background: #fff; border-left: 4px solid #0284c7;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Perangkat Meja Kasir</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 4px 0;">DigitalPersona U.are.U 4500</h3>
                <p style="font-size: 12px; color: #0284c7; margin: 0; font-weight: 600;">USB Desktop POS (Aktif di Kasir)</p>
            </div>
            <div style="background: #e0f2fe; padding: 10px; border-radius: 10px; font-size: 20px;">👆</div>
        </div>
        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b;">
            Tersimpan: <b>{{ $inmatesDpCount }}</b> dari {{ $inmatesCount }} WBP
        </div>
    </div>

    <div class="card" style="padding: 18px; background: #fff; border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Perangkat Dinding / Kiosk</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 4px 0;">Solution X100C (ZKTeco)</h3>
                <p style="font-size: 12px; color: {{ $setting->solution_device_enabled ? '#059669' : '#d97706' }}; margin: 0; font-weight: 600;">
                    {{ $setting->solution_device_enabled ? '● Terintegrasi IP LAN' : '○ Standby / Belum Aktif' }}
                </p>
            </div>
            <div style="background: #ecfdf5; padding: 10px; border-radius: 10px; font-size: 20px;">📡</div>
        </div>
        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b;">
            IP: <b>{{ $setting->solution_device_ip ?: '-' }}</b> ({{ $setting->solution_device_location }})
        </div>
    </div>

    <div class="card" style="padding: 18px; background: #fff; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Layar Kiosk Cek Saldo</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 4px 0;">Web Kiosk Mandiri</h3>
                <p style="font-size: 12px; color: #7c3aed; margin: 0; font-weight: 600;">Tablet Android / TV Gantung</p>
            </div>
            <div style="background: #f3e8ff; padding: 10px; border-radius: 10px; font-size: 20px;">🖥️</div>
        </div>
        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('kiosk.index') }}" target="_blank" style="color: #7c3aed; font-size: 12px; font-weight: 700; text-decoration: none;">
                Buka Layar Kiosk ↗
            </a>
            <span class="pill pill-success" style="font-size: 10px;">Siap Pakai</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; align-items: start;">
    <!-- Form Konfigurasi IP Mesin -->
    <div class="card" style="padding: 24px; background: #fff;">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
            ⚙️ Parameter Jaringan Mesin Solution X100C
        </h3>
        <p style="font-size: 12.5px; color: #64748b; margin-bottom: 20px;">
            Mesin Solution X100C berkomunikasi menggunakan protokol HTTP SOAP (<code>/iWsService</code>). Pastikan kabel LAN mesin sudah tercolok ke jaringan Lapas.
        </p>

        <form action="{{ route('admin.settings.devices.update') }}" method="POST">
            @csrf

            <!-- Enable Toggle -->
            <div style="padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <label style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0;">
                        Aktifkan Integrasi Mesin Solution X100C
                    </label>
                    <p style="font-size: 11.5px; color: #64748b; margin: 2px 0 0 0;">
                        Jika dicentang, Kiosk Cek Saldo akan mendengarkan tap sidik jari dari mesin ini secara otomatis.
                    </p>
                </div>
                <input type="checkbox" name="solution_device_enabled" value="1" {{ $setting->solution_device_enabled ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Alamat IP Mesin: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="solution_device_ip" id="inputDeviceIp" 
                           value="{{ old('solution_device_ip', $setting->solution_device_ip ?? '192.168.1.201') }}" 
                           required 
                           placeholder="Contoh: 192.168.1.201"
                           style="width: 100%; padding: 10px 12px; font-size: 13.5px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Port HTTP Web Service: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" name="solution_device_port" id="inputDevicePort" 
                           value="{{ old('solution_device_port', $setting->solution_device_port ?? 80) }}" 
                           required 
                           style="width: 100%; padding: 10px 12px; font-size: 13.5px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
                    <span style="font-size: 11px; color: #64748b;">Port default: <b>80</b> (atau 4370 jika direct TCP).</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Comm Key (Password Mesin):
                    </label>
                    <input type="number" name="solution_comkey" 
                           value="{{ old('solution_comkey', $setting->solution_comkey ?? 0) }}" 
                           style="width: 100%; padding: 10px 12px; font-size: 13.5px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
                    <span style="font-size: 11px; color: #64748b;">Default pabrik Solution adalah <b>0</b>.</span>
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Lokasi / Penempatan Mesin:
                    </label>
                    <input type="text" name="solution_device_location" 
                           value="{{ old('solution_device_location', $setting->solution_device_location ?? 'Blok Hunian WBP') }}" 
                           required
                           style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 8px;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    💾 Simpan Pengaturan
                </button>
                <button type="button" id="btnTestConn" class="btn btn-outline" style="padding: 10px 18px; border-color: #0284c7; color: #0284c7; font-weight: 700;">
                    ⚡ Uji Koneksi ke Mesin
                </button>
            </div>
        </form>

        <!-- Result Box Test Connection -->
        <div id="testConnResult" style="display: none; margin-top: 18px; padding: 12px 16px; border-radius: 8px; font-size: 12.5px;"></div>
    </div>

    <!-- Panel Operasi & Sinkronisasi Mesin -->
    <div>
        <div class="card" style="padding: 24px; background: #fff; margin-bottom: 24px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
                🔄 Operasi Sinkronisasi Mesin Solution
            </h3>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
                Perintah langsung ke memori mesin Solution X100C via jaringan LAN.
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                        1. Daftarkan Data WBP ke Mesin
                    </div>
                    <p style="font-size: 11.5px; color: #64748b; margin: 0 0 10px 0;">
                        Mengirimkan PIN (ID) dan Nama seluruh WBP aktif ({{ $inmatesCount }} orang) ke layar LCD mesin Solution.
                    </p>
                    <button type="button" id="btnSyncInmates" class="btn btn-primary" style="font-size: 12px; padding: 7px 14px;">
                        📤 Push {{ $inmatesCount }} WBP ke Mesin
                    </button>
                </div>

                <div style="padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                        2. Baca Log Sidik Jari Terakhir
                    </div>
                    <p style="font-size: 11.5px; color: #64748b; margin: 0 0 10px 0;">
                        Menarik data siapa saja WBP yang baru saja menempelkan sidik jari di mesin Solution X100C.
                    </p>
                    <button type="button" id="btnPullLogs" class="btn btn-outline" style="font-size: 12px; padding: 7px 14px; background: #fff;">
                        📥 Tarik Log Presensi Mesin
                    </button>
                </div>
            </div>

            <div id="operationResult" style="display: none; margin-top: 14px; padding: 10px 14px; border-radius: 6px; font-size: 12px;"></div>
        </div>

        <!-- Tabel Log Tap Terakhir -->
        <div class="card" style="padding: 20px; background: #fff;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin: 0;">
                    📋 Log Tap Mesin Terakhir
                </h4>
                <span id="logCountBadge" style="font-size: 11px; background: #f1f5f9; padding: 2px 8px; border-radius: 12px; color: #475569; font-weight: 700;">0 data</span>
            </div>

            <div id="logListContainer" style="max-height: 250px; overflow-y: auto; font-size: 12px;">
                <p style="color: #94a3b8; font-style: italic; text-align: center; margin: 20px 0;">
                    Klik 'Tarik Log Presensi Mesin' untuk memuat aktivitas tap sidik jari WBP.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btnTestConn');
    const boxTest = document.getElementById('testConnResult');
    const inputIp = document.getElementById('inputDeviceIp');
    const inputPort = document.getElementById('inputDevicePort');

    // Test Connection Handler
    btnTest.addEventListener('click', function() {
        const ip = inputIp.value.trim();
        const port = inputPort.value.trim();

        if (!ip) {
            alert('Silakan masukkan alamat IP mesin terlebih dahulu.');
            return;
        }

        btnTest.disabled = true;
        btnTest.innerHTML = '⏳ Menghubungi mesin...';
        boxTest.style.display = 'block';
        boxTest.style.background = '#f1f5f9';
        boxTest.style.color = '#475569';
        boxTest.style.border = '1px solid #cbd5e1';
        boxTest.innerHTML = `Menguji koneksi ke <b>${ip}:${port}</b> melalui protokol SOAP HTTP...`;

        fetch('{{ route("admin.settings.devices.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ip: ip, port: port })
        })
        .then(res => res.json())
        .then(data => {
            btnTest.disabled = false;
            btnTest.innerHTML = '⚡ Uji Koneksi ke Mesin';

            if (data.success) {
                boxTest.style.background = '#ecfdf5';
                boxTest.style.color = '#065f46';
                boxTest.style.border = '1px solid #a7f3d0';
                boxTest.innerHTML = `✅ <b>SUKSES:</b> ${data.message} ${data.latency_ms ? `(Latency: ${data.latency_ms} ms)` : ''}`;
            } else {
                boxTest.style.background = '#fef2f2';
                boxTest.style.color = '#991b1b';
                boxTest.style.border = '1px solid #fecaca';
                boxTest.innerHTML = `❌ <b>GAGAL:</b> ${data.message}`;
            }
        })
        .catch(err => {
            btnTest.disabled = false;
            btnTest.innerHTML = '⚡ Uji Koneksi ke Mesin';
            boxTest.style.background = '#fef2f2';
            boxTest.style.color = '#991b1b';
            boxTest.style.border = '1px solid #fecaca';
            boxTest.innerHTML = `❌ <b>ERROR KONEKSI:</b> Tidak dapat menghubungi server (${err.message})`;
        });
    });

    // Sync Inmates Handler
    const btnSync = document.getElementById('btnSyncInmates');
    const opResult = document.getElementById('operationResult');

    btnSync.addEventListener('click', function() {
        if (!confirm('Apakah Anda yakin ingin mendaftarkan seluruh WBP aktif ke mesin Solution X100C?')) {
            return;
        }

        btnSync.disabled = true;
        btnSync.innerHTML = '⏳ Mendaftarkan WBP...';
        opResult.style.display = 'block';
        opResult.style.background = '#f1f5f9';
        opResult.style.color = '#475569';
        opResult.innerHTML = 'Sedang mengirimkan PIN dan Nama WBP ke mesin Solution...';

        fetch('{{ route("admin.settings.devices.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnSync.disabled = false;
            btnSync.innerHTML = '📤 Push WBP ke Mesin';

            if (data.success) {
                opResult.style.background = '#ecfdf5';
                opResult.style.color = '#065f46';
                opResult.innerHTML = `✅ ${data.message}`;
            } else {
                opResult.style.background = '#fef2f2';
                opResult.style.color = '#991b1b';
                opResult.innerHTML = `❌ ${data.message}`;
            }
        })
        .catch(err => {
            btnSync.disabled = false;
            btnSync.innerHTML = '📤 Push WBP ke Mesin';
            opResult.style.background = '#fef2f2';
            opResult.style.color = '#991b1b';
            opResult.innerHTML = `❌ Gagal: ${err.message}`;
        });
    });

    // Pull Logs Handler
    const btnLogs = document.getElementById('btnPullLogs');
    const logList = document.getElementById('logListContainer');
    const logBadge = document.getElementById('logCountBadge');

    btnLogs.addEventListener('click', function() {
        btnLogs.disabled = true;
        btnLogs.innerHTML = '⏳ Menarik log...';

        fetch('{{ route("admin.settings.devices.pull_logs") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnLogs.disabled = false;
            btnLogs.innerHTML = '📥 Tarik Log Presensi Mesin';

            if (data.success && data.logs.length > 0) {
                logBadge.textContent = `${data.count} log ditemukan`;
                let html = '<div style="display: flex; flex-direction: column; gap: 6px;">';
                data.logs.slice(0, 15).forEach(l => {
                    html += `
                        <div style="padding: 8px 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <b style="color: #0f172a;">${l.inmate_nama}</b>
                                <span style="color: #64748b; font-size: 11px; margin-left: 6px;">(PIN: ${l.pin})</span>
                                <div style="color: #64748b; font-size: 11px;">Kamar: ${l.inmate_kamar}  •  ${l.datetime}</div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 800; color: #059669; font-size: 12px;">${l.inmate_saldo}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                logList.innerHTML = html;
            } else {
                logBadge.textContent = '0 log';
                logList.innerHTML = '<p style="color: #94a3b8; text-align: center; margin: 20px 0;">Belum ada aktivitas tap sidik jari di mesin Solution X100C.</p>';
            }
        })
        .catch(err => {
            btnLogs.disabled = false;
            btnLogs.innerHTML = '📥 Tarik Log Presensi Mesin';
            alert('Gagal mengambil log mesin: ' . err.message);
        });
    });
});
</script>
@endsection

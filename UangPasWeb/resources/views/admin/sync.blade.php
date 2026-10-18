@extends('layouts.admin')

@section('title', 'Sinkronisasi & Mutasi Antar-UPT')

@section('content')
<div class="content-wrapper">
    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 14px; padding: 24px 28px; color: #fff; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 24px;">🏛️</span>
                <h2 style="font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">{{ $setting->upt_name }} ({{ $setting->upt_code }})</h2>
                <span class="badge" style="background: {{ $setting->server_role === 'central' ? '#8b5cf6' : '#0284c7' }}; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    {{ $setting->server_role === 'central' ? '⭐ SERVER PUSAT' : '🏢 NODE UPT LOKAL' }}
                </span>
            </div>
            <p style="color: #94a3b8; font-size: 13px;">
                Status Koneksi Pusat: 
                <span style="color: {{ $centralOnline ? '#34d399' : '#f87171' }}; font-weight: 700;">
                    {{ $centralOnline ? '🟢 ONLINE' : '🔴 OFFLINE' }} - {{ $centralMessage }}
                </span>
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form action="{{ route('admin.sync.backup') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-primary" style="background: #10b981; border: none; padding: 12px 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <span>🔄</span> Backup & Sinkronkan Sekarang
                </button>
            </form>
            <form action="{{ route('admin.sync.backup') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="download_backup" value="1">
                <button type="submit" class="btn" style="background: #334155; color: #fff; border: 1px solid #475569; padding: 12px 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <span>💾</span> Unduh Cadangan Offline (.JSON)
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert" style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">⚠️</span>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">❌</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid-4" style="margin-bottom: 24px;">
        <div class="card stat-card">
            <div>
                <div class="stat-label">Warga Binaan UPT</div>
                <div class="stat-value">{{ number_format($stats['total_inmates']) }}</div>
                <div style="font-size: 11.5px; color: {{ $stats['pending_inmates'] > 0 ? '#f59e0b' : '#10b981' }}; margin-top: 4px; font-weight: 600;">
                    {{ $stats['pending_inmates'] }} data belum tersinkron
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--primary);">👥</div>
        </div>

        <div class="card stat-card">
            <div>
                <div class="stat-label">Total Transaksi Kasir</div>
                <div class="stat-value">{{ number_format($stats['total_transactions']) }}</div>
                <div style="font-size: 11.5px; color: {{ $stats['pending_transactions'] > 0 ? '#f59e0b' : '#10b981' }}; margin-top: 4px; font-weight: 600;">
                    {{ $stats['pending_transactions'] }} transaksi belum tersinkron
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">🛒</div>
        </div>

        <div class="card stat-card">
            <div>
                <div class="stat-label">Mutasi WBP Antar-UPT</div>
                <div class="stat-value">{{ number_format($stats['total_transfers']) }}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px; font-weight: 600;">
                    Riwayat mutasi tercatat
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">🔄</div>
        </div>

        <div class="card stat-card">
            <div>
                <div class="stat-label">Waktu Sinkron Terakhir</div>
                <div class="stat-value" style="font-size: 18px; line-height: 28px;">
                    {{ $setting->last_sync_at ? $setting->last_sync_at->diffForHumans() : 'Belum Pernah' }}
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                    {{ $setting->last_sync_at ? $setting->last_sync_at->format('d/m/Y H:i') : '-' }}
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">⏰</div>
        </div>
    </div>

    <!-- 2 Column Section: Mutation Pull & Node Settings -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
        <!-- Card 1: Penerimaan WBP Mutasi -->
        <div class="card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--card-border);">
                <span style="font-size: 20px;">📥</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-dark);">Penerimaan WBP Mutasi dari UPT Lain</h3>
            </div>
            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.5;">
                Gunakan menu ini jika ada narapidana pindahan dari Lapas/Rutan lain yang baru tiba di UPT ini. Sistem akan otomatis menarik biodata, <strong>saldo utuh</strong>, dan <strong>template sidik jari</strong> dari Server Pusat.
            </p>

            <form action="{{ route('admin.sync.pull') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nomor Registrasi Lapas / NIK WBP:</label>
                    <input type="text" name="search_key" placeholder="Contoh: WBP-2026-0001 atau 3201..." required>
                </div>

                <div class="form-group">
                    <label>Penempatan Blok & Kamar di UPT Ini:</label>
                    <input type="text" name="blok_kamar" placeholder="Contoh: Blok B-04 / Kamar Isolasi Mapenaling" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-weight: 700;">
                    <span>🔍</span> Cari & Aktifkan WBP Mutasi
                </button>
            </form>
        </div>

        <!-- Card 2: Pengaturan Node UPT & Server Pusat -->
        <div class="card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--card-border);">
                <span style="font-size: 20px;">⚙️</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-dark);">Konfigurasi Node UPT & Server Pusat</h3>
            </div>

            <form action="{{ route('admin.sync.settings') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
                    <div class="form-group">
                        <label>Kode UPT:</label>
                        <input type="text" name="upt_code" value="{{ $setting->upt_code }}" required>
                    </div>
                    <div class="form-group">
                        <label>Nama UPT / Lapas:</label>
                        <input type="text" name="upt_name" value="{{ $setting->upt_name }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Peran Server Ini:</label>
                    <select name="server_role">
                        <option value="upt_local" {{ $setting->server_role === 'upt_local' ? 'selected' : '' }}>🏢 Node Server UPT Lokal (Lapas/Rutan)</option>
                        <option value="central" {{ $setting->server_role === 'central' ? 'selected' : '' }}>⭐ Server Pusat / Kanwil (Master Hub)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>URL Endpoint Server Pusat (API Gateway):</label>
                    <input type="text" name="central_api_url" value="{{ $setting->central_api_url }}" placeholder="http://pusat.uangpas.id/api/v1">
                </div>

                <div class="form-group">
                    <label>Kunci Autentikasi Rahasia (API Secret Key):</label>
                    <input type="password" name="central_api_key" value="{{ $setting->central_api_key }}">
                </div>

                <button type="submit" class="btn" style="width: 100%; justify-content: center; padding: 12px; font-weight: 700; background: #334155; color: #fff;">
                    <span>💾</span> Simpan Konfigurasi Node
                </button>
            </form>
        </div>
    </div>

    <!-- Riwayat Mutasi Antar UPT -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">📜</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-dark);">Riwayat Mutasi WBP Antar-UPT Terkini</h3>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);">Menampilkan 10 mutasi terakhir</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Mutasi</th>
                        <th>Warga Binaan</th>
                        <th>UPT Asal</th>
                        <th>UPT Tujuan</th>
                        <th>Saldo Terbawa</th>
                        <th>Petugas Pelaksana</th>
                        <th>Status Sinkron</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransfers as $tf)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tf->tanggal_mutasi)->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $tf->inmate ? $tf->inmate->nama : 'WBP Telah Dimutasi' }}</strong>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $tf->inmate ? $tf->inmate->no_registrasi : '-' }}</div>
                            </td>
                            <td><span class="badge" style="background: #f1f5f9; color: #475569;">{{ $tf->lapas_asal }}</span></td>
                            <td><span class="badge" style="background: #e0f2fe; color: #0369a1;">{{ $tf->lapas_tujuan }}</span></td>
                            <td style="font-weight: 700; color: #10b981;">Rp {{ number_format($tf->saldo_dibawa, 0, ',', '.') }}</td>
                            <td>{{ $tf->petugas_mutasi }}</td>
                            <td>
                                <span class="pill {{ $tf->sync_status === 'synced' ? 'pill-success' : 'pill-warning' }}">
                                    {{ strtoupper($tf->sync_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                Belum ada riwayat mutasi antar-UPT yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

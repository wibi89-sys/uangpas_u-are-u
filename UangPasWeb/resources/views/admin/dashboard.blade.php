@extends('layouts.admin')

@section('title', 'Dashboard Ringkasan')
@section('page_title', 'Dashboard Ringkasan Lapas')
@section('page_desc', 'Monitoring Saldo Terhimpun, Transaksi Koperasi, dan Mutasi Narapidana')

@section('content')
@if(session('user_role') === 'kanwil_admin')
<!-- Kanwil Regional Multi-UPT Monitoring Banner -->
<div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 22px 28px; border: 1px solid #334155;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; background: rgba(56, 189, 248, 0.2); border: 1px solid #38bdf8; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                🏢
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="pill" style="background: #0284c7; color: #fff; font-size: 11px;">SERVER PUSAT KANWIL</span>
                    <span style="font-size: 12px; color: #38bdf8;">• Kantor Wilayah Kementerian Hukum dan HAM</span>
                </div>
                <h2 style="font-size: 19px; font-weight: 800; margin-top: 4px; color: #f8fafc;">Dashboard Pengawasan Koperasi Terpadu Seluruh UPT</h2>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.sync') }}" class="btn" style="background: #0284c7; color: #fff; font-size: 12.5px;">
                <span>🔄</span> Status Sinkronisasi UPT
            </a>
        </div>
    </div>

    <!-- Kanwil Metric Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
        <div style="background: rgba(255,255,255,0.06); padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">UPT Terintegrasi</div>
            <div style="font-size: 20px; font-weight: 800; color: #38bdf8; margin-top: 4px;">4 Lapas / Rutan</div>
            <div style="font-size: 11px; color: #10b981; margin-top: 2px;">● Semua Edge Server Terhubung</div>
        </div>

        <div style="background: rgba(255,255,255,0.06); padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total WBP se-Wilayah</div>
            <div style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 4px;">1.842 Orang</div>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Terdata Biometrik Terpusat</div>
        </div>

        <div style="background: rgba(255,255,255,0.06); padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total Titipan Dana WBP</div>
            <div style="font-size: 20px; font-weight: 800; color: #10b981; margin-top: 4px;">Rp 482.150.000</div>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Akumulasi Seluruh Rekening VA</div>
        </div>

        <div style="background: rgba(255,255,255,0.06); padding: 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Omset Koperasi Wilayah (Bulan Ini)</div>
            <div style="font-size: 20px; font-weight: 800; color: #f59e0b; margin-top: 4px;">Rp 128.450.000</div>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Dari 3.412 Transaksi POS</div>
        </div>
    </div>
</div>
@endif

<!-- Stat Cards -->
<div class="grid-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Total Saldo Terhimpun</div>
            <div class="stat-value" style="color: #0284c7;">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
            <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">Uang digital aktif di dompet WBP</p>
        </div>
        <div class="stat-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">💰</div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Warga Binaan Aktif</div>
            <div class="stat-value">{{ $totalWbp }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">orang</span></div>
            <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">Terdaftar di Lapas saat ini</p>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">👥</div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Belanja Koperasi Hari Ini</div>
            <div class="stat-value">Rp {{ number_format($todayVolume, 0, ',', '.') }}</div>
            <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">Dari {{ $todayTrx }} kali transaksi kasir</p>
        </div>
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🛒</div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Setor / Top Up Hari Ini</div>
            <div class="stat-value" style="color: #10b981;">Rp {{ number_format($todayTopup, 0, ',', '.') }}</div>
            <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">Titipan keluarga (Loket / VA)</p>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">📥</div>
    </div>
</div>

<!-- Main Row: Transaksi Kasir & Mutasi WBP -->
<div class="grid-2">
    <!-- Transaksi Terakhir -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 16px; font-weight: 700;">🛒 Transaksi Koperasi Terkini</h2>
            <a href="{{ route('admin.transactions') }}" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 700;">Lihat Semua &rarr;</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice / WBP</th>
                        <th>Waktu</th>
                        <th>Tipe / Metode</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $trx)
                    <tr>
                        <td>
                            <strong>{{ $trx->inmate->nama ?? 'Umum' }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $trx->invoice_no }} &bull; {{ $trx->inmate->blok_kamar ?? '-' }}</div>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $trx->created_at->format('H:i') }}
                        </td>
                        <td>
                            @if($trx->type == 'purchase')
                                <span class="pill pill-primary">Belanja</span>
                            @else
                                <span class="pill pill-success">Top Up</span>
                            @endif
                            
                            @if($trx->payment_method == 'fingerprint')
                                <span class="pill pill-success" title="Otorisasi Sidik Jari">👆 Sidik Jari</span>
                            @elseif($trx->payment_method == 'bank_va')
                                <span class="pill pill-primary">Bank VA</span>
                            @else
                                <span class="pill pill-warning">Loket Tunai</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: {{ $trx->type == 'purchase' ? '#ef4444' : '#10b981' }};">
                            {{ $trx->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">Belum ada transaksi hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mutasi WBP Terkini -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 16px; font-weight: 700;">🔄 Riwayat Perpindahan WBP (Mutasi)</h2>
            <a href="{{ route('admin.inmates') }}" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 700;">Data WBP &rarr;</a>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
            Ketika WBP dipindah blok atau antar Lapas, saldo dan sidik jari otomatis langsung terbaca di kasir tujuan tanpa perlu daftar ulang.
        </p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Warga Binaan</th>
                        <th>Perpindahan</th>
                        <th>Saldo Terbawa</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransfers as $trf)
                    <tr>
                        <td>
                            <strong>{{ $trf->inmate->nama ?? '-' }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $trf->inmate->no_registrasi ?? '-' }}</div>
                        </td>
                        <td style="font-size: 12px;">
                            <span style="color: #64748b;">{{ $trf->lapas_asal }} ({{ $trf->blok_asal }})</span>
                            <div style="color: #0284c7; font-weight: 700;">&rarr; {{ $trf->lapas_tujuan }} ({{ $trf->blok_tujuan }})</div>
                        </td>
                        <td style="font-weight: 700; color: #10b981;">
                            Rp {{ number_format($trf->saldo_dibawa, 0, ',', '.') }}
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $trf->tanggal_mutasi->format('d/m/Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">Belum ada riwayat mutasi antar blok/Lapas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
@if($lowStockProducts->count() > 0)
<div class="card" style="border-left: 4px solid var(--warning);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 14px; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span> Peringatan Stok Koperasi Menipis
            </h3>
            <p style="font-size: 12.5px; color: #64748b; margin-top: 4px;">
                Ada {{ $lowStockProducts->count() }} barang dengan stok kurang dari 20 pcs. Segera lakukan pengadaan ulang agar kasir tidak kehabisan barang.
            </p>
        </div>
        <a href="{{ route('admin.products') }}" class="btn btn-outline" style="font-size: 12px;">Kelola Stok Barang</a>
    </div>
</div>
@endif
@endsection

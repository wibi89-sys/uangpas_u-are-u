@extends('layouts.admin')

@section('title', 'Detail WBP - ' . $inmate->nama)
@section('page_title', 'Profil Warga Binaan')
@section('page_desc', 'Informasi Saldo, Rekam Biometrik, dan Riwayat Mutasi ' . $inmate->nama)

@section('content')
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <a href="{{ route('admin.inmates') }}" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700;">
        &larr; Kembali ke Daftar Warga Binaan
    </a>
    <a href="{{ route('admin.register_d.show', $inmate->id) }}" class="btn btn-outline" style="border-color: #0284c7; color: #0284c7; font-weight: 700;">
        <span>📖</span> Buka Buku Register D & Mutasi
    </a>
</div>

<div class="grid-2">
    <!-- Profil & Saldo Card -->
    <div class="card">
        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--card-border);">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; flex-shrink: 0;">
                {{ substr($inmate->nama, 0, 1) }}
            </div>
            <div>
                <h2 style="font-size: 19px; font-weight: 800; color: #0f172a;">{{ $inmate->nama }}</h2>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">
                    No. Reg: <strong>{{ $inmate->no_registrasi }}</strong> &bull; {{ $inmate->lapas_asal }}
                </div>
                <div style="margin-top: 8px;">
                    <span class="pill pill-primary">{{ $inmate->blok_kamar }}</span>
                    <span class="pill pill-success">Status: {{ strtoupper($inmate->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Saldo Banner -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 22px; border-radius: 14px; margin-bottom: 24px;">
            <div style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                Saldo Dompet Digital WBP
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #38bdf8; margin: 8px 0;">
                Rp {{ number_format($inmate->saldo, 0, ',', '.') }}
            </div>
            <div style="font-size: 12.5px; color: #cbd5e1; display: flex; justify-content: space-between; border-top: 1px solid #334155; padding-top: 10px; margin-top: 8px;">
                <span>Limit Belanja Harian: <strong>Rp {{ number_format($inmate->limit_harian, 0, ',', '.') }}</strong></span>
                <span>Sisa Hari Ini: <strong style="color: #34d399;">Rp {{ number_format($inmate->remaining_daily_limit, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- Detail Data -->
        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Nomor Buku Register D</span>
                <span style="font-family: monospace; font-weight: 800; color: #0284c7; font-size: 14px;">
                    {{ $inmate->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT)) }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Nomor Induk Kependudukan (NIK)</span>
                <strong>{{ $inmate->nik ?? 'Tidak Ada' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Virtual Account Bank (Top-Up)</span>
                <span style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 14px;">{{ $inmate->va_number }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Penjamin / Keluarga Terdekat</span>
                <strong>{{ $inmate->penjamin_nama ?? ($inmate->kontak_keluarga ?? '-') }}</strong>
            </div>
            @if($inmate->penjamin_hubungan || $inmate->penjamin_telepon)
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Hubungan & Telp Penjamin</span>
                <span>{{ $inmate->penjamin_hubungan ?? 'Keluarga' }} &bull; {{ $inmate->penjamin_telepon ?? '-' }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Tanggal Registrasi</span>
                <strong>{{ $inmate->created_at->format('d F Y') }}</strong>
            </div>
        </div>

        <!-- Quick Register D Action Buttons -->
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--card-border); display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.register_d.show', $inmate->id) }}" class="btn btn-primary" style="font-size: 12px; font-weight: 700;">
                <span>📖</span> Buka Buku Register D
            </a>
            <a href="{{ route('admin.register_d.statement', $inmate->id) }}" target="_blank" class="btn btn-outline" style="font-size: 12px; font-weight: 700;">
                <span>📄</span> Cetak Rekening Koran
            </a>
            @if($inmate->status === 'bebas' || session('print_release_id'))
            <a href="{{ route('admin.register_d.release_receipt', $inmate->id) }}" target="_blank" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444; font-size: 12px; font-weight: 700;">
                <span>📜</span> Berita Acara Bebas
            </a>
            @endif
        </div>
    </div>

    <!-- Biometrik & Mutasi Card -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Biometric Status -->
        <div class="card">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>👆</span> Status Biometrik Sidik Jari
            </h3>
            
            <div style="display: flex; gap: 18px; align-items: center;">
                <div style="width: 80px; height: 100px; background: #0f172a; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; border: 1px solid var(--card-border);">
                    @if(!empty($inmate->fingerprint_image))
                        <img src="{{ $inmate->fingerprint_image }}" style="width: 100%; height: 100%; object-fit: contain;">
                    @else
                        <span style="font-size: 28px;">👆</span>
                    @endif
                </div>
                <div>
                    @if(!empty($inmate->fingerprint_template))
                        <div style="color: #10b981; font-weight: 700; font-size: 14px; margin-bottom: 4px;">
                            ✓ Template Sidik Jari Tersimpan
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                            WBP dapat melakukan transaksi kasir di seluruh koperasi Lapas hanya dengan menempelkan jari pada sensor sidik jari.
                        </p>
                    @else
                        <div style="color: #f59e0b; font-weight: 700; font-size: 14px; margin-bottom: 4px;">
                            ⚪ Belum Ada Rekam Sidik Jari
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                            Silakan lakukan pendaftaran sidik jari melalui aplikasi Desktop POS di meja kasir/registrasi.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mutasi History -->
        <div class="card">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>🔄</span> Riwayat Perpindahan WBP (Mutasi)
            </h3>

            @forelse($inmate->transfers as $trf)
                <div style="background: #f8fafc; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; font-size: 12.5px; border-left: 3px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; font-weight: 700;">
                        <span>{{ $trf->lapas_asal }} &rarr; {{ $trf->lapas_tujuan }}</span>
                        <span style="color: var(--text-muted);">{{ $trf->tanggal_mutasi->format('d/m/Y') }}</span>
                    </div>
                    <div style="color: var(--text-muted); margin-top: 4px;">
                        Kamar: {{ $trf->blok_asal }} &rarr; {{ $trf->blok_tujuan }} &bull; Saldo Terbawa: <strong style="color: #10b981;">Rp {{ number_format($trf->saldo_dibawa, 0, ',', '.') }}</strong>
                    </div>
                    @if($trf->alasan)
                        <div style="font-size: 11.5px; color: #475569; margin-top: 4px; font-style: italic;">
                            "{{ $trf->alasan }}"
                        </div>
                    @endif
                </div>
            @empty
                <p style="font-size: 12.5px; color: var(--text-muted);">WBP ini belum pernah mengalami pemindahan/mutasi antar blok atau Lapas.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Riwayat Transaksi -->
<div class="card" style="margin-top: 24px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">
        📑 Riwayat Mutasi Keuangan & Belanja Kasir
    </h3>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice / Kode</th>
                    <th>Waktu</th>
                    <th>Tipe Transaksi</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                    <th>Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inmate->transactions as $trx)
                <tr>
                    <td><strong>{{ $trx->invoice_no }}</strong></td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($trx->type == 'purchase')
                            <span class="pill pill-primary">Belanja</span>
                        @else
                            <span class="pill pill-success">Top Up</span>
                        @endif
                        <span class="pill pill-outline">{{ strtoupper($trx->payment_method) }}</span>
                    </td>
                    <td style="font-size: 12px;">{{ $trx->notes ?? '-' }}</td>
                    <td style="font-weight: 700; color: {{ $trx->type == 'purchase' ? '#ef4444' : '#10b981' }};">
                        {{ $trx->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td style="font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        Belum ada riwayat transaksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

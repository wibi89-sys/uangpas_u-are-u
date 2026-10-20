@extends('layouts.admin')

@section('title', 'Buku Register D - Mini Bank WBP')
@section('page_title', 'Buku Register D Digital & Mini Core-Banking')
@section('page_desc', 'Pencatatan Resmi Uang Titipan Narapidana / Tahanan, Rekening Koran Individu, dan Audit Kepatuhan Ditjenpas')

@section('content')
<!-- Metric Cards -->
<div class="grid-4" style="margin-bottom: 24px;">
    <div class="card stat-card" style="border-left: 4px solid #0284c7;">
        <div>
            <div class="stat-label">Total Saldo Titipan Mengendap</div>
            <div class="stat-value" style="color: #0284c7; font-size: 22px;">
                Rp {{ number_format($totalSaldoTitipan, 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                Giro/Escrow: {{ $setting->bank_name_escrow ?? 'Bank BNI Titipan Lapas' }}
            </div>
        </div>
        <div class="stat-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
            🏦
        </div>
    </div>

    <div class="card stat-card" style="border-left: 4px solid #10b981;">
        <div>
            <div class="stat-label">Nasabah WBP Aktif</div>
            <div class="stat-value" style="color: #10b981; font-size: 22px;">
                {{ number_format($totalWbpAktif) }} <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Orang</span>
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                Memiliki Buku Register D
            </div>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            👥
        </div>
    </div>

    <div class="card stat-card" style="border-left: 4px solid #10b981;">
        <div>
            <div class="stat-label">Titipan Masuk Bulan Ini</div>
            <div class="stat-value" style="color: #10b981; font-size: 20px;">
                + Rp {{ number_format($kreditBulanIni, 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                Setoran Loket & Transfer VA
            </div>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            📥
        </div>
    </div>

    <div class="card stat-card" style="border-left: 4px solid #ef4444;">
        <div>
            <div class="stat-label">Uang Keluar Bulan Ini</div>
            <div class="stat-value" style="color: #ef4444; font-size: 20px;">
                - Rp {{ number_format($debitBulanIni, 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                Debit Belanja & Saldo Bebas
            </div>
        </div>
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
            📤
        </div>
    </div>
</div>

<!-- Filter & Action Bar -->
<div class="card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form action="{{ route('admin.register_d.index') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin: 0;">
        <div style="display: flex; align-items: center; gap: 10px; flex-grow: 1; max-width: 720px; flex-wrap: wrap;">
            <!-- Search Input -->
            <div style="position: relative; flex-grow: 1; min-width: 240px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama WBP, No. Reg Lapas, No. Register D, atau NIK..." style="padding-left: 36px;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;">🔍</span>
            </div>

            <!-- Filter Blok -->
            <select name="blok" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
                <option value="">-- Semua Blok --</option>
                @foreach($blokList as $b)
                    <option value="{{ $b }}" {{ request('blok') == $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>

            <!-- Filter Status -->
            <select name="status" style="width: auto; min-width: 130px;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="isolasi" {{ request('status') == 'isolasi' ? 'selected' : '' }}>Isolasi (Reg F)</option>
                <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                <option value="bebas" {{ request('status') == 'bebas' ? 'selected' : '' }}>Bebas</option>
            </select>

            <button type="submit" class="btn btn-primary" style="padding: 10px 16px;">
                Cari
            </button>
            @if(request()->hasAny(['q', 'blok', 'status']))
                <a href="{{ route('admin.register_d.index') }}" class="btn btn-outline" style="padding: 10px 14px;">Reset</a>
            @endif
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.register_d.ledger') }}" target="_blank" class="btn btn-outline" style="border-color: #0284c7; color: #0284c7; font-weight: 700;">
                <span>📑</span> Cetak Rekapitulasi Buku Besar Bulanan
            </a>
            <a href="{{ route('admin.topup') }}" class="btn btn-success" style="font-weight: 700;">
                <span>📥</span> Loket Setor Titipan
            </a>
        </div>
    </form>
</div>

<!-- Table Register D -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
        <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span>📖</span> Daftar Buku Register D Narapidana / Tahanan
        </h3>
        <span style="font-size: 12.5px; color: var(--text-muted);">
            Menampilkan {{ $inmates->firstItem() ?? 0 }} - {{ $inmates->lastItem() ?? 0 }} dari {{ $inmates->total() }} rekening nasabah
        </span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 140px;">No. Register D</th>
                    <th>Identitas Warga Binaan</th>
                    <th>Blok & Kamar</th>
                    <th>Nomor Virtual Account</th>
                    <th>Penjamin / Keluarga</th>
                    <th style="text-align: right;">Sisa Saldo Titipan</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center; width: 170px;">Aksi Pembukuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inmates as $wbp)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 800; color: #0284c7; font-size: 13px; background: #e0f2fe; padding: 3px 8px; border-radius: 6px;">
                            {{ $wbp->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($wbp->id, 4, '0', STR_PAD_LEFT)) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: #0f172a; font-size: 14px;">
                            {{ $wbp->nama }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            Reg: <strong>{{ $wbp->no_registrasi }}</strong> &bull; NIK: {{ $wbp->nik ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $wbp->blok_kamar }}</span>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #334155; font-size: 13px;">
                            {{ $wbp->va_number }}
                        </span>
                    </td>
                    <td style="font-size: 12.5px;">
                        @if($wbp->penjamin_nama)
                            <strong>{{ $wbp->penjamin_nama }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $wbp->penjamin_hubungan ?? 'Keluarga' }} &bull; {{ $wbp->penjamin_telepon ?? '-' }}</div>
                        @else
                            <span style="color: var(--text-muted); font-style: italic;">{{ $wbp->kontak_keluarga ?? 'Belum ada data' }}</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="font-size: 15px; font-weight: 800; color: #0284c7;">
                            Rp {{ number_format($wbp->saldo, 0, ',', '.') }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px;">
                            {{ $wbp->transactions_count }} transaksi
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @if($wbp->status === 'aktif')
                            <span class="pill pill-success">AKTIF</span>
                        @elseif($wbp->status === 'isolasi')
                            <span class="pill pill-danger">ISOLASI (REG F)</span>
                        @elseif($wbp->status === 'mutasi')
                            <span class="pill pill-warning">MUTASI</span>
                        @else
                            <span class="pill pill-outline">BEBAS</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('admin.register_d.show', $wbp->id) }}" class="btn btn-primary" style="padding: 6px 10px; font-size: 11.5px;" title="Buka Buku Mutasi / Rekening">
                                <span>📖</span> Mutasi
                            </a>
                            <a href="{{ route('admin.register_d.statement', $wbp->id) }}" target="_blank" class="btn btn-outline" style="padding: 6px 9px; font-size: 11.5px;" title="Cetak Rekening Koran PDF">
                                <span>📄</span>
                            </a>
                            <a href="{{ route('admin.register_d.passbook', $wbp->id) }}" target="_blank" class="btn btn-outline" style="padding: 6px 9px; font-size: 11.5px;" title="Cetak Buku Saku Passbook">
                                <span>💳</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 36px;">
                        Tidak ditemukan data WBP yang sesuai kriteria pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 20px;">
        {{ $inmates->links() }}
    </div>
</div>
@endsection

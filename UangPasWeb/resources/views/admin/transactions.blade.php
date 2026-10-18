@extends('layouts.admin')

@section('title', 'Laporan & Mutasi Transaksi')
@section('page_title', 'Laporan Keuangan & Mutasi Transaksi Kasir')
@section('page_desc', 'Audit Trail Belanja Koperasi, Otorisasi Biometrik, & Top Up Saldo Lapas')

@section('content')
<!-- Filter Card -->
<div class="card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.transactions') }}">
        <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 2; min-width: 220px;">
                <label>Pencarian Invoice / Nama WBP</label>
                <input type="text" name="search" placeholder="Cari nomor invoice atau nama WBP..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1.2; min-width: 150px;">
                <label>Tipe Transaksi</label>
                <select name="type">
                    <option value="">Semua Tipe</option>
                    <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Belanja Koperasi</option>
                    <option value="topup" {{ request('type') == 'topup' ? 'selected' : '' }}>Top-Up Saldo</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1.2; min-width: 150px;">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1.2; min-width: 150px;">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div style="flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 18px;">
                    🔍 Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Stat Banner -->
<div class="grid-2" style="margin-bottom: 24px;">
    <div class="card" style="background: #f8fafc; border-left: 4px solid #ef4444;">
        <div class="stat-label">Total Belanja Terfilter</div>
        <div class="stat-value" style="color: #ef4444; font-size: 22px;">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</div>
    </div>
    <div class="card" style="background: #f8fafc; border-left: 4px solid #10b981;">
        <div class="stat-label">Total Top-Up Terfilter</div>
        <div class="stat-value" style="color: #10b981; font-size: 22px;">Rp {{ number_format($totalTopup, 0, ',', '.') }}</div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice / Kode</th>
                    <th>Warga Binaan</th>
                    <th>Waktu</th>
                    <th>Tipe / Kanal</th>
                    <th>Rincian Item</th>
                    <th>Nominal</th>
                    <th>Saldo Akhir</th>
                    <th>Petugas / Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 13px;">{{ $t->invoice_no }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">Ref: {{ $t->reference_id ?? '-' }}</div>
                    </td>
                    <td>
                        <strong>{{ $t->inmate->nama ?? 'Umum' }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            {{ $t->inmate->no_registrasi ?? '-' }} &bull; {{ $t->inmate->blok_kamar ?? '-' }}
                        </div>
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ $t->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @if($t->type == 'purchase')
                            <span class="pill pill-primary">Belanja</span>
                        @else
                            <span class="pill pill-success">Top Up</span>
                        @endif

                        @if($t->payment_method == 'fingerprint')
                            <span class="pill pill-success">👆 Sidik Jari</span>
                        @elseif($t->payment_method == 'bank_va')
                            <span class="pill pill-primary">Bank VA</span>
                        @else
                            <span class="pill pill-outline">Loket Kas</span>
                        @endif
                    </td>
                    <td>
                        @if($t->items->count() > 0)
                            <div style="font-size: 11.5px; color: #334155;">
                                @foreach($t->items as $it)
                                    <div>&bull; {{ $it->nama_barang }} (x{{ $it->qty }})</div>
                                @endforeach
                            </div>
                        @else
                            <span style="font-size: 12px; color: var(--text-muted);">{{ $t->notes ?? '-' }}</span>
                        @endif
                    </td>
                    <td style="font-weight: 800; font-size: 14px; color: {{ $t->type == 'purchase' ? '#ef4444' : '#10b981' }};">
                        {{ $t->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($t->amount, 0, ',', '.') }}
                    </td>
                    <td style="font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($t->balance_after, 0, ',', '.') }}
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ $t->cashier_name }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        Tidak ada transaksi yang sesuai kriteria pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $transactions->links() }}
    </div>
</div>
@endsection

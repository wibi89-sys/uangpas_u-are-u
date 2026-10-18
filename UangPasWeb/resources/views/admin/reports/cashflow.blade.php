@extends('layouts.admin')

@section('title', 'Arus Kas & Titipan WBP')
@section('page_title', 'Arus Kas & Audit Titipan Saldo Non-Tunai WBP')
@section('page_desc', 'Monitoring Peredaran Dana Virtual WBP, Setoran Loket/VA, Belanja Koperasi, dan Pencairan Pembebasan')

@section('content')
<!-- Filter Period Bar -->
<div class="card" style="margin-bottom: 24px; padding: 20px 24px; border: 1px solid #e2e8f0; background: #ffffff;">
    <form action="{{ route('admin.reports.cashflow') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin: 0;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 13px; font-weight: 700; color: #334155;">Periode:</span>
            
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="{{ route('admin.reports.cashflow', ['period' => 'today']) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'today' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Hari Ini
                </a>
                <a href="{{ route('admin.reports.cashflow', ['period' => 'this_week']) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'this_week' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('admin.reports.cashflow', ['period' => 'this_month']) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ (!request('period') || request('period') === 'this_month') ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('admin.reports.cashflow', ['period' => 'last_month']) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'last_month' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Bulan Lalu
                </a>
                <a href="{{ route('admin.reports.cashflow', ['period' => 'this_year']) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'this_year' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Tahun Ini
                </a>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <span style="font-size: 12px; color: #64748b;">s.d.</span>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;">
                Terapkan
            </button>
        </div>
    </form>
</div>

<!-- Titipan Saldo Berjalan Banner -->
<div class="card" style="margin-bottom: 24px; padding: 22px 28px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border: 1px solid #334155;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                🏦
            </div>
            <div>
                <span style="font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px;">
                    POSISI SALDO TITIPAN BERJALAN SAAT INI (REAL-TIME AUDIT)
                </span>
                <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 2px;">
                    Rp {{ number_format($totalSaldoWbpAktif, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; border-left: 1px solid #334155; padding-left: 20px;">
            <div>
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">WBP Aktif</div>
                <div style="font-size: 18px; font-weight: 800; color: #fff;">{{ number_format($totalWbpAktif) }} Orang</div>
                <div style="font-size: 11px; color: #38bdf8;">Berhak belanja kasir</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Isolasi (Reg. F)</div>
                <div style="font-size: 18px; font-weight: 800; color: #f87171;">{{ number_format($totalWbpIsolasi) }} Orang</div>
                <div style="font-size: 11px; color: #fca5a5;">Transaksi dibekukan</div>
            </div>
        </div>
    </div>
</div>

<!-- Arus Kas Periode Ini (Cash Flow Metrics) -->
<h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 12px 0;">
    Mutasi Dana Titipan WBP: <span style="color: #0284c7;">{{ $periodLabel }}</span>
</h3>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <!-- Dana Masuk (Top Up) -->
    <div class="card" style="padding: 20px; border-left: 4px solid #10b981; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Total Dana Masuk (Top-Up)</span>
            <span style="font-size: 20px;">📥</span>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: #10b981; margin-top: 8px;">
            +Rp {{ number_format($totalTopup, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
            • Loket Tunai: <b>Rp {{ number_format($topupByCounter, 0, ',', '.') }}</b><br>
            • Virtual Account: <b>Rp {{ number_format($topupByVa, 0, ',', '.') }}</b> ({{ $countTopup }} Trx)
        </div>
    </div>

    <!-- Belanja Koperasi (Potong Saldo) -->
    <div class="card" style="padding: 20px; border-left: 4px solid #0284c7; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Belanja Koperasi (Penyerapan)</span>
            <span style="font-size: 20px;">🛒</span>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: #0284c7; margin-top: 8px;">
            -Rp {{ number_format($totalBelanja, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
            Total transaksi kasir: <b>{{ $countBelanja }} struk belanja</b>
        </div>
    </div>

    <!-- Pencairan Pembebasan (Cash Out) -->
    <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pencairan Bebas (Payout)</span>
            <span style="font-size: 20px;">🚪</span>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: #f59e0b; margin-top: 8px;">
            -Rp {{ number_format($totalRelease, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
            Penyerahan saldo sisa kepada <b>{{ $countRelease }} WBP bebas</b>
        </div>
    </div>

    <!-- Perubahan Bersih (Net Change) -->
    <div class="card" style="padding: 20px; border-left: 4px solid {{ $netFlow >= 0 ? '#10b981' : '#ef4444' }}; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Perubahan Bersih Dana</span>
            <span style="font-size: 20px;">⚖️</span>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: {{ $netFlow >= 0 ? '#10b981' : '#ef4444' }}; margin-top: 8px;">
            {{ $netFlow >= 0 ? '+' : '' }}Rp {{ number_format($netFlow, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
            Arus dana masuk dikurangi arus dana keluar
        </div>
    </div>
</div>

<!-- Recent Cashflow Audit Table -->
<div class="card" style="padding: 22px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
                📑 Buku Catatan Mutasi Dana Terkini
            </h3>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">
                25 transaksi terakhir pada periode ini untuk kebutuhan audit kepatuhan dan rekonsiliasi
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>No. Transaksi</th>
                    <th>Nama WBP / No. Reg</th>
                    <th>Jenis Mutasi</th>
                    <th>Metode Pembayaran</th>
                    <th style="text-align: right;">Nominal Mutasi</th>
                    <th style="text-align: right;">Saldo Setelahnya</th>
                    <th>Petugas / Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTrx as $trx)
                <tr>
                    <td style="color: #64748b; font-size: 12px; white-space: nowrap;">
                        {{ $trx->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="font-family: monospace; font-size: 11.5px; font-weight: 700; color: #0284c7;">
                        {{ $trx->invoice_no }}
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $trx->inmate ? $trx->inmate->nama : '-' }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $trx->inmate ? $trx->inmate->no_registrasi : '-' }}</div>
                    </td>
                    <td>
                        @if($trx->type === 'topup')
                            <span class="pill" style="background: #dcfce7; color: #166534; font-weight: 700;">
                                + TOP UP MASUK
                            </span>
                        @elseif($trx->type === 'purchase')
                            <span class="pill" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">
                                - BELANJA KASIR
                            </span>
                        @elseif($trx->type === 'release')
                            <span class="pill" style="background: #fef3c7; color: #92400e; font-weight: 700;">
                                - PENCAIRAN BEBAS
                            </span>
                        @else
                            <span class="pill" style="background: #f1f5f9; color: #475569;">
                                {{ strtoupper($trx->type) }}
                            </span>
                        @endif
                    </td>
                    <td style="font-size: 12px; text-transform: uppercase;">
                        @if($trx->payment_method === 'fingerprint')
                            👆 Sidik Jari WBP
                        @elseif($trx->payment_method === 'bank_va')
                            🏦 Virtual Account Bank
                        @elseif($trx->payment_method === 'cash_counter')
                            💵 Loket Tunai Lapas
                        @else
                            {{ $trx->payment_method }}
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 800; color: {{ $trx->type === 'topup' ? '#10b981' : '#0f172a' }};">
                        {{ $trx->type === 'topup' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #64748b;">
                        Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                    </td>
                    <td style="font-size: 12px; color: #334155;">
                        {{ $trx->cashier_name ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: #94a3b8;">
                        Tidak ada catatan transaksi pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

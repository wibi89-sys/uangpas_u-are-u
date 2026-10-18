@extends('layouts.admin')

@section('title', 'Laporan Laba Rugi & Retail')
@section('page_title', 'Laporan Laba Rugi & Analisis Retail Koperasi')
@section('page_desc', 'Monitoring Omset Penjualan, Harga Pokok Penjualan (HPP), Gross Margin, dan Fast-Moving Items')

@section('content')
<!-- Filter & Action Controls -->
<div class="card" style="margin-bottom: 24px; padding: 20px 24px; border: 1px solid #e2e8f0; background: #ffffff;">
    <form action="{{ route('admin.reports.profit_loss') }}" method="GET" style="display: flex; flex-direction: column; gap: 14px; margin: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="font-size: 13px; font-weight: 700; color: #334155;">Periode:</span>
                
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <a href="{{ route('admin.reports.profit_loss', ['period' => 'today']) }}" 
                       class="btn btn-outline" 
                       style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'today' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('admin.reports.profit_loss', ['period' => 'this_week']) }}" 
                       class="btn btn-outline" 
                       style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'this_week' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                        Minggu Ini
                    </a>
                    <a href="{{ route('admin.reports.profit_loss', ['period' => 'this_month']) }}" 
                       class="btn btn-outline" 
                       style="padding: 6px 12px; font-size: 12px; {{ (!request('period') || request('period') === 'this_month') ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('admin.reports.profit_loss', ['period' => 'last_month']) }}" 
                       class="btn btn-outline" 
                       style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'last_month' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                        Bulan Lalu
                    </a>
                    <a href="{{ route('admin.reports.profit_loss', ['period' => 'this_year']) }}" 
                       class="btn btn-outline" 
                       style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'this_year' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                        Tahun Ini
                    </a>
                </div>
            </div>

            <!-- Export Buttons -->
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.reports.export_excel', request()->all()) }}" class="btn" style="background: #10b981; color: #fff; font-size: 12.5px;">
                    <span>📊</span> Ekspor Excel (CSV)
                </a>
                <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn" style="background: #0f172a; color: #fff; font-size: 12.5px;">
                    <span>🖨️</span> Cetak Berita Acara Resmi
                </a>
            </div>
        </div>

        <!-- Custom Filters Row -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
            <input type="hidden" name="period" value="custom">
            
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Dari:</span>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Sampai:</span>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Kasir:</span>
                <select name="cashier" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiersList as $c)
                        <option value="{{ $c }}" {{ $cashierFilter == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Kategori:</span>
                <select name="category" style="padding: 6px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                    <option value="">Semua Kategori</option>
                    @foreach($categoriesList as $cat)
                        <option value="{{ $cat }}" {{ $categoryFilter == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;">
                Terapkan Filter
            </button>
            <a href="{{ route('admin.reports.profit_loss') }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Header Info Banner -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
            Rekap Finansial Koperasi: <span style="color: #0284c7;">{{ $periodLabel }}</span>
        </h3>
        <p style="font-size: 12px; color: #64748b; margin-top: 3px;">
            Rentang tanggal: {{ $startDate->translatedFormat('d F Y') }} s.d. {{ $endDate->translatedFormat('d F Y') }}
        </p>
    </div>
    <div>
        <span class="pill" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 12px; padding: 6px 12px;">
            🛒 {{ number_format($totalTrx) }} Transaksi Kasir • {{ number_format($totalUnits) }} Unit Barang Terjual
        </span>
    </div>
</div>

<!-- 4 Executive KPI Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <!-- Omset Penjualan -->
    <div class="card" style="padding: 20px; border-left: 4px solid #0284c7; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Pendapatan Omset (Gross)</span>
            <span style="font-size: 20px;">💵</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 8px;">
            Rp {{ number_format($totalOmset, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
            Rata-rata per struk: <b>Rp {{ number_format($avgBasketSize, 0, ',', '.') }}</b>
        </div>
    </div>

    <!-- Total HPP / Modal Pokok -->
    <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Harga Pokok Penjualan (HPP)</span>
            <span style="font-size: 20px;">📦</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #f59e0b; margin-top: 8px;">
            Rp {{ number_format($totalHpp, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
            Modal pengadaan barang dari supplier
        </div>
    </div>

    <!-- Laba Kotor -->
    <div class="card" style="padding: 20px; border-left: 4px solid #10b981; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Laba Kotor (Gross Profit)</span>
            <span style="font-size: 20px;">📈</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 8px;">
            Rp {{ number_format($grossProfit, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #10b981; margin-top: 4px; font-weight: 600;">
            Omset Bersih dikurangi Total HPP
        </div>
    </div>

    <!-- Margin % Keuntungan -->
    <div class="card" style="padding: 20px; border-left: 4px solid #8b5cf6; background: #fff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Margin Keuntungan</span>
            <span style="font-size: 20px;">🎯</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #8b5cf6; margin-top: 8px;">
            {{ $grossMarginPct }}%
        </div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
            Target margin retail sehat: 15% - 30%
        </div>
    </div>
</div>

<!-- Two-Column Grid: Category Breakdown vs Fast Moving Products -->
<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; margin-bottom: 28px;">
    <!-- Kategori Laba Rugi Table -->
    <div class="card" style="padding: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
                    📊 Kontribusi Laba Rugi per Kategori
                </h3>
                <p style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Analisis margin keuntungan dan perputaran omset menurut kelompok produk
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Total Omset</th>
                        <th style="text-align: right;">Total HPP</th>
                        <th style="text-align: right;">Laba Kotor</th>
                        <th style="text-align: right;">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorySummary as $cat)
                    <tr>
                        <td style="font-weight: 700; color: #0f172a;">
                            {{ $cat['kategori'] }}
                        </td>
                        <td style="text-align: center;">
                            <span class="pill" style="background: #f1f5f9; color: #475569;">
                                {{ number_format($cat['qty']) }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #0284c7;">
                            Rp {{ number_format($cat['omset'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; color: #64748b;">
                            Rp {{ number_format($cat['hpp'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #10b981;">
                            Rp {{ number_format($cat['laba'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            <span class="pill" style="background: {{ $cat['margin_pct'] >= 20 ? '#dcfce7' : '#fef3c7' }}; color: {{ $cat['margin_pct'] >= 20 ? '#166534' : '#92400e' }}; font-weight: 700;">
                                {{ $cat['margin_pct'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: #94a3b8;">
                            Belum ada data penjualan pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($categorySummary) > 0)
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                        <td>TOTAL KESELURUHAN</td>
                        <td style="text-align: center;">{{ number_format($totalUnits) }}</td>
                        <td style="text-align: right; color: #0284c7;">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #64748b;">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #10b981;">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ $grossMarginPct }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Fast-Moving & Most Profitable Items -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Top 5 Terlaris by Qty -->
        <div class="card" style="padding: 20px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 12px 0;">
                🔥 5 Produk Paling Laris (Fast-Moving)
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @php $rank = 1; @endphp
                @forelse(array_slice($topProductsByQty, 0, 5) as $top)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 24px; border-radius: 50%; background: #0284c7; color: #fff; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                            {{ $rank++ }}
                        </span>
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $top['nama_barang'] }}</div>
                            <div style="font-size: 11px; color: #64748b;">{{ $top['kategori'] }}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="pill" style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 12px;">
                            {{ number_format($top['qty']) }} pcs
                        </span>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                            Rp {{ number_format($top['omset'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="color: #94a3b8; font-size: 12px; text-align: center; padding: 12px;">
                    Belum ada data barang terjual.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Top 5 Penyumbang Laba Terbesar -->
        <div class="card" style="padding: 20px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 12px 0;">
                💎 5 Produk Penyumbang Laba Terbesar
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @php $rankProfit = 1; @endphp
                @forelse(array_slice($topProductsByProfit, 0, 5) as $topP)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: #fff; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                            {{ $rankProfit++ }}
                        </span>
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $topP['nama_barang'] }}</div>
                            <div style="font-size: 11px; color: #10b981; font-weight: 600;">Margin {{ $topP['margin_pct'] }}%</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 13px; font-weight: 800; color: #10b981;">
                            +Rp {{ number_format($topP['laba'], 0, ',', '.') }}
                        </div>
                        <div style="font-size: 11px; color: #64748b;">
                            {{ number_format($topP['qty']) }} pcs terjual
                        </div>
                    </div>
                </div>
                @empty
                <div style="color: #94a3b8; font-size: 12px; text-align: center; padding: 12px;">
                    Belum ada data barang terjual.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Daily Sales Breakdown Table -->
<div class="card" style="padding: 22px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
                📅 Tren Penjualan Harian
            </h3>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Rekapitulasi total struk kasir dan omset per hari dalam rentang pelaporan
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal Transaksi</th>
                    <th style="text-align: center;">Jumlah Transaksi (Struk)</th>
                    <th style="text-align: right;">Omset Kasir</th>
                    <th style="text-align: right;">Rata-rata per Struk</th>
                    <th style="text-align: right;">Kontribusi Omset</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyTrend as $d)
                @php
                    $dOmset = (float)$d->daily_omset;
                    $dTrx = (int)$d->trx_count;
                    $dAvg = $dTrx > 0 ? $dOmset / $dTrx : 0;
                    $dPct = $totalOmset > 0 ? round(($dOmset / $totalOmset) * 100, 1) : 0;
                @endphp
                <tr>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ \Carbon\Carbon::parse($d->trx_date)->translatedFormat('l, d F Y') }}
                    </td>
                    <td style="text-align: center;">
                        <span class="pill" style="background: #f1f5f9; color: #0f172a; font-weight: 700;">
                            {{ number_format($dTrx) }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #0284c7;">
                        Rp {{ number_format($dOmset, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #64748b;">
                        Rp {{ number_format($dAvg, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right;">
                        <span class="pill" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">
                            {{ $dPct }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 24px; color: #94a3b8;">
                        Tidak ada transaksi kasir pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Slow-Moving Products Alert -->
@if(count($slowMovingProducts) > 0)
<div class="card" style="padding: 20px; border-left: 4px solid #ef4444; background: #fff;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div>
            <h4 style="font-size: 14px; font-weight: 800; color: #991b1b; margin: 0;">
                ⚠️ Peringatan Barang Kurang Laku (Slow-Moving)
            </h4>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Produk berikut memiliki persediaan stok di rak/gudang namun <b>belum mengalami perputaran penjualan</b> pada periode ini:
            </p>
        </div>
        <a href="{{ route('admin.products') }}" class="btn btn-outline" style="font-size: 11.5px; padding: 5px 10px;">
            Kelola Stok ➔
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
        @foreach($slowMovingProducts as $slow)
        <div style="padding: 10px 12px; background: #fff1f2; border-radius: 8px; border: 1px solid #fecdd3;">
            <div style="font-size: 12.5px; font-weight: 700; color: #9f1239;">{{ $slow->nama_barang }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                Stok Tertahan: <b>{{ $slow->stok }} {{ $slow->satuan ?? 'pcs' }}</b> • HPP: Rp {{ number_format($slow->harga_beli, 0, ',', '.') }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

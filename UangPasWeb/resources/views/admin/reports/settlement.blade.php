@extends('layouts.admin')

@section('title', 'Rekonsiliasi & Settlement Kantin')
@section('page_title', 'Rekonsiliasi & Settlement Merchant Kantin')
@section('page_desc', 'Laporan Rekapitulasi Pembayaran Non-Tunai WBP di Tiap Titik Kantin & Rekonsiliasi Pencairan Dana')

@section('content')
<!-- Filter Period & Merchant Bar -->
<div class="card" style="margin-bottom: 24px; padding: 20px 24px; border: 1px solid #e2e8f0; background: #ffffff;">
    <form action="{{ route('admin.reports.settlement') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin: 0;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 13px; font-weight: 700; color: #334155;">Periode:</span>
            
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="{{ route('admin.reports.settlement', array_merge(request()->query(), ['period' => 'today'])) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'today' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Hari Ini
                </a>
                <a href="{{ route('admin.reports.settlement', array_merge(request()->query(), ['period' => 'this_week'])) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'this_week' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('admin.reports.settlement', array_merge(request()->query(), ['period' => 'this_month'])) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ (!request('period') || request('period') === 'this_month') ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('admin.reports.settlement', array_merge(request()->query(), ['period' => 'last_month'])) }}" 
                   class="btn btn-outline" 
                   style="padding: 6px 12px; font-size: 12px; {{ request('period') === 'last_month' ? 'background: #0284c7; color: #fff; border-color: #0284c7;' : '' }}">
                    Bulan Lalu
                </a>
            </div>

            <!-- Filter Merchant Selector -->
            <div style="margin-left: 8px;">
                <select name="merchant" onchange="this.form.submit()" style="padding: 6px 10px; font-size: 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600; color: #1e293b; background: #f8fafc;">
                    <option value="">-- Semua Titik Merchant / Kantin --</option>
                    @foreach($merchantList as $m)
                        <option value="{{ $m }}" {{ $merchantFilter === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
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
            <a href="{{ route('admin.reports.settlement.print', request()->query()) }}" target="_blank" class="btn btn-outline" style="padding: 6px 14px; font-size: 12px; background: #0f172a; color: #fff; border: none; display: flex; align-items: center; gap: 6px;">
                <span>🖨️</span> Cetak Berita Acara
            </a>
        </div>
    </form>
</div>

<!-- Settlement Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Card Total Omzet Settlement -->
    <div class="card" style="padding: 22px; border-left: 5px solid #0284c7; background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Total Omzet Terdebit (Settlement)</span>
            <span style="font-size: 22px;">💳</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #0284c7; margin-top: 8px;">
            Rp {{ number_format($totalNominal, 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            Dana saldo titipan WBP berhasil dipotong
        </div>
    </div>

    <!-- Card Total Transaksi -->
    <div class="card" style="padding: 22px; border-left: 5px solid #10b981; background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Jumlah Transaksi EDC</span>
            <span style="font-size: 22px;">⚡</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 8px;">
            {{ number_format($totalTrx) }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">Struk / Kali</span>
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            Otentikasi biometrik & kartu barcode
        </div>
    </div>

    <!-- Card Rekening Escrow / Kas Koperasi -->
    <div class="card" style="padding: 22px; border-left: 5px solid #8b5cf6; background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b;">Sumber Pencairan Dana</span>
            <span style="font-size: 22px;">🏦</span>
        </div>
        <div style="font-size: 15px; font-weight: 800; color: #1e293b; margin-top: 8px;">
            {{ $setting->bank_escrow_name ?? 'Bank BRI' }} - {{ $setting->bank_escrow_number ?? '1088-01-000123-50-8' }}
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            A.n. {{ $setting->bank_escrow_holder ?? 'Koperasi Pengayoman UPT' }}
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi Per Titik Merchant -->
<div class="card" style="margin-bottom: 24px; padding: 20px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Rekapitulasi Omzet Per Merchant / Lokasi Kantin</h3>
            <p style="font-size: 12.5px; color: #64748b; margin-top: 2px;">Rincian total debet yang harus dicairkan / ditransfer ke pengelola stan/kantin pada periode ini</p>
        </div>
        <span class="pill pill-primary" style="font-size: 12px; padding: 5px 12px;">Periode: {{ $periodLabel }}</span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Merchant / Titik Layanan</th>
                    <th style="text-align: center;">Frekuensi Trx</th>
                    <th style="text-align: right;">Total Omzet (Rp)</th>
                    <th style="text-align: right;">Rata-rata / Trx</th>
                    <th style="text-align: center;">Status Rekon</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchantSummary as $idx => $m)
                    <tr>
                        <td style="font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a; font-size: 13.5px;">{{ $m['name'] }}</strong>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ number_format($m['count']) }} kali
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #0284c7; font-size: 14px;">
                            Rp {{ number_format($m['total'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; color: #64748b; font-size: 12.5px;">
                            Rp {{ number_format($m['avg'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if($m['count'] > 0)
                                <span class="pill pill-success" style="font-size: 11px;">Siap Pencairan</span>
                            @else
                                <span class="pill" style="background: #f1f5f9; color: #94a3b8; font-size: 11px;">Nihil</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.reports.settlement', array_merge(request()->query(), ['merchant' => $m['name']])) }}" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">
                                Filter Data
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: #94a3b8;">
                            Belum ada aktivitas transaksi pada titik merchant ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="2" style="text-align: right; text-transform: uppercase;">Total Akumulasi Semua Titik:</td>
                    <td style="text-align: center; color: #0f172a;">{{ number_format(collect($merchantSummary)->sum('count')) }} kali</td>
                    <td style="text-align: right; color: #0284c7; font-size: 15px;">Rp {{ number_format(collect($merchantSummary)->sum('total'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Log Rincian Transaksi Debet Terakhir -->
<div class="card" style="padding: 20px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Buku Jurnal Transaksi Debet Non-Tunai</h3>
            <p style="font-size: 12.5px; color: #64748b; margin-top: 2px;">
                Daftar transaksi rinci pemotongan saldo WBP yang tercatat di sistem UangPas
                @if($merchantFilter) - <strong style="color: #0284c7;">Filter: {{ $merchantFilter }}</strong> @endif
            </p>
        </div>
        <div style="font-size: 12px; color: #64748b;">
            Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Waktu / Tanggal</th>
                    <th>No. Referensi / Invoice</th>
                    <th>Warga Binaan (Nasabah)</th>
                    <th>Lokasi Stan / Catatan</th>
                    <th style="text-align: right;">Nominal Debit</th>
                    <th>Kasir / Petugas</th>
                    <th style="text-align: center;">Struk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                    <tr>
                        <td style="white-space: nowrap; font-size: 12px; color: #475569;">
                            {{ $trx->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="font-weight: 700; font-family: monospace; font-size: 12px; color: #0f172a;">
                            {{ $trx->reference_id ?? $trx->invoice_no }}
                        </td>
                        <td>
                            @if($trx->inmate)
                                <a href="{{ route('admin.register_d.show', $trx->inmate->id) }}" style="text-decoration: none; color: #0284c7; font-weight: 700;">
                                    {{ $trx->inmate->nama }}
                                </a>
                                <div style="font-size: 11px; color: #64748b;">
                                    Reg: {{ $trx->inmate->no_registrasi }} | {{ $trx->inmate->blok ?? 'Blok' }} / {{ $trx->inmate->kamar ?? '-' }}
                                </div>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Umum / Tamu</span>
                            @endif
                        </td>
                        <td>
                            <span class="pill pill-warning" style="font-size: 11px; font-weight: 600;">
                                {{ $trx->notes ?? 'Belanja Kantin' }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #dc2626; font-size: 13.5px;">
                            -Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td style="font-size: 12px; color: #475569;">
                            {{ $trx->cashier_name ?? 'Kasir EDC' }}
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.edc.receipt', $trx->id) }}" target="_blank" class="btn btn-outline" style="padding: 3px 8px; font-size: 11px;" title="Cetak Ulang Struk">
                                🧾 Cetak
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 28px; color: #94a3b8;">
                            Tidak ada transaksi debet yang cocok dengan kriteria filter.
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

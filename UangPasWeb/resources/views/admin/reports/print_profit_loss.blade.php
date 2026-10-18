<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi Koperasi - {{ $setting->upt_name ?? 'Lapas' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px 40px;
            font-size: 12pt;
            line-height: 1.35;
        }

        .header-kop {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 24px;
            text-align: center;
            position: relative;
        }

        .logo-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 75px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        .kop-text h3 {
            font-size: 13pt;
            margin: 0;
            font-weight: normal;
            letter-spacing: 0.5px;
        }

        .kop-text h2 {
            font-size: 15pt;
            margin: 2px 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .kop-text h1 {
            font-size: 16pt;
            margin: 2px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-text p {
            font-size: 10pt;
            margin: 2px 0 0 0;
            font-style: italic;
        }

        .report-title {
            text-align: center;
            margin-bottom: 24px;
        }

        .report-title h2 {
            font-size: 14pt;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .report-title p {
            margin: 4px 0 0 0;
            font-size: 11pt;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 12px 16px;
            margin-bottom: 24px;
            background: #fbfbfb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            font-size: 11pt;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #ccc;
            padding: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 10.5pt;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            text-align: center;
            page-break-inside: avoid;
        }

        .sig-block {
            padding: 0 20px;
        }

        .sig-space {
            height: 75px;
        }

        .kalapas-sig {
            margin-top: 30px;
            text-align: center;
            page-break-inside: avoid;
        }

        .print-btn-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f172a;
            padding: 10px 18px;
            border-radius: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            gap: 12px;
            z-index: 999;
        }

        .print-btn {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            body {
                padding: 0;
            }
            .print-btn-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Bar (Hidden on Print) -->
    <div class="print-btn-bar">
        <button onclick="window.print()" class="print-btn" style="background: #10b981;">
            🖨️ Cetak Dokumen / Simpan PDF
        </button>
        <button onclick="window.close()" class="print-btn" style="background: #64748b;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat Resmi Dinas Pemasyarakatan -->
    <div class="header-kop">
        <div class="logo-box">
            ⚖️
        </div>
        <div class="kop-text">
            <h3>KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA REPUBLIK INDONESIA</h3>
            <h2>DIREKTORAT JENDERAL PEMASYARAKATAN</h2>
            <h1>KOPERASI PENGAYOMAN PEGAWAI DEPKUMHAM</h1>
            <h2>{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN') }}</h2>
            <p>Jalan Pemasyarakatan No. 1 • Telp: (021) 5523456 • Email: koperasi.lapas@kemenkumham.go.id</p>
        </div>
    </div>

    <!-- Judul Dokumen Laporan -->
    <div class="report-title">
        <h2>LAPORAN LABA RUGI & REKAPITULASI PENJUALAN RETAIL</h2>
        <p>Nomor: LAP-LR/{{ date('Ym') }}/{{ strtoupper($setting->upt_code ?? 'LPS') }}/01</p>
        <p><b>Periode Pelaporan: {{ $startDate->translatedFormat('d F Y') }} s.d. {{ $endDate->translatedFormat('d F Y') }}</b></p>
    </div>

    <!-- Ringkasan Eksekutif Finansial -->
    <div class="summary-box">
        <div style="font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #000; padding-bottom: 4px;">
            I. REKAPITULASI HASIL USAHA KOPERASI (PROFIT & LOSS STATEMENT)
        </div>
        <div class="summary-grid">
            <div>
                <div class="summary-row">
                    <span>Pendapatan Omset Penjualan (Gross):</span>
                    <b>Rp {{ number_format($totalOmset, 0, ',', '.') }}</b>
                </div>
                <div class="summary-row">
                    <span>Harga Pokok Penjualan (HPP / Modal):</span>
                    <b>Rp {{ number_format($totalHpp, 0, ',', '.') }}</b>
                </div>
                <div class="summary-row" style="border-bottom: 2px solid #000; font-size: 11.5pt;">
                    <span><b>SISA HASIL USAHA / LABA KOTOR:</b></span>
                    <b>Rp {{ number_format($grossProfit, 0, ',', '.') }}</b>
                </div>
            </div>
            <div>
                <div class="summary-row">
                    <span>Gross Profit Margin:</span>
                    <b>{{ $grossMarginPct }}%</b>
                </div>
                <div class="summary-row">
                    <span>Total Frekuensi Transaksi Kasir:</span>
                    <b>{{ number_format($totalTrx) }} Struk</b>
                </div>
                <div class="summary-row">
                    <span>Total Fisik Barang Terjual:</span>
                    <b>{{ number_format($totalUnits) }} Unit</b>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian per Kategori -->
    <div style="font-weight: bold; margin-bottom: 6px;">
        II. RINCIAN PENDAPATAN & LABA BERDASARKAN KELOMPOK BARANG
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Kelompok Kategori Barang</th>
                <th style="width: 90px;">Volume (Qty)</th>
                <th style="width: 140px;">Total Omset (Rp)</th>
                <th style="width: 140px;">Total HPP (Rp)</th>
                <th style="width: 140px;">Laba Kotor (Rp)</th>
                <th style="width: 80px;">Margin (%)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($categorySummary as $cat)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td style="font-weight: bold;">{{ $cat['kategori'] }}</td>
                <td class="text-center">{{ number_format($cat['qty']) }}</td>
                <td class="text-right">Rp {{ number_format($cat['omset'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($cat['hpp'], 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($cat['laba'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $cat['margin_pct'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px;">
                    Tidak ada transaksi pada periode pelaporan ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f2f2f2; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format($totalUnits) }}</td>
                <td class="text-right">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                <td class="text-center">{{ $grossMarginPct }}%</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 10pt; font-style: italic; margin-bottom: 30px;">
        Catatan: Seluruh transaksi tercatat secara otomatis dan real-time melalui sistem otorisasi biometrik non-tunai UangPas Koperasi Pemasyarakatan.
    </div>

    <!-- Tanda Tangan Pejabat & Pengurus -->
    <div class="signatures">
        <div class="sig-block">
            <div>Diperiksa Oleh,</div>
            <div style="font-weight: bold;">Auditor / Petugas Kasir Koperasi</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">RAHMAT HIDAYAT, S.Tr.Pas.</div>
            <div>NIP. 19930412 201801 1 002</div>
        </div>

        <div class="sig-block">
            <div>{{ $setting->upt_name ?? 'Tangerang' }}, {{ now()->translatedFormat('d F Y') }}</div>
            <div>Dibuat Oleh,</div>
            <div style="font-weight: bold;">Bendahara Koperasi Pengayoman</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">SITI AMINAH, S.E.</div>
            <div>NIP. 19890620 201212 2 001</div>
        </div>
    </div>

    <div class="kalapas-sig">
        <div>Mengetahui / Mengesahkan,</div>
        <div style="font-weight: bold;">KEPALA LEMBAGA PEMASYARAKATAN</div>
        <div class="sig-space"></div>
        <div style="font-weight: bold; text-decoration: underline;">DR. BAMBANG SUTRISNO, Bc.IP., S.H., M.H.</div>
        <div>Pembina Utama Muda (IV/c) • NIP. 19740815 199603 1 001</div>
    </div>

</body>
</html>

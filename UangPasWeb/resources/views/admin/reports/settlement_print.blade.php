<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Rekonsiliasi & Settlement Kantin - {{ $setting->upt_name ?? 'Lapas' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            padding: 20px;
        }

        .paper {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 18mm 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .no-print {
            max-width: 210mm;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #cbd5e1;
        }

        .btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
        .btn-secondary { background: #fff; color: #334155; }

        .letterhead {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
        }

        .letterhead h4 {
            font-size: 12pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .letterhead h3 {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .letterhead p {
            font-size: 9.5pt;
            margin-top: 2px;
            font-style: italic;
        }

        .double-line {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin: 10px 0 16px 0;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title .doc-number {
            font-size: 10pt;
            margin-top: 3px;
            font-family: monospace;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 10.5pt;
        }

        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .signatures {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures table {
            width: 100%;
            text-align: center;
            font-size: 10.5pt;
        }

        .signatures td {
            padding: 4px;
            vertical-align: top;
            width: 33.3%;
        }

        .sign-space {
            height: 60px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .paper {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 10mm 15mm;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('admin.reports.settlement') }}" class="btn btn-secondary">
            ← Kembali ke Panel Rekonsiliasi
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Cetak Berita Acara (A4)
        </button>
    </div>

    <div class="paper">
        <!-- Kop Surat Resmi -->
        <div class="letterhead">
            <h4>KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA R.I.</h4>
            <h4>KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM</h4>
            <h3>{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN KELAS I') }}</h3>
            <p>{{ $setting->address ?? 'Jl. Pemasyarakatan No. 1' }} | Telp/Fax: (021) 1234567 | Pos: 12345</p>
        </div>

        <div class="double-line"></div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h2>BERITA ACARA REKONSILIASI & PENCAIRAN DANA KANTIN</h2>
            <div class="doc-number">Nomor: BA.SETL/{{ date('Y/m') }}/{{ str_pad(rand(10, 99), 3, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- Narasi Pembuka & Metadata -->
        <p style="font-size: 10.5pt; line-height: 1.5; margin-bottom: 12px; text-align: justify;">
            Pada hari ini, <strong>{{ \Carbon\Carbon::now()->translatedFormat('l') }}</strong> tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong>, telah dilakukan verifikasi dan rekonsiliasi data transaksi debet virtual dompet non-tunai UangPas Warga Binaan Pemasyarakatan (WBP) untuk penyelesaian tagihan (settlement) operasional titik layanan kantin/koperasi dengan rincian sebagai berikut:
        </p>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;"><strong>Titik Merchant / Kantin</strong></td>
                <td style="width: 2%;">:</td>
                <td><strong>{{ $merchantFilter ?: 'Semua Titik Merchant (Kolektif)' }}</strong></td>
                <td style="width: 20%;"><strong>Periode Transaksi</strong></td>
                <td style="width: 2%;">:</td>
                <td>{{ $periodLabel }}</td>
            </tr>
            <tr>
                <td><strong>Total Transaksi Terverifikasi</strong></td>
                <td>:</td>
                <td><strong>{{ number_format($totalTrx) }} Transaksi</strong></td>
                <td><strong>Sumber Rekening</strong></td>
                <td>:</td>
                <td>{{ $setting->bank_escrow_name ?? 'Bank BRI' }} (Escrow WBP)</td>
            </tr>
            <tr>
                <td><strong>Total Tagihan Settlement</strong></td>
                <td>:</td>
                <td colspan="4" style="font-size: 12pt; color: #000;">
                    <strong>Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong>
                    <span style="font-size: 9.5pt; font-style: italic;">
                        ({{ ucwords(trim(\App\Helpers\Terbilang::make($totalNominal) ?? 'Nol')) }} Rupiah)
                    </span>
                </td>
            </tr>
        </table>

        <!-- Rincian Buku Transaksi -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 105px;">Waktu & Tanggal</th>
                    <th style="width: 110px;">No. Referensi / EDC</th>
                    <th>Nama WBP (Nasabah)</th>
                    <th style="width: 100px;">Kamar / Blok</th>
                    <th>Catatan Stan</th>
                    <th style="width: 90px; text-align: right;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions->take(40) as $idx => $trx)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="text-align: center; font-size: 8.5pt;">{{ $trx->created_at->format('d/m/y H:i') }}</td>
                        <td style="font-family: monospace; font-size: 8.5pt;">{{ $trx->reference_id ?? $trx->invoice_no }}</td>
                        <td>{{ $trx->inmate->nama ?? 'Umum / Tamu' }}</td>
                        <td style="text-align: center; font-size: 8.5pt;">{{ $trx->inmate ? ($trx->inmate->blok . '/' . $trx->inmate->kamar) : '-' }}</td>
                        <td style="font-size: 8.5pt;">{{ $trx->notes ?? 'Belanja' }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($trx->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 15px;">Tidak ada transaksi dalam rentang periode ini.</td>
                    </tr>
                @endforelse
                @if($transactions->count() > 40)
                    <tr>
                        <td colspan="7" style="text-align: center; font-style: italic; background: #fafafa; font-size: 8.5pt;">
                            ... Menampilkan 40 dari total {{ $transactions->count() }} transaksi. Rekapitulasi lengkap terlampir pada database sistem UangPas ...
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="6" style="text-align: right;">TOTAL DANA TERDEBET (SETTLEMENT):</td>
                    <td style="text-align: right; font-size: 10.5pt;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <p style="font-size: 9.5pt; line-height: 1.4; margin-bottom: 15px; text-align: justify;">
            Demikian Berita Acara Rekonsiliasi ini dibuat dan ditandatangani dengan sebenar-benarnya untuk dipergunakan sebagai dasar pencairan dana dari Rekening Escrow Titipan WBP ke rekening pengelola stan kantin terkait secara non-tunai.
        </p>

        <!-- Tanda Tangan 3 Pihak -->
        <div class="signatures">
            <table>
                <tr>
                    <td>
                        Pihak Penyedia /<br>
                        <strong>Pengelola Kantin / Stan</strong>
                        <div class="sign-space"></div>
                        <strong>( .................................................. )</strong><br>
                        Penanggung Jawab Kantin
                    </td>
                    <td>
                        Pihak Pemeriksa /<br>
                        <strong>Bendahara Koperasi / Reg. D</strong>
                        <div class="sign-space"></div>
                        <strong><u>{{ $setting->register_d_officer_name ?? '..................................................' }}</u></strong><br>
                        NIP. {{ $setting->register_d_officer_nip ?? '....................................' }}
                    </td>
                    <td>
                        Mengetahui / Menyetujui,<br>
                        <strong>Ka. KPLP / Pejabat Berwenang</strong>
                        <div class="sign-space"></div>
                        <strong><u>{{ $setting->kplp_officer_name ?? '..................................................' }}</u></strong><br>
                        NIP. {{ $setting->kplp_officer_nip ?? '....................................' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

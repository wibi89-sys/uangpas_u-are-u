<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Stock Opname - {{ $opname->opname_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 24px;
        }

        .paper {
            max-width: 850px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0 0 4px;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #334155;
        }

        .header p {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 13px;
            margin-bottom: 24px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .meta-item {
            display: flex;
        }

        .meta-label {
            width: 150px;
            color: #64748b;
        }

        .meta-val {
            font-weight: 700;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 24px;
        }

        th {
            background: #f1f5f9;
            color: #334155;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }

        .summary-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 32px;
        }

        .summary-table {
            width: 320px;
            font-size: 13px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            text-align: center;
            font-size: 12.5px;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .sig-box {
            padding: 10px;
        }

        .sig-space {
            height: 70px;
        }

        .btn-print {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0284c7;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Berita Acara</button>

    <div class="paper">
        <div class="header">
            <h1>KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI</h1>
            <h2>KOPERASI PENGAYOMAN PEGAWAI DEPKUMHAM (KPPDK)</h2>
            <p>LEMBAGA PEMASYARAKATAN KELAS I TANGERANG</p>
            <p style="font-size: 11px; margin-top: 4px;">Sistem Informasi Pembayaran Digital WBP - UangPas</p>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; text-decoration: underline;">BERITA ACARA AUDIT STOCK OPNAME</h3>
            <p style="margin: 4px 0 0; font-size: 13px; font-family: monospace; font-weight: 700;">Nomor: {{ $opname->opname_no }}</p>
        </div>

        <p style="font-size: 12.5px; line-height: 1.6; margin-bottom: 16px;">
            Pada hari ini, <strong>{{ $opname->tanggal_opname->translatedFormat('l, d F Y') }}</strong>, telah dilakukan perhitungan fisik inventori (Stock Opname) terhadap barang-barang dagangan pada Koperasi Lapas dengan rincian data sebagai berikut:
        </p>

        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Petugas Pelaksana</span>
                <span class="meta-val">: {{ $opname->petugas_opname }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Auditor / Saksi</span>
                <span class="meta-val">: {{ $opname->auditor_name ?: '-' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Total Item Diperiksa</span>
                <span class="meta-val">: {{ $opname->total_item }} SKU</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Catatan Pemeriksaan</span>
                <span class="meta-val">: {{ $opname->catatan ?: '-' }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">No</th>
                    <th>Barcode</th>
                    <th>Nama Barang</th>
                    <th style="text-align: center;">Stok Sistem</th>
                    <th style="text-align: center;">Stok Fisik</th>
                    <th style="text-align: center;">Selisih (+/-)</th>
                    <th style="text-align: right;">HPP (Rp)</th>
                    <th style="text-align: right;">Total Nilai (Rp)</th>
                    <th>Keterangan / Alasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($opname->items as $idx => $it)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 11px;">{{ $it->product ? $it->product->barcode : '-' }}</td>
                    <td><strong>{{ $it->product ? $it->product->nama_barang : 'Barang' }}</strong></td>
                    <td style="text-align: center;">{{ $it->stok_sistem }}</td>
                    <td style="text-align: center; font-weight: 700;">{{ $it->stok_fisik }}</td>
                    <td style="text-align: center; font-weight: 700; color: {{ $it->selisih_qty < 0 ? '#dc2626' : ($it->selisih_qty > 0 ? '#0284c7' : '#10b981') }};">
                        {{ $it->selisih_qty > 0 ? '+' . $it->selisih_qty : $it->selisih_qty }}
                    </td>
                    <td style="text-align: right;">{{ number_format($it->hpp, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 700;">
                        {{ $it->selisih_nominal < 0 ? '-Rp ' . number_format(abs($it->selisih_nominal), 0, ',', '.') : 'Rp ' . number_format($it->selisih_nominal, 0, ',', '.') }}
                    </td>
                    <td style="font-size: 11px; color: #475569;">{{ $it->keterangan ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td><strong>Total Selisih Kuantitas</strong></td>
                    <td style="text-align: right; font-weight: 700; color: {{ $opname->total_selisih_qty < 0 ? '#dc2626' : '#0284c7' }};">
                        {{ $opname->total_selisih_qty > 0 ? '+' . $opname->total_selisih_qty : $opname->total_selisih_qty }} pcs
                    </td>
                </tr>
                <tr>
                    <td><strong>Total Selisih Nilai (HPP)</strong></td>
                    <td style="text-align: right; font-weight: 700; font-size: 14px; color: {{ $opname->total_selisih_nominal < 0 ? '#dc2626' : '#0284c7' }};">
                        {{ $opname->total_selisih_nominal < 0 ? '-Rp ' . number_format(abs($op->total_selisih_nominal ?? $opname->total_selisih_nominal), 0, ',', '.') : 'Rp ' . number_format($opname->total_selisih_nominal, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        <p style="font-size: 12px; line-height: 1.5;">
            Demikian Berita Acara ini dibuat dengan sebenarnya sesuai dengan kondisi fisik riil di lapangan untuk dipergunakan sebagai dasar penyesuaian catatan pembukuan dan laporan pertanggungjawaban pengurus koperasi.
        </p>

        <div class="signatures">
            <div class="sig-box">
                <div>Petugas Pelaksana,</div>
                <div class="sig-space"></div>
                <div style="font-weight: 800; text-decoration: underline;">{{ $opname->petugas_opname }}</div>
                <div style="font-size: 11px; color: #64748b;">Pengurus / Kasir Koperasi</div>
            </div>

            <div class="sig-box">
                <div>Auditor / Saksi,</div>
                <div class="sig-space"></div>
                <div style="font-weight: 800; text-decoration: underline;">{{ $opname->auditor_name ?: '( ................................... )' }}</div>
                <div style="font-size: 11px; color: #64748b;">Pengawas Internal Koperasi</div>
            </div>

            <div class="sig-box">
                <div>Mengetahui,</div>
                <div class="sig-space"></div>
                <div style="font-weight: 800; text-decoration: underline;">Dr. Bambang Irawan, S.H.</div>
                <div style="font-size: 11px; color: #64748b;">Kepala Koperasi / Kalapas</div>
            </div>
        </div>
    </div>
</body>
</html>

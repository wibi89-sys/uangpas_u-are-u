<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Buku Besar Register D - {{ $setting->upt_name }} ({{ $bulan->translatedFormat('F Y') }})</title>
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
            width: 297mm; /* A4 Landscape */
            min-height: 210mm;
            margin: 0 auto;
            padding: 15mm 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .no-print {
            max-width: 297mm;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
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
            margin-bottom: 8px;
        }

        .letterhead h4 { font-size: 12pt; text-transform: uppercase; font-weight: normal; }
        .letterhead h3 { font-size: 13pt; text-transform: uppercase; font-weight: bold; margin: 2px 0; }
        .letterhead h2 { font-size: 15pt; text-transform: uppercase; font-weight: 800; margin-bottom: 3px; }
        .letterhead p { font-size: 9pt; }

        .kop-divider {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin: 8px 0 14px 0;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h3 {
            font-size: 13pt;
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-title p {
            font-size: 10pt;
            margin-top: 2px;
            font-style: italic;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .ledger-table th, .ledger-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .ledger-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .sign-grid {
            width: 100%;
            margin-top: 20px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .sign-grid td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .sign-space { height: 60px; }
        .sign-name { font-weight: bold; text-decoration: underline; }

        @media print {
            body { background: #fff; padding: 0; }
            .paper { box-shadow: none; margin: 0; width: 100%; padding: 10mm 15mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('admin.register_d.index') }}" class="btn btn-secondary">&larr; Kembali ke Buku Register D</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Rekapitulasi (A4 Landscape)</button>
</div>

<div class="paper">
    <div class="letterhead">
        <h4>KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</h4>
        <h3>{{ strtoupper($setting->kanwil_name ?? 'KANTOR WILAYAH DITJENPAS') }}</h3>
        <h2>{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN KELAS I') }}</h2>
        <p>{{ $setting->alamat_lengkap ?? '-' }} &bull; Telp: {{ $setting->kontak_telepon ?? '-' }}</p>
    </div>

    <div class="kop-divider"></div>

    <div class="doc-title">
        <h3>BUKU BESAR REKAPITULASI REGISTER D (UANG TITIPAN WARGA BINAAN)</h3>
        <p>Bulan Pembukuan: {{ $bulan->translatedFormat('F Y') }} (Status Per: {{ now()->translatedFormat('d F Y') }})</p>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 110px;">No. Register D</th>
                <th style="width: 110px;">No. Reg Lapas</th>
                <th>Nama Warga Binaan</th>
                <th style="width: 80px;">Kamar</th>
                <th style="width: 120px;">VA Bank</th>
                <th style="width: 110px;">Penerimaan (+)</th>
                <th style="width: 110px;">Pengeluaran (-)</th>
                <th style="width: 120px;">Saldo Akhir Mengendap</th>
                <th style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalKredit = 0;
                $grandTotalDebit = 0;
            @endphp
            @forelse($inmates as $idx => $w)
                @php
                    $kredit = $w->transactions->where('type', 'topup')->sum('amount');
                    $debit = $w->transactions->whereIn('type', ['purchase', 'transfer_out'])->sum('amount');
                    $grandTotalKredit += $kredit;
                    $grandTotalDebit += $debit;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8.5pt;">{{ $w->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($w->id, 4, '0', STR_PAD_LEFT)) }}</td>
                    <td>{{ $w->no_registrasi }}</td>
                    <td><strong>{{ $w->nama }}</strong></td>
                    <td class="text-center">{{ $w->blok_kamar }}</td>
                    <td style="font-family: monospace; font-size: 8.5pt;">{{ $w->va_number }}</td>
                    <td class="text-right">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($w->saldo, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-size: 8pt;">{{ strtoupper($w->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Belum ada data warga binaan terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td colspan="6" style="text-align: right; padding: 6px;">TOTAL KESELURUHAN (UPT):</td>
                <td class="text-right">Rp {{ number_format($grandTotalKredit, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 10pt;">Rp {{ number_format($totalSaldoSeluruh, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-grid">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Lembaga Pemasyarakatan,
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->kepala_nama ?? 'Bpk. Kepala Lapas Kelas I' }}</div>
                <div>NIP. {{ $setting->kepala_nip ?? '198002022005011002' }}</div>
            </td>
            <td>
                Kepala Kesatuan Pengamanan Lapas (Ka. KPLP),
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->kplp_nama ?? 'Bpk. Ka. KPLP Lapas' }}</div>
                <div>NIP. {{ $setting->kplp_nip ?? '198205052006041003' }}</div>
            </td>
            <td>
                {{ $setting->upt_name ?? 'Lapas' }}, {{ now()->translatedFormat('d F Y') }}<br>
                Petugas Pengelola Register D,
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->register_d_officer_name ?? 'Petugas Register D' }}</div>
                <div>NIP. {{ $setting->register_d_officer_nip ?? '198801012012011001' }}</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

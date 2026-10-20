<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Setor Titipan Register D - {{ $transaction->receipt_number ?? $transaction->invoice_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            padding: 20px;
        }

        .paper {
            background: #fff;
            width: 210mm;
            min-height: 148mm; /* A5 Landscape */
            margin: 0 auto;
            padding: 15mm 20mm;
            border: 2px dashed #334155;
            position: relative;
        }

        .no-print {
            max-width: 210mm;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
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

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h3 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header p {
            font-size: 9pt;
        }

        .kuitansi-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 14px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 16px;
        }

        .content-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .content-table .lbl {
            width: 190px;
        }

        .content-table .sep {
            width: 15px;
            text-align: center;
        }

        .nominal-box {
            border: 2px solid #000;
            background: #f8fafc;
            padding: 8px 14px;
            font-size: 14pt;
            font-weight: bold;
            display: inline-block;
            margin-top: 6px;
        }

        .sign-grid {
            width: 100%;
            margin-top: 18px;
            font-size: 9.5pt;
        }

        .sign-grid td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .sign-space {
            height: 55px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .paper { border: 1px solid #000; box-shadow: none; margin: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('admin.topup') }}" class="btn btn-secondary">&larr; Kembali ke Loket</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Kuitansi Slip</button>
</div>

<div class="paper">
    <div class="header">
        <h3>{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN KELAS I') }}</h3>
        <p>LOKET PENERIMAAN UANG TITIPAN NARAPIDANA / TAHANAN (REGISTER D)</p>
        <p style="font-size: 8pt;">{{ $setting->alamat_lengkap ?? '-' }} &bull; Kontak: {{ $setting->kontak_telepon ?? '-' }}</p>
    </div>

    <div class="kuitansi-title">
        TANDA TERIMA TITIPAN DANA (REGISTER D)
    </div>

    <table class="content-table">
        <tr>
            <td class="lbl">No. Kuitansi / Bukti</td>
            <td class="sep">:</td>
            <td><strong>{{ $transaction->receipt_number ?? $transaction->invoice_no }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Telah Terima Dari</td>
            <td class="sep">:</td>
            <td>
                <strong>{{ strtoupper($transaction->sender_name ?? 'KELUARGA WBP') }}</strong>
                @if($transaction->sender_relation)
                    ({{ $transaction->sender_relation }})
                @endif
                @if($transaction->sender_nik)
                    &bull; NIK: {{ $transaction->sender_nik }}
                @endif
                @if($transaction->sender_phone)
                    &bull; No. HP: {{ $transaction->sender_phone }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Uang Titipan Sejumlah</td>
            <td class="sep">:</td>
            <td>
                <div class="nominal-box">
                    Rp {{ number_format($transaction->amount, 0, ',', '.') }},-
                </div>
            </td>
        </tr>
        <tr>
            <td class="lbl">Untuk Diserahkan Kepada</td>
            <td class="sep">:</td>
            <td>
                <strong>{{ strtoupper($transaction->inmate->nama) }}</strong> 
                (No. Reg: <strong>{{ $transaction->inmate->no_registrasi }}</strong> &bull; No. Reg D: <strong>{{ $transaction->inmate->register_d_number }}</strong>)
            </td>
        </tr>
        <tr>
            <td class="lbl">Kamar & Blok Hunian</td>
            <td class="sep">:</td>
            <td>{{ $transaction->inmate->blok_kamar }}</td>
        </tr>
        <tr>
            <td class="lbl">Posisi Saldo Akhir WBP</td>
            <td class="sep">:</td>
            <td><strong>Rp {{ number_format($transaction->balance_after, 0, ',', '.') }},-</strong></td>
        </tr>
        <tr>
            <td class="lbl">Catatan Titipan</td>
            <td class="sep">:</td>
            <td style="font-style: italic;">{{ $transaction->notes ?? 'Setoran tunai loket kunjungan keluarga' }}</td>
        </tr>
    </table>

    <table class="sign-grid">
        <tr>
            <td>
                Penyetor (Keluarga/Pembesuk),
                <div class="sign-space"></div>
                <strong>( {{ strtoupper($transaction->sender_name ?? '...........................') }} )</strong>
            </td>
            <td>
                {{ $setting->upt_name ?? 'Lapas' }}, {{ $transaction->created_at->format('d/m/Y H:i') }} WIB<br>
                Petugas Penerima Loket Register D,
                <div class="sign-space"></div>
                <strong>( {{ strtoupper($transaction->officer_name ?? ($setting->register_d_officer_name ?? 'PETUGAS LOKET')) }} )</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

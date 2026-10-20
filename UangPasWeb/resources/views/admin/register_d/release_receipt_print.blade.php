<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Penyerahan Saldo Register D - {{ $inmate->nama }}</title>
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
            padding: 20mm 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .no-print {
            max-width: 210mm;
            margin: 0 auto 14px auto;
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
            margin: 8px 0 16px 0;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h3 {
            font-size: 13pt;
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-title p {
            font-size: 10pt;
            margin-top: 3px;
        }

        .body-text {
            font-size: 11pt;
            line-height: 1.6;
            text-align: justify;
            margin-bottom: 14px;
        }

        .info-table {
            width: 100%;
            margin-left: 20px;
            margin-bottom: 14px;
            font-size: 11pt;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .info-table .lbl { width: 200px; }
        .info-table .sep { width: 15px; }

        .nominal-highlight {
            border: 2px solid #000;
            background: #f8fafc;
            padding: 10px 16px;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 16px 0;
        }

        .sign-grid {
            width: 100%;
            margin-top: 30px;
            font-size: 10.5pt;
            border-collapse: collapse;
        }

        .sign-grid td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .sign-space { height: 75px; }
        .sign-name { font-weight: bold; text-decoration: underline; }

        @media print {
            body { background: #fff; padding: 0; }
            .paper { box-shadow: none; margin: 0; width: 100%; padding: 15mm 20mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('admin.inmates.detail', $inmate->id) }}" class="btn btn-secondary">&larr; Kembali ke Profil WBP</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Berita Acara (A4)</button>
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
        <h3>BERITA ACARA PENYERAHAN SISA UANG TITIPAN</h3>
        <p>Nomor: W.10.PAS/REG-D/BA/{{ date('Y') }}/{{ str_pad($inmate->id, 4, '0', STR_PAD_LEFT) }}</p>
    </div>

    <p class="body-text">
        Pada hari ini, <strong>{{ now()->translatedFormat('l') }}</strong> tanggal <strong>{{ now()->translatedFormat('d F Y') }}</strong>, bertempat di Lembaga Pemasyarakatan, kami yang bertanda tangan di bawah ini:
    </p>

    <table class="info-table">
        <tr>
            <td class="lbl">1. Nama Petugas</td>
            <td class="sep">:</td>
            <td><strong>{{ $inmate->payout_officer ?? ($setting->register_d_officer_name ?? 'Petugas Register D') }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Jabatan</td>
            <td class="sep">:</td>
            <td>Pengelola Buku Register D / Petugas Registrasi Pembebasan</td>
        </tr>
    </table>

    <p class="body-text" style="margin-left: 20px;">
        Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.
    </p>

    <table class="info-table">
        <tr>
            <td class="lbl">2. Nama Warga Binaan</td>
            <td class="sep">:</td>
            <td><strong>{{ strtoupper($inmate->nama) }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Nomor Registrasi Lapas</td>
            <td class="sep">:</td>
            <td>{{ $inmate->no_registrasi }}</td>
        </tr>
        <tr>
            <td class="lbl">Nomor Buku Register D</td>
            <td class="sep">:</td>
            <td>{{ $inmate->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT)) }}</td>
        </tr>
        <tr>
            <td class="lbl">Kamar / Blok Terakhir</td>
            <td class="sep">:</td>
            <td>{{ $inmate->blok_kamar }}</td>
        </tr>
        <tr>
            <td class="lbl">Alasan Pembebasan</td>
            <td class="sep">:</td>
            <td>{{ $inmate->alasan_status ?? 'Bebas Murni / PB / CB / Asimilasi' }}</td>
        </tr>
    </table>

    <p class="body-text" style="margin-left: 20px;">
        Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.
    </p>

    <p class="body-text">
        Dengan ini menerangkan bahwa <strong>PIHAK PERTAMA</strong> telah menyerahkan kembali secara utuh sisa uang titipan narapidana pada rekening <strong>Buku Register D</strong> kepada <strong>PIHAK KEDUA</strong> sehubungan dengan telah berakhirnya masa pidana / pembebasan narapidana yang bersangkutan sejumlah:
    </p>

    <div class="nominal-highlight">
        Rp {{ number_format($inmate->payout_amount ?? 0, 0, ',', '.') }},-
    </div>

    <p class="body-text">
        <strong>PIHAK KEDUA</strong> menyatakan telah menerima uang titipan tersebut secara tunai dalam keadaan penuh, benar, dan tanpa potongan apapun. Dengan ditandatanganinya Berita Acara ini, rekening Buku Register D atas nama yang bersangkutan resmi <strong>DITUTUP</strong>.
    </p>

    <p class="body-text">
        Demikian Berita Acara ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <table class="sign-grid">
        <tr>
            <td>
                Yang Menerima,<br>
                <strong>PIHAK KEDUA</strong> (Warga Binaan),
                <div class="sign-space"></div>
                <div class="sign-name">{{ strtoupper($inmate->nama) }}</div>
                <div style="font-size: 9pt; color: #475569;">(Tanda Tangan & Cap Jempol)</div>
            </td>
            <td>
                Yang Menyerahkan,<br>
                <strong>PIHAK PERTAMA</strong> (Petugas Register D),
                <div class="sign-space"></div>
                <div class="sign-name">{{ $inmate->payout_officer ?? ($setting->register_d_officer_name ?? 'Petugas Register D') }}</div>
                <div>NIP. {{ $setting->register_d_officer_nip ?? '-' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 24px;">
                Mengetahui,<br>
                Kepala Kesatuan Pengamanan Lapas (Ka. KPLP),
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->kplp_nama ?? 'Bpk. Ka. KPLP Lapas' }}</div>
                <div>NIP. {{ $setting->kplp_nip ?? '198205052006041003' }}</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

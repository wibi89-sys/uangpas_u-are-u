<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening Koran Register D - {{ $inmate->nama }} ({{ $inmate->no_registrasi }})</title>
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
            position: relative;
        }

        /* Action bar for screen */
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

        /* Letterhead / Kop Surat */
        .letterhead {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
        }

        .letterhead h4 {
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .letterhead h3 {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .letterhead h2 {
            font-size: 16pt;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .letterhead p {
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.3;
        }

        .kop-divider {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin: 10px 0 16px 0;
        }

        /* Title */
        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h3 {
            font-size: 13pt;
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title p {
            font-size: 10.5pt;
            margin-top: 2px;
            font-style: italic;
        }

        /* Profile Box */
        .profile-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .profile-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .profile-table .label {
            width: 170px;
            color: #1e293b;
        }

        .profile-table .sep {
            width: 15px;
            text-align: center;
        }

        .profile-table .val {
            font-weight: bold;
        }

        /* Mutasi Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9pt;
        }

        .ledger-table th, .ledger-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .ledger-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .ledger-table .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .ledger-table .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #fafafa;
        }

        /* Signatures Three-way */
        .sign-grid {
            width: 100%;
            margin-top: 24px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .sign-grid td {
            vertical-align: top;
            text-align: center;
            width: 33.33%;
            padding: 0 10px;
        }

        .sign-space {
            height: 70px;
        }

        .sign-name {
            font-weight: bold;
            text-decoration: underline;
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
                padding: 10mm 15mm;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('admin.register_d.show', $inmate->id) }}" class="btn btn-secondary">
        &larr; Kembali ke Buku Mutasi
    </a>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-primary">
            <span>🖨️</span> Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="paper">
    <!-- Kop Surat Resmi -->
    <div class="letterhead">
        <h4>KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</h4>
        <h3>{{ strtoupper($setting->kanwil_name ?? 'KANTOR WILAYAH DITJENPAS') }}</h3>
        <h2>{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN KELAS I') }}</h2>
        <p>{{ $setting->alamat_lengkap ?? 'Jl. Veteran No. 1' }} &bull; Telp: {{ $setting->kontak_telepon ?? '-' }} &bull; Email: {{ $setting->kontak_email ?? '-' }}</p>
    </div>

    <div class="kop-divider"></div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h3>BUKU REGISTER D - REKENING KORAN UANG TITIPAN WARGA BINAAN</h3>
        <p>Periode Pembukuan: {{ $startDate->translatedFormat('d F Y') }} s.d. {{ $endDate->translatedFormat('d F Y') }}</p>
    </div>

    <!-- Identitas Nasabah WBP -->
    <table class="profile-table">
        <tr>
            <td class="label">Nomor Register D</td>
            <td class="sep">:</td>
            <td class="val" style="color: #0284c7;">{{ $inmate->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT)) }}</td>
            <td class="label">Blok & Kamar Hunian</td>
            <td class="sep">:</td>
            <td class="val">{{ $inmate->blok_kamar }}</td>
        </tr>
        <tr>
            <td class="label">Nama Warga Binaan</td>
            <td class="sep">:</td>
            <td class="val">{{ strtoupper($inmate->nama) }}</td>
            <td class="label">Status WBP</td>
            <td class="sep">:</td>
            <td class="val">{{ strtoupper($inmate->status) }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Registrasi Lapas</td>
            <td class="sep">:</td>
            <td class="val">{{ $inmate->no_registrasi }}</td>
            <td class="label">Nomor Rekening VA Titipan</td>
            <td class="sep">:</td>
            <td class="val">{{ $inmate->va_number }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Kependudukan (NIK)</td>
            <td class="sep">:</td>
            <td class="val">{{ $inmate->nik ?? '-' }}</td>
            <td class="label">Nama Penjamin / Keluarga</td>
            <td class="sep">:</td>
            <td class="val">{{ $inmate->penjamin_nama ?? ($inmate->kontak_keluarga ?? 'Keluarga Bersangkutan') }}</td>
        </tr>
    </table>

    <!-- Tabel Rekening Koran Mutasi -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 95px;">No. Bukti / Kuitansi</th>
                <th>Uraian / Keperluan Transaksi</th>
                <th style="width: 120px;">Penyetor / Pihak Terkait</th>
                <th style="width: 85px;">Debet (-)</th>
                <th style="width: 85px;">Kredit (+)</th>
                <th style="width: 90px;">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                <td style="font-family: monospace; font-size: 8pt;">{{ $trx->receipt_number ?? $trx->invoice_no }}</td>
                <td>
                    <strong>
                        @if($trx->type === 'topup')
                            [KREDIT] {{ $trx->payment_method === 'bank_va' ? 'Transfer Bank Virtual Account' : 'Setor Tunai Loket Kunjungan' }}
                        @elseif($trx->type === 'transfer_out')
                            [DEBET] Pencairan Sisa Saldo Bebas
                        @else
                            [DEBET] Belanja Non-Tunai Kantin
                        @endif
                    </strong>
                    @if($trx->notes)
                        <div style="font-size: 8pt; color: #475569;">{{ $trx->notes }}</div>
                    @endif
                </td>
                <td style="font-size: 8.5pt;">
                    @if($trx->sender_name)
                        {{ $trx->sender_name }} ({{ $trx->sender_relation ?? 'Keluarga' }})
                    @else
                        {{ $trx->cashier_name ?? $trx->officer_name ?? 'Kasir Lapas' }}
                    @endif
                </td>
                <td class="text-right">
                    @if(in_array($trx->type, ['purchase', 'transfer_out']))
                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">
                    @if($trx->type === 'topup')
                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right" style="font-weight: bold;">
                    Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    Tidak ada mutasi penerimaan atau pengeluaran pada rentang waktu ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding: 6px;">TOTAL PERIODE INI:</td>
                <td class="text-right" style="color: #000;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #000;">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 10pt;">Rp {{ number_format($inmate->saldo, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="font-size: 8.5pt; font-style: italic; margin-bottom: 14px;">
        * Catatan: Lembar rekening koran ini merupakan salinan sah dari Buku Register D Digital Lembaga Pemasyarakatan berdasarkan pencatatan transaksi non-tunai bebas uang tunai (Zero Halinar).
    </p>

    <!-- Lembar Pengesahan Tiga Pihak -->
    <table class="sign-grid">
        <tr>
            <td>
                Warga Binaan Pemasyarakatan<br>
                (Nasabah Pemilik Rekening),
                <div class="sign-space"></div>
                <div class="sign-name">{{ strtoupper($inmate->nama) }}</div>
                <div>No. Reg: {{ $inmate->no_registrasi }}</div>
            </td>
            <td>
                {{ $setting->upt_name ?? 'Lapas Kelas I' }}, {{ now()->translatedFormat('d F Y') }}<br>
                Petugas Pengelola Register D,
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->register_d_officer_name ?? 'Petugas Register D' }}</div>
                <div>NIP. {{ $setting->register_d_officer_nip ?? '198801012012011001' }}</div>
            </td>
            <td>
                Mengetahui,<br>
                Ka. Kesatuan Pengamanan Lapas (Ka. KPLP),
                <div class="sign-space"></div>
                <div class="sign-name">{{ $setting->kplp_nama ?? 'Bpk. Ka. KPLP Lapas' }}</div>
                <div>NIP. {{ $setting->kplp_nip ?? '198205052006041003' }}</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tabungan Register D - {{ $inmate->nama }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f8fafc;
            padding: 20px;
        }

        .passbook {
            background: #fffef0; /* Khas warna kertas buku tabungan */
            width: 190mm;
            min-height: 250mm;
            margin: 0 auto;
            padding: 16mm 16mm;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            border: 1px solid #d4d4d8;
        }

        .no-print {
            max-width: 190mm;
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

        .passbook-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .passbook-header h3 {
            font-size: 13pt;
            font-weight: bold;
        }

        .passbook-info {
            width: 100%;
            font-size: 9.5pt;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .passbook-info td {
            padding: 2px 4px;
        }

        .passbook-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .passbook-table th, .passbook-table td {
            border: 1px solid #52525b;
            padding: 4px 5px;
        }

        .passbook-table th {
            background-color: #fef08a;
            font-weight: bold;
            text-align: center;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .passbook { box-shadow: none; border: none; margin: 0; width: 100%; background: #fff; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('admin.register_d.show', $inmate->id) }}" class="btn btn-secondary">&larr; Kembali</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Lembar Buku Saku</button>
</div>

<div class="passbook">
    <div class="passbook-header">
        <h3>BUKU TABUNGAN UANG TITIPAN (REGISTER D)</h3>
        <p style="font-size: 10pt;">{{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN') }}</p>
    </div>

    <table class="passbook-info">
        <tr>
            <td style="width: 120px;">No. Register D</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $inmate->register_d_number ?? ('REG-D/' . date('Y') . '/' . str_pad($inmate->id, 4, '0', STR_PAD_LEFT)) }}</strong></td>
            <td style="width: 100px;">Kamar/Blok</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $inmate->blok_kamar }}</strong></td>
        </tr>
        <tr>
            <td>Nama Nasabah</td>
            <td>:</td>
            <td><strong>{{ strtoupper($inmate->nama) }}</strong></td>
            <td>No. Reg Lapas</td>
            <td>:</td>
            <td>{{ $inmate->no_registrasi }}</td>
        </tr>
        <tr>
            <td>Nomor VA Bank</td>
            <td>:</td>
            <td>{{ $inmate->va_number }}</td>
            <td>Saldo Akhir</td>
            <td>:</td>
            <td><strong style="color: #0284c7;">Rp {{ number_format($inmate->saldo, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table class="passbook-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 80px;">Kode/Kwt</th>
                <th>Uraian Transaksi</th>
                <th style="width: 75px;">Debet (-)</th>
                <th style="width: 75px;">Kredit (+)</th>
                <th style="width: 80px;">Saldo</th>
                <th style="width: 40px;">Paraf</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $idx => $t)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $t->created_at->format('d/m/y') }}</td>
                <td style="font-size: 8pt;">{{ substr($t->receipt_number ?? $t->invoice_no, -8) }}</td>
                <td style="font-size: 8pt;">
                    @if($t->type === 'topup')
                        Setoran Titipan
                    @elseif($t->type === 'transfer_out')
                        Pencairan Bebas
                    @else
                        Belanja Kantin
                    @endif
                </td>
                <td class="text-right">
                    {{ in_array($t->type, ['purchase', 'transfer_out']) ? number_format($t->amount, 0, ',', '.') : '' }}
                </td>
                <td class="text-right">
                    {{ $t->type === 'topup' ? number_format($t->amount, 0, ',', '.') : '' }}
                </td>
                <td class="text-right" style="font-weight: bold;">
                    {{ number_format($t->balance_after, 0, ',', '.') }}
                </td>
                <td class="text-center" style="font-size: 7pt;">✓</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 16px;">Belum ada baris transaksi.</td>
            </tr>
            @endforelse
            {{-- Isi baris kosong khas buku tabungan jika sedikit transaksi --}}
            @for($i = count($transactions); $i < 18; $i++)
            <tr style="height: 22px;">
                <td class="text-center" style="color: #a1a1aa;">{{ $i + 1 }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
        </tbody>
    </table>
</div>

</body>
</html>

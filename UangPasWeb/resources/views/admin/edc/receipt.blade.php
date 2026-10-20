<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Debit EDC - {{ $transaction->invoice_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            padding: 10px;
        }

        .thermal-receipt {
            background: #fff;
            width: 76mm; /* Standard 80mm printer printable width */
            margin: 0 auto;
            padding: 10px 8px;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            border: 1px dashed #cbd5e1;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
        }

        .no-print {
            max-width: 76mm;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 6px 12px;
            font-family: sans-serif;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #cbd5e1;
        }

        .btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
        .btn-secondary { background: #fff; color: #334155; }

        @media print {
            body { background: #fff; padding: 0; }
            .thermal-receipt { border: none; margin: 0; width: 100%; padding: 4px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print">
    <a href="{{ route('admin.edc.index') }}" class="btn btn-secondary">&larr; Kembali</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak</button>
</div>

<div class="thermal-receipt">
    <div class="text-center bold" style="font-size: 13px;">
        UANGPAS LAPAS
    </div>
    <div class="text-center bold" style="font-size: 11px;">
        {{ strtoupper($setting->upt_name ?? 'LEMBAGA PEMASYARAKATAN') }}
    </div>
    <div class="text-center" style="font-size: 9px; margin-top: 2px;">
        STRUK BUKTI DEBIT NON-TUNAI (EDC)
    </div>

    <div class="divider"></div>

    <div class="flex-between">
        <span>No. Trx</span>
        <span class="bold">{{ $transaction->invoice_no }}</span>
    </div>
    <div class="flex-between">
        <span>Waktu</span>
        <span>{{ $transaction->created_at->format('d/m/y H:i:s') }}</span>
    </div>
    <div class="flex-between">
        <span>Kasir</span>
        <span>{{ $transaction->cashier_name }}</span>
    </div>
    @if($transaction->reference_id)
    <div class="flex-between">
        <span>Ref POS</span>
        <span>{{ $transaction->reference_id }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="flex-between">
        <span>Nama WBP</span>
        <span class="bold">{{ strtoupper($transaction->inmate->nama) }}</span>
    </div>
    <div class="flex-between">
        <span>No. Reg</span>
        <span>{{ $transaction->inmate->no_registrasi }}</span>
    </div>
    <div class="flex-between">
        <span>Reg D</span>
        <span>{{ $transaction->inmate->register_d_number ?? '-' }}</span>
    </div>
    <div class="flex-between">
        <span>Kamar</span>
        <span>{{ $transaction->inmate->blok_kamar }}</span>
    </div>

    <div class="divider"></div>

    <div style="margin: 4px 0;">
        <div class="bold" style="font-size: 11.5px;">{{ $transaction->notes ?? 'Belanja Kantin Lapas' }}</div>
    </div>

    <div class="divider"></div>

    <div class="flex-between bold" style="font-size: 13px; margin: 4px 0;">
        <span>TOTAL DEBIT:</span>
        <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
    </div>
    <div class="flex-between">
        <span>Metode:</span>
        <span class="bold">{{ strtoupper($transaction->payment_method) }}</span>
    </div>
    <div class="flex-between">
        <span>Status:</span>
        <span class="bold" style="color: #000;">✓ LUNAS</span>
    </div>

    <div class="divider"></div>

    <div class="flex-between" style="font-size: 10px;">
        <span>Saldo Awal:</span>
        <span>Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}</span>
    </div>
    <div class="flex-between bold" style="font-size: 11px;">
        <span>SISA SALDO TITIPAN:</span>
        <span>Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</span>
    </div>
    <div class="flex-between" style="font-size: 10px;">
        <span>Sisa Limit Hari Ini:</span>
        <span>Rp {{ number_format($transaction->inmate->remaining_daily_limit, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 9px; margin-top: 6px;">
        PROGRAM BEBAS UANG TUNAI<br>
        (ZERO HALINAR DITJENPAS)<br>
        Simpan struk ini sebagai bukti potong saldo.
    </div>
</div>

</body>
</html>

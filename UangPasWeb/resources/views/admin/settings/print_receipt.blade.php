<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja - {{ $transaction->invoice_no }}</title>
    <style>
        @page {
            size: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '80mm' : '58mm' }} auto;
            margin: 0;
        }

        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '12px' : '10.5px' }};
            color: #000;
            background: #fff;
            margin: 0;
            padding: 8px 10px;
            width: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '74mm' : '52mm' }};
            line-height: 1.3;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .store-name {
            font-size: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '14px' : '12px' }};
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .meta-section {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        .items-section {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .item-row {
            margin-bottom: 4px;
        }

        .total-section {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '14px' : '12px' }};
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: {{ ($setting->receipt_paper_width ?? '58mm') === '80mm' ? '10px' : '9px' }};
            white-space: pre-line;
            line-height: 1.25;
            padding-top: 4px;
        }

        .no-print-bar {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #0f172a;
            padding: 8px 14px;
            border-radius: 20px;
            display: flex;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 999;
        }

        .btn-print {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-close {
            background: #64748b;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 4px 6px;
            }
        }
    </style>
</head>
<body>

    <!-- Controls (Hidden on Print) -->
    <div class="no-print-bar">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
        <button onclick="window.close()" class="btn-close">Tutup</button>
    </div>

    <!-- Receipt Header -->
    <div class="header">
        <div class="store-name">{{ $setting->receipt_store_name ?? 'KOPERASI PENGAYOMAN LAPAS' }}</div>
        @if(!empty($setting->receipt_address))
            <div>{{ $setting->receipt_address }}</div>
        @endif
        @if(!empty($setting->receipt_phone))
            <div>Telp: {{ $setting->receipt_phone }}</div>
        @endif
    </div>

    <!-- Metadata Transaksi -->
    <div class="meta-section">
        <div class="row">
            <span>No. Struk:</span>
            <span class="font-bold">{{ $transaction->invoice_no }}</span>
        </div>
        <div class="row">
            <span>Tanggal :</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Kasir   :</span>
            <span>{{ $transaction->cashier_name ?? 'Kasir POS' }}</span>
        </div>
        <div class="row">
            <span>Metode  :</span>
            <span class="font-bold">
                @if($transaction->payment_method === 'fingerprint')
                    SIDIK JARI WBP
                @elseif($transaction->payment_method === 'cash_counter')
                    LOKET TUNAI
                @else
                    {{ strtoupper($transaction->payment_method) }}
                @endif
            </span>
        </div>
        @if($transaction->inmate)
        <div style="margin-top: 3px; padding-top: 3px; border-top: 1px dotted #ccc;">
            <div>WBP : <span class="font-bold">{{ $transaction->inmate->nama }}</span></div>
            <div>Reg : {{ $transaction->inmate->no_registrasi }}</div>
            <div>Blok: {{ $transaction->inmate->blok_kamar ?? '-' }}</div>
        </div>
        @endif
    </div>

    <!-- Rincian Barang -->
    <div class="items-section">
        <div class="row font-bold" style="margin-bottom: 3px;">
            <span>BARANG</span>
            <span>SUBTOTAL</span>
        </div>

        @foreach($transaction->items as $it)
        <div class="item-row">
            <div>{{ $it->nama_barang }}</div>
            <div class="row" style="color: #333; font-size: 0.95em;">
                <span>{{ $it->qty }} x Rp {{ number_format($it->harga_satuan, 0, ',', '.') }}</span>
                <span class="font-bold">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total Tagihan & Sisa Saldo -->
    <div class="total-section">
        <div class="total-row">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
        </div>

        @if($setting->receipt_show_inmate_balance ?? true)
        <div style="margin-top: 4px; padding-top: 3px; border-top: 1px dotted #ccc; font-size: 0.95em;">
            <div class="row">
                <span>Saldo Sebelum:</span>
                <span>Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}</span>
            </div>
            <div class="row font-bold">
                <span>SISA SALDO WBP:</span>
                <span>Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Footer Text -->
    <div class="footer">
{{ $setting->receipt_footer ?? "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Sah\nUangPas - Zero Cash Solution" }}
    </div>

    <script>
        // Auto trigger print dialog on page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>

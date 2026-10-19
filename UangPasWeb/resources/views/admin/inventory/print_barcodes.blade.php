<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode Rak - Koperasi Lapas</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
        }

        .no-print {
            background: #0f172a;
            color: #fff;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .no-print h2 {
            font-size: 16px;
            font-weight: 800;
        }

        .no-print p {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .btn-print {
            background: #0284c7;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #0369a1; }

        .btn-back {
            background: #334155;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-back:hover { background: #475569; }

        /* Label Grid Layout */
        .sheet-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin: 0 auto;
            max-width: 900px;
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .barcode-card {
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            background: #fff;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 145px;
        }

        .card-header-lapas {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .product-name {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            height: 28px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .barcode-svg-container {
            margin: 2px auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .barcode-svg {
            max-width: 100%;
            height: 38px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-top: 1px solid #f1f5f9;
            padding-top: 4px;
            margin-top: 2px;
        }

        .price-tag {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
        }

        .rak-tag {
            font-size: 9.5px;
            color: #64748b;
            font-weight: 600;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .sheet-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .barcode-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            .barcode-card {
                border: 1px dashed #94a3b8;
                height: 140px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div>
            <h2>🖨️ Cetak Stiker Label Barcode & Rak Toko Koperasi</h2>
            <p>Daftar {{ $products->count() }} SKU barang aktif. Sesuaikan ke kertas stiker label atau printer thermal.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.products') }}" class="btn-back">← Kembali ke Katalog</a>
            <button onclick="window.print()" class="btn-print">
                <span>🖨️</span> Cetak Sekarang (Ctrl+P)
            </button>
        </div>
    </div>

    <div class="sheet-container">
        <div class="barcode-grid">
            @forelse($products as $p)
            <div class="barcode-card">
                <div class="card-header-lapas">
                    KOPERASI PRIMER LAPAS • ZERO CASH
                </div>
                <div class="product-name" title="{{ $p->nama_barang }}">
                    {{ $p->nama_barang }}
                </div>
                <div class="barcode-svg-container">
                    <svg class="barcode-svg" data-barcode="{{ $p->barcode }}"></svg>
                </div>
                <div class="card-footer">
                    <span class="rak-tag">📍 {{ $p->lokasi_rak ?: 'Etalase' }} ({{ $p->satuan }})</span>
                    <span class="price-tag">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
                Tidak ada barang yang dipilih untuk dicetak.
            </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof JsBarcode === 'function') {
                document.querySelectorAll('.barcode-svg').forEach(function(svg) {
                    var code = svg.getAttribute('data-barcode');
                    if (code) {
                        try {
                            JsBarcode(svg, code, {
                                format: (code.length === 13 && /^\d+$/.test(code)) ? "EAN13" : "CODE128",
                                width: 1.4,
                                height: 32,
                                displayValue: true,
                                fontSize: 10,
                                margin: 0,
                                textMargin: 1
                            });
                        } catch (e) {
                            // Fallback to Code 128 if EAN13 checksum fails
                            try {
                                JsBarcode(svg, code, {
                                    format: "CODE128",
                                    width: 1.3,
                                    height: 32,
                                    displayValue: true,
                                    fontSize: 10,
                                    margin: 0
                                });
                            } catch (err) {
                                svg.outerHTML = '<div style="font-family: monospace; font-size: 11px; font-weight: bold; padding: 4px;">' + code + '</div>';
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UangPas POS Modern - Terminal Kasir Koperasi Lapas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-light: #e0f2fe;
            --success: #10b981;
            --success-hover: #059669;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f1f5f9;
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 62px;
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
        }

        .brand-text h1 {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .upt-tag {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .scanner-pill {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .dot-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
        }

        .dot-green { background: #10b981; box-shadow: 0 0 8px #10b981; }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; animation: pulse 1.5s infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .btn-header {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-header:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Main POS Grid Layout */
        .pos-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 62px);
            overflow: hidden;
        }

        /* Left Column: Products */
        .pos-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px 20px;
            background: #f8fafc;
            border-right: 1px solid var(--border);
            overflow: hidden;
        }

        .search-toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            padding: 11px 16px 11px 40px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 14px;
            font-weight: 600;
            background: #fff;
            outline: none;
            transition: all 0.2s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        .category-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .cat-chip {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            white-space: nowrap;
            transition: all 0.15s;
        }

        .cat-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .cat-chip.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
        }

        /* Excel-style Flexible Table */
        .table-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .table-header {
            padding: 10px 16px;
            background: #f1f5f9;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-scroll {
            flex: 1;
            overflow-y: auto;
        }

        table.excel-grid {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        table.excel-grid thead th {
            background: #f1f5f9;
            color: #334155;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        table.excel-grid tbody tr {
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s;
        }

        /* Zebra Striping */
        table.excel-grid tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.excel-grid tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table.excel-grid tbody tr:hover {
            background-color: #e0f2fe !important;
        }

        table.excel-grid td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .barcode-cell {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #475569;
            font-weight: 700;
        }

        .product-name {
            font-weight: 700;
            color: var(--text-main);
        }

        .price-cell {
            font-weight: 800;
            color: var(--primary);
            text-align: right;
            font-size: 14px;
        }

        .stock-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-align: center;
        }

        .stock-safe { background: #dcfce7; color: #166534; }
        .stock-low { background: #fee2e2; color: #991b1b; }

        .btn-add {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-add:hover {
            background: #059669;
        }

        /* Right Column: Cart */
        .pos-right {
            width: 440px;
            background: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 16px rgba(0,0,0,0.03);
            flex-shrink: 0;
        }

        .cart-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .cart-header h2 {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            padding: 12px 16px;
        }

        .cart-item {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.15s;
        }

        .cart-item:hover {
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .cart-item-info {
            flex: 1;
            margin-right: 12px;
        }

        .cart-item-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .cart-item-price {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .cart-qty-ctrl {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-qty {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #fff;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }

        .btn-qty:hover {
            background: #f1f5f9;
        }

        .qty-val {
            font-size: 14px;
            font-weight: 800;
            min-width: 22px;
            text-align: center;
        }

        .cart-item-subtotal {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--primary);
            min-width: 80px;
            text-align: right;
            margin-left: 10px;
        }

        .cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #94a3b8;
            text-align: center;
            padding: 40px 20px;
        }

        .cart-footer {
            padding: 16px 20px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .total-row span {
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
        }

        .total-row h3 {
            font-size: 26px;
            font-weight: 800;
            color: #0284c7;
            letter-spacing: -0.5px;
        }

        .btn-checkout {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            transition: all 0.2s;
        }

        .btn-checkout:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.5);
        }

        .btn-checkout:disabled {
            background: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .checkout-alt-row {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-alt {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-alt:hover {
            background: #f1f5f9;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            transform: scale(0.95);
            transition: transform 0.2s ease;
        }

        .modal-overlay.active .modal-card {
            transform: scale(1);
        }

        .scanner-visual {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            background: #e0f2fe;
            border: 2px solid #bae6fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(2, 132, 199, 0); }
            100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }

        /* Struk Belanja */
        .receipt-card {
            font-family: 'JetBrains Mono', monospace;
            background: #fff;
            padding: 24px;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header>
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="brand-badge">
                <div class="brand-icon">👆</div>
                <div class="brand-text">
                    <h1>UangPas POS</h1>
                    <span>Koperasi Lapas Digital</span>
                </div>
            </a>
            <div class="upt-tag">
                <span>🏛️</span> {{ $setting->upt_name }} ({{ $setting->upt_code }})
            </div>
        </div>

        <div class="header-right">
            <!-- Scanner Finger Status -->
            <div class="scanner-pill" id="scannerStatusPill">
                <div class="dot-status dot-yellow" id="scannerDot"></div>
                <span id="scannerStatusText">Scanner: Memeriksa...</span>
            </div>

            <button class="btn-header" onclick="openEnrollModal()" title="Daftarkan / Update Sidik Jari WBP">
                <span>👆</span> Rekam Jari WBP
            </button>

            <button class="btn-header" onclick="checkScannerBridge()" title="Deteksi Ulang Fingerprint">
                <span>🔄</span> Refresh Finger
            </button>

            <a href="{{ route('admin.dashboard') }}" class="btn-header">
                <span>📊</span> Portal Admin
            </a>

            <button class="btn-header" onclick="toggleFullscreen()">
                <span>🖥️</span> Layar Penuh
            </button>
        </div>
    </header>

    <!-- Main POS Interface -->
    <div class="pos-container">
        <!-- LEFT: Products Table -->
        <div class="pos-left">
            <!-- Search & Toolbar -->
            <div class="search-toolbar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Cari nama barang atau scan barcode [Enter]..." autofocus>
                </div>
            </div>

            <!-- Categories -->
            <div class="category-bar">
                <div class="cat-chip active" onclick="filterCategory('all', this)">Semua Produk ({{ count($products) }})</div>
                <div class="cat-chip" onclick="filterCategory('Makanan & Minuman', this)">🍜 Makanan & Minuman</div>
                <div class="cat-chip" onclick="filterCategory('Perlengkapan Mandi', this)">🧼 Perlengkapan Mandi</div>
                <div class="cat-chip" onclick="filterCategory('Kebutuhan Kamar', this)">🛏️ Kebutuhan Kamar</div>
            </div>

            <!-- Excel-style Zebra Table -->
            <div class="table-card">
                <div class="table-header">
                    <span style="font-size: 13px; font-weight: 700; color: #475569;">
                        📋 Katalog Barang Koperasi (<span id="visibleCount">{{ count($products) }}</span> item)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b;">
                        Tips: Double-click baris untuk menambah cepat ke keranjang
                    </span>
                </div>

                <div class="table-scroll">
                    <table class="excel-grid" id="productGrid">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Barcode</th>
                                <th>Nama Barang</th>
                                <th style="width: 170px;">Kategori</th>
                                <th style="width: 130px; text-align: right;">Harga Satuan</th>
                                <th style="width: 100px; text-align: center;">Sisa Stok</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productTbody">
                            @foreach($products as $p)
                                <tr onclick="selectProductRow(this)" ondblclick="addToCart({{ json_encode($p) }})" data-category="{{ $p->kategori }}" data-barcode="{{ $p->barcode }}" data-name="{{ strtolower($p->nama_barang) }}">
                                    <td class="barcode-cell">{{ $p->barcode }}</td>
                                    <td class="product-name">{{ $p->nama_barang }}</td>
                                    <td><span class="cat-chip" style="padding: 2px 8px; font-size: 11px;">{{ $p->kategori }}</span></td>
                                    <td class="price-cell">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                                    <td style="text-align: center;">
                                        <span class="stock-badge {{ $p->stok <= 10 ? 'stock-low' : 'stock-safe' }}">
                                            {{ $p->stok }} pcs
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="btn-add" onclick="event.stopPropagation(); addToCart({{ json_encode($p) }})">
                                            ➕ Tambah
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: Shopping Cart -->
        <div class="pos-right">
            <div class="cart-header">
                <h2><span>🛒</span> Keranjang Belanja</h2>
                <button class="btn-alt" style="padding: 4px 10px; font-size: 11px;" onclick="clearCart()">
                    🗑️ Kosongkan
                </button>
            </div>

            <!-- Quick Barcode / Card Input -->
            <div style="padding: 10px 16px; background: #f8fafc; border-bottom: 1px solid var(--border);">
                <input type="text" id="barcodeQuickInput" placeholder="⚡ Scan Barcode Cepat [Enter]..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600;">
            </div>

            <!-- Items List -->
            <div class="cart-items-container" id="cartContainer">
                <div class="cart-empty">
                    <span style="font-size: 48px; margin-bottom: 10px;">🛍️</span>
                    <h3 style="font-size: 15px; font-weight: 700; color: #475569; margin-bottom: 4px;">Keranjang Masih Kosong</h3>
                    <p style="font-size: 12px;">Pilih barang dari tabel atau scan barcode untuk memulai transaksi kasir.</p>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="cart-footer">
                <div class="total-row">
                    <span>TOTAL PEMBAYARAN:</span>
                    <h3 id="cartTotalText">Rp 0</h3>
                </div>

                <button class="btn-checkout" id="btnCheckout" onclick="openCheckoutModal()" disabled>
                    <span>👆</span> BAYAR DENGAN SIDIK JARI WBP
                </button>

                <div class="checkout-alt-row">
                    <button class="btn-alt" onclick="openManualInmateModal()">
                        💳 Pilih WBP / Tap Kartu
                    </button>
                    <button class="btn-alt" onclick="openSyncModal()">
                        🔄 Status Sinkron
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: BIOMETRIC CHECKOUT SCANNER -->
    <div class="modal-overlay" id="biometricModal">
        <div class="modal-card" style="text-align: center;">
            <div class="scanner-visual" id="modalScannerVisual">👆</div>
            <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 6px;" id="modalBiometricTitle">Tempelkan Sidik Jari WBP</h2>
            <p style="color: #64748b; font-size: 13.5px; margin-bottom: 20px;" id="modalBiometricDesc">
                Silakan tempelkan jari WBP pada alat scanner sidik jari untuk otorisasi pembayaran.
            </p>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: left;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                    <span style="color: #64748b;">Total Tagihan:</span>
                    <strong style="color: var(--primary); font-size: 15px;" id="modalTotalTagihan">Rp 0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: #64748b;">Jumlah Barang:</span>
                    <strong id="modalItemCount">0 pcs</strong>
                </div>
            </div>

            <!-- Identified Inmate Preview (Hidden until identified) -->
            <div id="inmateIdentifiedBox" style="display: none; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; background: #10b981; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>
                    <div>
                        <h4 style="font-size: 15px; font-weight: 800; color: #065f46;" id="inmateName">Budi Santoso</h4>
                        <div style="font-size: 12px; color: #047857;" id="inmateReg">WBP-2026-0001</div>
                    </div>
                </div>
                <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #d1fae5; display: flex; justify-content: space-between; font-size: 12.5px;">
                    <span>Saldo Sebelum: <strong id="inmateBalanceBefore">Rp 0</strong></span>
                    <span>Sisa Saldo: <strong style="color: #059669;" id="inmateBalanceAfter">Rp 0</strong></span>
                </div>
            </div>

            <!-- Unregistered Finger Prompt Box (Shown if finger scanned but not enrolled) -->
            <div id="unregisteredFingerBox" style="display: none; background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="font-size: 18px;">⚠️</span>
                    <strong style="color: #92400e; font-size: 13.5px;">Sidik Jari Belum Terdaftar!</strong>
                </div>
                <p style="color: #78350f; font-size: 12px; margin-bottom: 12px; line-height: 1.4;">
                    Scanner sidik jari berhasil membaca sidik jari Anda. Pilih akun WBP di bawah untuk mendaftarkan sidik jari ini dan langsung menyelesaikan transaksi:
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <select id="quickEnrollInmateSelect" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; background: #fff;">
                        @foreach($inmates as $wbp)
                            <option value="{{ $wbp->id }}">{{ $wbp->nama }} ({{ $wbp->no_registrasi }}) - Saldo: Rp {{ number_format($wbp->saldo, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn-checkout" id="btnQuickEnrollAndPay" style="width: 100%; padding: 11px; font-size: 13px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);" onclick="handleQuickEnrollAndPay()">
                        ✅ Daftarkan Jari ke WBP & Bayar Sekarang
                    </button>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button class="btn-alt" style="flex: 1; padding: 12px;" onclick="closeModal('biometricModal')">Batal</button>
                <button class="btn-alt" style="flex: 1; padding: 12px; background: #0284c7; color: #fff; border: none;" onclick="retryBiometricScan()">Scan Ulang</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: MANUAL INMATE SELECTION / CARD TAP -->
    <div class="modal-overlay" id="manualInmateModal">
        <div class="modal-card">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 14px;">Pilih Warga Binaan (WBP) / Tap Kartu</h2>
            <div class="form-group" style="margin-bottom: 14px;">
                <input type="text" id="manualSearchInput" placeholder="Ketik NIK, Nama, atau No Reg WBP..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;">
                @foreach($inmates as $wbp)
                    <div class="manual-inmate-row" onclick="selectInmateForCheckout({{ json_encode($wbp) }})" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        <div>
                            <strong style="font-size: 13.5px;">{{ $wbp->nama }}</strong>
                            <div style="font-size: 11.5px; color: #64748b;">{{ $wbp->no_registrasi }} | {{ $wbp->blok_kamar }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13.5px; font-weight: 800; color: #10b981;">Rp {{ number_format($wbp->saldo, 0, ',', '.') }}</div>
                            <span style="font-size: 10px; color: #64748b;">Saldo</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="btn-alt" style="width: 100%; padding: 10px;" onclick="closeModal('manualInmateModal')">Tutup</button>
        </div>
    </div>

    <!-- MODAL 3: STRUK BELANJA SUKSES -->
    <div class="modal-overlay" id="receiptModal">
        <div class="modal-card" style="max-width: 420px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">🎉</div>
            <h2 style="font-size: 18px; font-weight: 800; color: #10b981; margin-bottom: 4px;">Transaksi Berhasil!</h2>
            <p style="font-size: 12.5px; color: #64748b; margin-bottom: 16px;">Struk transaksi pembayaran kasir koperasi</p>

            <div class="receipt-card" id="receiptContent">
                <!-- Injected via JS -->
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn-alt" style="flex: 1; padding: 12px;" onclick="closeModal('receiptModal')">Tutup</button>
                <button class="btn-checkout" style="flex: 1; padding: 12px; font-size: 13px;" onclick="printReceipt()">🖨️ Cetak Struk</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: ENROLLMENT FINGERPRINT WBP -->
    <div class="modal-overlay" id="enrollModal">
        <div class="modal-card" style="max-width: 440px; text-align: center;">
            <div class="scanner-visual" id="enrollScannerVisual">👆</div>
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;" id="enrollModalTitle">Perekaman Sidik Jari WBP</h2>
            <p style="color: #64748b; font-size: 12.5px; margin-bottom: 16px;" id="enrollModalDesc">
                Pilih Warga Binaan, lalu tempelkan jarinya pada scanner sidik jari.
            </p>

            <div style="text-align: left; margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Pilih Warga Binaan:</label>
                <select id="enrollTargetInmateSelect" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600;">
                    @foreach($inmates as $wbp)
                        <option value="{{ $wbp->id }}">
                            {{ $wbp->nama }} ({{ $wbp->no_registrasi }}) - {{ $wbp->fingerprint_template ? '🟢 Sudah Terdaftar' : '⚪ Belum Ada Finger' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="enrollStatusBox" style="display: none; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 12.5px; font-weight: 600;"></div>

            <div style="display: flex; gap: 10px;">
                <button class="btn-alt" style="flex: 1; padding: 12px;" onclick="closeModal('enrollModal')">Tutup</button>
                <button class="btn-checkout" id="btnStartEnrollWeb" style="flex: 1; padding: 12px; font-size: 13px;" onclick="startEnrollWebScan()">⚡ Mulai Rekam Jari</button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT POS ENGINE -->
    <script>
        const productsData = @json($products);
        const inmatesData = @json($inmates);
        const uptSetting = @json($setting);

        let cart = [];
        let bridgeOnline = false;

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            checkScannerBridge();
            setupKeyboardListeners();
        });

        // 1. Check Hardware Bridge (UangPasBridge on localhost:8080)
        async function checkScannerBridge() {
            const pill = document.getElementById('scannerStatusPill');
            const dot = document.getElementById('scannerDot');
            const text = document.getElementById('scannerStatusText');

            dot.className = 'dot-status dot-yellow';
            text.innerText = 'Scanner: Memeriksa...';

            try {
                const res = await fetch('http://localhost:8080/api/status', { method: 'GET', signal: AbortSignal.timeout(2000) });
                const data = await res.json();
                const isConnected = data.readerConnected || data.connected || data.online;
                if (isConnected) {
                    bridgeOnline = true;
                    dot.className = 'dot-status dot-green';
                    const sn = data.serialNumber || data.serial || 'U.are.U 4500';
                    text.innerText = 'Finger: Siap (' + sn.substring(0, 10) + '...)';
                } else {
                    bridgeOnline = false;
                    dot.className = 'dot-status dot-red';
                    text.innerText = 'Scanner: Belum Tercolok';
                }
            } catch (err) {
                bridgeOnline = false;
                dot.className = 'dot-status dot-yellow';
                text.innerText = 'Bridge: Memulai (Port 8080)...';
            }
        }

        // 2. Cart Operations
        function addToCart(product) {
            const existing = cart.find(x => x.product_id === product.id);
            if (existing) {
                if (existing.qty < product.stok) {
                    existing.qty++;
                } else {
                    alert('Stok barang sudah mencapai batas maksimum!');
                }
            } else {
                cart.push({
                    product_id: product.id,
                    nama: product.nama_barang,
                    harga: parseFloat(product.harga),
                    stok: product.stok,
                    qty: 1
                });
            }
            renderCart();
        }

        function updateQty(idx, delta) {
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) {
                cart.splice(idx, 1);
            } else if (cart[idx].qty > cart[idx].stok) {
                cart[idx].qty = cart[idx].stok;
                alert('Stok barang tidak mencukupi!');
            }
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartContainer');
            const totalText = document.getElementById('cartTotalText');
            const btnCheckout = document.getElementById('btnCheckout');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <span style="font-size: 48px; margin-bottom: 10px;">🛍️</span>
                        <h3 style="font-size: 15px; font-weight: 700; color: #475569; margin-bottom: 4px;">Keranjang Masih Kosong</h3>
                        <p style="font-size: 12px;">Pilih barang dari tabel atau scan barcode untuk memulai transaksi kasir.</p>
                    </div>
                `;
                totalText.innerText = 'Rp 0';
                btnCheckout.disabled = true;
                return;
            }

            let total = 0;
            let html = '';

            cart.forEach((it, idx) => {
                const subtotal = it.harga * it.qty;
                total += subtotal;

                html += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-title">${it.nama}</div>
                            <div class="cart-item-price">Rp ${it.harga.toLocaleString('id-ID')} / pcs</div>
                        </div>
                        <div class="cart-qty-ctrl">
                            <button class="btn-qty" onclick="updateQty(${idx}, -1)">-</button>
                            <span class="qty-val">${it.qty}</span>
                            <button class="btn-qty" onclick="updateQty(${idx}, 1)">+</button>
                        </div>
                        <div class="cart-item-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                    </div>
                `;
            });

            container.innerHTML = html;
            totalText.innerText = 'Rp ' + total.toLocaleString('id-ID');
            btnCheckout.disabled = false;
        }

        // 3. Search & Filter
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('#productTbody tr');
            let visible = 0;

            rows.forEach(r => {
                const barcode = r.getAttribute('data-barcode').toLowerCase();
                const name = r.getAttribute('data-name');
                const cat = r.getAttribute('data-category').toLowerCase();

                if (barcode.includes(query) || name.includes(query) || cat.includes(query)) {
                    r.style.display = '';
                    visible++;
                } else {
                    r.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').innerText = visible;
        });

        function filterCategory(cat, chip) {
            document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');

            const rows = document.querySelectorAll('#productTbody tr');
            let visible = 0;

            rows.forEach(r => {
                const itemCat = r.getAttribute('data-category');
                if (cat === 'all' || itemCat === cat) {
                    r.style.display = '';
                    visible++;
                } else {
                    r.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').innerText = visible;
        }

        // 4. Barcode Quick Input Listener
        document.getElementById('barcodeQuickInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const code = this.value.trim();
                if (code) {
                    const prod = productsData.find(x => x.barcode === code);
                    if (prod) {
                        addToCart(prod);
                        this.value = '';
                    } else {
                        alert('Barang dengan barcode ' + code + ' tidak ditemukan!');
                    }
                }
            }
        });

        // 5. Checkout Modal & Biometric Flow
        let currentInmate = null;
        let scanAbortController = null;
        let lastCapturedTemplate = null;
        let lastCapturedImage = null;

        function openCheckoutModal() {
            const total = cart.reduce((sum, it) => sum + (it.harga * it.qty), 0);
            const totalQty = cart.reduce((sum, it) => sum + it.qty, 0);

            document.getElementById('modalTotalTagihan').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('modalItemCount').innerText = totalQty + ' pcs';
            document.getElementById('inmateIdentifiedBox').style.display = 'none';
            document.getElementById('unregisteredFingerBox').style.display = 'none';

            openModal('biometricModal');
            startBiometricScan();
        }

        async function startBiometricScan() {
            const visual = document.getElementById('modalScannerVisual');
            const title = document.getElementById('modalBiometricTitle');
            const desc = document.getElementById('modalBiometricDesc');
            const unregBox = document.getElementById('unregisteredFingerBox');
            const identBox = document.getElementById('inmateIdentifiedBox');

            unregBox.style.display = 'none';
            identBox.style.display = 'none';
            visual.innerHTML = '👆';
            title.innerText = 'Tempelkan Sidik Jari WBP';
            desc.innerHTML = 'Silakan tempelkan jari WBP pada alat scanner sidik jari...';

            if (!bridgeOnline) {
                desc.innerHTML = `<span style="color: #ef4444; font-weight: 700;">Hardware Bridge Offline!</span><br>Pastikan UangPasBridge berjalan di port 8080. Atau gunakan tombol <strong>"Pilih WBP Manual"</strong> di bawah.`;
                return;
            }

            try {
                if (scanAbortController) {
                    try { scanAbortController.abort(); } catch(e){}
                }
                scanAbortController = new AbortController();

                // Build candidate list with valid templates
                const candidates = inmatesData.map(wbp => ({
                    id: wbp.id,
                    inmate_id: wbp.id,
                    nama: wbp.nama,
                    no_registrasi: wbp.no_registrasi,
                    saldo: wbp.saldo,
                    template: wbp.fingerprint_template
                }));

                const res = await fetch('http://localhost:8080/api/identify', {
                    method: 'POST',
                    signal: scanAbortController.signal,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        timeout: 20000,
                        candidates: candidates
                    })
                });
                const data = await res.json();

                if (data.image) {
                    lastCapturedImage = data.image;
                    visual.innerHTML = `<img src="${data.image}" style="width: 72px; height: 92px; object-fit: contain; border-radius: 8px; border: 2px solid #0284c7; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);" />`;
                }

                if (data.captured_template) {
                    lastCapturedTemplate = data.captured_template;
                }

                if (data.success && data.identified) {
                    const inmateId = data.inmate_id || (data.candidate && data.candidate.id) || (data.candidate && data.candidate.inmate_id);
                    const inmate = inmatesData.find(x => x.id == inmateId);
                    if (inmate) {
                        title.innerText = '✅ Teridentifikasi!';
                        desc.innerText = 'Sidik jari terverifikasi cocok dengan data WBP: ' + inmate.nama;
                        onInmateIdentified(inmate, 'fingerprint');
                    } else {
                        desc.innerText = 'Sidik jari cocok dengan ID ' + inmateId + ', namun status WBP tidak aktif.';
                    }
                } else if (data.success && !data.identified && data.captured_template) {
                    title.innerText = '⚠️ Jari Terbaca (Belum Terdaftar)';
                    desc.innerText = 'Sensor berhasil membaca sidik jari, namun belum terhubung ke akun WBP.';
                    unregBox.style.display = 'block';
                } else {
                    desc.innerText = data.message || 'Waktu pembacaan habis atau scanner dibatalkan.';
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    desc.innerText = 'Gagal menghubungi scanner: ' + err.message;
                }
            }
        }

        async function handleQuickEnrollAndPay() {
            const select = document.getElementById('quickEnrollInmateSelect');
            const inmateId = select.value;
            const btn = document.getElementById('btnQuickEnrollAndPay');

            if (!lastCapturedTemplate) {
                alert('Belum ada template sidik jari yang terbaca. Silakan klik Scan Ulang.');
                return;
            }

            btn.disabled = true;
            btn.innerText = '⏳ Menyimpan Sidik Jari ke Server...';

            try {
                // Save to Laravel API
                const saveRes = await fetch(`/api/v1/pos/inmate/${inmateId}/save-fingerprint`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        fingerprint_template: lastCapturedTemplate,
                        fingerprint_image: lastCapturedImage || ''
                    })
                });

                const saveJson = await saveRes.json();
                if (!saveJson.success) {
                    alert('Gagal menyimpan sidik jari: ' + saveJson.message);
                    btn.disabled = false;
                    btn.innerText = '✅ Daftarkan Jari ke WBP & Bayar Sekarang';
                    return;
                }

                // Update local inmate data so subsequent scans match immediately!
                const inmate = inmatesData.find(x => x.id == inmateId);
                if (inmate) {
                    inmate.fingerprint_template = lastCapturedTemplate;
                }

                btn.innerText = '⚡ Memproses Pembayaran...';
                onInmateIdentified(inmate, 'fingerprint');
            } catch (err) {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.innerText = '✅ Daftarkan Jari ke WBP & Bayar Sekarang';
            }
        }

        function retryBiometricScan() {
            startBiometricScan();
        }

        // 6. Direct Enrollment Modal Functions
        function openEnrollModal() {
            document.getElementById('enrollScannerVisual').innerHTML = '👆';
            document.getElementById('enrollModalTitle').innerText = 'Perekaman Sidik Jari WBP';
            document.getElementById('enrollModalDesc').innerText = 'Pilih Warga Binaan, lalu klik Mulai Rekam Jari.';
            const statusBox = document.getElementById('enrollStatusBox');
            statusBox.style.display = 'none';
            openModal('enrollModal');
        }

        async function startEnrollWebScan() {
            const select = document.getElementById('enrollTargetInmateSelect');
            const inmateId = select.value;
            const visual = document.getElementById('enrollScannerVisual');
            const title = document.getElementById('enrollModalTitle');
            const desc = document.getElementById('enrollModalDesc');
            const statusBox = document.getElementById('enrollStatusBox');
            const btn = document.getElementById('btnStartEnrollWeb');

            visual.innerHTML = '🔴';
            title.innerText = 'Menunggu Jari Ditempelkan...';
            desc.innerText = 'Tempelkan jari WBP pada alat scanner sidik jari sekarang...';
            btn.disabled = true;
            statusBox.style.display = 'none';

            try {
                const res = await fetch('http://localhost:8080/api/capture', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ timeout: 15000 })
                });
                const data = await res.json();

                if (data.success && data.template) {
                    if (data.image) {
                        visual.innerHTML = `<img src="${data.image}" style="width: 72px; height: 92px; object-fit: contain; border-radius: 8px; border: 2px solid #10b981;" />`;
                    } else {
                        visual.innerHTML = '🟢';
                    }

                    title.innerText = 'Sidik Jari Terbaca!';
                    desc.innerText = 'Menyimpan template ke server database...';

                    // Save to Laravel
                    const saveRes = await fetch(`/api/v1/pos/inmate/${inmateId}/save-fingerprint`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            fingerprint_template: data.template,
                            fingerprint_image: data.image || ''
                        })
                    });
                    const saveJson = await saveRes.json();

                    if (saveJson.success) {
                        statusBox.style.display = 'block';
                        statusBox.style.background = '#ecfdf5';
                        statusBox.style.color = '#065f46';
                        statusBox.style.border = '1px solid #a7f3d0';
                        statusBox.innerText = '✅ Berhasil! Template sidik jari WBP tersimpan di database.';

                        // Update local cache
                        const wbp = inmatesData.find(x => x.id == inmateId);
                        if (wbp) wbp.fingerprint_template = data.template;
                    } else {
                        statusBox.style.display = 'block';
                        statusBox.style.background = '#fef2f2';
                        statusBox.style.color = '#991b1b';
                        statusBox.style.border = '1px solid #fecaca';
                        statusBox.innerText = '❌ Gagal simpan: ' + saveJson.message;
                    }
                } else {
                    visual.innerHTML = '⚠️';
                    title.innerText = 'Perekaman Gagal';
                    desc.innerText = data.message || 'Waktu habis atau scanner dibatalkan.';
                }
            } catch (err) {
                visual.innerHTML = '❌';
                title.innerText = 'Koneksi Error';
                desc.innerText = err.message;
            } finally {
                btn.disabled = false;
            }
        }

        function onInmateIdentified(inmate, paymentMethod) {
            currentInmate = inmate;
            const total = cart.reduce((sum, it) => sum + (it.harga * it.qty), 0);
            const balanceAfter = inmate.saldo - total;

            document.getElementById('inmateName').innerText = inmate.nama;
            document.getElementById('inmateReg').innerText = inmate.no_registrasi + ' | ' + inmate.blok_kamar;
            document.getElementById('inmateBalanceBefore').innerText = 'Rp ' + inmate.saldo.toLocaleString('id-ID');
            document.getElementById('inmateBalanceAfter').innerText = 'Rp ' + balanceAfter.toLocaleString('id-ID');
            document.getElementById('inmateIdentifiedBox').style.display = 'block';

            // Auto submit transaction after 800ms
            setTimeout(() => {
                submitCheckout(inmate.id, paymentMethod);
            }, 800);
        }

        async function submitCheckout(inmateId, paymentMethod) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('{{ route("pos.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        inmate_id: inmateId,
                        cart: cart,
                        payment_method: paymentMethod
                    })
                });

                const data = await res.json();
                if (data.success) {
                    closeModal('biometricModal');
                    closeModal('manualInmateModal');
                    showReceipt(data);
                    clearCart();
                } else {
                    alert('Transaksi Ditolak: ' + data.message);
                }
            } catch (err) {
                alert('Kesalahan Server: ' + err.message);
            }
        }

        function showReceipt(data) {
            let itemsHtml = '';
            data.items.forEach(it => {
                itemsHtml += `
                    <div style="display: flex; justify-content: space-between;">
                        <span>${it.nama} x${it.qty}</span>
                        <span>Rp ${it.subtotal.toLocaleString('id-ID')}</span>
                    </div>
                `;
            });

            document.getElementById('receiptContent').innerHTML = `
                <div style="text-align: center; margin-bottom: 12px;">
                    <div style="font-weight: 800; font-size: 14px;">KOPERASI LAPAS UANGPAS</div>
                    <div>${uptSetting.upt_name}</div>
                    <div style="font-size: 10px; color: #64748b;">${data.created_at}</div>
                </div>
                <div style="border-top: 1px dashed #cbd5e1; border-bottom: 1px dashed #cbd5e1; padding: 8px 0; margin-bottom: 8px;">
                    <div>No. Invoice : ${data.invoice_no}</div>
                    <div>Warga Binaan : ${data.inmate_name} (${data.inmate_reg})</div>
                </div>
                <div style="margin-bottom: 8px;">
                    ${itemsHtml}
                </div>
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 14px;">
                        <span>TOTAL :</span>
                        <span>Rp ${data.total.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px;">
                        <span>Saldo Sebelum :</span>
                        <span>Rp ${data.balance_before.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #10b981; font-weight: 700;">
                        <span>Sisa Saldo WBP :</span>
                        <span>Rp ${data.balance_after.toLocaleString('id-ID')}</span>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 14px; font-size: 11px; color: #64748b;">
                    *** TERIMA KASIH ***<br>
                    Pembayaran Sah via Biometrik Sidik Jari
                </div>
            `;

            openModal('receiptModal');
        }

        function printReceipt() {
            window.print();
        }

        // 6. Manual Inmate Selection
        function openManualInmateModal() {
            openModal('manualInmateModal');
        }

        function selectInmateForCheckout(inmate) {
            closeModal('manualInmateModal');
            onInmateIdentified(inmate, 'manual_selection');
        }

        // Modal Helpers
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'biometricModal' && scanAbortController) {
                scanAbortController.abort();
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        function openSyncModal() {
            window.open('{{ route("admin.sync") }}', '_blank');
        }
    </script>
</body>
</html>

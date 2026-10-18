<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>UangPas Mobile POS - Kasir Keliling Blok</title>
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --secondary: #10b981;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --surface: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: var(--bg);
            color: var(--text-dark);
            padding-bottom: 80px;
            user-select: none;
        }

        /* Top Header */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--dark);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .brand-title h1 {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .brand-title span {
            font-size: 10.5px;
            color: #94a3b8;
            display: block;
        }

        .header-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34d399;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .dot-live {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
        }

        /* Search Bar */
        .search-container {
            padding: 12px 16px 8px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s;
        }

        .search-box input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            font-size: 15px;
            color: #94a3b8;
        }

        /* Category Chips Horizontal Scroll */
        .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 10px 16px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            scrollbar-width: none;
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .chip {
            white-space: nowrap;
            padding: 8px 14px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chip.active {
            background: #0284c7;
            color: #fff;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
        }

        /* Products Grid */
        .products-container {
            padding: 14px 16px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        @media (min-width: 600px) {
            .products-container {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
        }

        @media (min-width: 900px) {
            .products-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.15s, box-shadow 0.15s;
            cursor: pointer;
        }

        .product-card:active {
            transform: scale(0.97);
        }

        .product-badge-cat {
            font-size: 10px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .product-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 8px;
            min-height: 36px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
        }

        .btn-add {
            width: 100%;
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 9px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
        }

        .btn-add:active {
            background: #0369a1;
        }

        /* Floating Bottom Cart Bar */
        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #fff;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
            z-index: 90;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cart-info {
            display: flex;
            flex-direction: column;
        }

        .cart-items-count {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
        }

        .cart-total-price {
            font-size: 18px;
            font-weight: 800;
            color: #38bdf8;
        }

        .cart-action-btn {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
        }

        /* Cart Bottom Sheet Modal */
        .sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: none;
            align-items: flex-end;
            justify-content: center;
        }

        .sheet-content {
            background: #fff;
            width: 100%;
            max-width: 600px;
            border-radius: 20px 20px 0 0;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.25s ease-out;
            padding: 20px 20px 28px;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .sheet-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 14px;
        }

        .sheet-header h3 {
            font-size: 17px;
            font-weight: 800;
        }

        .cart-list {
            overflow-y: auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 16px;
            padding-right: 4px;
        }

        .cart-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #fff;
            font-weight: 800;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .qty-num {
            font-weight: 800;
            font-size: 14px;
            min-width: 20px;
            text-align: center;
        }

        /* WBP Checkout Modal */
        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 92%;
            max-width: 480px;
            padding: 24px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            outline: none;
        }

        .wbp-card-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 16px;
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-brand">
            <div class="brand-icon">👆</div>
            <div class="brand-title">
                <h1>UangPas Mobile POS</h1>
                <span>Petugas Keliling Blok Lapas</span>
            </div>
        </div>

        <div class="header-badge">
            <span class="dot-live"></span>
            <span>LAN Online</span>
        </div>
    </header>

    <!-- Search Box -->
    <div class="search-container">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Scan barcode / cari nama barang..." oninput="filterProducts()">
        </div>
    </div>

    <!-- Category Chips -->
    <div class="category-scroll">
        <div class="chip active" onclick="selectCategory('', this)">Semua Barang</div>
        @foreach($categories as $cat)
            <div class="chip" onclick="selectCategory('{{ $cat }}', this)">{{ $cat }}</div>
        @endforeach
    </div>

    <!-- Products Grid -->
    <div class="products-container" id="productsGrid">
        @foreach($products as $prod)
        <div class="product-card" data-cat="{{ $prod->kategori }}" data-name="{{ strtolower($prod->nama_barang) }}" data-barcode="{{ $prod->barcode }}" onclick="addToCart({{ $prod->id }}, '{{ addslashes($prod->nama_barang) }}', {{ $prod->harga }}, {{ $prod->stok }}, '{{ $prod->satuan }}')">
            <div>
                <div class="product-badge-cat">{{ $prod->kategori }}</div>
                <div class="product-name">{{ $prod->nama_barang }}</div>
            </div>
            <div>
                <div class="product-meta">
                    <span>Sisa: <strong>{{ $prod->stok }} {{ $prod->satuan }}</strong></span>
                    <span>📍 {{ $prod->lokasi_rak ?: 'Rak' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                    <div class="product-price">Rp {{ number_format($prod->harga, 0, ',', '.') }}</div>
                    <button type="button" class="btn-add" style="width: auto; padding: 6px 12px;">+ Tambah</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Floating Cart Bar (Bottom) -->
    <div class="cart-bar" id="cartBar" onclick="openCartSheet()">
        <div class="cart-info">
            <span class="cart-items-count" id="cartSummaryCount">0 Barang Dipilih</span>
            <span class="cart-total-price" id="cartSummaryTotal">Rp 0</span>
        </div>
        <button class="cart-action-btn" type="button">
            <span>Checkout</span> ➔
        </button>
    </div>

    <!-- Bottom Sheet Cart Modal -->
    <div class="sheet-overlay" id="cartSheet">
        <div class="sheet-content">
            <div class="sheet-header">
                <div>
                    <h3>🛒 Keranjang Belanja</h3>
                    <span style="font-size: 12px; color: var(--text-muted);" id="sheetItemCount">0 barang</span>
                </div>
                <button onclick="closeCartSheet()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b;">✕</button>
            </div>

            <div class="cart-list" id="cartList">
                <!-- Dynamic Items -->
            </div>

            <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-weight: 700; font-size: 14px;">Total Tagihan:</span>
                <span style="font-size: 20px; font-weight: 800; color: #0284c7;" id="sheetTotal">Rp 0</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px;">
                <button type="button" onclick="clearCart()" style="background: #fee2e2; color: #dc2626; border: none; padding: 14px; border-radius: 10px; font-weight: 700; cursor: pointer;">
                    🗑️ Batal
                </button>
                <button type="button" onclick="openWbpModal()" style="background: #10b981; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 14px; cursor: pointer;">
                    👤 Pilih WBP & Bayar ➔
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Pilih WBP & Bayar -->
    <div class="sheet-overlay" id="wbpModal" style="align-items: center;">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                <h3 style="font-size: 16px; font-weight: 800;">👤 Otorisasi Warga Binaan (WBP)</h3>
                <button onclick="closeWbpModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
            </div>

            <div class="form-group">
                <label>Pilih Warga Binaan Pemasyarakatan *</label>
                <select id="wbpSelect" onchange="onWbpSelected()">
                    <option value="">-- Pilih WBP --</option>
                    @foreach($inmates as $wbp)
                        <option value="{{ $wbp->id }}" data-saldo="{{ $wbp->saldo }}" data-limit="{{ $wbp->remaining_daily_limit }}" data-kamar="{{ $wbp->blok_kamar }}" data-reg="{{ $wbp->no_registrasi }}" data-status="{{ $wbp->status }}">
                            {{ $wbp->no_registrasi }} - {{ $wbp->nama }} ({{ $wbp->blok_kamar }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Card Info Saldo WBP -->
            <div class="wbp-card-info" id="wbpInfoBox">
                <div style="font-size: 11px; text-transform: uppercase; color: #166534; font-weight: 700;">Dompet Digital WBP:</div>
                <div style="font-size: 22px; font-weight: 800; color: #15803d; margin: 4px 0;" id="wbpSaldoText">Rp 0</div>
                <div style="font-size: 11.5px; color: #166534;">
                    Sisa Kuota Belanja Hari Ini: <strong id="wbpLimitText">Rp 0</strong>
                </div>
                <div id="wbpSanctionWarning" style="color: #dc2626; font-weight: 800; font-size: 12px; margin-top: 6px; display: none;">
                    ⛔ STATUS WBP: SANKSI REGISTER F (ISOLASI) - TRANSAKSI DITOLAK!
                </div>
            </div>

            <div class="form-group">
                <label>PIN Petugas Otorisasi (Opsional)</label>
                <input type="password" id="officerPin" placeholder="Masukkan PIN Petugas Lapas">
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeWbpModal()" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: #f8fafc; font-weight: 700;">Batal</button>
                <button type="button" id="btnSubmitPayment" onclick="submitMobileCheckout()" style="flex: 2; padding: 12px; border-radius: 8px; border: none; background: #10b981; color: #fff; font-weight: 800; cursor: pointer;">
                    ⚡ Selesaikan Transaksi
                </button>
            </div>
        </div>
    </div>

    <!-- Success Struk Modal -->
    <div class="sheet-overlay" id="successModal" style="align-items: center;">
        <div class="modal-box" style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 8px;">✅</div>
            <h3 style="font-size: 18px; font-weight: 800; color: #10b981;">Pembayaran Berhasil!</h3>
            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;" id="succInvText">No. Struk: -</p>

            <div style="background: #f8fafc; border-radius: 10px; padding: 16px; text-align: left; font-size: 13px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Warga Binaan:</span>
                    <strong id="succWbpName">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Total Belanja:</span>
                    <strong style="color: #0284c7;" id="succTotal">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Sisa Saldo WBP:</span>
                    <strong style="color: #10b981;" id="succBalanceAfter">-</strong>
                </div>
            </div>

            <button type="button" onclick="closeSuccessModal()" style="width: 100%; padding: 14px; border-radius: 10px; background: #0f172a; color: #fff; border: none; font-weight: 800; font-size: 14px; cursor: pointer;">
                Selesai & Transaksi Baru ➔
            </button>
        </div>
    </div>

    <script>
        let cart = {};
        let selectedCategory = '';

        function addToCart(id, name, price, stock, unit) {
            if (stock <= 0) {
                alert('Stok barang ' + name + ' habis!');
                return;
            }

            if (cart[id]) {
                if (cart[id].qty >= stock) {
                    alert('Tidak dapat menambah lebih dari stok tersedia (' + stock + ' ' + unit + ').');
                    return;
                }
                cart[id].qty++;
            } else {
                cart[id] = { id, name, price, stock, unit, qty: 1 };
            }

            updateCartUI();
        }

        function changeQty(id, delta) {
            if (!cart[id]) return;
            cart[id].qty += delta;
            if (cart[id].qty <= 0) {
                delete cart[id];
            } else if (cart[id].qty > cart[id].stock) {
                cart[id].qty = cart[id].stock;
                alert('Maksimal stok tercapai.');
            }
            updateCartUI();
            renderCartList();
        }

        function clearCart() {
            cart = {};
            updateCartUI();
            closeCartSheet();
        }

        function updateCartUI() {
            let totalItems = 0;
            let totalPrice = 0;

            for (let id in cart) {
                totalItems += cart[id].qty;
                totalPrice += (cart[id].qty * cart[id].price);
            }

            document.getElementById('cartSummaryCount').innerText = totalItems + ' Barang Dipilih';
            document.getElementById('cartSummaryTotal').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            document.getElementById('sheetItemCount').innerText = totalItems + ' barang di keranjang';
            document.getElementById('sheetTotal').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');

            const bar = document.getElementById('cartBar');
            if (totalItems > 0) {
                bar.style.display = 'flex';
            } else {
                bar.style.display = 'none';
                closeCartSheet();
            }
        }

        function renderCartList() {
            const listEl = document.getElementById('cartList');
            listEl.innerHTML = '';

            let hasItems = false;
            for (let id in cart) {
                hasItems = true;
                const it = cart[id];
                const subtotal = it.qty * it.price;

                const row = document.createElement('div');
                row.className = 'cart-item-row';
                row.innerHTML = `
                    <div>
                        <strong style="font-size: 13.5px; color: #0f172a;">${it.name}</strong>
                        <div style="font-size: 11.5px; color: #64748b;">Rp ${it.price.toLocaleString('id-ID')} / ${it.unit}</div>
                    </div>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn" onclick="changeQty(${it.id}, -1)">-</button>
                        <span class="qty-num">${it.qty}</span>
                        <button type="button" class="qty-btn" onclick="changeQty(${it.id}, 1)">+</button>
                    </div>
                `;
                listEl.appendChild(row);
            }

            if (!hasItems) {
                listEl.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 24px;">Keranjang kosong.</div>';
            }
        }

        function openCartSheet() {
            renderCartList();
            document.getElementById('cartSheet').style.display = 'flex';
        }

        function closeCartSheet() {
            document.getElementById('cartSheet').style.display = 'none';
        }

        function openWbpModal() {
            let totalItems = 0;
            for (let id in cart) totalItems += cart[id].qty;
            if (totalItems === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            closeCartSheet();
            document.getElementById('wbpModal').style.display = 'flex';
        }

        function closeWbpModal() {
            document.getElementById('wbpModal').style.display = 'none';
        }

        function onWbpSelected() {
            const sel = document.getElementById('wbpSelect');
            const opt = sel.options[sel.selectedIndex];
            const box = document.getElementById('wbpInfoBox');
            const warn = document.getElementById('wbpSanctionWarning');
            const btnSubmit = document.getElementById('btnSubmitPayment');

            if (sel.value) {
                const saldo = parseFloat(opt.getAttribute('data-saldo')) || 0;
                const limit = parseFloat(opt.getAttribute('data-limit')) || 0;
                const status = opt.getAttribute('data-status') || 'aktif';

                document.getElementById('wbpSaldoText').innerText = 'Rp ' + saldo.toLocaleString('id-ID');
                document.getElementById('wbpLimitText').innerText = 'Rp ' + limit.toLocaleString('id-ID');
                box.style.display = 'block';

                if (status === 'isolasi') {
                    warn.style.display = 'block';
                    btnSubmit.disabled = true;
                    btnSubmit.style.background = '#cbd5e1';
                } else {
                    warn.style.display = 'none';
                    btnSubmit.disabled = false;
                    btnSubmit.style.background = '#10b981';
                }
            } else {
                box.style.display = 'none';
            }
        }

        function filterProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const barcode = card.getAttribute('data-barcode');
                const cat = card.getAttribute('data-cat');

                const matchCat = (selectedCategory === '' || cat === selectedCategory);
                const matchSearch = (name.indexOf(query) >= 0 || barcode.indexOf(query) >= 0);

                if (matchCat && matchSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function selectCategory(cat, el) {
            selectedCategory = cat;
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            filterProducts();
        }

        async function submitMobileCheckout() {
            const sel = document.getElementById('wbpSelect');
            if (!sel.value) {
                alert('Pilih Warga Binaan terlebih dahulu.');
                return;
            }

            const items = [];
            for (let id in cart) {
                items.push({
                    product_id: parseInt(id),
                    qty: cart[id].qty
                });
            }

            const btnSubmit = document.getElementById('btnSubmitPayment');
            btnSubmit.disabled = true;
            btnSubmit.innerText = '⏳ Memproses Pembayaran...';

            try {
                const response = await fetch('/mobile-pos/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        inmate_id: sel.value,
                        items: items,
                        officer_pin: document.getElementById('officerPin').value
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    closeWbpModal();
                    cart = {};
                    updateCartUI();

                    document.getElementById('succInvText').innerText = 'No. Struk: ' + result.data.invoice_no;
                    document.getElementById('succWbpName').innerText = result.data.wbp_nama;
                    document.getElementById('succTotal').innerText = 'Rp ' + result.data.total_amount.toLocaleString('id-ID');
                    document.getElementById('succBalanceAfter').innerText = 'Rp ' + result.data.balance_after.toLocaleString('id-ID');

                    document.getElementById('successModal').style.display = 'flex';
                } else {
                    alert('Gagal Transaksi: ' + (result.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Koneksi Error: ' + err.message);
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerText = '⚡ Selesaikan Transaksi';
            }
        }

        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            location.reload();
        }
    </script>
</body>
</html>

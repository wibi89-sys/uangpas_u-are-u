<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - UangPas Dompet Digital Lapas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-light: rgba(2, 132, 199, 0.12);
            --sidebar-bg: #0f172a;
            --sidebar-border: #1e293b;
            --bg: #f8fafc;
            --surface: #ffffff;
            --card-border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --success-light: #ecfdf5;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        aside {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid var(--sidebar-border);
            position: sticky;
            top: 0;
            height: 100vh;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
        }

        body.sidebar-collapsed aside {
            width: 76px;
        }

        body.sidebar-collapsed .brand-text,
        body.sidebar-collapsed .nav-label,
        body.sidebar-collapsed .nav-item span:not(.nav-icon),
        body.sidebar-collapsed .sidebar-footer p,
        body.sidebar-collapsed .sidebar-footer .status-pill span:not(.dot-green) {
            display: none;
        }

        body.sidebar-collapsed .sidebar-brand {
            padding: 20px 14px;
            justify-content: center;
        }

        body.sidebar-collapsed .nav-item {
            justify-content: center;
            padding: 12px;
        }

        body.sidebar-collapsed .nav-icon {
            font-size: 20px;
            margin: 0;
        }

        body.sidebar-collapsed .sidebar-footer {
            padding: 16px 12px;
            display: flex;
            justify-content: center;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }

        .brand-text h2 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .brand-text span {
            font-size: 11px;
            color: #94a3b8;
            display: block;
        }

        .nav-menu {
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 10px 12px 6px;
            font-weight: 700;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active {
            color: #fff;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .nav-icon {
            font-size: 18px;
            width: 22px;
            display: flex;
            justify-content: center;
        }

        .sidebar-footer {
            padding: 18px 20px;
            border-top: 1px solid var(--sidebar-border);
            font-size: 12px;
            color: #64748b;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }

        .dot-green {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
        }

        /* Main Content */
        main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .btn-toggle-sidebar {
            background: #f1f5f9;
            border: 1px solid var(--card-border);
            border-radius: 8px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-dark);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-toggle-sidebar:hover {
            background: #e2e8f0;
            color: var(--primary);
        }

        /* Top Header */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--card-border);
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .page-title h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .page-title p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .badge-live {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Body container */
        .content-wrapper {
            padding: 24px 28px;
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
        }

        @media (max-width: 1366px) {
            header { padding: 14px 20px; }
            .content-wrapper { padding: 18px 16px; }
        }

        /* Alert notifications */
        .alert-success {
            background: var(--success-light);
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Cards & Grids */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 28px;
        }

        @media (max-width: 1100px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            aside { width: 100%; height: auto; position: static; }
            .grid-4 { grid-template-columns: 1fr; }
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .stat-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-dark);
            margin-top: 6px;
            letter-spacing: -0.5px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 820px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 12px 14px;
            font-weight: 700;
            border-bottom: 2px solid var(--card-border);
            text-transform: uppercase;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--card-border);
            color: var(--text-dark);
        }

        .btn-outline:hover {
            background: #f1f5f9;
        }

        /* Badges */
        .pill {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .pill-success { background: var(--success-light); color: var(--success); }
        .pill-primary { background: var(--primary-light); color: var(--primary); }
        .pill-warning { background: var(--warning-light); color: var(--warning); }
        .pill-danger { background: var(--danger-light); color: var(--danger); }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 13.5px;
            background: #fff;
            color: #1e293b;
            outline: none;
            transition: border 0.2s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside>
        <div class="sidebar-brand">
            <div class="brand-logo">👆</div>
            <div class="brand-text">
                <h2>UangPas Lapas</h2>
                <span>Dompet Digital Biometrik WBP</span>
            </div>
        </div>

        <nav class="nav-menu">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span>Dashboard Ringkasan</span>
            </a>
            <a href="{{ route('admin.inmates') }}" class="nav-item {{ request()->routeIs('admin.inmates*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span>
                <span>Data Warga Binaan</span>
            </a>
            <a href="{{ route('admin.topup') }}" class="nav-item {{ request()->routeIs('admin.topup*') ? 'active' : '' }}">
                <span class="nav-icon">💳</span>
                <span>Loket Top-Up & Bank VA</span>
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <span class="nav-icon">🛡️</span>
                <span>Petugas & Hak Akses</span>
            </a>

            <div class="nav-label">Koperasi & Inventori</div>
            <a href="{{ route('admin.products') }}" class="nav-item {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <span class="nav-icon">📦</span>
                <span>Katalog & Kartu Stok</span>
            </a>
            <a href="{{ route('admin.inventory.restock') }}" class="nav-item {{ request()->routeIs('admin.inventory.restock*') ? 'active' : '' }}">
                <span class="nav-icon">📥</span>
                <span>Restock / Barang Masuk</span>
            </a>
            <a href="{{ route('admin.inventory.opname') }}" class="nav-item {{ request()->routeIs('admin.inventory.opname*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span>
                <span>Stock Opname (Audit)</span>
            </a>
            <a href="{{ route('admin.inventory.suppliers') }}" class="nav-item {{ request()->routeIs('admin.inventory.suppliers*') ? 'active' : '' }}">
                <span class="nav-icon">🏭</span>
                <span>Supplier Rekanan</span>
            </a>
            <div class="nav-label">Keuangan & Audit Koperasi</div>
            <a href="{{ route('admin.reports.profit_loss') }}" class="nav-item {{ request()->routeIs('admin.reports.profit_loss*') ? 'active' : '' }}">
                <span class="nav-icon">📈</span>
                <span>Laba Rugi & Gross Margin</span>
            </a>
            <a href="{{ route('admin.reports.cashflow') }}" class="nav-item {{ request()->routeIs('admin.reports.cashflow*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span>
                <span>Arus Kas & Titipan WBP</span>
            </a>
            <a href="{{ route('admin.transactions') }}" class="nav-item {{ request()->routeIs('admin.transactions*') ? 'active' : '' }}">
                <span class="nav-icon">📑</span>
                <span>Buku Transaksi Kasir</span>
            </a>
            <a href="{{ route('admin.settings.receipt') }}" class="nav-item {{ request()->routeIs('admin.settings.receipt*') ? 'active' : '' }}">
                <span class="nav-icon">🧾</span>
                <span>Pengaturan Struk Kasir</span>
            </a>
            <div style="padding: 10px 14px; margin: 8px 12px; background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 8px; font-size: 11px; color: #0369a1; line-height: 1.4;">
                <div style="font-weight: 700; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                    <span>🖥️</span> Terminal Kasir Lapas
                </div>
                Transaksi & sensor sidik jari aktif di <b>UangPasDesktop.exe</b>
            </div>
            <a href="{{ route('mobile.pos') }}" target="_blank" class="nav-item" style="color: #0369a1; background: #e0f2fe; margin: 4px 12px; border-radius: 8px;">
                <span class="nav-icon">📱</span>
                <span style="font-weight: 700;">Mobile POS (Tablet/HP) ↗</span>
            </a>

            <div class="nav-label">Jaringan & Sinkronisasi</div>
            <a href="{{ route('admin.sync') }}" class="nav-item {{ request()->routeIs('admin.sync*') ? 'active' : '' }}">
                <span class="nav-icon">🔄</span>
                <span>Sinkron & Mutasi Antar-UPT</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div style="margin-bottom: 8px;">
                <span class="status-pill">
                    <span class="dot-green"></span> Sistem Aktif
                </span>
            </div>
            <p>Database: <strong>Hosting Terpusat</strong></p>
            <p style="font-size: 11px; margin-top: 4px;">Biometrik POS Ready</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main>
        @php
            $activeUser = \App\Models\User::find(session('simulated_user_id', 1)) ?? \App\Models\User::first();
            $allUsers = \App\Models\User::where('is_active', true)->get();
        @endphp
        <header>
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" id="btnToggleSidebar" class="btn-toggle-sidebar" title="Ciutkan / Lebarkan Menu Sidebar">
                    <span>☰</span>
                </button>
                <div class="page-title">
                    <h1>@yield('page_title', 'Dashboard')</h1>
                    <p>@yield('page_desc', 'Sistem Pembayaran Bebas Uang Tunai (Zero Cash) Lapas')</p>
                </div>
            </div>
            <div class="header-actions">
                <!-- User Profile & Quick Role Switcher -->
                <form action="{{ route('admin.users.switch_role') }}" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    @csrf
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Peran:</span>
                    <select name="user_id" onchange="this.form.submit()" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; font-weight: 700; background: #f8fafc; border: 1px solid #cbd5e1; cursor: pointer; color: #0f172a; max-width: 260px;">
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" {{ $activeUser && $activeUser->id == $u->id ? 'selected' : '' }}>
                                [{{ strtoupper(str_replace('_', ' ', $u->role)) }}] {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="badge-live">
                    <span>🏛️</span> {{ $activeUser->upt_code ?? 'LPS-01' }}
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert-success">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnToggle = document.getElementById('btnToggleSidebar');
            if (localStorage.getItem('uangpas_sidebar_collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            if (btnToggle) {
                btnToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('uangpas_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
                });
            }
        });
    </script>

    @yield('scripts')
</body>
</html>

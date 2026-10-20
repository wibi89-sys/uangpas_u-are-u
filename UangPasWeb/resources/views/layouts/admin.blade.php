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

        /* Backdrop for Mobile Drawer */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        @media (max-width: 1100px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 1024px) {
            aside {
                position: fixed !important;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px !important;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 999;
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.3);
            }
            body.mobile-sidebar-open aside {
                transform: translateX(0);
            }
            body.mobile-sidebar-open .sidebar-backdrop {
                display: block;
                opacity: 1;
            }
            body.mobile-sidebar-open {
                overflow: hidden;
            }
            header {
                padding: 12px 16px;
            }
            .content-wrapper {
                padding: 16px 14px;
            }
        }

        @media (max-width: 640px) {
            .grid-4 { grid-template-columns: 1fr; }
            header {
                flex-wrap: wrap;
                gap: 12px;
            }
            .header-actions {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }
            .page-title h1 {
                font-size: 17px;
            }
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
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside id="mainSidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">👆</div>
            <div class="brand-text">
                <h2>UangPas Lapas</h2>
                <span>Dompet Digital Biometrik WBP</span>
            </div>
        </div>

        @php
            $activeUser = auth()->user() ?? \App\Models\User::first();
            $appSetting = \App\Models\UptSetting::current();
        @endphp

        <nav class="nav-menu">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span>Dashboard Ringkasan</span>
            </a>

            @if($activeUser->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi', 'petugas_loket', 'auditor']))
            <a href="{{ route('admin.register_d.index') }}" class="nav-item {{ request()->routeIs('admin.register_d*') ? 'active' : '' }}">
                <span class="nav-icon">📖</span>
                <span>Buku Register D</span>
            </a>
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'admin_koperasi', 'kasir']))
            <a href="{{ route('admin.edc.index') }}" class="nav-item {{ request()->routeIs('admin.edc*') ? 'active' : '' }}">
                <span class="nav-icon">⚡</span>
                <span>Terminal EDC Cepat</span>
            </a>
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi', 'petugas_loket']))
            <a href="{{ route('admin.inmates') }}" class="nav-item {{ request()->routeIs('admin.inmates*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span>
                <span>Data Warga Binaan</span>
            </a>
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'admin_koperasi', 'petugas_loket', 'kasir']))
            <a href="{{ route('admin.topup') }}" class="nav-item {{ request()->routeIs('admin.topup*') ? 'active' : '' }}">
                <span class="nav-icon">💳</span>
                <span>Loket Setor Titipan & VA</span>
            </a>
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi']) || $activeUser->hasPermission('users_manage'))
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <span class="nav-icon">🛡️</span>
                <span>Petugas & Hak Akses</span>
            </a>
            @endif

            @if(($appSetting->app_mode ?? 'wallet_only') !== 'wallet_only')
                @if($activeUser->hasRole(['kanwil_admin', 'admin_koperasi']))
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
                @endif
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi', 'auditor']))
            <div class="nav-label">Keuangan & Audit Dana</div>
            @if(($appSetting->app_mode ?? 'wallet_only') !== 'wallet_only')
            <a href="{{ route('admin.reports.profit_loss') }}" class="nav-item {{ request()->routeIs('admin.reports.profit_loss*') ? 'active' : '' }}">
                <span class="nav-icon">📈</span>
                <span>Laba Rugi & Gross Margin</span>
            </a>
            @endif
            <a href="{{ route('admin.reports.cashflow') }}" class="nav-item {{ request()->routeIs('admin.reports.cashflow*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span>
                <span>Arus Kas & Titipan WBP</span>
            </a>
            <a href="{{ route('admin.transactions') }}" class="nav-item {{ request()->routeIs('admin.transactions*') ? 'active' : '' }}">
                <span class="nav-icon">📑</span>
                <span>Buku Transaksi Kasir</span>
            </a>
            @endif

            @if($activeUser->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi']) || $activeUser->hasPermission('settings_app'))
            <div class="nav-label">Pengaturan & Perangkat</div>
            <a href="{{ route('admin.settings.general') }}" class="nav-item {{ request()->routeIs('admin.settings.general*') || request()->routeIs('admin.settings') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span>
                <span>Pengaturan Umum Aplikasi</span>
            </a>
            <a href="{{ route('admin.settings.receipt') }}" class="nav-item {{ request()->routeIs('admin.settings.receipt*') ? 'active' : '' }}">
                <span class="nav-icon">🧾</span>
                <span>Pengaturan Struk Kasir</span>
            </a>
            <a href="{{ route('admin.settings.devices') }}" class="nav-item {{ request()->routeIs('admin.settings.devices*') ? 'active' : '' }}">
                <span class="nav-icon">📡</span>
                <span>Perangkat Biometrik IP</span>
            </a>
            <a href="{{ route('admin.sync') }}" class="nav-item {{ request()->routeIs('admin.sync*') ? 'active' : '' }}">
                <span class="nav-icon">🔄</span>
                <span>Sinkron & Mutasi Antar-UPT</span>
            </a>
            @endif

            <div class="nav-label">Terminal POS & Kiosk</div>
            @if($activeUser->hasRole(['kanwil_admin', 'admin_koperasi', 'kasir']))
            <a href="{{ route('mobile.pos') }}" target="_blank" class="nav-item" style="color: #0369a1; background: #e0f2fe; margin: 4px 12px; border-radius: 8px;">
                <span class="nav-icon">📱</span>
                <span style="font-weight: 700;">Mobile POS (Tablet/HP) ↗</span>
            </a>
            @endif

            <a href="{{ route('kiosk.index') }}" target="_blank" class="nav-item" style="color: #059669; background: #ecfdf5; margin: 4px 12px; border-radius: 8px;">
                <span class="nav-icon">🔍</span>
                <span style="font-weight: 700;">Kiosk Cek Saldo (TV/Tablet) ↗</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div style="margin-bottom: 6px;">
                <span class="status-pill">
                    <span class="dot-green"></span> Sistem Aktif
                </span>
            </div>
            <p>UPT: <strong>{{ $activeUser->upt_code ?? 'LPS-01' }}</strong></p>
            <p style="font-size: 11px; margin-top: 2px; color: #94a3b8;">Biometrik POS Ready</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main>
        <header>
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" id="btnToggleSidebar" class="btn-toggle-sidebar" title="Ciutkan / Buka Menu Sidebar">
                    <span>☰</span>
                </button>
                <div class="page-title">
                    <h1>@yield('page_title', 'Dashboard')</h1>
                    <p>@yield('page_desc', 'Sistem Pembayaran Bebas Uang Tunai (Zero Cash) Lapas')</p>
                </div>
            </div>
            <div class="header-actions">
                @if($activeUser->isKanwil())
                @php $allUsers = \App\Models\User::where('is_active', true)->get(); @endphp
                <!-- Superadmin Role Switcher -->
                <form action="{{ route('admin.users.switch_role') }}" method="POST" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                    @csrf
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Simulasi:</span>
                    <select name="user_id" onchange="this.form.submit()" style="padding: 5px 8px; font-size: 11.5px; border-radius: 6px; font-weight: 700; background: #f8fafc; border: 1px solid #cbd5e1; cursor: pointer; color: #0f172a; max-width: 200px;">
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" {{ $activeUser && $activeUser->id == $u->id ? 'selected' : '' }}>
                                [{{ strtoupper(str_replace('_', ' ', $u->role)) }}] {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                <!-- User Profile Badge -->
                <div style="display: flex; align-items: center; gap: 10px; padding: 4px 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                        {{ strtoupper(substr($activeUser->name, 0, 1)) }}
                    </div>
                    <div style="text-align: left; line-height: 1.2;">
                        <div style="font-size: 12.5px; font-weight: 700; color: #0f172a;">{{ $activeUser->name }}</div>
                        <div style="font-size: 11px; color: #64748b; font-weight: 600;">{{ $activeUser->role_label }}</div>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="display: flex; align-items: center; gap: 6px; padding: 7px 12px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.18)'" onmouseout="this.style.background='rgba(239,68,68,0.08)'" title="Keluar dari sistem">
                        <span>🚪</span> Keluar
                    </button>
                </form>
            </div>
        </header>

        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert-success" style="padding: 12px 16px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error" style="padding: 12px 16px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnToggle = document.getElementById('btnToggleSidebar');
            
            // Restore desktop collapse preference
            if (window.innerWidth > 1024 && localStorage.getItem('uangpas_sidebar_collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            if (btnToggle) {
                btnToggle.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        // Mobile/Tablet: toggle drawer
                        document.body.classList.toggle('mobile-sidebar-open');
                    } else {
                        // Desktop: toggle compact
                        document.body.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('uangpas_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
                    }
                });
            }
        });

        function closeMobileSidebar() {
            document.body.classList.remove('mobile-sidebar-open');
        }
    </script>

    @yield('scripts')
</body>
</html>

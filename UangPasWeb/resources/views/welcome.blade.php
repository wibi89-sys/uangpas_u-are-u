<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'UangPas Lapas') }} - Portal Layanan Terpadu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --bg: #090d16;
            --card-bg: rgba(15, 23, 42, 0.75);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header {
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .brand-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-badge {
            font-size: 10px;
            font-weight: 700;
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
            padding: 2px 8px;
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.3);
            text-transform: uppercase;
        }

        main {
            max-width: 1080px;
            margin: 0 auto;
            padding: 48px 24px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 50px;
            color: #34d399;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-pill .dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
        }

        h1 {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            font-size: 16px;
            color: var(--text-muted);
            max-width: 640px;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .grid-portals {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            width: 100%;
            text-align: left;
        }

        .portal-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .portal-card:hover {
            transform: translateY(-4px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
        }

        .portal-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .portal-title {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .portal-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .portal-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #38bdf8;
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 20px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }
    </style>
</head>
<body>
    <header>
        <a href="{{ url('/') }}" class="brand">
            <div class="brand-icon">💳</div>
            <div>
                <span class="brand-name">{{ config('app.name', 'UangPas Lapas') }}</span>
                <span class="brand-badge">Sistem Terpadu</span>
            </div>
        </a>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 13px;">
                    Masuk Dashboard &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 13px;">
                    Login Petugas &rarr;
                </a>
            @endauth
        </div>
    </header>

    <main>
        <div class="hero-pill">
            <span class="dot"></span>
            <span>UangPas Ecosystem &bull; Siap Operasional</span>
        </div>

        <h1>Ekosistem Transaksi Non-Tunai &amp; Koperasi Pemasyarakatan</h1>
        <p class="subtitle">
            Platform terpadu pengelolaan saldo titipan Warga Binaan Pemasyarakatan (WBP), terminal kasir belanja biometrik sidik jari, dan pengawasan multi-UPT Kantor Wilayah.
        </p>

        <div class="grid-portals">
            <!-- Portal Admin -->
            <a href="{{ route('login') }}" class="portal-card">
                <div>
                    <div class="portal-icon" style="background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.3);">
                        🏛️
                    </div>
                    <div class="portal-title">Portal Manajemen Web</div>
                    <div class="portal-desc">
                        Dashboard manajemen WBP, penerimaan setoran keluarga di loket, mutasi, laporan laba rugi, dan pengelolaan hak akses.
                    </div>
                </div>
                <div class="portal-action">
                    <span>Masuk ke Portal</span>
                    <span>&rarr;</span>
                </div>
            </a>

            <!-- Terminal Web POS -->
            <a href="{{ route('pos.index') }}" class="portal-card">
                <div>
                    <div class="portal-icon" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);">
                        🛒
                    </div>
                    <div class="portal-title">Terminal Kasir POS Web</div>
                    <div class="portal-desc">
                        Kasir belanja koperasi layar penuh berbasis browser dengan integrasi pemindai barcode, biometrik, dan cetak struk thermal.
                    </div>
                </div>
                <div class="portal-action" style="color: #34d399;">
                    <span>Buka Web POS</span>
                    <span>&rarr;</span>
                </div>
            </a>

            <!-- Mobile POS -->
            <a href="{{ route('mobile.pos') }}" class="portal-card">
                <div>
                    <div class="portal-icon" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                        📱
                    </div>
                    <div class="portal-title">Mobile POS Tablet</div>
                    <div class="portal-desc">
                        Antarmuka kasir belanja yang dioptimalkan untuk perangkat layar sentuh smartphone atau tablet petugas keliling blok hunian.
                    </div>
                </div>
                <div class="portal-action" style="color: #fbbf24;">
                    <span>Buka Mobile POS</span>
                    <span>&rarr;</span>
                </div>
            </a>

            <!-- Kiosk Mandiri -->
            <a href="{{ route('kiosk.index') }}" class="portal-card">
                <div>
                    <div class="portal-icon" style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.3);">
                        🖥️
                    </div>
                    <div class="portal-title">Kiosk Cek Saldo WBP</div>
                    <div class="portal-desc">
                        Layar anjungan mandiri untuk Warga Binaan memeriksa riwayat mutasi dan sisa saldo titipan menggunakan kartu atau sidik jari.
                    </div>
                </div>
                <div class="portal-action" style="color: #c084fc;">
                    <span>Buka Layar Kiosk</span>
                    <span>&rarr;</span>
                </div>
            </a>
        </div>
    </main>

    <footer>
        <div>
            &copy; {{ date('Y') }} {{ config('app.name', 'UangPas Lapas') }} &bull; Sistem Transaksi Non-Tunai Pemasyarakatan
        </div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px;">
            v2.4.0-prod &bull; PHP {{ PHP_VERSION }} &bull; Laravel v{{ Illuminate\Foundation\Application::VERSION }}
        </div>
    </footer>
</body>
</html>

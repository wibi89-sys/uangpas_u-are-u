<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Kedaluwarsa | UangPas Lapas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --warning: #f59e0b;
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.85);
            --card-border: rgba(245, 158, 11, 0.2);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(2, 132, 199, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text-main);
        }
        .error-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 42px 36px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        .error-badge {
            display: inline-block;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fde047;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
            font-family: 'JetBrains Mono', monospace;
        }
        .error-code {
            font-size: 72px;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #fde047 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .error-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--warning-gradient);
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px -4px rgba(245, 158, 11, 0.4);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }
        .footer-note {
            margin-top: 24px;
            font-size: 11.5px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">KODE 419 • SESSION EXPIRED</div>
        <div class="error-code">419</div>
        <h1 class="error-title">Sesi Halaman Kedaluwarsa</h1>
        <p class="error-desc">
            Token keamanan halaman Anda telah kedaluwarsa demi melindungi data transaksi Lapas. Silakan muat ulang halaman atau login kembali.
        </p>
        <a href="{{ route('login') }}" class="btn-action">
            <span>🔄</span> Muat Ulang & Masuk Kembali
        </a>
        <div class="footer-note">
            Portal UangPas Lapas • Proteksi Token CSRF Keamanan Finansial
        </div>
    </div>
</body>
</html>

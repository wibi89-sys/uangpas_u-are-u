<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - Portal UangPas Lapas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-gradient: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            --accent: #10b981;
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.78);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.45) 0px, transparent 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text-main);
        }

        .login-container {
            width: 100%;
            max-width: 460px;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 38px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(2, 132, 199, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.25);
            color: #38bdf8;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }

        .brand-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #38bdf8;
            box-shadow: 0 0 8px #38bdf8;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.25) 0%, rgba(16, 185, 129, 0.2) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 14px;
            box-shadow: 0 12px 24px -6px rgba(2, 132, 199, 0.35);
        }

        .brand-title {
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 12.5px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.4;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            font-size: 16px;
            color: var(--text-muted);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 44px;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.28);
            background: rgba(15, 23, 42, 0.92);
        }

        .form-input::placeholder {
            color: #64748b;
            font-size: 13.5px;
        }

        .input-error {
            border-color: rgba(239, 68, 68, 0.6) !important;
        }

        .btn-toggle-pass {
            position: absolute;
            right: 10px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: color 0.15s ease;
        }

        .btn-toggle-pass:hover {
            color: #f8fafc;
        }

        .btn-toggle-pass svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #cbd5e1;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .help-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 12.5px;
            transition: color 0.15s;
        }

        .help-link:hover {
            color: #38bdf8;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 25px -5px rgba(2, 132, 199, 0.5);
            filter: brightness(1.06);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .portal-links {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            font-size: 12px;
        }

        .portal-link {
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }

        .portal-link:hover {
            color: #38bdf8;
        }

        .security-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.5;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 4px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-badge">
                <span class="dot"></span>
                <span>Kemenkumham &bull; Ditjen Pemasyarakatan</span>
            </div>
            <div>
                <div class="brand-logo">🏛️</div>
            </div>
            <h1 class="brand-title">PORTAL UANGPAS LAPAS</h1>
            <p class="brand-subtitle">
                Sistem Dompet Digital Non-Tunai &amp; Biometrik Warga Binaan<br>
                Koperasi Pengayoman UPT Pemasyarakatan
            </p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->has('login_id'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>{{ $errors->first('login_id') }}</div>
            </div>
        @endif

        @if($errors->has('password') && !$errors->has('login_id'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>{{ $errors->first('password') }}</div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="formLogin" onsubmit="handleSubmit(event)">
            @csrf

            <div class="form-group">
                <label class="form-label" for="login_id">NIP Dinas atau Email Petugas</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input 
                        type="text" 
                        id="login_id" 
                        name="login_id" 
                        class="form-input @if($errors->has('login_id')) input-error @endif" 
                        placeholder="Masukkan NIP (18 digit) atau email dinas" 
                        value="{{ old('login_id') }}" 
                        required 
                        autocomplete="username"
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi (Password)</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input @if($errors->has('password')) input-error @endif" 
                        placeholder="Masukkan kata sandi akun Anda" 
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility()" id="btnTogglePass" title="Lihat Password" aria-label="Lihat Password">
                        <svg id="eyeIcon" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat sesi di perangkat ini</span>
                </label>
                <a href="javascript:void(0)" onclick="alert('Untuk perubahan kata sandi atau reset akun, silakan hubungi Administrator SIMKOP / IT UPT Pemasyarakatan setempat.')" class="help-link">
                    Bantuan Akun?
                </a>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <span id="btnSubmitText">Masuk ke Portal Petugas</span>
                <span id="btnSubmitArrow">➔</span>
            </button>
        </form>

        <div class="portal-links">
            <a href="{{ route('kiosk.index') }}" target="_blank" class="portal-link">
                <span>🖥️</span>
                <span>Kiosk Cek Saldo WBP ↗</span>
            </a>
            <span style="color: #334155;">&bull;</span>
            <a href="{{ route('mobile.pos') }}" target="_blank" class="portal-link">
                <span>📱</span>
                <span>Mobile POS Tablet ↗</span>
            </a>
        </div>

        <div class="security-footer">
            <div class="security-badge">
                <span>🔒</span>
                <span>Terenkripsi TLS 1.3 &bull; Otorisasi Hak Akses Berjenjang (RBAC)</span>
            </div>
            <div>&copy; {{ date('Y') }} UangPas Lapas &bull; Koperasi Pengayoman UPT Pemasyarakatan</div>
        </div>
    </div>
</div>

<script>
    function togglePassVisibility() {
        const pass = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        } else {
            pass.type = 'password';
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    }

    function handleSubmit(e) {
        const btn = document.getElementById('btnSubmit');
        const text = document.getElementById('btnSubmitText');
        const arrow = document.getElementById('btnSubmitArrow');
        
        btn.disabled = true;
        text.innerText = 'Memverifikasi Akses...';
        arrow.innerText = '⏳';
        
        // Form will naturally submit
        return true;
    }
</script>

</body>
</html>

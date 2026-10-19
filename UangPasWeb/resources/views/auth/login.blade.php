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
            --card-bg: rgba(15, 23, 42, 0.75);
            --card-border: rgba(255, 255, 255, 0.1);
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
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.2) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.5) 0px, transparent 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text-main);
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.2) 0%, rgba(16, 185, 129, 0.2) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.3);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.4;
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
        }

        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 16px 0 42px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
            background: rgba(15, 23, 42, 0.9);
        }

        .form-input::placeholder {
            color: #64748b;
        }

        .btn-toggle-pass {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            padding: 6px;
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
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
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
            filter: brightness(1.05);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Quick Test Login Chips */
        .quick-section {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .quick-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            margin-bottom: 12px;
            text-align: center;
        }

        .quick-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }

        .chip {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .chip:hover {
            background: rgba(2, 132, 199, 0.2);
            border-color: rgba(2, 132, 199, 0.4);
            color: #38bdf8;
            transform: translateY(-1px);
        }

        .security-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-logo">🏛️</div>
            <h1 class="brand-title">PORTAL UANGPAS LAPAS</h1>
            <p class="brand-subtitle">Sistem Dompet Digital & Biometrik Warga Binaan<br>Koperasi Pengayoman UPT Pemasyarakatan</p>
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

        <form action="{{ route('login.post') }}" method="POST" id="formLogin">
            @csrf

            <div class="form-group">
                <label class="form-label" for="login_id">NIP Dinas atau Email Petugas</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input 
                        type="text" 
                        id="login_id" 
                        name="login_id" 
                        class="form-input" 
                        placeholder="Contoh: 199511112019031005 atau kasir@lapas.go.id" 
                        value="{{ old('login_id') }}" 
                        required 
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
                        class="form-input" 
                        placeholder="Masukkan password akun Anda" 
                        required
                    >
                    <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility()" title="Lihat Password">
                        👁️
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Masuk ke Sistem</span> ➔
            </button>
        </form>

        <!-- Quick Credentials Chip for Development & Testing -->
        <div class="quick-section">
            <div class="quick-title">⚡ Akun Uji Coba Cepat (Pilih Peran):</div>
            <div class="quick-chips">
                <button type="button" class="chip" onclick="fillCredential('kanwil@lapas.go.id', 'password')">
                    👑 Kanwil Admin
                </button>
                <button type="button" class="chip" onclick="fillCredential('kalapas@lapas.go.id', 'password')">
                    🎖️ Kalapas
                </button>
                <button type="button" class="chip" onclick="fillCredential('gudang@lapas.go.id', 'password')">
                    📦 Admin Koperasi
                </button>
                <button type="button" class="chip" onclick="fillCredential('loket@lapas.go.id', 'password')">
                    🏢 Petugas Loket
                </button>
                <button type="button" class="chip" onclick="fillCredential('kasir@lapas.go.id', 'password')">
                    🛒 Kasir POS
                </button>
                <button type="button" class="chip" onclick="fillCredential('auditor@lapas.go.id', 'password')">
                    🔍 Auditor
                </button>
            </div>
        </div>

        <div class="security-footer">
            🔒 Terenkripsi & Dilindungi Otorisasi Hak Akses Berjenjang (RBAC)
        </div>
    </div>
</div>

<script>
    function togglePassVisibility() {
        const pass = document.getElementById('password');
        if (pass.type === 'password') {
            pass.type = 'text';
        } else {
            pass.type = 'password';
        }
    }

    function fillCredential(id, pass) {
        document.getElementById('login_id').value = id;
        document.getElementById('password').value = pass;
        document.getElementById('formLogin').submit();
    }
</script>

</body>
</html>

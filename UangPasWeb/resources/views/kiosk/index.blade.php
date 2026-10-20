<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Mandiri Cek Saldo WBP - UangPas Lapas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --card-border: #334155;
            --accent-emerald: #10b981;
            --accent-sky: #0ea5e9;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
            -webkit-user-select: none;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 100%);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Header */
        header {
            padding: 20px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }

        .brand-text h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .brand-text span {
            font-size: 12px;
            color: var(--accent-sky);
            font-weight: 600;
            display: block;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .device-badge {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dot-pulse {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .clock-box {
            text-align: right;
            font-family: monospace;
        }

        .clock-time {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        .clock-date {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Main Screen Container */
        main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px 20px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

        /* 1. STANDBY / IDLE STATE */
        #stateIdle {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 720px;
            animation: fadeIn 0.4s ease;
        }

        .fingerprint-scanner-anim {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(14, 165, 233, 0.08);
            border: 2px dashed rgba(14, 165, 233, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            margin-bottom: 28px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }

        .fingerprint-scanner-anim:hover {
            transform: scale(1.05);
            background: rgba(14, 165, 233, 0.15);
            border-color: var(--accent-sky);
        }

        .scan-laser {
            position: absolute;
            width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
            box-shadow: 0 0 10px #38bdf8;
            border-radius: 50%;
            animation: scanMove 2.2s infinite ease-in-out;
        }

        @keyframes scanMove {
            0% { top: 20%; opacity: 0.2; }
            50% { top: 75%; opacity: 1; }
            100% { top: 20%; opacity: 0.2; }
        }

        .idle-title {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .idle-sub {
            font-size: 15px;
            color: var(--text-muted);
            max-width: 520px;
            line-height: 1.5;
            margin-bottom: 34px;
        }

        /* Manual Input & Simulation Bar */
        .idle-actions {
            display: flex;
            gap: 12px;
            width: 100%;
            max-width: 460px;
        }

        .input-manual {
            flex-grow: 1;
            background: var(--bg-card);
            border: 1px solid var(--card-border);
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 16px;
            color: #fff;
            outline: none;
            text-align: center;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .input-manual:focus {
            border-color: var(--accent-sky);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }

        .btn-check {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-check:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        /* Simulation Dropdown Bar */
        .simulation-bar {
            margin-top: 32px;
            padding: 14px 18px;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .simulation-bar select {
            background: #0f172a;
            color: #fff;
            border: 1px solid #475569;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
        }

        /* 2. RESULT STATE (WHEN INMATE TAP DETECTED) */
        #stateResult {
            display: none;
            width: 100%;
            animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-card {
            background: var(--bg-card);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        /* Privacy Auto-Reset Bar */
        .privacy-bar-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
        }

        .privacy-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #0284c7);
            width: 100%;
            transition: width 1s linear;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .inmate-bio {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .inmate-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0f172a, #334155);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .inmate-details h2 {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .inmate-meta {
            font-size: 13.5px;
            color: var(--text-muted);
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .meta-pill {
            background: rgba(255, 255, 255, 0.06);
            padding: 3px 10px;
            border-radius: 6px;
            color: #cbd5e1;
            font-weight: 600;
        }

        .btn-close-result {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close-result:hover {
            background: rgba(255, 255, 255, 0.16);
        }

        /* Big Saldo & Kuota Banner */
        .balance-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .balance-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(6, 95, 70, 0.25));
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 20px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .balance-label {
            font-size: 13px;
            font-weight: 800;
            color: #34d399;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .balance-amount {
            font-size: 42px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
        }

        .limit-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .limit-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13.5px;
        }

        .limit-row span:first-child { color: var(--text-muted); }
        .limit-row span:last-child { font-weight: 700; color: #fff; }

        /* Recent Transactions Table */
        .transactions-section {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
        }

        .trx-title {
            font-size: 14px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trx-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .trx-item {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 12px 16px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .trx-date { font-size: 11.5px; color: var(--text-muted); }

        /* Footer */
        footer {
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        @media (max-width: 768px) {
            .balance-grid { grid-template-columns: 1fr; }
            .balance-amount { font-size: 32px; }
            .inmate-bio { flex-direction: column; text-align: center; }
            .inmate-meta { justify-content: center; flex-wrap: wrap; }
            .result-header { flex-direction: column; gap: 16px; align-items: center; }
            header { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-brand">
            <div class="brand-icon">👆</div>
            <div class="brand-text">
                <h1>{{ $setting->upt_name ?? 'Lapas Pemasyarakatan' }}</h1>
                <span>KIOSK CEK SALDO MANDIRI WARGA BINAAN</span>
            </div>
        </div>

        <div class="header-status">
            <div class="device-badge">
                <div class="dot-pulse"></div>
                <span>Sensor Solution X100C: {{ $setting->solution_device_ip ? $setting->solution_device_ip : '192.168.1.201' }}</span>
            </div>

            <div class="clock-box">
                <div class="clock-time" id="clockTime">12:00:00</div>
                <div class="clock-date" id="clockDate">Senin, 19 Oktober 2026</div>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main>
        <!-- 1. STANDBY / IDLE STATE -->
        <div id="stateIdle">
            <div class="fingerprint-scanner-anim" id="touchTrigger" title="Klik atau tempelkan sidik jari pada mesin">
                <div class="scan-laser"></div>
                <span>👆</span>
            </div>

            <h2 class="idle-title">Tempelkan Jari Anda pada Sensor</h2>
            <p class="idle-sub">
                Sentuhkan sidik jari Anda pada mesin <b>Solution X100C</b> di samping/bawah layar untuk memeriksa sisa saldo dan batas belanja harian Anda.
            </p>

            <!-- Manual Lookup (ID / Barcode / PIN) -->
            <form id="formManual" class="idle-actions" onsubmit="event.preventDefault(); lookupInmate();">
                <input type="text" id="inputQuery" class="input-manual" placeholder="No. Reg / PIN WBP..." autocomplete="off">
                <button type="submit" class="btn-check">Cek Saldo</button>
            </form>

            <!-- Quick Testing / Simulation Bar -->
            <div class="simulation-bar">
                <span>🧪 <b>Simulasi Coba:</b></span>
                <select id="selectSample" onchange="simulateSelect(this.value)">
                    <option value="">-- Pilih Contoh WBP untuk Tes --</option>
                    @foreach($sampleInmates as $sample)
                        <option value="{{ $sample->id }}">{{ $sample->nama }} (Kamar: {{ $sample->blok_kamar }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 2. RESULT CARD STATE -->
        <div id="stateResult">
            <div class="result-card">
                <!-- Privacy Auto-Reset Bar (5 seconds) -->
                <div class="privacy-bar-wrapper">
                    <div class="privacy-bar-fill" id="privacyBar"></div>
                </div>

                <div class="result-header">
                    <div class="inmate-bio">
                        <div class="inmate-avatar">👤</div>
                        <div class="inmate-details">
                            <h2 id="resNama">-</h2>
                            <div class="inmate-meta" style="flex-wrap: wrap;">
                                <span class="meta-pill" id="resRegisterD" style="color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); background: rgba(251, 191, 36, 0.1);">📖 Reg D: -</span>
                                <span class="meta-pill" id="resNoReg">No. Reg: -</span>
                                <span class="meta-pill" id="resKamar">Kamar: -</span>
                                <span class="meta-pill" style="color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">🔒 Biometrik Terverifikasi</span>
                            </div>
                        </div>
                    </div>

                    <button class="btn-close-result" onclick="resetToIdle()">
                        ✕ Tutup Sekarang (<span id="countdownSec">5</span>s)
                    </button>
                </div>

                <!-- Big Saldo Banner -->
                <div class="balance-grid">
                    <div class="balance-box">
                        <div class="balance-label">Sisa Saldo Rekening Anda</div>
                        <div class="balance-amount" id="resSaldo">Rp 0</div>
                    </div>

                    <div class="limit-box">
                        <div class="limit-row">
                            <span>Batas Belanja Harian:</span>
                            <span id="resLimit">Rp 0</span>
                        </div>
                        <div class="limit-row">
                            <span>Sudah Dibelanjakan Hari Ini:</span>
                            <span id="resSpent" style="color: #f87171;">Rp 0</span>
                        </div>
                        <div class="limit-row" style="margin-top: 10px; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                            <span style="font-weight: 700; color: #fff;">Sisa Kuota Belanja Hari Ini:</span>
                            <span id="resRemainingLimit" style="color: #38bdf8; font-size: 15px;">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Last 5 Transactions -->
                <div class="transactions-section">
                    <div class="trx-title">
                        <span>📑 5 Riwayat Belanja / Transaksi Terakhir</span>
                    </div>
                    <div class="trx-list" id="trxList">
                        <div style="color: #94a3b8; font-style: italic; text-align: center; padding: 12px;">Memuat transaksi...</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        UangPas Kiosk System • Terintegrasi dengan Sensor Solution X100C & U.are.U 4500 • Lapas Kelas I Pemasyarakatan
    </footer>

    <script>
        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clockTime').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('clockDate').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        let countdownTimer = null;
        let secondsLeft = 5;

        // Lookup Function
        function lookupInmate(queryVal) {
            const query = queryVal || document.getElementById('inputQuery').value.trim();
            if (!query) return;

            fetch(`{{ route('kiosk.lookup') }}?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayResult(data);
                    } else {
                        alert(data.message || 'WBP tidak ditemukan');
                    }
                })
                .catch(err => {
                    alert('Gagal mengambil data dari server: ' + err.message);
                });
        }

        // Display Inmate Info & Speak Balance
        function displayResult(data) {
            const inm = data.inmate;
            document.getElementById('resNama').textContent = inm.nama;
            document.getElementById('resRegisterD').textContent = '📖 Reg D: ' + (inm.register_d_number || inm.no_registrasi);
            document.getElementById('resNoReg').textContent = 'No. Reg: ' + inm.no_registrasi;
            document.getElementById('resKamar').textContent = 'Kamar: ' + inm.blok_kamar;
            document.getElementById('resSaldo').textContent = inm.saldo_formatted;
            document.getElementById('resLimit').textContent = inm.limit_harian_formatted;
            document.getElementById('resSpent').textContent = inm.today_spent_formatted;
            document.getElementById('resRemainingLimit').textContent = inm.remaining_limit_formatted;

            // Render transactions
            const trxList = document.getElementById('trxList');
            if (data.recent_transactions && data.recent_transactions.length > 0) {
                let html = '';
                data.recent_transactions.forEach(t => {
                    const isPlus = t.type === 'topup' || t.type === 'transfer_in';
                    const color = isPlus ? '#34d399' : '#f87171';
                    const sign = isPlus ? '+' : '-';
                    html += `
                        <div class="trx-item">
                            <div>
                                <b style="color: #fff;">${t.items_summary || (isPlus ? 'Top-Up Saldo' : 'Belanja Koperasi')}</b>
                                <div class="trx-date">${t.date}  •  No: ${t.invoice_no}</div>
                            </div>
                            <div style="font-weight: 800; font-size: 14px; color: ${color};">
                                ${sign} ${t.amount_formatted}
                            </div>
                        </div>
                    `;
                });
                trxList.innerHTML = html;
            } else {
                trxList.innerHTML = '<div style="color: #94a3b8; text-align: center; padding: 10px;">Belum ada riwayat transaksi.</div>';
            }

            // Text-to-speech audio in Indonesian
            speakBalance(inm.nama, inm.saldo);

            // Switch UI state
            document.getElementById('stateIdle').style.display = 'none';
            document.getElementById('stateResult').style.display = 'block';

            // Start 5-second privacy countdown
            startPrivacyCountdown();
        }

        // 5-Second Privacy Auto-Reset
        function startPrivacyCountdown() {
            clearInterval(countdownTimer);
            secondsLeft = 6;
            const bar = document.getElementById('privacyBar');
            bar.style.transition = 'none';
            bar.style.width = '100%';

            setTimeout(() => {
                bar.style.transition = 'width 6s linear';
                bar.style.width = '0%';
            }, 50);

            countdownTimer = setInterval(() => {
                secondsLeft--;
                document.getElementById('countdownSec').textContent = secondsLeft;
                if (secondsLeft <= 0) {
                    resetToIdle();
                }
            }, 1000);
        }

        function resetToIdle() {
            clearInterval(countdownTimer);
            document.getElementById('stateResult').style.display = 'none';
            document.getElementById('stateIdle').style.display = 'flex';
            document.getElementById('inputQuery').value = '';
            document.getElementById('selectSample').value = '';
        }

        // Text to Speech
        function speakBalance(name, amount) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const text = `Warga Binaan ${name}. Sisa saldo Anda ${amount} rupiah.`;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 1.0;
                window.speechSynthesis.speak(utterance);
            }
        }

        function simulateSelect(id) {
            if (id) lookupInmate(id);
        }

        // Background polling for Solution X100C hardware events
        let isPolling = false;
        function pollHardwareDevice() {
            if (isPolling) return;
            isPolling = true;

            fetch('{{ route("kiosk.poll") }}')
                .then(res => res.json())
                .then(data => {
                    isPolling = false;
                    if (data.active && data.has_tap && data.inmate_id) {
                        lookupInmate(data.inmate_id);
                    }
                })
                .catch(() => { isPolling = false; });
        }

        // Poll every 3 seconds if idle
        setInterval(() => {
            const idle = document.getElementById('stateIdle').style.display !== 'none';
            if (idle) pollHardwareDevice();
        }, 3000);
    </script>
</body>
</html>

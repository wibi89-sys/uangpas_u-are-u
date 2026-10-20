@extends('layouts.admin')

@section('title', 'Terminal EDC Cepat - Debit Saldo WBP')
@section('page_title', 'Terminal EDC Mandiri (Quick Debit)')
@section('page_desc', 'Terminal Pembayaran Khusus Kantin / Layanan dengan Verifikasi Biometrik Sidik Jari & Scan Kartu WBP')

@section('styles')
<style>
    .edc-container {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 24px;
    }
    @media (max-width: 992px) {
        .edc-container {
            grid-template-columns: 1fr;
        }
    }
    .amount-display-box {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 2px solid #334155;
        border-radius: 16px;
        padding: 24px;
        color: #fff;
        text-align: right;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.2);
    }
    .amount-display-val {
        font-size: 44px;
        font-weight: 800;
        color: #38bdf8;
        letter-spacing: -1px;
        font-family: monospace;
        line-height: 1.1;
    }
    .quick-chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        justify-content: flex-end;
    }
    .chip-btn {
        background: #334155;
        color: #e2e8f0;
        border: 1px solid #475569;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .chip-btn:hover {
        background: #0284c7;
        color: #fff;
        border-color: #38bdf8;
    }
    .numpad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 14px;
    }
    .num-btn {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 16px;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        user-select: none;
    }
    .num-btn:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }
    .num-btn:active {
        transform: translateY(1px);
    }
    .num-btn.btn-action-del {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }
    .num-btn.btn-action-clear {
        background: #fef3c7;
        color: #d97706;
        border-color: #fcd34d;
    }
    .inmate-selected-card {
        background: #f0f9ff;
        border: 2px solid #38bdf8;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 20px;
        display: flex;
        gap: 16px;
        align-items: center;
        animation: fadeIn 0.25s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hardware-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .badge-online { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .badge-offline { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        display: inline-block;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.4); opacity: 0.5; }
    }
</style>
@endsection

@section('content')
<div class="edc-container">
    <!-- Kolom Kiri: Input Nominal & Otorisasi Transaksi -->
    <div>
        <div class="card" style="padding: 24px;">
            <!-- Header Terminal & Status Alat -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border); flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(2, 132, 199, 0.15); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        ⚡
                    </div>
                    <div>
                        <h2 style="font-size: 16px; font-weight: 800; color: #0f172a;">Input Tagihan Belanja (EDC)</h2>
                        <span style="font-size: 12px; color: var(--text-muted);">Sistem potong saldo mandiri untuk software POS kantin</span>
                    </div>
                </div>

                <div id="hardwareStatusIndicator">
                    <span class="hardware-status-badge badge-online" id="bridgeBadge">
                        <span class="pulse-dot"></span> <span id="bridgeText">Bridge U.are.U Siap</span>
                    </span>
                </div>
            </div>

            <!-- Merchant & Pos Belanja -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Titik Belanja / Pos Merchant *</label>
                    <select id="merchantSelect" style="font-weight: 700; color: #0f172a;">
                        <option value="Kantin Koperasi Utama">🏪 Kantin Koperasi Utama</option>
                        <option value="Kantin Blok A">🥪 Kantin Blok A</option>
                        <option value="Kantin Blok B">🍜 Kantin Blok B</option>
                        <option value="Kantin Blok C">☕ Kantin Blok C</option>
                        <option value="Wartelsuspas (Telepon)">📞 Wartelsuspas (Telepon)</option>
                        <option value="Layanan Laundry">🧺 Layanan Laundry WBP</option>
                        <option value="Toko Roti & Bimker">🍞 Toko Roti & Bimker</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>No. Struk POS Kantin (Opsional)</label>
                    <input type="text" id="externalRefInput" placeholder="Contoh: POS-9821 / STRUK-01">
                </div>
            </div>

            <!-- Display Nominal Besar -->
            <div class="amount-display-box">
                <div style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;">
                    Nominal Tagihan Kasir:
                </div>
                <div class="amount-display-val" id="displayAmount">
                    Rp 0
                </div>
                <div class="quick-chips">
                    <button type="button" class="chip-btn" onclick="addAmount(5000)">+5rb</button>
                    <button type="button" class="chip-btn" onclick="addAmount(10000)">+10rb</button>
                    <button type="button" class="chip-btn" onclick="addAmount(20000)">+20rb</button>
                    <button type="button" class="chip-btn" onclick="addAmount(50000)">+50rb</button>
                    <button type="button" class="chip-btn" onclick="addAmount(100000)">+100rb</button>
                </div>
            </div>

            <!-- Virtual Numpad & Keyboard Support -->
            <div class="numpad-grid">
                <button type="button" class="num-btn" onclick="pressDigit('1')">1</button>
                <button type="button" class="num-btn" onclick="pressDigit('2')">2</button>
                <button type="button" class="num-btn" onclick="pressDigit('3')">3</button>

                <button type="button" class="num-btn" onclick="pressDigit('4')">4</button>
                <button type="button" class="num-btn" onclick="pressDigit('5')">5</button>
                <button type="button" class="num-btn" onclick="pressDigit('6')">6</button>

                <button type="button" class="num-btn" onclick="pressDigit('7')">7</button>
                <button type="button" class="num-btn" onclick="pressDigit('8')">8</button>
                <button type="button" class="num-btn" onclick="pressDigit('9')">9</button>

                <button type="button" class="num-btn btn-action-clear" onclick="clearAmount()">C</button>
                <button type="button" class="num-btn" onclick="pressDigit('0')">0</button>
                <button type="button" class="num-btn" onclick="pressDigit('000')">000</button>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="button" class="num-btn btn-action-del" style="flex: 1;" onclick="pressBackspace()">
                    ⌫ Hapus Angka
                </button>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Identifikasi WBP & Otorisasi Biometrik -->
    <div>
        <div class="card" style="padding: 24px; position: sticky; top: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--card-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    👤
                </div>
                <div>
                    <h2 style="font-size: 16px; font-weight: 800; color: #0f172a;">Identifikasi Warga Binaan</h2>
                    <span style="font-size: 12px; color: var(--text-muted);">Pilih WBP via Scan Sidik Jari atau Scan Kartu</span>
                </div>
            </div>

            <!-- Tombol Trigger Biometrik Cepat (U.are.U 4500) -->
            <button type="button" id="btnScanFingerprint" class="btn btn-primary" onclick="triggerBiometricScan()" style="width: 100%; padding: 14px; justify-content: center; font-size: 15px; font-weight: 800; margin-bottom: 16px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <span style="font-size: 20px;">👆</span> SCAN SIDIK JARI (U.ARE.U)
            </button>

            <!-- Mode Pencarian Kartu / Teks Alternatif -->
            <div style="position: relative; margin-bottom: 18px;">
                <input type="text" id="inmateSearchInput" placeholder="Atau scan barcode kartu / ketik nama / No. Reg..." oninput="filterInmateList(this.value)" style="padding-left: 36px; font-size: 13.5px;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;">🔍</span>

                <!-- Dropdown Pencarian WBP -->
                <div id="inmateSearchResults" style="display: none; position: absolute; left: 0; right: 0; top: 105%; background: #fff; border: 1px solid #cbd5e1; border-radius: 10px; max-height: 220px; overflow-y: auto; z-index: 50; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);"></div>
            </div>

            <!-- Kartu WBP Terpilih -->
            <div id="selectedInmateCard" style="display: none;">
                <div class="inmate-selected-card">
                    <div style="width: 56px; height: 56px; border-radius: 12px; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; flex-shrink: 0;" id="inmateInitial">
                        W
                    </div>
                    <div style="flex-grow: 1;">
                        <div style="font-weight: 800; color: #0f172a; font-size: 15px;" id="inmateName">-</div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 1px;">
                            Reg: <strong id="inmateReg">-</strong> &bull; <span id="inmateRegD" style="color: #0284c7; font-weight: 700;">-</span>
                        </div>
                        <div style="margin-top: 4px;">
                            <span class="pill pill-primary" id="inmateRoom">-</span>
                            <span class="pill pill-success" id="inmateBioStatus">👆 Terdaftar</span>
                        </div>
                    </div>
                    <button type="button" onclick="clearSelectedInmate()" style="background: transparent; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">&times;</button>
                </div>

                <!-- Status Saldo & Limit Hari Ini -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Sisa Saldo Titipan (Register D):</span>
                        <strong style="font-size: 16px; color: #10b981;" id="inmateBalanceDisplay">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Sisa Limit Belanja Hari Ini:</span>
                        <strong style="font-size: 14px; color: #0284c7;" id="inmateLimitDisplay">Rp 0</strong>
                    </div>
                </div>
            </div>

            <!-- Status Box / Notifikasi Verifikasi -->
            <div id="edcStatusAlert" style="display: none; padding: 12px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; line-height: 1.4;"></div>

            <!-- Tombol Eksekusi Debit Utama -->
            <button type="button" id="btnExecuteCharge" class="btn btn-success" onclick="processCharge()" style="width: 100%; padding: 16px; justify-content: center; font-size: 16px; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);" disabled>
                <span>💳</span> PROSES DEBIT SALDO SEKARANG
            </button>
            <div style="text-align: center; font-size: 11.5px; color: var(--text-muted); margin-top: 8px;">
                Tekan tombol atau Enter untuk mengeksekusi pemotongan saldo
            </div>
        </div>
    </div>
</div>

<!-- Modal Sukses Debit (Full Overlay) -->
<div id="modalDebitSuccess" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 20px; width: 100%; max-width: 480px; padding: 32px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); animation: zoomIn 0.25s ease;">
        <div style="width: 76px; height: 76px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 16px auto; border: 3px solid #86efac;">
            ✓
        </div>
        <h3 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">DEBIT BERHASIL (LUNAS)</h3>
        <p style="font-size: 13px; color: #16a34a; font-weight: 700;" id="successInvoiceText">EDC-LPS-01-xxxx</p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin: 20px 0; text-align: left; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Warga Binaan:</span>
                <strong id="successInmateName" style="color: #0f172a;">-</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Titik Merchant:</span>
                <span id="successMerchant" style="font-weight: 600;">-</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                <span style="color: var(--text-muted);">Nominal Terpotong:</span>
                <strong id="successAmount" style="color: #ef4444; font-size: 16px;">Rp 0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Sisa Saldo Register D:</span>
                <strong id="successBalanceAfter" style="color: #0284c7; font-size: 15px;">Rp 0</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Sisa Kuota Belanja Hari Ini:</span>
                <strong id="successRemainingLimit" style="color: #10b981;">Rp 0</strong>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a id="btnSuccessPrint" href="#" target="_blank" class="btn btn-outline" style="flex: 1; padding: 12px; justify-content: center; font-weight: 700; border-color: #0284c7; color: #0284c7;">
                <span>🖨️</span> Cetak Struk Mini
            </a>
            <button type="button" class="btn btn-primary" onclick="closeSuccessModal()" style="flex: 1; padding: 12px; justify-content: center; font-weight: 800;">
                Selesai (Siap Berikutnya)
            </button>
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 10px;" id="autoCloseCountdown">
            Otomatis ditutup dalam 5 detik...
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Master data inmates inject
    const inmatesData = @json($inmates);
    const bridgeUrl = "http://127.0.0.1:8080";

    let currentAmount = 0;
    let selectedInmate = null;
    let autoCloseTimer = null;

    // --- Audio Feedback Synth ---
    function playAudioTone(freq, type, duration) {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type || 'sine';
            osc.frequency.value = freq || 880;
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + (duration || 0.15));
            setTimeout(() => audioCtx.close(), (duration || 0.15) * 1000 + 50);
        } catch(e) {}
    }

    function soundBeepSuccess() {
        playAudioTone(987.77, 'sine', 0.12); // B5
        setTimeout(() => playAudioTone(1318.51, 'sine', 0.25), 120); // E6
    }

    function soundBeepError() {
        playAudioTone(220, 'sawtooth', 0.25);
    }

    // --- Numpad Logic ---
    function updateDisplay() {
        const display = document.getElementById('displayAmount');
        display.innerText = 'Rp ' + Number(currentAmount).toLocaleString('id-ID');
        validateState();
    }

    function pressDigit(d) {
        playAudioTone(520, 'triangle', 0.04);
        let str = currentAmount.toString();
        if (str === '0') str = '';
        str += d;
        if (str.length > 9) return; // limit 999.999.999
        currentAmount = parseInt(str) || 0;
        updateDisplay();
    }

    function pressBackspace() {
        playAudioTone(400, 'triangle', 0.04);
        let str = currentAmount.toString();
        if (str.length <= 1) {
            currentAmount = 0;
        } else {
            currentAmount = parseInt(str.slice(0, -1)) || 0;
        }
        updateDisplay();
    }

    function clearAmount() {
        playAudioTone(350, 'triangle', 0.05);
        currentAmount = 0;
        updateDisplay();
    }

    function addAmount(delta) {
        playAudioTone(600, 'triangle', 0.04);
        currentAmount += delta;
        updateDisplay();
    }

    // Keyboard support
    window.addEventListener('keydown', function(e) {
        // Jangan tangkap jika sedang ketik di input biasa
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (e.key === 'Enter') {
                e.preventDefault();
                processCharge();
            }
            return;
        }

        if (e.key >= '0' && e.key <= '9') {
            pressDigit(e.key);
        } else if (e.key === 'Backspace') {
            pressBackspace();
        } else if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') {
            clearAmount();
        } else if (e.key === 'Enter') {
            processCharge();
        }
    });

    // --- Inmate Search & Selection ---
    function filterInmateList(query) {
        const q = query.trim().toLowerCase();
        const resultsBox = document.getElementById('inmateSearchResults');
        if (!q || q.length < 2) {
            resultsBox.style.display = 'none';
            return;
        }

        const filtered = inmatesData.filter(w => 
            w.nama.toLowerCase().includes(q) ||
            w.no_registrasi.toLowerCase().includes(q) ||
            w.register_d_number.toLowerCase().includes(q) ||
            (w.va_number && w.va_number.includes(q))
        ).slice(0, 8);

        if (filtered.length === 0) {
            resultsBox.innerHTML = '<div style="padding: 12px; font-size: 12px; color: #94a3b8; text-align: center;">WBP tidak ditemukan</div>';
            resultsBox.style.display = 'block';
            return;
        }

        let html = '';
        filtered.forEach(w => {
            html += `
                <div onclick="selectInmateById(${w.id})" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <div>
                        <strong style="font-size: 13.5px; color: #0f172a;">${w.nama}</strong>
                        <div style="font-size: 11.5px; color: #64748b;">${w.no_registrasi} &bull; ${w.blok_kamar}</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 13px; font-weight: 700; color: #10b981;">Rp ${Number(w.saldo).toLocaleString('id-ID')}</span>
                        <div style="font-size: 10.5px; color: #64748b;">Limit: Rp ${Number(w.remaining_limit).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            `;
        });
        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';
    }

    function selectInmateById(id) {
        const inmate = inmatesData.find(w => w.id === id);
        if (!inmate) return;

        selectedInmate = inmate;
        document.getElementById('inmateSearchResults').style.display = 'none';
        document.getElementById('inmateSearchInput').value = '';

        // Render Card
        document.getElementById('inmateInitial').innerText = inmate.nama.charAt(0);
        document.getElementById('inmateName').innerText = inmate.nama;
        document.getElementById('inmateReg').innerText = inmate.no_registrasi;
        document.getElementById('inmateRegD').innerText = inmate.register_d_number;
        document.getElementById('inmateRoom').innerText = inmate.blok_kamar;
        document.getElementById('inmateBalanceDisplay').innerText = 'Rp ' + Number(inmate.saldo).toLocaleString('id-ID');
        document.getElementById('inmateLimitDisplay').innerText = 'Rp ' + Number(inmate.remaining_limit).toLocaleString('id-ID');

        const bioStatus = document.getElementById('inmateBioStatus');
        if (inmate.has_fingerprint) {
            bioStatus.className = 'pill pill-success';
            bioStatus.innerText = '👆 Sidik Jari Siap';
        } else {
            bioStatus.className = 'pill pill-warning';
            bioStatus.innerText = '⚪ Belum Rekam Jari';
        }

        document.getElementById('selectedInmateCard').style.display = 'block';
        playAudioTone(750, 'sine', 0.1);
        validateState();
    }

    function clearSelectedInmate() {
        selectedInmate = null;
        document.getElementById('selectedInmateCard').style.display = 'none';
        validateState();
    }

    // --- Biometric Hardware Bridge Integration ---
    async function checkBridgeStatus() {
        const badge = document.getElementById('bridgeBadge');
        const text = document.getElementById('bridgeText');
        try {
            const resp = await fetch(bridgeUrl + "/api/status", { method: 'GET' });
            if (resp.ok) {
                badge.className = 'hardware-status-badge badge-online';
                text.innerText = 'Sensor U.are.U Siap';
            } else {
                badge.className = 'hardware-status-badge badge-offline';
                text.innerText = 'Bridge Offline';
            }
        } catch (e) {
            badge.className = 'hardware-status-badge badge-offline';
            text.innerText = 'Sensor Bridge Standby (Port 8080)';
        }
    }

    async function triggerBiometricScan() {
        showAlert('Silakan WBP tempelkan jari pada sensor scanner U.are.U 4500...', 'info');
        const btn = document.getElementById('btnScanFingerprint');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> MENUNGGU TEMPEL JARI...';

        try {
            // Panggil API Bridge untuk capture sidik jari
            const resp = await fetch(bridgeUrl + "/api/capture", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ timeout: 10000 })
            });

            const data = await resp.json();
            btn.disabled = false;
            btn.innerHTML = '<span style="font-size: 20px;">👆</span> SCAN SIDIK JARI (U.ARE.U)';

            if (!data.success) {
                showAlert('Gagal memindai jari: ' + (data.message || 'Timeout / Dibatalkan'), 'error');
                soundBeepError();
                return;
            }

            // Jika kita sudah memilih WBP sebelumnya: Lakukan 1:1 Verify
            if (selectedInmate && selectedInmate.fingerprint_template) {
                const verifyResp = await fetch(bridgeUrl + "/api/verify", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        sample: data.template,
                        template: selectedInmate.fingerprint_template
                    })
                });
                const verifyData = await verifyResp.json();
                if (verifyData.verified) {
                    showAlert('✓ Verifikasi Sidik Jari Cocok dengan ' + selectedInmate.nama, 'success');
                    soundBeepSuccess();
                    // Langsung eksekusi jika nominal sudah ada
                    if (currentAmount > 0) {
                        setTimeout(() => processCharge(), 400);
                    }
                } else {
                    showAlert('❌ Sidik Jari TIDAK COCOK dengan WBP ' + selectedInmate.nama + '! Transaksi dibatalkan.', 'error');
                    soundBeepError();
                }
                return;
            }

            // Jika belum pilih WBP: Lakukan 1:N Identify di database WBP aktif yang punya template
            const candidates = inmatesData.filter(w => w.has_fingerprint).map(w => ({
                id: w.id,
                template: w.fingerprint_template
            }));

            if (candidates.length === 0) {
                showAlert('Belum ada WBP yang merekam sidik jari di database!', 'error');
                return;
            }

            const identifyResp = await fetch(bridgeUrl + "/api/identify", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sample: data.template,
                    candidates: candidates
                })
            });

            const identifyData = await identifyResp.json();
            if (identifyData.success && identifyData.matched_id) {
                selectInmateById(identifyData.matched_id);
                showAlert('✓ Sidik Jari Terverifikasi: ' + selectedInmate.nama, 'success');
                soundBeepSuccess();
                if (currentAmount > 0) {
                    setTimeout(() => processCharge(), 400);
                }
            } else {
                showAlert('❌ Sidik Jari tidak dikenali dalam daftar narapidana aktif!', 'error');
                soundBeepError();
            }

        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<span style="font-size: 20px;">👆</span> SCAN SIDIK JARI (U.ARE.U)';
            showAlert('Sensor Hardware Bridge tidak terhubung. Pastikan UangPasBridge aktif di latar belakang (Port 8080). Anda tetap dapat menggunakan pencarian kartu WBP secara manual.', 'warning');
        }
    }

    // --- State Validation & Alert Helper ---
    function showAlert(msg, type) {
        const box = document.getElementById('edcStatusAlert');
        box.style.display = 'block';
        if (type === 'error') {
            box.style.background = '#fee2e2';
            box.style.color = '#991b1b';
            box.style.border = '1px solid #fca5a5';
        } else if (type === 'success') {
            box.style.background = '#dcfce7';
            box.style.color = '#166534';
            box.style.border = '1px solid #86efac';
        } else if (type === 'info') {
            box.style.background = '#e0f2fe';
            box.style.color = '#0369a1';
            box.style.border = '1px solid #7dd3fc';
        } else {
            box.style.background = '#fef3c7';
            box.style.color = '#92400e';
            box.style.border = '1px solid #fcd34d';
        }
        box.innerHTML = msg;
    }

    function hideAlert() {
        document.getElementById('edcStatusAlert').style.display = 'none';
    }

    function validateState() {
        const btn = document.getElementById('btnExecuteCharge');
        if (!selectedInmate || currentAmount <= 0) {
            btn.disabled = true;
            return;
        }

        if (selectedInmate.saldo < currentAmount) {
            showAlert('⚠️ Saldo titipan WBP (Rp ' + Number(selectedInmate.saldo).toLocaleString('id-ID') + ') tidak mencukupi untuk tagihan Rp ' + Number(currentAmount).toLocaleString('id-ID'), 'error');
            btn.disabled = true;
            return;
        }

        if (selectedInmate.remaining_limit < currentAmount) {
            showAlert('⚠️ Melebihi batas limit belanja harian Lapas! Sisa limit hari ini: Rp ' + Number(selectedInmate.remaining_limit).toLocaleString('id-ID'), 'error');
            btn.disabled = true;
            return;
        }

        hideAlert();
        btn.disabled = false;
    }

    // --- Process Charge Execution ---
    async function processCharge() {
        if (!selectedInmate || currentAmount <= 0) return;

        const btn = document.getElementById('btnExecuteCharge');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> MEMPROSES DEBIT SALDO...';

        const payload = {
            inmate_id: selectedInmate.id,
            amount: currentAmount,
            merchant_name: document.getElementById('merchantSelect').value,
            reference_id: document.getElementById('externalRefInput').value,
            payment_method: selectedInmate.has_fingerprint ? 'fingerprint' : 'barcode_card',
            notes: 'Pembayaran ' + document.getElementById('merchantSelect').value + ' via EDC Cepat'
        };

        try {
            const resp = await fetch("{{ route('admin.edc.charge') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const res = await resp.json();
            btn.disabled = false;
            btn.innerHTML = '<span>💳</span> PROSES DEBIT SALDO SEKARANG';

            if (!res.success) {
                showAlert('❌ ' + res.message, 'error');
                soundBeepError();
                return;
            }

            // Sukses! Update lokal saldo inmate
            selectedInmate.saldo = res.data.balance_after;
            selectedInmate.remaining_limit = res.data.remaining_limit;

            soundBeepSuccess();

            // Tampilkan Modal Sukses
            showSuccessModal(res.data);

        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<span>💳</span> PROSES DEBIT SALDO SEKARANG';
            showAlert('Terjadi kesalahan jaringan/server saat memproses transaksi.', 'error');
            soundBeepError();
        }
    }

    function showSuccessModal(data) {
        document.getElementById('successInvoiceText').innerText = data.invoice_no;
        document.getElementById('successInmateName').innerText = data.inmate_nama + ' (' + data.inmate_reg + ')';
        document.getElementById('successMerchant').innerText = data.merchant;
        document.getElementById('successAmount').innerText = data.amount_formatted;
        document.getElementById('successBalanceAfter').innerText = data.balance_after_formatted;
        document.getElementById('successRemainingLimit').innerText = data.remaining_limit_formatted;
        document.getElementById('btnSuccessPrint').href = "/edc/receipt/" + data.transaction_id;

        document.getElementById('modalDebitSuccess').style.display = 'flex';

        // Auto close timer (5 detik)
        let countdown = 5;
        const countEl = document.getElementById('autoCloseCountdown');
        if (autoCloseTimer) clearInterval(autoCloseTimer);
        countEl.innerText = `Otomatis ditutup dalam ${countdown} detik...`;

        autoCloseTimer = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(autoCloseTimer);
                closeSuccessModal();
            } else {
                countEl.innerText = `Otomatis ditutup dalam ${countdown} detik...`;
            }
        }, 1000);
    }

    function closeSuccessModal() {
        if (autoCloseTimer) clearInterval(autoCloseTimer);
        document.getElementById('modalDebitSuccess').style.display = 'none';

        // Reset nominal & WBP agar kasir siap melayani antrean berikutnya
        clearAmount();
        clearSelectedInmate();
        document.getElementById('externalRefInput').value = '';
    }

    // On Load: Cek status bridge
    document.addEventListener('DOMContentLoaded', function() {
        checkBridgeStatus();
        setInterval(checkBridgeStatus, 15000); // Polling status hardware tiap 15 detik
    });
</script>
@endsection

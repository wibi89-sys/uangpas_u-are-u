using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Threading;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public partial class MainForm
    {
        #region TAB 4: PENGATURAN SISTEM & DIAGNOSTIK SENSOR
        // Sensor Diagnostic Fields
        private PictureBox picDiagFinger;
        private Label lblDiagStatus;
        private Label lblDiagQuality;
        private Label lblDiagLatency;
        private Label lblDiagMatch;
        private Label lblDiagDevice;
        private Label lblDiagSerial;
        private Button btnDiagToggle;
        private Button btnDiagSaveEnroll;
        private Button btnDiagCompare;
        private bool isDiagCapturing = false;
        private int diagScanCount = 0;
        private Fmd diagEnrolledFmd = null;
        private Fmd diagLastFmd = null;
        private Stopwatch diagSw = new Stopwatch();

        private void BuildSettingsTab(TabPage page)
        {
            Panel p = new Panel { 
                Dock = DockStyle.Fill, 
                Padding = new Padding(24), 
                AutoScroll = true, 
                BackColor = Color.FromArgb(248, 250, 252) 
            };

            Label lblTitle = new Label { 
                Text = "⚙️ Pengaturan Sistem & Uji Sensor Biometrik", 
                Font = new Font("Segoe UI", 15f, FontStyle.Bold), 
                ForeColor = Color.FromArgb(15, 23, 42),
                AutoSize = true, 
                Location = new Point(24, 18) 
            };
            p.Controls.Add(lblTitle);

            Label lblSub = new Label { 
                Text = "Konfigurasi alamat server UangPas, tata letak keranjang belanja kasir, dan pengujian sensor sidik jari.", 
                Font = new Font("Segoe UI", 9.5f), 
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true, 
                Location = new Point(24, 50) 
            };
            p.Controls.Add(lblSub);

            int cardTop = 86;
            int cardWidth = 860;

            // ==========================================
            // CARD 1: PENGATURAN ALAMAT SERVER UANGPAS
            // ==========================================
            GroupBox grpServer = new GroupBox {
                Text = " 🌐 Pengaturan Alamat Server UangPas ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 260,
                BackColor = Color.White
            };

            Label lblServerDesc = new Label {
                Text = "Masukkan Alamat IP atau Domain Server UangPas Lapas (contoh: uangpas.test, 192.168.1.50, atau 127.0.0.1:8000):",
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(18, 28),
                Width = 820,
                Height = 20
            };
            grpServer.Controls.Add(lblServerDesc);

            Label lblIpTag = new Label { 
                Text = "Alamat IP / Domain Server:", 
                Location = new Point(18, 54), 
                Width = 220, 
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59)
            };
            grpServer.Controls.Add(lblIpTag);

            txtServerAddress = new TextBox {
                Location = new Point(18, 76),
                Width = 470,
                Font = new Font("Segoe UI", 11f),
                Text = GetDisplayServerAddress(this.apiUrl)
            };
            grpServer.Controls.Add(txtServerAddress);

            Button btnSaveServer = new Button {
                Text = "💾 Simpan & Sambungkan",
                Location = new Point(500, 74),
                Width = 200,
                Height = 35,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnSaveServer.FlatAppearance.BorderSize = 0;
            btnSaveServer.Click += (s, e) => {
                string input = txtServerAddress.Text.Trim();
                if (string.IsNullOrEmpty(input))
                {
                    MessageBox.Show("Silakan masukkan alamat IP atau Domain server.", "Perhatian", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
                ApplyNewServerUrl(input);
            };
            grpServer.Controls.Add(btnSaveServer);

            Button btnTestServer = new Button {
                Text = "🔄 Uji Koneksi",
                Location = new Point(710, 74),
                Width = 130,
                Height = 35,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnTestServer.Click += (s, e) => {
                btnTestServer.Text = "⏳ Menguji...";
                btnTestServer.Enabled = false;
                ThreadPool.QueueUserWorkItem(_ => {
                    TestCurrentConnection();
                    try
                    {
                        this.BeginInvoke((Action)(() => {
                            btnTestServer.Text = "🔄 Uji Koneksi";
                            btnTestServer.Enabled = true;
                        }));
                    }
                    catch { }
                });
            };
            grpServer.Controls.Add(btnTestServer);

            // Quick Presets
            Label lblPresets = new Label {
                Text = "Pilihan Cepat:",
                Location = new Point(18, 122),
                Width = 95,
                Height = 26,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(100, 116, 139),
                TextAlign = ContentAlignment.MiddleLeft
            };
            grpServer.Controls.Add(lblPresets);

            Button btnPresetLaragon = new Button {
                Text = "🌐 Laragon Lokal (uangpas.test)",
                Location = new Point(118, 120),
                Width = 230,
                Height = 28,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPresetLaragon.Click += (s, e) => {
                txtServerAddress.Text = "uangpas.test";
            };
            grpServer.Controls.Add(btnPresetLaragon);

            Button btnPresetLocalhost = new Button {
                Text = "🏠 Localhost Port 8000 (127.0.0.1:8000)",
                Location = new Point(358, 120),
                Width = 270,
                Height = 28,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPresetLocalhost.Click += (s, e) => {
                txtServerAddress.Text = "127.0.0.1:8000";
            };
            grpServer.Controls.Add(btnPresetLocalhost);

            lblCurrentServerInfo = new Label {
                Text = "Alamat Aktif: " + this.apiUrl + "\nStatus: Menunggu pengujian...",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(18, 158),
                Width = 820,
                Height = 44
            };
            grpServer.Controls.Add(lblCurrentServerInfo);

            // Android LAN Info Banner
            Panel pnlMobileHint = new Panel {
                Location = new Point(18, 206),
                Width = 820,
                Height = 40,
                BackColor = Color.FromArgb(240, 253, 250),
                BorderStyle = BorderStyle.FixedSingle
            };
            Label lblMobileHint = new Label {
                Text = "📱 Akses dari HP Android / Tablet (Satu Wi-Fi): Buka browser HP dan ketik  http://192.168.1.11  atau  http://192.168.1.11:8000",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(13, 148, 136),
                Dock = DockStyle.Fill,
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(8, 0, 8, 0)
            };
            pnlMobileHint.Controls.Add(lblMobileHint);
            grpServer.Controls.Add(pnlMobileHint);

            p.Controls.Add(grpServer);

            cardTop += 275;

            // ==========================================
            // CARD 2: PENGATURAN TATA LETAK KERANJANG
            // ==========================================
            GroupBox grpLayout = new GroupBox {
                Text = " 🛒 Pengaturan Tata Letak Keranjang Belanja & Katalog ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 150,
                BackColor = Color.White
            };

            lblSplitterInfo = new Label {
                Text = "Ukuran Saat Ini: " + (int)(this.savedSplitterRatio * 100) + "% Katalog Produk  |  " + (int)Math.Round((1.0 - this.savedSplitterRatio) * 100) + "% Keranjang Belanja\n(Posisi pemisah juga dapat langsung digeser dengan mouse di Tab Kasir, tersimpan otomatis).",
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(18, 28),
                Width = 820,
                Height = 42
            };
            grpLayout.Controls.Add(lblSplitterInfo);

            Button btnPreset25 = new Button {
                Text = "📏 Standar (75% Produk : 25% Keranjang)",
                Location = new Point(18, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset25.Click += (s, e) => {
                this.savedSplitterRatio = 0.75;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Standar:\n25% Keranjang Belanja, 75% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset25);

            Button btnPreset35 = new Button {
                Text = "📐 Sedang (65% Produk : 35% Keranjang)",
                Location = new Point(295, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset35.Click += (s, e) => {
                this.savedSplitterRatio = 0.65;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Sedang:\n35% Keranjang Belanja, 65% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset35);

            Button btnPreset50 = new Button {
                Text = "⚖️ Seimbang (50% Produk : 50% Keranjang)",
                Location = new Point(572, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset50.Click += (s, e) => {
                this.savedSplitterRatio = 0.50;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Seimbang:\n50% Keranjang Belanja, 50% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset50);

            p.Controls.Add(grpLayout);

            cardTop += 165;

            // ==========================================
            // CARD 3: UJI DIAGNOSTIK SENSOR SIDIK JARI
            // ==========================================
            GroupBox grpDiag = new GroupBox {
                Text = " 🔬 Uji & Diagnostik Sensor Sidik Jari Biometrik ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 445,
                BackColor = Color.White
            };

            // Left: Live visual preview
            Panel pnlDiagLeft = new Panel {
                Location = new Point(18, 28),
                Width = 310,
                Height = 405,
                BackColor = Color.Transparent
            };

            Label lblPreviewTitle = new Label {
                Text = "Visual Citra Sidik Jari:",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlDiagLeft.Controls.Add(lblPreviewTitle);

            picDiagFinger = new PictureBox {
                Dock = DockStyle.Top,
                Height = 265,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            pnlDiagLeft.Controls.Add(picDiagFinger);

            lblDiagStatus = new Label {
                Text = "Sensor dalam keadaan siaga (standby).",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Dock = DockStyle.Top,
                Height = 55,
                TextAlign = ContentAlignment.MiddleCenter
            };
            pnlDiagLeft.Controls.Add(lblDiagStatus);

            btnDiagToggle = new Button {
                Text = "▶️ Mulai Pengujian Sensor",
                Dock = DockStyle.Bottom,
                Height = 44,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnDiagToggle.FlatAppearance.BorderSize = 0;
            btnDiagToggle.Click += (s, e) => ToggleDiagCapture();
            pnlDiagLeft.Controls.Add(btnDiagToggle);
            grpDiag.Controls.Add(pnlDiagLeft);

            // Right: Sensor Info, Quality, and Verification
            Panel pnlDiagRight = new Panel {
                Location = new Point(345, 28),
                Width = 495,
                Height = 405,
                BackColor = Color.Transparent
            };

            // Sub-box 1: Hardware Info
            GroupBox grpHardware = new GroupBox {
                Text = " Informasi Perangkat Scanner ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 0),
                Width = 495,
                Height = 95
            };
            lblDiagDevice = new Label { 
                Text = "Perangkat: " + (currentReader != null ? currentReader.Description.Id.ProductName : "Mendeteksi..."), 
                Location = new Point(14, 22), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            lblDiagSerial = new Label { 
                Text = "Serial ID: " + (currentReader != null ? currentReader.Description.SerialNumber : "-"), 
                Location = new Point(14, 44), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            Label lblResInfo = new Label { 
                Text = "Resolusi: 500 DPI (ANSI/NIST Optical Sensor)", 
                Location = new Point(14, 66), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            grpHardware.Controls.Add(lblDiagDevice);
            grpHardware.Controls.Add(lblDiagSerial);
            grpHardware.Controls.Add(lblResInfo);
            pnlDiagRight.Controls.Add(grpHardware);

            // Sub-box 2: Kualitas Scan Terakhir
            GroupBox grpQuality = new GroupBox {
                Text = " Kualitas Hasil Scan Terakhir ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 102),
                Width = 495,
                Height = 82
            };
            lblDiagQuality = new Label { 
                Text = "Kualitas: Belum ada sentuhan", 
                Location = new Point(14, 24), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            lblDiagLatency = new Label { 
                Text = "Kecepatan Respon: -", 
                Location = new Point(14, 48), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            grpQuality.Controls.Add(lblDiagQuality);
            grpQuality.Controls.Add(lblDiagLatency);
            pnlDiagRight.Controls.Add(grpQuality);

            // Sub-box 3: Uji Pencocokan 1:1
            GroupBox grpTest = new GroupBox {
                Text = " Uji Pencocokan 1:1 Biometrik ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 192),
                Width = 495,
                Height = 155
            };

            btnDiagSaveEnroll = new Button { 
                Text = "1. Rekam Template Uji", 
                Location = new Point(14, 26), 
                Width = 205, 
                Height = 34, 
                BackColor = Color.FromArgb(2, 132, 199), 
                ForeColor = Color.White, 
                FlatStyle = FlatStyle.Flat, 
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Enabled = false 
            };
            btnDiagSaveEnroll.FlatAppearance.BorderSize = 0;
            btnDiagSaveEnroll.Click += (s, e) => {
                if (diagLastFmd != null) {
                    diagEnrolledFmd = diagLastFmd;
                    btnDiagCompare.Enabled = true;
                    btnDiagCompare.BackColor = Color.FromArgb(16, 185, 129);
                    btnDiagCompare.ForeColor = Color.White;
                    lblDiagMatch.Text = "Template tersimpan. Tempelkan jari lagi lalu klik '2. Uji Kecocokan'.";
                    lblDiagMatch.ForeColor = Color.FromArgb(16, 185, 129);
                }
            };

            btnDiagCompare = new Button { 
                Text = "2. Uji Kecocokan", 
                Location = new Point(230, 26), 
                Width = 150, 
                Height = 34, 
                BackColor = Color.FromArgb(241, 245, 249), 
                FlatStyle = FlatStyle.Flat, 
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Enabled = false 
            };
            btnDiagCompare.Click += (s, e) => {
                if (diagEnrolledFmd != null && diagLastFmd != null) {
                    CompareResult cr = Comparison.Compare(diagLastFmd, 0, diagEnrolledFmd, 0);
                    int threshold = 2147483647 / 100000;
                    if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Score < threshold) {
                        lblDiagMatch.Text = "🎉 COCOK 100%! Sidik jari identik (Score: " + cr.Score + ")";
                        lblDiagMatch.ForeColor = Color.FromArgb(16, 185, 129);
                        MessageBox.Show("✅ HASIL UJI COCOK 100%!\n\nAlgoritma ANSI Biometrik berhasil mencocokkan sidik jari dengan template referensi.", "Hasil Verifikasi: COCOK", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    } else {
                        lblDiagMatch.Text = "❌ TIDAK COCOK! Jari berbeda.";
                        lblDiagMatch.ForeColor = Color.FromArgb(239, 68, 68);
                        MessageBox.Show("❌ HASIL UJI TIDAK COCOK!\n\nSensor mendeteksi bahwa jari berbeda. Sistem proteksi transaksi bekerja akurat.", "Hasil Verifikasi: BEDA", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
            };

            lblDiagMatch = new Label { 
                Text = "Tempelkan jari pada sensor untuk merekam template uji.", 
                Location = new Point(14, 70), 
                Width = 465, 
                Height = 70, 
                Font = new Font("Segoe UI", 8.5f),
                ForeColor = Color.FromArgb(100, 116, 139) 
            };
            grpTest.Controls.Add(btnDiagSaveEnroll);
            grpTest.Controls.Add(btnDiagCompare);
            grpTest.Controls.Add(lblDiagMatch);
            pnlDiagRight.Controls.Add(grpTest);

            // Bottom action buttons for Diagnostic
            Button btnRefreshScanner = new Button {
                Text = "🔄 Deteksi Ulang Scanner",
                Location = new Point(0, 358),
                Width = 235,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnRefreshScanner.Click += (s, e) => {
                StopDiagCapture();
                InitializeBiometrics();
                if (currentReader != null) {
                    lblDiagDevice.Text = "Perangkat: " + currentReader.Description.Id.ProductName;
                    lblDiagSerial.Text = "Serial ID: " + currentReader.Description.SerialNumber;
                }
            };
            pnlDiagRight.Controls.Add(btnRefreshScanner);

            Button btnOpenSdk = new Button {
                Text = "🛠️ Diagnostic Sample SDK",
                Location = new Point(245, 358),
                Width = 250,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnOpenSdk.Click += (s, e) => {
                string sdkPath = @"C:\Program Files\DigitalPersona\U.are.U SDK\Windows\Samples\Bin\.NET\UareUSampleCSharp.exe";
                if (File.Exists(sdkPath)) {
                    StopDiagCapture();
                    try { System.Diagnostics.Process.Start(sdkPath); } catch { }
                } else {
                    MessageBox.Show("File tidak ditemukan: " + sdkPath, "Info", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            };
            pnlDiagRight.Controls.Add(btnOpenSdk);

            grpDiag.Controls.Add(pnlDiagRight);
            p.Controls.Add(grpDiag);

            page.Controls.Add(p);
        }

        private void UpdateSplitterInfoText()
        {
            if (lblSplitterInfo != null)
            {
                int cartPct = (int)Math.Round((1.0 - this.savedSplitterRatio) * 100);
                int prodPct = 100 - cartPct;
                lblSplitterInfo.Text = string.Format("Ukuran Saat Ini: {0}% Katalog Produk  |  {1}% Keranjang Belanja\n(Posisi pemisah juga dapat langsung digeser dengan mouse di Tab Kasir, tersimpan otomatis).", prodPct, cartPct);
            }
        }
        #endregion

        #region SETTINGS METHODS
        private void LoadLocalSettings()
        {
            try
            {
                string path = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
                if (File.Exists(path))
                {
                    string json = File.ReadAllText(path);
                    var dict = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (dict != null)
                    {
                        if (dict.ContainsKey("api_url") && dict["api_url"] != null && !string.IsNullOrEmpty(dict["api_url"].ToString()))
                        {
                            this.apiUrl = NormalizeApiUrl(dict["api_url"].ToString().Trim());
                        }
                        if (dict.ContainsKey("splitter_ratio") && dict["splitter_ratio"] != null)
                        {
                            double r;
                            if (double.TryParse(dict["splitter_ratio"].ToString(), System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out r))
                            {
                                if (r >= 0.40 && r <= 0.85) this.savedSplitterRatio = r;
                                else this.savedSplitterRatio = 0.75;
                            }
                        }
                    }
                }
            }
            catch { }
        }

        private void SaveLocalSettings()
        {
            try
            {
                string path = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
                Dictionary<string, object> dict = new Dictionary<string, object>();
                dict["api_url"] = this.apiUrl;
                dict["splitter_ratio"] = this.savedSplitterRatio;
                File.WriteAllText(path, serializer.Serialize(dict));
            }
            catch { }
        }

        private void ApplySplitterRatio()
        {
            try
            {
                if (splitKasir != null && splitKasir.Width > 400)
                {
                    isApplyingSplitter = true;
                    double ratio = (this.savedSplitterRatio >= 0.40 && this.savedSplitterRatio <= 0.85)
                        ? this.savedSplitterRatio
                        : 0.75; // 75% left (katalog produk), 25% right (keranjang belanja)
                    int targetDist = (int)(splitKasir.Width * ratio);
                    int minLeft = 450;
                    int minRight = 260;
                    if (targetDist < minLeft) targetDist = minLeft;
                    if (splitKasir.Width - targetDist < minRight) targetDist = Math.Max(minLeft, splitKasir.Width - minRight);
                    if (targetDist > 0 && targetDist < splitKasir.Width)
                    {
                        splitKasir.SplitterDistance = targetDist;
                    }
                }
            }
            catch { }
            finally
            {
                isApplyingSplitter = false;
            }
        }

        private string NormalizeApiUrl(string input)
        {
            if (string.IsNullOrEmpty(input)) return "http://uangpas.test/api/v1";
            string s = input.Trim().TrimEnd('/');
            if (!s.StartsWith("http://", StringComparison.OrdinalIgnoreCase) && !s.StartsWith("https://", StringComparison.OrdinalIgnoreCase))
            {
                s = "http://" + s;
            }
            try
            {
                Uri uri = new Uri(s);
                string scheme = uri.Scheme;
                string host = uri.Host;
                int port = uri.Port;

                string authority;
                if (uri.IsDefaultPort || (scheme.Equals("http", StringComparison.OrdinalIgnoreCase) && port == 80) || (scheme.Equals("https", StringComparison.OrdinalIgnoreCase) && port == 443))
                {
                    authority = host;
                }
                else
                {
                    authority = host + ":" + port;
                }

                string path = uri.AbsolutePath.TrimEnd('/');
                if (string.IsNullOrEmpty(path) || path == "/")
                {
                    path = "/api/v1";
                }
                else if (!path.EndsWith("/api/v1", StringComparison.OrdinalIgnoreCase))
                {
                    if (path.EndsWith("/api", StringComparison.OrdinalIgnoreCase)) path += "/v1";
                    else path += "/api/v1";
                }
                return scheme + "://" + authority + path;
            }
            catch
            {
                return s;
            }
        }

        private string GetDisplayServerAddress(string url)
        {
            try
            {
                if (string.IsNullOrEmpty(url)) return "uangpas.test";
                Uri uri = new Uri(url);
                if (uri.IsDefaultPort || uri.Port == 80 || uri.Port == 443)
                {
                    return uri.Host;
                }
                return uri.Host + ":" + uri.Port;
            }
            catch
            {
                return url ?? "uangpas.test";
            }
        }

        private void ApplyNewServerUrl(string rawUrl)
        {
            string normalized = NormalizeApiUrl(rawUrl);
            this.apiUrl = normalized;
            if (txtServerAddress != null) txtServerAddress.Text = GetDisplayServerAddress(normalized);
            SaveLocalSettings();

            if (lblCurrentServerInfo != null)
            {
                lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n⏳ Menghubungkan & menyinkronkan data...";
                lblCurrentServerInfo.ForeColor = Color.FromArgb(2, 132, 199);
            }

            ThreadPool.QueueUserWorkItem(_ => {
                TestCurrentConnection();
                SyncDataFromApi();
            });

            MessageBox.Show("Alamat Server UangPas berhasil diperbarui ke:\n" + this.apiUrl + "\n\nSistem sedang menyinkronkan database produk dan WBP.", "Koneksi Disimpan", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }

        private void TestCurrentConnection()
        {
            try
            {
                Stopwatch sw = Stopwatch.StartNew();
                string pingUrl = this.apiUrl.TrimEnd('/') + "/sync/ping";
                string json = HttpGet(pingUrl);
                sw.Stop();

                string uptName = "Server UangPas";
                try
                {
                    var data = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (data != null && data.ContainsKey("upt_name") && data["upt_name"] != null)
                        uptName = data["upt_name"].ToString();
                }
                catch { }

                this.BeginInvoke((Action)(() => {
                    if (lblCurrentServerInfo != null)
                    {
                        lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n✅ Terhubung ke " + uptName + " (" + sw.ElapsedMilliseconds + " ms latency)";
                        lblCurrentServerInfo.ForeColor = Color.FromArgb(16, 185, 129);
                    }
                    if (lblApiStatus != null)
                    {
                        lblApiStatus.Text = "Server: Online (" + productList.Count + " Produk)";
                        lblApiStatus.ForeColor = Color.FromArgb(52, 211, 153);
                    }
                }));
            }
            catch (Exception ex)
            {
                this.BeginInvoke((Action)(() => {
                    if (lblCurrentServerInfo != null)
                    {
                        lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n❌ Tidak dapat terhubung: " + ex.Message;
                        lblCurrentServerInfo.ForeColor = Color.FromArgb(239, 68, 68);
                    }
                    if (lblApiStatus != null)
                    {
                        lblApiStatus.Text = "Server: Offline";
                        lblApiStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    }
                }));
            }
        }
        #endregion

        #region SENSOR DIAGNOSTIC METHODS
        private void ToggleDiagCapture()
        {
            if (isDiagCapturing) StopDiagCapture();
            else StartDiagCapture();
        }

        private void StartDiagCapture()
        {
            try
            {
                StopDiagCapture();

                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers == null || readers.Count == 0)
                {
                    lblDiagStatus.Text = "Scanner sidik jari tidak ditemukan. Periksa kabel USB!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Scanner sidik jari tidak ditemukan. Periksa kabel USB.", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    return;
                }

                currentReader = readers[0];
                Constants.ResultCode resOpen = currentReader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (resOpen != Constants.ResultCode.DP_SUCCESS && resOpen != Constants.ResultCode.DP_DEVICE_BUSY)
                {
                    lblDiagStatus.Text = "Gagal buka scanner: " + resOpen + ".\nPastikan aplikasi penguji lain sudah ditutup!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Gagal mengakses scanner (" + resOpen + ")!\n\nKemungkinan scanner masih digunakan oleh aplikasi lain.\nSilakan tutup aplikasi penguji tersebut terlebih dahulu.", "Scanner Sibuk", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }

                currentReader.On_Captured += Diag_OnCaptured;
                currentReader.GetStatus();

                int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                    ? currentReader.Capabilities.Resolutions[0] : 500;

                diagSw.Restart();
                Constants.ResultCode rc = currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);
                if (rc == Constants.ResultCode.DP_SUCCESS)
                {
                    isDiagCapturing = true;
                    btnDiagToggle.Text = "⏹️ Hentikan Pengujian";
                    btnDiagToggle.BackColor = Color.FromArgb(239, 68, 68);
                    lblDiagStatus.Text = "🔴 Sensor Aktif! TEMPELKAN JARI PADA SCANNER SEKARANG...";
                    lblDiagStatus.ForeColor = Color.FromArgb(2, 132, 199);
                }
                else
                {
                    lblDiagStatus.Text = "CaptureAsync gagal: " + rc;
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
            catch (Exception ex)
            {
                lblDiagStatus.Text = "Error: " + ex.Message;
                lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
            }
        }

        private void StopDiagCapture()
        {
            if (currentReader != null)
            {
                try
                {
                    currentReader.CancelCapture();
                    currentReader.On_Captured -= Diag_OnCaptured;
                    currentReader.Dispose();
                }
                catch { }
                currentReader = null;
            }
            isDiagCapturing = false;
            if (btnDiagToggle != null)
            {
                btnDiagToggle.Text = "▶️ Mulai Pengujian Sensor";
                btnDiagToggle.BackColor = Color.FromArgb(16, 185, 129);
            }
            if (lblDiagStatus != null)
            {
                lblDiagStatus.Text = "Sensor dalam keadaan siaga (standby).";
                lblDiagStatus.ForeColor = Color.FromArgb(100, 116, 139);
            }
        }

        private void Diag_OnCaptured(CaptureResult cr)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action<CaptureResult>(Diag_OnCaptured), cr);
                return;
            }

            diagSw.Stop();
            long ms = diagSw.ElapsedMilliseconds;

            if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Data != null)
            {
                diagScanCount++;
                try { System.Media.SystemSounds.Beep.Play(); } catch { }

                if (cr.Data.Views != null && cr.Data.Views.Count > 0)
                {
                    var view = cr.Data.Views[0];
                    if (picDiagFinger != null)
                    {
                        if (picDiagFinger.Image != null) picDiagFinger.Image.Dispose();
                        picDiagFinger.Image = Create24bppBitmapStatic(view.RawImage, view.Width, view.Height);
                    }
                }

                DataResult<Fmd> res = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                if (res.ResultCode == Constants.ResultCode.DP_SUCCESS && res.Data != null)
                {
                    diagLastFmd = res.Data;
                    if (btnDiagSaveEnroll != null) btnDiagSaveEnroll.Enabled = true;
                    if (diagEnrolledFmd != null && btnDiagCompare != null) {
                        btnDiagCompare.Enabled = true;
                    }
                }

                if (lblDiagStatus != null)
                {
                    lblDiagStatus.Text = "🟢 JARI TERDETEKSI! (Scan #" + diagScanCount + " Sukses)";
                    lblDiagStatus.ForeColor = Color.FromArgb(16, 185, 129);
                }

                if (lblDiagQuality != null)
                {
                    lblDiagQuality.Text = "Kualitas: " + cr.Quality + " (Bagus)";
                    lblDiagQuality.ForeColor = Color.FromArgb(16, 185, 129);
                }

                if (lblDiagLatency != null)
                {
                    lblDiagLatency.Text = "Kecepatan Respon: " + ms + " ms (Scan #" + diagScanCount + ")";
                }

                // Continue capture
                RestartDiagCapture();
            }
            else
            {
                if (lblDiagQuality != null)
                {
                    lblDiagQuality.Text = "Kualitas: " + cr.Quality;
                    lblDiagQuality.ForeColor = Color.FromArgb(239, 68, 68);
                }
                RestartDiagCapture();
            }
        }

        private void RestartDiagCapture()
        {
            if (currentReader != null && isDiagCapturing)
            {
                try
                {
                    diagSw.Restart();
                    int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                        ? currentReader.Capabilities.Resolutions[0] : 500;
                    currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);
                }
                catch { }
            }
        }
        #endregion
    }
}

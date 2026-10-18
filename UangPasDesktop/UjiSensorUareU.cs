using System;
using System.IO;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
using System.Collections.Generic;
using System.Diagnostics;
using DPUruNet;

namespace UangPasDesktop
{
    static class DiagnosticProgram
    {
        [MTAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new DiagnosticForm());
        }
    }

    public class DiagnosticForm : Form
    {
        private Reader currentReader;
        private ReaderCollection readers;
        private bool isCapturing = false;
        private int scanCount = 0;
        private Stopwatch sw = new Stopwatch();

        // Template comparison test
        private Fmd enrolledFmd = null;
        private Fmd lastCapturedFmd = null;

        // UI Controls - Header
        private Label lblStatusBadge;
        private Label lblDeviceDetails;

        // UI Controls - Left
        private PictureBox picFinger;
        private Label lblInstruction;
        private Button btnStartStop;

        // UI Controls - Right
        private Label lblDeviceName;
        private Label lblSerial;
        private Label lblResolution;
        private Label lblQuality;
        private Label lblLatency;
        private Label lblMatchResult;
        private Button btnSaveEnroll;
        private Button btnCompare;
        private ListBox listLog;
        private Button btnOpenOfficialSample;
        private Button btnRefresh;

        public DiagnosticForm()
        {
            this.Text = "UangPas - Alat Uji Hardware Fingerprint U.are.U 4500 (Diagnostic & Test Tool)";
            this.Size = new Size(1060, 740);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.MinimumSize = new Size(900, 600);
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            this.BackColor = Color.FromArgb(241, 245, 249);
            this.AutoScroll = true;

            BuildUI();

            this.Shown += (s, e) => DetectAndOpenReader();

            this.FormClosing += (s, e) => {
                StopCaptureInternal();
                if (currentReader != null)
                {
                    try { currentReader.Dispose(); } catch { }
                    currentReader = null;
                }
            };
        }

        private void BuildUI()
        {
            // 1. TOP BAR (Always visible, prominent status header)
            Panel topBar = new Panel {
                Dock = DockStyle.Top,
                Height = 84,
                BackColor = Color.FromArgb(15, 23, 42),
                Padding = new Padding(20, 12, 20, 12)
            };

            Label lblTitle = new Label {
                Text = "🔬 Uji Hardware & Diagnostik Scanner U.are.U 4500",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(20, 14)
            };
            Label lblSub = new Label {
                Text = "UangPas Dompet Digital Lapas - Pengujian Fisik Scanner & Biometrik ANSI",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9f),
                AutoSize = true,
                Location = new Point(22, 44)
            };
            topBar.Controls.Add(lblTitle);
            topBar.Controls.Add(lblSub);

            // Prominent Status Badge on TOP RIGHT
            lblStatusBadge = new Label {
                Text = "MEMERIKSA SCANNER...",
                ForeColor = Color.FromArgb(251, 191, 36),
                Font = new Font("Segoe UI", 11.5f, FontStyle.Bold),
                AutoSize = true,
                Anchor = AnchorStyles.Top | AnchorStyles.Right,
                Location = new Point(640, 14),
                TextAlign = ContentAlignment.TopRight
            };
            lblDeviceDetails = new Label {
                Text = "Mendeteksi perangkat USB...",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9f),
                AutoSize = true,
                Anchor = AnchorStyles.Top | AnchorStyles.Right,
                Location = new Point(640, 44),
                TextAlign = ContentAlignment.TopRight
            };
            topBar.Controls.Add(lblStatusBadge);
            topBar.Controls.Add(lblDeviceDetails);
            this.Controls.Add(topBar);

            // 1B. NAVIGATION BANNER TO MAIN POS & ENROLLMENT APP
            Panel navBanner = new Panel {
                Dock = DockStyle.Top,
                Height = 54,
                BackColor = Color.FromArgb(238, 242, 255),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(20, 8, 20, 8)
            };

            Label lblBannerText = new Label {
                Text = "💡 Aplikasi ini KHUSUS UJI SENSOR. Untuk MEREKAM sidik jari WBP & TRANSAKSI KASIR, gunakan Aplikasi Kasir POS 👉",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(67, 56, 202),
                AutoSize = true,
                Location = new Point(20, 16)
            };
            navBanner.Controls.Add(lblBannerText);

            Button btnSwitchToPos = new Button {
                Text = "🚀 Buka Menu Rekam Jari & Kasir POS",
                Location = new Point(710, 8),
                Width = 310,
                Height = 36,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Anchor = AnchorStyles.Top | AnchorStyles.Right,
                Cursor = Cursors.Hand
            };
            btnSwitchToPos.Click += (s, e) => LaunchMainPOSApp();
            navBanner.Controls.Add(btnSwitchToPos);

            this.Controls.Add(navBanner);

            // 2. MAIN SCROLLABLE CONTENT PANEL
            Panel contentPanel = new Panel {
                Dock = DockStyle.Fill,
                AutoScroll = true,
                Padding = new Padding(20)
            };

            // LEFT COLUMN (Preview & Trigger)
            Panel leftCol = new Panel {
                Location = new Point(20, 16),
                Width = 380,
                Height = 580,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(16)
            };

            Label lblPreviewHead = new Label {
                Text = "📷 Visual Sidik Jari Langsung",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(16, 12),
                AutoSize = true
            };
            leftCol.Controls.Add(lblPreviewHead);

            picFinger = new PictureBox {
                Location = new Point(16, 42),
                Width = 344,
                Height = 360,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            leftCol.Controls.Add(picFinger);

            lblInstruction = new Label {
                Text = "Klik tombol di bawah untuk mengaktifkan sensor.",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(16, 412),
                Width = 344,
                Height = 50,
                TextAlign = ContentAlignment.MiddleCenter
            };
            leftCol.Controls.Add(lblInstruction);

            btnStartStop = new Button {
                Text = "▶️ AKTIFKAN SENSOR (MULAI UJI)",
                Location = new Point(16, 470),
                Width = 344,
                Height = 52,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 11.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnStartStop.Click += (s, e) => ToggleCapture();
            leftCol.Controls.Add(btnStartStop);

            contentPanel.Controls.Add(leftCol);

            // RIGHT COLUMN (Diagnostics & Verification)
            Panel rightCol = new Panel {
                Location = new Point(420, 16),
                Width = 590,
                Height = 580,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(16),
                Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right
            };

            // Group: Hardware Info
            GroupBox grpHardware = new GroupBox {
                Text = " 1. Status Hardware Scanner ",
                Location = new Point(16, 12),
                Width = 554,
                Height = 110,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right
            };
            lblDeviceName = new Label { Text = "Nama Perangkat : Mendeteksi...", Location = new Point(16, 26), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            lblSerial = new Label { Text = "Nomor Seri : -", Location = new Point(16, 52), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            lblResolution = new Label { Text = "Resolusi Sensor : 500 DPI (ANSI Standard)", Location = new Point(16, 78), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            grpHardware.Controls.Add(lblDeviceName);
            grpHardware.Controls.Add(lblSerial);
            grpHardware.Controls.Add(lblResolution);
            rightCol.Controls.Add(grpHardware);

            // Group: Last Scan Result
            GroupBox grpQuality = new GroupBox {
                Text = " 2. Hasil Pembacaan Terakhir ",
                Location = new Point(16, 130),
                Width = 554,
                Height = 80,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right
            };
            lblQuality = new Label { Text = "Kualitas Citra : Belum ada sentuhan jari", Location = new Point(16, 24), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            lblLatency = new Label { Text = "Kecepatan Respon : -", Location = new Point(16, 50), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            grpQuality.Controls.Add(lblQuality);
            grpQuality.Controls.Add(lblLatency);
            rightCol.Controls.Add(grpQuality);

            // Group: 1:1 Matching
            GroupBox grpMatch = new GroupBox {
                Text = " 3. Uji Kecocokan Biometrik 1:1 (ANSI Template Match) ",
                Location = new Point(16, 218),
                Width = 554,
                Height = 116,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right
            };

            btnSaveEnroll = new Button {
                Text = "1. Jadikan Template Uji",
                Location = new Point(16, 26),
                Width = 190,
                Height = 36,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Enabled = false
            };
            btnSaveEnroll.Click += (s, e) => SaveLastAsEnrollment();

            btnCompare = new Button {
                Text = "2. Cocokkan Jari",
                Location = new Point(216, 26),
                Width = 150,
                Height = 36,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Enabled = false
            };
            btnCompare.Click += (s, e) => CompareLastWithEnrolled();

            lblMatchResult = new Label {
                Text = "Tempelkan jari terlebih dahulu untuk mengaktifkan pengujian pencocokan.",
                Location = new Point(16, 72),
                Width = 520,
                Height = 32,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139)
            };
            grpMatch.Controls.Add(btnSaveEnroll);
            grpMatch.Controls.Add(btnCompare);
            grpMatch.Controls.Add(lblMatchResult);
            rightCol.Controls.Add(grpMatch);

            // Activity Log
            Label lblLogTitle = new Label {
                Text = "Log Aktivitas Scanner:",
                Location = new Point(16, 342),
                AutoSize = true,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold)
            };
            rightCol.Controls.Add(lblLogTitle);

            listLog = new ListBox {
                Location = new Point(16, 366),
                Width = 554,
                Height = 140,
                Font = new Font("Consolas", 8.5f),
                BackColor = Color.FromArgb(248, 250, 252),
                Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right
            };
            rightCol.Controls.Add(listLog);

            // Bottom Buttons
            btnOpenOfficialSample = new Button {
                Text = "🛠️ Buka Tool Resmi DigitalPersona SDK",
                Location = new Point(16, 518),
                Width = 320,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold)
            };
            btnOpenOfficialSample.Click += (s, e) => LaunchOfficialSdkSample();
            rightCol.Controls.Add(btnOpenOfficialSample);

            btnRefresh = new Button {
                Text = "🔄 Deteksi Ulang",
                Location = new Point(346, 518),
                Width = 140,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold)
            };
            btnRefresh.Click += (s, e) => DetectAndOpenReader();
            rightCol.Controls.Add(btnRefresh);

            contentPanel.Controls.Add(rightCol);
            this.Controls.Add(contentPanel);
            topBar.BringToFront();
        }

        private void Log(string msg)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action<string>(Log), msg);
                return;
            }
            string time = DateTime.Now.ToString("HH:mm:ss");
            listLog.Items.Insert(0, "[" + time + "] " + msg);
        }

        private void DetectAndOpenReader()
        {
            try
            {
                StopCaptureInternal();
                Log("Mendeteksi scanner DigitalPersona U.are.U 4500...");
                readers = ReaderCollection.GetReaders();

                if (readers == null || readers.Count == 0)
                {
                    lblStatusBadge.Text = "⚠️ SCANNER TIDAK TERDETEKSI";
                    lblStatusBadge.ForeColor = Color.FromArgb(239, 68, 68);
                    lblDeviceDetails.Text = "Pastikan kabel USB U.are.U terpasang di komputer!";
                    
                    lblDeviceName.Text = "Nama Perangkat : Tidak ada perangkat terdeteksi";
                    lblSerial.Text = "Nomor Seri : -";
                    lblResolution.Text = "Resolusi : -";
                    
                    lblInstruction.Text = "Kabel USB belum terdeteksi. Hubungkan alat ke port USB.";
                    lblInstruction.ForeColor = Color.FromArgb(239, 68, 68);
                    btnStartStop.Enabled = false;
                    Log("ERROR: Tidak ada scanner U.are.U terdeteksi.");
                    return;
                }

                currentReader = readers[0];
                string prodName = currentReader.Description.Id.ProductName;
                string serial = currentReader.Description.SerialNumber;
                int res = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                    ? currentReader.Capabilities.Resolutions[0] : 500;

                lblStatusBadge.Text = "✅ TERHUBUNG & SIAP";
                lblStatusBadge.ForeColor = Color.FromArgb(52, 211, 153);
                lblDeviceDetails.Text = prodName + " (" + res + " DPI)";

                lblDeviceName.Text = "Nama Perangkat : " + prodName;
                lblSerial.Text = "Nomor Seri : " + serial;
                lblResolution.Text = "Resolusi Sensor : " + res + " DPI (ANSI Standard)";

                lblInstruction.Text = "Scanner siap! Klik tombol 'AKTIFKAN SENSOR' untuk mulai.";
                lblInstruction.ForeColor = Color.FromArgb(2, 132, 199);
                btnStartStop.Enabled = true;

                Log("Scanner terdeteksi: " + prodName + " (Serial: " + serial + ")");
                Log("Klik tombol 'AKTIFKAN SENSOR' untuk menguji pembacaan jari.");
            }
            catch (Exception ex)
            {
                lblStatusBadge.Text = "ERROR INISIALISASI";
                lblStatusBadge.ForeColor = Color.FromArgb(239, 68, 68);
                lblDeviceDetails.Text = ex.Message;
                Log("Exception: " + ex.Message);
            }
        }

        private void ToggleCapture()
        {
            if (isCapturing)
            {
                StopCaptureInternal();
            }
            else
            {
                StartCaptureInternal();
            }
        }

        private void StartCaptureInternal()
        {
            if (currentReader == null)
            {
                DetectAndOpenReader();
                if (currentReader == null) return;
            }

            try
            {
                Constants.ResultCode resOpen = currentReader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (resOpen != Constants.ResultCode.DP_SUCCESS && resOpen != Constants.ResultCode.DP_DEVICE_BUSY)
                {
                    Log("Gagal membuka scanner: " + resOpen);
                    lblInstruction.Text = "Gagal membuka scanner: " + resOpen;
                    lblInstruction.ForeColor = Color.FromArgb(239, 68, 68);
                    return;
                }

                currentReader.On_Captured += Reader_OnCaptured;
                currentReader.GetStatus();

                int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                    ? currentReader.Capabilities.Resolutions[0] : 500;

                sw.Restart();
                Constants.ResultCode resCap = currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);

                if (resCap == Constants.ResultCode.DP_SUCCESS)
                {
                    isCapturing = true;
                    btnStartStop.Text = "⏹️ HENTIKAN PENGUJIAN";
                    btnStartStop.BackColor = Color.FromArgb(239, 68, 68);
                    lblInstruction.Text = "🔴 Sensor Aktif! TEMPELKAN JARI PADA SCANNER SEKARANG...";
                    lblInstruction.ForeColor = Color.FromArgb(2, 132, 199);
                    Log("CaptureAsync aktif. Sensor menyala, siap membaca sidik jari.");
                }
                else
                {
                    Log("CaptureAsync gagal: " + resCap);
                    lblInstruction.Text = "Gagal memulai capture: " + resCap;
                    lblInstruction.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
            catch (Exception ex)
            {
                Log("Error StartCapture: " + ex.Message);
            }
        }

        private void StopCaptureInternal()
        {
            if (currentReader != null && isCapturing)
            {
                try
                {
                    currentReader.CancelCapture();
                    currentReader.On_Captured -= Reader_OnCaptured;
                    currentReader.Dispose();
                    currentReader = null;
                }
                catch { }

                isCapturing = false;
                btnStartStop.Text = "▶️ AKTIFKAN SENSOR (MULAI UJI)";
                btnStartStop.BackColor = Color.FromArgb(16, 185, 129);
                lblInstruction.Text = "Sensor dalam keadaan siaga (standby).";
                lblInstruction.ForeColor = Color.FromArgb(100, 116, 139);
                Log("Sensor dihentikan (standby).");

                try {
                    readers = ReaderCollection.GetReaders();
                    if (readers.Count > 0) currentReader = readers[0];
                } catch { }
            }
        }

        private void Reader_OnCaptured(CaptureResult cr)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action<CaptureResult>(Reader_OnCaptured), cr);
                return;
            }

            sw.Stop();
            long latencyMs = sw.ElapsedMilliseconds;

            if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Data != null)
            {
                scanCount++;
                System.Media.SystemSounds.Beep.Play();

                // 1. Render Bitmap
                if (cr.Data.Views != null && cr.Data.Views.Count > 0)
                {
                    var view = cr.Data.Views[0];
                    Bitmap bmp = Create24bppBitmap(view.RawImage, view.Width, view.Height);
                    if (picFinger.Image != null) picFinger.Image.Dispose();
                    picFinger.Image = bmp;

                    Log("Scan #" + scanCount + " SUKSES: " + view.Width + "x" + view.Height + " px, 500 DPI (" + latencyMs + " ms)");
                }

                // 2. Extract FMD
                DataResult<Fmd> resFmd = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                if (resFmd.ResultCode == Constants.ResultCode.DP_SUCCESS && resFmd.Data != null)
                {
                    lastCapturedFmd = resFmd.Data;
                    btnSaveEnroll.Enabled = true;
                    if (enrolledFmd != null) btnCompare.Enabled = true;
                    Log("ANSI FMD Minutiae berhasil diekstrak (" + resFmd.Data.Bytes.Length + " bytes).");
                }

                lblInstruction.Text = "🟢 JARI TERDETEKSI! (Scan #" + scanCount + " Sukses)";
                lblInstruction.ForeColor = Color.FromArgb(16, 185, 129);

                lblQuality.Text = "Kualitas Citra : " + cr.Quality.ToString() + " (Optimal)";
                lblQuality.ForeColor = Color.FromArgb(16, 185, 129);

                lblLatency.Text = "Kecepatan Respon : " + latencyMs + " ms (Scan #" + scanCount + ")";

                // Continue capture for live testing
                RestartCaptureAsync();
            }
            else
            {
                Log("Kualitas kurang: " + cr.Quality + " (" + cr.ResultCode + ")");
                lblQuality.Text = "Kualitas : " + cr.Quality;
                lblQuality.ForeColor = Color.FromArgb(239, 68, 68);
                RestartCaptureAsync();
            }
        }

        private void RestartCaptureAsync()
        {
            if (currentReader != null && isCapturing)
            {
                try
                {
                    sw.Restart();
                    int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                        ? currentReader.Capabilities.Resolutions[0] : 500;
                    currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);
                }
                catch (Exception ex)
                {
                    Log("Restart Capture error: " + ex.Message);
                }
            }
        }

        private void SaveLastAsEnrollment()
        {
            if (lastCapturedFmd != null)
            {
                enrolledFmd = lastCapturedFmd;
                btnCompare.Enabled = true;
                btnCompare.BackColor = Color.FromArgb(16, 185, 129);
                btnCompare.ForeColor = Color.White;
                lblMatchResult.Text = "✅ Template tersimpan! Tempelkan jari lagi lalu klik '2. Cocokkan Jari'.";
                lblMatchResult.ForeColor = Color.FromArgb(16, 185, 129);
                Log("Template referensi tersimpan. Siap dicocokkan.");
            }
        }

        private void CompareLastWithEnrolled()
        {
            if (enrolledFmd == null || lastCapturedFmd == null)
            {
                MessageBox.Show("Silakan sentuh scanner untuk mengambil sidik jari terlebih dahulu.", "Info", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            try
            {
                CompareResult cr = Comparison.Compare(lastCapturedFmd, 0, enrolledFmd, 0);
                int threshold = 2147483647 / 100000;

                if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS)
                {
                    if (cr.Score < threshold)
                    {
                        lblMatchResult.Text = "🎉 COCOK 100%! Sidik Jari Identik (Score: " + cr.Score + ")";
                        lblMatchResult.ForeColor = Color.FromArgb(16, 185, 129);
                        Log("VERIFIKASI: COCOK! Score: " + cr.Score);
                        MessageBox.Show("✅ SIDIK JARI COCOK 100%!\n\nAlgoritma ANSI Biometrik berhasil memverifikasi bahwa jari identik dengan template referensi.", "Hasil: COCOK", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }
                    else
                    {
                        lblMatchResult.Text = "❌ TIDAK COCOK! Jari Berbeda (Score: " + cr.Score + ")";
                        lblMatchResult.ForeColor = Color.FromArgb(239, 68, 68);
                        Log("VERIFIKASI: TIDAK COCOK. Score: " + cr.Score);
                        MessageBox.Show("❌ SIDIK JARI TIDAK COCOK!\n\nSensor mendeteksi bahwa jari berbeda. Sistem proteksi biometrik bekerja akurat.", "Hasil: TIDAK COCOK", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
            }
            catch (Exception ex)
            {
                Log("Error Compare: " + ex.Message);
            }
        }

        private void LaunchOfficialSdkSample()
        {
            string samplePath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "UareUSampleCSharp.exe");
            if (File.Exists(samplePath))
            {
                StopCaptureInternal();
                try
                {
                    Process.Start(samplePath);
                    Log("Menjalankan tool resmi DigitalPersona SDK: " + samplePath);
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Gagal membuka tool resmi: " + ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
            else
            {
                MessageBox.Show("File tool resmi tidak ditemukan di:\n" + samplePath, "Tidak Ditemukan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }

        private void LaunchMainPOSApp()
        {
            StopCaptureInternal();
            if (currentReader != null)
            {
                try { currentReader.Dispose(); } catch { }
                currentReader = null;
            }

            string mainExe = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "UangPasDesktop.exe");
            if (File.Exists(mainExe))
            {
                try
                {
                    Process.Start(mainExe);
                    this.Close();
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Gagal membuka aplikasi kasir: " + ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
            else
            {
                MessageBox.Show("File aplikasi kasir tidak ditemukan di:\n" + mainExe, "Error", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }

        private Bitmap Create24bppBitmap(byte[] bytes, int width, int height)
        {
            try
            {
                byte[] rgbBytes = new byte[bytes.Length * 3];
                for (int i = 0; i <= bytes.Length - 1; i++)
                {
                    rgbBytes[(i * 3)] = bytes[i];
                    rgbBytes[(i * 3) + 1] = bytes[i];
                    rgbBytes[(i * 3) + 2] = bytes[i];
                }
                Bitmap bmp = new Bitmap(width, height, PixelFormat.Format24bppRgb);
                BitmapData data = bmp.LockBits(new Rectangle(0, 0, bmp.Width, bmp.Height), ImageLockMode.WriteOnly, PixelFormat.Format24bppRgb);
                for (int i = 0; i <= bmp.Height - 1; i++)
                {
                    IntPtr p = new IntPtr(data.Scan0.ToInt64() + data.Stride * i);
                    System.Runtime.InteropServices.Marshal.Copy(rgbBytes, i * bmp.Width * 3, p, bmp.Width * 3);
                }
                bmp.UnlockBits(data);
                return bmp;
            }
            catch
            {
                return null;
            }
        }
    }
}

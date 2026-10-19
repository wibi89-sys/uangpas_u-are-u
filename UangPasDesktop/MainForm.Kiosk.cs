using System;
using System.Collections.Generic;
using System.Drawing;
using System.Threading;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public partial class MainForm
    {
        #region TAB 3: KIOSK CEK SALDO MANDIRI
        private void BuildKioskTab(TabPage page)
        {
            Panel p = new Panel { Dock = DockStyle.Fill, Padding = new Padding(24), AutoScroll = true, BackColor = Color.FromArgb(248, 250, 252) };

            Label lblTitle = new Label {
                Text = "🔍 Kios Pengecekan Saldo & Status Dompet WBP (Self-Service)",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                AutoSize = true,
                Location = new Point(24, 16)
            };
            p.Controls.Add(lblTitle);

            Label lblSub = new Label {
                Text = "Pengecekan saldo dapat dilakukan dengan Scan Sidik Jari Biometrik atau Scan Barcode Kartu / Ketik No. Registrasi WBP.",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true,
                Location = new Point(24, 44)
            };
            p.Controls.Add(lblSub);

            // ==========================================
            // SEARCH & BARCODE SCAN BAR
            // ==========================================
            Panel pnlSearch = new Panel {
                Location = new Point(24, 76),
                Width = 920,
                Height = 62,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(12, 10, 12, 10)
            };

            Label lblSearchIcon = new Label {
                Text = "💳 Scan Barcode / Ketik No. Registrasi / NIK / Nama:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(10, 7),
                AutoSize = true
            };
            pnlSearch.Controls.Add(lblSearchIcon);

            txtKioskSearch = new TextBox {
                Location = new Point(12, 28),
                Width = 530,
                Font = new Font("Segoe UI", 11.5f),
                ForeColor = Color.FromArgb(15, 23, 42)
            };
            txtKioskSearch.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    DoKioskSearch(txtKioskSearch.Text.Trim());
                }
            };
            pnlSearch.Controls.Add(txtKioskSearch);

            btnKioskSearch = new Button {
                Text = "🔍 Cek Saldo [Enter]",
                Location = new Point(552, 26),
                Width = 160,
                Height = 32,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnKioskSearch.FlatAppearance.BorderSize = 0;
            btnKioskSearch.Click += (s, e) => DoKioskSearch(txtKioskSearch.Text.Trim());
            pnlSearch.Controls.Add(btnKioskSearch);

            btnKioskReset = new Button {
                Text = "🔄 Bersihkan",
                Location = new Point(722, 26),
                Width = 110,
                Height = 32,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnKioskReset.Click += (s, e) => ResetKioskDisplay();
            pnlSearch.Controls.Add(btnKioskReset);

            p.Controls.Add(pnlSearch);

            // ==========================================
            // LEFT COLUMN: SENSOR BIOMETRIK
            // ==========================================
            GroupBox grpSensor = new GroupBox {
                Text = " 👆 Scan Sidik Jari Biometrik ",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, 150),
                Width = 260,
                Height = 420,
                BackColor = Color.White
            };

            picKioskFinger = new PictureBox {
                Location = new Point(24, 30),
                Width = 210,
                Height = 240,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            grpSensor.Controls.Add(picKioskFinger);

            btnStartKioskScan = new Button {
                Text = "👆 Scan Sidik Jari WBP",
                Location = new Point(24, 282),
                Width = 210,
                Height = 46,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnStartKioskScan.FlatAppearance.BorderSize = 0;
            btnStartKioskScan.Click += (s, e) => DoKioskScan();
            grpSensor.Controls.Add(btnStartKioskScan);

            Label lblSensorHint = new Label {
                Text = "Minta WBP menempelkan jari pada sensor untuk menampilkan saldo secara otomatis.",
                Font = new Font("Segoe UI", 8.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(24, 340),
                Width = 210,
                Height = 65,
                TextAlign = ContentAlignment.TopCenter
            };
            grpSensor.Controls.Add(lblSensorHint);

            p.Controls.Add(grpSensor);

            // ==========================================
            // RIGHT COLUMN: DIGITAL WALLET CARD
            // ==========================================
            Panel infoCard = new Panel {
                Location = new Point(302, 150),
                Width = 642,
                Height = 420,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(20)
            };

            Label lblCardTitle = new Label {
                Text = "KARTU DOMPET DIGITAL WARGA BINAAN",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(20, 16),
                Width = 360,
                Height = 18
            };
            infoCard.Controls.Add(lblCardTitle);

            lblKioskStatusBadge = new Label {
                Text = "● MENUNGGU SCAN / PENCARIAN",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(360, 14),
                Width = 250,
                Height = 22,
                TextAlign = ContentAlignment.MiddleRight
            };
            infoCard.Controls.Add(lblKioskStatusBadge);

            lblKioskNama = new Label {
                Text = "Nama WBP: -",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(20, 38),
                Width = 590,
                Height = 32
            };
            infoCard.Controls.Add(lblKioskNama);

            lblKioskNoReg = new Label {
                Text = "No. Registrasi: -",
                Font = new Font("Consolas", 10.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(20, 72),
                Width = 300,
                Height = 22
            };
            infoCard.Controls.Add(lblKioskNoReg);

            lblKioskKamar = new Label {
                Text = "Kamar / Blok: -",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(330, 72),
                Width = 280,
                Height = 22
            };
            infoCard.Controls.Add(lblKioskKamar);

            lblKioskSanctionWarning = new Label {
                Text = "",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(220, 38, 38),
                Location = new Point(20, 96),
                Width = 590,
                Height = 24
            };
            infoCard.Controls.Add(lblKioskSanctionWarning);

            // SISA SALDO BOX (Guaranteed ample height, NO text clipping)
            Panel pnlSaldoBox = new Panel {
                Location = new Point(20, 124),
                Width = 598,
                Height = 110,
                BackColor = Color.FromArgb(236, 253, 245),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(16, 10, 16, 10)
            };
            Label lblSaldoHeader = new Label {
                Text = "💰 SISA SALDO DOMPET ELEKTRONIK:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(4, 120, 87),
                Location = new Point(14, 10),
                Width = 400,
                Height = 20
            };
            pnlSaldoBox.Controls.Add(lblSaldoHeader);

            lblKioskSaldo = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 24f, FontStyle.Bold),
                ForeColor = Color.FromArgb(5, 150, 105),
                Location = new Point(14, 34),
                Width = 560,
                Height = 60,
                TextAlign = ContentAlignment.MiddleLeft
            };
            pnlSaldoBox.Controls.Add(lblKioskSaldo);
            infoCard.Controls.Add(pnlSaldoBox);

            // SISA LIMIT BOX
            Panel pnlLimitBox = new Panel {
                Location = new Point(20, 244),
                Width = 598,
                Height = 80,
                BackColor = Color.FromArgb(240, 249, 255),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(16, 8, 16, 8)
            };
            Label lblLimitHeader = new Label {
                Text = "🛒 SISA KUOTA BELANJA HARI INI:",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(3, 105, 161),
                Location = new Point(14, 8),
                Width = 400,
                Height = 18
            };
            pnlLimitBox.Controls.Add(lblLimitHeader);

            lblKioskLimit = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(14, 28),
                Width = 560,
                Height = 42,
                TextAlign = ContentAlignment.MiddleLeft
            };
            pnlLimitBox.Controls.Add(lblKioskLimit);
            infoCard.Controls.Add(pnlLimitBox);

            lblKioskVa = new Label {
                Text = "Virtual Account Bank: -  •  Gunakan untuk titipan transfer uang keluarga",
                Font = new Font("Segoe UI", 8.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(20, 334),
                Width = 590,
                Height = 22
            };
            infoCard.Controls.Add(lblKioskVa);

            Label lblKioskFooter = new Label {
                Text = "ℹ️ Data saldo disinkronkan secara real-time dengan server lokal Lapas & Kanwil.",
                Font = new Font("Segoe UI", 8f, FontStyle.Italic),
                ForeColor = Color.FromArgb(148, 163, 184),
                Location = new Point(20, 364),
                Width = 590,
                Height = 22
            };
            infoCard.Controls.Add(lblKioskFooter);

            p.Controls.Add(infoCard);
            page.Controls.Add(p);
        }
        #endregion

        #region TAB 3: KIOSK SCAN & SEARCH (CEK SALDO)
        private void DoKioskScan()
        {
            if (currentReader == null)
            {
                InitializeBiometrics();
                if (currentReader == null)
                {
                    MessageBox.Show("Sensor sidik jari biometrik belum terdeteksi.\n\nAnda dapat mengecek saldo dengan mengetikkan Nomor Registrasi / Nama WBP atau melakukan scan barcode kartu pada kotak pencarian di atas.", "Sensor Belum Siap", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    if (txtKioskSearch != null) txtKioskSearch.Focus();
                    return;
                }
            }

            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari WBP pada sensor untuk melihat informasi saldo..."))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    if (prompt.CapturedBitmap != null)
                    {
                        picKioskFinger.Image = (Bitmap)prompt.CapturedBitmap.Clone();
                    }

                    Fmd liveFmd = prompt.CapturedFmd;
                    Dictionary<string, object> matched = null;
                    int threshold = 2147483647 / 10000;
                    int bestScore = int.MaxValue;
                    int totalWithTemplate = 0;
                    int parsedCount = 0;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
                            totalWithTemplate++;
                            Fmd storedFmd = ParseFmdFromTemplate(wbp["template"].ToString());
                            if (storedFmd != null)
                            {
                                parsedCount++;
                                try
                                {
                                    CompareResult cr = Comparison.Compare(liveFmd, 0, storedFmd, 0);
                                    if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Score < threshold && cr.Score < bestScore)
                                    {
                                        bestScore = cr.Score;
                                        matched = wbp;
                                    }
                                }
                                catch { }
                            }
                        }
                    }

                    if (matched != null)
                    {
                        SoundHelper.PlaySuccess();
                        DisplayKioskInmate(matched, true);
                        FetchFreshInmateBalance(matched);
                    }
                    else
                    {
                        SoundHelper.PlayError();
                        if (totalWithTemplate == 0)
                        {
                            MessageBox.Show(
                                "⚠️ Belum ada Warga Binaan yang memiliki data sidik jari di sistem POS (0 template terdaftar).\n\n" +
                                "Silakan lakukan perekaman sidik jari terlebih dahulu pada tab 'Rekam Jari' atau lakukan pencarian saldo menggunakan Nomor Registrasi / Scan Barcode pada kotak pencarian di atas.",
                                "Data Sidik Jari Belum Ada",
                                MessageBoxButtons.OK,
                                MessageBoxIcon.Warning
                            );
                        }
                        else
                        {
                            DialogResult dr = MessageBox.Show(
                                "⚠️ Sidik jari tidak dikenali (tidak cocok dengan " + parsedCount + " WBP terdaftar sidik jari).\n\n" +
                                "Kemungkinan penyebab:\n" +
                                "1. WBP belum pernah melakukan rekam sidik jari di sistem.\n" +
                                "2. Jari yang ditempelkan berbeda dengan jari saat perekaman (misal jempol vs telunjuk).\n" +
                                "3. Posisi jari miring atau sensor kotor.\n\n" +
                                "Apakah Anda ingin membuka menu 'Rekam Jari' untuk mendaftarkan WBP ini sekarang?",
                                "Sidik Jari Belum Terdaftar",
                                MessageBoxButtons.YesNo,
                                MessageBoxIcon.Question
                            );
                            if (dr == DialogResult.Yes && tabs != null && tabs.TabPages.Count > 1)
                            {
                                tabs.SelectedIndex = 1; // Pindah ke tab Rekam Jari
                            }
                        }
                    }
                }
            }
        }

        private void DoKioskSearch(string query)
        {
            if (string.IsNullOrEmpty(query))
            {
                MessageBox.Show("Masukkan Nomor Registrasi, Barcode Kartu, atau Nama WBP terlebih dahulu.", "Pencarian Saldo", MessageBoxButtons.OK, MessageBoxIcon.Information);
                if (txtKioskSearch != null) txtKioskSearch.Focus();
                return;
            }

            // 1. Search locally in inmateList
            Dictionary<string, object> found = inmateList.Find(x => 
                (x.ContainsKey("no_registrasi") && x["no_registrasi"] != null && x["no_registrasi"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("nama") && x["nama"] != null && x["nama"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("nik") && x["nik"] != null && x["nik"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("va_number") && x["va_number"] != null && x["va_number"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0)
            );

            if (found != null)
            {
                DisplayKioskInmate(found, false);
                FetchFreshInmateBalance(found);
            }
            else
            {
                // Try direct API lookup if not found locally
                ThreadPool.QueueUserWorkItem(_ => {
                    try
                    {
                        string url = this.apiUrl + "/pos/inmate/" + Uri.EscapeDataString(query);
                        string json = HttpGet(url);
                        var resp = serializer.Deserialize<Dictionary<string, object>>(json);
                        if (resp != null && resp.ContainsKey("success") && Convert.ToBoolean(resp["success"]) && resp.ContainsKey("data"))
                        {
                            var wbpData = (Dictionary<string, object>)resp["data"];
                            this.BeginInvoke((Action)(() => {
                                DisplayKioskInmate(wbpData, false);
                            }));
                            return;
                        }
                    }
                    catch { }

                    this.BeginInvoke((Action)(() => {
                        MessageBox.Show("Warga Binaan dengan kata kunci '" + query + "' tidak ditemukan di database Lapas.", "Pencarian WBP", MessageBoxButtons.OK, MessageBoxIcon.Information);
                        if (txtKioskSearch != null)
                        {
                            txtKioskSearch.SelectAll();
                            txtKioskSearch.Focus();
                        }
                    }));
                });
            }
        }

        private void DisplayKioskInmate(Dictionary<string, object> wbp, bool fromBiometric)
        {
            if (wbp == null) return;

            string nama = wbp.ContainsKey("nama") && wbp["nama"] != null ? wbp["nama"].ToString() : "-";
            string reg = wbp.ContainsKey("no_registrasi") && wbp["no_registrasi"] != null ? wbp["no_registrasi"].ToString() : "-";
            string kamar = wbp.ContainsKey("blok_kamar") && wbp["blok_kamar"] != null ? wbp["blok_kamar"].ToString() : "-";
            string status = wbp.ContainsKey("status") && wbp["status"] != null ? wbp["status"].ToString().ToLower() : "aktif";
            string va = wbp.ContainsKey("va_number") && wbp["va_number"] != null ? wbp["va_number"].ToString() : "-";

            decimal saldo = wbp.ContainsKey("saldo") ? Convert.ToDecimal(wbp["saldo"]) : 0;
            decimal limit = wbp.ContainsKey("remaining_limit") ? Convert.ToDecimal(wbp["remaining_limit"]) : (wbp.ContainsKey("limit_harian") ? Convert.ToDecimal(wbp["limit_harian"]) : 0);

            if (lblKioskNama != null) lblKioskNama.Text = nama;
            if (lblKioskNoReg != null) lblKioskNoReg.Text = "No. Registrasi: " + reg;
            if (lblKioskKamar != null) lblKioskKamar.Text = "Kamar / Blok: " + kamar;
            if (lblKioskVa != null) lblKioskVa.Text = "Virtual Account: " + va + "  •  Gunakan untuk titipan transfer uang keluarga";

            if (lblKioskSaldo != null) lblKioskSaldo.Text = "Rp " + saldo.ToString("N0");
            if (lblKioskLimit != null) lblKioskLimit.Text = "Rp " + limit.ToString("N0");

            if (lblKioskStatusBadge != null && lblKioskSanctionWarning != null)
            {
                if (status == "isolasi")
                {
                    lblKioskStatusBadge.Text = "⛔ REGISTER F (ISOLASI)";
                    lblKioskStatusBadge.ForeColor = Color.FromArgb(220, 38, 38);
                    lblKioskSanctionWarning.Text = "⚠️ SANKSI REGISTER F AKTIF - SELURUH TRANSAKSI BELANJA KANTIN DIBLOKIR!";
                    lblKioskSanctionWarning.Visible = true;
                }
                else if (status == "bebas")
                {
                    lblKioskStatusBadge.Text = "🕊️ TELAH BEBAS";
                    lblKioskStatusBadge.ForeColor = Color.FromArgb(100, 116, 139);
                    lblKioskSanctionWarning.Text = "ℹ️ Warga Binaan telah dinyatakan bebas. Rekening dompet telah ditutup.";
                    lblKioskSanctionWarning.Visible = true;
                }
                else
                {
                    lblKioskStatusBadge.Text = fromBiometric ? "✅ TERVERIFIKASI (SIDIK JARI)" : "● AKTIF (KARTU/BARCODE)";
                    lblKioskStatusBadge.ForeColor = Color.FromArgb(5, 150, 105);
                    lblKioskSanctionWarning.Visible = false;
                }
            }
        }

        private void ResetKioskDisplay()
        {
            if (txtKioskSearch != null)
            {
                txtKioskSearch.Clear();
                txtKioskSearch.Focus();
            }
            if (picKioskFinger != null) picKioskFinger.Image = null;
            if (lblKioskNama != null) lblKioskNama.Text = "Nama WBP: -";
            if (lblKioskNoReg != null) lblKioskNoReg.Text = "No. Registrasi: -";
            if (lblKioskKamar != null) lblKioskKamar.Text = "Kamar / Blok: -";
            if (lblKioskVa != null) lblKioskVa.Text = "Virtual Account: -  •  Gunakan untuk titipan transfer uang keluarga";
            if (lblKioskSaldo != null) lblKioskSaldo.Text = "Rp 0";
            if (lblKioskLimit != null) lblKioskLimit.Text = "Rp 0";
            if (lblKioskStatusBadge != null)
            {
                lblKioskStatusBadge.Text = "● MENUNGGU SCAN / PENCARIAN";
                lblKioskStatusBadge.ForeColor = Color.FromArgb(100, 116, 139);
            }
            if (lblKioskSanctionWarning != null) lblKioskSanctionWarning.Visible = false;
        }

        private void FetchFreshInmateBalance(Dictionary<string, object> wbp)
        {
            if (wbp == null || !wbp.ContainsKey("id")) return;
            string idOrReg = wbp.ContainsKey("no_registrasi") ? wbp["no_registrasi"].ToString() : wbp["id"].ToString();

            ThreadPool.QueueUserWorkItem(_ => {
                try
                {
                    string url = this.apiUrl + "/pos/inmate/" + Uri.EscapeDataString(idOrReg);
                    string json = HttpGet(url);
                    var resp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (resp != null && resp.ContainsKey("success") && Convert.ToBoolean(resp["success"]) && resp.ContainsKey("data"))
                    {
                        var fresh = (Dictionary<string, object>)resp["data"];
                        if (fresh.ContainsKey("saldo")) wbp["saldo"] = fresh["saldo"];
                        if (fresh.ContainsKey("remaining_limit")) wbp["remaining_limit"] = fresh["remaining_limit"];
                        if (fresh.ContainsKey("status")) wbp["status"] = fresh["status"];

                        this.BeginInvoke((Action)(() => {
                            DisplayKioskInmate(wbp, false);
                        }));
                    }
                }
                catch { }
            });
        }
        #endregion
    }
}

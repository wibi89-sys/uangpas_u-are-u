using System;
using System.Collections.Generic;
using System.Drawing;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public partial class MainForm
    {
        #region TAB 2: ENROLLMENT
        private void BuildEnrollmentTab(TabPage page)
        {
            Panel panel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(24), AutoScroll = true, BackColor = Color.FromArgb(248, 250, 252) };

            Label lblTitle = new Label {
                Text = "Pendaftaran & Perekaman Biometrik Sidik Jari WBP (4-Touch Enrollment)",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                AutoSize = true,
                Location = new Point(24, 16)
            };
            panel.Controls.Add(lblTitle);

            Label lblSub = new Label {
                Text = "Cari WBP pada daftar di bawah, pilih nama yang bersangkutan, lalu minta WBP menempelkan jari 4x pada sensor biometrik.",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true,
                Location = new Point(24, 44)
            };
            panel.Controls.Add(lblSub);

            // Hidden fallback ComboBox
            cmbInmateEnroll = new ComboBox { Visible = false, Location = new Point(0, 0), Width = 10 };
            panel.Controls.Add(cmbInmateEnroll);

            // LEFT PANEL: Inmate Search & List
            GroupBox grpInmateList = new GroupBox {
                Text = " 👥 Daftar Warga Binaan Pemasyarakatan ",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, 76),
                Width = 470,
                Height = 490,
                BackColor = Color.White
            };

            Label lblSearchIcon = new Label {
                Text = "🔍 Cari Nama / No. Reg / NIK / Kamar:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(16, 26),
                AutoSize = true
            };
            grpInmateList.Controls.Add(lblSearchIcon);

            txtEnrollSearch = new TextBox {
                Location = new Point(16, 48),
                Width = 436,
                Font = new Font("Segoe UI", 11f),
                ForeColor = Color.FromArgb(15, 23, 42)
            };
            txtEnrollSearch.TextChanged += (s, e) => FilterEnrollInmates();
            grpInmateList.Controls.Add(txtEnrollSearch);

            chkEnrollOnlyUnenrolled = new CheckBox {
                Text = "⚠️ Hanya WBP yang BELUM ada sidik jari",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(194, 65, 12),
                Location = new Point(16, 82),
                Width = 330,
                Height = 24,
                Checked = true
            };
            chkEnrollOnlyUnenrolled.CheckedChanged += (s, e) => FilterEnrollInmates();
            grpInmateList.Controls.Add(chkEnrollOnlyUnenrolled);

            Button btnRefreshInmates = new Button {
                Text = "🔄 Segarkan",
                Location = new Point(352, 80),
                Width = 100,
                Height = 26,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnRefreshInmates.Click += (s, e) => SyncDataFromApi();
            grpInmateList.Controls.Add(btnRefreshInmates);

            lblEnrollCount = new Label {
                Text = "Memuat data...",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(16, 110),
                Width = 436,
                Height = 20
            };
            grpInmateList.Controls.Add(lblEnrollCount);

            lstEnrollInmates = new ListBox {
                Location = new Point(16, 134),
                Width = 436,
                Height = 338,
                Font = new Font("Segoe UI", 9.5f),
                BorderStyle = BorderStyle.FixedSingle,
                ItemHeight = 22
            };
            lstEnrollInmates.SelectedIndexChanged += (s, e) => {
                var item = lstEnrollInmates.SelectedItem as InmateEnrollItem;
                if (item != null)
                {
                    selectedEnrollInmate = item.Inmate;
                    UpdateEnrollInmateCard(item.Inmate);
                }
            };
            grpInmateList.Controls.Add(lstEnrollInmates);
            panel.Controls.Add(grpInmateList);

            // RIGHT PANEL: Enrollment Action Card & Biometrics Preview
            GroupBox grpAction = new GroupBox {
                Text = " Panel Perekaman Biometrik Sensor U.are.U ",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(510, 76),
                Width = 490,
                Height = 490,
                BackColor = Color.White
            };

            // Selected Inmate Card
            Panel pnlSelectedInfo = new Panel {
                Location = new Point(16, 26),
                Width = 456,
                Height = 105,
                BackColor = Color.FromArgb(248, 250, 252),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(12)
            };

            lblEnrollSelectedName = new Label {
                Text = "Pilih Warga Binaan dari daftar...",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(10, 8),
                Width = 430,
                Height = 26
            };
            pnlSelectedInfo.Controls.Add(lblEnrollSelectedName);

            lblEnrollSelectedReg = new Label {
                Text = "No. Registrasi: -  •  Kamar: -",
                Font = new Font("Consolas", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(10, 36),
                Width = 430,
                Height = 20
            };
            pnlSelectedInfo.Controls.Add(lblEnrollSelectedReg);

            lblEnrollSelectedFpStatus = new Label {
                Text = "Status: -",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(10, 60),
                Width = 430,
                Height = 36
            };
            pnlSelectedInfo.Controls.Add(lblEnrollSelectedFpStatus);
            grpAction.Controls.Add(pnlSelectedInfo);

            // Sensor Preview Box (Compact & Sleek)
            Panel pnlEnrollBorder = new Panel {
                Location = new Point(20, 145),
                Width = 134,
                Height = 164,
                BackColor = Color.FromArgb(2, 132, 199),
                Padding = new Padding(2)
            };

            picEnrollPreview = new PictureBox {
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(15, 23, 42),
                SizeMode = PictureBoxSizeMode.Zoom
            };
            string enrollFpPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (System.IO.File.Exists(enrollFpPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(enrollFpPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    {
                        picEnrollPreview.Image = new Bitmap(ms);
                    }
                } catch { }
            }
            pnlEnrollBorder.Controls.Add(picEnrollPreview);
            grpAction.Controls.Add(pnlEnrollBorder);

            // Enrollment Status & Step Indicator
            lblEnrollStep = new Label {
                Text = "Status: Pilih Warga Binaan pada daftar, lalu klik tombol Mulai Perekaman di bawah.",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(170, 145),
                Width = 300,
                Height = 95
            };
            grpAction.Controls.Add(lblEnrollStep);

            btnStartEnroll = new Button {
                Text = "  Mulai Perekaman 4x Sentuh Jari",
                Location = new Point(170, 255),
                Width = 300,
                Height = 48,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            if (System.IO.File.Exists(enrollFpPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(enrollFpPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnStartEnroll.Image = new Bitmap(orig, new Size(22, 22));
                        btnStartEnroll.ImageAlign = ContentAlignment.MiddleLeft;
                        btnStartEnroll.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnStartEnroll.FlatAppearance.BorderSize = 0;
            btnStartEnroll.Click += (s, e) => StartEnrollmentSequence();
            grpAction.Controls.Add(btnStartEnroll);

            Label lblEnrollTips = new Label {
                Text = "💡 Tips Perekaman: Minta WBP menempelkan jari yang sama sebanyak 4 kali dengan posisi mantap dan bersih dari minyak.",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(16, 360),
                Width = 456,
                Height = 50
            };
            grpAction.Controls.Add(lblEnrollTips);

            panel.Controls.Add(grpAction);
            page.Controls.Add(panel);
        }
        #endregion

        #region ENROLLMENT WIZARD (SEARCHABLE & BATCH 4-TOUCH)
        private void UpdateEnrollInmateCard(Dictionary<string, object> wbp)
        {
            if (wbp == null) return;
            string nama = wbp.ContainsKey("nama") && wbp["nama"] != null ? wbp["nama"].ToString() : "-";
            string reg = wbp.ContainsKey("no_registrasi") && wbp["no_registrasi"] != null ? wbp["no_registrasi"].ToString() : "-";
            string kamar = wbp.ContainsKey("blok_kamar") && wbp["blok_kamar"] != null ? wbp["blok_kamar"].ToString() : "-";
            bool hasFp = wbp.ContainsKey("has_fingerprint") && Convert.ToBoolean(wbp["has_fingerprint"]);

            if (lblEnrollSelectedName != null) lblEnrollSelectedName.Text = nama;
            if (lblEnrollSelectedReg != null) lblEnrollSelectedReg.Text = "No. Registrasi: " + reg + "  •  Kamar: " + kamar;

            if (lblEnrollSelectedFpStatus != null)
            {
                if (hasFp)
                {
                    lblEnrollSelectedFpStatus.Text = "Status: ✅ SUDAH TERDAFTAR DI SISTEM\n(Perekaman ulang akan memperbarui template sidik jari WBP)";
                    lblEnrollSelectedFpStatus.ForeColor = Color.FromArgb(5, 150, 105);
                    if (btnStartEnroll != null) btnStartEnroll.Text = "🔄 Perbarui Sidik Jari (4x Sentuh)";
                }
                else
                {
                    lblEnrollSelectedFpStatus.Text = "Status: ⚪ BELUM ADA SIDIK JARI\n(Wajib rekam 4x sentuh untuk aktivasi belanja di kantin)";
                    lblEnrollSelectedFpStatus.ForeColor = Color.FromArgb(220, 38, 38);
                    if (btnStartEnroll != null) btnStartEnroll.Text = "▶️ Mulai Perekaman 4x Sentuh Jari";
                }
            }

            if (lblEnrollStep != null)
            {
                lblEnrollStep.Text = "Siap merekam sidik jari untuk:\n" + nama;
                lblEnrollStep.ForeColor = Color.FromArgb(2, 132, 199);
            }
        }

        private void FilterEnrollInmates()
        {
            if (lstEnrollInmates == null) return;

            string query = txtEnrollSearch != null ? txtEnrollSearch.Text.Trim().ToLower() : "";
            bool onlyUnenrolled = chkEnrollOnlyUnenrolled != null && chkEnrollOnlyUnenrolled.Checked;

            lstEnrollInmates.BeginUpdate();
            lstEnrollInmates.Items.Clear();

            int matchedCount = 0;
            int unenrolledCount = 0;
            InmateEnrollItem toSelect = null;

            foreach (var wbp in inmateList)
            {
                bool hasFp = wbp.ContainsKey("has_fingerprint") && Convert.ToBoolean(wbp["has_fingerprint"]);
                if (!hasFp) unenrolledCount++;

                if (onlyUnenrolled && hasFp) continue;

                string nama = wbp.ContainsKey("nama") && wbp["nama"] != null ? wbp["nama"].ToString().ToLower() : "";
                string reg = wbp.ContainsKey("no_registrasi") && wbp["no_registrasi"] != null ? wbp["no_registrasi"].ToString().ToLower() : "";
                string kamar = wbp.ContainsKey("blok_kamar") && wbp["blok_kamar"] != null ? wbp["blok_kamar"].ToString().ToLower() : "";
                string nik = wbp.ContainsKey("nik") && wbp["nik"] != null ? wbp["nik"].ToString().ToLower() : "";

                if (!string.IsNullOrEmpty(query))
                {
                    if (!nama.Contains(query) && !reg.Contains(query) && !kamar.Contains(query) && !nik.Contains(query))
                    {
                        continue;
                    }
                }

                var item = new InmateEnrollItem { Inmate = wbp };
                lstEnrollInmates.Items.Add(item);
                matchedCount++;

                if (selectedEnrollInmate != null && wbp.ContainsKey("id") && selectedEnrollInmate.ContainsKey("id") &&
                    wbp["id"].ToString() == selectedEnrollInmate["id"].ToString())
                {
                    toSelect = item;
                }
            }

            lstEnrollInmates.EndUpdate();

            if (lblEnrollCount != null)
            {
                lblEnrollCount.Text = "Menampilkan " + matchedCount + " dari " + inmateList.Count + " WBP (" + unenrolledCount + " belum ada sidik jari)";
            }

            if (toSelect != null)
            {
                lstEnrollInmates.SelectedItem = toSelect;
            }
            else if (lstEnrollInmates.Items.Count > 0)
            {
                lstEnrollInmates.SelectedIndex = 0;
            }
            else
            {
                selectedEnrollInmate = null;
                if (lblEnrollSelectedName != null) lblEnrollSelectedName.Text = "(Tidak ada WBP yang cocok)";
                if (lblEnrollSelectedReg != null) lblEnrollSelectedReg.Text = "No. Registrasi: -";
                if (lblEnrollSelectedFpStatus != null)
                {
                    lblEnrollSelectedFpStatus.Text = onlyUnenrolled ? "Semua WBP sudah memiliki rekam sidik jari." : "Pencarian tidak menemukan hasil.";
                    lblEnrollSelectedFpStatus.ForeColor = Color.FromArgb(100, 116, 139);
                }
            }
        }

        private void StartEnrollmentSequence()
        {
            if (selectedEnrollInmate == null && lstEnrollInmates != null && lstEnrollInmates.SelectedItem != null)
            {
                var item = lstEnrollInmates.SelectedItem as InmateEnrollItem;
                if (item != null) selectedEnrollInmate = item.Inmate;
            }

            if (selectedEnrollInmate == null && cmbInmateEnroll.SelectedIndex >= 0)
            {
                int fallbackId = Convert.ToInt32(cmbInmateEnroll.SelectedItem.ToString().Split('-')[0].Trim());
                selectedEnrollInmate = inmateList.Find(x => x.ContainsKey("id") && Convert.ToInt32(x["id"]) == fallbackId);
            }

            if (selectedEnrollInmate == null)
            {
                MessageBox.Show("Silakan pilih salah satu Warga Binaan pada daftar terlebih dahulu.", "Peringatan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            if (currentReader == null)
            {
                InitializeBiometrics();
                if (currentReader == null)
                {
                    MessageBox.Show("Sensor sidik jari biometrik belum terdeteksi.\nSilakan pastikan kabel scanner U.are.U terpasang pada port USB komputer.", "Sensor Belum Siap", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
            }

            int inmateId = Convert.ToInt32(selectedEnrollInmate["id"]);
            string inmateNama = selectedEnrollInmate.ContainsKey("nama") ? selectedEnrollInmate["nama"].ToString() : "WBP";
            enrollFmdList.Clear();

            for (int step = 1; step <= 4; step++)
            {
                lblEnrollStep.Text = "LANGKAH " + step + " DARI 4:\nTempelkan jari " + inmateNama + " pada sensor...";
                lblEnrollStep.ForeColor = Color.FromArgb(2, 132, 199);
                Application.DoEvents();
                System.Threading.Thread.Sleep(200);

                using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Langkah " + step + "/4: Tempelkan jari " + inmateNama + " pada sensor sidik jari"))
                {
                    if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                    {
                        enrollFmdList.Add(prompt.CapturedFmd);
                        if (prompt.CapturedBitmap != null)
                        {
                            picEnrollPreview.Image = (Bitmap)prompt.CapturedBitmap.Clone();
                        }
                    }
                    else
                    {
                        lblEnrollStep.Text = "Perekaman dibatalkan.";
                        lblEnrollStep.ForeColor = Color.FromArgb(239, 68, 68);
                        return;
                    }
                }
            }

            // Combine 4 FMDs into Master Enrollment FMD
            lblEnrollStep.Text = "Memproses Master Template ANSI/INCITS...";
            Application.DoEvents();

            try
            {
                DataResult<Fmd> masterRes = Enrollment.CreateEnrollmentFmd(Constants.Formats.Fmd.ANSI, enrollFmdList);
                if (masterRes.ResultCode == Constants.ResultCode.DP_SUCCESS && masterRes.Data != null)
                {
                    string masterXml = Fmd.SerializeXml(masterRes.Data);

                    // Send to Laravel API
                    var payload = new Dictionary<string, object> {
                        { "fingerprint_template", masterXml }
                    };
                    string resp = HttpPost(this.apiUrl + "/pos/inmate/" + inmateId + "/save-fingerprint", serializer.Serialize(payload));

                    lblEnrollStep.Text = "✅ SUKSES! Sidik jari " + inmateNama + " berhasil tersimpan di server.";
                    lblEnrollStep.ForeColor = Color.FromArgb(16, 185, 129);
                    SoundHelper.PlaySuccess();

                    // Mark as enrolled locally
                    selectedEnrollInmate["has_fingerprint"] = true;
                    selectedEnrollInmate["template"] = masterXml;

                    MessageBox.Show("Pendaftaran sidik jari WBP " + inmateNama + " berhasil disimpan ke server pusat!\n\nWBP ini kini dapat langsung berbelanja di kasir POS.", "Perekaman Berhasil", MessageBoxButtons.OK, MessageBoxIcon.Information);

                    // Refresh list - if onlyUnenrolled is active, this person disappears and next person is selected!
                    FilterEnrollInmates();
                }
                else
                {
                    SoundHelper.PlayError();
                    lblEnrollStep.Text = "❌ Gagal membuat master template: " + masterRes.ResultCode;
                    lblEnrollStep.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
            catch (Exception ex)
            {
                SoundHelper.PlayError();
                lblEnrollStep.Text = "Error: " + ex.Message;
                lblEnrollStep.ForeColor = Color.FromArgb(239, 68, 68);
            }
        }
        #endregion
    }
}

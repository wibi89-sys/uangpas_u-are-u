using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public class QuickCheckSaldoForm : Form
    {
        private Reader reader;
        private List<Dictionary<string, object>> inmateList;
        private string apiUrl;
        private TextBox txtSearch;
        private Panel pnlResult;
        private Label lblNama;
        private Label lblNoReg;
        private Label lblKamar;
        private Label lblSaldo;
        private Label lblLimit;
        private Label lblStatusSanction;

        public QuickCheckSaldoForm(Reader r, List<Dictionary<string, object>> inmates, string api)
        {
            this.reader = r;
            this.inmateList = inmates;
            this.apiUrl = api;

            this.Text = "🔍 Cek Saldo & Limit WBP Cepat (F8)";
            this.Size = new Size(560, 520);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);

            Panel top = new Panel {
                Dock = DockStyle.Top,
                Height = 90,
                Padding = new Padding(18, 12, 18, 10),
                BackColor = Color.FromArgb(15, 23, 42)
            };

            Label lblTitle = new Label {
                Text = "Ketik No. Reg / Scan Barcode / Scan Sidik Jari Biometrik:",
                ForeColor = Color.FromArgb(203, 213, 225),
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Dock = DockStyle.Top,
                Height = 22
            };
            top.Controls.Add(lblTitle);

            Panel pnlSearchRow = new Panel {
                Dock = DockStyle.Bottom,
                Height = 36
            };

            txtSearch = new TextBox {
                Location = new Point(0, 2),
                Width = 340,
                Font = new Font("Segoe UI", 12f),
                ForeColor = Color.FromArgb(15, 23, 42)
            };
            txtSearch.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    DoSearch(txtSearch.Text.Trim());
                }
            };
            pnlSearchRow.Controls.Add(txtSearch);

            Button btnScanFinger = new Button {
                Text = "👆 Scan Sidik Jari",
                Location = new Point(348, 0),
                Width = 156,
                Height = 35,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnScanFinger.FlatAppearance.BorderSize = 0;
            btnScanFinger.Click += (s, e) => DoQuickFingerprintScan();
            pnlSearchRow.Controls.Add(btnScanFinger);

            top.Controls.Add(pnlSearchRow);
            this.Controls.Add(top);

            // Result Card
            pnlResult = new Panel {
                Dock = DockStyle.Fill,
                Padding = new Padding(24),
                BackColor = Color.White
            };

            lblNama = new Label {
                Text = "Silakan scan atau masukkan nomor registrasi WBP...",
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Dock = DockStyle.Top,
                Height = 34
            };
            pnlResult.Controls.Add(lblNama);

            lblNoReg = new Label {
                Text = "-",
                Font = new Font("Consolas", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlResult.Controls.Add(lblNoReg);

            lblKamar = new Label {
                Text = "-",
                Font = new Font("Segoe UI", 10f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Dock = DockStyle.Top,
                Height = 26
            };
            pnlResult.Controls.Add(lblKamar);

            lblStatusSanction = new Label {
                Text = "",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(239, 68, 68),
                Dock = DockStyle.Top,
                Height = 26
            };
            pnlResult.Controls.Add(lblStatusSanction);

            Panel saldoBox = new Panel {
                Dock = DockStyle.Top,
                Height = 110,
                BackColor = Color.FromArgb(240, 253, 244),
                Padding = new Padding(16, 12, 16, 12),
                Margin = new Padding(0, 10, 0, 0)
            };
            Label lblSaldoTitle = new Label {
                Text = "SISA SALDO DOMPET DIGITAL:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(22, 101, 52),
                Dock = DockStyle.Top,
                Height = 22
            };
            lblSaldo = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 22f, FontStyle.Bold),
                ForeColor = Color.FromArgb(22, 101, 52),
                Dock = DockStyle.Fill,
                TextAlign = ContentAlignment.MiddleLeft
            };
            saldoBox.Controls.Add(lblSaldo);
            saldoBox.Controls.Add(lblSaldoTitle);
            pnlResult.Controls.Add(saldoBox);

            lblLimit = new Label {
                Text = "Sisa Kuota Belanja Hari Ini: -",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(14, 165, 233),
                Dock = DockStyle.Top,
                Height = 36,
                Padding = new Padding(0, 10, 0, 0)
            };
            pnlResult.Controls.Add(lblLimit);

            Panel bottom = new Panel {
                Dock = DockStyle.Bottom,
                Height = 56,
                Padding = new Padding(16, 10, 16, 10),
                BackColor = Color.FromArgb(241, 245, 249)
            };
            Button btnClose = new Button {
                Text = "Tutup [Esc / Enter]",
                Dock = DockStyle.Right,
                Width = 160,
                DialogResult = DialogResult.OK,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            bottom.Controls.Add(btnClose);
            this.Controls.Add(pnlResult);
            this.Controls.Add(bottom);
            this.AcceptButton = btnClose;
            this.CancelButton = btnClose;

            this.Shown += (s, e) => txtSearch.Focus();
        }

        private void DoQuickFingerprintScan()
        {
            if (this.reader == null)
            {
                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers != null && readers.Count > 0) this.reader = readers[0];
            }

            if (this.reader == null)
            {
                MessageBox.Show("Sensor sidik jari belum terhubung.\nSilakan gunakan pencarian menggunakan Nomor Registrasi atau Barcode kartu.", "Sensor Tidak Ditemukan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            using (FingerprintPromptForm prompt = new FingerprintPromptForm(this.reader, "Tempelkan jari WBP pada sensor untuk cek saldo..."))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    Fmd liveFmd = prompt.CapturedFmd;
                    Dictionary<string, object> matched = null;
                    int threshold = 2147483647 / 10000;
                    int bestScore = int.MaxValue;
                    int withTemplate = 0;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
                            withTemplate++;
                            Fmd storedFmd = MainForm.ParseFmdFromTemplate(wbp["template"].ToString());
                            if (storedFmd != null)
                            {
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
                        DisplayInmate(matched);
                    }
                    else
                    {
                        MessageBox.Show(
                            "Sidik jari tidak cocok dengan " + withTemplate + " WBP terdaftar pada sistem.\nSilakan gunakan pencarian Nomor Registrasi atau daftarkan sidik jari di menu Rekam Jari.",
                            "Sidik Jari Tidak Dikenali",
                            MessageBoxButtons.OK,
                            MessageBoxIcon.Warning
                        );
                    }
                }
            }
        }

        private void DisplayInmate(Dictionary<string, object> match)
        {
            if (match == null) return;

            lblNama.Text = match.ContainsKey("nama") ? match["nama"].ToString() : "-";
            lblNoReg.Text = "No. Registrasi: " + (match.ContainsKey("no_registrasi") ? match["no_registrasi"].ToString() : "-");
            lblKamar.Text = "Lokasi: " + (match.ContainsKey("blok_kamar") ? match["blok_kamar"].ToString() : "-") + " • Lapas: " + (match.ContainsKey("lapas_asal") ? match["lapas_asal"].ToString() : "-");

            decimal saldo = match.ContainsKey("saldo") ? Convert.ToDecimal(match["saldo"]) : 0;
            decimal limit = match.ContainsKey("remaining_limit") ? Convert.ToDecimal(match["remaining_limit"]) : (match.ContainsKey("limit_harian") ? Convert.ToDecimal(match["limit_harian"]) : 0);
            string status = match.ContainsKey("status") ? match["status"].ToString().ToLower() : "aktif";

            lblSaldo.Text = "Rp " + saldo.ToString("N0");
            lblLimit.Text = "Sisa Kuota Belanja Hari Ini: Rp " + limit.ToString("N0");

            if (status == "isolasi")
            {
                lblStatusSanction.Text = "⛔ STATUS: SANKSI REGISTER F (ISOLASI) - TRANSAKSI DIBLOKIR!";
                lblStatusSanction.ForeColor = Color.FromArgb(220, 38, 38);
            }
            else if (status == "bebas")
            {
                lblStatusSanction.Text = "ℹ️ STATUS: TELAH BEBAS (NONAKTIF)";
                lblStatusSanction.ForeColor = Color.FromArgb(100, 116, 139);
            }
            else
            {
                lblStatusSanction.Text = "✅ STATUS: AKTIF (DAPAT BERTRANSAKSI)";
                lblStatusSanction.ForeColor = Color.FromArgb(16, 185, 129);
            }
        }

        private void DoSearch(string query)
        {
            if (string.IsNullOrEmpty(query)) return;

            var match = inmateList.Find(x => 
                (x.ContainsKey("no_registrasi") && x["no_registrasi"] != null && x["no_registrasi"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("nama") && x["nama"] != null && x["nama"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("nik") && x["nik"] != null && x["nik"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0) ||
                (x.ContainsKey("va_number") && x["va_number"] != null && x["va_number"].ToString().IndexOf(query, StringComparison.OrdinalIgnoreCase) >= 0)
            );

            // If not found locally, query API server
            if (match == null && !string.IsNullOrEmpty(this.apiUrl))
            {
                try
                {
                    HttpWebRequest req = (HttpWebRequest)WebRequest.Create(this.apiUrl + "/pos/inmate/" + Uri.EscapeDataString(query));
                    req.Method = "GET";
                    req.Timeout = 4000;
                    using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
                    using (StreamReader r = new StreamReader(res.GetResponseStream()))
                    {
                        string json = r.ReadToEnd();
                        var ser = new JavaScriptSerializer();
                        var resp = ser.Deserialize<Dictionary<string, object>>(json);
                        if (resp != null && resp.ContainsKey("success") && Convert.ToBoolean(resp["success"]) && resp.ContainsKey("data"))
                        {
                            match = (Dictionary<string, object>)resp["data"];
                        }
                    }
                }
                catch { }
            }

            if (match != null)
            {
                DisplayInmate(match);
            }
            else
            {
                MessageBox.Show("Warga Binaan dengan kata kunci '" + query + "' tidak ditemukan di database Lapas.", "Pencarian WBP", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
        }
    }
}

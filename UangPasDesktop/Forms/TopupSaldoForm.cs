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
    public class TopupSaldoForm : Form
    {
        private Reader reader;
        private List<Dictionary<string, object>> inmateList;
        private string apiUrl;
        private Action<int, decimal> onBalanceUpdated;

        private TextBox txtSearch;
        private Label lblNama;
        private Label lblNoReg;
        private Label lblKamar;
        private Label lblStatus;
        private Label lblCurrentSaldo;
        private Label lblNewSaldoPreview;

        private TextBox txtAmount;
        private TextBox txtPenyetor;
        private TextBox txtNotes;
        private Button btnSubmit;

        private Dictionary<string, object> selectedInmate = null;
        private decimal currentAmount = 0;

        public TopupSaldoForm(Reader r, List<Dictionary<string, object>> inmates, string api, Action<int, decimal> balanceCallback)
        {
            this.reader = r;
            this.inmateList = inmates;
            this.apiUrl = api;
            this.onBalanceUpdated = balanceCallback;

            this.Text = "Setor Tunai / Top-Up Saldo Dompet WBP";
            this.Size = new Size(620, 720);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);

            string walletPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "wallet_logo.png");
            if (System.IO.File.Exists(walletPath))
            {
                try
                {
                    byte[] bytes = System.IO.File.ReadAllBytes(walletPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        this.Icon = Icon.FromHandle(orig.GetHicon());
                    }
                }
                catch { }
            }

            BuildUI();
        }

        private void BuildUI()
        {
            // HEADER BAR
            Panel top = new Panel {
                Dock = DockStyle.Top,
                Height = 70,
                Padding = new Padding(20, 12, 20, 10),
                BackColor = Color.FromArgb(15, 23, 42)
            };

            Label lblHeaderTitle = new Label {
                Text = "💰 SETOR TUNAI / TOP-UP SALDO DOMPET WBP",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                Dock = DockStyle.Top,
                Height = 24
            };
            top.Controls.Add(lblHeaderTitle);

            Label lblHeaderSub = new Label {
                Text = "Scan sidik jari WBP atau cari nomor registrasi untuk mengisi saldo dompet digital.",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9f),
                Dock = DockStyle.Top,
                Height = 22
            };
            top.Controls.Add(lblHeaderSub);
            this.Controls.Add(top);

            // MAIN SCROLLABLE CONTAINER
            Panel main = new Panel {
                Dock = DockStyle.Fill,
                Padding = new Padding(20, 14, 20, 14),
                AutoScroll = true
            };

            // 1. SEARCH SECTION
            GroupBox grpSearch = new GroupBox {
                Text = " 1. Identifikasi Warga Binaan (WBP) ",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(20, 10),
                Width = 560,
                Height = 84,
                BackColor = Color.White
            };

            txtSearch = new TextBox {
                Location = new Point(14, 30),
                Width = 350,
                Font = new Font("Segoe UI", 11.5f),
                ForeColor = Color.FromArgb(15, 23, 42)
            };
            txtSearch.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    DoSearch(txtSearch.Text.Trim());
                }
            };
            grpSearch.Controls.Add(txtSearch);

            Button btnScanFinger = new Button {
                Text = "  Scan Sidik Jari",
                Location = new Point(374, 28),
                Width = 170,
                Height = 35,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            string qkFpPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (System.IO.File.Exists(qkFpPath))
            {
                try
                {
                    byte[] bytes = System.IO.File.ReadAllBytes(qkFpPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnScanFinger.Image = new Bitmap(orig, new Size(18, 18));
                        btnScanFinger.ImageAlign = ContentAlignment.MiddleLeft;
                        btnScanFinger.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                }
                catch { }
            }
            btnScanFinger.FlatAppearance.BorderSize = 0;
            btnScanFinger.Click += (s, e) => DoQuickFingerprintScan();
            grpSearch.Controls.Add(btnScanFinger);
            main.Controls.Add(grpSearch);

            // 2. INMATE INFO CARD
            Panel pnlInmate = new Panel {
                Location = new Point(20, 104),
                Width = 560,
                Height = 110,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(16, 12, 16, 12)
            };

            lblNama = new Label {
                Text = "Belum ada WBP dipilih. Silakan cari atau scan sidik jari...",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(14, 10),
                Width = 530,
                Height = 24
            };
            pnlInmate.Controls.Add(lblNama);

            lblNoReg = new Label {
                Text = "No. Registrasi: -",
                Font = new Font("Consolas", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(14, 36),
                Width = 260,
                Height = 20
            };
            pnlInmate.Controls.Add(lblNoReg);

            lblKamar = new Label {
                Text = "Kamar / Blok: -",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(280, 36),
                Width = 260,
                Height = 20
            };
            pnlInmate.Controls.Add(lblKamar);

            lblStatus = new Label {
                Text = "Status: -",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(14, 60),
                Width = 260,
                Height = 20
            };
            pnlInmate.Controls.Add(lblStatus);

            lblCurrentSaldo = new Label {
                Text = "Saldo Sekarang: Rp 0",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(22, 101, 52),
                Location = new Point(280, 58),
                Width = 260,
                Height = 22
            };
            pnlInmate.Controls.Add(lblCurrentSaldo);

            main.Controls.Add(pnlInmate);

            // 3. TOP-UP AMOUNT SECTION
            GroupBox grpAmount = new GroupBox {
                Text = " 2. Nominal Top-Up & Setoran ",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(20, 224),
                Width = 560,
                Height = 245,
                BackColor = Color.White
            };

            Label lblNominalTitle = new Label {
                Text = "Nominal Setoran (Rp):",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(16, 26),
                Width = 200,
                Height = 20
            };
            grpAmount.Controls.Add(lblNominalTitle);

            txtAmount = new TextBox {
                Location = new Point(16, 48),
                Width = 524,
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.FromArgb(16, 185, 129),
                TextAlign = HorizontalAlignment.Right,
                Text = "0"
            };
            txtAmount.TextChanged += (s, e) => {
                string raw = txtAmount.Text.Replace(".", "").Replace(",", "").Trim();
                decimal val = 0;
                if (decimal.TryParse(raw, out val))
                {
                    currentAmount = val;
                }
                else
                {
                    currentAmount = 0;
                }
                UpdateCalculationPreview();
            };
            grpAmount.Controls.Add(txtAmount);

            // Quick Chips FlowLayoutPanel
            FlowLayoutPanel flowChips = new FlowLayoutPanel {
                Location = new Point(16, 94),
                Width = 524,
                Height = 42
            };
            AddQuickChip(flowChips, "+20.000", 20000);
            AddQuickChip(flowChips, "+50.000", 50000);
            AddQuickChip(flowChips, "+100.000", 100000);
            AddQuickChip(flowChips, "+200.000", 200000);
            AddQuickChip(flowChips, "+500.000", 500000);
            AddClearChip(flowChips, "Reset", 0);
            grpAmount.Controls.Add(flowChips);

            // Detail Penyetor & Catatan
            Label lblPenyetor = new Label {
                Text = "Nama Penyetor:",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(16, 144),
                Width = 140,
                Height = 18
            };
            grpAmount.Controls.Add(lblPenyetor);

            txtPenyetor = new TextBox {
                Location = new Point(16, 164),
                Width = 250,
                Font = new Font("Segoe UI", 9.5f),
                Text = "Kasir POS / Loket"
            };
            grpAmount.Controls.Add(txtPenyetor);

            Label lblNotes = new Label {
                Text = "Catatan Tambahan:",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(280, 144),
                Width = 140,
                Height = 18
            };
            grpAmount.Controls.Add(lblNotes);

            txtNotes = new TextBox {
                Location = new Point(280, 164),
                Width = 260,
                Font = new Font("Segoe UI", 9.5f),
                Text = "Setor tunai loket kasir"
            };
            grpAmount.Controls.Add(txtNotes);

            // Preview calculation card
            lblNewSaldoPreview = new Label {
                Text = "Simulasi: Saldo Awal Rp 0 + Top-up Rp 0 = Saldo Baru Rp 0",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(5, 150, 105),
                BackColor = Color.FromArgb(236, 253, 245),
                BorderStyle = BorderStyle.FixedSingle,
                Location = new Point(16, 200),
                Width = 524,
                Height = 32,
                TextAlign = ContentAlignment.MiddleCenter
            };
            grpAmount.Controls.Add(lblNewSaldoPreview);

            main.Controls.Add(grpAmount);

            // 4. ACTION BUTTONS ROW
            Panel bottom = new Panel {
                Dock = DockStyle.Bottom,
                Height = 62,
                Padding = new Padding(20, 12, 20, 12),
                BackColor = Color.FromArgb(241, 245, 249)
            };

            btnSubmit = new Button {
                Text = "✓ Proses Setor Saldo [Enter]",
                Dock = DockStyle.Right,
                Width = 250,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnSubmit.FlatAppearance.BorderSize = 0;
            btnSubmit.Click += (s, e) => ProcessTopup();
            bottom.Controls.Add(btnSubmit);

            Button btnCancel = new Button {
                Text = "Batal [Esc]",
                Dock = DockStyle.Left,
                Width = 120,
                BackColor = Color.FromArgb(203, 213, 225),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                DialogResult = DialogResult.Cancel
            };
            btnCancel.FlatAppearance.BorderSize = 0;
            bottom.Controls.Add(btnCancel);

            this.Controls.Add(main);
            this.Controls.Add(bottom);

            this.CancelButton = btnCancel;
            this.Shown += (s, e) => txtSearch.Focus();
        }

        private void AddQuickChip(FlowLayoutPanel flow, string label, decimal val)
        {
            Button chip = new Button {
                Text = label,
                Width = 78,
                Height = 32,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 4, 0)
            };
            chip.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            chip.Click += (s, e) => {
                currentAmount += val;
                txtAmount.Text = currentAmount.ToString("N0");
                txtAmount.SelectionStart = txtAmount.Text.Length;
            };
            flow.Controls.Add(chip);
        }

        private void AddClearChip(FlowLayoutPanel flow, string label, decimal val)
        {
            Button chip = new Button {
                Text = label,
                Width = 64,
                Height = 32,
                BackColor = Color.FromArgb(254, 226, 226),
                ForeColor = Color.FromArgb(185, 28, 28),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 2, 0)
            };
            chip.FlatAppearance.BorderColor = Color.FromArgb(252, 165, 165);
            chip.Click += (s, e) => {
                currentAmount = 0;
                txtAmount.Text = "0";
            };
            flow.Controls.Add(chip);
        }

        private void UpdateCalculationPreview()
        {
            decimal before = 0;
            if (selectedInmate != null && selectedInmate.ContainsKey("saldo"))
            {
                before = Convert.ToDecimal(selectedInmate["saldo"]);
            }
            decimal after = before + currentAmount;
            lblNewSaldoPreview.Text = "Simulasi: Saldo Awal Rp " + before.ToString("N0") + " + Top-up Rp " + currentAmount.ToString("N0") + " = Saldo Baru Rp " + after.ToString("N0");
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

            using (FingerprintPromptForm prompt = new FingerprintPromptForm(this.reader, "Tempelkan jari WBP pada sensor untuk verifikasi top-up saldo..."))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    Fmd liveFmd = prompt.CapturedFmd;
                    Dictionary<string, object> matched = null;
                    int threshold = 2147483647 / 10000;
                    int bestScore = int.MaxValue;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
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
                        SoundHelper.PlaySuccess();
                        SelectInmate(matched);
                    }
                    else
                    {
                        SoundHelper.PlayError();
                        MessageBox.Show(
                            "Sidik jari tidak cocok dengan WBP terdaftar pada sistem.\nSilakan gunakan pencarian Nomor Registrasi atau daftarkan sidik jari di menu Rekam Jari.",
                            "Sidik Jari Tidak Dikenali",
                            MessageBoxButtons.OK,
                            MessageBoxIcon.Warning
                        );
                    }
                }
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

            if (match == null && !string.IsNullOrEmpty(this.apiUrl))
            {
                try
                {
                    string endpoint = this.apiUrl.TrimEnd('/') + "/pos/inmate/" + Uri.EscapeDataString(query);
                    HttpWebRequest request = (HttpWebRequest)WebRequest.Create(endpoint);
                    request.Timeout = 4000;
                    using (HttpWebResponse response = (HttpWebResponse)request.GetResponse())
                    using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                    {
                        string json = sr.ReadToEnd();
                        JavaScriptSerializer js = new JavaScriptSerializer();
                        var res = js.Deserialize<Dictionary<string, object>>(json);
                        if (res != null && res.ContainsKey("success") && Convert.ToBoolean(res["success"]) && res.ContainsKey("data"))
                        {
                            match = res["data"] as Dictionary<string, object>;
                        }
                    }
                }
                catch { }
            }

            if (match != null)
            {
                SelectInmate(match);
            }
            else
            {
                MessageBox.Show("Warga Binaan dengan kata kunci '" + query + "' tidak ditemukan.", "Pencarian WBP", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
        }

        private void SelectInmate(Dictionary<string, object> inmate)
        {
            selectedInmate = inmate;
            lblNama.Text = inmate.ContainsKey("nama") ? inmate["nama"].ToString() : "-";
            lblNoReg.Text = "No. Registrasi: " + (inmate.ContainsKey("no_registrasi") ? inmate["no_registrasi"].ToString() : "-");
            lblKamar.Text = "Kamar / Blok: " + (inmate.ContainsKey("blok_kamar") ? inmate["blok_kamar"].ToString() : "-");

            decimal saldo = inmate.ContainsKey("saldo") ? Convert.ToDecimal(inmate["saldo"]) : 0;
            lblCurrentSaldo.Text = "Saldo Sekarang: Rp " + saldo.ToString("N0");

            string st = inmate.ContainsKey("status") ? inmate["status"].ToString().ToLower() : "aktif";
            if (st == "isolasi")
            {
                lblStatus.Text = "Status: Sanksi Isolasi (Register F)";
                lblStatus.ForeColor = Color.FromArgb(220, 38, 38);
            }
            else
            {
                lblStatus.Text = "Status: Aktif";
                lblStatus.ForeColor = Color.FromArgb(16, 185, 129);
            }

            UpdateCalculationPreview();
            txtAmount.Focus();
            txtAmount.SelectAll();
        }

        private void ProcessTopup()
        {
            if (selectedInmate == null)
            {
                MessageBox.Show("Silakan pilih Warga Binaan terlebih dahulu.", "Peringatan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtSearch.Focus();
                return;
            }

            if (currentAmount < 1000)
            {
                MessageBox.Show("Nominal top-up minimal Rp 1.000.", "Nominal Kurang", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                txtAmount.Focus();
                return;
            }

            int inmateId = Convert.ToInt32(selectedInmate["id"]);
            string wbpNama = selectedInmate.ContainsKey("nama") ? selectedInmate["nama"].ToString() : "";
            decimal before = Convert.ToDecimal(selectedInmate["saldo"]);
            decimal after = before + currentAmount;

            string confirmMsg = string.Format(
                "Konfirmasi Setor Saldo Dompet Digital:\n\n" +
                "Nama WBP: {0}\n" +
                "No. Registrasi: {1}\n" +
                "Saldo Saat Ini: Rp {2:N0}\n" +
                "Nominal Top-Up: + Rp {3:N0}\n\n" +
                "Estimasi Saldo Baru: Rp {4:N0}\n\n" +
                "Lanjutkan pemrosesan transaksi?",
                wbpNama,
                selectedInmate.ContainsKey("no_registrasi") ? selectedInmate["no_registrasi"].ToString() : "-",
                before,
                currentAmount,
                after
            );

            if (MessageBox.Show(confirmMsg, "Konfirmasi Top-Up Saldo", MessageBoxButtons.YesNo, MessageBoxIcon.Question) != DialogResult.Yes)
            {
                return;
            }

            btnSubmit.Enabled = false;
            btnSubmit.Text = "Memproses...";

            try
            {
                string endpoint = this.apiUrl.TrimEnd('/') + "/pos/topup";
                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(endpoint);
                req.Method = "POST";
                req.ContentType = "application/json";
                req.Timeout = 6000;

                var payload = new Dictionary<string, object> {
                    { "inmate_id", inmateId },
                    { "amount", currentAmount },
                    { "penyetor", txtPenyetor.Text.Trim() },
                    { "cashier_name", "Kasir POS" },
                    { "notes", txtNotes.Text.Trim() }
                };

                JavaScriptSerializer js = new JavaScriptSerializer();
                string postData = js.Serialize(payload);
                byte[] byteData = System.Text.Encoding.UTF8.GetBytes(postData);
                req.ContentLength = byteData.Length;

                using (Stream stream = req.GetRequestStream())
                {
                    stream.Write(byteData, 0, byteData.Length);
                }

                using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
                using (StreamReader sr = new StreamReader(res.GetResponseStream()))
                {
                    string resJson = sr.ReadToEnd();
                    var dict = js.Deserialize<Dictionary<string, object>>(resJson);

                    if (dict != null && dict.ContainsKey("success") && Convert.ToBoolean(dict["success"]))
                    {
                        SoundHelper.PlaySuccess();

                        // Update in-memory inmate balance
                        selectedInmate["saldo"] = after;
                        if (this.onBalanceUpdated != null)
                        {
                            this.onBalanceUpdated(inmateId, after);
                        }

                        string invoiceNo = "TOPUP-" + DateTime.Now.ToString("yyyyMMddHHmmss");
                        if (dict.ContainsKey("data"))
                        {
                            var dataDict = dict["data"] as Dictionary<string, object>;
                            if (dataDict != null && dataDict.ContainsKey("invoice_no"))
                            {
                                invoiceNo = dataDict["invoice_no"].ToString();
                            }
                        }

                        MessageBox.Show(
                            string.Format("✓ Top-Up Saldo Berhasil!\n\nNo. Invoice: {0}\nNama: {1}\nNominal Setor: Rp {2:N0}\nSisa Saldo Baru: Rp {3:N0}",
                                invoiceNo, wbpNama, currentAmount, after),
                            "Top-Up Berhasil",
                            MessageBoxButtons.OK,
                            MessageBoxIcon.Information
                        );

                        this.DialogResult = DialogResult.OK;
                        this.Close();
                        return;
                    }
                    else
                    {
                        string msg = (dict != null && dict.ContainsKey("message")) ? dict["message"].ToString() : "Gagal memproses top-up.";
                        MessageBox.Show(msg, "Gagal", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    }
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Terjadi kesalahan jaringan atau server:\n" + ex.Message, "Error Top-Up", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            finally
            {
                btnSubmit.Enabled = true;
                btnSubmit.Text = "✓ Proses Setor Saldo [Enter]";
            }
        }
    }
}

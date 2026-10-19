using System;
using System.Drawing;
using System.Windows.Forms;

namespace UangPasDesktop
{
    public class TransactionSuccessDialog : Form
    {
        public bool ShouldPrintReceipt { get; private set; }

        public TransactionSuccessDialog(string invoiceNo, string inmateNama, string noReg, string blokKamar, decimal totalBelanja, decimal sisaSaldo, decimal sisaLimit)
        {
            this.Text = "Transaksi Kasir Berhasil";
            this.Size = new Size(500, 520);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);
            this.KeyPreview = true;

            // Header Banner
            Panel pnlHeader = new Panel {
                Dock = DockStyle.Top,
                Height = 120,
                BackColor = Color.FromArgb(15, 23, 42),
                Padding = new Padding(20, 15, 20, 10)
            };

            Label lblBadge = new Label {
                Text = "✅ PEMBAYARAN BERHASIL",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                Dock = DockStyle.Top,
                Height = 32,
                TextAlign = ContentAlignment.MiddleCenter
            };
            pnlHeader.Controls.Add(lblBadge);

            Label lblInvoice = new Label {
                Text = "No. Transaksi: " + invoiceNo + "  •  " + DateTime.Now.ToString("dd/MM/yyyy HH:mm:ss"),
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(148, 163, 184),
                Dock = DockStyle.Top,
                Height = 22,
                TextAlign = ContentAlignment.MiddleCenter
            };
            pnlHeader.Controls.Add(lblInvoice);

            Label lblAuthType = new Label {
                Text = "🔒 Terverifikasi melalui Sidik Jari Biometrik DigitalPersona",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(56, 189, 248),
                Dock = DockStyle.Top,
                Height = 22,
                TextAlign = ContentAlignment.MiddleCenter
            };
            pnlHeader.Controls.Add(lblAuthType);

            this.Controls.Add(pnlHeader);

            // Detail Card
            Panel pnlCard = new Panel {
                Location = new Point(25, 140),
                Size = new Size(434, 230),
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle
            };

            Label lblInmateTitle = new Label {
                Text = "INFORMASI WARGA BINAAN (WBP)",
                Font = new Font("Segoe UI", 8f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(16, 12),
                Size = new Size(400, 18)
            };
            pnlCard.Controls.Add(lblInmateTitle);

            Label lblNama = new Label {
                Text = inmateNama,
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(16, 32),
                Size = new Size(400, 26)
            };
            pnlCard.Controls.Add(lblNama);

            Label lblSub = new Label {
                Text = "No. Reg: " + noReg + "  |  Kamar: " + (string.IsNullOrEmpty(blokKamar) ? "-" : blokKamar),
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(16, 58),
                Size = new Size(400, 20)
            };
            pnlCard.Controls.Add(lblSub);

            Panel line = new Panel {
                Location = new Point(16, 85),
                Size = new Size(402, 1),
                BackColor = Color.FromArgb(226, 232, 240)
            };
            pnlCard.Controls.Add(line);

            // Nominal rows
            Label lblTotalLabel = new Label {
                Text = "Total Pembayaran POS:",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(16, 96),
                Size = new Size(180, 24)
            };
            pnlCard.Controls.Add(lblTotalLabel);

            Label lblTotalVal = new Label {
                Text = "Rp " + totalBelanja.ToString("N0"),
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(220, 38, 38),
                Location = new Point(200, 93),
                Size = new Size(218, 28),
                TextAlign = ContentAlignment.MiddleRight
            };
            pnlCard.Controls.Add(lblTotalVal);

            Label lblSaldoLabel = new Label {
                Text = "Sisa Saldo Rekening:",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(16, 130),
                Size = new Size(180, 24)
            };
            pnlCard.Controls.Add(lblSaldoLabel);

            Label lblSaldoVal = new Label {
                Text = "Rp " + sisaSaldo.ToString("N0"),
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                ForeColor = Color.FromArgb(5, 150, 105),
                Location = new Point(200, 126),
                Size = new Size(218, 30),
                TextAlign = ContentAlignment.MiddleRight
            };
            pnlCard.Controls.Add(lblSaldoVal);

            Label lblLimitLabel = new Label {
                Text = "Sisa Kuota Belanja Hari Ini:",
                Font = new Font("Segoe UI", 8.5f),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(16, 168),
                Size = new Size(180, 20)
            };
            pnlCard.Controls.Add(lblLimitLabel);

            Label lblLimitVal = new Label {
                Text = "Rp " + sisaLimit.ToString("N0"),
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(200, 166),
                Size = new Size(218, 22),
                TextAlign = ContentAlignment.MiddleRight
            };
            pnlCard.Controls.Add(lblLimitVal);

            this.Controls.Add(pnlCard);

            // Bottom Buttons & Instruction
            Label lblHint = new Label {
                Text = "Tekan [Enter] untuk Cetak Struk, atau [Esc] untuk Selesai",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(25, 385),
                Size = new Size(434, 20),
                TextAlign = ContentAlignment.MiddleCenter
            };
            this.Controls.Add(lblHint);

            Button btnPrint = new Button {
                Text = "🖨️ Cetak Struk (Enter)",
                Location = new Point(25, 415),
                Size = new Size(230, 44),
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPrint.FlatAppearance.BorderSize = 0;
            btnPrint.Click += (s, e) => {
                this.ShouldPrintReceipt = true;
                CloseDialog();
            };
            this.Controls.Add(btnPrint);

            Button btnDone = new Button {
                Text = "Selesai (Esc)",
                Location = new Point(265, 415),
                Size = new Size(194, 44),
                BackColor = Color.FromArgb(71, 85, 105),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnDone.FlatAppearance.BorderSize = 0;
            btnDone.Click += (s, e) => {
                this.ShouldPrintReceipt = false;
                CloseDialog();
            };
            this.Controls.Add(btnDone);

            // Keyboard shortcut hooks
            this.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.Handled = true;
                    this.ShouldPrintReceipt = true;
                    CloseDialog();
                }
                else if (e.KeyCode == Keys.Escape || e.KeyCode == Keys.Space)
                {
                    e.Handled = true;
                    this.ShouldPrintReceipt = false;
                    CloseDialog();
                }
            };
        }

        private void CloseDialog()
        {
            this.DialogResult = DialogResult.OK;
            this.Close();
        }
    }
}

using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Printing;
using System.Text;
using System.Windows.Forms;

namespace UangPasDesktop
{
    public class ReceiptForm : Form
    {
        private string receiptText;
        private ReceiptConfig config;

        public ReceiptForm(string invoiceNo, Dictionary<string, object> inmate, string method, List<CartItem> items, decimal total, decimal saldoBefore, decimal saldoAfter, bool isOffline, ReceiptConfig cfg = null)
        {
            this.config = cfg ?? new ReceiptConfig();
            int lineWidth = (this.config.PaperWidth == "80mm") ? 44 : 32;
            string sepDashed = new string('-', lineWidth);
            string sepDouble = new string('=', lineWidth);

            this.Text = "Struk Belanja Kasir - " + this.config.StoreName;
            this.Size = (this.config.PaperWidth == "80mm") ? new Size(520, 680) : new Size(440, 640);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);

            StringBuilder sb = new StringBuilder();
            sb.AppendLine(sepDouble);
            sb.AppendLine(PadCenter(this.config.StoreName, lineWidth));
            if (!string.IsNullOrEmpty(this.config.Address))
                sb.AppendLine(PadCenter(this.config.Address, lineWidth));
            if (!string.IsNullOrEmpty(this.config.Phone))
                sb.AppendLine(PadCenter("Telp: " + this.config.Phone, lineWidth));
            sb.AppendLine(sepDouble);

            if (isOffline)
            {
                sb.AppendLine(PadCenter("*** MODE OFFLINE - ANTREAN ***", lineWidth));
                sb.AppendLine(sepDashed);
            }

            sb.AppendLine("No. Struk : " + invoiceNo);
            sb.AppendLine("Waktu     : " + DateTime.Now.ToString("dd/MM/yyyy HH:mm"));
            sb.AppendLine("Kasir     : Kasir POS");
            sb.AppendLine("Metode    : " + (method == "fingerprint" ? "SIDIK JARI WBP" : "MANUAL"));
            sb.AppendLine(sepDashed);

            string wbpNama = inmate.ContainsKey("nama") ? inmate["nama"].ToString() : "-";
            string wbpReg = inmate.ContainsKey("no_registrasi") ? inmate["no_registrasi"].ToString() : "-";
            string wbpKamar = inmate.ContainsKey("blok_kamar") ? inmate["blok_kamar"].ToString() : "-";
            sb.AppendLine("WBP       : " + (wbpNama.Length > lineWidth - 12 ? wbpNama.Substring(0, lineWidth - 12) : wbpNama));
            sb.AppendLine("No. Reg   : " + wbpReg);
            sb.AppendLine("Kamar     : " + wbpKamar);
            sb.AppendLine(sepDashed);

            if (lineWidth == 44)
            {
                sb.AppendLine(string.Format("{0,-24} {1,5} {2,13}", "BARANG", "QTY", "SUBTOTAL"));
                sb.AppendLine(sepDashed);
                foreach (var it in items)
                {
                    string nama = it.Nama;
                    if (nama.Length > 23) nama = nama.Substring(0, 23);
                    decimal sub = it.Harga * it.Qty;
                    sb.AppendLine(string.Format("{0,-24} {1,5} {2,13}", nama, "x" + it.Qty, "Rp " + sub.ToString("N0")));
                }
                sb.AppendLine(sepDashed);
                sb.AppendLine(string.Format("{0,-22} {1,22}", "TOTAL TAGIHAN", "Rp " + total.ToString("N0")));
                if (this.config.ShowBalance)
                {
                    sb.AppendLine(string.Format("{0,-22} {1,22}", "Saldo Sebelum", "Rp " + saldoBefore.ToString("N0")));
                    sb.AppendLine(string.Format("{0,-22} {1,22}", "SISA SALDO WBP", "Rp " + saldoAfter.ToString("N0")));
                }
            }
            else
            {
                sb.AppendLine(string.Format("{0,-16} {1,4} {2,10}", "BARANG", "QTY", "SUBTOTAL"));
                sb.AppendLine(sepDashed);
                foreach (var it in items)
                {
                    string nama = it.Nama;
                    if (nama.Length > 15) nama = nama.Substring(0, 15);
                    decimal sub = it.Harga * it.Qty;
                    sb.AppendLine(string.Format("{0,-16} {1,4} {2,10}", nama, "x" + it.Qty, "Rp " + sub.ToString("N0")));
                }
                sb.AppendLine(sepDashed);
                sb.AppendLine(string.Format("{0,-14} {1,18}", "TOTAL TAGIHAN", "Rp " + total.ToString("N0")));
                if (this.config.ShowBalance)
                {
                    sb.AppendLine(string.Format("{0,-14} {1,18}", "Saldo Sebelum", "Rp " + saldoBefore.ToString("N0")));
                    sb.AppendLine(string.Format("{0,-14} {1,18}", "SISA SALDO WBP", "Rp " + saldoAfter.ToString("N0")));
                }
            }

            sb.AppendLine(sepDouble);
            if (!string.IsNullOrEmpty(this.config.Footer))
            {
                string[] lines = this.config.Footer.Split('\n');
                foreach (string line in lines)
                {
                    string trimmed = line.Trim();
                    if (!string.IsNullOrEmpty(trimmed))
                    {
                        sb.AppendLine(PadCenter(trimmed, lineWidth));
                    }
                }
                sb.AppendLine(sepDouble);
            }

            this.receiptText = sb.ToString();

            TextBox txtReceipt = new TextBox {
                Multiline = true,
                ReadOnly = true,
                ScrollBars = ScrollBars.Vertical,
                Font = new Font("Consolas", 10f),
                BackColor = Color.White,
                ForeColor = Color.FromArgb(15, 23, 42),
                Dock = DockStyle.Fill,
                Text = receiptText
            };
            this.Controls.Add(txtReceipt);

            Panel bottom = new Panel {
                Dock = DockStyle.Bottom,
                Height = 60,
                Padding = new Padding(12),
                BackColor = Color.FromArgb(241, 245, 249)
            };

            Button btnClose = new Button {
                Text = "Tutup",
                Dock = DockStyle.Left,
                Width = 110,
                BackColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnClose.Click += (s, e) => this.Close();
            bottom.Controls.Add(btnClose);

            Button btnPrint = new Button {
                Text = "🖨️ Cetak ke Printer Kasir",
                Dock = DockStyle.Right,
                Width = 220,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPrint.Click += (s, e) => PrintThermalReceipt();
            bottom.Controls.Add(btnPrint);

            this.Controls.Add(bottom);
            txtReceipt.BringToFront();

            if (this.config.AutoPrint)
            {
                this.Shown += (s, e) => {
                    PrintThermalReceipt(true);
                };
            }
        }

        private static string PadCenter(string text, int width)
        {
            if (string.IsNullOrEmpty(text)) return "";
            if (text.Length >= width) return text.Substring(0, width);
            int leftPad = (width - text.Length) / 2;
            int rightPad = width - text.Length - leftPad;
            return new string(' ', leftPad) + text + new string(' ', rightPad);
        }

        private void PrintThermalReceipt(bool silent = false)
        {
            try
            {
                PrintDocument pDoc = new PrintDocument();
                pDoc.PrintPage += (s, e) => {
                    Font f = new Font("Consolas", (this.config.PaperWidth == "80mm") ? 9.5f : 8.5f);
                    e.Graphics.DrawString(this.receiptText, f, Brushes.Black, new PointF(5, 5));
                };

                if (silent)
                {
                    pDoc.Print();
                }
                else
                {
                    PrintDialog pd = new PrintDialog();
                    pd.Document = pDoc;
                    if (pd.ShowDialog() == DialogResult.OK)
                    {
                        pDoc.Print();
                    }
                }
            }
            catch (Exception ex)
            {
                if (!silent)
                {
                    MessageBox.Show("Gagal mencetak: " + ex.Message, "Error Printer", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            }
        }
    }
}

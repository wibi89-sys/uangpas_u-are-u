using System;
using System.Drawing;
using System.Drawing.Printing;
using System.Text;
using System.Windows.Forms;

namespace UangPasDesktop
{
    public class ShiftRekapForm : Form
    {
        private string rekapText;

        public ShiftRekapForm(string cashier, DateTime startTime, decimal modalAwal, int trxCount, decimal totalOmset)
        {
            this.Text = "Rekap Shift Kasir & Laporan Omset (Z-Report)";
            this.Size = new Size(460, 620);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);

            StringBuilder sb = new StringBuilder();
            sb.AppendLine("================================================");
            sb.AppendLine("            KOPERASI LAPAS DIGITAL             ");
            sb.AppendLine("       LAPORAN REKAP SHIFT KASIR (Z-REPORT)    ");
            sb.AppendLine("================================================");
            sb.AppendLine("Petugas Kasir: " + cashier);
            sb.AppendLine("Waktu Buka   : " + startTime.ToString("dd/MM/yyyy HH:mm:ss"));
            sb.AppendLine("Waktu Tutup  : " + DateTime.Now.ToString("dd/MM/yyyy HH:mm:ss"));
            TimeSpan dur = DateTime.Now - startTime;
            sb.AppendLine("Durasi Shift : " + string.Format("{0} jam {1} menit", (int)dur.TotalHours, dur.Minutes));
            sb.AppendLine("------------------------------------------------");
            sb.AppendLine(string.Format("{0,-24} {1,21}", "Modal Kasir Awal", "Rp " + modalAwal.ToString("N0")));
            sb.AppendLine("------------------------------------------------");
            sb.AppendLine(string.Format("{0,-24} {1,21}", "Total Transaksi POS", trxCount + " Transaksi"));
            sb.AppendLine(string.Format("{0,-24} {1,21}", "Total Omset Belanja", "Rp " + totalOmset.ToString("N0")));
            sb.AppendLine("------------------------------------------------");
            sb.AppendLine(string.Format("{0,-24} {1,21}", "TOTAL REKAP SHIFT", "Rp " + (modalAwal + totalOmset).ToString("N0")));
            sb.AppendLine("================================================");
            sb.AppendLine("  Dicetak otomatis Terminal Kasir POS UangPas   ");
            sb.AppendLine("================================================");

            this.rekapText = sb.ToString();

            TextBox txt = new TextBox {
                Multiline = true,
                ReadOnly = true,
                ScrollBars = ScrollBars.Vertical,
                Font = new Font("Consolas", 10f),
                BackColor = Color.White,
                ForeColor = Color.FromArgb(15, 23, 42),
                Dock = DockStyle.Fill,
                Text = rekapText
            };
            this.Controls.Add(txt);

            Panel bottom = new Panel {
                Dock = DockStyle.Bottom,
                Height = 60,
                Padding = new Padding(12),
                BackColor = Color.FromArgb(241, 245, 249)
            };

            Button btnPrint = new Button {
                Text = "🖨️ Cetak Struk Z-Report",
                Dock = DockStyle.Right,
                Width = 200,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPrint.Click += (s, e) => {
                try {
                    PrintDocument pDoc = new PrintDocument();
                    pDoc.PrintPage += (ps, pe) => {
                        Font f = new Font("Consolas", 9.5f);
                        pe.Graphics.DrawString(this.rekapText, f, Brushes.Black, new PointF(10, 10));
                    };
                    PrintDialog pd = new PrintDialog();
                    pd.Document = pDoc;
                    if (pd.ShowDialog() == DialogResult.OK) pDoc.Print();
                } catch (Exception ex) {
                    MessageBox.Show("Gagal mencetak: " + ex.Message, "Printer", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            };
            bottom.Controls.Add(btnPrint);

            Button btnCloseShift = new Button {
                Text = "🔒 Tutup Shift & Reset",
                Dock = DockStyle.Left,
                Width = 170,
                BackColor = Color.FromArgb(239, 68, 68),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCloseShift.Click += (s, e) => {
                if (MessageBox.Show("Tutup shift saat ini dan reset akumulasi transaksi kasir?", "Konfirmasi Tutup Shift", MessageBoxButtons.YesNo, MessageBoxIcon.Question) == DialogResult.Yes)
                {
                    this.DialogResult = DialogResult.OK;
                    this.Close();
                }
            };
            bottom.Controls.Add(btnCloseShift);

            this.Controls.Add(bottom);
            txt.BringToFront();
        }
    }
}

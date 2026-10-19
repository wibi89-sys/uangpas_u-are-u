using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Windows.Forms;
using UangPasDesktop;

namespace UangPasDesktop
{
    public class CustomerDisplayForm : Form
    {
        private Timer clockTimer;
        private Timer autoResetTimer;
        private Label lblClock;

        // Container Panels
        private Panel pnlIdle;
        private Panel pnlCart;
        private Panel pnlVerify;
        private Panel pnlSuccess;
        private Panel pnlCekSaldo;

        // Cart Mode Controls
        private DataGridView gridCart;
        private Label lblCartTotal;
        private Label lblCartCount;

        // Verify Mode Controls
        private Label lblVerifyNama;
        private Label lblVerifyReg;
        private Label lblVerifySaldoBefore;
        private Label lblVerifyTotal;
        private Label lblVerifySaldoAfter;
        private Label lblVerifyQuota;

        // Success Mode Controls
        private Label lblSuccessInvoice;
        private Label lblSuccessNama;
        private Label lblSuccessTotal;
        private Label lblSuccessSaldo;

        // Cek Saldo Mode Controls
        private Label lblKioskNama;
        private Label lblKioskReg;
        private Label lblKioskSaldo;
        private Label lblKioskLimit;

        public CustomerDisplayForm()
        {
            this.Text = "Layar Pelanggan / Layar Kedua (Customer Facing Display)";
            this.BackColor = Color.FromArgb(15, 23, 42);
            this.DoubleBuffered = true;

            // Detect multiple screens
            if (Screen.AllScreens.Length > 1)
            {
                Screen second = Screen.AllScreens[1];
                this.StartPosition = FormStartPosition.Manual;
                this.Location = second.Bounds.Location;
                this.Size = second.Bounds.Size;
                this.FormBorderStyle = FormBorderStyle.None;
                this.WindowState = FormWindowState.Maximized;
            }
            else
            {
                this.StartPosition = FormStartPosition.CenterScreen;
                this.Size = new Size(880, 640);
                this.FormBorderStyle = FormBorderStyle.Sizable;
                this.MinimumSize = new Size(760, 500);
                this.TopMost = true; // Stays on top during single monitor testing
            }

            BuildHeader();
            BuildPanels();

            // Clock Timer
            clockTimer = new Timer { Interval = 1000 };
            clockTimer.Tick += (s, e) => {
                if (lblClock != null && !lblClock.IsDisposed)
                {
                    lblClock.Text = DateTime.Now.ToString("HH:mm:ss") + " WIB • " + DateTime.Now.ToString("dd MMM yyyy");
                }
            };
            clockTimer.Start();

            // Auto Reset Timer (e.g. after success screen)
            autoResetTimer = new Timer { Interval = 6000 };
            autoResetTimer.Tick += (s, e) => {
                autoResetTimer.Stop();
                ShowIdle();
            };

            this.FormClosing += (s, e) => {
                clockTimer.Stop();
                autoResetTimer.Stop();
            };

            this.Shown += (s, e) => {
                CenterCard(idleCard, pnlIdle);
                CenterCard(verifyCard, pnlVerify);
                CenterCard(successCard, pnlSuccess);
                CenterCard(saldoCard, pnlCekSaldo);
            };

            ShowIdle();
        }

        private void BuildHeader()
        {
            Panel top = new Panel {
                Dock = DockStyle.Top,
                Height = 72,
                BackColor = Color.FromArgb(10, 15, 30),
                Padding = new Padding(24, 12, 24, 12)
            };

            PictureBox picLogo = new PictureBox {
                Dock = DockStyle.Left,
                Width = 48,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            string walletPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "wallet_logo.png");
            if (File.Exists(walletPath))
            {
                try
                {
                    byte[] bytes = File.ReadAllBytes(walletPath);
                    using (MemoryStream ms = new MemoryStream(bytes))
                    {
                        picLogo.Image = new Bitmap(ms);
                    }
                }
                catch { }
            }
            top.Controls.Add(picLogo);

            Panel pnlTitle = new Panel {
                Dock = DockStyle.Left,
                Width = 450,
                Padding = new Padding(14, 2, 0, 0)
            };
            Label lblTitle = new Label {
                Text = "KOPERASI PENGAYOMAN LAPAS",
                Font = new Font("Segoe UI", 12.5f, FontStyle.Bold),
                ForeColor = Color.White,
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlTitle.Controls.Add(lblTitle);

            Label lblSub = new Label {
                Text = "Sistem Transaksi Dompet Digital & Biometrik Warga Binaan",
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(148, 163, 184),
                Dock = DockStyle.Top,
                Height = 20
            };
            pnlTitle.Controls.Add(lblSub);
            top.Controls.Add(pnlTitle);

            lblClock = new Label {
                Text = DateTime.Now.ToString("HH:mm:ss") + " WIB • " + DateTime.Now.ToString("dd MMM yyyy"),
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(56, 189, 248),
                Dock = DockStyle.Right,
                Width = 300,
                TextAlign = ContentAlignment.MiddleRight
            };
            top.Controls.Add(lblClock);

            this.Controls.Add(top);
        }

        // Cards
        private Panel idleCard;
        private Panel verifyCard;
        private Panel successCard;
        private Panel saldoCard;

        private void CenterCard(Control card, Control parent)
        {
            if (card == null || parent == null || parent.ClientSize.Width <= 0 || parent.ClientSize.Height <= 0) return;
            int x = Math.Max(10, (parent.ClientSize.Width - card.Width) / 2);
            int y = Math.Max(10, (parent.ClientSize.Height - card.Height) / 2);
            card.Location = new Point(x, y);
        }

        private void BuildPanels()
        {
            Panel body = new Panel {
                Dock = DockStyle.Fill,
                Padding = new Padding(24),
                BackColor = Color.FromArgb(15, 23, 42)
            };

            // 1. IDLE / WELCOME PANEL
            pnlIdle = new Panel { Dock = DockStyle.Fill, Visible = true, BackColor = Color.FromArgb(15, 23, 42) };
            
            idleCard = new Panel {
                Width = 680,
                Height = 380,
                BackColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(24)
            };

            PictureBox picIdleLogo = new PictureBox {
                Location = new Point((idleCard.Width - 84) / 2, 22),
                Size = new Size(84, 84),
                SizeMode = PictureBoxSizeMode.Zoom
            };
            string fpPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (File.Exists(fpPath))
            {
                try
                {
                    byte[] bytes = File.ReadAllBytes(fpPath);
                    using (MemoryStream ms = new MemoryStream(bytes))
                    {
                        picIdleLogo.Image = new Bitmap(ms);
                    }
                }
                catch { }
            }
            idleCard.Controls.Add(picIdleLogo);

            Label lblIdleWelcome = new Label {
                Text = "SELAMAT DATANG DI KASIR KOPERASI",
                Font = new Font("Segoe UI", 18f, FontStyle.Bold),
                ForeColor = Color.White,
                Location = new Point(10, 118),
                Width = idleCard.Width - 20,
                Height = 38,
                TextAlign = ContentAlignment.MiddleCenter
            };
            idleCard.Controls.Add(lblIdleWelcome);

            Label lblIdleSub = new Label {
                Text = "Silakan letakkan barang belanjaan di meja kasir.\nSistem pembayaran menggunakan Sidik Jari Biometrik Resmi WBP.",
                Font = new Font("Segoe UI", 11.5f),
                ForeColor = Color.FromArgb(203, 213, 225),
                Location = new Point(10, 164),
                Width = idleCard.Width - 20,
                Height = 52,
                TextAlign = ContentAlignment.MiddleCenter
            };
            idleCard.Controls.Add(lblIdleSub);

            Label lblSensorStatus = new Label {
                Text = "● SENSOR BIOMETRIK SIAGA",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                BackColor = Color.FromArgb(6, 78, 59),
                Location = new Point((idleCard.Width - 280) / 2, 228),
                Width = 280,
                Height = 38,
                TextAlign = ContentAlignment.MiddleCenter
            };
            idleCard.Controls.Add(lblSensorStatus);

            Label lblTip = new Label {
                Text = "💡 Anda juga dapat menempelkan jari Anda untuk cek sisa saldo dompet secara mandiri.",
                Font = new Font("Segoe UI", 10f, FontStyle.Italic),
                ForeColor = Color.FromArgb(148, 163, 184),
                Location = new Point(10, 282),
                Width = idleCard.Width - 20,
                Height = 40,
                TextAlign = ContentAlignment.MiddleCenter
            };
            idleCard.Controls.Add(lblTip);

            pnlIdle.Controls.Add(idleCard);
            pnlIdle.Resize += (s, e) => CenterCard(idleCard, pnlIdle);
            body.Controls.Add(pnlIdle);

            // 2. ACTIVE CART PANEL (LIVE MIRROR)
            pnlCart = new Panel { Dock = DockStyle.Fill, Visible = false, BackColor = Color.FromArgb(15, 23, 42) };
            
            // Left: Grid
            Panel pnlGridHolder = new Panel {
                Dock = DockStyle.Fill,
                Padding = new Padding(0, 0, 16, 0)
            };

            gridCart = new DataGridView {
                Dock = DockStyle.Fill,
                BackgroundColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.None,
                RowHeadersVisible = false,
                AllowUserToAddRows = false,
                AllowUserToDeleteRows = false,
                ReadOnly = true,
                SelectionMode = DataGridViewSelectionMode.FullRowSelect,
                AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill
            };
            gridCart.ColumnHeadersHeightSizeMode = DataGridViewColumnHeadersHeightSizeMode.DisableResizing;
            gridCart.ColumnHeadersHeight = 38;
            gridCart.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(51, 65, 85);
            gridCart.ColumnHeadersDefaultCellStyle.ForeColor = Color.White;
            gridCart.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 10f, FontStyle.Bold);
            gridCart.RowTemplate.Height = 36;
            gridCart.RowsDefaultCellStyle.Font = new Font("Segoe UI", 10f);
            gridCart.RowsDefaultCellStyle.BackColor = Color.FromArgb(30, 41, 59);
            gridCart.RowsDefaultCellStyle.ForeColor = Color.White;
            gridCart.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(41, 53, 72);

            gridCart.Columns.Add("no", "#");
            gridCart.Columns["no"].FillWeight = 15;
            gridCart.Columns["no"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;

            gridCart.Columns.Add("nama", "Nama Barang");
            gridCart.Columns["nama"].FillWeight = 48;

            gridCart.Columns.Add("qty", "Qty");
            gridCart.Columns["qty"].FillWeight = 15;
            gridCart.Columns["qty"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns["qty"].DefaultCellStyle.Font = new Font("Segoe UI", 10f, FontStyle.Bold);

            gridCart.Columns.Add("harga", "Harga");
            gridCart.Columns["harga"].FillWeight = 26;
            gridCart.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;

            gridCart.Columns.Add("subtotal", "Subtotal");
            gridCart.Columns["subtotal"].FillWeight = 30;
            gridCart.Columns["subtotal"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["subtotal"].DefaultCellStyle.Font = new Font("Segoe UI", 10f, FontStyle.Bold);
            gridCart.Columns["subtotal"].DefaultCellStyle.ForeColor = Color.FromArgb(56, 189, 248);

            pnlGridHolder.Controls.Add(gridCart);
            pnlCart.Controls.Add(pnlGridHolder);

            // Right: Grand Total Card
            Panel pnlCartRight = new Panel {
                Dock = DockStyle.Right,
                Width = 320,
                BackColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(20)
            };

            Label lblTotalCaption = new Label {
                Text = "TOTAL PEMBELIAN:",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(148, 163, 184),
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlCartRight.Controls.Add(lblTotalCaption);

            lblCartTotal = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 26f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                Dock = DockStyle.Top,
                Height = 65,
                TextAlign = ContentAlignment.MiddleLeft
            };
            pnlCartRight.Controls.Add(lblCartTotal);

            lblCartCount = new Label {
                Text = "0 Barang Belanjaan",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(203, 213, 225),
                Dock = DockStyle.Top,
                Height = 30
            };
            pnlCartRight.Controls.Add(lblCartCount);

            Panel pnlPayInstruction = new Panel {
                Dock = DockStyle.Bottom,
                Height = 160,
                BackColor = Color.FromArgb(15, 23, 42),
                Padding = new Padding(14)
            };
            Label lblPayTitle = new Label {
                Text = "🔒 PEMBAYARAN SIDIK JARI",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(56, 189, 248),
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlPayInstruction.Controls.Add(lblPayTitle);

            Label lblPayDesc = new Label {
                Text = "Saat kasir siap melakukan checkout, silakan tempelkan jari Anda pada sensor sidik jari.",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(203, 213, 225),
                Dock = DockStyle.Fill
            };
            pnlPayInstruction.Controls.Add(lblPayDesc);
            pnlCartRight.Controls.Add(pnlPayInstruction);

            pnlCart.Controls.Add(pnlCartRight);
            body.Controls.Add(pnlCart);

            // 3. BIOMETRIC VERIFICATION & SALDO CHECK PANEL
            pnlVerify = new Panel { Dock = DockStyle.Fill, Visible = false, BackColor = Color.FromArgb(15, 23, 42) };
            verifyCard = new Panel {
                Width = 640,
                Height = 410,
                BackColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(24)
            };

            Label lblVerifyHeader = new Label {
                Text = "IDENTIFIKASI BIOMETRIK WARGA BINAAN",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(56, 189, 248),
                Location = new Point(20, 16),
                Width = 580,
                Height = 26,
                TextAlign = ContentAlignment.MiddleCenter
            };
            verifyCard.Controls.Add(lblVerifyHeader);

            lblVerifyNama = new Label {
                Text = "Nama WBP: -",
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.White,
                Location = new Point(20, 48),
                Width = 580,
                Height = 36,
                TextAlign = ContentAlignment.MiddleCenter
            };
            verifyCard.Controls.Add(lblVerifyNama);

            lblVerifyReg = new Label {
                Text = "No. Reg: -  •  Kamar: -",
                Font = new Font("Consolas", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(148, 163, 184),
                Location = new Point(20, 84),
                Width = 580,
                Height = 24,
                TextAlign = ContentAlignment.MiddleCenter
            };
            verifyCard.Controls.Add(lblVerifyReg);

            // Calculation Box
            Panel pnlCalc = new Panel {
                Location = new Point(30, 120),
                Width = 560,
                Height = 180,
                BackColor = Color.FromArgb(15, 23, 42),
                Padding = new Padding(20, 14, 20, 14)
            };

            lblVerifySaldoBefore = new Label {
                Text = "Saldo Sebelum Transaksi: Rp 0",
                Font = new Font("Segoe UI", 11f),
                ForeColor = Color.FromArgb(203, 213, 225),
                Location = new Point(14, 12),
                Width = 530,
                Height = 26
            };
            pnlCalc.Controls.Add(lblVerifySaldoBefore);

            lblVerifyTotal = new Label {
                Text = "Total Belanja Saat Ini: - Rp 0",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(248, 113, 113),
                Location = new Point(14, 42),
                Width = 530,
                Height = 28
            };
            pnlCalc.Controls.Add(lblVerifyTotal);

            Label lblDivider = new Label {
                BackColor = Color.FromArgb(51, 65, 85),
                Location = new Point(14, 76),
                Width = 530,
                Height = 2
            };
            pnlCalc.Controls.Add(lblDivider);

            lblVerifySaldoAfter = new Label {
                Text = "Sisa Saldo Baru: Rp 0",
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                Location = new Point(14, 88),
                Width = 530,
                Height = 40
            };
            pnlCalc.Controls.Add(lblVerifySaldoAfter);

            lblVerifyQuota = new Label {
                Text = "Sisa Kuota Belanja Hari Ini: Rp 0",
                Font = new Font("Segoe UI", 10f),
                ForeColor = Color.FromArgb(56, 189, 248),
                Location = new Point(14, 136),
                Width = 530,
                Height = 26
            };
            pnlCalc.Controls.Add(lblVerifyQuota);

            verifyCard.Controls.Add(pnlCalc);

            Label lblConfirmHint = new Label {
                Text = "✓ Saldo dompet mencukupi untuk pembayaran transaksi ini.",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                Location = new Point(20, 320),
                Width = 580,
                Height = 30,
                TextAlign = ContentAlignment.MiddleCenter
            };
            verifyCard.Controls.Add(lblConfirmHint);

            pnlVerify.Controls.Add(verifyCard);
            pnlVerify.Resize += (s, e) => CenterCard(verifyCard, pnlVerify);
            body.Controls.Add(pnlVerify);

            // 4. TRANSACTION SUCCESS PANEL
            pnlSuccess = new Panel { Dock = DockStyle.Fill, Visible = false, BackColor = Color.FromArgb(15, 23, 42) };
            successCard = new Panel {
                Width = 640,
                Height = 390,
                BackColor = Color.FromArgb(6, 78, 59),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(24)
            };

            Label lblSuccessIcon = new Label {
                Text = "✓",
                Font = new Font("Segoe UI", 48f, FontStyle.Bold),
                ForeColor = Color.FromArgb(52, 211, 153),
                Location = new Point(20, 10),
                Width = 580,
                Height = 80,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessIcon);

            Label lblSuccessTitle = new Label {
                Text = "TRANSAKSI PEMBAYARAN BERHASIL!",
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.White,
                Location = new Point(20, 95),
                Width = 580,
                Height = 36,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessTitle);

            lblSuccessInvoice = new Label {
                Text = "No. Nota: INV-20261019-0001",
                Font = new Font("Consolas", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(209, 250, 229),
                Location = new Point(20, 135),
                Width = 580,
                Height = 24,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessInvoice);

            lblSuccessNama = new Label {
                Text = "WBP: AHMAD FAUZI",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.White,
                Location = new Point(20, 165),
                Width = 580,
                Height = 28,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessNama);

            lblSuccessTotal = new Label {
                Text = "Total Terpotong: Rp 45.000",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(254, 240, 138),
                Location = new Point(20, 200),
                Width = 580,
                Height = 32,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessTotal);

            lblSuccessSaldo = new Label {
                Text = "Sisa Saldo Dompet: Rp 105.000",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(167, 243, 208),
                Location = new Point(20, 235),
                Width = 580,
                Height = 32,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblSuccessSaldo);

            Label lblThanks = new Label {
                Text = "Terima kasih telah berbelanja secara tertib di Koperasi Pengayoman.",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(209, 250, 229),
                Location = new Point(20, 285),
                Width = 580,
                Height = 30,
                TextAlign = ContentAlignment.MiddleCenter
            };
            successCard.Controls.Add(lblThanks);

            pnlSuccess.Controls.Add(successCard);
            pnlSuccess.Resize += (s, e) => CenterCard(successCard, pnlSuccess);
            body.Controls.Add(pnlSuccess);

            // 5. SELF-SERVICE CEK SALDO PANEL
            pnlCekSaldo = new Panel { Dock = DockStyle.Fill, Visible = false, BackColor = Color.FromArgb(15, 23, 42) };
            saldoCard = new Panel {
                Width = 640,
                Height = 370,
                BackColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(24)
            };

            Label lblKioskTitle = new Label {
                Text = "PENGECEKAN SALDO MANDIRI (KIOSK)",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(56, 189, 248),
                Location = new Point(20, 16),
                Width = 580,
                Height = 26,
                TextAlign = ContentAlignment.MiddleCenter
            };
            saldoCard.Controls.Add(lblKioskTitle);

            lblKioskNama = new Label {
                Text = "Nama WBP: -",
                Font = new Font("Segoe UI", 16f, FontStyle.Bold),
                ForeColor = Color.White,
                Location = new Point(20, 48),
                Width = 580,
                Height = 36,
                TextAlign = ContentAlignment.MiddleCenter
            };
            saldoCard.Controls.Add(lblKioskNama);

            lblKioskReg = new Label {
                Text = "No. Registrasi: -  •  Kamar: -",
                Font = new Font("Consolas", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(148, 163, 184),
                Location = new Point(20, 84),
                Width = 580,
                Height = 24,
                TextAlign = ContentAlignment.MiddleCenter
            };
            saldoCard.Controls.Add(lblKioskReg);

            Panel saldoBox = new Panel {
                Location = new Point(40, 120),
                Width = 540,
                Height = 110,
                BackColor = Color.FromArgb(6, 78, 59),
                Padding = new Padding(16)
            };
            Label lblSaldoCaption = new Label {
                Text = "SISA SALDO DOMPET DIGITAL:",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(167, 243, 208),
                Dock = DockStyle.Top,
                Height = 22
            };
            saldoBox.Controls.Add(lblSaldoCaption);

            lblKioskSaldo = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 26f, FontStyle.Bold),
                ForeColor = Color.White,
                Dock = DockStyle.Fill,
                TextAlign = ContentAlignment.MiddleLeft
            };
            saldoBox.Controls.Add(lblKioskSaldo);
            saldoCard.Controls.Add(saldoBox);

            lblKioskLimit = new Label {
                Text = "Sisa Kuota Belanja Hari Ini: Rp 0",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(56, 189, 248),
                Location = new Point(20, 250),
                Width = 580,
                Height = 30,
                TextAlign = ContentAlignment.MiddleCenter
            };
            saldoCard.Controls.Add(lblKioskLimit);

            pnlCekSaldo.Controls.Add(saldoCard);
            pnlCekSaldo.Resize += (s, e) => CenterCard(saldoCard, pnlCekSaldo);
            body.Controls.Add(pnlCekSaldo);

            this.Controls.Add(body);
        }

        // ==========================================
        // PUBLIC STATE CONTROLLERS
        // ==========================================

        public void ShowIdle()
        {
            if (this.InvokeRequired)
            {
                this.BeginInvoke(new Action(ShowIdle));
                return;
            }
            autoResetTimer.Stop();
            CenterCard(idleCard, pnlIdle);
            pnlIdle.Visible = true;
            pnlCart.Visible = false;
            pnlVerify.Visible = false;
            pnlSuccess.Visible = false;
            pnlCekSaldo.Visible = false;
        }

        public void UpdateCart(List<CartItem> items, decimal total)
        {
            if (this.InvokeRequired)
            {
                this.BeginInvoke(new Action(() => UpdateCart(items, total)));
                return;
            }

            if (items == null || items.Count == 0)
            {
                ShowIdle();
                return;
            }

            autoResetTimer.Stop();
            pnlIdle.Visible = false;
            pnlCart.Visible = true;
            pnlVerify.Visible = false;
            pnlSuccess.Visible = false;
            pnlCekSaldo.Visible = false;

            gridCart.Rows.Clear();
            int idx = 1;
            int totalItems = 0;
            foreach (var it in items)
            {
                decimal sub = it.Harga * it.Qty;
                totalItems += it.Qty;
                gridCart.Rows.Add(
                    idx++,
                    it.Nama,
                    it.Qty,
                    "Rp " + it.Harga.ToString("N0"),
                    "Rp " + sub.ToString("N0")
                );
            }

            lblCartTotal.Text = "Rp " + total.ToString("N0");
            lblCartCount.Text = totalItems + " Barang Belanjaan";
        }

        public void ShowBiometricVerification(string nama, string noReg, string blok, decimal before, decimal total, decimal after, decimal quota)
        {
            if (this.InvokeRequired)
            {
                this.BeginInvoke(new Action(() => ShowBiometricVerification(nama, noReg, blok, before, total, after, quota)));
                return;
            }

            autoResetTimer.Stop();
            CenterCard(verifyCard, pnlVerify);
            pnlIdle.Visible = false;
            pnlCart.Visible = false;
            pnlVerify.Visible = true;
            pnlSuccess.Visible = false;
            pnlCekSaldo.Visible = false;

            lblVerifyNama.Text = nama;
            lblVerifyReg.Text = "No. Reg: " + noReg + "  •  Kamar: " + blok;
            lblVerifySaldoBefore.Text = "Saldo Sebelum Transaksi: Rp " + before.ToString("N0");
            lblVerifyTotal.Text = "Total Belanja Saat Ini: - Rp " + total.ToString("N0");
            lblVerifySaldoAfter.Text = "Sisa Saldo Baru: Rp " + after.ToString("N0");
            lblVerifyQuota.Text = "Sisa Kuota Belanja Hari Ini: Rp " + quota.ToString("N0");
        }

        public void ShowSuccess(string invoiceNo, string nama, decimal total, decimal balanceAfter)
        {
            if (this.InvokeRequired)
            {
                this.BeginInvoke(new Action(() => ShowSuccess(invoiceNo, nama, total, balanceAfter)));
                return;
            }

            CenterCard(successCard, pnlSuccess);
            pnlIdle.Visible = false;
            pnlCart.Visible = false;
            pnlVerify.Visible = false;
            pnlSuccess.Visible = true;
            pnlCekSaldo.Visible = false;

            lblSuccessInvoice.Text = "No. Nota: " + invoiceNo;
            lblSuccessNama.Text = "WBP: " + nama;
            lblSuccessTotal.Text = "Total Terpotong: Rp " + total.ToString("N0");
            lblSuccessSaldo.Text = "Sisa Saldo Dompet: Rp " + balanceAfter.ToString("N0");

            autoResetTimer.Interval = 5000;
            autoResetTimer.Start();
        }

        public void ShowKioskCheckSaldo(string nama, string noReg, string blok, decimal saldo, decimal limit)
        {
            if (this.InvokeRequired)
            {
                this.BeginInvoke(new Action(() => ShowKioskCheckSaldo(nama, noReg, blok, saldo, limit)));
                return;
            }

            CenterCard(saldoCard, pnlCekSaldo);
            pnlIdle.Visible = false;
            pnlCart.Visible = false;
            pnlVerify.Visible = false;
            pnlSuccess.Visible = false;
            pnlCekSaldo.Visible = true;

            lblKioskNama.Text = nama;
            lblKioskReg.Text = "No. Reg: " + noReg + "  •  Kamar: " + blok;
            lblKioskSaldo.Text = "Rp " + saldo.ToString("N0");
            lblKioskLimit.Text = "Sisa Kuota Belanja Hari Ini: Rp " + limit.ToString("N0");

            autoResetTimer.Interval = 7000;
            autoResetTimer.Start();
        }
    }
}

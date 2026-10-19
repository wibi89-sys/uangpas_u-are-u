using System;
using System.Collections.Generic;
using System.Drawing;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using DPUruNet;
using UangPasDesktop.Models;

namespace UangPasDesktop
{
    public partial class MainForm : Form
    {
        // Settings & API
        private string apiUrl = "http://127.0.0.1:8000/api/v1";
        private JavaScriptSerializer serializer = new JavaScriptSerializer();

        // Biometric Reader
        private Reader currentReader;

        // Data Cache
        private List<Dictionary<string, object>> productList = new List<Dictionary<string, object>>();
        private List<Dictionary<string, object>> inmateList = new List<Dictionary<string, object>>();
        private List<CartItem> cart = new List<CartItem>();

        // UI Controls - Kasir
        private DataGridView gridProducts;
        private Label lblCartCountBadge;
        private DataGridView gridCart;
        private Label lblTotal;
        private Label lblScannerStatus;
        private Label lblApiStatus;
        private TextBox txtBarcode;
        private TextBox txtSearchProduct;
        private ComboBox cmbCategory;
        private Button btnCheckoutManual;
        private Button btnCheckoutFingerprint;

        // UI Controls - Enrollment (Batch & Searchable)
        private ComboBox cmbInmateEnroll;
        private TextBox txtEnrollSearch;
        private CheckBox chkEnrollOnlyUnenrolled;
        private ListBox lstEnrollInmates;
        private Label lblEnrollCount;
        private Label lblEnrollSelectedName;
        private Label lblEnrollSelectedReg;
        private Label lblEnrollSelectedFpStatus;
        private Dictionary<string, object> selectedEnrollInmate = null;
        private Label lblEnrollStep;
        private PictureBox picEnrollPreview;
        private Button btnStartEnroll;
        private List<Fmd> enrollFmdList = new List<Fmd>();

        // UI Controls - Cek Saldo Kiosk (Barcode, No. Reg & Biometrik)
        private TextBox txtKioskSearch;
        private Button btnKioskSearch;
        private Button btnKioskReset;
        private Label lblKioskNama;
        private Label lblKioskNoReg;
        private Label lblKioskKamar;
        private Label lblKioskVa;
        private Label lblKioskStatusBadge;
        private Label lblKioskSanctionWarning;
        private Label lblKioskSaldo;
        private Label lblKioskLimit;
        private PictureBox picKioskFinger;
        private Button btnStartKioskScan;

        // UI Controls - Navigation Header
        private Button btnTopKasir;
        private Button btnTopEnroll;
        private Button btnTopKiosk;
        private Button btnTopTopup;
        private Button btnTopDualScreen;
        private Button btnTopSettings;
        private Timer timerScannerPoll;
        private CustomerDisplayForm customerDisplay;

        // UI Settings & Splitter Persistence
        private TabControl tabs;
        private double savedSplitterRatio = 0.75; // Default 75% left (katalog produk), 25% right (keranjang belanja)
        private SplitContainer splitKasir;
        private bool isApplyingSplitter = false;

        // UI Controls - Settings Tab
        private TextBox txtServerAddress;
        private Label lblCurrentServerInfo;
        private Label lblSplitterInfo;

        // Retail POS State (Level 3 - Fast Keyboard Shortcuts & Shift/Hold)
        private List<HeldBill> heldBills = new List<HeldBill>();
        private Label lblHoldStatus;
        private DateTime shiftStartTime = DateTime.Now;
        private string cashierName = "Kasir Koperasi Lapas";
        private decimal shiftModalAwal = 200000;
        private int shiftTrxCount = 0;
        private decimal shiftTotalOmset = 0;
        private ReceiptConfig receiptConfig = new ReceiptConfig();
        public bool WantsLogoutAndRelogin { get; private set; }

        public MainForm()
        {
            this.Text = "UangPas Lapas - Terminal Kasir POS & Biometrik";
            this.Size = new Size(1280, 800);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.WindowState = FormWindowState.Maximized;
            this.MinimumSize = new Size(960, 580);
            this.AutoScaleMode = AutoScaleMode.Dpi;
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            this.BackColor = Color.FromArgb(248, 250, 252);
            this.KeyPreview = true;
            this.KeyDown += MainForm_KeyDown;

            LoadLocalSettings();
            InitializeUI();

            this.Shown += (s, e) => {
                this.BeginInvoke(new Action(() => {
                    ApplySplitterRatio();
                }));
                InitializeBiometrics();
                SyncDataFromApi();
            };
        }

        private void InitializeUI()
        {
            // Top Status Bar (Responsive Docking)
            Panel topBar = new Panel { 
                Dock = DockStyle.Top, 
                Height = 64, 
                BackColor = Color.FromArgb(15, 23, 42), 
                Padding = new Padding(12, 0, 12, 0) 
            };
            
            // Right Status Indicators & Authenticated User Profile (Compact to avoid covering buttons)
            Panel topStatusRight = new Panel {
                Dock = DockStyle.Right,
                Width = 330,
                BackColor = Color.Transparent
            };

            lblScannerStatus = new Label {
                Text = "● Scanner: Memeriksa...",
                ForeColor = Color.FromArgb(148, 163, 184),
                BackColor = Color.FromArgb(30, 41, 59),
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                AutoSize = false,
                Size = new Size(130, 20),
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(4, 0, 0, 0),
                Location = new Point(4, 10),
                Cursor = Cursors.Hand
            };
            lblScannerStatus.Click += (s, e) => {
                lblScannerStatus.Text = "● Scanner: Memeriksa...";
                lblScannerStatus.ForeColor = Color.FromArgb(251, 191, 36);
                InitializeBiometrics();
            };
            topStatusRight.Controls.Add(lblScannerStatus);

            lblApiStatus = new Label {
                Text = "● Server: Online",
                ForeColor = Color.FromArgb(52, 211, 153),
                BackColor = Color.FromArgb(30, 41, 59),
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                AutoSize = false,
                Size = new Size(130, 20),
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(4, 0, 0, 0),
                Location = new Point(4, 33)
            };
            topStatusRight.Controls.Add(lblApiStatus);

            // User Profile Card & Ganti Shift / Logout Button
            Panel pnlUserCard = new Panel {
                Location = new Point(136, 8),
                Size = new Size(190, 48),
                BackColor = Color.FromArgb(30, 41, 59)
            };

            string initialLetter = (AuthSession.CurrentUser != null && !string.IsNullOrEmpty(AuthSession.CurrentUser.Name)) ? AuthSession.CurrentUser.Name.Substring(0, 1).ToUpper() : "P";
            Label lblUserInitial = new Label {
                Location = new Point(4, 8),
                Size = new Size(32, 32),
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                TextAlign = ContentAlignment.MiddleCenter,
                Text = initialLetter
            };
            pnlUserCard.Controls.Add(lblUserInitial);

            string userNameStr = (AuthSession.CurrentUser != null) ? AuthSession.CurrentUser.Name : "Petugas Kasir";
            Label lblUserName = new Label {
                Location = new Point(38, 6),
                Size = new Size(92, 17),
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 8f, FontStyle.Bold),
                AutoEllipsis = true,
                Text = userNameStr
            };
            pnlUserCard.Controls.Add(lblUserName);

            string roleLabelStr = (AuthSession.CurrentUser != null) ? AuthSession.CurrentUser.RoleLabel : "Kasir POS";
            Label lblUserRole = new Label {
                Location = new Point(38, 24),
                Size = new Size(92, 15),
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 7f, FontStyle.Regular),
                AutoEllipsis = true,
                Text = roleLabelStr
            };
            pnlUserCard.Controls.Add(lblUserRole);

            Button btnGantiShift = new Button {
                Location = new Point(132, 8),
                Size = new Size(54, 32),
                BackColor = Color.FromArgb(239, 68, 68),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                Text = "Shift 🚪",
                Cursor = Cursors.Hand
            };
            btnGantiShift.FlatAppearance.BorderSize = 0;
            btnGantiShift.Click += (s, e) => {
                DialogResult dr = MessageBox.Show(this, 
                    "Apakah Anda yakin ingin mengakhiri shift dan mengganti akun petugas?", 
                    "Ganti Shift / Logout", 
                    MessageBoxButtons.YesNo, 
                    MessageBoxIcon.Question);
                if (dr == DialogResult.Yes)
                {
                    this.WantsLogoutAndRelogin = true;
                    AuthSession.Clear();
                    this.Close();
                }
            };
            pnlUserCard.Controls.Add(btnGantiShift);

            topStatusRight.Controls.Add(pnlUserCard);
            topBar.Controls.Add(topStatusRight);

            // Left Flow for Brand + Navigation
            FlowLayoutPanel topFlowLeft = new FlowLayoutPanel {
                Dock = DockStyle.Fill,
                FlowDirection = FlowDirection.LeftToRight,
                WrapContents = false,
                BackColor = Color.Transparent,
                Padding = new Padding(2, 12, 2, 8),
                AutoScroll = false
            };

            Panel pnlBrand = new Panel {
                Size = new Size(135, 48),
                Margin = new Padding(2, 2, 2, 0),
                BackColor = Color.Transparent
            };

            PictureBox picBrandLogo = new PictureBox {
                Size = new Size(26, 26),
                Location = new Point(0, 9),
                SizeMode = PictureBoxSizeMode.Zoom,
                BackColor = Color.Transparent
            };
            string logoPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (System.IO.File.Exists(logoPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(logoPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    {
                        Bitmap bmp = new Bitmap(ms);
                        picBrandLogo.Image = (Image)bmp.Clone();
                        this.Icon = Icon.FromHandle(bmp.GetHicon());
                    }
                } catch { }
            }
            pnlBrand.Controls.Add(picBrandLogo);

            Label lblBrand = new Label {
                Text = "UangPas Lapas",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(30, 6)
            };
            Label lblSubBrand = new Label {
                Text = "POS & BIOMETRIK",
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 6.5f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(32, 25)
            };
            pnlBrand.Controls.Add(lblBrand);
            pnlBrand.Controls.Add(lblSubBrand);
            topFlowLeft.Controls.Add(pnlBrand);

            // 1. KASIR POS BUTTON
            btnTopKasir = new Button {
                Text = "🛒 Kasir POS",
                Width = 90,
                Height = 36,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            btnTopKasir.FlatAppearance.BorderSize = 0;
            btnTopKasir.Click += (s, e) => SwitchTab(0);
            topFlowLeft.Controls.Add(btnTopKasir);

            // 2. LAYAR PELANGGAN (DUAL SCREEN) BUTTON
            btnTopDualScreen = new Button {
                Text = "🖥️ Layar 2",
                Width = 105,
                Height = 36,
                BackColor = Color.FromArgb(14, 165, 233),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            btnTopDualScreen.FlatAppearance.BorderSize = 0;
            btnTopDualScreen.Click += (s, e) => ToggleCustomerDisplay();
            topFlowLeft.Controls.Add(btnTopDualScreen);

            // 3. TOP-UP SALDO BUTTON
            string walletPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "wallet_logo.png");
            btnTopTopup = new Button {
                Text = "  Top-Up",
                Width = 80,
                Height = 36,
                BackColor = Color.FromArgb(6, 78, 59),
                ForeColor = Color.FromArgb(167, 243, 208),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            if (System.IO.File.Exists(walletPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(walletPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopTopup.Image = new Bitmap(orig, new Size(16, 16));
                        btnTopTopup.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopTopup.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopTopup.FlatAppearance.BorderSize = 0;
            btnTopTopup.Click += (s, e) => OpenTopupDialog();
            topFlowLeft.Controls.Add(btnTopTopup);

            // 4. CEK SALDO MANDIRI (KIOSK)
            btnTopKiosk = new Button {
                Text = "  Cek Saldo",
                Width = 95,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            if (System.IO.File.Exists(walletPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(walletPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopKiosk.Image = new Bitmap(orig, new Size(16, 16));
                        btnTopKiosk.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopKiosk.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopKiosk.FlatAppearance.BorderSize = 0;
            btnTopKiosk.Click += (s, e) => SwitchTab(2);
            topFlowLeft.Controls.Add(btnTopKiosk);

            // 5. REKAM SIDIK JARI
            btnTopEnroll = new Button {
                Text = "  Rekam Jari",
                Width = 98,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            if (System.IO.File.Exists(logoPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(logoPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopEnroll.Image = new Bitmap(orig, new Size(16, 16));
                        btnTopEnroll.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopEnroll.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopEnroll.FlatAppearance.BorderSize = 0;
            btnTopEnroll.Click += (s, e) => SwitchTab(1);
            topFlowLeft.Controls.Add(btnTopEnroll);

            // 6. PENGATURAN SENSOR
            btnTopSettings = new Button {
                Text = "  Pengaturan",
                Width = 98,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            string gearPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "gear_logo.png");
            if (System.IO.File.Exists(gearPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(gearPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopSettings.Image = new Bitmap(orig, new Size(16, 16));
                        btnTopSettings.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopSettings.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopSettings.FlatAppearance.BorderSize = 0;
            btnTopSettings.Click += (s, e) => SwitchTab(3);
            topFlowLeft.Controls.Add(btnTopSettings);

            // 7. SINKRONISASI
            Button btnTopSync = new Button {
                Text = "🔄",
                Width = 38,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            btnTopSync.Click += (s, e) => {
                btnTopSync.Text = "⏳";
                btnTopSync.Enabled = false;
                SyncDataFromApi();
                Timer t = new Timer { Interval = 800 };
                t.Tick += (ts, te) => {
                    btnTopSync.Text = "🔄";
                    btnTopSync.Enabled = true;
                    t.Stop();
                    t.Dispose();
                };
                t.Start();
            };
            topFlowLeft.Controls.Add(btnTopSync);

            // 8. PORTAL ADMIN WEB
            Button btnOpenPortalAdmin = new Button {
                Text = "🌐 Admin",
                Width = 78,
                Height = 36,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            btnOpenPortalAdmin.Click += (s, e) => {
                try {
                    System.Diagnostics.Process.Start("http://uangpas.test");
                } catch {
                    try { System.Diagnostics.Process.Start("http://localhost:8000"); } catch { }
                }
            };
            topFlowLeft.Controls.Add(btnOpenPortalAdmin);

            topBar.Controls.Add(topFlowLeft);
            topFlowLeft.BringToFront();

            // Tab Control (Flat mode without redundant tabs)
            tabs = new TabControl { 
                Dock = DockStyle.Fill, 
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Appearance = TabAppearance.FlatButtons,
                ItemSize = new Size(0, 1),
                SizeMode = TabSizeMode.Fixed
            };

            // TAB 1: KASIR KOPERASI
            TabPage tabKasir = new TabPage("🛒 Kasir Belanja Koperasi");
            tabKasir.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildKasirTab(tabKasir);
            tabs.TabPages.Add(tabKasir);

            // TAB 2: PENDAFTARAN SIDIK JARI (ENROLLMENT)
            TabPage tabEnroll = new TabPage("Rekam Sidik Jari WBP");
            tabEnroll.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildEnrollmentTab(tabEnroll);
            tabs.TabPages.Add(tabEnroll);

            // TAB 3: CEK SALDO MANDIRI (KIOSK)
            TabPage tabKiosk = new TabPage("Cek Saldo Mandiri WBP");
            tabKiosk.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildKioskTab(tabKiosk);
            tabs.TabPages.Add(tabKiosk);

            // TAB 4: PENGATURAN & SENSOR
            TabPage tabSettings = new TabPage("Pengaturan & Sensor");
            tabSettings.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildSettingsTab(tabSettings);
            tabs.TabPages.Add(tabSettings);

            tabs.SelectedIndexChanged += (s, e) => {
                UpdateNavButtonStyles(tabs.SelectedIndex);
                if (tabs.SelectedIndex != 3) StopDiagCapture();
            };

            // IMPORTANT WINFORMS DOCK ORDER: Add tabs first, topBar second, then topBar.SendToBack()
            // This ensures topBar reserves the top 64px and tabs strictly fills the space beneath it without overlapping!
            this.Controls.Add(tabs);
            this.Controls.Add(topBar);
            topBar.SendToBack();
            tabs.BringToFront();

            UpdateNavButtonStyles(0);
            ApplyUserPermissions();

            // Scanner Auto-Poller: Keep checking in background so status automatically turns green
            timerScannerPoll = new Timer { Interval = 2500 };
            timerScannerPoll.Tick += (s, e) => {
                try
                {
                    if (currentReader == null || !lblScannerStatus.Text.Contains("Siap"))
                    {
                        ReaderCollection readers = ReaderCollection.GetReaders();
                        if (readers != null && readers.Count > 0)
                        {
                            currentReader = readers[0];
                            string prodName = currentReader.Description.Id.ProductName;
                            if (string.IsNullOrEmpty(prodName)) prodName = "U.are.U 4500";
                            lblScannerStatus.Text = "Scanner: " + prodName + " (Siap)";
                            lblScannerStatus.ForeColor = Color.FromArgb(52, 211, 153);
                        }
                    }
                }
                catch { }
            };
            timerScannerPoll.Start();
        }

        private void ApplyUserPermissions()
        {
            if (AuthSession.CurrentUser == null)
            {
                AuthSession.CurrentUser = new AuthUser();
            }

            AuthUser user = AuthSession.CurrentUser;
            this.cashierName = user.Name;

            // Restrict buttons based on role permissions
            if (btnTopKasir != null)
            {
                btnTopKasir.Visible = user.CanCheckout;
            }
            if (btnTopDualScreen != null)
            {
                btnTopDualScreen.Visible = user.CanCheckout;
            }
            if (btnTopTopup != null)
            {
                btnTopTopup.Visible = user.CanTopup;
            }
            if (btnTopEnroll != null)
            {
                btnTopEnroll.Visible = user.CanEnroll;
            }
            if (btnTopSettings != null)
            {
                btnTopSettings.Visible = user.CanSettings;
            }

            // Auto select suitable tab if Kasir POS is not allowed for this role
            if (!user.CanCheckout)
            {
                if (user.CanEnroll) SwitchTab(1);
                else if (user.CanTopup) SwitchTab(2);
                else if (user.CanSettings) SwitchTab(3);
                else SwitchTab(2);
            }
        }

        private void SwitchTab(int index)
        {
            if (AuthSession.CurrentUser != null)
            {
                if (index == 0 && !AuthSession.CurrentUser.CanCheckout)
                {
                    MessageBox.Show(this, "Peran Anda (" + AuthSession.CurrentUser.RoleLabel + ") tidak memiliki hak akses transaksi Kasir POS.", "Akses Terbatas", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
                if (index == 1 && !AuthSession.CurrentUser.CanEnroll)
                {
                    MessageBox.Show(this, "Peran Anda (" + AuthSession.CurrentUser.RoleLabel + ") tidak memiliki hak akses Perekaman Biometrik.", "Akses Terbatas", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
                if (index == 3 && !AuthSession.CurrentUser.CanSettings)
                {
                    MessageBox.Show(this, "Peran Anda (" + AuthSession.CurrentUser.RoleLabel + ") tidak memiliki hak akses Pengaturan Sistem.", "Akses Terbatas", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
            }

            if (tabs != null && tabs.TabPages.Count > index)
            {
                tabs.SelectedIndex = index;
                UpdateNavButtonStyles(index);
                if (index == 3 && txtServerAddress != null)
                {
                    txtServerAddress.Text = GetDisplayServerAddress(this.apiUrl);
                }
            }
        }

        private void UpdateNavButtonStyles(int selectedIndex)
        {
            Color dark = Color.FromArgb(30, 41, 59);
            Color textMuted = Color.FromArgb(203, 213, 225);

            if (btnTopKasir != null)
            {
                btnTopKasir.BackColor = (selectedIndex == 0) ? Color.FromArgb(16, 185, 129) : dark;
                btnTopKasir.ForeColor = (selectedIndex == 0) ? Color.White : textMuted;
            }
            if (btnTopEnroll != null)
            {
                btnTopEnroll.BackColor = (selectedIndex == 1) ? Color.FromArgb(99, 102, 241) : dark;
                btnTopEnroll.ForeColor = (selectedIndex == 1) ? Color.White : textMuted;
            }
            if (btnTopKiosk != null)
            {
                btnTopKiosk.BackColor = (selectedIndex == 2) ? Color.FromArgb(14, 165, 233) : dark;
                btnTopKiosk.ForeColor = (selectedIndex == 2) ? Color.White : textMuted;
            }
            if (btnTopSettings != null)
            {
                btnTopSettings.BackColor = (selectedIndex == 3) ? Color.FromArgb(245, 158, 11) : dark;
                btnTopSettings.ForeColor = (selectedIndex == 3) ? Color.White : textMuted;
            }
        }

        public void ToggleCustomerDisplay()
        {
            if (customerDisplay == null || customerDisplay.IsDisposed)
            {
                customerDisplay = new CustomerDisplayForm();
                customerDisplay.FormClosed += (s, e) => { customerDisplay = null; };
                customerDisplay.Show();
                if (cart != null && cart.Count > 0)
                {
                    decimal tot = 0;
                    foreach (var it in cart) tot += (it.Harga * it.Qty);
                    customerDisplay.UpdateCart(cart, tot);
                }
            }
            else
            {
                if (customerDisplay.WindowState == FormWindowState.Minimized)
                    customerDisplay.WindowState = FormWindowState.Normal;
                customerDisplay.BringToFront();
                customerDisplay.Focus();
            }
        }

        public void OpenTopupDialog()
        {
            if (AuthSession.CurrentUser != null && !AuthSession.CurrentUser.CanTopup)
            {
                MessageBox.Show(this, "Peran Anda (" + AuthSession.CurrentUser.RoleLabel + ") tidak memiliki hak akses Top-Up Saldo.", "Akses Terbatas", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            using (TopupSaldoForm topup = new TopupSaldoForm(this.currentReader, this.inmateList, this.apiUrl, (id, newSaldo) => {
                var inmate = inmateList.Find(x => x.ContainsKey("id") && Convert.ToInt32(x["id"]) == id);
                if (inmate != null)
                {
                    inmate["saldo"] = newSaldo;
                }
            }))
            {
                topup.ShowDialog(this);
            }
        }
    }
}


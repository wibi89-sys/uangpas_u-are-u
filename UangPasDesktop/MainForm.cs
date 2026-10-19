using System;
using System.Collections.Generic;
using System.Drawing;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using DPUruNet;

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
        private Button btnTopSettings;
        private Timer timerScannerPoll;

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
            
            // Right Status Indicators
            Panel topStatusRight = new Panel {
                Dock = DockStyle.Right,
                Width = 270,
                BackColor = Color.Transparent
            };

            lblScannerStatus = new Label {
                Text = "● Scanner: Memeriksa...",
                ForeColor = Color.FromArgb(148, 163, 184),
                BackColor = Color.FromArgb(30, 41, 59),
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                AutoSize = false,
                Size = new Size(250, 22),
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(6, 0, 0, 0),
                Location = new Point(10, 10),
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
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                AutoSize = false,
                Size = new Size(250, 22),
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(6, 0, 0, 0),
                Location = new Point(10, 34)
            };
            topStatusRight.Controls.Add(lblApiStatus);
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
                Size = new Size(240, 48),
                Margin = new Padding(4, 2, 14, 0),
                BackColor = Color.Transparent
            };

            PictureBox picBrandLogo = new PictureBox {
                Size = new Size(38, 38),
                Location = new Point(0, 4),
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
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(44, 4)
            };
            Label lblSubBrand = new Label {
                Text = "POS KOPERASI & BIOMETRIK",
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(46, 26)
            };
            pnlBrand.Controls.Add(lblBrand);
            pnlBrand.Controls.Add(lblSubBrand);
            topFlowLeft.Controls.Add(pnlBrand);

            // Fast Switch Buttons in Header
            btnTopKasir = new Button {
                Text = "🛒 Kasir POS",
                Width = 115,
                Height = 36,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 4, 0)
            };
            btnTopKasir.FlatAppearance.BorderSize = 0;
            btnTopKasir.Click += (s, e) => SwitchTab(0);
            topFlowLeft.Controls.Add(btnTopKasir);

            btnTopEnroll = new Button {
                Text = "  Rekam Sidik Jari",
                Width = 155,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 4, 0)
            };
            if (System.IO.File.Exists(logoPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(logoPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopEnroll.Image = new Bitmap(orig, new Size(20, 20));
                        btnTopEnroll.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopEnroll.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopEnroll.FlatAppearance.BorderSize = 0;
            btnTopEnroll.Click += (s, e) => SwitchTab(1);
            topFlowLeft.Controls.Add(btnTopEnroll);

            btnTopKiosk = new Button {
                Text = "🔍 Cek Saldo",
                Width = 115,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 4, 0)
            };
            btnTopKiosk.FlatAppearance.BorderSize = 0;
            btnTopKiosk.Click += (s, e) => SwitchTab(2);
            topFlowLeft.Controls.Add(btnTopKiosk);

            btnTopSettings = new Button {
                Text = "  Pengaturan Sensor",
                Width = 175,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 6, 0)
            };
            string gearPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "gear_logo.png");
            if (System.IO.File.Exists(gearPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(gearPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnTopSettings.Image = new Bitmap(orig, new Size(20, 20));
                        btnTopSettings.ImageAlign = ContentAlignment.MiddleLeft;
                        btnTopSettings.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnTopSettings.FlatAppearance.BorderSize = 0;
            btnTopSettings.Click += (s, e) => SwitchTab(3);
            topFlowLeft.Controls.Add(btnTopSettings);

            Button btnTopSync = new Button {
                Text = "🔄 Sinkron",
                Width = 100,
                Height = 34,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 3, 0)
            };
            btnTopSync.Click += (s, e) => {
                btnTopSync.Text = "⏳ Sinkron...";
                btnTopSync.Enabled = false;
                SyncDataFromApi();
                Timer t = new Timer { Interval = 800 };
                t.Tick += (ts, te) => {
                    btnTopSync.Text = "🔄 Sinkron";
                    btnTopSync.Enabled = true;
                    t.Stop();
                    t.Dispose();
                };
                t.Start();
            };
            topFlowLeft.Controls.Add(btnTopSync);

            Button btnOpenPortalAdmin = new Button {
                Text = "🌐 Portal Admin",
                Width = 125,
                Height = 34,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
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
            TabPage tabKiosk = new TabPage("🔍 Cek Saldo Mandiri WBP");
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

        private void SwitchTab(int index)
        {
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
    }
}

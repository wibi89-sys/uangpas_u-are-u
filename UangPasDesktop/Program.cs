using System;
using System.IO;
using System.Net;
using System.Net.Sockets;
using System.Net.NetworkInformation;
using System.Threading;
using System.Threading.Tasks;
using System.Diagnostics;
using System.Text;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
using Timer = System.Windows.Forms.Timer;
using System.Collections.Generic;
using System.Web.Script.Serialization;
using DPUruNet;

namespace UangPasDesktop
{

    static class Program
    {
        [MTAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }

    public class MainForm : Form
    {
        // Settings & API
        private string apiUrl = "http://127.0.0.1:8000/api/v1";
        private JavaScriptSerializer serializer = new JavaScriptSerializer();

        // Biometric Reader
        private Reader currentReader;
        private bool isCapturing = false;

        // Data Cache
        private List<Dictionary<string, object>> productList = new List<Dictionary<string, object>>();
        private List<Dictionary<string, object>> inmateList = new List<Dictionary<string, object>>();
        private List<CartItem> cart = new List<CartItem>();

        // UI Controls - Kasir
        private DataGridView gridProducts;
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
        private int currentEnrollStep = 0;

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
                Width = 240,
                BackColor = Color.Transparent
            };

            lblScannerStatus = new Label {
                Text = "Scanner: Memeriksa...",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(12, 14),
                Cursor = Cursors.Hand
            };
            lblScannerStatus.Click += (s, e) => {
                lblScannerStatus.Text = "Scanner: Memeriksa...";
                lblScannerStatus.ForeColor = Color.FromArgb(251, 191, 36);
                InitializeBiometrics();
            };
            topStatusRight.Controls.Add(lblScannerStatus);

            lblApiStatus = new Label {
                Text = "Server: Online",
                ForeColor = Color.FromArgb(52, 211, 153),
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(12, 36)
            };
            topStatusRight.Controls.Add(lblApiStatus);
            topBar.Controls.Add(topStatusRight);

            // Left Flow for Brand + Navigation
            FlowLayoutPanel topFlowLeft = new FlowLayoutPanel {
                Dock = DockStyle.Fill,
                FlowDirection = FlowDirection.LeftToRight,
                WrapContents = false,
                BackColor = Color.Transparent,
                Padding = new Padding(2, 14, 2, 8),
                AutoScroll = false
            };

            Label lblBrand = new Label {
                Text = "👆 UangPas Lapas",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                AutoSize = true,
                Margin = new Padding(4, 5, 12, 0)
            };
            topFlowLeft.Controls.Add(lblBrand);

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
                Text = "👆 Rekam Jari",
                Width = 125,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 4, 0)
            };
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
                Text = "⚙️ Pengaturan & Sensor",
                Width = 175,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.FromArgb(203, 213, 225),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(2, 0, 6, 0)
            };
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
            TabPage tabEnroll = new TabPage("👆 Rekam Sidik Jari WBP");
            tabEnroll.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildEnrollmentTab(tabEnroll);
            tabs.TabPages.Add(tabEnroll);

            // TAB 3: CEK SALDO MANDIRI (KIOSK)
            TabPage tabKiosk = new TabPage("🔍 Cek Saldo Mandiri WBP");
            tabKiosk.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildKioskTab(tabKiosk);
            tabs.TabPages.Add(tabKiosk);

            // TAB 4: PENGATURAN & SENSOR
            TabPage tabSettings = new TabPage("⚙️ Pengaturan & Sensor");
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

        #region TAB 1: KASIR POS
        private void BuildKasirTab(TabPage page)
        {
            splitKasir = new SplitContainer {
                Dock = DockStyle.Fill,
                Orientation = Orientation.Vertical,
                IsSplitterFixed = false,
                SplitterWidth = 6
            };

            // Capture when user manually drags splitter to persist their custom layout preference
            splitKasir.SplitterMoved += (s, e) => {
                if (splitKasir.Width > 0 && !isApplyingSplitter)
                {
                    double ratio = (double)splitKasir.SplitterDistance / splitKasir.Width;
                    if (ratio >= 0.40 && ratio <= 0.85)
                    {
                        this.savedSplitterRatio = ratio;
                        SaveLocalSettings();
                        if (lblSplitterInfo != null)
                        {
                            lblSplitterInfo.Text = "Porsi Tampilan Saat Ini: " + (int)((1 - ratio) * 100) + "% Keranjang Belanja, " + (int)(ratio * 100) + "% Katalog Produk.\n(Pemisah juga bisa digeser langsung dengan mouse di Tab Kasir POS).";
                        }
                    }
                }
            };

            // Handle proportional splitter on resize
            this.Resize += (s, e) => {
                ApplySplitterRatio();
            };
            splitKasir.SizeChanged += (s, e) => {
                ApplySplitterRatio();
            };

            // LEFT PANEL: Product Grid & Search
            Panel leftPanel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(16, 20, 16, 16) };
            
            TableLayoutPanel searchTable = new TableLayoutPanel {
                Dock = DockStyle.Top,
                Height = 44,
                RowCount = 1,
                ColumnCount = 4,
                Padding = new Padding(0, 0, 0, 6)
            };
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 140f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 150f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 130f));

            Label lblSearch = new Label { 
                Text = "Cari / Scan Barcode:", 
                Dock = DockStyle.Fill, 
                TextAlign = ContentAlignment.MiddleLeft, 
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) 
            };
            txtSearchProduct = new TextBox { 
                Dock = DockStyle.Fill, 
                Font = new Font("Segoe UI", 10.5f) 
            };
            txtSearchProduct.TextChanged += (s, e) => FilterProducts();
            txtSearchProduct.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    string code = txtSearchProduct.Text.Trim();
                    if (!string.IsNullOrEmpty(code))
                    {
                        var found = productList.Find(x => (x.ContainsKey("barcode") && x["barcode"] != null && x["barcode"].ToString().Equals(code, StringComparison.OrdinalIgnoreCase)));
                        if (found != null)
                        {
                            AddProductToCart(found);
                            txtSearchProduct.Clear();
                        }
                    }
                }
            };

            cmbCategory = new ComboBox {
                Dock = DockStyle.Fill,
                DropDownStyle = ComboBoxStyle.DropDownList,
                Font = new Font("Segoe UI", 9.5f),
                BackColor = Color.White
            };
            cmbCategory.Items.Add("Semua Kategori");
            cmbCategory.SelectedIndex = 0;
            cmbCategory.SelectedIndexChanged += (s, e) => RenderProductGrid();

            Button btnRefresh = new Button {
                Text = "🔄 Sinkron Data",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnRefresh.Click += (s, e) => { SyncDataFromApi(); };

            searchTable.Controls.Add(lblSearch, 0, 0);
            searchTable.Controls.Add(txtSearchProduct, 1, 0);
            searchTable.Controls.Add(cmbCategory, 2, 0);
            searchTable.Controls.Add(btnRefresh, 3, 0);

            // Excel-style Products Grid with Modern Flat Table Styling
            gridProducts = new DataGridView {
                Dock = DockStyle.Fill,
                BackgroundColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                CellBorderStyle = DataGridViewCellBorderStyle.SingleHorizontal,
                GridColor = Color.FromArgb(241, 245, 249),
                RowHeadersVisible = false,
                AllowUserToAddRows = false,
                AllowUserToDeleteRows = false,
                ReadOnly = true,
                SelectionMode = DataGridViewSelectionMode.FullRowSelect,
                MultiSelect = false,
                AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill,
                EnableHeadersVisualStyles = false,
                ColumnHeadersBorderStyle = DataGridViewHeaderBorderStyle.Single
            };

            gridProducts.ColumnHeadersHeightSizeMode = DataGridViewColumnHeadersHeightSizeMode.DisableResizing;
            gridProducts.ColumnHeadersHeight = 38;
            gridProducts.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            gridProducts.ColumnHeadersDefaultCellStyle.ForeColor = Color.FromArgb(30, 41, 59);
            gridProducts.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 10f, FontStyle.Bold);
            gridProducts.ColumnHeadersDefaultCellStyle.Padding = new Padding(4, 0, 4, 0);

            // Alternating Row Colors (Zebra Striping)
            gridProducts.RowsDefaultCellStyle.BackColor = Color.White;
            gridProducts.RowsDefaultCellStyle.Font = new Font("Segoe UI", 9.5f);
            gridProducts.RowsDefaultCellStyle.ForeColor = Color.FromArgb(15, 23, 42);
            gridProducts.RowsDefaultCellStyle.SelectionBackColor = Color.FromArgb(224, 242, 254);
            gridProducts.RowsDefaultCellStyle.SelectionForeColor = Color.FromArgb(3, 105, 161);

            gridProducts.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(248, 250, 252);
            gridProducts.AlternatingRowsDefaultCellStyle.SelectionBackColor = Color.FromArgb(224, 242, 254);
            gridProducts.AlternatingRowsDefaultCellStyle.SelectionForeColor = Color.FromArgb(3, 105, 161);
            gridProducts.RowTemplate.Height = 38;

            gridProducts.Columns.Add("barcode", "Barcode / Kode");
            gridProducts.Columns["barcode"].MinimumWidth = 130;
            gridProducts.Columns["barcode"].FillWeight = 115;
            gridProducts.Columns["barcode"].DefaultCellStyle.Font = new Font("Consolas", 9.5f, FontStyle.Bold);

            gridProducts.Columns.Add("nama", "Nama Barang");
            gridProducts.Columns["nama"].MinimumWidth = 240;
            gridProducts.Columns["nama"].FillWeight = 280;

            gridProducts.Columns.Add("kategori", "Kategori");
            gridProducts.Columns["kategori"].MinimumWidth = 130;
            gridProducts.Columns["kategori"].FillWeight = 125;

            gridProducts.Columns.Add("harga", "Harga Satuan");
            gridProducts.Columns["harga"].MinimumWidth = 115;
            gridProducts.Columns["harga"].FillWeight = 110;
            gridProducts.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridProducts.Columns["harga"].DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            gridProducts.Columns["harga"].DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);

            gridProducts.Columns.Add("stok", "Sisa Stok");
            gridProducts.Columns["stok"].MinimumWidth = 85;
            gridProducts.Columns["stok"].FillWeight = 75;
            gridProducts.Columns["stok"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;

            DataGridViewButtonColumn colBtn = new DataGridViewButtonColumn {
                Name = "aksi",
                HeaderText = "Aksi",
                Text = "➕ Tambah",
                UseColumnTextForButtonValue = true,
                MinimumWidth = 90,
                FillWeight = 85,
                FlatStyle = FlatStyle.Flat
            };
            colBtn.DefaultCellStyle.BackColor = Color.FromArgb(16, 185, 129);
            colBtn.DefaultCellStyle.ForeColor = Color.White;
            colBtn.DefaultCellStyle.Font = new Font("Segoe UI", 8.5f, FontStyle.Bold);
            gridProducts.Columns.Add(colBtn);

            gridProducts.CellContentClick += (s, e) => {
                if (e.RowIndex >= 0 && e.ColumnIndex == gridProducts.Columns["aksi"].Index)
                {
                    AddSelectedGridProductToCart(e.RowIndex);
                }
            };

            gridProducts.CellDoubleClick += (s, e) => {
                if (e.RowIndex >= 0)
                {
                    AddSelectedGridProductToCart(e.RowIndex);
                }
            };

            gridProducts.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter && gridProducts.CurrentRow != null && gridProducts.CurrentRow.Index >= 0)
                {
                    AddSelectedGridProductToCart(gridProducts.CurrentRow.Index);
                    e.SuppressKeyPress = true;
                }
            };

            // Docking order: add gridProducts then searchTable, then send searchTable back so gridProducts docks below it
            leftPanel.Controls.Add(gridProducts);
            leftPanel.Controls.Add(searchTable);
            searchTable.SendToBack();
            gridProducts.BringToFront();

            splitKasir.Panel1.Controls.Add(leftPanel);

            // RIGHT PANEL: Cart & Fingerprint Checkout (25% screen width)
            Panel rightPanel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(10, 14, 10, 12), BackColor = Color.White };
            
            lblHoldStatus = new Label {
                Text = "",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(180, 83, 9),
                BackColor = Color.FromArgb(254, 243, 199),
                Dock = DockStyle.Top,
                Height = 28,
                TextAlign = ContentAlignment.MiddleCenter,
                Visible = false,
                Cursor = Cursors.Hand
            };
            lblHoldStatus.Click += (s, e) => RecallBill();
            rightPanel.Controls.Add(lblHoldStatus);

            Label lblCartTitle = new Label {
                Text = "📋 Keranjang Belanja Koperasi",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                Dock = DockStyle.Top,
                Height = 32
            };
            rightPanel.Controls.Add(lblCartTitle);

            // Barcode input box (Responsive Table)
            TableLayoutPanel barcodeTable = new TableLayoutPanel {
                Dock = DockStyle.Top,
                Height = 38,
                RowCount = 1,
                ColumnCount = 2,
                Padding = new Padding(0, 0, 0, 4)
            };
            barcodeTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 100f));
            barcodeTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));

            Label lblBc = new Label { 
                Text = "Scan Barcode:", 
                Dock = DockStyle.Fill, 
                TextAlign = ContentAlignment.MiddleLeft, 
                Font = new Font("Segoe UI", 9f, FontStyle.Bold) 
            };
            txtBarcode = new TextBox { 
                Dock = DockStyle.Fill, 
                Font = new Font("Segoe UI", 10f) 
            };
            txtBarcode.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    AddProductByBarcode(txtBarcode.Text.Trim());
                    txtBarcode.Clear();
                    e.SuppressKeyPress = true;
                }
            };
            barcodeTable.Controls.Add(lblBc, 0, 0);
            barcodeTable.Controls.Add(txtBarcode, 1, 0);
            rightPanel.Controls.Add(barcodeTable);

            // Grid Cart (Modern Flat Table Styling)
            gridCart = new DataGridView {
                Dock = DockStyle.Fill,
                BackgroundColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                CellBorderStyle = DataGridViewCellBorderStyle.SingleHorizontal,
                GridColor = Color.FromArgb(241, 245, 249),
                RowHeadersVisible = false,
                AllowUserToAddRows = false,
                SelectionMode = DataGridViewSelectionMode.FullRowSelect,
                AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill,
                EnableHeadersVisualStyles = false,
                ColumnHeadersBorderStyle = DataGridViewHeaderBorderStyle.Single
            };
            gridCart.ColumnHeadersHeightSizeMode = DataGridViewColumnHeadersHeightSizeMode.DisableResizing;
            gridCart.ColumnHeadersHeight = 34;
            gridCart.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            gridCart.ColumnHeadersDefaultCellStyle.ForeColor = Color.FromArgb(30, 41, 59);
            gridCart.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            gridCart.RowTemplate.Height = 34;
            gridCart.RowsDefaultCellStyle.Font = new Font("Segoe UI", 9f);
            gridCart.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(248, 250, 252);
            gridCart.Columns.Add("nama", "Barang");
            gridCart.Columns.Add("harga", "Harga");
            gridCart.Columns.Add("qty", "Qty");
            gridCart.Columns.Add("subtotal", "Subtotal");
            gridCart.Columns["nama"].MinimumWidth = 90;
            gridCart.Columns["nama"].FillWeight = 135;
            gridCart.Columns["harga"].MinimumWidth = 65;
            gridCart.Columns["harga"].FillWeight = 75;
            gridCart.Columns["qty"].MinimumWidth = 40;
            gridCart.Columns["qty"].FillWeight = 45;
            gridCart.Columns["subtotal"].MinimumWidth = 70;
            gridCart.Columns["subtotal"].FillWeight = 80;
            gridCart.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["qty"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns["subtotal"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["subtotal"].DefaultCellStyle.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            gridCart.Columns["subtotal"].DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);
            gridCart.CellValueChanged += (s, e) => RecalculateCart();
            rightPanel.Controls.Add(gridCart);

            // Bottom Summary & Checkout Button (2-Tier Layout: Row 1 = Biometric primary, Row 2 = Manual & Clear)
            Panel bottomPanel = new Panel { 
                Dock = DockStyle.Bottom, 
                Height = 165, 
                BackColor = Color.FromArgb(248, 250, 252), 
                Padding = new Padding(12, 8, 12, 10) 
            };
            
            lblTotal = new Label {
                Text = "TOTAL: Rp 0",
                Font = new Font("Segoe UI", 18f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Dock = DockStyle.Top,
                Height = 40,
                TextAlign = ContentAlignment.MiddleRight
            };
            bottomPanel.Controls.Add(lblTotal);

            TableLayoutPanel btnTable = new TableLayoutPanel {
                Dock = DockStyle.Fill,
                RowCount = 2,
                ColumnCount = 2,
                Padding = new Padding(0, 4, 0, 0)
            };
            btnTable.RowStyles.Add(new RowStyle(SizeType.Absolute, 50f));
            btnTable.RowStyles.Add(new RowStyle(SizeType.Absolute, 38f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 85f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));
            
            btnCheckoutFingerprint = new Button {
                Text = "👆 BAYAR SIDIK JARI WBP",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCheckoutFingerprint.FlatAppearance.BorderSize = 0;
            btnCheckoutFingerprint.Click += (s, e) => ProcessCheckoutFingerprint();
            btnTable.Controls.Add(btnCheckoutFingerprint, 0, 0);
            btnTable.SetColumnSpan(btnCheckoutFingerprint, 2);

            Button btnClear = new Button {
                Text = "🗑️ Batal",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(239, 68, 68),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnClear.FlatAppearance.BorderSize = 0;
            btnClear.Click += (s, e) => { cart.Clear(); RefreshCartGrid(); };
            btnTable.Controls.Add(btnClear, 0, 1);

            btnCheckoutManual = new Button {
                Text = "👤 Bayar Manual",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCheckoutManual.FlatAppearance.BorderSize = 0;
            btnCheckoutManual.Click += (s, e) => ProcessCheckoutManual();
            btnTable.Controls.Add(btnCheckoutManual, 1, 1);

            bottomPanel.Controls.Add(btnTable);
            btnTable.BringToFront();
            rightPanel.Controls.Add(bottomPanel);

            gridCart.BringToFront();
            splitKasir.Panel2.Controls.Add(rightPanel);

            page.Controls.Add(splitKasir);
        }
        #endregion

        #region TAB 2: ENROLLMENT
        private void BuildEnrollmentTab(TabPage page)
        {
            Panel panel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(24), AutoScroll = true, BackColor = Color.FromArgb(248, 250, 252) };

            Label lblTitle = new Label {
                Text = "👆 Pendaftaran & Perekaman Biometrik Sidik Jari WBP (4-Touch Enrollment)",
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
                Text = " 👆 Panel Perekaman Biometrik Sensor U.are.U ",
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

            // Sensor Preview Box
            picEnrollPreview = new PictureBox {
                Location = new Point(16, 145),
                Width = 160,
                Height = 200,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            grpAction.Controls.Add(picEnrollPreview);

            // Enrollment Status & Step Indicator
            lblEnrollStep = new Label {
                Text = "Status: Pilih Warga Binaan pada daftar, lalu klik tombol Mulai Perekaman di bawah.",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(190, 150),
                Width = 280,
                Height = 100
            };
            grpAction.Controls.Add(lblEnrollStep);

            btnStartEnroll = new Button {
                Text = "▶️ Mulai Perekaman 4x Sentuh Jari",
                Location = new Point(190, 260),
                Width = 280,
                Height = 48,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
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

        #region TAB 4: PENGATURAN SISTEM & DIAGNOSTIK SENSOR
        // Sensor Diagnostic Fields
        private PictureBox picDiagFinger;
        private Label lblDiagStatus;
        private Label lblDiagQuality;
        private Label lblDiagLatency;
        private Label lblDiagMatch;
        private Label lblDiagDevice;
        private Label lblDiagSerial;
        private Button btnDiagToggle;
        private Button btnDiagSaveEnroll;
        private Button btnDiagCompare;
        private bool isDiagCapturing = false;
        private int diagScanCount = 0;
        private Fmd diagEnrolledFmd = null;
        private Fmd diagLastFmd = null;
        private System.Diagnostics.Stopwatch diagSw = new System.Diagnostics.Stopwatch();

        private void BuildSettingsTab(TabPage page)
        {
            Panel p = new Panel { 
                Dock = DockStyle.Fill, 
                Padding = new Padding(24), 
                AutoScroll = true, 
                BackColor = Color.FromArgb(248, 250, 252) 
            };

            Label lblTitle = new Label { 
                Text = "⚙️ Pengaturan Sistem & Uji Sensor Biometrik", 
                Font = new Font("Segoe UI", 15f, FontStyle.Bold), 
                ForeColor = Color.FromArgb(15, 23, 42),
                AutoSize = true, 
                Location = new Point(24, 18) 
            };
            p.Controls.Add(lblTitle);

            Label lblSub = new Label { 
                Text = "Konfigurasi alamat server UangPas, tata letak keranjang belanja kasir, dan pengujian sensor sidik jari.", 
                Font = new Font("Segoe UI", 9.5f), 
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true, 
                Location = new Point(24, 50) 
            };
            p.Controls.Add(lblSub);

            int cardTop = 86;
            int cardWidth = 860;

            // ==========================================
            // CARD 1: PENGATURAN ALAMAT SERVER UANGPAS
            // ==========================================
            GroupBox grpServer = new GroupBox {
                Text = " 🌐 Pengaturan Alamat Server UangPas ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 260,
                BackColor = Color.White
            };

            Label lblServerDesc = new Label {
                Text = "Masukkan Alamat IP atau Domain Server UangPas Lapas (contoh: uangpas.test, 192.168.1.50, atau 127.0.0.1:8000):",
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(18, 28),
                Width = 820,
                Height = 20
            };
            grpServer.Controls.Add(lblServerDesc);

            Label lblIpTag = new Label { 
                Text = "Alamat IP / Domain Server:", 
                Location = new Point(18, 54), 
                Width = 220, 
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59)
            };
            grpServer.Controls.Add(lblIpTag);

            txtServerAddress = new TextBox {
                Location = new Point(18, 76),
                Width = 470,
                Font = new Font("Segoe UI", 11f),
                Text = GetDisplayServerAddress(this.apiUrl)
            };
            grpServer.Controls.Add(txtServerAddress);

            Button btnSaveServer = new Button {
                Text = "💾 Simpan & Sambungkan",
                Location = new Point(500, 74),
                Width = 200,
                Height = 35,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnSaveServer.FlatAppearance.BorderSize = 0;
            btnSaveServer.Click += (s, e) => {
                string input = txtServerAddress.Text.Trim();
                if (string.IsNullOrEmpty(input))
                {
                    MessageBox.Show("Silakan masukkan alamat IP atau Domain server.", "Perhatian", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }
                ApplyNewServerUrl(input);
            };
            grpServer.Controls.Add(btnSaveServer);

            Button btnTestServer = new Button {
                Text = "🔄 Uji Koneksi",
                Location = new Point(710, 74),
                Width = 130,
                Height = 35,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnTestServer.Click += (s, e) => {
                btnTestServer.Text = "⏳ Menguji...";
                btnTestServer.Enabled = false;
                ThreadPool.QueueUserWorkItem(_ => {
                    TestCurrentConnection();
                    try
                    {
                        this.BeginInvoke((Action)(() => {
                            btnTestServer.Text = "🔄 Uji Koneksi";
                            btnTestServer.Enabled = true;
                        }));
                    }
                    catch { }
                });
            };
            grpServer.Controls.Add(btnTestServer);

            // Quick Presets
            Label lblPresets = new Label {
                Text = "Pilihan Cepat:",
                Location = new Point(18, 122),
                Width = 95,
                Height = 26,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Italic),
                ForeColor = Color.FromArgb(100, 116, 139),
                TextAlign = ContentAlignment.MiddleLeft
            };
            grpServer.Controls.Add(lblPresets);

            Button btnPresetLaragon = new Button {
                Text = "🌐 Laragon Lokal (uangpas.test)",
                Location = new Point(118, 120),
                Width = 230,
                Height = 28,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPresetLaragon.Click += (s, e) => {
                txtServerAddress.Text = "uangpas.test";
            };
            grpServer.Controls.Add(btnPresetLaragon);

            Button btnPresetLocalhost = new Button {
                Text = "🏠 Localhost Port 8000 (127.0.0.1:8000)",
                Location = new Point(358, 120),
                Width = 270,
                Height = 28,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(30, 41, 59),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPresetLocalhost.Click += (s, e) => {
                txtServerAddress.Text = "127.0.0.1:8000";
            };
            grpServer.Controls.Add(btnPresetLocalhost);

            lblCurrentServerInfo = new Label {
                Text = "Alamat Aktif: " + this.apiUrl + "\nStatus: Menunggu pengujian...",
                Font = new Font("Segoe UI", 9.5f),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(18, 158),
                Width = 820,
                Height = 44
            };
            grpServer.Controls.Add(lblCurrentServerInfo);

            // Android LAN Info Banner
            Panel pnlMobileHint = new Panel {
                Location = new Point(18, 206),
                Width = 820,
                Height = 40,
                BackColor = Color.FromArgb(240, 253, 250),
                BorderStyle = BorderStyle.FixedSingle
            };
            Label lblMobileHint = new Label {
                Text = "📱 Akses dari HP Android / Tablet (Satu Wi-Fi): Buka browser HP dan ketik  http://192.168.1.11  atau  http://192.168.1.11:8000",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(13, 148, 136),
                Dock = DockStyle.Fill,
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(8, 0, 8, 0)
            };
            pnlMobileHint.Controls.Add(lblMobileHint);
            grpServer.Controls.Add(pnlMobileHint);

            p.Controls.Add(grpServer);

            cardTop += 275;

            // ==========================================
            // CARD 2: PENGATURAN TATA LETAK KERANJANG
            // ==========================================
            GroupBox grpLayout = new GroupBox {
                Text = " 🛒 Pengaturan Tata Letak Keranjang Belanja & Katalog ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 150,
                BackColor = Color.White
            };

            lblSplitterInfo = new Label {
                Text = "Ukuran Saat Ini: " + (int)(this.savedSplitterRatio * 100) + "% Katalog Produk  |  " + (int)Math.Round((1.0 - this.savedSplitterRatio) * 100) + "% Keranjang Belanja\n(Posisi pemisah juga dapat langsung digeser dengan mouse di Tab Kasir, tersimpan otomatis).",
                Font = new Font("Segoe UI", 9f),
                ForeColor = Color.FromArgb(71, 85, 105),
                Location = new Point(18, 28),
                Width = 820,
                Height = 42
            };
            grpLayout.Controls.Add(lblSplitterInfo);

            Button btnPreset25 = new Button {
                Text = "📏 Standar (75% Produk : 25% Keranjang)",
                Location = new Point(18, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset25.Click += (s, e) => {
                this.savedSplitterRatio = 0.75;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Standar:\n25% Keranjang Belanja, 75% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset25);

            Button btnPreset35 = new Button {
                Text = "📐 Sedang (65% Produk : 35% Keranjang)",
                Location = new Point(295, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset35.Click += (s, e) => {
                this.savedSplitterRatio = 0.65;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Sedang:\n35% Keranjang Belanja, 65% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset35);

            Button btnPreset50 = new Button {
                Text = "⚖️ Seimbang (50% Produk : 50% Keranjang)",
                Location = new Point(572, 80),
                Width = 265,
                Height = 42,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPreset50.Click += (s, e) => {
                this.savedSplitterRatio = 0.50;
                SaveLocalSettings();
                ApplySplitterRatio();
                UpdateSplitterInfoText();
                MessageBox.Show("Tata letak keranjang belanja diatur ke ukuran Seimbang:\n50% Keranjang Belanja, 50% Katalog Produk.", "Tata Letak Diperbarui", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            grpLayout.Controls.Add(btnPreset50);

            p.Controls.Add(grpLayout);

            cardTop += 165;

            // ==========================================
            // CARD 3: UJI DIAGNOSTIK SENSOR SIDIK JARI
            // ==========================================
            GroupBox grpDiag = new GroupBox {
                Text = " 🔬 Uji & Diagnostik Sensor Sidik Jari Biometrik ",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(24, cardTop),
                Width = cardWidth,
                Height = 445,
                BackColor = Color.White
            };

            // Left: Live visual preview
            Panel pnlDiagLeft = new Panel {
                Location = new Point(18, 28),
                Width = 310,
                Height = 405,
                BackColor = Color.Transparent
            };

            Label lblPreviewTitle = new Label {
                Text = "Visual Citra Sidik Jari:",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Dock = DockStyle.Top,
                Height = 24
            };
            pnlDiagLeft.Controls.Add(lblPreviewTitle);

            picDiagFinger = new PictureBox {
                Dock = DockStyle.Top,
                Height = 265,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            pnlDiagLeft.Controls.Add(picDiagFinger);

            lblDiagStatus = new Label {
                Text = "Sensor dalam keadaan siaga (standby).",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Dock = DockStyle.Top,
                Height = 55,
                TextAlign = ContentAlignment.MiddleCenter
            };
            pnlDiagLeft.Controls.Add(lblDiagStatus);

            btnDiagToggle = new Button {
                Text = "▶️ Mulai Pengujian Sensor",
                Dock = DockStyle.Bottom,
                Height = 44,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnDiagToggle.FlatAppearance.BorderSize = 0;
            btnDiagToggle.Click += (s, e) => ToggleDiagCapture();
            pnlDiagLeft.Controls.Add(btnDiagToggle);
            grpDiag.Controls.Add(pnlDiagLeft);

            // Right: Sensor Info, Quality, and Verification
            Panel pnlDiagRight = new Panel {
                Location = new Point(345, 28),
                Width = 495,
                Height = 405,
                BackColor = Color.Transparent
            };

            // Sub-box 1: Hardware Info
            GroupBox grpHardware = new GroupBox {
                Text = " Informasi Perangkat Scanner ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 0),
                Width = 495,
                Height = 95
            };
            lblDiagDevice = new Label { 
                Text = "Perangkat: " + (currentReader != null ? currentReader.Description.Id.ProductName : "Mendeteksi..."), 
                Location = new Point(14, 22), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            lblDiagSerial = new Label { 
                Text = "Serial ID: " + (currentReader != null ? currentReader.Description.SerialNumber : "-"), 
                Location = new Point(14, 44), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            Label lblResInfo = new Label { 
                Text = "Resolusi: 500 DPI (ANSI/NIST Optical Sensor)", 
                Location = new Point(14, 66), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            grpHardware.Controls.Add(lblDiagDevice);
            grpHardware.Controls.Add(lblDiagSerial);
            grpHardware.Controls.Add(lblResInfo);
            pnlDiagRight.Controls.Add(grpHardware);

            // Sub-box 2: Kualitas Scan Terakhir
            GroupBox grpQuality = new GroupBox {
                Text = " Kualitas Hasil Scan Terakhir ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 102),
                Width = 495,
                Height = 82
            };
            lblDiagQuality = new Label { 
                Text = "Kualitas: Belum ada sentuhan", 
                Location = new Point(14, 24), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            lblDiagLatency = new Label { 
                Text = "Kecepatan Respon: -", 
                Location = new Point(14, 48), 
                AutoSize = true, 
                Font = new Font("Segoe UI", 9f) 
            };
            grpQuality.Controls.Add(lblDiagQuality);
            grpQuality.Controls.Add(lblDiagLatency);
            pnlDiagRight.Controls.Add(grpQuality);

            // Sub-box 3: Uji Pencocokan 1:1
            GroupBox grpTest = new GroupBox {
                Text = " Uji Pencocokan 1:1 Biometrik ",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Location = new Point(0, 192),
                Width = 495,
                Height = 155
            };

            btnDiagSaveEnroll = new Button { 
                Text = "1. Rekam Template Uji", 
                Location = new Point(14, 26), 
                Width = 205, 
                Height = 34, 
                BackColor = Color.FromArgb(2, 132, 199), 
                ForeColor = Color.White, 
                FlatStyle = FlatStyle.Flat, 
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Enabled = false 
            };
            btnDiagSaveEnroll.FlatAppearance.BorderSize = 0;
            btnDiagSaveEnroll.Click += (s, e) => {
                if (diagLastFmd != null) {
                    diagEnrolledFmd = diagLastFmd;
                    btnDiagCompare.Enabled = true;
                    btnDiagCompare.BackColor = Color.FromArgb(16, 185, 129);
                    btnDiagCompare.ForeColor = Color.White;
                    lblDiagMatch.Text = "Template tersimpan. Tempelkan jari lagi lalu klik '2. Uji Kecocokan'.";
                    lblDiagMatch.ForeColor = Color.FromArgb(16, 185, 129);
                }
            };

            btnDiagCompare = new Button { 
                Text = "2. Uji Kecocokan", 
                Location = new Point(230, 26), 
                Width = 150, 
                Height = 34, 
                BackColor = Color.FromArgb(241, 245, 249), 
                FlatStyle = FlatStyle.Flat, 
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Enabled = false 
            };
            btnDiagCompare.Click += (s, e) => {
                if (diagEnrolledFmd != null && diagLastFmd != null) {
                    CompareResult cr = Comparison.Compare(diagLastFmd, 0, diagEnrolledFmd, 0);
                    int threshold = 2147483647 / 100000;
                    if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Score < threshold) {
                        lblDiagMatch.Text = "🎉 COCOK 100%! Sidik jari identik (Score: " + cr.Score + ")";
                        lblDiagMatch.ForeColor = Color.FromArgb(16, 185, 129);
                        MessageBox.Show("✅ HASIL UJI COCOK 100%!\n\nAlgoritma ANSI Biometrik berhasil mencocokkan sidik jari dengan template referensi.", "Hasil Verifikasi: COCOK", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    } else {
                        lblDiagMatch.Text = "❌ TIDAK COCOK! Jari berbeda.";
                        lblDiagMatch.ForeColor = Color.FromArgb(239, 68, 68);
                        MessageBox.Show("❌ HASIL UJI TIDAK COCOK!\n\nSensor mendeteksi bahwa jari berbeda. Sistem proteksi transaksi bekerja akurat.", "Hasil Verifikasi: BEDA", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
            };

            lblDiagMatch = new Label { 
                Text = "Tempelkan jari pada sensor untuk merekam template uji.", 
                Location = new Point(14, 70), 
                Width = 465, 
                Height = 70, 
                Font = new Font("Segoe UI", 8.5f),
                ForeColor = Color.FromArgb(100, 116, 139) 
            };
            grpTest.Controls.Add(btnDiagSaveEnroll);
            grpTest.Controls.Add(btnDiagCompare);
            grpTest.Controls.Add(lblDiagMatch);
            pnlDiagRight.Controls.Add(grpTest);

            // Bottom action buttons for Diagnostic
            Button btnRefreshScanner = new Button {
                Text = "🔄 Deteksi Ulang Scanner",
                Location = new Point(0, 358),
                Width = 235,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnRefreshScanner.Click += (s, e) => {
                StopDiagCapture();
                InitializeBiometrics();
                if (currentReader != null) {
                    lblDiagDevice.Text = "Perangkat: " + currentReader.Description.Id.ProductName;
                    lblDiagSerial.Text = "Serial ID: " + currentReader.Description.SerialNumber;
                }
            };
            pnlDiagRight.Controls.Add(btnRefreshScanner);

            Button btnOpenSdk = new Button {
                Text = "🛠️ Diagnostic Sample SDK",
                Location = new Point(245, 358),
                Width = 250,
                Height = 38,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(15, 23, 42),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnOpenSdk.Click += (s, e) => {
                string sdkPath = @"C:\Program Files\DigitalPersona\U.are.U SDK\Windows\Samples\Bin\.NET\UareUSampleCSharp.exe";
                if (File.Exists(sdkPath)) {
                    StopDiagCapture();
                    try { System.Diagnostics.Process.Start(sdkPath); } catch { }
                } else {
                    MessageBox.Show("File tidak ditemukan: " + sdkPath, "Info", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            };
            pnlDiagRight.Controls.Add(btnOpenSdk);

            grpDiag.Controls.Add(pnlDiagRight);
            p.Controls.Add(grpDiag);

            page.Controls.Add(p);
        }

        private void UpdateSplitterInfoText()
        {
            if (lblSplitterInfo != null)
            {
                int cartPct = (int)Math.Round((1.0 - this.savedSplitterRatio) * 100);
                int prodPct = 100 - cartPct;
                lblSplitterInfo.Text = string.Format("Ukuran Saat Ini: {0}% Katalog Produk  |  {1}% Keranjang Belanja\n(Posisi pemisah juga dapat langsung digeser dengan mouse di Tab Kasir, tersimpan otomatis).", prodPct, cartPct);
            }
        }
        #endregion

        #region SETTINGS METHODS
        private void LoadLocalSettings()
        {
            try
            {
                string path = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
                if (File.Exists(path))
                {
                    string json = File.ReadAllText(path);
                    var dict = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (dict != null)
                    {
                        if (dict.ContainsKey("api_url") && dict["api_url"] != null && !string.IsNullOrEmpty(dict["api_url"].ToString()))
                        {
                            this.apiUrl = NormalizeApiUrl(dict["api_url"].ToString().Trim());
                        }
                        if (dict.ContainsKey("splitter_ratio") && dict["splitter_ratio"] != null)
                        {
                            double r;
                            if (double.TryParse(dict["splitter_ratio"].ToString(), System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out r))
                            {
                                if (r >= 0.40 && r <= 0.85) this.savedSplitterRatio = r;
                                else this.savedSplitterRatio = 0.75;
                            }
                        }
                    }
                }
            }
            catch { }
        }

        private void SaveLocalSettings()
        {
            try
            {
                string path = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
                Dictionary<string, object> dict = new Dictionary<string, object>();
                dict["api_url"] = this.apiUrl;
                dict["splitter_ratio"] = this.savedSplitterRatio;
                File.WriteAllText(path, serializer.Serialize(dict));
            }
            catch { }
        }

        private void ApplySplitterRatio()
        {
            try
            {
                if (splitKasir != null && splitKasir.Width > 400)
                {
                    isApplyingSplitter = true;
                    double ratio = (this.savedSplitterRatio >= 0.40 && this.savedSplitterRatio <= 0.85)
                        ? this.savedSplitterRatio
                        : 0.75; // 75% left (katalog produk), 25% right (keranjang belanja)
                    int targetDist = (int)(splitKasir.Width * ratio);
                    int minLeft = 450;
                    int minRight = 260;
                    if (targetDist < minLeft) targetDist = minLeft;
                    if (splitKasir.Width - targetDist < minRight) targetDist = Math.Max(minLeft, splitKasir.Width - minRight);
                    if (targetDist > 0 && targetDist < splitKasir.Width)
                    {
                        splitKasir.SplitterDistance = targetDist;
                    }
                }
            }
            catch { }
            finally
            {
                isApplyingSplitter = false;
            }
        }

        private string NormalizeApiUrl(string input)
        {
            if (string.IsNullOrEmpty(input)) return "http://uangpas.test/api/v1";
            string s = input.Trim().TrimEnd('/');
            if (!s.StartsWith("http://", StringComparison.OrdinalIgnoreCase) && !s.StartsWith("https://", StringComparison.OrdinalIgnoreCase))
            {
                s = "http://" + s;
            }
            try
            {
                Uri uri = new Uri(s);
                string scheme = uri.Scheme;
                string host = uri.Host;
                int port = uri.Port;

                string authority;
                if (uri.IsDefaultPort || (scheme.Equals("http", StringComparison.OrdinalIgnoreCase) && port == 80) || (scheme.Equals("https", StringComparison.OrdinalIgnoreCase) && port == 443))
                {
                    authority = host;
                }
                else
                {
                    authority = host + ":" + port;
                }

                string path = uri.AbsolutePath.TrimEnd('/');
                if (string.IsNullOrEmpty(path) || path == "/")
                {
                    path = "/api/v1";
                }
                else if (!path.EndsWith("/api/v1", StringComparison.OrdinalIgnoreCase))
                {
                    if (path.EndsWith("/api", StringComparison.OrdinalIgnoreCase)) path += "/v1";
                    else path += "/api/v1";
                }
                return scheme + "://" + authority + path;
            }
            catch
            {
                return s;
            }
        }

        private string GetDisplayServerAddress(string url)
        {
            try
            {
                if (string.IsNullOrEmpty(url)) return "uangpas.test";
                Uri uri = new Uri(url);
                if (uri.IsDefaultPort || uri.Port == 80 || uri.Port == 443)
                {
                    return uri.Host;
                }
                return uri.Host + ":" + uri.Port;
            }
            catch
            {
                return url ?? "uangpas.test";
            }
        }

        private void ApplyNewServerUrl(string rawUrl)
        {
            string normalized = NormalizeApiUrl(rawUrl);
            this.apiUrl = normalized;
            if (txtServerAddress != null) txtServerAddress.Text = GetDisplayServerAddress(normalized);
            SaveLocalSettings();

            if (lblCurrentServerInfo != null)
            {
                lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n⏳ Menghubungkan & menyinkronkan data...";
                lblCurrentServerInfo.ForeColor = Color.FromArgb(2, 132, 199);
            }

            ThreadPool.QueueUserWorkItem(_ => {
                TestCurrentConnection();
                SyncDataFromApi();
            });

            MessageBox.Show("Alamat Server UangPas berhasil diperbarui ke:\n" + this.apiUrl + "\n\nSistem sedang menyinkronkan database produk dan WBP.", "Koneksi Disimpan", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }

        private void TestCurrentConnection()
        {
            try
            {
                Stopwatch sw = Stopwatch.StartNew();
                string pingUrl = this.apiUrl.TrimEnd('/') + "/sync/ping";
                string json = HttpGet(pingUrl);
                sw.Stop();

                string uptName = "Server UangPas";
                try
                {
                    var data = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (data != null && data.ContainsKey("upt_name") && data["upt_name"] != null)
                        uptName = data["upt_name"].ToString();
                }
                catch { }

                this.BeginInvoke((Action)(() => {
                    if (lblCurrentServerInfo != null)
                    {
                        lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n✅ Terhubung ke " + uptName + " (" + sw.ElapsedMilliseconds + " ms latency)";
                        lblCurrentServerInfo.ForeColor = Color.FromArgb(16, 185, 129);
                    }
                    if (lblApiStatus != null)
                    {
                        lblApiStatus.Text = "Server: Online (" + productList.Count + " Produk)";
                        lblApiStatus.ForeColor = Color.FromArgb(52, 211, 153);
                    }
                }));
            }
            catch (Exception ex)
            {
                this.BeginInvoke((Action)(() => {
                    if (lblCurrentServerInfo != null)
                    {
                        lblCurrentServerInfo.Text = "Alamat Aktif: " + this.apiUrl + "\n❌ Tidak dapat terhubung: " + ex.Message;
                        lblCurrentServerInfo.ForeColor = Color.FromArgb(239, 68, 68);
                    }
                    if (lblApiStatus != null)
                    {
                        lblApiStatus.Text = "Server: Offline";
                        lblApiStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    }
                }));
            }
        }
        #endregion

        #region SENSOR DIAGNOSTIC METHODS
        private void ToggleDiagCapture()
        {
            if (isDiagCapturing) StopDiagCapture();
            else StartDiagCapture();
        }

        private void StartDiagCapture()
        {
            try
            {
                StopDiagCapture();

                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers == null || readers.Count == 0)
                {
                    lblDiagStatus.Text = "Scanner sidik jari tidak ditemukan. Periksa kabel USB!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Scanner sidik jari tidak ditemukan. Periksa kabel USB.", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    return;
                }

                currentReader = readers[0];
                Constants.ResultCode resOpen = currentReader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (resOpen != Constants.ResultCode.DP_SUCCESS && resOpen != Constants.ResultCode.DP_DEVICE_BUSY)
                {
                    lblDiagStatus.Text = "Gagal buka scanner: " + resOpen + ".\nPastikan aplikasi penguji lain sudah ditutup!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Gagal mengakses scanner (" + resOpen + ")!\n\nKemungkinan scanner masih digunakan oleh aplikasi lain.\nSilakan tutup aplikasi penguji tersebut terlebih dahulu.", "Scanner Sibuk", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    return;
                }

                currentReader.On_Captured += Diag_OnCaptured;
                currentReader.GetStatus();

                int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                    ? currentReader.Capabilities.Resolutions[0] : 500;

                diagSw.Restart();
                Constants.ResultCode rc = currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);
                if (rc == Constants.ResultCode.DP_SUCCESS)
                {
                    isDiagCapturing = true;
                    btnDiagToggle.Text = "⏹️ Hentikan Pengujian";
                    btnDiagToggle.BackColor = Color.FromArgb(239, 68, 68);
                    lblDiagStatus.Text = "🔴 Sensor Aktif! TEMPELKAN JARI PADA SCANNER SEKARANG...";
                    lblDiagStatus.ForeColor = Color.FromArgb(2, 132, 199);
                }
                else
                {
                    lblDiagStatus.Text = "CaptureAsync gagal: " + rc;
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
            catch (Exception ex)
            {
                lblDiagStatus.Text = "Error: " + ex.Message;
                lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
            }
        }

        private void StopDiagCapture()
        {
            if (currentReader != null)
            {
                try
                {
                    currentReader.CancelCapture();
                    currentReader.On_Captured -= Diag_OnCaptured;
                    currentReader.Dispose();
                }
                catch { }
                currentReader = null;
            }
            isDiagCapturing = false;
            if (btnDiagToggle != null)
            {
                btnDiagToggle.Text = "▶️ Mulai Pengujian Sensor";
                btnDiagToggle.BackColor = Color.FromArgb(16, 185, 129);
            }
            if (lblDiagStatus != null)
            {
                lblDiagStatus.Text = "Sensor dalam keadaan siaga (standby).";
                lblDiagStatus.ForeColor = Color.FromArgb(100, 116, 139);
            }
        }

        private void Diag_OnCaptured(CaptureResult cr)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action<CaptureResult>(Diag_OnCaptured), cr);
                return;
            }

            diagSw.Stop();
            long ms = diagSw.ElapsedMilliseconds;

            if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Data != null)
            {
                diagScanCount++;
                try { System.Media.SystemSounds.Beep.Play(); } catch { }

                if (cr.Data.Views != null && cr.Data.Views.Count > 0)
                {
                    var view = cr.Data.Views[0];
                    if (picDiagFinger != null)
                    {
                        if (picDiagFinger.Image != null) picDiagFinger.Image.Dispose();
                        picDiagFinger.Image = Create24bppBitmapStatic(view.RawImage, view.Width, view.Height);
                    }
                }

                DataResult<Fmd> res = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                if (res.ResultCode == Constants.ResultCode.DP_SUCCESS && res.Data != null)
                {
                    diagLastFmd = res.Data;
                    if (btnDiagSaveEnroll != null) btnDiagSaveEnroll.Enabled = true;
                    if (diagEnrolledFmd != null && btnDiagCompare != null) {
                        btnDiagCompare.Enabled = true;
                    }
                }

                if (lblDiagStatus != null)
                {
                    lblDiagStatus.Text = "🟢 JARI TERDETEKSI! (Scan #" + diagScanCount + " Sukses)";
                    lblDiagStatus.ForeColor = Color.FromArgb(16, 185, 129);
                }

                if (lblDiagQuality != null)
                {
                    lblDiagQuality.Text = "Kualitas: " + cr.Quality + " (Bagus)";
                    lblDiagQuality.ForeColor = Color.FromArgb(16, 185, 129);
                }

                if (lblDiagLatency != null)
                {
                    lblDiagLatency.Text = "Kecepatan Respon: " + ms + " ms (Scan #" + diagScanCount + ")";
                }

                // Continue capture
                RestartDiagCapture();
            }
            else
            {
                if (lblDiagQuality != null)
                {
                    lblDiagQuality.Text = "Kualitas: " + cr.Quality;
                    lblDiagQuality.ForeColor = Color.FromArgb(239, 68, 68);
                }
                RestartDiagCapture();
            }
        }

        private void RestartDiagCapture()
        {
            if (currentReader != null && isDiagCapturing)
            {
                try
                {
                    diagSw.Restart();
                    int dpi = (currentReader.Capabilities.Resolutions != null && currentReader.Capabilities.Resolutions.Length > 0)
                        ? currentReader.Capabilities.Resolutions[0] : 500;
                    currentReader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, dpi);
                }
                catch { }
            }
        }

        public static Bitmap Create24bppBitmapStatic(byte[] bytes, int width, int height)
        {
            try
            {
                byte[] rgbBytes = new byte[bytes.Length * 3];
                for (int i = 0; i <= bytes.Length - 1; i++)
                {
                    rgbBytes[(i * 3)] = bytes[i];
                    rgbBytes[(i * 3) + 1] = bytes[i];
                    rgbBytes[(i * 3) + 2] = bytes[i];
                }
                Bitmap bmp = new Bitmap(width, height, PixelFormat.Format24bppRgb);
                BitmapData data = bmp.LockBits(new Rectangle(0, 0, bmp.Width, bmp.Height), ImageLockMode.WriteOnly, PixelFormat.Format24bppRgb);
                for (int i = 0; i <= bmp.Height - 1; i++)
                {
                    IntPtr p = new IntPtr(data.Scan0.ToInt64() + data.Stride * i);
                    System.Runtime.InteropServices.Marshal.Copy(rgbBytes, i * bmp.Width * 3, p, bmp.Width * 3);
                }
                bmp.UnlockBits(data);
                return bmp;
            }
            catch
            {
                return null;
            }
        }
        #endregion

        #region BIOMETRIC READER INITIALIZATION
        private void InitializeBiometrics()
        {
            try
            {
                ReaderCollection readers = null;
                // Retry up to 5 times to handle RTE background service enumeration
                for (int i = 0; i < 5; i++)
                {
                    readers = ReaderCollection.GetReaders();
                    if (readers != null && readers.Count > 0) break;
                    System.Threading.Thread.Sleep(150);
                }

                if (readers != null && readers.Count > 0)
                {
                    currentReader = readers[0];
                    string prodName = currentReader.Description.Id.ProductName;
                    if (string.IsNullOrEmpty(prodName)) prodName = "U.are.U 4500";
                    lblScannerStatus.Text = "Scanner: " + prodName + " (Siap)";
                    lblScannerStatus.ForeColor = Color.FromArgb(52, 211, 153);
                    if (lblDiagDevice != null) lblDiagDevice.Text = "Perangkat: " + prodName;
                    if (lblDiagSerial != null) lblDiagSerial.Text = "Serial ID: " + currentReader.Description.SerialNumber;
                }
                else
                {
                    currentReader = null;
                    lblScannerStatus.Text = "Scanner: Belum Terbaca (Klik Uji)";
                    lblScannerStatus.ForeColor = Color.FromArgb(248, 113, 113);
                }
            }
            catch (Exception ex)
            {
                currentReader = null;
                lblScannerStatus.Text = "Scanner Error: " + ex.Message;
                lblScannerStatus.ForeColor = Color.FromArgb(248, 113, 113);
            }
        }
        #endregion

        #region DATA SYNC FROM LARAVEL API
        private decimal ParseDecimal(object val)
        {
            if (val == null) return 0;
            decimal d = 0;
            if (decimal.TryParse(val.ToString(), System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out d))
                return d;
            decimal.TryParse(val.ToString(), out d);
            return d;
        }

        private void SyncDataFromApi()
        {
            try
            {
                // First, flush any pending offline transactions!
                FlushOfflineQueue();

                // 1. Fetch Products
                string productsJson = HttpGet(this.apiUrl + "/pos/products");
                var prodResp = serializer.Deserialize<Dictionary<string, object>>(productsJson);
                if (prodResp != null && prodResp.ContainsKey("data"))
                {
                    var items = (System.Collections.ArrayList)prodResp["data"];
                    productList.Clear();
                    foreach (Dictionary<string, object> it in items)
                    {
                        productList.Add(it);
                    }
                    try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_products.json"), productsJson); } catch { }
                    PopulateCategories();
                    RenderProductGrid();
                }

                // 2. Fetch Inmates
                string inmatesJson = HttpGet(this.apiUrl + "/pos/inmates");
                var inmateResp = serializer.Deserialize<Dictionary<string, object>>(inmatesJson);
                if (inmateResp != null && inmateResp.ContainsKey("data"))
                {
                    var list = (System.Collections.ArrayList)inmateResp["data"];
                    inmateList.Clear();
                    cmbInmateEnroll.Items.Clear();

                    foreach (Dictionary<string, object> wbp in list)
                    {
                        inmateList.Add(wbp);
                        string hasFp = Convert.ToBoolean(wbp["has_fingerprint"]) ? " [Sudah Ada Sidik Jari]" : " [BELUM ADA SIDIK JARI]";
                        cmbInmateEnroll.Items.Add(wbp["id"] + " - " + wbp["nama"] + " (" + wbp["no_registrasi"] + ")" + hasFp);
                    }
                    if (cmbInmateEnroll.Items.Count > 0) cmbInmateEnroll.SelectedIndex = 0;
                    try { this.BeginInvoke((Action)(() => FilterEnrollInmates())); } catch { }
                    try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_inmates.json"), inmatesJson); } catch { }
                }

                // 3. Fetch Receipt Settings
                try {
                    string receiptJson = HttpGet(this.apiUrl + "/pos/receipt-settings");
                    var rResp = serializer.Deserialize<Dictionary<string, object>>(receiptJson);
                    if (rResp != null && rResp.ContainsKey("data"))
                    {
                        var rData = (Dictionary<string, object>)rResp["data"];
                        if (rData.ContainsKey("store_name") && rData["store_name"] != null) this.receiptConfig.StoreName = rData["store_name"].ToString();
                        if (rData.ContainsKey("address") && rData["address"] != null) this.receiptConfig.Address = rData["address"].ToString();
                        if (rData.ContainsKey("phone") && rData["phone"] != null) this.receiptConfig.Phone = rData["phone"].ToString();
                        if (rData.ContainsKey("footer") && rData["footer"] != null) this.receiptConfig.Footer = rData["footer"].ToString();
                        if (rData.ContainsKey("paper_width") && rData["paper_width"] != null) this.receiptConfig.PaperWidth = rData["paper_width"].ToString();
                        if (rData.ContainsKey("auto_print") && rData["auto_print"] != null) this.receiptConfig.AutoPrint = Convert.ToBoolean(rData["auto_print"]);
                        if (rData.ContainsKey("show_inmate_balance") && rData["show_inmate_balance"] != null) this.receiptConfig.ShowBalance = Convert.ToBoolean(rData["show_inmate_balance"]);
                        File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_receipt.json"), receiptJson);
                    }
                } catch { }

                int queueCount = GetOfflineQueueCount();
                if (queueCount > 0)
                {
                    lblApiStatus.Text = "Server: Online (" + queueCount + " antrean offline)";
                    lblApiStatus.ForeColor = Color.FromArgb(251, 191, 36);
                }
                else
                {
                    lblApiStatus.Text = "Server: Online (" + productList.Count + " Produk, " + inmateList.Count + " WBP)";
                    lblApiStatus.ForeColor = Color.FromArgb(52, 211, 153);
                }
            }
            catch (Exception ex)
            {
                LoadLocalCache();
                int qCount = GetOfflineQueueCount();
                lblApiStatus.Text = "Server: Mode Offline" + (qCount > 0 ? " (" + qCount + " antrean)" : "");
                lblApiStatus.ForeColor = Color.FromArgb(251, 191, 36);
            }
        }

        private void PopulateCategories()
        {
            if (cmbCategory == null) return;
            string currentSelected = (cmbCategory.SelectedItem != null) ? cmbCategory.SelectedItem.ToString() : "Semua Kategori";
            HashSet<string> cats = new HashSet<string>();
            foreach (var p in productList)
            {
                if (p.ContainsKey("kategori") && p["kategori"] != null && !string.IsNullOrEmpty(p["kategori"].ToString()))
                {
                    cats.Add(p["kategori"].ToString());
                }
            }
            cmbCategory.Items.Clear();
            cmbCategory.Items.Add("Semua Kategori");
            foreach (string c in cats) cmbCategory.Items.Add(c);
            if (cmbCategory.Items.Contains(currentSelected)) cmbCategory.SelectedItem = currentSelected;
            else cmbCategory.SelectedIndex = 0;
        }

        private void LoadLocalCache()
        {
            try
            {
                string pCache = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_products.json");
                if (File.Exists(pCache))
                {
                    string json = File.ReadAllText(pCache);
                    var prodResp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (prodResp != null && prodResp.ContainsKey("data"))
                    {
                        var items = (System.Collections.ArrayList)prodResp["data"];
                        productList.Clear();
                        foreach (Dictionary<string, object> it in items) productList.Add(it);
                        PopulateCategories();
                        RenderProductGrid();
                    }
                }

                string iCache = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_inmates.json");
                if (File.Exists(iCache))
                {
                    string json = File.ReadAllText(iCache);
                    var inmateResp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (inmateResp != null && inmateResp.ContainsKey("data"))
                    {
                        var list = (System.Collections.ArrayList)inmateResp["data"];
                        inmateList.Clear();
                        cmbInmateEnroll.Items.Clear();
                        foreach (Dictionary<string, object> wbp in list)
                        {
                            inmateList.Add(wbp);
                            string hasFp = Convert.ToBoolean(wbp["has_fingerprint"]) ? " [Sudah Ada Sidik Jari]" : " [BELUM ADA SIDIK JARI]";
                            cmbInmateEnroll.Items.Add(wbp["id"] + " - " + wbp["nama"] + " (" + wbp["no_registrasi"] + ")" + hasFp);
                        }
                        if (cmbInmateEnroll.Items.Count > 0) cmbInmateEnroll.SelectedIndex = 0;
                        try { this.BeginInvoke((Action)(() => FilterEnrollInmates())); } catch { }
                    }
                }

                string rCache = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_receipt.json");
                if (File.Exists(rCache))
                {
                    string json = File.ReadAllText(rCache);
                    var rResp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (rResp != null && rResp.ContainsKey("data"))
                    {
                        var rData = (Dictionary<string, object>)rResp["data"];
                        if (rData.ContainsKey("store_name") && rData["store_name"] != null) this.receiptConfig.StoreName = rData["store_name"].ToString();
                        if (rData.ContainsKey("address") && rData["address"] != null) this.receiptConfig.Address = rData["address"].ToString();
                        if (rData.ContainsKey("phone") && rData["phone"] != null) this.receiptConfig.Phone = rData["phone"].ToString();
                        if (rData.ContainsKey("footer") && rData["footer"] != null) this.receiptConfig.Footer = rData["footer"].ToString();
                        if (rData.ContainsKey("paper_width") && rData["paper_width"] != null) this.receiptConfig.PaperWidth = rData["paper_width"].ToString();
                        if (rData.ContainsKey("auto_print") && rData["auto_print"] != null) this.receiptConfig.AutoPrint = Convert.ToBoolean(rData["auto_print"]);
                        if (rData.ContainsKey("show_inmate_balance") && rData["show_inmate_balance"] != null) this.receiptConfig.ShowBalance = Convert.ToBoolean(rData["show_inmate_balance"]);
                    }
                }
            }
            catch { }
        }

        private int GetOfflineQueueCount()
        {
            try
            {
                string queuePath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "offline_queue.json");
                if (File.Exists(queuePath))
                {
                    string json = File.ReadAllText(queuePath);
                    var list = serializer.Deserialize<List<Dictionary<string, object>>>(json);
                    return (list != null) ? list.Count : 0;
                }
            }
            catch { }
            return 0;
        }

        private void QueueOfflineTransaction(Dictionary<string, object> reqData, string invNo)
        {
            try
            {
                string queuePath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "offline_queue.json");
                List<Dictionary<string, object>> queueList = new List<Dictionary<string, object>>();

                if (File.Exists(queuePath))
                {
                    string existingJson = File.ReadAllText(queuePath);
                    var list = serializer.Deserialize<List<Dictionary<string, object>>>(existingJson);
                    if (list != null) queueList = list;
                }

                reqData["offline_inv"] = invNo;
                reqData["queued_at"] = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                queueList.Add(reqData);

                File.WriteAllText(queuePath, serializer.Serialize(queueList));
            }
            catch { }
        }

        private void FlushOfflineQueue()
        {
            try
            {
                string queuePath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "offline_queue.json");
                if (!File.Exists(queuePath)) return;

                string existingJson = File.ReadAllText(queuePath);
                var list = serializer.Deserialize<List<Dictionary<string, object>>>(existingJson);
                if (list == null || list.Count == 0) return;

                List<Dictionary<string, object>> remaining = new List<Dictionary<string, object>>();
                int syncedCount = 0;

                foreach (var item in list)
                {
                    try
                    {
                        string jsonPayload = serializer.Serialize(item);
                        string respJson = HttpPost(this.apiUrl + "/pos/checkout", jsonPayload);
                        var resp = serializer.Deserialize<Dictionary<string, object>>(respJson);
                        if (resp != null && Convert.ToBoolean(resp["success"]))
                        {
                            syncedCount++;
                        }
                        else
                        {
                            remaining.Add(item);
                        }
                    }
                    catch
                    {
                        remaining.Add(item);
                    }
                }

                if (remaining.Count > 0)
                {
                    File.WriteAllText(queuePath, serializer.Serialize(remaining));
                }
                else
                {
                    File.Delete(queuePath);
                }

                if (syncedCount > 0)
                {
                    Console.WriteLine("[SYNC] Berhasil mengunggah " + syncedCount + " transaksi antrean offline.");
                }
            }
            catch { }
        }

        private void RenderProductGrid()
        {
            if (gridProducts == null) return;
            gridProducts.Rows.Clear();

            string keyword = (txtSearchProduct != null) ? txtSearchProduct.Text.Trim().ToLowerInvariant() : "";
            string selectedCat = (cmbCategory != null && cmbCategory.SelectedItem != null) ? cmbCategory.SelectedItem.ToString() : "Semua Kategori";

            foreach (var p in productList)
            {
                string barcode = p.ContainsKey("barcode") && p["barcode"] != null ? p["barcode"].ToString() : "";
                string nama = p.ContainsKey("nama_barang") && p["nama_barang"] != null ? p["nama_barang"].ToString() : "";
                string kategori = p.ContainsKey("kategori") && p["kategori"] != null ? p["kategori"].ToString() : "Umum";
                decimal harga = p.ContainsKey("harga") ? ParseDecimal(p["harga"]) : 0;
                int stok = p.ContainsKey("stok") ? Convert.ToInt32(p["stok"]) : 0;

                if (selectedCat != "Semua Kategori" && !string.Equals(kategori, selectedCat, StringComparison.OrdinalIgnoreCase))
                {
                    continue;
                }

                if (!string.IsNullOrEmpty(keyword))
                {
                    bool match = barcode.ToLowerInvariant().Contains(keyword) ||
                                 nama.ToLowerInvariant().Contains(keyword) ||
                                 kategori.ToLowerInvariant().Contains(keyword);
                    if (!match) continue;
                }

                int rowIdx = gridProducts.Rows.Add(
                    barcode,
                    nama,
                    kategori,
                    "Rp " + harga.ToString("N0"),
                    stok.ToString() + " pcs"
                );

                gridProducts.Rows[rowIdx].Tag = p;

                if (stok <= 10)
                {
                    gridProducts.Rows[rowIdx].Cells["stok"].Style.ForeColor = Color.FromArgb(239, 68, 68);
                    gridProducts.Rows[rowIdx].Cells["stok"].Style.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
                }
            }
        }

        private void FilterProducts()
        {
            RenderProductGrid();
        }

        private void AddSelectedGridProductToCart(int rowIndex)
        {
            if (rowIndex >= 0 && rowIndex < gridProducts.Rows.Count)
            {
                var p = gridProducts.Rows[rowIndex].Tag as Dictionary<string, object>;
                if (p != null)
                {
                    AddProductToCart(p);
                }
            }
        }
        #endregion

        #region CART MANAGEMENT
        private void AddProductToCart(Dictionary<string, object> product)
        {
            int pId = Convert.ToInt32(product["id"]);
            CartItem existing = cart.Find(x => x.ProductId == pId);
            if (existing != null)
            {
                existing.Qty++;
            }
            else
            {
                cart.Add(new CartItem {
                    ProductId = pId,
                    Nama = product["nama_barang"].ToString(),
                    Harga = ParseDecimal(product["harga"]),
                    Qty = 1
                });
            }
            RefreshCartGrid();
        }

        private void AddProductByBarcode(string barcode)
        {
            if (string.IsNullOrEmpty(barcode)) return;
            var prod = productList.Find(x => x.ContainsKey("barcode") && x["barcode"].ToString() == barcode);
            if (prod != null)
            {
                AddProductToCart(prod);
            }
            else
            {
                MessageBox.Show("Barang dengan barcode " + barcode + " tidak ditemukan di katalog.", "Tidak Ditemukan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }

        private void RefreshCartGrid()
        {
            gridCart.Rows.Clear();
            decimal total = 0;
            foreach (var it in cart)
            {
                decimal subtotal = it.Harga * it.Qty;
                total += subtotal;
                gridCart.Rows.Add(it.Nama, "Rp " + it.Harga.ToString("N0"), it.Qty, "Rp " + subtotal.ToString("N0"));
            }
            lblTotal.Text = "TOTAL: Rp " + total.ToString("N0");
        }

        private void RecalculateCart()
        {
            decimal total = 0;
            for (int i = 0; i < gridCart.Rows.Count; i++)
            {
                if (i < cart.Count)
                {
                    int qty = 1;
                    int.TryParse(gridCart.Rows[i].Cells["qty"].Value.ToString(), out qty);
                    cart[i].Qty = Math.Max(1, qty);
                    decimal sub = cart[i].Harga * cart[i].Qty;
                    gridCart.Rows[i].Cells["subtotal"].Value = "Rp " + sub.ToString("N0");
                    total += sub;
                }
            }
            lblTotal.Text = "TOTAL: Rp " + total.ToString("N0");
        }
        #endregion

        #region CHECKOUT VIA FINGERPRINT
        private Fmd ParseFmdFromTemplate(string tXml)
        {
            if (string.IsNullOrEmpty(tXml)) return null;
            try
            {
                if (tXml.Contains("<Fid") || tXml.StartsWith("<Fid"))
                {
                    try
                    {
                        Fid fid = Fid.DeserializeXml(tXml);
                        var fe = FeatureExtraction.CreateFmdFromFid(fid, Constants.Formats.Fmd.ANSI);
                        if (fe.ResultCode == Constants.ResultCode.DP_SUCCESS) return fe.Data;
                    }
                    catch { }
                }
                else
                {
                    return Fmd.DeserializeXml(tXml);
                }
            }
            catch { }
            return null;
        }

        private void ProcessCheckoutFingerprint()
        {
            if (cart.Count == 0)
            {
                MessageBox.Show("Keranjang belanja masih kosong! Pilih barang terlebih dahulu.", "Keranjang Kosong", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            decimal totalAmount = 0;
            foreach (var it in cart) totalAmount += (it.Harga * it.Qty);

            // Open Touch Prompt Form
            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari WBP pada sensor sidik jari untuk otorisasi pembayaran Rp " + totalAmount.ToString("N0")))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    Fmd liveFmd = prompt.CapturedFmd;

                    // Match 1:N with enrolled inmates
                    Dictionary<string, object> matchedInmate = null;
                    int bestScore = int.MaxValue;
                    int threshold = 2147483647 / 100000;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
                            Fmd storedFmd = ParseFmdFromTemplate(wbp["template"].ToString());
                            if (storedFmd != null)
                            {
                                try
                                {
                                    CompareResult cr = Comparison.Compare(liveFmd, 0, storedFmd, 0);
                                    if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Score < threshold)
                                    {
                                        if (cr.Score < bestScore)
                                        {
                                            bestScore = cr.Score;
                                            matchedInmate = wbp;
                                        }
                                    }
                                }
                                catch { }
                            }
                        }
                    }

                    if (matchedInmate == null)
                    {
                        string defaultInmateName = (inmateList.Count > 0 && inmateList[0].ContainsKey("nama")) ? inmateList[0]["nama"].ToString() : "Budi Santoso bin Suhendar";
                        int defaultInmateId = (inmateList.Count > 0 && inmateList[0].ContainsKey("id")) ? Convert.ToInt32(inmateList[0]["id"]) : 1;

                        DialogResult dr = MessageBox.Show(
                            "⚠️ Sidik jari Anda belum terdaftar pada akun WBP mana pun!\n\n" +
                            "Sensor sidik jari berhasil membaca sidik jari Anda.\n" +
                            "Apakah Anda ingin mendaftarkan sidik jari ini ke akun:\n" +
                            "👉 " + defaultInmateName + " (ID: " + defaultInmateId + ")\n\n" +
                            "dan langsung menyelesaikan pembayaran sebesar Rp " + totalAmount.ToString("N0") + "?",
                            "Daftarkan Sidik Jari Sekarang?",
                            MessageBoxButtons.YesNo,
                            MessageBoxIcon.Question
                        );

                        if (dr == DialogResult.Yes)
                        {
                            try
                            {
                                string liveFmdXml = Fmd.SerializeXml(liveFmd);
                                string imgBase64 = "";
                                if (prompt.CapturedBitmap != null)
                                {
                                    using (MemoryStream ms = new MemoryStream())
                                    {
                                        prompt.CapturedBitmap.Save(ms, ImageFormat.Png);
                                        imgBase64 = Convert.ToBase64String(ms.ToArray());
                                    }
                                }

                                var savePayload = new Dictionary<string, object> {
                                    { "fingerprint_template", liveFmdXml },
                                    { "fingerprint_image", imgBase64 }
                                };

                                HttpPost(this.apiUrl + "/pos/inmate/" + defaultInmateId + "/save-fingerprint", serializer.Serialize(savePayload));

                                SyncDataFromApi();
                                matchedInmate = inmateList.Find(x => Convert.ToInt32(x["id"]) == defaultInmateId);
                                if (matchedInmate == null && inmateList.Count > 0) matchedInmate = inmateList[0];
                            }
                            catch (Exception ex)
                            {
                                MessageBox.Show("Gagal mendaftarkan sidik jari: " + ex.Message, "Error Pendaftaran", MessageBoxButtons.OK, MessageBoxIcon.Error);
                                return;
                            }
                        }
                        else
                        {
                            return;
                        }
                    }

                    SubmitTransaction(matchedInmate, "fingerprint", totalAmount);
                }
            }
        }

        private void ProcessCheckoutManual()
        {
            if (cart.Count == 0)
            {
                MessageBox.Show("Keranjang belanja masih kosong! Pilih barang terlebih dahulu.", "Keranjang Kosong", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            decimal totalAmount = 0;
            foreach (var it in cart) totalAmount += (it.Harga * it.Qty);

            using (SelectInmateForm selectForm = new SelectInmateForm(inmateList, totalAmount))
            {
                if (selectForm.ShowDialog(this) == DialogResult.OK && selectForm.SelectedInmate != null)
                {
                    SubmitTransaction(selectForm.SelectedInmate, "manual", totalAmount);
                }
            }
        }

        private void SubmitTransaction(Dictionary<string, object> matchedInmate, string paymentMethod, decimal totalAmount)
        {
            // Verify Balance & Daily Limit
            decimal saldo = Convert.ToDecimal(matchedInmate["saldo"]);
            decimal limit = Convert.ToDecimal(matchedInmate["remaining_limit"]);

            if (saldo < totalAmount)
            {
                MessageBox.Show("Saldo WBP " + matchedInmate["nama"] + " TIDAK MENCUKUPI!\n\nSisa Saldo: Rp " + saldo.ToString("N0") + "\nTotal Tagihan: Rp " + totalAmount.ToString("N0"), "Saldo Kurang", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            if (totalAmount > limit)
            {
                MessageBox.Show("Transaksi MELEBIHI LIMIT BELANJA HARIAN LAPAS!\n\nSisa Limit Hari Ini: Rp " + limit.ToString("N0") + "\nTotal Tagihan: Rp " + totalAmount.ToString("N0"), "Limit Terlampaui", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            var reqData = new Dictionary<string, object>();
            reqData["inmate_id"] = Convert.ToInt32(matchedInmate["id"]);
            reqData["payment_method"] = paymentMethod;
            reqData["cashier_name"] = "Kasir POS Desktop";

            var itemsArr = new List<Dictionary<string, object>>();
            foreach (var it in cart)
            {
                itemsArr.Add(new Dictionary<string, object> {
                    { "product_id", it.ProductId },
                    { "qty", it.Qty }
                });
            }
            reqData["items"] = itemsArr;

            string jsonPayload = serializer.Serialize(reqData);

            bool isOnlineSuccess = false;
            string invNo = "";
            decimal balanceAfter = saldo - totalAmount;

            try
            {
                string respJson = HttpPost(this.apiUrl + "/pos/checkout", jsonPayload);
                var resp = serializer.Deserialize<Dictionary<string, object>>(respJson);

                if (resp != null && Convert.ToBoolean(resp["success"]))
                {
                    isOnlineSuccess = true;
                    var data = (Dictionary<string, object>)resp["data"];
                    invNo = data["invoice_no"].ToString();
                    balanceAfter = Convert.ToDecimal(data["balance_after"]);
                }
                else
                {
                    string errMsg = (resp != null && resp.ContainsKey("message")) ? resp["message"].ToString() : "Transaksi ditolak server.";
                    MessageBox.Show(errMsg, "Gagal Checkout", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    return;
                }
            }
            catch (Exception ex)
            {
                // OFFLINE FALLBACK: Save to offline queue!
                invNo = "OFF-" + DateTime.Now.ToString("yyyyMMddHHmmss");
                QueueOfflineTransaction(reqData, invNo);
                isOnlineSuccess = false;
            }

            // Update local in-memory inmate balance and product stock
            matchedInmate["saldo"] = balanceAfter;
            matchedInmate["remaining_limit"] = limit - totalAmount;
            foreach (var cartIt in cart)
            {
                var prod = productList.Find(x => Convert.ToInt32(x["id"]) == cartIt.ProductId);
                if (prod != null && prod.ContainsKey("stok"))
                {
                    int s = Convert.ToInt32(prod["stok"]);
                    prod["stok"] = Math.Max(0, s - cartIt.Qty);
                }
            }

            // Create copy of cart for receipt before clearing
            List<CartItem> receiptCart = new List<CartItem>(cart);
            shiftTrxCount++;
            shiftTotalOmset += totalAmount;
            cart.Clear();
            RefreshCartGrid();
            RenderProductGrid();

            // Show Receipt Dialog
            using (ReceiptForm rForm = new ReceiptForm(invNo, matchedInmate, paymentMethod, receiptCart, totalAmount, saldo, balanceAfter, !isOnlineSuccess, this.receiptConfig))
            {
                rForm.ShowDialog(this);
            }

            if (isOnlineSuccess)
            {
                SyncDataFromApi();
            }
            else
            {
                int qCount = GetOfflineQueueCount();
                lblApiStatus.Text = "Server: Mode Offline (" + qCount + " antrean)";
                lblApiStatus.ForeColor = Color.FromArgb(251, 191, 36);
            }
        }
        #endregion

        #region LEVEL 3: RETAIL SHORTCUTS, SHIFT & HOLD BILL
        private void MainForm_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.KeyCode == Keys.F1)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                FocusSearch();
            }
            else if (e.KeyCode == Keys.F2)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                EditSelectedCartQty();
            }
            else if (e.KeyCode == Keys.F3)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                DeleteSelectedCartItem();
            }
            else if (e.KeyCode == Keys.F4)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                SwitchTab(0);
                ProcessCheckoutFingerprint();
            }
            else if (e.KeyCode == Keys.F5)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                HoldCurrentBill();
            }
            else if (e.KeyCode == Keys.F6)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                RecallBill();
            }
            else if (e.KeyCode == Keys.F7)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                ShowShiftRekap();
            }
            else if (e.KeyCode == Keys.F8)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                QuickCheckSaldoWbp();
            }
            else if (e.KeyCode == Keys.F9)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                ClearCartConfirm();
            }
            else if (e.KeyCode == Keys.F12)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                SwitchTab(0);
                ProcessCheckoutFingerprint();
            }
        }

        private void FocusSearch()
        {
            SwitchTab(0);
            if (txtSearchProduct != null)
            {
                txtSearchProduct.Focus();
                txtSearchProduct.SelectAll();
            }
        }

        private void EditSelectedCartQty()
        {
            SwitchTab(0);
            if (cart.Count == 0)
            {
                MessageBox.Show("Keranjang belanja masih kosong.", "Ubah Qty (F2)", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            int idx = (gridCart.CurrentRow != null && gridCart.CurrentRow.Index >= 0 && gridCart.CurrentRow.Index < cart.Count) 
                ? gridCart.CurrentRow.Index 
                : cart.Count - 1;

            var item = cart[idx];

            using (Form prompt = new Form())
            {
                prompt.Width = 340;
                prompt.Height = 190;
                prompt.FormBorderStyle = FormBorderStyle.FixedDialog;
                prompt.Text = "Ubah Jumlah: " + item.Nama;
                prompt.StartPosition = FormStartPosition.CenterParent;
                prompt.MaximizeBox = false;
                prompt.MinimizeBox = false;
                prompt.BackColor = Color.FromArgb(248, 250, 252);

                Label lblPrompt = new Label {
                    Left = 20, Top = 16, Width = 280,
                    Text = "Masukkan Jumlah (Qty) Baru:",
                    Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                    ForeColor = Color.FromArgb(15, 23, 42)
                };
                NumericUpDown num = new NumericUpDown {
                    Left = 20, Top = 46, Width = 280,
                    Minimum = 1, Maximum = 9999, Value = item.Qty,
                    Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                    TextAlign = HorizontalAlignment.Center
                };
                Button btnOk = new Button {
                    Text = "Simpan (Enter)",
                    Left = 160, Top = 96, Width = 140, Height = 36,
                    DialogResult = DialogResult.OK,
                    BackColor = Color.FromArgb(16, 185, 129),
                    ForeColor = Color.White,
                    FlatStyle = FlatStyle.Flat,
                    Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                    Cursor = Cursors.Hand
                };
                Button btnCancel = new Button {
                    Text = "Batal",
                    Left = 20, Top = 96, Width = 100, Height = 36,
                    DialogResult = DialogResult.Cancel,
                    BackColor = Color.FromArgb(241, 245, 249),
                    FlatStyle = FlatStyle.Flat,
                    Font = new Font("Segoe UI", 9.5f),
                    Cursor = Cursors.Hand
                };

                prompt.Controls.Add(lblPrompt);
                prompt.Controls.Add(num);
                prompt.Controls.Add(btnOk);
                prompt.Controls.Add(btnCancel);
                prompt.AcceptButton = btnOk;
                prompt.CancelButton = btnCancel;

                num.Select(0, num.Text.Length);

                if (prompt.ShowDialog(this) == DialogResult.OK)
                {
                    item.Qty = (int)num.Value;
                    RefreshCartGrid();
                }
            }
        }

        private void DeleteSelectedCartItem()
        {
            SwitchTab(0);
            if (cart.Count == 0) return;

            int idx = (gridCart.CurrentRow != null && gridCart.CurrentRow.Index >= 0 && gridCart.CurrentRow.Index < cart.Count) 
                ? gridCart.CurrentRow.Index 
                : cart.Count - 1;

            cart.RemoveAt(idx);
            RefreshCartGrid();
        }

        private void HoldCurrentBill()
        {
            SwitchTab(0);
            if (cart.Count == 0)
            {
                MessageBox.Show("Keranjang belanja masih kosong! Tidak ada transaksi yang perlu ditahan.", "Hold Bill (F5)", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            decimal total = 0;
            foreach (var it in cart) total += (it.Harga * it.Qty);

            HeldBill hb = new HeldBill {
                Id = "HOLD-" + (heldBills.Count + 1),
                Time = DateTime.Now,
                Items = new List<CartItem>(cart),
                TotalAmount = total
            };
            heldBills.Add(hb);

            cart.Clear();
            RefreshCartGrid();
            UpdateHoldStatusUI();

            MessageBox.Show(string.Format("Transaksi ({0} item, total Rp {1:N0}) berhasil DITAHAN [Antrean #{2}].\n\nTekan [F6] untuk memanggil kembali transaksi ini saat WBP siap bertransaksi.", hb.Items.Count, hb.TotalAmount, heldBills.Count), "Transaksi Ditahan (F5)", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }

        private void RecallBill()
        {
            SwitchTab(0);
            if (heldBills.Count == 0)
            {
                MessageBox.Show("Tidak ada antrean transaksi yang sedang ditahan.", "Recall Bill (F6)", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            if (cart.Count > 0)
            {
                DialogResult dr = MessageBox.Show("Keranjang saat ini masih terisi barang. Apakah Anda ingin menahan transaksi saat ini terlebih dahulu sebelum memanggil antrean?", "Konfirmasi Antrean", MessageBoxButtons.YesNoCancel, MessageBoxIcon.Question);
                if (dr == DialogResult.Yes)
                {
                    HoldCurrentBill();
                }
                else if (dr == DialogResult.Cancel)
                {
                    return;
                }
            }

            if (heldBills.Count == 1)
            {
                var hb = heldBills[0];
                heldBills.RemoveAt(0);
                cart = new List<CartItem>(hb.Items);
                RefreshCartGrid();
                UpdateHoldStatusUI();
                MessageBox.Show(string.Format("Transaksi Antrean {0} ({1} item, Rp {2:N0}) telah dimuat kembali ke keranjang belanja.", hb.Id, cart.Count, hb.TotalAmount), "Recall Berhasil", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            // Multiple held bills dialog
            using (Form picker = new Form())
            {
                picker.Text = "Pilih Antrean Transaksi yang Ditahan (F6 Recall)";
                picker.Size = new Size(520, 360);
                picker.StartPosition = FormStartPosition.CenterParent;
                picker.FormBorderStyle = FormBorderStyle.FixedDialog;
                picker.MaximizeBox = false;
                picker.MinimizeBox = false;
                picker.BackColor = Color.FromArgb(248, 250, 252);

                Label lblHeader = new Label {
                    Dock = DockStyle.Top, Height = 40,
                    Text = "Pilih transaksi antrean yang ingin dibuka kembali ke kasir:",
                    Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                    Padding = new Padding(12, 10, 12, 0)
                };

                ListBox lb = new ListBox {
                    Dock = DockStyle.Fill,
                    Font = new Font("Segoe UI", 10f),
                    ItemHeight = 28
                };
                foreach (var hb in heldBills)
                {
                    lb.Items.Add(string.Format("{0}  [Pukul {1:HH:mm}]  -  {2} Item  -  Rp {3:N0}", hb.Id, hb.Time, hb.Items.Count, hb.TotalAmount));
                }
                lb.SelectedIndex = 0;

                Panel pnl = new Panel {
                    Dock = DockStyle.Bottom, Height = 56,
                    Padding = new Padding(12),
                    BackColor = Color.FromArgb(241, 245, 249)
                };
                Button btnOk = new Button {
                    Text = "Buka Kembali (Enter)",
                    Dock = DockStyle.Right, Width = 180,
                    DialogResult = DialogResult.OK,
                    BackColor = Color.FromArgb(16, 185, 129),
                    ForeColor = Color.White,
                    FlatStyle = FlatStyle.Flat,
                    Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                    Cursor = Cursors.Hand
                };
                Button btnCancel = new Button {
                    Text = "Batal",
                    Dock = DockStyle.Left, Width = 100,
                    DialogResult = DialogResult.Cancel,
                    BackColor = Color.FromArgb(203, 213, 225),
                    FlatStyle = FlatStyle.Flat,
                    Cursor = Cursors.Hand
                };
                pnl.Controls.Add(btnOk);
                pnl.Controls.Add(btnCancel);

                picker.Controls.Add(lb);
                picker.Controls.Add(lblHeader);
                picker.Controls.Add(pnl);
                picker.AcceptButton = btnOk;
                picker.CancelButton = btnCancel;

                if (picker.ShowDialog(this) == DialogResult.OK && lb.SelectedIndex >= 0)
                {
                    var hb = heldBills[lb.SelectedIndex];
                    heldBills.RemoveAt(lb.SelectedIndex);
                    cart = new List<CartItem>(hb.Items);
                    RefreshCartGrid();
                    UpdateHoldStatusUI();
                }
            }
        }

        private void UpdateHoldStatusUI()
        {
            if (lblHoldStatus != null)
            {
                if (heldBills.Count > 0)
                {
                    lblHoldStatus.Text = string.Format("⚠️ {0} Transaksi Ditahan [Klik / Tekan F6 untuk Recall]", heldBills.Count);
                    lblHoldStatus.Visible = true;
                }
                else
                {
                    lblHoldStatus.Visible = false;
                }
            }
        }

        private void ClearCartConfirm()
        {
            SwitchTab(0);
            if (cart.Count == 0) return;

            if (MessageBox.Show("Apakah Anda yakin ingin mengosongkan keranjang belanja?", "Kosongkan Keranjang (F9)", MessageBoxButtons.YesNo, MessageBoxIcon.Question) == DialogResult.Yes)
            {
                cart.Clear();
                RefreshCartGrid();
            }
        }

        private void ShowShiftRekap()
        {
            using (ShiftRekapForm f = new ShiftRekapForm(this.cashierName, this.shiftStartTime, this.shiftModalAwal, this.shiftTrxCount, this.shiftTotalOmset))
            {
                if (f.ShowDialog(this) == DialogResult.OK)
                {
                    this.shiftStartTime = DateTime.Now;
                    this.shiftTrxCount = 0;
                    this.shiftTotalOmset = 0;
                    MessageBox.Show("Shift kasir telah ditutup dan direset untuk petugas kasir berikutnya.", "Shift Ditutup", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
            }
        }

        private void QuickCheckSaldoWbp()
        {
            using (QuickCheckSaldoForm f = new QuickCheckSaldoForm(currentReader, inmateList, this.apiUrl))
            {
                f.ShowDialog(this);
            }
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
            currentEnrollStep = 0;

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

                    // Mark as enrolled locally
                    selectedEnrollInmate["has_fingerprint"] = true;
                    selectedEnrollInmate["template"] = masterXml;

                    MessageBox.Show("Pendaftaran sidik jari WBP " + inmateNama + " berhasil disimpan ke server pusat!\n\nWBP ini kini dapat langsung berbelanja di kasir POS.", "Perekaman Berhasil", MessageBoxButtons.OK, MessageBoxIcon.Information);

                    // Refresh list - if onlyUnenrolled is active, this person disappears and next person is selected!
                    FilterEnrollInmates();
                }
                else
                {
                    lblEnrollStep.Text = "❌ Gagal membuat master template: " + masterRes.ResultCode;
                    lblEnrollStep.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
            catch (Exception ex)
            {
                lblEnrollStep.Text = "Error: " + ex.Message;
                lblEnrollStep.ForeColor = Color.FromArgb(239, 68, 68);
            }
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
                    int threshold = 2147483647 / 100000;
                    int bestScore = int.MaxValue;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
                            Fmd storedFmd = ParseFmdFromTemplate(wbp["template"].ToString());
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
                        DisplayKioskInmate(matched, true);
                        FetchFreshInmateBalance(matched);
                    }
                    else
                    {
                        MessageBox.Show("Sidik jari belum terdaftar pada sistem.\nSilakan daftarkan sidik jari WBP pada menu 'Rekam Jari' terlebih dahulu, atau gunakan pencarian Nomor Registrasi di atas.", "Sidik Jari Tidak Dikenal", MessageBoxButtons.OK, MessageBoxIcon.Warning);
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

        #region HTTP HELPERS
        private string HttpGet(string url)
        {
            HttpWebRequest req = (HttpWebRequest)WebRequest.Create(url);
            req.Method = "GET";
            req.Timeout = 8000;
            using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
            using (StreamReader r = new StreamReader(res.GetResponseStream()))
            {
                return r.ReadToEnd();
            }
        }

        private string HttpPost(string url, string json)
        {
            HttpWebRequest req = (HttpWebRequest)WebRequest.Create(url);
            req.Method = "POST";
            req.ContentType = "application/json";
            req.Timeout = 10000;
            byte[] bytes = Encoding.UTF8.GetBytes(json);
            req.ContentLength = bytes.Length;
            using (Stream os = req.GetRequestStream())
            {
                os.Write(bytes, 0, bytes.Length);
            }
            using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
            using (StreamReader r = new StreamReader(res.GetResponseStream()))
            {
                return r.ReadToEnd();
            }
        }
        #endregion
    }

    // Modal Form for Fingerprint Touch Prompt
    public class FingerprintPromptForm : Form
    {
        private Reader reader;
        private string promptText;
        private Label lblStatusText;
        private PictureBox picPreview;
        public Fmd CapturedFmd { get; private set; }
        public Bitmap CapturedBitmap { get; private set; }

        public FingerprintPromptForm(Reader r, string prompt)
        {
            this.reader = r;
            this.promptText = prompt;

            this.Text = "Sentuh Sensor Sidik Jari";
            this.Size = new Size(480, 380);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(15, 23, 42);

            Label lbl = new Label {
                Text = prompt,
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                Location = new Point(24, 20),
                Width = 420,
                Height = 50,
                TextAlign = ContentAlignment.TopCenter
            };
            this.Controls.Add(lbl);

            picPreview = new PictureBox {
                Location = new Point(190, 75),
                Width = 100,
                Height = 130,
                BackColor = Color.FromArgb(30, 41, 59),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            this.Controls.Add(picPreview);

            lblStatusText = new Label {
                Text = "🔴 Sensor Siaga: Tempelkan jari Anda...",
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Location = new Point(24, 220),
                Width = 420,
                Height = 32,
                TextAlign = ContentAlignment.MiddleCenter
            };
            this.Controls.Add(lblStatusText);

            Button btnCancel = new Button {
                Text = "Batal",
                Location = new Point(190, 270),
                Width = 100,
                Height = 38,
                BackColor = Color.FromArgb(51, 65, 85),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold)
            };
            btnCancel.Click += (s, e) => {
                this.DialogResult = DialogResult.Cancel;
                this.Close();
            };
            this.Controls.Add(btnCancel);

            this.Load += (s, e) => StartCapture();
            this.FormClosing += (s, e) => StopCapture();
        }

        private void StartCapture()
        {
            try
            {
                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers == null || readers.Count == 0)
                {
                    lblStatusText.Text = "⚠️ Reader belum terdeteksi!";
                    lblStatusText.ForeColor = Color.FromArgb(239, 68, 68);
                    return;
                }

                reader = readers[0];
                Constants.ResultCode rcOpen = Constants.ResultCode.DP_DEVICE_BUSY;
                for (int attempt = 0; attempt < 5; attempt++)
                {
                    rcOpen = reader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                    if (rcOpen == Constants.ResultCode.DP_SUCCESS) break;
                    System.Threading.Thread.Sleep(150);
                }

                if (rcOpen != Constants.ResultCode.DP_SUCCESS)
                {
                    lblStatusText.Text = "Scanner sibuk (" + rcOpen + "). Tutup aplikasi '01_Uji_Sensor'!";
                    lblStatusText.ForeColor = Color.FromArgb(239, 68, 68);
                    return;
                }

                reader.On_Captured += Reader_OnCaptured;
                reader.GetStatus();

                int res = (reader.Capabilities.Resolutions != null && reader.Capabilities.Resolutions.Length > 0)
                    ? reader.Capabilities.Resolutions[0] : 500;
                
                Constants.ResultCode rc = reader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, res);
                if (rc == Constants.ResultCode.DP_SUCCESS)
                {
                    lblStatusText.Text = "🔴 Sensor Siaga: Tempelkan jari Anda...";
                    lblStatusText.ForeColor = Color.FromArgb(56, 189, 248);
                }
                else
                {
                    lblStatusText.Text = "Status Capture: " + rc;
                }
            }
            catch (Exception ex)
            {
                lblStatusText.Text = "Error: " + ex.Message;
            }
        }

        private void StopCapture()
        {
            if (reader != null)
            {
                try
                {
                    reader.CancelCapture();
                    reader.On_Captured -= Reader_OnCaptured;
                    reader.Dispose();
                }
                catch { }
                reader = null;
            }
        }

        private void Reader_OnCaptured(CaptureResult cr)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action<CaptureResult>(Reader_OnCaptured), cr);
                return;
            }

            if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Data != null)
            {
                if (cr.Data.Views != null && cr.Data.Views.Count > 0)
                {
                    var view = cr.Data.Views[0];
                    this.CapturedBitmap = MainForm.Create24bppBitmapStatic(view.RawImage, view.Width, view.Height);
                    picPreview.Image = this.CapturedBitmap;
                }

                DataResult<Fmd> res = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                if (res.ResultCode == Constants.ResultCode.DP_SUCCESS && res.Data != null)
                {
                    this.CapturedFmd = res.Data;
                    System.Media.SystemSounds.Beep.Play();
                    lblStatusText.Text = "🟢 Sidik Jari Terverifikasi!";
                    lblStatusText.ForeColor = Color.FromArgb(52, 211, 153);
                    Application.DoEvents();
                    System.Threading.Thread.Sleep(200);
                    this.DialogResult = DialogResult.OK;
                    this.Close();
                }
                else
                {
                    lblStatusText.Text = "Kualitas kurang jelas. Tempelkan jari lagi...";
                    lblStatusText.ForeColor = Color.FromArgb(248, 113, 113);
                    ReCapture();
                }
            }
            else
            {
                lblStatusText.Text = "Kualitas: " + cr.Quality + ". Silakan tempelkan jari kembali.";
                lblStatusText.ForeColor = Color.FromArgb(248, 113, 113);
                ReCapture();
            }
        }

        private void ReCapture()
        {
            if (reader == null) return;
            try
            {
                int res = (reader.Capabilities.Resolutions != null && reader.Capabilities.Resolutions.Length > 0)
                    ? reader.Capabilities.Resolutions[0] : 500;
                reader.CaptureAsync(Constants.Formats.Fid.ANSI, Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT, res);
            }
            catch { }
        }
    }

    public class CartItem
    {
        public int ProductId { get; set; }
        public string Nama { get; set; }
        public decimal Harga { get; set; }
        public int Qty { get; set; }
    }

    public class SelectInmateForm : Form
    {
        public Dictionary<string, object> SelectedInmate { get; private set; }
        private List<Dictionary<string, object>> allInmates;
        private DataGridView grid;
        private TextBox txtSearch;
        private decimal totalAmount;

        public SelectInmateForm(List<Dictionary<string, object>> inmates, decimal total)
        {
            this.allInmates = inmates;
            this.totalAmount = total;

            this.Text = "Pilih Warga Binaan (WBP) - Pembayaran Manual";
            this.Size = new Size(680, 520);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);

            Panel top = new Panel { Dock = DockStyle.Top, Height = 95, Padding = new Padding(16, 12, 16, 8), BackColor = Color.FromArgb(15, 23, 42) };
            
            Label lblH = new Label { 
                Text = "Otorisasi Manual: Tagihan Rp " + total.ToString("N0"), 
                ForeColor = Color.White, 
                Font = new Font("Segoe UI", 12f, FontStyle.Bold), 
                Dock = DockStyle.Top, 
                Height = 28 
            };
            top.Controls.Add(lblH);

            txtSearch = new TextBox { 
                Dock = DockStyle.Bottom, 
                Font = new Font("Segoe UI", 11f), 
                Height = 32 
            };
            txtSearch.TextChanged += (s, e) => FilterGrid();
            top.Controls.Add(txtSearch);

            Label lblSub = new Label { 
                Text = "Ketik Nama, NIK, atau No. Registrasi WBP untuk mencari:", 
                ForeColor = Color.FromArgb(148, 163, 184), 
                Font = new Font("Segoe UI", 8.5f), 
                Dock = DockStyle.Top, 
                Height = 20 
            };
            top.Controls.Add(lblSub);

            this.Controls.Add(top);

            grid = new DataGridView {
                Dock = DockStyle.Fill,
                BackgroundColor = Color.White,
                BorderStyle = BorderStyle.None,
                CellBorderStyle = DataGridViewCellBorderStyle.SingleHorizontal,
                GridColor = Color.FromArgb(241, 245, 249),
                RowHeadersVisible = false,
                AllowUserToAddRows = false,
                ReadOnly = true,
                SelectionMode = DataGridViewSelectionMode.FullRowSelect,
                MultiSelect = false,
                AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill,
                EnableHeadersVisualStyles = false,
                Font = new Font("Segoe UI", 9.5f)
            };
            grid.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            grid.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            grid.ColumnHeadersHeight = 36;
            grid.RowTemplate.Height = 36;

            grid.Columns.Add("no_reg", "No. Registrasi");
            grid.Columns.Add("nama", "Nama WBP");
            grid.Columns.Add("kamar", "Kamar / Blok");
            grid.Columns.Add("saldo", "Sisa Saldo");
            grid.Columns["saldo"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            grid.Columns["saldo"].DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            grid.Columns["saldo"].DefaultCellStyle.ForeColor = Color.FromArgb(16, 185, 129);

            grid.DoubleClick += (s, e) => ConfirmSelection();

            Panel bottom = new Panel { Dock = DockStyle.Bottom, Height = 56, Padding = new Padding(16, 8, 16, 10), BackColor = Color.FromArgb(241, 245, 249) };
            Button btnCancel = new Button { Text = "Batal", Dock = DockStyle.Left, Width = 100, BackColor = Color.FromArgb(203, 213, 225), FlatStyle = FlatStyle.Flat, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold), Cursor = Cursors.Hand };
            btnCancel.Click += (s, e) => { this.DialogResult = DialogResult.Cancel; this.Close(); };
            bottom.Controls.Add(btnCancel);

            Button btnSelect = new Button { Text = "✅ Pilih & Lanjutkan Pembayaran", Dock = DockStyle.Right, Width = 250, BackColor = Color.FromArgb(2, 132, 199), ForeColor = Color.White, FlatStyle = FlatStyle.Flat, Font = new Font("Segoe UI", 10f, FontStyle.Bold), Cursor = Cursors.Hand };
            btnSelect.Click += (s, e) => ConfirmSelection();
            bottom.Controls.Add(btnSelect);

            this.Controls.Add(grid);
            this.Controls.Add(bottom);
            grid.BringToFront();

            PopulateGrid("");
        }

        private void FilterGrid()
        {
            PopulateGrid(txtSearch.Text.Trim().ToLowerInvariant());
        }

        private void PopulateGrid(string query)
        {
            grid.Rows.Clear();
            foreach (var wbp in allInmates)
            {
                string nama = wbp.ContainsKey("nama") && wbp["nama"] != null ? wbp["nama"].ToString() : "";
                string noReg = wbp.ContainsKey("no_registrasi") && wbp["no_registrasi"] != null ? wbp["no_registrasi"].ToString() : "";
                string kamar = wbp.ContainsKey("blok_kamar") && wbp["blok_kamar"] != null ? wbp["blok_kamar"].ToString() : "";
                decimal saldo = 0;
                if (wbp.ContainsKey("saldo")) decimal.TryParse(wbp["saldo"].ToString(), out saldo);

                if (!string.IsNullOrEmpty(query))
                {
                    bool match = nama.ToLowerInvariant().Contains(query) ||
                                 noReg.ToLowerInvariant().Contains(query) ||
                                 kamar.ToLowerInvariant().Contains(query);
                    if (!match) continue;
                }

                int rowIdx = grid.Rows.Add(noReg, nama, kamar, "Rp " + saldo.ToString("N0"));
                grid.Rows[rowIdx].Tag = wbp;

                if (saldo < totalAmount)
                {
                    grid.Rows[rowIdx].Cells["saldo"].Style.ForeColor = Color.FromArgb(239, 68, 68);
                }
            }
        }

        private void ConfirmSelection()
        {
            if (grid.SelectedRows.Count > 0)
            {
                this.SelectedInmate = (Dictionary<string, object>)grid.SelectedRows[0].Tag;
                this.DialogResult = DialogResult.OK;
                this.Close();
            }
            else
            {
                MessageBox.Show("Pilih Warga Binaan terlebih dahulu.", "Pilih WBP", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }
    }

    public class ReceiptConfig
    {
        public string StoreName { get; set; }
        public string Address { get; set; }
        public string Phone { get; set; }
        public string Footer { get; set; }
        public string PaperWidth { get; set; }
        public bool AutoPrint { get; set; }
        public bool ShowBalance { get; set; }

        public ReceiptConfig()
        {
            StoreName = "KOPERASI PENGAYOMAN LAPAS";
            Address = "Jalan Pemasyarakatan No. 1";
            Phone = "(021) 5523456";
            Footer = "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Belanja Sah\nUangPas - Zero Cash Solution";
            PaperWidth = "58mm";
            AutoPrint = false;
            ShowBalance = true;
        }
    }

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
                System.Drawing.Printing.PrintDocument pDoc = new System.Drawing.Printing.PrintDocument();
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

    public class HeldBill
    {
        public string Id { get; set; }
        public DateTime Time { get; set; }
        public List<CartItem> Items { get; set; }
        public decimal TotalAmount { get; set; }
    }

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
                    System.Drawing.Printing.PrintDocument pDoc = new System.Drawing.Printing.PrintDocument();
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
    
    public class InmateEnrollItem
    {
        public Dictionary<string, object> Inmate { get; set; }
        public override string ToString()
        {
            if (Inmate == null) return "";
            string reg = Inmate.ContainsKey("no_registrasi") && Inmate["no_registrasi"] != null ? Inmate["no_registrasi"].ToString() : "-";
            string nama = Inmate.ContainsKey("nama") && Inmate["nama"] != null ? Inmate["nama"].ToString() : "-";
            string kamar = Inmate.ContainsKey("blok_kamar") && Inmate["blok_kamar"] != null ? Inmate["blok_kamar"].ToString() : "-";
            bool hasFp = Inmate.ContainsKey("has_fingerprint") && Convert.ToBoolean(Inmate["has_fingerprint"]);
            string badge = hasFp ? "[✓ Terdaftar]" : "[⚪ BELUM REKAM]";
            return badge + "  " + reg + " - " + nama + " (" + kamar + ")";
        }
    }

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
                Height = 85,
                Padding = new Padding(18, 14, 18, 10),
                BackColor = Color.FromArgb(15, 23, 42)
            };

            Label lblTitle = new Label {
                Text = "Scan Barcode Kartu / No Registrasi / Nama WBP:",
                ForeColor = Color.FromArgb(203, 213, 225),
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Dock = DockStyle.Top,
                Height = 22
            };
            top.Controls.Add(lblTitle);

            txtSearch = new TextBox {
                Dock = DockStyle.Bottom,
                Height = 32,
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
            top.Controls.Add(txtSearch);
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
            else
            {
                MessageBox.Show("Warga Binaan dengan kata kunci '" + query + "' tidak ditemukan di database Lapas.", "Pencarian WBP", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
        }
    }
}

using System;
using System.IO;
using System.Net;
using System.Text;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
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
        private Button btnCheckoutFingerprint;

        // UI Controls - Enrollment
        private ComboBox cmbInmateEnroll;
        private Label lblEnrollStep;
        private PictureBox picEnrollPreview;
        private Button btnStartEnroll;
        private List<Fmd> enrollFmdList = new List<Fmd>();
        private int currentEnrollStep = 0;

        // UI Controls - Cek Saldo Kiosk
        private Label lblKioskNama;
        private Label lblKioskNoReg;
        private Label lblKioskKamar;
        private Label lblKioskSaldo;
        private Label lblKioskLimit;
        private PictureBox picKioskFinger;
        private Button btnStartKioskScan;

        // UI Controls - Navigation Header
        private Button btnTopKasir;
        private Button btnTopEnroll;
        private Button btnTopKiosk;
        private Button btnTopSettings;
        private Button btnTopUji;
        private Timer timerScannerPoll;

        // UI Settings
        private TextBox txtSettingApiUrl;
        private TabControl tabs;

        public MainForm()
        {
            this.Text = "UangPas Lapas - Terminal Kasir POS & Biometrik U.are.U 4500";
            this.Size = new Size(1280, 800);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.WindowState = FormWindowState.Maximized;
            this.MinimumSize = new Size(960, 580);
            this.AutoScaleMode = AutoScaleMode.Dpi;
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            this.BackColor = Color.FromArgb(248, 250, 252);

            InitializeUI();
            this.Shown += (s, e) => {
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
                Width = 440,
                BackColor = Color.Transparent
            };

            Button btnRefreshScanner = new Button {
                Text = "🔄 Refresh Finger",
                Width = 145,
                Height = 36,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Location = new Point(10, 14)
            };
            btnRefreshScanner.FlatAppearance.BorderColor = Color.FromArgb(71, 85, 105);
            btnRefreshScanner.Click += (s, e) => {
                btnRefreshScanner.Text = "⏳ Mendeteksi...";
                btnRefreshScanner.Enabled = false;
                InitializeBiometrics();
                Timer t = new Timer { Interval = 600 };
                t.Tick += (ts, te) => {
                    btnRefreshScanner.Text = "🔄 Refresh Finger";
                    btnRefreshScanner.Enabled = true;
                    t.Stop();
                    t.Dispose();
                };
                t.Start();
            };
            topStatusRight.Controls.Add(btnRefreshScanner);

            lblScannerStatus = new Label {
                Text = "Scanner: Memeriksa...",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(165, 14),
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
                Location = new Point(165, 36)
            };
            topStatusRight.Controls.Add(lblApiStatus);
            topBar.Controls.Add(topStatusRight);

            // Left Flow for Brand + Navigation
            FlowLayoutPanel topFlowLeft = new FlowLayoutPanel {
                Dock = DockStyle.Fill,
                FlowDirection = FlowDirection.LeftToRight,
                WrapContents = false,
                BackColor = Color.Transparent,
                Padding = new Padding(4, 14, 4, 8),
                AutoScroll = false
            };

            Label lblBrand = new Label {
                Text = "👆 UangPas Lapas",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                AutoSize = true,
                Margin = new Padding(6, 4, 16, 0)
            };
            topFlowLeft.Controls.Add(lblBrand);

            // Fast Switch Buttons in Header
            btnTopKasir = new Button {
                Text = "🛒 Kasir POS",
                Width = 110,
                Height = 34,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(3, 0, 4, 0)
            };
            btnTopKasir.Click += (s, e) => SwitchTab(0);
            topFlowLeft.Controls.Add(btnTopKasir);

            btnTopEnroll = new Button {
                Text = "👆 Rekam Sidik Jari WBP",
                Width = 175,
                Height = 34,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(3, 0, 4, 0)
            };
            btnTopEnroll.Click += (s, e) => SwitchTab(1);
            topFlowLeft.Controls.Add(btnTopEnroll);

            btnTopKiosk = new Button {
                Text = "🔍 Cek Saldo WBP",
                Width = 140,
                Height = 34,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(3, 0, 4, 0)
            };
            btnTopKiosk.Click += (s, e) => SwitchTab(2);
            topFlowLeft.Controls.Add(btnTopKiosk);

            btnTopSettings = new Button {
                Text = "⚙️ Pengaturan",
                Width = 125,
                Height = 34,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(3, 0, 4, 0)
            };
            btnTopSettings.Click += (s, e) => SwitchTab(3);
            topFlowLeft.Controls.Add(btnTopSettings);

            btnTopUji = new Button {
                Text = "🔬 Uji Alat U.are.U",
                Width = 140,
                Height = 34,
                BackColor = Color.FromArgb(30, 41, 59),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand,
                Margin = new Padding(3, 0, 4, 0)
            };
            btnTopUji.Click += (s, e) => SwitchTab(4);
            topFlowLeft.Controls.Add(btnTopUji);

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

            // TAB 4: PENGATURAN SERVER
            TabPage tabSettings = new TabPage("⚙️ Pengaturan Server");
            tabSettings.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildSettingsTab(tabSettings);
            tabs.TabPages.Add(tabSettings);

            // TAB 5: UJI HARDWARE SCANNER U.ARE.U
            TabPage tabDiagnostic = new TabPage("🔬 Uji Alat U.are.U");
            tabDiagnostic.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            BuildDiagnosticTab(tabDiagnostic);
            tabs.TabPages.Add(tabDiagnostic);

            tabs.SelectedIndexChanged += (s, e) => {
                UpdateNavButtonStyles(tabs.SelectedIndex);
                if (tabs.SelectedIndex != 4) StopDiagCapture();
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
            }
        }

        private void UpdateNavButtonStyles(int selectedIndex)
        {
            Color dark = Color.FromArgb(30, 41, 59);
            if (btnTopKasir != null) btnTopKasir.BackColor = (selectedIndex == 0) ? Color.FromArgb(16, 185, 129) : dark;
            if (btnTopEnroll != null) btnTopEnroll.BackColor = (selectedIndex == 1) ? Color.FromArgb(99, 102, 241) : dark;
            if (btnTopKiosk != null) btnTopKiosk.BackColor = (selectedIndex == 2) ? Color.FromArgb(14, 165, 233) : dark;
            if (btnTopSettings != null) btnTopSettings.BackColor = (selectedIndex == 3) ? Color.FromArgb(245, 158, 11) : dark;
            if (btnTopUji != null) btnTopUji.BackColor = (selectedIndex == 4) ? Color.FromArgb(139, 92, 246) : dark;
        }

        #region TAB 1: KASIR POS
        private void BuildKasirTab(TabPage page)
        {
            SplitContainer split = new SplitContainer {
                Dock = DockStyle.Fill,
                Orientation = Orientation.Vertical,
                IsSplitterFixed = false
            };

            // Handle proportional splitter on resize
            this.Resize += (s, e) => {
                try {
                    if (split != null && this.ClientSize.Width > 500)
                    {
                        int targetDist = (int)(this.ClientSize.Width * 0.58);
                        if (targetDist > 300 && targetDist < this.ClientSize.Width - 250)
                            split.SplitterDistance = targetDist;
                    }
                } catch { }
            };

            // LEFT PANEL: Product Grid & Search
            Panel leftPanel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(16, 20, 16, 16) };
            
            TableLayoutPanel searchTable = new TableLayoutPanel {
                Dock = DockStyle.Top,
                Height = 44,
                RowCount = 1,
                ColumnCount = 3,
                Padding = new Padding(0, 0, 0, 6)
            };
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 155f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 155f));

            Label lblSearch = new Label { 
                Text = "Cari Barang / Barcode:", 
                Dock = DockStyle.Fill, 
                TextAlign = ContentAlignment.MiddleLeft, 
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) 
            };
            txtSearchProduct = new TextBox { 
                Dock = DockStyle.Fill, 
                Font = new Font("Segoe UI", 10.5f) 
            };
            txtSearchProduct.TextChanged += (s, e) => FilterProducts();

            Button btnRefresh = new Button {
                Text = "🔄 Sinkron Database",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnRefresh.Click += (s, e) => { SyncDataFromApi(); };

            searchTable.Controls.Add(lblSearch, 0, 0);
            searchTable.Controls.Add(txtSearchProduct, 1, 0);
            searchTable.Controls.Add(btnRefresh, 2, 0);

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
            gridProducts.Columns["barcode"].Width = 130;
            gridProducts.Columns["barcode"].DefaultCellStyle.Font = new Font("Consolas", 9.5f, FontStyle.Bold);

            gridProducts.Columns.Add("nama", "Nama Barang");
            gridProducts.Columns["nama"].FillWeight = 160;

            gridProducts.Columns.Add("kategori", "Kategori");
            gridProducts.Columns["kategori"].Width = 130;

            gridProducts.Columns.Add("harga", "Harga Satuan");
            gridProducts.Columns["harga"].Width = 110;
            gridProducts.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridProducts.Columns["harga"].DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            gridProducts.Columns["harga"].DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);

            gridProducts.Columns.Add("stok", "Sisa Stok");
            gridProducts.Columns["stok"].Width = 85;
            gridProducts.Columns["stok"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;

            DataGridViewButtonColumn colBtn = new DataGridViewButtonColumn {
                Name = "aksi",
                HeaderText = "Aksi",
                Text = "➕ Tambah",
                UseColumnTextForButtonValue = true,
                Width = 90,
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

            split.Panel1.Controls.Add(leftPanel);

            // RIGHT PANEL: Cart & Fingerprint Checkout
            Panel rightPanel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(16, 20, 16, 16), BackColor = Color.White };
            
            Label lblCartTitle = new Label {
                Text = "📋 Keranjang Belanja Koperasi",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                Dock = DockStyle.Top,
                Height = 36
            };
            rightPanel.Controls.Add(lblCartTitle);

            // Barcode input box (Responsive Table)
            TableLayoutPanel barcodeTable = new TableLayoutPanel {
                Dock = DockStyle.Top,
                Height = 40,
                RowCount = 1,
                ColumnCount = 2,
                Padding = new Padding(0, 0, 0, 6)
            };
            barcodeTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 105f));
            barcodeTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));

            Label lblBc = new Label { 
                Text = "Scan Barcode:", 
                Dock = DockStyle.Fill, 
                TextAlign = ContentAlignment.MiddleLeft, 
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) 
            };
            txtBarcode = new TextBox { 
                Dock = DockStyle.Fill, 
                Font = new Font("Segoe UI", 10.5f) 
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
            gridCart.ColumnHeadersHeight = 38;
            gridCart.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            gridCart.ColumnHeadersDefaultCellStyle.ForeColor = Color.FromArgb(30, 41, 59);
            gridCart.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            gridCart.RowTemplate.Height = 36;
            gridCart.RowsDefaultCellStyle.Font = new Font("Segoe UI", 9.5f);
            gridCart.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(248, 250, 252);
            gridCart.Columns.Add("nama", "Barang");
            gridCart.Columns.Add("harga", "Harga");
            gridCart.Columns.Add("qty", "Qty");
            gridCart.Columns.Add("subtotal", "Subtotal");
            gridCart.Columns["harga"].Width = 90;
            gridCart.Columns["qty"].Width = 60;
            gridCart.Columns["subtotal"].Width = 100;
            gridCart.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["qty"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns["subtotal"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["subtotal"].DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            gridCart.Columns["subtotal"].DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);
            gridCart.CellValueChanged += (s, e) => RecalculateCart();
            rightPanel.Controls.Add(gridCart);

            // Bottom Summary & Checkout Button (Fully Responsive Grid)
            Panel bottomPanel = new Panel { 
                Dock = DockStyle.Bottom, 
                Height = 150, 
                BackColor = Color.FromArgb(248, 250, 252), 
                Padding = new Padding(12) 
            };
            
            lblTotal = new Label {
                Text = "TOTAL: Rp 0",
                Font = new Font("Segoe UI", 18f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Dock = DockStyle.Top,
                Height = 44,
                TextAlign = ContentAlignment.MiddleRight
            };
            bottomPanel.Controls.Add(lblTotal);

            TableLayoutPanel btnTable = new TableLayoutPanel {
                Dock = DockStyle.Bottom,
                Height = 72,
                RowCount = 1,
                ColumnCount = 2,
                Padding = new Padding(0, 6, 0, 0)
            };
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 110f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));
            
            Button btnClear = new Button {
                Text = "Batal",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(239, 68, 68),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnClear.Click += (s, e) => { cart.Clear(); RefreshCartGrid(); };
            btnTable.Controls.Add(btnClear, 0, 0);

            btnCheckoutFingerprint = new Button {
                Text = "👆 BAYAR DENGAN SIDIK JARI WBP",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 11.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCheckoutFingerprint.Click += (s, e) => ProcessCheckoutFingerprint();
            btnTable.Controls.Add(btnCheckoutFingerprint, 1, 0);

            bottomPanel.Controls.Add(btnTable);
            rightPanel.Controls.Add(bottomPanel);

            gridCart.BringToFront();
            split.Panel2.Controls.Add(rightPanel);

            page.Controls.Add(split);
        }
        #endregion

        #region TAB 2: ENROLLMENT
        private void BuildEnrollmentTab(TabPage page)
        {
            Panel panel = new Panel { Dock = DockStyle.Fill, Padding = new Padding(32), AutoScroll = true };

            Label lblTitle = new Label {
                Text = "Pendaftaran Sidik Jari Warga Binaan Pemasyarakatan",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(32, 24)
            };
            panel.Controls.Add(lblTitle);

            Label lblSub = new Label {
                Text = "Pilih WBP yang belum memiliki rekam biometrik, lalu minta WBP menempelkan jari 4x pada sensor U.are.U 4500.",
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true,
                Location = new Point(32, 56)
            };
            panel.Controls.Add(lblSub);

            Label lblSelect = new Label { Text = "Pilih Warga Binaan:", Location = new Point(32, 100), AutoSize = true, Font = new Font("Segoe UI", 10f, FontStyle.Bold) };
            panel.Controls.Add(lblSelect);

            cmbInmateEnroll = new ComboBox {
                Location = new Point(32, 128),
                Width = 480,
                DropDownStyle = ComboBoxStyle.DropDownList,
                Font = new Font("Segoe UI", 10.5f)
            };
            panel.Controls.Add(cmbInmateEnroll);

            // Preview Box
            picEnrollPreview = new PictureBox {
                Location = new Point(32, 180),
                Width = 200,
                Height = 250,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            panel.Controls.Add(picEnrollPreview);

            lblEnrollStep = new Label {
                Text = "Status: Siap memulai pendaftaran.",
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Location = new Point(260, 200),
                Width = 500,
                Height = 80
            };
            panel.Controls.Add(lblEnrollStep);

            btnStartEnroll = new Button {
                Text = "▶️ Mulai Perekaman 4x Sentuh Jari",
                Location = new Point(260, 290),
                Width = 300,
                Height = 48,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 11f, FontStyle.Bold)
            };
            btnStartEnroll.Click += (s, e) => StartEnrollmentSequence();
            panel.Controls.Add(btnStartEnroll);

            page.Controls.Add(panel);
        }
        #endregion

        #region TAB 3: KIOSK CEK SALDO MANDIRI
        private void BuildKioskTab(TabPage page)
        {
            Panel p = new Panel { Dock = DockStyle.Fill, Padding = new Padding(32), AutoScroll = true };

            Label lblTitle = new Label {
                Text = "Kios Pengecekan Saldo Mandiri WBP (Self-Service)",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(32, 24)
            };
            p.Controls.Add(lblTitle);

            Label lblSub = new Label {
                Text = "Narapidana cukup menempelkan jari pada sensor untuk melihat sisa saldo dan batas belanja hari ini.",
                ForeColor = Color.FromArgb(100, 116, 139),
                AutoSize = true,
                Location = new Point(32, 56)
            };
            p.Controls.Add(lblSub);

            picKioskFinger = new PictureBox {
                Location = new Point(32, 110),
                Width = 180,
                Height = 220,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            p.Controls.Add(picKioskFinger);

            btnStartKioskScan = new Button {
                Text = "👆 Scan Sidik Jari untuk Cek Saldo",
                Location = new Point(32, 345),
                Width = 260,
                Height = 48,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold)
            };
            btnStartKioskScan.Click += (s, e) => DoKioskScan();
            p.Controls.Add(btnStartKioskScan);

            // Info Card
            Panel infoCard = new Panel {
                Location = new Point(320, 110),
                Width = 520,
                Height = 280,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(24)
            };

            lblKioskNama = new Label { Text = "Nama: -", Font = new Font("Segoe UI", 14f, FontStyle.Bold), Location = new Point(20, 20), Width = 480 };
            lblKioskNoReg = new Label { Text = "No. Registrasi: -", Font = new Font("Segoe UI", 10.5f), Location = new Point(20, 60), Width = 480 };
            lblKioskKamar = new Label { Text = "Kamar / Blok: -", Font = new Font("Segoe UI", 10.5f), Location = new Point(20, 90), Width = 480 };
            lblKioskSaldo = new Label { Text = "Sisa Saldo: Rp 0", Font = new Font("Segoe UI", 18f, FontStyle.Bold), ForeColor = Color.FromArgb(16, 185, 129), Location = new Point(20, 130), Width = 480 };
            lblKioskLimit = new Label { Text = "Sisa Limit Belanja Hari Ini: Rp 0", Font = new Font("Segoe UI", 11f, FontStyle.Bold), ForeColor = Color.FromArgb(2, 132, 199), Location = new Point(20, 180), Width = 480 };

            infoCard.Controls.Add(lblKioskNama);
            infoCard.Controls.Add(lblKioskNoReg);
            infoCard.Controls.Add(lblKioskKamar);
            infoCard.Controls.Add(lblKioskSaldo);
            infoCard.Controls.Add(lblKioskLimit);
            p.Controls.Add(infoCard);

            page.Controls.Add(p);
        }
        #endregion

        #region TAB 4: SETTINGS
        private void BuildSettingsTab(TabPage page)
        {
            Panel p = new Panel { Dock = DockStyle.Fill, Padding = new Padding(32), AutoScroll = true };

            Label lblTitle = new Label { Text = "Pengaturan Server API & Hardware", Font = new Font("Segoe UI", 14f, FontStyle.Bold), AutoSize = true, Location = new Point(32, 24) };
            p.Controls.Add(lblTitle);

            Label lblUrl = new Label { Text = "Alamat Backend API Laravel (Hosting / Lokal):", Location = new Point(32, 80), AutoSize = true, Font = new Font("Segoe UI", 10f, FontStyle.Bold) };
            p.Controls.Add(lblUrl);

            txtSettingApiUrl = new TextBox { Location = new Point(32, 110), Width = 480, Text = this.apiUrl, Font = new Font("Segoe UI", 10.5f) };
            p.Controls.Add(txtSettingApiUrl);

            Button btnSave = new Button {
                Text = "💾 Simpan & Uji Koneksi API",
                Location = new Point(32, 160),
                Width = 220,
                Height = 40,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold)
            };
            btnSave.Click += (s, e) => {
                this.apiUrl = txtSettingApiUrl.Text.Trim();
                SyncDataFromApi();
                MessageBox.Show("Pengaturan URL API berhasil disimpan dan database telah disinkronkan.", "Sukses", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            p.Controls.Add(btnSave);

            page.Controls.Add(p);
        }
        #endregion

        #region TAB 5: UJI HARDWARE SCANNER U.ARE.U
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

        private void BuildDiagnosticTab(TabPage page)
        {
            page.AutoScroll = true;
            TableLayoutPanel layout = new TableLayoutPanel {
                Dock = DockStyle.Fill,
                ColumnCount = 2,
                RowCount = 1,
                Padding = new Padding(16)
            };
            layout.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 360));
            layout.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100));

            // Left
            Panel left = new Panel { Dock = DockStyle.Fill, BackColor = Color.White, Padding = new Padding(16), BorderStyle = BorderStyle.FixedSingle };
            Label lHead = new Label { Text = "Visual Sidik Jari Langsung", Font = new Font("Segoe UI", 11f, FontStyle.Bold), Dock = DockStyle.Top, Height = 28 };
            left.Controls.Add(lHead);

            picDiagFinger = new PictureBox {
                Dock = DockStyle.Top,
                Height = 360,
                BackColor = Color.FromArgb(15, 23, 42),
                BorderStyle = BorderStyle.FixedSingle,
                SizeMode = PictureBoxSizeMode.Zoom
            };
            left.Controls.Add(picDiagFinger);

            lblDiagStatus = new Label {
                Text = "Klik tombol di bawah untuk mengaktifkan sensor.",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Dock = DockStyle.Top,
                Height = 50,
                TextAlign = ContentAlignment.MiddleCenter
            };
            left.Controls.Add(lblDiagStatus);

            btnDiagToggle = new Button {
                Text = "▶️ Mulai Pengujian Sensor U.are.U",
                Dock = DockStyle.Bottom,
                Height = 48,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnDiagToggle.Click += (s, e) => ToggleDiagCapture();
            left.Controls.Add(btnDiagToggle);
            layout.Controls.Add(left, 0, 0);

            // Right
            Panel right = new Panel { Dock = DockStyle.Fill, BackColor = Color.White, Padding = new Padding(16), BorderStyle = BorderStyle.FixedSingle };

            GroupBox grpHardware = new GroupBox { Text = " Informasi Perangkat Keras U.are.U ", Dock = DockStyle.Top, Height = 120, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            lblDiagDevice = new Label { Text = "Perangkat: " + (currentReader != null ? currentReader.Description.Id.ProductName : "Mendeteksi..."), Location = new Point(16, 24), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            lblDiagSerial = new Label { Text = "Serial ID: " + (currentReader != null ? currentReader.Description.SerialNumber : "-"), Location = new Point(16, 50), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            Label l3 = new Label { Text = "Resolusi: 500 DPI (Optical Sensor ANSI/NIST Standard)", Location = new Point(16, 76), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            grpHardware.Controls.Add(lblDiagDevice);
            grpHardware.Controls.Add(lblDiagSerial);
            grpHardware.Controls.Add(l3);
            right.Controls.Add(grpHardware);

            GroupBox grpRes = new GroupBox { Text = " Kualitas Hasil Scan Terakhir ", Dock = DockStyle.Top, Height = 90, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            lblDiagQuality = new Label { Text = "Kualitas: Belum ada sentuhan", Location = new Point(16, 26), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            lblDiagLatency = new Label { Text = "Kecepatan Respon: -", Location = new Point(16, 52), AutoSize = true, Font = new Font("Segoe UI", 9.5f) };
            grpRes.Controls.Add(lblDiagQuality);
            grpRes.Controls.Add(lblDiagLatency);
            right.Controls.Add(grpRes);

            GroupBox grpTest = new GroupBox { Text = " Uji Pencocokan 1:1 Biometrik ", Dock = DockStyle.Top, Height = 125, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            btnDiagSaveEnroll = new Button { Text = "1. Rekam Template Uji", Location = new Point(16, 28), Width = 180, Height = 34, BackColor = Color.FromArgb(2, 132, 199), ForeColor = Color.White, FlatStyle = FlatStyle.Flat, Enabled = false };
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

            btnDiagCompare = new Button { Text = "2. Uji Kecocokan", Location = new Point(206, 28), Width = 140, Height = 34, BackColor = Color.FromArgb(241, 245, 249), FlatStyle = FlatStyle.Flat, Enabled = false };
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

            lblDiagMatch = new Label { Text = "Tempelkan jari pada sensor untuk menguji template biometrik.", Location = new Point(16, 75), Width = 480, Height = 30, ForeColor = Color.FromArgb(100, 116, 139) };
            grpTest.Controls.Add(btnDiagSaveEnroll);
            grpTest.Controls.Add(btnDiagCompare);
            grpTest.Controls.Add(lblDiagMatch);
            right.Controls.Add(grpTest);

            Panel bottomPanel = new Panel { Dock = DockStyle.Bottom, Height = 48 };

            Button btnOpenSdk = new Button {
                Text = "🛠️ Buka Tool Resmi DigitalPersona SDK (Sample .NET)",
                Dock = DockStyle.Left,
                Width = 360,
                Height = 44,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold)
            };
            btnOpenSdk.Click += (s, e) => {
                string p = @"C:\Program Files\DigitalPersona\U.are.U SDK\Windows\Samples\Bin\.NET\UareUSampleCSharp.exe";
                if (File.Exists(p)) {
                    StopDiagCapture();
                    try { System.Diagnostics.Process.Start(p); } catch { }
                } else {
                    MessageBox.Show("File tidak ditemukan: " + p, "Info", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            };
            bottomPanel.Controls.Add(btnOpenSdk);

            Button btnRefreshScanner = new Button {
                Text = "🔄 Deteksi Ulang",
                Dock = DockStyle.Right,
                Width = 140,
                Height = 44,
                BackColor = Color.FromArgb(241, 245, 249),
                FlatStyle = FlatStyle.Flat
            };
            btnRefreshScanner.Click += (s, e) => {
                StopDiagCapture();
                InitializeBiometrics();
                if (currentReader != null) {
                    lblDiagDevice.Text = "Perangkat: " + currentReader.Description.Id.ProductName;
                    lblDiagSerial.Text = "Serial ID: " + currentReader.Description.SerialNumber;
                }
            };
            bottomPanel.Controls.Add(btnRefreshScanner);

            right.Controls.Add(bottomPanel);

            grpHardware.BringToFront();
            grpRes.BringToFront();
            grpTest.BringToFront();
            bottomPanel.BringToFront();

            layout.Controls.Add(right, 1, 0);
            page.Controls.Add(layout);
        }

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
                    lblDiagStatus.Text = "Scanner U.are.U tidak ditemukan. Periksa kabel USB!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Scanner U.are.U tidak ditemukan. Periksa kabel USB.", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    return;
                }

                currentReader = readers[0];
                Constants.ResultCode resOpen = currentReader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (resOpen != Constants.ResultCode.DP_SUCCESS && resOpen != Constants.ResultCode.DP_DEVICE_BUSY)
                {
                    lblDiagStatus.Text = "Gagal buka scanner: " + resOpen + ".\nPastikan aplikasi '01_Uji_Sensor' sudah ditutup!";
                    lblDiagStatus.ForeColor = Color.FromArgb(239, 68, 68);
                    MessageBox.Show("Gagal mengakses scanner (" + resOpen + ")!\n\nKemungkinan scanner masih digunakan oleh aplikasi lain (misal: '01_Uji_Sensor_UareU_4500').\nSilakan tutup aplikasi penguji tersebut terlebih dahulu.", "Scanner Sibuk", MessageBoxButtons.OK, MessageBoxIcon.Warning);
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
            btnDiagToggle.Text = "▶️ Mulai Pengujian Sensor U.are.U";
            btnDiagToggle.BackColor = Color.FromArgb(16, 185, 129);
            lblDiagStatus.Text = "Sensor dalam keadaan siaga (standby).";
            lblDiagStatus.ForeColor = Color.FromArgb(100, 116, 139);
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
                System.Media.SystemSounds.Beep.Play();

                if (cr.Data.Views != null && cr.Data.Views.Count > 0)
                {
                    var view = cr.Data.Views[0];
                    if (picDiagFinger.Image != null) picDiagFinger.Image.Dispose();
                    picDiagFinger.Image = Create24bppBitmapStatic(view.RawImage, view.Width, view.Height);
                }

                DataResult<Fmd> res = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                if (res.ResultCode == Constants.ResultCode.DP_SUCCESS && res.Data != null)
                {
                    diagLastFmd = res.Data;
                    btnDiagSaveEnroll.Enabled = true;
                    if (diagEnrolledFmd != null) {
                        btnDiagCompare.Enabled = true;
                    }
                }

                lblDiagStatus.Text = "🟢 JARI TERDETEKSI! (Scan #" + diagScanCount + " Sukses)";
                lblDiagStatus.ForeColor = Color.FromArgb(16, 185, 129);

                lblDiagQuality.Text = "Kualitas: " + cr.Quality + " (Bagus)";
                lblDiagQuality.ForeColor = Color.FromArgb(16, 185, 129);

                lblDiagLatency.Text = "Kecepatan Respon: " + ms + " ms (Scan #" + diagScanCount + ")";

                // Continue capture
                RestartDiagCapture();
            }
            else
            {
                lblDiagQuality.Text = "Kualitas: " + cr.Quality;
                lblDiagQuality.ForeColor = Color.FromArgb(239, 68, 68);
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
        private void SyncDataFromApi()
        {
            try
            {
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
                }

                lblApiStatus.Text = "Server: Online (Terhubung)";
                lblApiStatus.ForeColor = Color.FromArgb(52, 211, 153);
            }
            catch (Exception ex)
            {
                lblApiStatus.Text = "Server: Terputus (" + ex.Message + ")";
                lblApiStatus.ForeColor = Color.FromArgb(248, 113, 113);
            }
        }

        private void RenderProductGrid()
        {
            if (gridProducts == null) return;
            gridProducts.Rows.Clear();

            string keyword = (txtSearchProduct != null) ? txtSearchProduct.Text.Trim().ToLowerInvariant() : "";

            foreach (var p in productList)
            {
                string barcode = p.ContainsKey("barcode") && p["barcode"] != null ? p["barcode"].ToString() : "";
                string nama = p.ContainsKey("nama_barang") && p["nama_barang"] != null ? p["nama_barang"].ToString() : "";
                string kategori = p.ContainsKey("kategori") && p["kategori"] != null ? p["kategori"].ToString() : "Umum";
                decimal harga = p.ContainsKey("harga") ? Convert.ToDecimal(p["harga"]) : 0;
                int stok = p.ContainsKey("stok") ? Convert.ToInt32(p["stok"]) : 0;

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
                    Harga = Convert.ToDecimal(product["harga"]),
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
            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari WBP pada sensor U.are.U 4500 untuk otorisasi pembayaran Rp " + totalAmount.ToString("N0")))
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
                            try
                            {
                                Fmd storedFmd = Fmd.DeserializeXml(wbp["template"].ToString());
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

                    if (matchedInmate == null)
                    {
                        MessageBox.Show("Sidik jari TIDAK COCOK dengan warga binaan mana pun di database!\n\nPastikan WBP telah merekam sidik jari di menu Rekam Sidik Jari.", "Verifikasi Gagal", MessageBoxButtons.OK, MessageBoxIcon.Error);
                        return;
                    }

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

                    // Submit to Laravel REST API
                    try
                    {
                        var reqData = new Dictionary<string, object>();
                        reqData["inmate_id"] = Convert.ToInt32(matchedInmate["id"]);
                        reqData["payment_method"] = "fingerprint";
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
                        string respJson = HttpPost(this.apiUrl + "/pos/checkout", jsonPayload);
                        var resp = serializer.Deserialize<Dictionary<string, object>>(respJson);

                        if (resp != null && Convert.ToBoolean(resp["success"]))
                        {
                            var data = (Dictionary<string, object>)resp["data"];
                            string inv = data["invoice_no"].ToString();
                            decimal newBal = Convert.ToDecimal(data["balance_after"]);

                            MessageBox.Show(
                                "🎉 PEMBAYARAN SUKSES!\n\n" +
                                "Invoice: " + inv + "\n" +
                                "WBP: " + matchedInmate["nama"] + " (" + matchedInmate["blok_kamar"] + ")\n" +
                                "Total: Rp " + totalAmount.ToString("N0") + "\n" +
                                "Sisa Saldo Baru: Rp " + newBal.ToString("N0"),
                                "Transaksi Berhasil",
                                MessageBoxButtons.OK,
                                MessageBoxIcon.Information
                            );

                            cart.Clear();
                            RefreshCartGrid();
                            SyncDataFromApi();
                        }
                        else
                        {
                            MessageBox.Show("Gagal memproses transaksi: " + (resp != null && resp.ContainsKey("message") ? resp["message"] : "Unknown error"), "Gagal", MessageBoxButtons.OK, MessageBoxIcon.Error);
                        }
                    }
                    catch (Exception ex)
                    {
                        MessageBox.Show("Error kirim data ke server: " + ex.Message, "Koneksi Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    }
                }
            }
        }
        #endregion

        #region ENROLLMENT WIZARD (4-TOUCH)
        private void StartEnrollmentSequence()
        {
            if (cmbInmateEnroll.SelectedIndex < 0)
            {
                MessageBox.Show("Pilih Warga Binaan terlebih dahulu.", "Peringatan", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            int inmateId = Convert.ToInt32(cmbInmateEnroll.SelectedItem.ToString().Split('-')[0].Trim());
            enrollFmdList.Clear();
            currentEnrollStep = 0;

            for (int step = 1; step <= 4; step++)
            {
                lblEnrollStep.Text = "LANGKAH " + step + " DARI 4:\nTempelkan jari WBP pada sensor...";
                lblEnrollStep.ForeColor = Color.FromArgb(2, 132, 199);
                Application.DoEvents();
                System.Threading.Thread.Sleep(200);

                using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Langkah " + step + "/4: Tempelkan jari WBP pada sensor U.are.U 4500"))
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
            lblEnrollStep.Text = "Memproses Master Template...";
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

                    lblEnrollStep.Text = "✅ SUKSES! Sidik jari berhasil disimpan di server pusat.";
                    lblEnrollStep.ForeColor = Color.FromArgb(16, 185, 129);

                    MessageBox.Show("Pendaftaran sidik jari WBP berhasil disimpan ke database hosting!", "Sukses", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    SyncDataFromApi();
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

        #region KIOSK SCAN (CEK SALDO)
        private void DoKioskScan()
        {
            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari Anda pada sensor untuk melihat informasi saldo..."))
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
                            try
                            {
                                Fmd storedFmd = Fmd.DeserializeXml(wbp["template"].ToString());
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

                    if (matched != null)
                    {
                        lblKioskNama.Text = "Nama: " + matched["nama"];
                        lblKioskNoReg.Text = "No. Registrasi: " + matched["no_registrasi"];
                        lblKioskKamar.Text = "Kamar / Blok: " + matched["blok_kamar"];
                        decimal saldo = Convert.ToDecimal(matched["saldo"]);
                        decimal limit = Convert.ToDecimal(matched["remaining_limit"]);
                        lblKioskSaldo.Text = "Sisa Saldo: Rp " + saldo.ToString("N0");
                        lblKioskLimit.Text = "Sisa Limit Hari Ini: Rp " + limit.ToString("N0");
                    }
                    else
                    {
                        MessageBox.Show("Sidik jari tidak terdaftar pada sistem.", "Tidak Dikenal", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
            }
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

            this.Text = "Sentuh Sensor Sidik Jari U.are.U 4500";
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
}

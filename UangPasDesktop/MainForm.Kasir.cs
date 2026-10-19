using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Imaging;
using System.IO;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public partial class MainForm
    {
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
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 130f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 160f));
            searchTable.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 130f));

            Label lblSearch = new Label { 
                Text = "🔍 Cari / Barcode (F1):", 
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
                else if (e.KeyCode == Keys.Down)
                {
                    if (gridProducts.Rows.Count > 0)
                    {
                        gridProducts.Focus();
                        e.Handled = true;
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

            // Excel-style Products Grid with Modern Zebra Table Styling
            gridProducts = new DataGridView {
                Dock = DockStyle.Fill,
                BackgroundColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                CellBorderStyle = DataGridViewCellBorderStyle.SingleHorizontal,
                GridColor = Color.FromArgb(226, 232, 240),
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
            gridProducts.ColumnHeadersDefaultCellStyle.ForeColor = Color.FromArgb(15, 23, 42);
            gridProducts.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 10f, FontStyle.Bold);
            gridProducts.ColumnHeadersDefaultCellStyle.Padding = new Padding(6, 0, 6, 0);

            // Alternating Row Colors (Zebra Striping)
            gridProducts.RowsDefaultCellStyle.BackColor = Color.White;
            gridProducts.RowsDefaultCellStyle.Font = new Font("Segoe UI", 9.5f);
            gridProducts.RowsDefaultCellStyle.ForeColor = Color.FromArgb(15, 23, 42);
            gridProducts.RowsDefaultCellStyle.SelectionBackColor = Color.FromArgb(224, 242, 254);
            gridProducts.RowsDefaultCellStyle.SelectionForeColor = Color.FromArgb(3, 105, 161);

            gridProducts.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            gridProducts.AlternatingRowsDefaultCellStyle.SelectionBackColor = Color.FromArgb(224, 242, 254);
            gridProducts.AlternatingRowsDefaultCellStyle.SelectionForeColor = Color.FromArgb(3, 105, 161);
            gridProducts.RowTemplate.Height = 40;

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

            // Shortcut ribbon bar at bottom of product table (Guaranteed 52px height so scrollbar never covers chips)
            Panel pnlShortcutBar = new Panel {
                Dock = DockStyle.Bottom,
                Height = 52,
                BackColor = Color.FromArgb(248, 250, 252),
                Padding = new Padding(2, 4, 2, 4)
            };

            FlowLayoutPanel flowShortcuts = new FlowLayoutPanel {
                Dock = DockStyle.Fill,
                FlowDirection = FlowDirection.LeftToRight,
                WrapContents = false,
                AutoScroll = true
            };

            flowShortcuts.Controls.Add(CreateShortcutChip("F1", "Cari Produk", () => FocusSearch()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F2", "Ubah Qty", () => EditSelectedCartQty()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F3", "Hapus Item", () => DeleteSelectedCartItem()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F4", "Bayar Jari", () => { SwitchTab(0); ProcessCheckoutFingerprint(); }));
            flowShortcuts.Controls.Add(CreateShortcutChip("F5", "Tahan Nota", () => HoldCurrentBill()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F6", "Panggil Nota", () => RecallBill()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F7", "Top-Up Saldo", () => OpenTopupDialog()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F8", "Cek Saldo WBP", () => QuickCheckSaldoWbp()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F9", "Batal", () => ClearCartConfirm()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F10", "Manual", () => { SwitchTab(0); ProcessCheckoutManual(); }));
            flowShortcuts.Controls.Add(CreateShortcutChip("F11", "Rekap Shift", () => ShowShiftRekap()));
            flowShortcuts.Controls.Add(CreateShortcutChip("F12", "Layar Pelanggan", () => ToggleCustomerDisplay()));
            flowShortcuts.Controls.Add(CreateShortcutChip("+ / -", "Ubah Jumlah", null));
            pnlShortcutBar.Controls.Add(flowShortcuts);

            // Docking order
            leftPanel.Controls.Add(gridProducts);
            leftPanel.Controls.Add(pnlShortcutBar);
            leftPanel.Controls.Add(searchTable);
            searchTable.SendToBack();
            pnlShortcutBar.SendToBack();
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

            Panel pnlCartHeader = new Panel {
                Dock = DockStyle.Top,
                Height = 36,
                BackColor = Color.Transparent
            };
            Label lblCartTitle = new Label {
                Text = "📋 Keranjang Belanja",
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 23, 42),
                Location = new Point(0, 6),
                AutoSize = true
            };
            lblCartCountBadge = new Label {
                Text = "(0 item)",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(165, 8),
                AutoSize = true
            };
            pnlCartHeader.Controls.Add(lblCartTitle);
            pnlCartHeader.Controls.Add(lblCartCountBadge);

            Button btnHeaderCustomerDisplay = new Button {
                Text = "🖥️ Layar Pelanggan [F12]",
                Dock = DockStyle.Right,
                Width = 168,
                Height = 30,
                BackColor = Color.FromArgb(240, 249, 255),
                ForeColor = Color.FromArgb(2, 132, 199),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnHeaderCustomerDisplay.FlatAppearance.BorderColor = Color.FromArgb(186, 230, 253);
            btnHeaderCustomerDisplay.Click += (s, e) => ToggleCustomerDisplay();
            pnlCartHeader.Controls.Add(btnHeaderCustomerDisplay);
            rightPanel.Controls.Add(pnlCartHeader);

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
                    string bc = txtBarcode.Text.Trim();
                    if (string.IsNullOrEmpty(bc))
                    {
                        if (cart.Count > 0)
                        {
                            SwitchTab(0);
                            ProcessCheckoutFingerprint();
                        }
                    }
                    else
                    {
                        AddProductByBarcode(bc);
                        txtBarcode.Clear();
                    }
                    e.SuppressKeyPress = true;
                }
            };
            barcodeTable.Controls.Add(lblBc, 0, 0);
            barcodeTable.Controls.Add(txtBarcode, 1, 0);
            rightPanel.Controls.Add(barcodeTable);

            // Grid Cart (Modern Flat Table Styling with Interactive Action Buttons)
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
            gridCart.RowTemplate.Height = 36;
            gridCart.RowsDefaultCellStyle.Font = new Font("Segoe UI", 9f);
            gridCart.AlternatingRowsDefaultCellStyle.BackColor = Color.FromArgb(248, 250, 252);

            gridCart.Columns.Add("nama", "Barang");
            gridCart.Columns["nama"].MinimumWidth = 70;
            gridCart.Columns["nama"].FillWeight = 110;
            gridCart.Columns["nama"].ReadOnly = true;

            gridCart.Columns.Add("harga", "Harga");
            gridCart.Columns["harga"].MinimumWidth = 50;
            gridCart.Columns["harga"].FillWeight = 65;
            gridCart.Columns["harga"].ReadOnly = true;
            gridCart.Columns["harga"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;

            DataGridViewButtonColumn colMin = new DataGridViewButtonColumn {
                Name = "col_min",
                HeaderText = "",
                Text = "−",
                UseColumnTextForButtonValue = true,
                MinimumWidth = 24,
                Width = 26,
                FillWeight = 22,
                FlatStyle = FlatStyle.Flat
            };
            colMin.SortMode = DataGridViewColumnSortMode.NotSortable;
            colMin.DefaultCellStyle.BackColor = Color.FromArgb(241, 245, 249);
            colMin.DefaultCellStyle.ForeColor = Color.FromArgb(71, 85, 105);
            colMin.DefaultCellStyle.SelectionBackColor = Color.FromArgb(226, 232, 240);
            colMin.DefaultCellStyle.SelectionForeColor = Color.FromArgb(15, 23, 42);
            colMin.DefaultCellStyle.Font = new Font("Segoe UI", 11f, FontStyle.Bold);
            colMin.DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns.Add(colMin);

            gridCart.Columns.Add("qty", "Qty");
            gridCart.Columns["qty"].MinimumWidth = 28;
            gridCart.Columns["qty"].FillWeight = 32;
            gridCart.Columns["qty"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns["qty"].DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);

            DataGridViewButtonColumn colPlus = new DataGridViewButtonColumn {
                Name = "col_plus",
                HeaderText = "",
                Text = "+",
                UseColumnTextForButtonValue = true,
                MinimumWidth = 24,
                Width = 26,
                FillWeight = 22,
                FlatStyle = FlatStyle.Flat
            };
            colPlus.SortMode = DataGridViewColumnSortMode.NotSortable;
            colPlus.DefaultCellStyle.BackColor = Color.FromArgb(224, 242, 254);
            colPlus.DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);
            colPlus.DefaultCellStyle.SelectionBackColor = Color.FromArgb(186, 230, 253);
            colPlus.DefaultCellStyle.SelectionForeColor = Color.FromArgb(3, 105, 161);
            colPlus.DefaultCellStyle.Font = new Font("Segoe UI", 11f, FontStyle.Bold);
            colPlus.DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns.Add(colPlus);

            gridCart.Columns.Add("subtotal", "Subtotal");
            gridCart.Columns["subtotal"].MinimumWidth = 55;
            gridCart.Columns["subtotal"].FillWeight = 70;
            gridCart.Columns["subtotal"].ReadOnly = true;
            gridCart.Columns["subtotal"].DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleRight;
            gridCart.Columns["subtotal"].DefaultCellStyle.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            gridCart.Columns["subtotal"].DefaultCellStyle.ForeColor = Color.FromArgb(2, 132, 199);

            DataGridViewButtonColumn colDel = new DataGridViewButtonColumn {
                Name = "col_del",
                HeaderText = "",
                Text = "✕",
                UseColumnTextForButtonValue = true,
                MinimumWidth = 24,
                Width = 26,
                FillWeight = 22,
                FlatStyle = FlatStyle.Flat
            };
            colDel.SortMode = DataGridViewColumnSortMode.NotSortable;
            colDel.DefaultCellStyle.BackColor = Color.FromArgb(254, 242, 242);
            colDel.DefaultCellStyle.ForeColor = Color.FromArgb(239, 68, 68);
            colDel.DefaultCellStyle.SelectionBackColor = Color.FromArgb(254, 202, 202);
            colDel.DefaultCellStyle.SelectionForeColor = Color.FromArgb(185, 28, 28);
            colDel.DefaultCellStyle.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            colDel.DefaultCellStyle.Alignment = DataGridViewContentAlignment.MiddleCenter;
            gridCart.Columns.Add(colDel);

            gridCart.CellValueChanged += (s, e) => RecalculateCart();
            gridCart.CellClick += (s, e) => {
                if (e.RowIndex >= 0 && e.RowIndex < cart.Count)
                {
                    if (e.ColumnIndex == gridCart.Columns["col_min"].Index)
                    {
                        QuickIncrementCartQtyAt(e.RowIndex, -1);
                    }
                    else if (e.ColumnIndex == gridCart.Columns["col_plus"].Index)
                    {
                        QuickIncrementCartQtyAt(e.RowIndex, 1);
                    }
                    else if (e.ColumnIndex == gridCart.Columns["col_del"].Index)
                    {
                        DeleteCartItemAt(e.RowIndex);
                    }
                }
            };
            gridCart.CellDoubleClick += (s, e) => {
                if (e.RowIndex >= 0 && e.RowIndex < cart.Count)
                {
                    if (e.ColumnIndex == gridCart.Columns["qty"].Index || e.ColumnIndex == gridCart.Columns["nama"].Index)
                    {
                        EditSelectedCartQty();
                    }
                }
            };
            gridCart.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Add || (e.KeyCode == Keys.Oemplus && e.Modifiers == Keys.None))
                {
                    QuickIncrementCartQty(1);
                    e.Handled = true;
                    e.SuppressKeyPress = true;
                }
                else if (e.KeyCode == Keys.Subtract || (e.KeyCode == Keys.OemMinus && e.Modifiers == Keys.None))
                {
                    QuickIncrementCartQty(-1);
                    e.Handled = true;
                    e.SuppressKeyPress = true;
                }
                else if (e.KeyCode == Keys.Delete || e.KeyCode == Keys.Back)
                {
                    DeleteSelectedCartItem();
                    e.Handled = true;
                    e.SuppressKeyPress = true;
                }
                else if (e.KeyCode == Keys.Enter)
                {
                    e.Handled = true;
                    e.SuppressKeyPress = true;
                    SwitchTab(0);
                    ProcessCheckoutFingerprint();
                }
            };
            rightPanel.Controls.Add(gridCart);

            // Bottom Summary & Checkout Button
            Panel bottomPanel = new Panel { 
                Dock = DockStyle.Bottom, 
                Height = 185, 
                BackColor = Color.FromArgb(248, 250, 252), 
                Padding = new Padding(10, 8, 10, 8) 
            };
            
            Panel pnlTotalCard = new Panel {
                Dock = DockStyle.Top,
                Height = 62,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Padding = new Padding(12, 6, 12, 6)
            };

            Label lblTotalTitle = new Label {
                Text = "TOTAL TAGIHAN BELANJA",
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(10, 8),
                AutoSize = true
            };
            lblTotal = new Label {
                Text = "Rp 0",
                Font = new Font("Segoe UI", 18f, FontStyle.Bold),
                ForeColor = Color.FromArgb(2, 132, 199),
                Dock = DockStyle.Bottom,
                Height = 34,
                TextAlign = ContentAlignment.MiddleRight
            };
            pnlTotalCard.Controls.Add(lblTotalTitle);
            pnlTotalCard.Controls.Add(lblTotal);
            bottomPanel.Controls.Add(pnlTotalCard);

            TableLayoutPanel btnTable = new TableLayoutPanel {
                Dock = DockStyle.Bottom,
                Height = 98,
                RowCount = 2,
                ColumnCount = 3,
                Padding = new Padding(0, 4, 0, 0)
            };
            btnTable.RowStyles.Add(new RowStyle(SizeType.Absolute, 52f));
            btnTable.RowStyles.Add(new RowStyle(SizeType.Absolute, 38f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 34f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 33f));
            btnTable.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 33f));
            
            btnCheckoutFingerprint = new Button {
                Text = "  Bayar dengan Sidik Jari (F4)",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            string fpLogoPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (System.IO.File.Exists(fpLogoPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(fpLogoPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    using (Bitmap orig = new Bitmap(ms))
                    {
                        btnCheckoutFingerprint.Image = new Bitmap(orig, new Size(24, 24));
                        btnCheckoutFingerprint.ImageAlign = ContentAlignment.MiddleLeft;
                        btnCheckoutFingerprint.TextImageRelation = TextImageRelation.ImageBeforeText;
                    }
                } catch { }
            }
            btnCheckoutFingerprint.FlatAppearance.BorderSize = 0;
            btnCheckoutFingerprint.Click += (s, e) => ProcessCheckoutFingerprint();
            btnTable.Controls.Add(btnCheckoutFingerprint, 0, 0);
            btnTable.SetColumnSpan(btnCheckoutFingerprint, 3);

            btnCheckoutManual = new Button {
                Text = "👤 Manual (F10)",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCheckoutManual.FlatAppearance.BorderSize = 0;
            btnCheckoutManual.Click += (s, e) => ProcessCheckoutManual();
            btnTable.Controls.Add(btnCheckoutManual, 0, 1);

            Button btnHold = new Button {
                Text = "⏸️ Tahan (F5)",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(245, 158, 11),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnHold.FlatAppearance.BorderSize = 0;
            btnHold.Click += (s, e) => HoldCurrentBill();
            btnTable.Controls.Add(btnHold, 1, 1);

            Button btnClear = new Button {
                Text = "🗑️ Batal (F9)",
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(239, 68, 68),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnClear.FlatAppearance.BorderSize = 0;
            btnClear.Click += (s, e) => ClearCartConfirm();
            btnTable.Controls.Add(btnClear, 2, 1);

            bottomPanel.Controls.Add(btnTable);
            btnTable.BringToFront();
            rightPanel.Controls.Add(bottomPanel);

            gridCart.BringToFront();
            splitKasir.Panel2.Controls.Add(rightPanel);

            page.Controls.Add(splitKasir);
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
            SoundHelper.PlayBarcodeScan();
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
            int prevSelectedIdx = (gridCart.CurrentRow != null) ? gridCart.CurrentRow.Index : -1;
            gridCart.Rows.Clear();
            decimal total = 0;
            int totalItems = 0;
            foreach (var it in cart)
            {
                decimal subtotal = it.Harga * it.Qty;
                total += subtotal;
                totalItems += it.Qty;
                gridCart.Rows.Add(
                    it.Nama, 
                    "Rp " + it.Harga.ToString("N0"), 
                    "−", 
                    it.Qty, 
                    "+", 
                    "Rp " + subtotal.ToString("N0"), 
                    "✕"
                );
            }
            lblTotal.Text = "Rp " + total.ToString("N0");
            if (lblCartCountBadge != null)
            {
                lblCartCountBadge.Text = "(" + totalItems + " item)";
            }

            if (gridCart.Rows.Count > 0)
            {
                int targetIdx = Math.Min(Math.Max(0, prevSelectedIdx), gridCart.Rows.Count - 1);
                gridCart.ClearSelection();
                gridCart.Rows[targetIdx].Selected = true;
                gridCart.CurrentCell = gridCart.Rows[targetIdx].Cells[0];
            }

            if (customerDisplay != null && !customerDisplay.IsDisposed)
            {
                customerDisplay.UpdateCart(cart, total);
            }
        }

        private void RecalculateCart()
        {
            decimal total = 0;
            int totalItems = 0;
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
                    totalItems += cart[i].Qty;
                }
            }
            lblTotal.Text = "Rp " + total.ToString("N0");
            if (lblCartCountBadge != null)
            {
                lblCartCountBadge.Text = "(" + totalItems + " item)";
            }

            if (customerDisplay != null && !customerDisplay.IsDisposed)
            {
                customerDisplay.UpdateCart(cart, total);
            }
        }
        #endregion

        #region CHECKOUT OPERATIONS
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
                    int threshold = 2147483647 / 10000;

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
                SoundHelper.PlayWarning();
                MessageBox.Show("Saldo WBP " + matchedInmate["nama"] + " TIDAK MENCUKUPI!\n\nSisa Saldo: Rp " + saldo.ToString("N0") + "\nTotal Tagihan: Rp " + totalAmount.ToString("N0"), "Saldo Kurang", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            if (totalAmount > limit)
            {
                SoundHelper.PlayWarning();
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
            catch (Exception)
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

            // Play Pleasant Success Chime & Show Interactive Dialog
            SoundHelper.PlaySuccess();
            string inmateNama = matchedInmate.ContainsKey("nama") ? matchedInmate["nama"].ToString() : "-";
            string noReg = matchedInmate.ContainsKey("no_registrasi") ? matchedInmate["no_registrasi"].ToString() : "-";
            string blokKamar = matchedInmate.ContainsKey("blok_kamar") ? matchedInmate["blok_kamar"].ToString() : "-";
            decimal remainingLimit = Math.Max(0, limit - totalAmount);

            if (customerDisplay != null && !customerDisplay.IsDisposed)
            {
                customerDisplay.ShowSuccess(invNo, inmateNama, totalAmount, balanceAfter);
            }

            using (TransactionSuccessDialog succDialog = new TransactionSuccessDialog(invNo, inmateNama, noReg, blokKamar, totalAmount, balanceAfter, remainingLimit))
            {
                succDialog.ShowDialog(this);
                if (succDialog.ShouldPrintReceipt)
                {
                    using (ReceiptForm rForm = new ReceiptForm(invNo, matchedInmate, paymentMethod, receiptCart, totalAmount, saldo, balanceAfter, !isOnlineSuccess, this.receiptConfig))
                    {
                        rForm.ShowDialog(this);
                    }
                }
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
            else if (e.KeyCode == Keys.F12)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                ToggleCustomerDisplay();
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
                OpenTopupDialog();
            }
            else if (e.KeyCode == Keys.F11)
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
            else if (e.KeyCode == Keys.F10)
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                SwitchTab(0);
                ProcessCheckoutManual();
            }
            else if ((e.KeyCode == Keys.Add || (e.KeyCode == Keys.Oemplus && e.Modifiers == Keys.None)) && (ActiveControl != txtSearchProduct && ActiveControl != txtBarcode))
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                QuickIncrementCartQty(1);
            }
            else if ((e.KeyCode == Keys.Subtract || (e.KeyCode == Keys.OemMinus && e.Modifiers == Keys.None)) && (ActiveControl != txtSearchProduct && ActiveControl != txtBarcode))
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                QuickIncrementCartQty(-1);
            }
            else if (e.KeyCode == Keys.Delete && (ActiveControl != txtSearchProduct && ActiveControl != txtBarcode))
            {
                e.Handled = true;
                e.SuppressKeyPress = true;
                DeleteSelectedCartItem();
            }
        }

        private void QuickIncrementCartQtyAt(int idx, int delta)
        {
            SwitchTab(0);
            if (idx < 0 || idx >= cart.Count) return;

            var item = cart[idx];
            if (delta > 0)
            {
                item.Qty += delta;
                SoundHelper.PlayBarcodeScan();
                RefreshCartGrid();
            }
            else if (delta < 0)
            {
                if (item.Qty > 1)
                {
                    item.Qty += delta;
                    SoundHelper.PlayTouch();
                    RefreshCartGrid();
                }
                else
                {
                    cart.RemoveAt(idx);
                    SoundHelper.PlayTouch();
                    RefreshCartGrid();
                }
            }
        }

        private void DeleteCartItemAt(int idx)
        {
            SwitchTab(0);
            if (idx < 0 || idx >= cart.Count) return;

            cart.RemoveAt(idx);
            SoundHelper.PlayTouch();
            RefreshCartGrid();
        }

        private void QuickIncrementCartQty(int delta)
        {
            SwitchTab(0);
            if (cart.Count == 0) return;

            int idx = (gridCart.CurrentRow != null && gridCart.CurrentRow.Index >= 0 && gridCart.CurrentRow.Index < cart.Count) 
                ? gridCart.CurrentRow.Index 
                : cart.Count - 1;

            QuickIncrementCartQtyAt(idx, delta);
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

            DeleteCartItemAt(idx);
        }

        private void HoldCurrentBill()
        {
            SwitchTab(0);
            if (cart.Count == 0)
            {
                SoundHelper.PlayWarning();
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
            SoundHelper.PlayTouch();

            MessageBox.Show(string.Format("Transaksi ({0} item, total Rp {1:N0}) berhasil DITAHAN [Antrean #{2}].\n\nTekan [F6] untuk memanggil kembali transaksi ini saat WBP siap bertransaksi.", hb.Items.Count, hb.TotalAmount, heldBills.Count), "Transaksi Ditahan (F5)", MessageBoxButtons.OK, MessageBoxIcon.Information);
        }

        private void RecallBill()
        {
            SwitchTab(0);
            if (heldBills.Count == 0)
            {
                SoundHelper.PlayWarning();
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
                SoundHelper.PlaySuccess();
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
                    SoundHelper.PlaySuccess();
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

        private Control CreateShortcutChip(string keyText, string actionText, Action onClick)
        {
            Panel pnl = new Panel {
                AutoSize = true,
                Height = 26,
                BackColor = Color.White,
                BorderStyle = BorderStyle.FixedSingle,
                Margin = new Padding(0, 0, 6, 0),
                Cursor = (onClick != null) ? Cursors.Hand : Cursors.Default
            };

            FlowLayoutPanel flow = new FlowLayoutPanel {
                AutoSize = true,
                FlowDirection = FlowDirection.LeftToRight,
                WrapContents = false,
                Dock = DockStyle.Fill,
                Padding = new Padding(2, 2, 4, 2),
                Margin = new Padding(0)
            };

            Label lblKey = new Label {
                Text = keyText,
                Font = new Font("Segoe UI", 7.5f, FontStyle.Bold),
                ForeColor = Color.White,
                BackColor = Color.FromArgb(30, 41, 59),
                AutoSize = true,
                Padding = new Padding(3, 1, 3, 1),
                Margin = new Padding(0, 0, 4, 0),
                TextAlign = ContentAlignment.MiddleCenter
            };

            Label lblDesc = new Label {
                Text = actionText,
                Font = new Font("Segoe UI", 8.2f, FontStyle.Regular),
                ForeColor = Color.FromArgb(51, 65, 85),
                AutoSize = true,
                Margin = new Padding(0, 1, 0, 0),
                TextAlign = ContentAlignment.MiddleLeft
            };

            flow.Controls.Add(lblKey);
            flow.Controls.Add(lblDesc);
            pnl.Controls.Add(flow);

            if (onClick != null)
            {
                EventHandler handler = (s, e) => onClick();
                pnl.Click += handler;
                flow.Click += handler;
                lblKey.Click += handler;
                lblDesc.Click += handler;

                pnl.MouseEnter += (s, e) => {
                    pnl.BackColor = Color.FromArgb(240, 249, 255);
                    lblDesc.ForeColor = Color.FromArgb(2, 132, 199);
                };
                pnl.MouseLeave += (s, e) => {
                    pnl.BackColor = Color.White;
                    lblDesc.ForeColor = Color.FromArgb(51, 65, 85);
                };
            }

            return pnl;
        }
        #endregion
    }
}

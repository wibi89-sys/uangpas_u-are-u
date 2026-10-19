using System;
using System.Collections.Generic;
using System.Drawing;
using System.Windows.Forms;

namespace UangPasDesktop
{
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
}

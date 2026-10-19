using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Windows.Forms;

namespace UangPasDesktop
{
    public partial class MainForm
    {
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
            catch (Exception)
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
}

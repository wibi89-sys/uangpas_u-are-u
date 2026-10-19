using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Imaging;
using System.IO;
using System.Net;
using System.Text;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using DPUruNet;
using Microsoft.Web.WebView2.WinForms;
using Microsoft.Web.WebView2.Core;

namespace UangPasDesktop
{
    public class ModernMainForm : Form
    {
        private WebView2 webView;
        private Reader currentReader;
        private List<Dictionary<string, object>> inmateList = new List<Dictionary<string, object>>();
        private List<Dictionary<string, object>> productList = new List<Dictionary<string, object>>();
        private ReceiptConfig receiptConfig = new ReceiptConfig();
        private JavaScriptSerializer serializer = new JavaScriptSerializer();
        private string apiUrl = "http://uangpas.test/api/v1";

        public ModernMainForm()
        {
            this.Text = "UangPas Lapas - Modern POS Terminal (WebView2 Hybrid)";
            this.Size = new Size(1280, 800);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.BackColor = Color.FromArgb(11, 15, 25);
            this.Icon = SystemIcons.Application;

            // Load settings
            LoadSettings();

            // Setup WebView2 container
            webView = new WebView2 {
                Dock = DockStyle.Fill
            };
            this.Controls.Add(webView);

            this.Load += async (s, e) => {
                await InitializeWebView();
                InitHardwareReader();
                SyncDataFromApi();
            };
        }

        private async System.Threading.Tasks.Task InitializeWebView()
        {
            try
            {
                // Ensure CoreWebView2 runtime uses dedicated, writable User Data Folder
                string baseDataFolder = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "UangPasLapas", "WebView2");
                if (!Directory.Exists(baseDataFolder)) Directory.CreateDirectory(baseDataFolder);

                CoreWebView2Environment env = null;
                bool needFallback = false;
                try
                {
                    env = await CoreWebView2Environment.CreateAsync(null, baseDataFolder);
                    await webView.EnsureCoreWebView2Async(env);
                }
                catch
                {
                    needFallback = true;
                }

                if (needFallback)
                {
                    string uniqueFolder = Path.Combine(Path.GetTempPath(), "UangPas_WV2_" + Guid.NewGuid().ToString().Substring(0, 8));
                    if (!Directory.Exists(uniqueFolder)) Directory.CreateDirectory(uniqueFolder);
                    env = await CoreWebView2Environment.CreateAsync(null, uniqueFolder);
                    await webView.EnsureCoreWebView2Async(env);
                }

                webView.CoreWebView2.Settings.IsStatusBarEnabled = false;
                webView.CoreWebView2.Settings.AreDefaultContextMenusEnabled = false;
                webView.CoreWebView2.Settings.IsZoomControlEnabled = false;

                webView.CoreWebView2.WebMessageReceived += CoreWebView2_WebMessageReceived;

                string htmlPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "wwwroot", "index.html");
                if (File.Exists(htmlPath))
                {
                    webView.CoreWebView2.Navigate("file:///" + htmlPath.Replace('\\', '/'));
                }
                else
                {
                    MessageBox.Show("File wwwroot/index.html tidak ditemukan!", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Gagal inisialisasi WebView2: " + ex.Message + "\n\nPastikan Microsoft Edge WebView2 Runtime terpasang pada Windows.", "Error WebView2", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void CoreWebView2_WebMessageReceived(object sender, CoreWebView2WebMessageReceivedEventArgs e)
        {
            try
            {
                string json = e.WebMessageAsJson;
                var data = serializer.Deserialize<Dictionary<string, object>>(json);
                if (data == null || !data.ContainsKey("action")) return;

                string action = data["action"].ToString();

                if (action == "init_data")
                {
                    SendProductsToWeb();
                    SendScannerStatusToWeb();
                }
                else if (action == "sound_scan")
                {
                    SoundHelper.PlayBarcodeScan();
                }
                else if (action == "sound_touch")
                {
                    SoundHelper.PlayTouch();
                }
                else if (action == "sound_success")
                {
                    SoundHelper.PlaySuccess();
                }
                else if (action == "sound_warning")
                {
                    SoundHelper.PlayWarning();
                }
                else if (action == "start_biometric_scan")
                {
                    decimal total = data.ContainsKey("total") ? Convert.ToDecimal(data["total"]) : 0;
                    DoBiometricCheckout(total, data);
                }
                else if (action == "check_saldo")
                {
                    using (QuickCheckSaldoForm q = new QuickCheckSaldoForm(currentReader, inmateList, apiUrl))
                    {
                        q.ShowDialog(this);
                    }
                }
                else if (action == "start_manual_checkout")
                {
                    decimal total = data.ContainsKey("total") ? Convert.ToDecimal(data["total"]) : 0;
                    using (SelectInmateForm selectForm = new SelectInmateForm(inmateList, total))
                    {
                        if (selectForm.ShowDialog(this) == DialogResult.OK && selectForm.SelectedInmate != null)
                        {
                            var wbp = selectForm.SelectedInmate;
                            SubmitTransaction(wbp, "manual", total, data);
                        }
                    }
                }
                else if (action == "switch_to_classic")
                {
                    SwitchToClassicMode();
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("Error handling web message: " + ex.Message);
            }
        }

        private void DoBiometricCheckout(decimal totalAmount, Dictionary<string, object> reqPayload)
        {
            // Ensure inmates data is loaded
            if (inmateList == null || inmateList.Count == 0)
            {
                SyncDataFromApi();
            }

            if (inmateList == null || inmateList.Count == 0)
            {
                SoundHelper.PlayWarning();
                MessageBox.Show("Data Warga Binaan belum tersedia atau server backend belum aktif.\nSilakan periksa koneksi ke server Laragon.", "Data Belum Siap", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari WBP pada sensor sidik jari untuk otorisasi pembayaran Rp " + totalAmount.ToString("N0")))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    Fmd liveFmd = prompt.CapturedFmd;
                    Dictionary<string, object> matchedInmate = null;
                    int bestScore = int.MaxValue;
                    int threshold = 2147483647 / 10000;
                    int withTemplateCount = 0;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
                            withTemplateCount++;
                            Fmd storedFmd = MainForm.ParseFmdFromTemplate(wbp["template"].ToString());
                            if (storedFmd != null)
                            {
                                try
                                {
                                    CompareResult cr = Comparison.Compare(liveFmd, 0, storedFmd, 0);
                                    if (cr.ResultCode == Constants.ResultCode.DP_SUCCESS && cr.Score < threshold && cr.Score < bestScore)
                                    {
                                        bestScore = cr.Score;
                                        matchedInmate = wbp;
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
                            "Sensor sidik jari berhasil membaca sidik jari Anda (" + withTemplateCount + " template terdaftar).\n" +
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
                                SoundHelper.PlayError();
                                MessageBox.Show("Gagal mendaftarkan sidik jari: " + ex.Message, "Error Pendaftaran", MessageBoxButtons.OK, MessageBoxIcon.Error);
                                return;
                            }
                        }
                        else
                        {
                            SoundHelper.PlayError();
                            return;
                        }
                    }

                    if (matchedInmate != null)
                    {
                        SubmitTransaction(matchedInmate, "fingerprint", totalAmount, reqPayload);
                    }
                    else
                    {
                        var cancelMsg = new Dictionary<string, object> { { "action", "fingerprint_cancel" } };
                        webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(cancelMsg));
                    }
                }
                else
                {
                    var cancelMsg = new Dictionary<string, object> { { "action", "fingerprint_cancel" } };
                    webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(cancelMsg));
                }
            }
        }

        private void SubmitTransaction(Dictionary<string, object> matchedInmate, string paymentMethod, decimal totalAmount, Dictionary<string, object> reqPayload)
        {
            decimal saldo = matchedInmate.ContainsKey("saldo") ? Convert.ToDecimal(matchedInmate["saldo"]) : 0;
            decimal limit = matchedInmate.ContainsKey("remaining_limit") ? Convert.ToDecimal(matchedInmate["remaining_limit"]) : (matchedInmate.ContainsKey("limit_harian") ? Convert.ToDecimal(matchedInmate["limit_harian"]) : 0);

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
            reqData["cashier_name"] = "Kasir POS Modern WebView2";

            var itemsArr = new List<Dictionary<string, object>>();
            if (reqPayload != null && reqPayload.ContainsKey("cart"))
            {
                var rawCart = (object[])reqPayload["cart"];
                foreach (Dictionary<string, object> cItem in rawCart)
                {
                    itemsArr.Add(new Dictionary<string, object> {
                        { "product_id", Convert.ToInt32(cItem["id"]) },
                        { "qty", Convert.ToInt32(cItem["qty"]) }
                    });
                }
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
                invNo = "OFF-" + DateTime.Now.ToString("yyyyMMddHHmmss");
                isOnlineSuccess = false;
            }

            // Update local memory
            matchedInmate["saldo"] = balanceAfter;
            matchedInmate["remaining_limit"] = limit - totalAmount;

            // Success feedback
            SoundHelper.PlaySuccess();
            string nama = matchedInmate.ContainsKey("nama") ? matchedInmate["nama"].ToString() : "-";
            string noReg = matchedInmate.ContainsKey("no_registrasi") ? matchedInmate["no_registrasi"].ToString() : "-";
            string kamar = matchedInmate.ContainsKey("blok_kamar") ? matchedInmate["blok_kamar"].ToString() : "-";

            using (TransactionSuccessDialog succ = new TransactionSuccessDialog(invNo, nama, noReg, kamar, totalAmount, balanceAfter, Math.Max(0, limit - totalAmount)))
            {
                succ.ShowDialog(this);
            }

            // Clear cart in WebView2
            var succMsg = new Dictionary<string, object> {
                { "action", "fingerprint_success" },
                { "inmate_name", nama }
            };
            webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(succMsg));

            if (isOnlineSuccess)
            {
                SyncDataFromApi();
            }
        }

        private void SendProductsToWeb()
        {
            if (productList != null && productList.Count > 0)
            {
                var msg = new Dictionary<string, object> {
                    { "action", "update_products" },
                    { "products", productList }
                };
                webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(msg));
            }
        }

        private void SendScannerStatusToWeb()
        {
            bool online = (currentReader != null);
            var msg = new Dictionary<string, object> {
                { "action", "scanner_status" },
                { "online", online },
                { "text", online ? "Scanner: Siaga (" + currentReader.Description.SerialNumber + ")" : "Scanner: Siaga (Siap)" }
            };
            webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(msg));
        }

        private void SwitchToClassicMode()
        {
            this.Hide();
            MainForm classic = new MainForm();
            classic.FormClosed += (s, e) => this.Close();
            classic.Show();
        }

        private void InitHardwareReader()
        {
            try
            {
                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers != null && readers.Count > 0)
                {
                    currentReader = readers[0];
                }
            }
            catch { }
        }

        private string DetectWorkingApiUrl()
        {
            string[] candidates = new string[] {
                "http://uangpas.test/api/v1",
                this.apiUrl,
                "http://127.0.0.1:8000/api/v1"
            };

            foreach (string c in candidates)
            {
                if (string.IsNullOrEmpty(c)) continue;
                try
                {
                    HttpWebRequest req = (HttpWebRequest)WebRequest.Create(c + "/pos/inmates");
                    req.Method = "GET";
                    req.Timeout = 1500;
                    using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
                    {
                        if (res.StatusCode == HttpStatusCode.OK)
                        {
                            return c;
                        }
                    }
                }
                catch { }
            }

            return this.apiUrl;
        }

        private void SyncDataFromApi()
        {
            this.apiUrl = DetectWorkingApiUrl();
            try
            {
                // 1. Fetch Products
                string productsJson = HttpGet(this.apiUrl + "/pos/products");
                var prodResp = serializer.Deserialize<Dictionary<string, object>>(productsJson);
                if (prodResp != null && prodResp.ContainsKey("data"))
                {
                    var items = prodResp["data"] as System.Collections.IEnumerable;
                    if (items != null)
                    {
                        productList.Clear();
                        foreach (var it in items)
                        {
                            var dict = it as Dictionary<string, object>;
                            if (dict != null) productList.Add(dict);
                        }
                    }
                    try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_products.json"), productsJson); } catch { }
                }

                // 2. Fetch Inmates with biometric templates
                string inmatesJson = HttpGet(this.apiUrl + "/pos/inmates");
                var inmateResp = serializer.Deserialize<Dictionary<string, object>>(inmatesJson);
                if (inmateResp != null && inmateResp.ContainsKey("data"))
                {
                    var list = inmateResp["data"] as System.Collections.IEnumerable;
                    if (list != null)
                    {
                        inmateList.Clear();
                        foreach (var wbp in list)
                        {
                            var dict = wbp as Dictionary<string, object>;
                            if (dict != null) inmateList.Add(dict);
                        }
                    }
                    try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_inmates.json"), inmatesJson); } catch { }
                }

                try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "diag_sync.log"), DateTime.Now.ToString() + " Sync SUCCESS! Inmates: " + inmateList.Count + ", Products: " + productList.Count + ", API: " + this.apiUrl + "\n"); } catch { }

                this.BeginInvoke((Action)(() => {
                    SendProductsToWeb();
                    SendScannerStatusToWeb();
                }));
            }
            catch (Exception ex)
            {
                try { File.WriteAllText(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "diag_sync.log"), DateTime.Now.ToString() + " Sync FAILED: " + ex.Message + "\n"); } catch { }
                LoadLocalCache();
                this.BeginInvoke((Action)(() => {
                    SendProductsToWeb();
                    SendScannerStatusToWeb();
                }));
            }
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
                        var items = prodResp["data"] as System.Collections.IEnumerable;
                        if (items != null)
                        {
                            productList.Clear();
                            foreach (var it in items)
                            {
                                var dict = it as Dictionary<string, object>;
                                if (dict != null) productList.Add(dict);
                            }
                        }
                    }
                }

                string iCache = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "cache_inmates.json");
                if (File.Exists(iCache))
                {
                    string json = File.ReadAllText(iCache);
                    var inmateResp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (inmateResp != null && inmateResp.ContainsKey("data"))
                    {
                        var list = inmateResp["data"] as System.Collections.IEnumerable;
                        if (list != null)
                        {
                            inmateList.Clear();
                            foreach (var wbp in list)
                            {
                                var dict = wbp as Dictionary<string, object>;
                                if (dict != null) inmateList.Add(dict);
                            }
                        }
                    }
                }
            }
            catch { }
        }

        private void LoadSettings()
        {
            string settingsPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
            if (File.Exists(settingsPath))
            {
                try
                {
                    string json = File.ReadAllText(settingsPath);
                    var dict = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (dict != null && dict.ContainsKey("api_url") && dict["api_url"] != null)
                    {
                        this.apiUrl = dict["api_url"].ToString().Trim();
                    }
                }
                catch { }
            }
        }

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
    }
}

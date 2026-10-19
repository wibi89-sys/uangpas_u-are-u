using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using DPUruNet;
using Microsoft.Web.WebView2.WinForms;

namespace UangPasDesktop
{
    public class ModernMainForm : Form
    {
        private WebView2 webView;
        private Reader currentReader;
        private List<Dictionary<string, object>> inmateList = new List<Dictionary<string, object>>();
        private List<Dictionary<string, object>> productList = new List<Dictionary<string, object>>();
        private JavaScriptSerializer serializer = new JavaScriptSerializer();
        private string apiUrl = "http://127.0.0.1:8000/api/v1";

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

                Microsoft.Web.WebView2.Core.CoreWebView2Environment env = null;
                bool needFallback = false;
                try
                {
                    env = await Microsoft.Web.WebView2.Core.CoreWebView2Environment.CreateAsync(null, baseDataFolder);
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
                    env = await Microsoft.Web.WebView2.Core.CoreWebView2Environment.CreateAsync(null, uniqueFolder);
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

        private void CoreWebView2_WebMessageReceived(object sender, Microsoft.Web.WebView2.Core.CoreWebView2WebMessageReceivedEventArgs e)
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
                    using (SelectInmateForm selectForm = new SelectInmateForm(inmateList, 0))
                    {
                        if (selectForm.ShowDialog(this) == DialogResult.OK && selectForm.SelectedInmate != null)
                        {
                            var wbp = selectForm.SelectedInmate;
                            SoundHelper.PlaySuccess();
                            MessageBox.Show("WBP Terpilih: " + wbp["nama"] + " (Saldo: Rp " + Convert.ToDecimal(wbp["saldo"]).ToString("N0") + ")", "Otorisasi Manual", MessageBoxButtons.OK, MessageBoxIcon.Information);
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
            using (FingerprintPromptForm prompt = new FingerprintPromptForm(currentReader, "Tempelkan jari WBP pada sensor sidik jari untuk otorisasi pembayaran Rp " + totalAmount.ToString("N0")))
            {
                if (prompt.ShowDialog(this) == DialogResult.OK && prompt.CapturedFmd != null)
                {
                    Fmd liveFmd = prompt.CapturedFmd;
                    Dictionary<string, object> matchedInmate = null;
                    int bestScore = int.MaxValue;
                    int threshold = 2147483647 / 10000;

                    foreach (var wbp in inmateList)
                    {
                        if (wbp.ContainsKey("template") && wbp["template"] != null && !string.IsNullOrEmpty(wbp["template"].ToString()))
                        {
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

                    if (matchedInmate != null)
                    {
                        string nama = matchedInmate.ContainsKey("nama") ? matchedInmate["nama"].ToString() : "-";
                        string noReg = matchedInmate.ContainsKey("no_registrasi") ? matchedInmate["no_registrasi"].ToString() : "-";
                        string kamar = matchedInmate.ContainsKey("blok_kamar") ? matchedInmate["blok_kamar"].ToString() : "-";
                        decimal saldo = Convert.ToDecimal(matchedInmate["saldo"]);
                        decimal sisaLimit = Convert.ToDecimal(matchedInmate["remaining_limit"]);

                        SoundHelper.PlaySuccess();
                        using (TransactionSuccessDialog succ = new TransactionSuccessDialog("INV-" + DateTime.Now.ToString("yyyyMMddHHmmss"), nama, noReg, kamar, totalAmount, saldo - totalAmount, sisaLimit - totalAmount))
                        {
                            succ.ShowDialog(this);
                        }

                        // Send success message to web view to clear cart
                        var succMsg = new Dictionary<string, object> {
                            { "action", "fingerprint_success" },
                            { "inmate_name", nama }
                        };
                        webView.CoreWebView2.PostWebMessageAsJson(serializer.Serialize(succMsg));
                    }
                    else
                    {
                        SoundHelper.PlayError();
                        MessageBox.Show("Sidik jari tidak cocok dengan data Warga Binaan mana pun.", "Tidak Dikenali", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
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

        private void SyncDataFromApi()
        {
            try
            {
                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(this.apiUrl + "/pos/sync-data");
                req.Method = "GET";
                req.Timeout = 5000;
                using (HttpWebResponse res = (HttpWebResponse)req.GetResponse())
                using (StreamReader r = new StreamReader(res.GetResponseStream()))
                {
                    string json = r.ReadToEnd();
                    var resp = serializer.Deserialize<Dictionary<string, object>>(json);
                    if (resp != null && resp.ContainsKey("success") && Convert.ToBoolean(resp["success"]))
                    {
                        var data = (Dictionary<string, object>)resp["data"];
                        if (data.ContainsKey("products"))
                        {
                            var rawProds = (object[])data["products"];
                            productList.Clear();
                            foreach (var p in rawProds) productList.Add((Dictionary<string, object>)p);
                        }
                        if (data.ContainsKey("inmates"))
                        {
                            var rawInmates = (object[])data["inmates"];
                            inmateList.Clear();
                            foreach (var inm in rawInmates) inmateList.Add((Dictionary<string, object>)inm);
                        }

                        this.BeginInvoke((Action)(() => SendProductsToWeb()));
                    }
                }
            }
            catch
            {
                // Fallback to local default / dummy products if server is not yet started
            }
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
    }
}

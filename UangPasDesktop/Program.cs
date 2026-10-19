using System;
using System.Collections.Generic;
using System.IO;
using System.Net;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using UangPasDesktop.Forms;
using UangPasDesktop.Models;

namespace UangPasDesktop
{
    static class Program
    {
        [STAThread]
        static void Main()
        {
            try
            {
                // Enable TLS 1.2 (3072) and TLS 1.1 (768) for modern HTTPS cloud / web hosting
                ServicePointManager.SecurityProtocol = (SecurityProtocolType)3072 | (SecurityProtocolType)768 | SecurityProtocolType.Tls;
                ServicePointManager.Expect100Continue = false;
            }
            catch { }

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            Application.ThreadException += delegate(object s, System.Threading.ThreadExceptionEventArgs e) {
                MessageBox.Show("Terjadi kesalahan antarmuka: " + e.Exception.Message, "Peringatan Sistem", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            };

            while (true)
            {
                try
                {
                    string apiUrl = LoadApiUrlFromSettings();
                    using (LoginForm loginForm = new LoginForm(apiUrl))
                    {
                        if (loginForm.ShowDialog() != DialogResult.OK)
                        {
                            // User cancelled login or exited
                            break;
                        }
                    }

                    // Launch main form with authenticated session
                    MainForm mainForm = new MainForm();
                    Application.Run(mainForm);

                    if (!mainForm.WantsLogoutAndRelogin)
                    {
                        break;
                    }
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Terjadi kesalahan aplikasi: " + ex.Message, "Error Sistem POS", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    break;
                }
            }
        }

        private static string LoadApiUrlFromSettings()
        {
            try
            {
                string path = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "settings.json");
                if (File.Exists(path))
                {
                    string json = File.ReadAllText(path);
                    JavaScriptSerializer js = new JavaScriptSerializer();
                    Dictionary<string, object> dict = js.Deserialize<Dictionary<string, object>>(json);
                    if (dict != null && dict.ContainsKey("api_url") && dict["api_url"] != null)
                    {
                        string url = dict["api_url"].ToString().Trim();
                        if (!string.IsNullOrEmpty(url)) return url;
                    }
                }
            }
            catch { }
            return "http://uangpas.test/api/v1";
        }
    }
}

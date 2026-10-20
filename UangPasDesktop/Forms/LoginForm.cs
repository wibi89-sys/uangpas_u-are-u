using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using UangPasDesktop.Models;

namespace UangPasDesktop.Forms
{
    public class LoginForm : Form
    {
        private string apiUrl = "http://127.0.0.1:8000/api/v1";
        private TextBox txtUsername;
        private TextBox txtPassword;
        private CheckBox chkShowPassword;
        private Label lblError;
        private Button btnLogin;
        private Button btnCancel;
        private Label lblServerInfo;

        public LoginForm(string currentApiUrl)
        {
            if (!string.IsNullOrEmpty(currentApiUrl))
            {
                this.apiUrl = currentApiUrl;
            }

            InitializeComponent();
        }

        private void InitializeComponent()
        {
            this.Text = "Login Petugas - Terminal Kasir POS UangPas";
            this.ClientSize = new Size(500, 395);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            this.AutoScaleMode = AutoScaleMode.None;

            // 1. Top Header Banner
            Panel pnlHeader = new Panel
            {
                Dock = DockStyle.Top,
                Height = 88,
                BackColor = Color.FromArgb(15, 23, 42)
            };

            PictureBox picLogo = new PictureBox
            {
                Size = new Size(44, 44),
                Location = new Point(22, 16),
                SizeMode = PictureBoxSizeMode.Zoom,
                BackColor = Color.Transparent
            };
            string logoPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (File.Exists(logoPath))
            {
                try
                {
                    byte[] bytes = File.ReadAllBytes(logoPath);
                    using (MemoryStream ms = new MemoryStream(bytes))
                    {
                        Bitmap bmp = new Bitmap(ms);
                        picLogo.Image = (Image)bmp.Clone();
                        this.Icon = Icon.FromHandle(bmp.GetHicon());
                    }
                }
                catch { }
            }
            string icoPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "app.ico");
            if (File.Exists(icoPath))
            {
                try { this.Icon = new Icon(icoPath); } catch { }
            }
            pnlHeader.Controls.Add(picLogo);

            Label lblTitle = new Label
            {
                Text = "UangPas POS Terminal",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(76, 12)
            };
            pnlHeader.Controls.Add(lblTitle);

            Label lblSubTitle = new Label
            {
                Text = "Otorisasi Akses Petugas & Kasir Koperasi Lapas",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
                AutoSize = true,
                Location = new Point(78, 38)
            };
            pnlHeader.Controls.Add(lblSubTitle);

            lblServerInfo = new Label
            {
                Text = "● Server: " + this.apiUrl,
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 8f, FontStyle.Regular),
                AutoSize = true,
                Location = new Point(78, 60)
            };
            pnlHeader.Controls.Add(lblServerInfo);
            this.Controls.Add(pnlHeader);

            // 2. Main Form Body (Docked Fill with BringToFront so it never hides behind header)
            Panel pnlBody = new Panel
            {
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(248, 250, 252),
                AutoScroll = true
            };

            int startX = 35;
            int inputWidth = 430;
            int curY = 20;

            // Username / NIP Field
            Label lblUsername = new Label
            {
                Text = "NIP Kedinasan atau Username Petugas:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(startX, curY),
                Size = new Size(inputWidth, 18)
            };
            pnlBody.Controls.Add(lblUsername);
            curY += 22;

            txtUsername = new TextBox
            {
                Font = new Font("Segoe UI", 10.5f, FontStyle.Regular),
                Location = new Point(startX, curY),
                Size = new Size(inputWidth, 28)
            };
            pnlBody.Controls.Add(txtUsername);
            curY += 38;

            // Password Field
            Label lblPassword = new Label
            {
                Text = "Kata Sandi (Password / PIN):",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(startX, curY),
                Size = new Size(inputWidth, 18)
            };
            pnlBody.Controls.Add(lblPassword);
            curY += 22;

            txtPassword = new TextBox
            {
                Font = new Font("Segoe UI", 10.5f, FontStyle.Regular),
                Location = new Point(startX, curY),
                Size = new Size(inputWidth, 28),
                UseSystemPasswordChar = true
            };
            txtPassword.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    DoLogin();
                }
            };
            pnlBody.Controls.Add(txtPassword);
            curY += 34;

            // Show password checkbox
            chkShowPassword = new CheckBox
            {
                Text = "Tampilkan Password",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(startX + 2, curY),
                Size = new Size(200, 20),
                Cursor = Cursors.Hand
            };
            chkShowPassword.CheckedChanged += (s, e) => {
                txtPassword.UseSystemPasswordChar = !chkShowPassword.Checked;
            };
            pnlBody.Controls.Add(chkShowPassword);
            curY += 26;

            // Error / Feedback message
            lblError = new Label
            {
                Text = "",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(239, 68, 68),
                Location = new Point(startX, curY),
                Size = new Size(inputWidth, 26),
                TextAlign = ContentAlignment.MiddleLeft
            };
            pnlBody.Controls.Add(lblError);
            curY += 32;

            // Buttons: Login and Cancel
            btnLogin = new Button
            {
                Text = "Masuk ke Terminal Kasir POS",
                Font = new Font("Segoe UI", 10f, FontStyle.Bold),
                ForeColor = Color.White,
                BackColor = Color.FromArgb(2, 132, 199),
                FlatStyle = FlatStyle.Flat,
                Location = new Point(startX, curY),
                Size = new Size(260, 42),
                Cursor = Cursors.Hand
            };
            btnLogin.FlatAppearance.BorderSize = 0;
            btnLogin.Click += (s, e) => DoLogin();
            pnlBody.Controls.Add(btnLogin);

            btnCancel = new Button
            {
                Text = "Batal / Keluar",
                Font = new Font("Segoe UI", 10f, FontStyle.Regular),
                ForeColor = Color.FromArgb(71, 85, 105),
                BackColor = Color.FromArgb(226, 232, 240),
                FlatStyle = FlatStyle.Flat,
                Location = new Point(startX + 270, curY),
                Size = new Size(160, 42),
                Cursor = Cursors.Hand
            };
            btnCancel.FlatAppearance.BorderSize = 0;
            btnCancel.Click += (s, e) => {
                this.DialogResult = DialogResult.Cancel;
                this.Close();
            };
            pnlBody.Controls.Add(btnCancel);

            this.Controls.Add(pnlBody);
            pnlBody.BringToFront();
            this.AcceptButton = btnLogin;
        }

        private void DoLogin()
        {
            string username = txtUsername.Text.Trim();
            string password = txtPassword.Text;

            if (string.IsNullOrEmpty(username))
            {
                lblError.ForeColor = Color.FromArgb(239, 68, 68);
                lblError.Text = "Silakan masukkan NIP atau username petugas.";
                txtUsername.Focus();
                return;
            }

            if (string.IsNullOrEmpty(password))
            {
                lblError.ForeColor = Color.FromArgb(239, 68, 68);
                lblError.Text = "Silakan masukkan password petugas.";
                txtPassword.Focus();
                return;
            }

            btnLogin.Enabled = false;
            btnLogin.Text = "Memverifikasi...";
            lblError.ForeColor = Color.FromArgb(2, 132, 199);
            lblError.Text = "Menghubungi server otorisasi...";
            Application.DoEvents();

            try
            {
                JavaScriptSerializer js = new JavaScriptSerializer();
                Dictionary<string, string> payload = new Dictionary<string, string>();
                payload["username"] = username;
                payload["password"] = password;
                string jsonPayload = js.Serialize(payload);

                string loginUrl = this.apiUrl.TrimEnd('/') + "/pos/login";

                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(loginUrl);
                req.Method = "POST";
                req.ContentType = "application/json";
                req.Timeout = 8000;

                byte[] data = Encoding.UTF8.GetBytes(jsonPayload);
                req.ContentLength = data.Length;

                using (Stream stream = req.GetRequestStream())
                {
                    stream.Write(data, 0, data.Length);
                }

                using (HttpWebResponse resp = (HttpWebResponse)req.GetResponse())
                using (StreamReader reader = new StreamReader(resp.GetResponseStream()))
                {
                    string jsonResp = reader.ReadToEnd();
                    Dictionary<string, object> res = js.Deserialize<Dictionary<string, object>>(jsonResp);

                    if (res != null && res.ContainsKey("success") && Convert.ToBoolean(res["success"]))
                    {
                        if (res.ContainsKey("data") && res["data"] is Dictionary<string, object>)
                        {
                            Dictionary<string, object> userData = (Dictionary<string, object>)res["data"];
                            AuthUser user = AuthUser.FromDictionary(userData);
                            AuthSession.CurrentUser = user;

                            lblError.ForeColor = Color.FromArgb(16, 185, 129);
                            lblError.Text = "Login sukses! Membuka antarmuka kasir...";
                            Application.DoEvents();

                            this.DialogResult = DialogResult.OK;
                            this.Close();
                            return;
                        }
                    }
                    else
                    {
                        string msg = (res != null && res.ContainsKey("message")) ? res["message"].ToString() : "Otentikasi gagal.";
                        lblError.ForeColor = Color.FromArgb(239, 68, 68);
                        lblError.Text = msg;
                    }
                }
            }
            catch (WebException wex)
            {
                string msg = "Gagal terhubung ke server UangPas.";
                try
                {
                    if (wex.Response != null)
                    {
                        using (StreamReader sr = new StreamReader(wex.Response.GetResponseStream()))
                        {
                            string errJson = sr.ReadToEnd();
                            JavaScriptSerializer js = new JavaScriptSerializer();
                            Dictionary<string, object> errObj = js.Deserialize<Dictionary<string, object>>(errJson);
                            if (errObj != null && errObj.ContainsKey("message"))
                            {
                                msg = errObj["message"].ToString();
                            }
                        }
                    }
                    else
                    {
                        msg = "Server offline atau URL salah (" + wex.Message + ").";
                    }
                }
                catch { }

                lblError.ForeColor = Color.FromArgb(239, 68, 68);
                lblError.Text = msg;
            }
            catch (Exception ex)
            {
                lblError.ForeColor = Color.FromArgb(239, 68, 68);
                lblError.Text = "Terjadi kesalahan: " + ex.Message;
            }
            finally
            {
                btnLogin.Enabled = true;
                btnLogin.Text = "🔑 Masuk Terminal";
            }
        }
    }
}

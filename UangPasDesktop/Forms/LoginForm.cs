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
        private ComboBox cmbQuickDemo;
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
            this.Size = new Size(480, 560);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(248, 250, 252);
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);

            // 1. Top Header Banner
            Panel pnlHeader = new Panel
            {
                Dock = DockStyle.Top,
                Height = 110,
                BackColor = Color.FromArgb(15, 23, 42)
            };

            PictureBox picLogo = new PictureBox
            {
                Size = new Size(42, 42),
                Location = new Point(24, 20),
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
            pnlHeader.Controls.Add(picLogo);

            Label lblTitle = new Label
            {
                Text = "UangPas POS Terminal",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(74, 18)
            };
            pnlHeader.Controls.Add(lblTitle);

            Label lblSubTitle = new Label
            {
                Text = "Otorisasi Akses Petugas & Kasir Koperasi Lapas",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
                AutoSize = true,
                Location = new Point(76, 44)
            };
            pnlHeader.Controls.Add(lblSubTitle);

            lblServerInfo = new Label
            {
                Text = "● Server API: " + this.apiUrl,
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Segoe UI", 8f, FontStyle.Regular),
                AutoSize = true,
                Location = new Point(76, 68)
            };
            pnlHeader.Controls.Add(lblServerInfo);
            this.Controls.Add(pnlHeader);

            // 2. Main Form Body
            Panel pnlBody = new Panel
            {
                Dock = DockStyle.Fill,
                Padding = new Padding(32, 20, 32, 20),
                BackColor = Color.FromArgb(248, 250, 252)
            };

            int curY = 16;

            // Quick Role Demo Picker
            Label lblDemo = new Label
            {
                Text = "Pilih Akun Cepat (Mode Uji Coba Role):",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(32, curY),
                Size = new Size(400, 18)
            };
            pnlBody.Controls.Add(lblDemo);
            curY += 22;

            cmbQuickDemo = new ComboBox
            {
                DropDownStyle = ComboBoxStyle.DropDownList,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Regular),
                Location = new Point(32, curY),
                Size = new Size(400, 28),
                BackColor = Color.White
            };
            cmbQuickDemo.Items.Add("-- Masukkan NIP / Password Manual --");
            cmbQuickDemo.Items.Add("[KASIR] Petugas Kasir Toko (199511112019031005)");
            cmbQuickDemo.Items.Add("[LOKET] Petugas Loket & Registrasi (199208082015021004)");
            cmbQuickDemo.Items.Add("[GUDANG] Pengelola Koperasi & Gudang (198705052010011003)");
            cmbQuickDemo.Items.Add("[KALAPAS] Kepala Lapas Kelas I (198002022005011002)");
            cmbQuickDemo.Items.Add("[SUPERADMIN] Kakanwil / Kadivpas (197501011999031001)");
            cmbQuickDemo.SelectedIndex = 1; // Default to Kasir
            cmbQuickDemo.SelectedIndexChanged += CmbQuickDemo_SelectedIndexChanged;
            pnlBody.Controls.Add(cmbQuickDemo);
            curY += 40;

            // Username / NIP Field
            Label lblUsername = new Label
            {
                Text = "NIP / Username / Email Petugas:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(32, curY),
                Size = new Size(400, 20)
            };
            pnlBody.Controls.Add(lblUsername);
            curY += 22;

            txtUsername = new TextBox
            {
                Font = new Font("Segoe UI", 10.5f, FontStyle.Regular),
                Location = new Point(32, curY),
                Size = new Size(400, 30),
                Text = "199511112019031005"
            };
            pnlBody.Controls.Add(txtUsername);
            curY += 40;

            // Password Field
            Label lblPassword = new Label
            {
                Text = "Password / PIN Petugas:",
                Font = new Font("Segoe UI", 9f, FontStyle.Bold),
                ForeColor = Color.FromArgb(30, 41, 59),
                Location = new Point(32, curY),
                Size = new Size(400, 20)
            };
            pnlBody.Controls.Add(lblPassword);
            curY += 22;

            txtPassword = new TextBox
            {
                Font = new Font("Segoe UI", 10.5f, FontStyle.Regular),
                Location = new Point(32, curY),
                Size = new Size(400, 30),
                UseSystemPasswordChar = true,
                Text = "password"
            };
            txtPassword.KeyDown += (s, e) => {
                if (e.KeyCode == Keys.Enter)
                {
                    e.SuppressKeyPress = true;
                    DoLogin();
                }
            };
            pnlBody.Controls.Add(txtPassword);
            curY += 36;

            // Show password checkbox
            chkShowPassword = new CheckBox
            {
                Text = "Tampilkan Password",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
                ForeColor = Color.FromArgb(100, 116, 139),
                Location = new Point(34, curY),
                Size = new Size(200, 22),
                Cursor = Cursors.Hand
            };
            chkShowPassword.CheckedChanged += (s, e) => {
                txtPassword.UseSystemPasswordChar = !chkShowPassword.Checked;
            };
            pnlBody.Controls.Add(chkShowPassword);
            curY += 30;

            // Error / Feedback message
            lblError = new Label
            {
                Text = "",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(239, 68, 68),
                Location = new Point(32, curY),
                Size = new Size(400, 36),
                TextAlign = ContentAlignment.MiddleLeft
            };
            pnlBody.Controls.Add(lblError);
            curY += 42;

            // Buttons: Login and Cancel
            btnLogin = new Button
            {
                Text = "🔑 Masuk Terminal",
                Font = new Font("Segoe UI", 10.5f, FontStyle.Bold),
                ForeColor = Color.White,
                BackColor = Color.FromArgb(2, 132, 199),
                FlatStyle = FlatStyle.Flat,
                Location = new Point(32, curY),
                Size = new Size(240, 42),
                Cursor = Cursors.Hand
            };
            btnLogin.FlatAppearance.BorderSize = 0;
            btnLogin.Click += (s, e) => DoLogin();
            pnlBody.Controls.Add(btnLogin);

            btnCancel = new Button
            {
                Text = "Keluar",
                Font = new Font("Segoe UI", 10f, FontStyle.Regular),
                ForeColor = Color.FromArgb(100, 116, 139),
                BackColor = Color.FromArgb(226, 232, 240),
                FlatStyle = FlatStyle.Flat,
                Location = new Point(282, curY),
                Size = new Size(150, 42),
                Cursor = Cursors.Hand
            };
            btnCancel.FlatAppearance.BorderSize = 0;
            btnCancel.Click += (s, e) => {
                this.DialogResult = DialogResult.Cancel;
                this.Close();
            };
            pnlBody.Controls.Add(btnCancel);

            this.Controls.Add(pnlBody);
            this.AcceptButton = btnLogin;
        }

        private void CmbQuickDemo_SelectedIndexChanged(object sender, EventArgs e)
        {
            switch (cmbQuickDemo.SelectedIndex)
            {
                case 1: // Kasir
                    txtUsername.Text = "199511112019031005";
                    txtPassword.Text = "password";
                    break;
                case 2: // Loket
                    txtUsername.Text = "199208082015021004";
                    txtPassword.Text = "password";
                    break;
                case 3: // Gudang
                    txtUsername.Text = "198705052010011003";
                    txtPassword.Text = "password";
                    break;
                case 4: // Kalapas
                    txtUsername.Text = "198002022005011002";
                    txtPassword.Text = "password";
                    break;
                case 5: // Superadmin Kanwil
                    txtUsername.Text = "197501011999031001";
                    txtPassword.Text = "password";
                    break;
            }
            lblError.Text = "";
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

using System;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
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
            this.Size = new Size(500, 410);
            this.StartPosition = FormStartPosition.CenterParent;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(15, 23, 42);

            Label lblTitle = new Label {
                Text = "🔒 OTORISASI SIDIK JARI BIOMETRIK",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12f, FontStyle.Bold),
                Location = new Point(20, 18),
                Width = 445,
                Height = 28,
                TextAlign = ContentAlignment.MiddleCenter
            };
            this.Controls.Add(lblTitle);

            Label lbl = new Label {
                Text = prompt,
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 9.5f),
                Location = new Point(20, 48),
                Width = 445,
                Height = 40,
                TextAlign = ContentAlignment.TopCenter
            };
            this.Controls.Add(lbl);

            Panel pnlPreviewBorder = new Panel {
                Location = new Point(190, 95),
                Width = 110,
                Height = 140,
                BackColor = Color.FromArgb(2, 132, 199),
                Padding = new Padding(2)
            };

            picPreview = new PictureBox {
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(30, 41, 59),
                SizeMode = PictureBoxSizeMode.Zoom
            };
            string logoPath = System.IO.Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "fingerprint_logo.png");
            if (System.IO.File.Exists(logoPath))
            {
                try {
                    byte[] bytes = System.IO.File.ReadAllBytes(logoPath);
                    using (System.IO.MemoryStream ms = new System.IO.MemoryStream(bytes))
                    {
                        picPreview.Image = new Bitmap(ms);
                    }
                } catch { }
            }
            pnlPreviewBorder.Controls.Add(picPreview);
            this.Controls.Add(pnlPreviewBorder);

            lblStatusText = new Label {
                Text = "● Sensor Siaga: Tempelkan jari Anda...",
                ForeColor = Color.FromArgb(56, 189, 248),
                BackColor = Color.FromArgb(30, 41, 59),
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Location = new Point(30, 250),
                Width = 425,
                Height = 36,
                TextAlign = ContentAlignment.MiddleCenter
            };
            this.Controls.Add(lblStatusText);

            Button btnCancel = new Button {
                Text = "Batal (Esc)",
                Location = new Point(195, 305),
                Width = 100,
                Height = 38,
                BackColor = Color.FromArgb(51, 65, 85),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnCancel.FlatAppearance.BorderSize = 0;
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
                    SoundHelper.PlayTouch();
                    lblStatusText.Text = "🟢 Sidik Jari Terverifikasi!";
                    lblStatusText.ForeColor = Color.FromArgb(52, 211, 153);
                    Application.DoEvents();
                    System.Threading.Thread.Sleep(150);
                    this.DialogResult = DialogResult.OK;
                    this.Close();
                }
                else
                {
                    SoundHelper.PlayWarning();
                    lblStatusText.Text = "Kualitas kurang jelas. Tempelkan jari lagi...";
                    lblStatusText.ForeColor = Color.FromArgb(248, 113, 113);
                    ReCapture();
                }
            }
            else
            {
                SoundHelper.PlayWarning();
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
}

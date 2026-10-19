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
}

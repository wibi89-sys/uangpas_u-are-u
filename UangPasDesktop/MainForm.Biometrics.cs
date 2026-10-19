using System;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
using DPUruNet;

namespace UangPasDesktop
{
    public partial class MainForm
    {
        #region BIOMETRIC READER INITIALIZATION
        private void InitializeBiometrics()
        {
            try
            {
                ReaderCollection readers = null;
                // Retry up to 5 times to handle RTE background service enumeration
                for (int i = 0; i < 5; i++)
                {
                    readers = ReaderCollection.GetReaders();
                    if (readers != null && readers.Count > 0) break;
                    System.Threading.Thread.Sleep(150);
                }

                if (readers != null && readers.Count > 0)
                {
                    currentReader = readers[0];
                    string prodName = currentReader.Description.Id.ProductName;
                    if (string.IsNullOrEmpty(prodName)) prodName = "U.are.U 4500";
                    lblScannerStatus.Text = "Scanner: " + prodName + " (Siap)";
                    lblScannerStatus.ForeColor = Color.FromArgb(52, 211, 153);
                    if (lblDiagDevice != null) lblDiagDevice.Text = "Perangkat: " + prodName;
                    if (lblDiagSerial != null) lblDiagSerial.Text = "Serial ID: " + currentReader.Description.SerialNumber;
                }
                else
                {
                    currentReader = null;
                    lblScannerStatus.Text = "Scanner: Belum Terbaca (Klik Uji)";
                    lblScannerStatus.ForeColor = Color.FromArgb(248, 113, 113);
                }
            }
            catch (Exception ex)
            {
                currentReader = null;
                lblScannerStatus.Text = "Scanner Error: " + ex.Message;
                lblScannerStatus.ForeColor = Color.FromArgb(248, 113, 113);
            }
        }
        #endregion

        #region BIOMETRIC UTILITIES & CONVERSION
        public static Bitmap Create24bppBitmapStatic(byte[] bytes, int width, int height)
        {
            try
            {
                byte[] rgbBytes = new byte[bytes.Length * 3];
                for (int i = 0; i <= bytes.Length - 1; i++)
                {
                    rgbBytes[(i * 3)] = bytes[i];
                    rgbBytes[(i * 3) + 1] = bytes[i];
                    rgbBytes[(i * 3) + 2] = bytes[i];
                }
                Bitmap bmp = new Bitmap(width, height, PixelFormat.Format24bppRgb);
                BitmapData data = bmp.LockBits(new Rectangle(0, 0, bmp.Width, bmp.Height), ImageLockMode.WriteOnly, PixelFormat.Format24bppRgb);
                for (int i = 0; i <= bmp.Height - 1; i++)
                {
                    IntPtr p = new IntPtr(data.Scan0.ToInt64() + data.Stride * i);
                    System.Runtime.InteropServices.Marshal.Copy(rgbBytes, i * bmp.Width * 3, p, bmp.Width * 3);
                }
                bmp.UnlockBits(data);
                return bmp;
            }
            catch
            {
                return null;
            }
        }

        public static Fmd ParseFmdFromTemplate(string tXml)
        {
            if (string.IsNullOrEmpty(tXml)) return null;
            tXml = tXml.Trim();

            // 1. Try standard Fmd.DeserializeXml (DPUruNet native FMD XML format)
            try
            {
                Fmd fmd = Fmd.DeserializeXml(tXml);
                if (fmd != null && fmd.Views != null && fmd.Views.Count > 0) return fmd;
            }
            catch { }

            // 2. Try replacing <Fid> root tag with <Fmd> in case XML root was mislabeled
            try
            {
                if (tXml.Contains("<Fid") || tXml.Contains("</Fid>"))
                {
                    string replaced = tXml.Replace("<Fid>", "<Fmd>").Replace("</Fid>", "</Fmd>").Replace("<Fid ", "<Fmd ");
                    Fmd fmd = Fmd.DeserializeXml(replaced);
                    if (fmd != null && fmd.Views != null && fmd.Views.Count > 0) return fmd;
                }
            }
            catch { }

            // 3. Try extracting raw <Bytes>...</Bytes> and importing via ANSI/ISO Importer
            try
            {
                int bStart = tXml.IndexOf("<Bytes>");
                int bEnd = tXml.IndexOf("</Bytes>");
                if (bStart >= 0 && bEnd > bStart)
                {
                    string b64 = tXml.Substring(bStart + 7, bEnd - (bStart + 7)).Trim();
                    byte[] raw = Convert.FromBase64String(b64);
                    var imp = Importer.ImportFmd(raw, Constants.Formats.Fmd.ANSI, Constants.Formats.Fmd.ANSI);
                    if (imp.ResultCode == Constants.ResultCode.DP_SUCCESS && imp.Data != null) return imp.Data;

                    var impIso = Importer.ImportFmd(raw, Constants.Formats.Fmd.ISO, Constants.Formats.Fmd.ANSI);
                    if (impIso.ResultCode == Constants.ResultCode.DP_SUCCESS && impIso.Data != null) return impIso.Data;
                }
            }
            catch { }

            // 4. Try raw Base64 string directly (if template is stored as pure base64 without XML wrapper)
            try
            {
                if (!tXml.StartsWith("<"))
                {
                    byte[] raw = Convert.FromBase64String(tXml);
                    var imp = Importer.ImportFmd(raw, Constants.Formats.Fmd.ANSI, Constants.Formats.Fmd.ANSI);
                    if (imp.ResultCode == Constants.ResultCode.DP_SUCCESS && imp.Data != null) return imp.Data;

                    var impIso = Importer.ImportFmd(raw, Constants.Formats.Fmd.ISO, Constants.Formats.Fmd.ANSI);
                    if (impIso.ResultCode == Constants.ResultCode.DP_SUCCESS && impIso.Data != null) return impIso.Data;
                }
            }
            catch { }

            // 5. Try true Fid image format (raw pixel data converted to FMD minutiae)
            try
            {
                if (tXml.Contains("<Fid") || tXml.StartsWith("<Fid"))
                {
                    Fid fid = Fid.DeserializeXml(tXml);
                    if (fid != null)
                    {
                        var fe = FeatureExtraction.CreateFmdFromFid(fid, Constants.Formats.Fmd.ANSI);
                        if (fe.ResultCode == Constants.ResultCode.DP_SUCCESS && fe.Data != null) return fe.Data;
                    }
                }
            }
            catch { }

            return null;
        }
        #endregion
    }
}

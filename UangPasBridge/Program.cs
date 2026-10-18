using System;
using System.IO;
using System.Net;
using System.Text;
using System.Drawing;
using System.Drawing.Imaging;
using System.Threading;
using System.Collections.Generic;
using System.Web.Script.Serialization;
using DPUruNet;

namespace UangPasBridge
{
    class Program
    {
        private static int port = 8080;
        private static HttpListener listener;
        private static JavaScriptSerializer serializer = new JavaScriptSerializer();
        private static object syncLock = new object();
        private static volatile bool isCapturing = false;
        private static string cachedSerial = "U.are.U 4500";
        private static string cachedProduct = "DigitalPersona U.are.U 4500";

        static void Main(string[] args)
        {
            Console.Title = "UangPas Biometric Hardware Bridge - U.are.U 4500";
            Console.ForegroundColor = ConsoleColor.Cyan;
            Console.WriteLine("===============================================================");
            Console.WriteLine("         UANGPAS DOMPET DIGITAL - HARDWARE BRIDGE              ");
            Console.WriteLine("           DigitalPersona U.are.U 4500 Service                 ");
            Console.WriteLine("===============================================================");
            Console.ResetColor();

            // Check scanner availability on startup
            try
            {
                ReaderCollection readers = ReaderCollection.GetReaders();
                if (readers.Count > 0)
                {
                    Console.ForegroundColor = ConsoleColor.Green;
                    Console.WriteLine("[INFO] Terdeteksi {0} perangkat fingerprint:", readers.Count);
                    foreach (Reader r in readers)
                    {
                        Console.WriteLine("       - Serial: {0} ({1})", r.Description.SerialNumber, r.Description.Name);
                    }
                    Console.ResetColor();
                }
                else
                {
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine("[WARN] Tidak ada perangkat fingerprint terdeteksi. Silakan colokkan U.are.U 4500.");
                    Console.ResetColor();
                }
            }
            catch (Exception ex)
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("[ERROR] Gagal inisialisasi SDK: " + ex.Message);
                Console.ResetColor();
            }

            // Start HTTP listener
            StartHttpServer();
        }

        private static void StartHttpServer()
        {
            listener = new HttpListener();
            string prefix = string.Format("http://localhost:{0}/", port);
            string prefixIp = string.Format("http://127.0.0.1:{0}/", port);

            try
            {
                listener.Prefixes.Add(prefix);
                listener.Prefixes.Add(prefixIp);
                listener.Start();

                Console.ForegroundColor = ConsoleColor.Green;
                Console.WriteLine("\n[BRIDGE ONLINE] Listening on {0} and {1}", prefix, prefixIp);
                Console.WriteLine("[INFO] Tekan Ctrl+C untuk menghentikan service.\n");
                Console.ResetColor();

                while (listener.IsListening)
                {
                    try
                    {
                        HttpListenerContext context = listener.GetContext();
                        ThreadPool.QueueUserWorkItem((c) => ProcessRequest((HttpListenerContext)c), context);
                    }
                    catch (HttpListenerException)
                    {
                        break;
                    }
                }
            }
            catch (Exception ex)
            {
                Console.ForegroundColor = ConsoleColor.Red;
                Console.WriteLine("[FATAL] Gagal memulai HTTP Listener: " + ex.Message);
                Console.WriteLine("Coba jalankan command prompt sebagai Administrator jika ada izin port terblokir.");
                Console.ResetColor();
                Console.ReadLine();
            }
        }

        private static void ProcessRequest(HttpListenerContext context)
        {
            HttpListenerRequest request = context.Request;
            HttpListenerResponse response = context.Response;

            // CORS headers for all origins
            response.AddHeader("Access-Control-Allow-Origin", "*");
            response.AddHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
            response.AddHeader("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With");
            response.ContentType = "application/json; charset=utf-8";

            if (request.HttpMethod == "OPTIONS")
            {
                response.StatusCode = 200;
                response.Close();
                return;
            }

            string rawUrl = request.Url.AbsolutePath.ToLowerInvariant();
            Console.WriteLine("[{0}] {1} {2}", DateTime.Now.ToString("HH:mm:ss"), request.HttpMethod, rawUrl);

            Dictionary<string, object> responseData = new Dictionary<string, object>();

            try
            {
                if ((rawUrl == "/" || rawUrl == "/index.html" || rawUrl == "/dashboard") && request.HttpMethod == "GET")
                {
                    string htmlPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "dashboard.html");
                    string htmlContent = "";
                    if (File.Exists(htmlPath))
                    {
                        htmlContent = File.ReadAllText(htmlPath, Encoding.UTF8);
                    }
                    else
                    {
                        htmlContent = "<!DOCTYPE html><html><body><h1>UangPas Hardware Bridge Online</h1><p>Status: Ready</p></body></html>";
                    }
                    byte[] htmlBytes = Encoding.UTF8.GetBytes(htmlContent);
                    response.ContentType = "text/html; charset=utf-8";
                    response.ContentLength64 = htmlBytes.Length;
                    using (Stream outStream = response.OutputStream)
                    {
                        outStream.Write(htmlBytes, 0, htmlBytes.Length);
                    }
                    response.Close();
                    return;
                }
                else if (rawUrl == "/api/status" && request.HttpMethod == "GET")
                {
                    responseData = HandleStatus();
                }
                else if (rawUrl == "/api/capture" && request.HttpMethod == "POST")
                {
                    string body = ReadBody(request);
                    int timeout = 10000;
                    if (!string.IsNullOrEmpty(body))
                    {
                        try {
                            var parsed = serializer.Deserialize<Dictionary<string, object>>(body);
                            if (parsed.ContainsKey("timeout")) timeout = Convert.ToInt32(parsed["timeout"]);
                        } catch { }
                    }
                    responseData = HandleCapture(timeout);
                }
                else if (rawUrl == "/api/enroll/combine" && request.HttpMethod == "POST")
                {
                    string body = ReadBody(request);
                    responseData = HandleEnrollCombine(body);
                }
                else if (rawUrl == "/api/verify" && request.HttpMethod == "POST")
                {
                    string body = ReadBody(request);
                    responseData = HandleVerify(body);
                }
                else if (rawUrl == "/api/identify" && request.HttpMethod == "POST")
                {
                    string body = ReadBody(request);
                    responseData = HandleIdentify(body);
                }
                else
                {
                    response.StatusCode = 404;
                    responseData["success"] = false;
                    responseData["message"] = "Endpoint not found: " + rawUrl;
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                responseData["success"] = false;
                responseData["message"] = "Internal error: " + ex.Message;
            }

            string json = serializer.Serialize(responseData);
            byte[] buffer = Encoding.UTF8.GetBytes(json);
            response.ContentLength64 = buffer.Length;
            using (Stream output = response.OutputStream)
            {
                output.Write(buffer, 0, buffer.Length);
            }
            response.Close();
        }

        private static string ReadBody(HttpListenerRequest request)
        {
            using (var reader = new StreamReader(request.InputStream, request.ContentEncoding))
            {
                return reader.ReadToEnd();
            }
        }

        private static Reader GetPrimaryReader()
        {
            ReaderCollection readers = ReaderCollection.GetReaders();
            if (readers.Count == 0) return null;
            return readers[0];
        }

        private static CaptureResult DoCapture(Reader reader, int timeoutMs)
        {
            AutoResetEvent captureEvt = new AutoResetEvent(false);
            CaptureResult captureResult = null;

            Reader.CaptureCallback onCaptured = delegate(CaptureResult cr)
            {
                captureResult = cr;
                captureEvt.Set();
            };

            reader.On_Captured += onCaptured;
            try
            {
                reader.GetStatus();
                int resolution = 500;
                if (reader.Capabilities != null && reader.Capabilities.Resolutions != null && reader.Capabilities.Resolutions.Length > 0)
                {
                    resolution = reader.Capabilities.Resolutions[0];
                }

                Constants.ResultCode capRes = reader.CaptureAsync(
                    Constants.Formats.Fid.ANSI,
                    Constants.CaptureProcessing.DP_IMG_PROC_DEFAULT,
                    resolution
                );

                if (capRes != Constants.ResultCode.DP_SUCCESS)
                {
                    return new CaptureResult(capRes, Constants.CaptureQuality.DP_QUALITY_CANCELED, 0, null);
                }

                bool signaled = captureEvt.WaitOne(timeoutMs);
                if (!signaled)
                {
                    try { reader.CancelCapture(); } catch { }
                    return new CaptureResult(Constants.ResultCode.DP_FAILURE, Constants.CaptureQuality.DP_QUALITY_TIMED_OUT, 0, null);
                }

                return captureResult;
            }
            catch (Exception ex)
            {
                Console.WriteLine("[ERROR DoCapture] " + ex.Message);
                return new CaptureResult(Constants.ResultCode.DP_DEVICE_FAILURE, Constants.CaptureQuality.DP_QUALITY_CANCELED, 0, null);
            }
            finally
            {
                reader.On_Captured -= onCaptured;
            }
        }

        private static Dictionary<string, object> HandleStatus()
        {
            var result = new Dictionary<string, object>();
            if (isCapturing)
            {
                result["success"] = true;
                result["online"] = true;
                result["readerConnected"] = true;
                result["serialNumber"] = cachedSerial;
                result["productName"] = cachedProduct;
                result["message"] = "Scanner sedang aktif membaca sidik jari...";
                return result;
            }

            lock (syncLock)
            {
                Reader reader = GetPrimaryReader();
                if (reader == null)
                {
                    result["success"] = true;
                    result["online"] = true;
                    result["readerConnected"] = false;
                    result["message"] = "Bridge aktif, tetapi scanner belum terhubung.";
                    return result;
                }

                cachedSerial = reader.Description.SerialNumber;
                cachedProduct = string.IsNullOrEmpty(reader.Description.Name) ? (reader.Description.Id != null && !string.IsNullOrEmpty(reader.Description.Id.ProductName) ? reader.Description.Id.ProductName : "U.are.U 4500") : reader.Description.Name;

                result["success"] = true;
                result["online"] = true;
                result["readerConnected"] = true;
                result["serialNumber"] = cachedSerial;
                result["productName"] = cachedProduct;
                result["message"] = "Scanner siap digunakan.";
            }
            return result;
        }

        private static Dictionary<string, object> HandleCapture(int timeoutMs)
        {
            var result = new Dictionary<string, object>();
            lock (syncLock)
            {
                Reader reader = GetPrimaryReader();
                if (reader == null)
                {
                    result["success"] = false;
                    result["message"] = "Scanner U.are.U tidak ditemukan. Harap pastikan kabel USB tercolok.";
                    return result;
                }

                Constants.ResultCode openRes = reader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (openRes != Constants.ResultCode.DP_SUCCESS)
                {
                    result["success"] = false;
                    result["message"] = "Gagal membuka scanner: " + openRes.ToString();
                    return result;
                }

                isCapturing = true;
                try
                {
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine("[ACTION] Menunggu sidik jari ditempelkan (timeout: {0}s)...", timeoutMs / 1000);
                    Console.ResetColor();

                    CaptureResult cr = DoCapture(reader, timeoutMs);

                    if (cr.ResultCode != Constants.ResultCode.DP_SUCCESS)
                    {
                        result["success"] = false;
                        result["message"] = "Gagal membaca sidik jari: " + cr.ResultCode.ToString();
                        return result;
                    }

                    if (cr.Quality != Constants.CaptureQuality.DP_QUALITY_GOOD)
                    {
                        result["success"] = false;
                        result["message"] = "Kualitas sidik jari kurang baik: " + cr.Quality.ToString() + ". Coba tempelkan jari kembali.";
                        return result;
                    }

                    // Extract FMD
                    DataResult<Fmd> fmdRes = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                    if (fmdRes.ResultCode != Constants.ResultCode.DP_SUCCESS || fmdRes.Data == null)
                    {
                        result["success"] = false;
                        result["message"] = "Gagal mengekstraksi fitur sidik jari (FMD): " + fmdRes.ResultCode.ToString();
                        return result;
                    }

                    string fmdXml = Fmd.SerializeXml(fmdRes.Data);
                    string imageBase64 = FidToPngBase64(cr.Data);

                    Console.ForegroundColor = ConsoleColor.Green;
                    Console.WriteLine("[SUCCESS] Sidik jari berhasil dibaca!");
                    Console.ResetColor();

                    result["success"] = true;
                    result["template"] = fmdXml;
                    result["image"] = "data:image/png;base64," + imageBase64;
                    result["quality"] = "good";
                    result["message"] = "Sidik jari berhasil dibaca.";
                }
                finally
                {
                    isCapturing = false;
                    reader.Dispose();
                }
            }
            return result;
        }

        private static Dictionary<string, object> HandleEnrollCombine(string body)
        {
            var result = new Dictionary<string, object>();
            if (string.IsNullOrEmpty(body))
            {
                result["success"] = false;
                result["message"] = "Request body kosong.";
                return result;
            }

            try
            {
                var req = serializer.Deserialize<Dictionary<string, object>>(body);
                if (!req.ContainsKey("templates"))
                {
                    result["success"] = false;
                    result["message"] = "Parameter 'templates' (array berisi template sidik jari) diperlukan.";
                    return result;
                }

                System.Collections.ArrayList list = (System.Collections.ArrayList)req["templates"];
                if (list.Count < 2)
                {
                    result["success"] = false;
                    result["message"] = "Dibutuhkan minimal 2 s/d 4 sampel sidik jari untuk membuat master template pendaftaran.";
                    return result;
                }

                List<Fmd> fmdList = new List<Fmd>();
                foreach (string tXml in list)
                {
                    Fmd fmd = Fmd.DeserializeXml(tXml);
                    fmdList.Add(fmd);
                }

                DataResult<Fmd> enrollRes = Enrollment.CreateEnrollmentFmd(Constants.Formats.Fmd.ANSI, fmdList);
                if (enrollRes.ResultCode != Constants.ResultCode.DP_SUCCESS || enrollRes.Data == null)
                {
                    result["success"] = false;
                    result["message"] = "Gagal membuat template master pendaftaran: " + enrollRes.ResultCode.ToString() + ". Pastikan jari yang sama digunakan pada setiap pemindaian.";
                    return result;
                }

                string masterXml = Fmd.SerializeXml(enrollRes.Data);
                result["success"] = true;
                result["masterTemplate"] = masterXml;
                result["message"] = "Template pendaftaran sidik jari berhasil dibuat.";
            }
            catch (Exception ex)
            {
                result["success"] = false;
                result["message"] = "Gagal menggabungkan template: " + ex.Message;
            }
            return result;
        }

        private static Dictionary<string, object> HandleVerify(string body)
        {
            var result = new Dictionary<string, object>();
            if (string.IsNullOrEmpty(body))
            {
                result["success"] = false;
                result["message"] = "Request body kosong.";
                return result;
            }

            string storedTemplateXml = null;
            int timeout = 10000;
            try
            {
                var req = serializer.Deserialize<Dictionary<string, object>>(body);
                if (req.ContainsKey("template")) storedTemplateXml = req["template"].ToString();
                if (req.ContainsKey("timeout")) timeout = Convert.ToInt32(req["timeout"]);
            }
            catch (Exception ex)
            {
                result["success"] = false;
                result["message"] = "Format JSON salah: " + ex.Message;
                return result;
            }

            if (string.IsNullOrEmpty(storedTemplateXml))
            {
                result["success"] = false;
                result["message"] = "Parameter 'template' (sidik jari terdaftar) harus disertakan.";
                return result;
            }

            Fmd storedFmd = null;
            try
            {
                if (storedTemplateXml.Contains("<Fid") || storedTemplateXml.StartsWith("<Fid"))
                {
                    try
                    {
                        Fid fid = Fid.DeserializeXml(storedTemplateXml);
                        var fe = FeatureExtraction.CreateFmdFromFid(fid, Constants.Formats.Fmd.ANSI);
                        if (fe.ResultCode == Constants.ResultCode.DP_SUCCESS) storedFmd = fe.Data;
                    }
                    catch { }
                }
                else
                {
                    storedFmd = Fmd.DeserializeXml(storedTemplateXml);
                }
            }
            catch (Exception ex)
            {
                result["success"] = false;
                result["message"] = "Template sidik jari tersimpan tidak valid: " + ex.Message;
                return result;
            }

            if (storedFmd == null)
            {
                result["success"] = false;
                result["message"] = "Gagal memproses template sidik jari tersimpan.";
                return result;
            }

            lock (syncLock)
            {
                Reader reader = GetPrimaryReader();
                if (reader == null)
                {
                    result["success"] = false;
                    result["message"] = "Scanner U.are.U tidak terhubung.";
                    return result;
                }

                Constants.ResultCode openRes = reader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                if (openRes != Constants.ResultCode.DP_SUCCESS)
                {
                    result["success"] = false;
                    result["message"] = "Gagal membuka scanner: " + openRes.ToString();
                    return result;
                }

                isCapturing = true;
                try
                {
                    Console.ForegroundColor = ConsoleColor.Yellow;
                    Console.WriteLine("[VERIFIKASI] Tempelkan sidik jari nasabah untuk verifikasi transaksi...");
                    Console.ResetColor();

                    CaptureResult cr = DoCapture(reader, timeout);

                    if (cr.ResultCode != Constants.ResultCode.DP_SUCCESS)
                    {
                        result["success"] = false;
                        result["verified"] = false;
                        result["message"] = "Waktu verifikasi habis atau scanner dibatalkan.";
                        return result;
                    }

                    DataResult<Fmd> liveFmdRes = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                    if (liveFmdRes.ResultCode != Constants.ResultCode.DP_SUCCESS || liveFmdRes.Data == null)
                    {
                        result["success"] = false;
                        result["verified"] = false;
                        result["message"] = "Gagal membaca fitur sidik jari langsung.";
                        return result;
                    }

                    CompareResult compRes = Comparison.Compare(liveFmdRes.Data, 0, storedFmd, 0);
                    int falsematchProbability = compRes.Score;
                    // Standard threshold FAR 1/100,000 (21474)
                    int threshold = 2147483647 / 100000;
                    bool isMatched = (falsematchProbability < threshold);

                    string imageBase64 = FidToPngBase64(cr.Data);

                    result["success"] = true;
                    result["verified"] = isMatched;
                    result["score"] = falsematchProbability;
                    result["threshold"] = threshold;
                    result["image"] = "data:image/png;base64," + imageBase64;

                    if (isMatched)
                    {
                        Console.ForegroundColor = ConsoleColor.Green;
                        Console.WriteLine("[VERIFIKASI SUKSES] Sidik jari cocok! (Score: {0})", falsematchProbability);
                        Console.ResetColor();
                        result["message"] = "Otentikasi sidik jari berhasil. Transaksi disetujui.";
                    }
                    else
                    {
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.WriteLine("[VERIFIKASI GAGAL] Sidik jari TIDAK cocok! (Score: {0})", falsematchProbability);
                        Console.ResetColor();
                        result["message"] = "Sidik jari tidak cocok dengan pemilik rekening.";
                    }
                }
                finally
                {
                    isCapturing = false;
                    reader.Dispose();
                }
            }

            return result;
        }

        private static Dictionary<string, object> HandleIdentify(string body)
        {
            var result = new Dictionary<string, object>();
            int timeout = 10000;
            System.Collections.ArrayList candidateList = null;

            if (!string.IsNullOrEmpty(body))
            {
                try
                {
                    var req = serializer.Deserialize<Dictionary<string, object>>(body);
                    if (req != null)
                    {
                        if (req.ContainsKey("timeout")) timeout = Convert.ToInt32(req["timeout"]);
                        if (req.ContainsKey("candidates") && req["candidates"] is System.Collections.ArrayList)
                        {
                            candidateList = (System.Collections.ArrayList)req["candidates"];
                        }
                    }
                }
                catch { }
            }

            // Auto-fetch active inmates from Laravel API if candidates list is missing or empty
            if (candidateList == null || candidateList.Count == 0)
            {
                try
                {
                    using (var wc = new WebClient())
                    {
                        wc.Encoding = Encoding.UTF8;
                        string json = wc.DownloadString("http://127.0.0.1:8000/api/v1/pos/inmates");
                        var apiResp = serializer.Deserialize<Dictionary<string, object>>(json);
                        if (apiResp != null && apiResp.ContainsKey("data") && apiResp["data"] is System.Collections.ArrayList)
                        {
                            candidateList = (System.Collections.ArrayList)apiResp["data"];
                        }
                    }
                }
                catch { }
            }

            if (candidateList == null) candidateList = new System.Collections.ArrayList();

            List<Fmd> fmdCandidates = new List<Fmd>();
            List<object> candidateMeta = new List<object>();

            foreach (object item in candidateList)
            {
                if (item is Dictionary<string, object>)
                {
                    var c = (Dictionary<string, object>)item;
                    if (c.ContainsKey("template") && c["template"] != null)
                    {
                        string tXml = c["template"].ToString();
                        if (!string.IsNullOrEmpty(tXml))
                        {
                            try
                            {
                                Fmd fmd = null;
                                if (tXml.Contains("<Fid") || tXml.StartsWith("<Fid"))
                                {
                                    try
                                    {
                                        Fid fid = Fid.DeserializeXml(tXml);
                                        var fe = FeatureExtraction.CreateFmdFromFid(fid, Constants.Formats.Fmd.ANSI);
                                        if (fe.ResultCode == Constants.ResultCode.DP_SUCCESS) fmd = fe.Data;
                                    }
                                    catch { }
                                }
                                else
                                {
                                    fmd = Fmd.DeserializeXml(tXml);
                                }

                                if (fmd != null)
                                {
                                    fmdCandidates.Add(fmd);
                                    candidateMeta.Add(c);
                                }
                            }
                            catch { }
                        }
                    }
                }
            }

            try
            {
                lock (syncLock)
                {
                    Reader reader = GetPrimaryReader();
                    if (reader == null)
                    {
                        result["success"] = false;
                        result["message"] = "Scanner U.are.U tidak terhubung. Pastikan kabel USB terpasang.";
                        return result;
                    }

                    Constants.ResultCode openRes = reader.Open(Constants.CapturePriority.DP_PRIORITY_COOPERATIVE);
                    if (openRes != Constants.ResultCode.DP_SUCCESS)
                    {
                        result["success"] = false;
                        result["message"] = "Gagal membuka scanner: " + openRes.ToString();
                        return result;
                    }

                    isCapturing = true;
                    try
                    {
                        Console.ForegroundColor = ConsoleColor.Yellow;
                        Console.WriteLine("[IDENTIFIKASI 1:N] Menunggu sidik jari ditempelkan pada sensor (kandidat database: {0})...", fmdCandidates.Count);
                        Console.ResetColor();

                        CaptureResult cr = DoCapture(reader, timeout);

                        if (cr.ResultCode != Constants.ResultCode.DP_SUCCESS)
                        {
                            result["success"] = false;
                            result["identified"] = false;
                            result["message"] = "Waktu identifikasi habis atau scanner dibatalkan.";
                            return result;
                        }

                        DataResult<Fmd> liveFmdRes = FeatureExtraction.CreateFmdFromFid(cr.Data, Constants.Formats.Fmd.ANSI);
                        if (liveFmdRes.ResultCode != Constants.ResultCode.DP_SUCCESS || liveFmdRes.Data == null)
                        {
                            result["success"] = false;
                            result["identified"] = false;
                            result["message"] = "Gagal mengekstrak fitur sidik jari.";
                            return result;
                        }

                        string imageBase64 = FidToPngBase64(cr.Data);
                        string liveFmdXml = Fmd.SerializeXml(liveFmdRes.Data);
                        result["image"] = "data:image/png;base64," + imageBase64;
                        result["captured_template"] = liveFmdXml;

                        int threshold = 2147483647 / 100000;
                        IdentifyResult identRes = null;

                        if (fmdCandidates.Count > 0)
                        {
                            identRes = Comparison.Identify(liveFmdRes.Data, 0, fmdCandidates, threshold, 1);
                        }

                        if (identRes != null && identRes.ResultCode == Constants.ResultCode.DP_SUCCESS && identRes.Indexes.Length > 0 && identRes.Indexes[0].Length > 0)
                        {
                            int matchIndex = identRes.Indexes[0][0];
                            var matchedCandidate = (Dictionary<string, object>)candidateMeta[matchIndex];

                            Console.ForegroundColor = ConsoleColor.Green;
                            Console.WriteLine("[IDENTIFIKASI SUKSES] WBP Ditemukan: {0}", matchedCandidate.ContainsKey("nama") ? matchedCandidate["nama"] : matchIndex.ToString());
                            Console.ResetColor();

                            result["success"] = true;
                            result["identified"] = true;
                            result["candidate"] = matchedCandidate;
                            if (matchedCandidate.ContainsKey("id")) result["inmate_id"] = matchedCandidate["id"];
                            else if (matchedCandidate.ContainsKey("inmate_id")) result["inmate_id"] = matchedCandidate["inmate_id"];
                            result["message"] = "Sidik jari cocok dengan WBP " + (matchedCandidate.ContainsKey("nama") ? matchedCandidate["nama"].ToString() : "");
                        }
                        else
                        {
                            Console.ForegroundColor = ConsoleColor.Yellow;
                            Console.WriteLine("[IDENTIFIKASI] Sidik jari terbaca, namun belum cocok dengan database.");
                            Console.ResetColor();

                            result["success"] = true;
                            result["identified"] = false;
                            result["message"] = "Sidik jari berhasil dibaca, namun belum terdaftar pada akun WBP manapun.";
                        }
                    }
                    finally
                    {
                        isCapturing = false;
                        reader.Dispose();
                    }
                }
            }
            catch (Exception ex)
            {
                result["success"] = false;
                result["message"] = "Error identifikasi: " + ex.Message;
            }

            return result;
        }

        private static string FidToPngBase64(Fid fid)
        {
            try
            {
                if (fid == null || fid.Views == null || fid.Views.Count == 0) return "";
                var view = fid.Views[0];
                int width = view.Width;
                int height = view.Height;
                byte[] raw = view.RawImage;

                using (Bitmap bmp = new Bitmap(width, height, PixelFormat.Format8bppIndexed))
                {
                    ColorPalette pal = bmp.Palette;
                    for (int i = 0; i < 256; i++)
                    {
                        pal.Entries[i] = Color.FromArgb(i, i, i);
                    }
                    bmp.Palette = pal;

                    BitmapData data = bmp.LockBits(new Rectangle(0, 0, width, height), ImageLockMode.WriteOnly, PixelFormat.Format8bppIndexed);
                    System.Runtime.InteropServices.Marshal.Copy(raw, 0, data.Scan0, raw.Length);
                    bmp.UnlockBits(data);

                    using (MemoryStream ms = new MemoryStream())
                    {
                        bmp.Save(ms, ImageFormat.Png);
                        return Convert.ToBase64String(ms.ToArray());
                    }
                }
            }
            catch
            {
                return "";
            }
        }
    }
}

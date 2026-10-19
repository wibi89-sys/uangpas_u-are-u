using System;
using System.Net;
using System.Windows.Forms;

namespace UangPasDesktop
{
    static class Program
    {
        [MTAThread]
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
            Application.Run(new MainForm());
        }
    }
}

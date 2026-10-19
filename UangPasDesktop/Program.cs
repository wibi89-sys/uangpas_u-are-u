using System;
using System.Windows.Forms;

namespace UangPasDesktop
{
    static class Program
    {
        [STAThread]
        static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            bool useClassic = false;
            if (args != null)
            {
                foreach (string a in args)
                {
                    if (a.ToLower() == "--classic") useClassic = true;
                }
            }

            if (useClassic)
            {
                Application.Run(new MainForm());
            }
            else
            {
                Application.Run(new ModernMainForm());
            }
        }
    }
}

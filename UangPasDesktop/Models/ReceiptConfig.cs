using System;

namespace UangPasDesktop
{
    public class ReceiptConfig
    {
        public string StoreName { get; set; }
        public string Address { get; set; }
        public string Phone { get; set; }
        public string Footer { get; set; }
        public string PaperWidth { get; set; }
        public bool AutoPrint { get; set; }
        public bool ShowBalance { get; set; }

        public ReceiptConfig()
        {
            StoreName = "KOPERASI PENGAYOMAN LAPAS";
            Address = "Jalan Pemasyarakatan No. 1";
            Phone = "(021) 5523456";
            Footer = "Terima Kasih Atas Kunjungan Anda\nSimpan Struk Sebagai Bukti Belanja Sah\nUangPas - Zero Cash Solution";
            PaperWidth = "58mm";
            AutoPrint = false;
            ShowBalance = true;
        }
    }
}

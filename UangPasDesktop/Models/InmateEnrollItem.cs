using System;
using System.Collections.Generic;

namespace UangPasDesktop
{
    public class InmateEnrollItem
    {
        public Dictionary<string, object> Inmate { get; set; }

        public override string ToString()
        {
            if (Inmate == null) return "";
            string reg = Inmate.ContainsKey("no_registrasi") && Inmate["no_registrasi"] != null ? Inmate["no_registrasi"].ToString() : "-";
            string nama = Inmate.ContainsKey("nama") && Inmate["nama"] != null ? Inmate["nama"].ToString() : "-";
            string kamar = Inmate.ContainsKey("blok_kamar") && Inmate["blok_kamar"] != null ? Inmate["blok_kamar"].ToString() : "-";
            bool hasFp = Inmate.ContainsKey("has_fingerprint") && Convert.ToBoolean(Inmate["has_fingerprint"]);
            string badge = hasFp ? "[✓ Terdaftar]" : "[⚪ BELUM REKAM]";
            return badge + "  " + reg + " - " + nama + " (" + kamar + ")";
        }
    }
}

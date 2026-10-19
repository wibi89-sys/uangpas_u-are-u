using System;
using System.Collections.Generic;

namespace UangPasDesktop.Models
{
    public class AuthUser
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string Nip { get; set; }
        public string Email { get; set; }
        public string Role { get; set; }
        public string RoleLabel { get; set; }
        public string UptCode { get; set; }

        public bool CanCheckout { get; set; }
        public bool CanTopup { get; set; }
        public bool CanEnroll { get; set; }
        public bool CanSettings { get; set; }

        public AuthUser()
        {
            Name = "Petugas Kasir";
            Role = "kasir";
            RoleLabel = "Kasir POS";
            CanCheckout = true;
            CanTopup = true;
            CanEnroll = false;
            CanSettings = false;
        }

        public static AuthUser FromDictionary(Dictionary<string, object> dict)
        {
            AuthUser user = new AuthUser();
            if (dict == null) return user;

            if (dict.ContainsKey("id") && dict["id"] != null)
                user.Id = Convert.ToInt32(dict["id"]);
            if (dict.ContainsKey("name") && dict["name"] != null)
                user.Name = dict["name"].ToString();
            if (dict.ContainsKey("nip") && dict["nip"] != null)
                user.Nip = dict["nip"].ToString();
            if (dict.ContainsKey("email") && dict["email"] != null)
                user.Email = dict["email"].ToString();
            if (dict.ContainsKey("role") && dict["role"] != null)
                user.Role = dict["role"].ToString();
            if (dict.ContainsKey("role_label") && dict["role_label"] != null)
                user.RoleLabel = dict["role_label"].ToString();
            if (dict.ContainsKey("upt_code") && dict["upt_code"] != null)
                user.UptCode = dict["upt_code"].ToString();

            if (dict.ContainsKey("permissions") && dict["permissions"] is Dictionary<string, object>)
            {
                Dictionary<string, object> perms = (Dictionary<string, object>)dict["permissions"];
                if (perms.ContainsKey("can_checkout"))
                    user.CanCheckout = Convert.ToBoolean(perms["can_checkout"]);
                if (perms.ContainsKey("can_topup"))
                    user.CanTopup = Convert.ToBoolean(perms["can_topup"]);
                if (perms.ContainsKey("can_enroll"))
                    user.CanEnroll = Convert.ToBoolean(perms["can_enroll"]);
                if (perms.ContainsKey("can_settings"))
                    user.CanSettings = Convert.ToBoolean(perms["can_settings"]);
            }
            else
            {
                // Fallback default permissions based on role
                bool isSuper = user.Role == "kanwil_admin" || user.Role == "admin_koperasi";
                user.CanCheckout = isSuper || user.Role == "kasir";
                user.CanTopup = isSuper || user.Role == "kasir" || user.Role == "petugas_loket";
                user.CanEnroll = isSuper || user.Role == "petugas_loket";
                user.CanSettings = isSuper;
            }

            return user;
        }
    }

    public static class AuthSession
    {
        public static AuthUser CurrentUser { get; set; }

        public static bool IsLoggedIn
        {
            get { return CurrentUser != null; }
        }

        public static void Clear()
        {
            CurrentUser = null;
        }
    }
}

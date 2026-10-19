using System;
using System.Collections.Generic;

namespace UangPasDesktop
{
    public class HeldBill
    {
        public string Id { get; set; }
        public DateTime Time { get; set; }
        public List<CartItem> Items { get; set; }
        public decimal TotalAmount { get; set; }
    }
}

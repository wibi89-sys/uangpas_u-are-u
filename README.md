# 🏛️ UangPas - Sistem Kasir POS & Biometrik Sidik Jari U.are.U 4500 (Lapas)

Sistem kasir koperasi dan dompet digital *cashless* khusus Lembaga Pemasyarakatan (Lapas) dengan otentikasi biometrik sidik jari optik **DigitalPersona U.are.U 4500** untuk transaksi Warga Binaan Pemasyarakatan (WBP).

---

## 🏗️ Arsitektur Sistem 2-Tier

Sistem dirancang dengan pemisahan peran yang andal, aman, dan tanpa kendala *driver sandbox*:

1. **Terminal Kasir POS & Biometrik (Desktop Native - C# WinForms)**:
   - Dikhususkan untuk petugas kasir di kantin / koperasi Lapas.
   - Terkoneksi langsung dan eksklusif dengan sensor sidik jari **DigitalPersona U.are.U 4500** via SDK resmi (`DPUruNet.dll`).
   - Fitur kasir lengkap:
     - 🔍 Pencarian produk instan & filter kategori
     - ⚡ Dukungan barcode scanner USB otomatis (Enter to add)
     - 👆 Pembayaran sidik jari WBP (ANSI/NIST biometric matching)
     - 👤 Pembayaran manual WBP (alternatif jika jari WBP terluka/diperban)
     - 🔄 Sinkronisasi data otomatis & antrian offline (*offline resilience*)
     - 🖨️ Cetak struk belanja thermal (ESC/POS compatible)

2. **Portal Manajemen & Mutasi (Web Portal - Laravel Backend)**:
   - Dikhususkan untuk petugas administrasi, pengelola koperasi, dan pejabat Lapas (tanpa ketergantungan perangkat keras USB).
   - Fitur admin:
     - 📊 Dashboard statistik & ringkasan saldo WBP
     - 👥 Manajemen Warga Binaan & Rekam Sidik Jari
     - 💳 Loket Top-Up & Integrasi Virtual Account Bank
     - 🛒 Katalog Barang, Update Harga, & Stok Masuk/Keluar
     - 📑 Laporan Transaksi & Audit Mutasi Kasir
     - 🔄 Sinkronisasi Data & Mutasi Antar-UPT (Pusat / Cabang)

---

## 📁 Struktur Direktori

* **`/UangPasDesktop`**: Aplikasi Kasir Desktop C# .NET dengan integrasi driver biometrik U.are.U 4500.
* **`/UangPasWeb`**: Portal Web Manajemen Lapas (Laravel) dan REST API backend untuk terminal kasir.
* **`/URU`**: Paket SDK dan Driver resmi DigitalPersona U.are.U 4500.

---

## 🚀 Cara Menjalankan

### Instalasi Mudah (Easy Install)
Cukup jalankan satu kali:
```cmd
00_INSTALLASI_MUDAH_UANGPAS.bat
```
Skrip ini akan memeriksa MySQL, mengonfigurasi Laravel, melakukan migrasi database, dan mengompilasi aplikasi desktop secara otomatis.

### Menjalankan Operasional Harian
* **Kasir Lapas**: Klik ganda `01_Buka_Kasir_POS_Lapas.bat` (langsung membuka terminal kasir desktop dan backend API).
* **Admin Lapas**: Klik ganda `02_Buka_Portal_Admin_Lapas.bat` (membuka portal web di browser `http://localhost:8000`).

---

## 🔒 Lisensi & Keamanan
Dikembangkan khusus untuk operasional koperasi dan keuangan Lembaga Pemasyarakatan (Lapas).

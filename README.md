# 🏛️ UangPas - Sistem Dompet Digital Biometrik & Buku Register D Elektronik (Lapas & Rutan)
### Solusi Terpadu Bebas Peredaran Uang Tunai (Zero Cash / Zero Halinar) - Ditjen Pemasyarakatan Kemenkumham RI

---

## 📌 Dokumentasi Resmi & Manual Book untuk Klien
Bagi pimpinan satuan kerja (Kalapas/Karutan/Ka. KPLP), pengelola kantin koperasi, dan tim pengembang, telah disediakan dokumen komprehensif berikut:

1. 📘 **[MANUAL_BOOK_UANGPAS.md](MANUAL_BOOK_UANGPAS.md)**:
   *Buku Panduan Operasional Lengkap untuk Petugas Loket Register D, Bendahara Koperasi, Operator EDC Kantin, Pengawas Kamtib, dan Pembebasan Narapidana.*
2. 🔌 **[DOKUMENTASI_API_INTEGRASI_KANTIN.md](DOKUMENTASI_API_INTEGRASI_KANTIN.md)**:
   *Panduan Teknis Integrasi Open API bagi Vendor / Aplikasi POS Kantin Pihak Ketiga yang ingin menerima pembayaran saldo non-tunai WBP via REST API.*
3. 🏛️ **[PROPOSAL_EKSEKUTIF_DAN_SOP_ZERO_HALINAR.md](PROPOSAL_EKSEKUTIF_DAN_SOP_ZERO_HALINAR.md)**:
   *Ringkasan Eksekutif, Landasan Regulasi Kemenkumham, Tata Kelola Rekening Escrow, dan Standard Operating Procedure (SOP) Resmi untuk Bahan Presentasi ke Pimpinan.*
4. ⚙️ **[PANDUAN_UJI_HARDWARE_UAREU.md](PANDUAN_UJI_HARDWARE_UAREU.md)**:
   *Petunjuk Teknis Instalasi Driver dan Pengujian Sensor Sidik Jari DigitalPersona U.are.U 4500.*

---

## 🏗️ Fitur Unggulan Sistem

### 1. Buku Register D Elektronik (Mini Core-Banking WBP)
* **Pencatatan Otomatis Standar Kemenkumham**: Menghilangkan buku tulis konvensional dan merekapitulasi setiap mutasi masuk (titipan keluarga/VA) dan keluar (belanja kantin).
* **Rekening Koran Resmi (Statement of Account A4)**: Lembar rekening koran ber-Kop Surat Kementerian Hukum dan HAM RI untuk audit keuangan.
* **Cetak Buku Tabungan Fisik (*Passbook*)**: Format pencetakan yang presisi untuk buku tabungan saku WBP.
* **Buku Jurnal Induk (Master Ledger)**: Rekapitulasi mutasi dana harian yang disahkan oleh Bendahara dan Ka. KPLP.
* **Slip Setoran Kas & Bukti Pengeluaran Bebas**: Cetak kwitansi resmi tanda terima titipan uang dan pengembalian sisa saldo saat narapidana bebas.

### 2. Terminal EDC Cepat Standalone & Biometrik U.are.U 4500
* **Non-Disruptive POS**: Kantin tidak perlu membuang software kasir retail eksisting mereka. Kasir cukup memasukkan total nominal di EDC UangPas dan memindai jari WBP.
* **Otentikasi Sidik Jari Presisi Tinggi**: Terintegrasi langsung dengan sensor optik **DigitalPersona U.are.U 4500** via service lokal `UangPasBridge` (Port 8080).
* **Validasi Limit Belanja Harian**: Mencegah monopoli barang di dalam blok hunian dengan pembatasan kuota belanja harian (*Daily Cap*).
* **Freeze Otomatis Pelanggaran Disiplin (Register F)**: WBP yang menjalani hukuman sel isolasi otomatis ditolak transaksi belanjanya.
* **Cetak Struk Thermal 58mm / 80mm**: Nota belanja bukti debet dengan rincian sisa saldo berjalan.

### 3. Rekonsiliasi & Settlement Merchant Kantin
* Rekapitulasi omzet per titik layanan (Kantin Koperasi Utama, Kantin Blok, Wartelsuspas, Laundry, Bimker).
* Cetak Berita Acara Rekonsiliasi & Pencairan Dana (Format A4) yang ditandatangani oleh Pengelola Stan, Bendahara Koperasi, dan Ka. KPLP.

### 4. Anjungan Cek Saldo Mandiri (Kiosk Self-Service)
* Layar sentuh / TV dinding di lorong blok hunian yang terhubung ke sensor IP **Solution X100C** atau barcode scanner.
* Suara audio bahasa Indonesia (*Text-to-Speech*) menyebutkan sisa saldo secara otomatis.
* Fitur perlindungan privasi dengan *auto-reset* dalam tempo 6 detik.

---

## 📁 Struktur Direktori Proyek

* **`/UangPasWeb`**: Backend Core-Banking Laravel 11, Web EDC Terminal, Panel Buku Register D, Rekonsiliasi Keuangan, dan Open API v1.
* **`/UangPasBridge`**: Layanan HTTP lokal C# .NET interfacing driver `DPUruNet.dll` (DigitalPersona U.are.U 4500) pada port 8080.
* **`/UangPasDesktop`**: Terminal Kasir POS Desktop Native WinForms alternatif.
* **`/URU`**: Paket driver USB resmi DigitalPersona U.are.U 4500 (32-bit & 64-bit).

---

## 🚀 Panduan Menjalankan Sistem

### 1. Instalasi Pertama Kali
Jalankan file batch:
```cmd
00_INSTALLASI_MUDAH_UANGPAS.bat
```
Skrip ini akan memeriksa database MySQL, mengonfigurasi environment Laravel, menjalankan migrasi tabel Register D & EDC, serta mempersiapkan dependensi.

### 2. Operasional Harian
* **Buka Portal Admin & Register D**: Jalankan `02_Buka_Portal_Admin_Lapas.bat` &rarr; Akses `http://localhost:8000`.
* **Buka Terminal EDC Kantin**: Akses `http://localhost:8000/edc`.
* **Buka Kiosk Cek Saldo Mandiri**: Akses `http://localhost:8000/kiosk`.
* **Uji Sensor Biometrik U.are.U 4500**: Jalankan `01_Uji_Sensor_UareU_4500.bat`.

---

## 🔒 Lisensi & Hak Cipta
Dikembangkan khusus untuk mendukung penguatan tata kelola keuangan bebas uang tunai di lingkungan Direktorat Jenderal Pemasyarakatan Kementerian Hukum dan Hak Asasi Manusia RI.

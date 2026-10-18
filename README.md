# 🏛️ UangPas - Sistem Kasir POS & Biometrik Sidik Jari U.are.U 4500 (Lapas)

Sistem kasir koperasi dan dompet digital *cashless* khusus Lembaga Pemasyarakatan (Lapas) yang mengintegrasikan otentikasi biometrik sidik jari optik **DigitalPersona U.are.U 4500** untuk transaksi Warga Binaan Pemasyarakatan (WBP).

---

## 🚀 Fitur Utama

1. **Otentikasi Sidik Jari ANSI/NIST**:
   - Verifikasi transaksi belanja kantin menggunakan sidik jari WBP secara *real-time*.
   - Mencegah peredaran uang tunai fisik dan pencurian identitas di lingkungan Lapas.
2. **Web Dashboard & POS (Laravel)**:
   - Manajemen data WBP (Blok, Kamar, NIK, No. Registrasi, Saldo, Limit Belanja Harian).
   - Katalog barang koperasi, stok, dan kasir penjualan.
   - Riwayat mutasi saldo dan rekap transaksi harian.
3. **Hardware Bridge & Desktop Client**:
   - Integrasi C# .NET dengan SDK resmi DigitalPersona (`DPUruNet.dll`).
   - Layanan penghubung scanner USB ke antarmuka web POS kasir.

---

## 📁 Struktur Direktori

* **`/UangPasWeb`**: Backend & Frontend Web Admin / Kasir (Laravel).
* **`/UangPasDesktop`**: Aplikasi Desktop C# untuk pengujian hardware dan modul POS standalone.
* **`/UangPasBridge`**: Layanan bridge scanner biometrik ke browser web.
* **`/URU`**: Paket SDK dan Driver resmi DigitalPersona U.are.U 4500.

---

## 🛠️ Panduan Menjalankan

### 1. Pengujian Scanner Sidik Jari
Klik ganda:
* `01_Uji_Sensor_UareU_4500.bat` untuk uji visual dan kecocokan sidik jari.
* `02_Buka_Kasir_POS_Lapas.bat` untuk membuka aplikasi kasir desktop.

### 2. Menjalankan Web Kasir & Admin (Laravel)
```bash
cd UangPasWeb
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
Akses di browser: `http://localhost:8000` (Login: `admin@lapas.go.id` / `password123`).

---

## 🔒 Lisensi & Keamanan
Dikembangkan khusus untuk operasional koperasi dan keuangan Lapas.

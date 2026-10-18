# Panduan Pengujian Scanner Sidik Jari DigitalPersona U.are.U 4500
**Project UangPas Dompet Digital Lapas**

---

### Status Perangkat Saat Ini
- **Status Hardware**: Terhubung (USB OK)
- **Model Reader**: `DigitalPersona U.are.U® 4500 Fingerprint Reader`
- **Serial Number**: `{E9FA1299-EC25-9E4B-AAAD-B7F30BAB3E52}`
- **Resolusi Optik**: `500 DPI` (ANSI/NIST Standard)
- **Kondisi Driver**: Driver RTE v2.3.1 aktif & siap

---

### Cara Menguji Scanner Sekarang

Tersedia **3 pilihan cara pengujian** yang sangat mudah:

#### Pilihan 1: Jalankan Tool Uji Khusus UangPas (Rekomendasi Utama)
Klik ganda file batch di folder project:
👉 **`01_Uji_Sensor_UareU_4500.bat`**
*(atau buka langsung file `UangPasDesktop\Uji_Sensor_UareU.exe`)*

**Fitur pada Tool Pengujian Ini:**
1. **Live Preview**: Begitu Anda menempelkan jari pada sensor kaca U.are.U 4500, gambar guratan sidik jari beresolusi tinggi langsung muncul di layar secara real-time.
2. **Audio Beep**: Scanner otomatis berbunyi *beep* saat sidik jari berhasil dibaca.
3. **Indikator Kualitas**: Menampilkan skor kualitas (`DP_QUALITY_GOOD`), resolusi, dan kecepatan respon scanner (ms).
4. **Uji Kecocokan Biometrik (1:1 Match Test)**:
   - Tempelkan jari (misal: jempol kanan) -> klik tombol **"1. Jadikan Template Uji"**.
   - Tempelkan jempol kanan lagi -> klik **"2. Cocokkan Jari"** -> Akan muncul **"COCOK 100%"**.
   - Tempelkan jari lain (misal: telunjuk) -> klik **"2. Cocokkan Jari"** -> Akan muncul **"TIDAK COCOK"**.
   Ini membuktikan algoritma biometrik ANSI bekerja akurat!

---

#### Pilihan 2: Uji Langsung di Aplikasi Kasir POS Desktop
Klik ganda file batch:
👉 **`02_Buka_Kasir_POS_Lapas.bat`**

1. Pada menu bagian atas, klik tombol biru **`[🔬 Uji Alat U.are.U]`** atau buka tab ke-5 **`🔬 Uji Alat U.are.U`**.
2. Klik tombol hijau **`▶️ Mulai Pengujian Sensor U.are.U`**.
3. Tempelkan jari Anda pada sensor kaca U.are.U untuk melihat respon visual dan verifikasi biometrik.

---

#### Pilihan 3: Uji Menggunakan Tool Resmi Bawaan DigitalPersona SDK
Jika ingin menguji menggunakan perangkat lunak resmi dari pabrikan DigitalPersona:
Klik ganda file batch:
👉 **`03_Uji_Alat_Resmi_DigitalPersona_SDK.bat`**

1. Klik tombol **`Readers`** -> pilih serial number scanner U.are.U Anda -> klik **`Select`**.
2. Klik menu **`Capture`** -> tempelkan jari pada scanner untuk melihat preview gambar resmi bawaan SDK.
3. Anda juga dapat mencoba menu **`Enrollment`**, **`Verification`**, dan **`Identification`**.

---

### Catatan Penting untuk Teknisi / Pengguna:
- Hanya satu aplikasi yang dapat mengakses scanner dalam satu waktu (`DP_PRIORITY_COOPERATIVE`). Jika aplikasi satu sedang aktif membaca scanner, tutup atau hentikan sesi sebelum membuka aplikasi lain.
- Lampu biru pada sensor U.are.U 4500 akan menyala saat proses pengambilan sidik jari (*CaptureAsync*) aktif dan siap mendeteksi sentuhan jari.

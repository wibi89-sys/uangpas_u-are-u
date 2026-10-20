# 🏛️ PROPOSAL EKSEKUTIF & STANDARD OPERATING PROCEDURE (SOP)
# SISTEM DIGITALISASI REGISTER D & DOMPET BIOMETRIK "UANGPAS"
## TRANSFORMASI MENUJU LEMBAGA PEMASYARAKATAN & RUTAN BEBAS PEREDARAN UANG TUNAI (ZERO CASH - ZERO HALINAR)
### Kementerian Hukum dan Hak Asasi Manusia Republik Indonesia - Direktorat Jenderal Pemasyarakatan

---

## RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

Peredaran uang tunai di dalam Lembaga Pemasyarakatan (Lapas) dan Rumah Tahanan Negara (Rutan) telah lama diidentifikasi sebagai akar masalah utama terjadinya berbagai penyimpangan dan gangguan keamanan dan ketertiban (Kamtib), mulai dari praktik pungutan liar (pungli), pemerasan sesama Warga Binaan Pemasyarakatan (WBP), utang-piutang perjudian, transaksi penyelundupan narkotika dan telepon genggam (*Handphone, Pungli, dan Narkoba - HALINAR*), hingga suap kepada oknum petugas.

Sebagai wujud implementasi nyata dari **3 Kunci Pemasyarakatan Maju** (Deteksi Dini, Berantas Narkoba, dan Sinergitas) serta **Back to Basics**, sistem **UangPas** dihadirkan sebagai platform ekosistem terpadu yang memadukan:
1. **Digitalisasi Buku Register D Kemenkumham**: Pengelolaan dana titipan narapidana secara tersentralisasi dan transparan layaknya *Core-Banking* modern.
2. **Otentikasi Biometrik Mutlak (DigitalPersona U.are.U 4500 & Solution X100C)**: Memastikan transaksi pemotongan dana hanya dapat diotorisasi oleh jari fisik pemilik saldo, menghilangkan celah pencurian kartu maupun pemerasan.
3. **Open Architecture & Non-Disruptive**: Tidak mewajibkan pengelola kantin/koperasi mengganti perangkat lunak kasir (*Point of Sale*) eksisting, melainkan bertindak sebagai terminal verifikasi pembayaran instan (EDC Cepat).
4. **Rekening Penampung Resmi (Escrow Account)**: Menjamin akuntabilitas dana titipan keluarga WBP di bank persepsi negara, bebas bunga, dan terisolasi dari kas operasional pribadi.

---

## 1. LANDASAN HUKUM & REGULASI

Penerapan sistem UangPas merujuk pada ketentuan perundang-undangan:
1. **Undang-Undang Republik Indonesia Nomor 22 Tahun 2022** tentang Pemasyarakatan.
2. **Peraturan Menteri Hukum dan HAM R.I. Nomor 6 Tahun 2013** tentang Tata Tertib Lembaga Pemasyarakatan dan Rumah Tahanan Negara.
   * *Pasal 4 Huruf c*: "Narapidana atau Tahanan dilarang membawa dan/atau menyimpan uang secara tidak sah dan barang berharga lainnya."
3. **Peraturan Menteri Hukum dan HAM R.I. Nomor 8 Tahun 2024** tentang Penyelenggaraan Keamanan dan Ketertiban pada Satuan Kerja Pemasyarakatan.
4. **Instruksi Direktur Jenderal Pemasyarakatan** terkait Program Aksi Pencegahan dan Pemberantasan Penyalahgunaan dan Peredaran Gelap Narkotika (P4GN) serta Gerakan Zero HALINAR.
5. **Surat Edaran Ditjen Pemasyarakatan** mengenai Pembukuan dan Penatausahaan Barang dan Uang Titipan Narapidana/Tahanan (Buku Register D).

---

## 2. MATRIKS PERBANDINGAN: METODE KONVENSIONAL VS UANGPAS

| Indikator | Metode Konvensional (Uang Tunai) | Metode Kartu Barcode Biasa | Solusi Terpadu UangPas (Biometrik) |
| :--- | :--- | :--- | :--- |
| **Peredaran Fisik Uang** | Beredar bebas di dalam blok hunian. | Masih ada risiko uang tunai gelap. | **100% Zero Cash**. Tidak ada uang fisik yang masuk ke dalam blok hunian. |
| **Resiko Pemerasan & Perampasan** | Sangat tinggi (uang tunai dirampas napi senior). | Tinggi (kartu dan nomor PIN dapat dipaksa/dipinjamkan). | **Nol**. Transaksi mensyaratkan sensor jari pemilik hidup (*liveness*). |
| **Pencatatan Buku Register D** | Buku tulis manual / spreadsheet rentan selisih. | Terpisah dari sistem kasir. | **Otomatis & Real-Time**. Setiap debit/kredit langsung membukukan mutasi. |
| **Batas Belanja Harian (Cap)** | Sulit diawasi dan tidak terkontrol. | Bergantung ketelitian manual kasir. | **Terkunci Sistematis**. Otomatis menolak belanja jika melampaui limit. |
| **Kepatuhan Sanksi (Register F)** | Napi di sel isolasi masih bisa titip belanja teman. | Kartu bisa dititipkan ke rekan sekamar. | **Terkunci Otomatis**. Status isolasi langsung membekukan akses saldo di kasir. |
| **Pencairan Hak Saat Bebas** | Sering tertunda karena rekap saldo lama. | Cek saldo memakan waktu antrean. | **Instan**. Cetak rekening koran dan berita acara pengeluaran dana langsung di loket. |

---

## 3. ARSITEKTUR TEKNOLOGI & KEAMANAN

```
      [ KELUARGA PEMBESUK ]
         │ (Setor Titipan)
         ▼
 ┌───────────────────────────────┐
 │ Loket Register D / Bank VA   │ ──► [ REKENING ESCROW RESMI UPT ]
 └──────────────┬────────────────┘     (Bank BNI/BRI/Mandiri)
                │
                ▼
 ┌─────────────────────────────────────────────────────────────┐
 │               UANGPAS CORE BANKING ENGINE                   │
 │ - Ledger Buku Register D Otomatis                           │
 │ - Enkripsi Database Transaksi SHA-256                       │
 │ - Kontrol Limit Belanja Harian & Validasi Register F        │
 └──────┬──────────────────────┬────────────────────────┬──────┘
        │                      │                        │
        ▼                      ▼                        ▼
 ┌───────────────┐      ┌───────────────┐       ┌───────────────┐
 │ Kios Mandiri  │      │ Terminal EDC  │       │ Open API      │
 │ Cek Saldo     │      │ Cepat Kantin  │       │ POS Kantin    │
 │ (Layar Touch/ │      │ (Sensor USB   │       │ Pihak Ketiga  │
 │ TV di Blok)   │      │ U.are.U 4500) │       │ (REST JSON)   │
 └───────────────┘      └───────────────┘       └───────────────┘
```

### Keunggulan Teknis:
1. **Lokal Offline-First**: Seluruh operasional kasir dan otentikasi biometrik tetap berfungsi normal walaupun akses internet provider eksternal terputus.
2. **Kesesuaian Standar Biometrik Internasional**: Menggunakan algoritma ekstraksi ciri ANSI/NIST dan ISO/IEC 19794-2 pada resolusi 512 DPI untuk akurasi tinggi tanpa salah identifikasi.
3. **Audit Trail Tak Terhapuskan (Immutable Logs)**: Setiap penambahan saldo, belanja kantin, dan pengembalian uang memiliki jejak audit lengkap (*Timestamp, Nama Operator/Kasir, Nomor Struk, IP Address*).

---

## 4. STANDARD OPERATING PROCEDURE (SOP) RESMI

### SOP 01: Penerimaan dan Pembukuan Uang Titipan (Register D Inflow)
1. **Pelaksana**: Petugas Loket Pendaftaran Kunjungan / Bendahara Register D.
2. **Tahapan**:
   - Petugas menerima uang tunai dari keluarga pembesuk yang sah.
   - Petugas membuka menu **Loket Setor Titipan** pada sistem UangPas.
   - Petugas memasukkan Nomor Register D atau Nama WBP tujuan.
   - Petugas menginput nominal setoran, data identitas penyetor (Nama, KTP, Hubungan Keluarga, Nomor HP).
   - Petugas menekan tombol **Proses Setor Titipan**.
   - Sistem mencatat kredit saldo dan mencetak 2 lembar **Bukti Setor Titipan Kas**:
     - *Lembar 1*: Diserahkan kepada keluarga sebagai bukti sah.
     - *Lembar 2*: Arsip loket pembukuan.
   - Uang tunai yang terkumpul wajib disetorkan secara utuh ke **Rekening Escrow Bank Resmi UPT** setiap akhir jam dinas pelayanan loket.

### SOP 02: Transaksi Pembelanjaan WBP di Kantin (Register D Outflow - Retail)
1. **Pelaksana**: Petugas Kasir Koperasi / Operator Kantin dan WBP.
2. **Tahapan**:
   - WBP memilih barang belanjaan di stan kantin koperasi.
   - Kasir menghitung total harga pada sistem POS kantin.
   - Kasir menginput nominal belanja pada **Terminal EDC UangPas**.
   - Kasir meminta WBP menempelkan jari telunjuk pada sensor **DigitalPersona U.are.U 4500**.
   - Sistem melakukan verifikasi biometrik secara instan (< 1 detik).
   - Sistem memastikan:
     * Saldo mencukupi.
     * Tidak melanggar limit belanja harian.
     * WBP berstatus aktif (bukan isolasi Register F).
   - Saldo terpotong otomatis, printer mencetak struk belanja thermal, dan notifikasi audio bersuara menyebutkan sisa saldo WBP.
   - Kasir menyerahkan barang belanjaan beserta struk belanja kepada WBP.

### SOP 03: Rekonsiliasi & Settlement Kantin Koperasi
1. **Pelaksana**: Bendahara Koperasi, Pengelola Stan Kantin, dan Ka. KPLP.
2. **Frekuensi**: Harian atau Mingguan (sesuai kesepakatan UPT).
3. **Tahapan**:
   - Bendahara membuka menu **Rekonsiliasi & Settlement Kantin**.
   - Sistem menampilkan rekapitulasi nilai transaksi debet per masing-masing stan kantin.
   - Bendahara mencetak **Berita Acara Rekonsiliasi & Pencairan Dana (Format A4 Kemenkumham)**.
   - Berita Acara ditandatangani oleh 3 pihak:
     1. Penanggung Jawab Stan/Kantin Rekanan.
     2. Bendahara Koperasi / Petugas Register D.
     3. Mengetahui: Kepala KPLP / Pejabat Berwenang.
   - Bendahara melakukan transfer dana dari Rekening Escrow ke rekening pengelola kantin sesuai angka mutlak yang tercantum pada Berita Acara resmi.

### SOP 04: Pengembalian Sisa Dana Saat WBP Bebas (Register D Settlement)
1. **Pelaksana**: Petugas Registrasi / Pembebasan dan Petugas Register D.
2. **Tahapan**:
   - Menjelang pelaksanaan pembebasan (PB, CB, CMB, atau Bebas Murni), petugas membuka berkas Register D WBP bersangkutan.
   - Petugas mencetak **Rekening Koran Register D** untuk diverifikasi dan diparaf bersama oleh WBP.
   - Petugas menekan tombol **Cairkan Sisa Saldo (Bebas / Mutasi)** pada menu Register D.
   - Sistem memotong saldo menjadi Rp 0 dan mencetak **Kwitansi Pengeluaran Dana Titipan Register D**.
   - Petugas menyerahkan sisa dana tunai / transfer bank kepada WBP disaksikan oleh petugas pengamanan.
   - WBP menandatangani Berita Acara Penyerahan Sisa Uang Titipan untuk dilampirkan dalam berkas pelepasan narapidana.

---

## 5. REKOMENDASI PERANGKAT KERAS & SPESIFIKASI INFRASTRUKTUR

Untuk implementasi optimal pada satu Satuan Kerja (Lapas/Rutan), berikut spesifikasi teknis yang disarankan:

### A. Perangkat Lunak & Server Utama (UPT Server):
* **Prosesor**: Intel Core i5 / AMD Ryzen 5 generasi ke-10 atau lebih baru.
* **Memori (RAM)**: 8 GB DDR4 (disarankan 16 GB).
* **Penyimpanan**: SSD NVMe 256 GB (kecepatan baca/tulis tinggi).
* **Sistem Operasi**: Windows 10/11 Pro 64-bit atau Linux Ubuntu Server 22.04 LTS.
* **Jaringan**: Switch Gigabit LAN (kabel CAT6 menghubungkan ruang loket, kantin, dan blok).

### B. Perangkat Keras Terminal Kasir / EDC Kantin:
* Mini PC / Laptop / Komputer All-in-One di setiap meja kasir.
* **Sensor Biometrik Sidik Jari**: **DigitalPersona U.are.U 4500 USB Optical Fingerprint Scanner** (resmi terdaftar dan teruji tahan gores).
* **Printer Struk**: Thermal Printer 58mm atau 80mm USB / Auto-Cutter.
* Barcode Scanner USB (opsional untuk kartu cadangan).

### C. Anjungan Cek Saldo Mandiri (Kiosk):
* Monitor Layar Sentuh (*Touchscreen*) 15"-21" atau TV LED terpasang di dinding lorong blok hunian.
* Terminal Sidik Jari IP LAN: **Solution X100C** (terkoneksi langsung ke jaringan server).

---

## 6. KESIMPULAN & NILAI STRATEGIS BAGI PIMPINAN

Penerapan sistem UangPas memberikan dampak strategis nyata bagi kepemimpinan Lapas/Rutan:
1. **Peningkatan Indeks Integritas & Reformasi Birokrasi (WBK/WBBM)**: Menjadi bukti inovasi nyata pelayanan publik berbasis teknologi informasi yang transparan dan akuntabel.
2. **Kondusivitas Keamanan Blok**: Menghilangkan perselisihan utang-piutang uang tunai di antara WBP, menekan angka kekerasan, dan meniadakan perputaran modal bisnis ilegal di dalam kamar hunian.
3. **Akuntabilitas Keuangan 100%**: Melindungi pejabat instansi (Kalapas, Ka. KPLP, dan Bendahara) dari potensi fitnah atau tuduhan pungli, karena seluruh uang titipan terdokumentasi rapi di rekening escrow bank pemerintah dan Buku Register D digital.

---
*Disusun oleh Tim Pengembang Sistem UangPas - Dokumen Resmi Presentasi Klien & SOP Pelaksanaan Lapas/Rutan 2026.*

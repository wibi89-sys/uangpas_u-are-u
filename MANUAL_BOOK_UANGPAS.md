# 📖 MANUAL BOOK & BUKU PANDUAN PENGGUNAAN RESMI
# SISTEM UANGPAS - DOMPET DIGITAL BIOMETRIK & BUKU REGISTER D ELEKTRONIK
### Standar Operasional Bebas Peredaran Uang Tunai (Zero Cash / Zero Halinar) Lembaga Pemasyarakatan & Rumah Tahanan Negara

---

## DAFTAR ISI
1. [BAB I: PENDAHULUAN & GAMBARAN UMUM](#bab-i-pendahuluan--gambaran-umum)
2. [BAB II: HAK AKSES & PERAN PENGGUNA (ROLE MATRIX)](#bab-ii-hak-akses--peran-pengguna-role-matrix)
3. [BAB III: PENGATURAN AWAL SISTEM & REKENING ESCROW](#bab-iii-pengaturan-awal-sistem--rekening-escrow)
4. [BAB IV: PENDAFTARAN WBP & PEREKAMAN SIDIK JARI](#bab-iv-pendaftaran-wbp--perekaman-sidik-jari)
5. [BAB V: LOKET PENERIMAAN TITIPAN DANA (SETOR TUNAI & VA)](#bab-v-loket-penerimaan-titipan-dana-setor-tunai--va)
6. [BAB VI: BUKU REGISTER D ELEKTRONIK & MINI CORE-BANKING](#bab-vi-buku-register-d-elektronik--mini-core-banking)
7. [BAB VII: TERMINAL EDC CEPAT KANTIN (DEBIT BIOMETRIK STANDALONE)](#bab-vii-terminal-edc-cepat-kantin-debit-biometrik-standalone)
8. [BAB VIII: REKONSILIASI & SETTLEMENT KANTIN KOPERASI](#bab-viii-rekonsiliasi--settlement-kantin-koperasi)
9. [BAB IX: PEMBEBASAN WBP & PENCAIRAN SISA SALDO (REGISTER D CHECKOUT)](#bab-ix-pembebasan-wbp--pencairan-sisa-saldo-register-d-checkout)
10. [BAB X: KIOSK MANDIRI CEK SALDO WBP (TOUCHSCREEN / TV)](#bab-x-kiosk-mandiri-cek-saldo-wbp-touchscreen--tv)
11. [BAB XI: PENANGANAN MASALAH (TROUBLESHOOTING & FAQ)](#bab-xi-penanganan-masalah-troubleshooting--faq)

---

## BAB I: PENDAHULUAN & GAMBARAN UMUM

### 1.1 Latar Belakang
Sistem **UangPas** dirancang khusus untuk memutus rantai peredaran uang tunai secara mutlak di dalam lingkungan Lembaga Pemasyarakatan (Lapas) dan Rumah Tahanan Negara (Rutan), mendukung program **P4GN** dan **Zero HALINAR** (Handphone, Pungli, dan Narkoba) Ditjen Pemasyarakatan Kementerian Hukum dan HAM RI.

### 1.2 Filosofi Desain Sistem
1. **Fokus Dompet Digital Terintegrasi Register D**: Tidak membebani kantin/koperasi untuk mengganti software POS kasir eksisting mereka. UangPas berperan sebagai *Core-Banking Engine* dan terminal otentikasi pembayaran non-tunai.
2. **Kepatuhan Regulasi Ditjenpas**: Mengadopsi format resmi Buku Register D Ditjenpas, Berita Acara Rekonsiliasi, slip setoran titipan, cetak buku tabungan fisik (*passbook*), dan bukti pengembalian uang saat bebas.
3. **Keamanan Biometrik Tak Terbantahkan**: Menggunakan sensor optik presisi tinggi **DigitalPersona U.are.U 4500** dan **Solution X100C**. Saldo hanya bisa didebit dengan kehadiran fisik jari WBP yang bersangkutan, mencegah pencurian kartu, pemerasan, atau peminjaman saldo antar-narapidana.
4. **Pencegahan Monopoli**: Fitur limit belanja harian (*Daily Spending Cap*) yang membatasi pengeluaran maksimal per WBP setiap harinya.
5. **Freeze Otomatis Pelanggaran Disiplin (Register F)**: Narapidana yang menjalani hukuman sel isolasi (*tutupan sunyi*) otomatis dibekukan saldonya hingga masa sanksi berakhir.

---

## BAB II: HAK AKSES & PERAN PENGGUNA (ROLE MATRIX)

Sistem UangPas mengimplementasikan kontrol akses berbasis peran (RBAC) ketat:

| Peran (Role) | Tanggung Jawab Operasional | Fitur yang Diakses |
| :--- | :--- | :--- |
| **Kanwil / Super Admin** | Pengawas wilayah & pemeliharaan teknis | Akses menyeluruh semua UPT, audit log, switch role simulasi, konfigurasi jaringan |
| **Kalapas / Karutan** | Pimpinan satuan kerja | Dashboard eksekutif, rekapitulasi audit dana titipan, Buku Register D, tanda tangan Berita Acara |
| **Admin Koperasi / Bendahara** | Pengelola keuangan titipan & rekon | Register D, Rekonsiliasi Settlement Kantin, Laporan Arus Kas, Manajemen WBP |
| **Petugas Loket Register D** | Pelayanan pendaftaran & setoran titipan keluarga | Pendaftaran WBP, rekam sidik jari, cetak slip setoran uang, cetak buku tabungan |
| **Kasir Kantin / Operator EDC** | Transaksi debit belanja di stan/kantin | Terminal EDC Cepat, cetak struk thermal 58mm/80mm |
| **Auditor / Inspektorat** | Pemeriksa kepatuhan & keuangan | Buku Register D (*read-only*), riwayat jurnal transaksi, ekspor data audit |

---

## BAB III: PENGATURAN AWAL SISTEM & REKENING ESCROW

Sebelum sistem digunakan untuk operasional harian, lakukan konfigurasi instansi:

### 3.1 Langkah Konfigurasi:
1. Masuk sebagai **Admin Koperasi** atau **Kalapas**.
2. Buka menu **Pengaturan & Perangkat** &rarr; **Pengaturan Umum Aplikasi**.
3. Lengkapi field data instansi:
   - **Nama UPT**: Contoh `Lembaga Pemasyarakatan Kelas I Cipinang`.
   - **Kode UPT**: Contoh `W10.PAS.PAS.1`.
   - **Mode Aplikasi**: Pilih `Hanya Dompet Digital & Register D (Wallet Only)` untuk menyembunyikan katalog retail jika kantin menggunakan POS eksternal.
   - **Limit Belanja Harian Default**: Contoh `Rp 150.000` per WBP/hari.
4. Lengkapi data **Pejabat Penandatangan Resmi**:
   - **Nama & NIP Kepala UPT (Kalapas/Karutan)**.
   - **Nama & NIP Ka. KPLP / Ka. KPR**.
   - **Nama & NIP Bendahara Koperasi / Petugas Register D**.
5. Lengkapi **Informasi Rekening Penampung (Escrow Account)**:
   - Nama Bank (contoh: *Bank BRI / Bank BNI*).
   - Nomor Rekening Escrow Titipan WBP.
   - Nama Pemilik Rekening (contoh: *RPL 012 Lapas Pengayoman*).
6. Klik **Simpan Pengaturan**.

---

## BAB IV: PENDAFTARAN WBP & PEREKAMAN SIDIK JARI

### 4.1 Input Data Warga Binaan
1. Buka menu **Data Warga Binaan** &rarr; Klik tombol **+ Tambah Warga Binaan**.
2. Masukkan data identitas:
   - **Nomor Registrasi**: Nomor register pembinaan resmi (contoh: `BI.123/2026`).
   - **Nomor Register D**: Nomor buku titipan uang (otomatis di-generate format `REG-D/2026/XXXXX`).
   - **Nama Lengkap**: Nama sesuai dokumen penetapan pengadilan.
   - **NIK & Nomor Kamar/Blok**: Contoh `Blok B, Kamar 04`.
   - **Data Penjamin Keluarga**: Nama penjamin, hubungan (Istri/Ibu/Ayah), nomor HP, dan alamat lengkap.
   - **Limit Belanja Khusus**: Kosongkan untuk menggunakan batas default UPT.
3. Klik **Simpan Data WBP**.

### 4.2 Perekaman Biometrik Sidik Jari (DigitalPersona U.are.U 4500)
1. Pada tabel Warga Binaan, klik tombol **Rekam Biometrik** (ikon sidik jari).
2. Pastikan lampu indikator biru sensor **U.are.U 4500** menyala dan service **UangPasBridge** aktif di latar belakang (`http://127.0.0.1:8080`).
3. Minta WBP meletakkan jari telunjuk kanan secara tegak dan stabil di kaca prisma sensor.
4. Sistem akan mengambil 4 sampel pindaian untuk membentuk template sidik jari biometrik standar ISO/IEC 19794-2.
5. Setelah muncul notifikasi **Template Sidik Jari Berhasil Disimpan**, WBP sudah dapat bertransaksi di EDC dan Kiosk Cek Saldo.

---

## BAB V: LOKET PENERIMAAN TITIPAN DANA (SETOR TUNAI & VA)

Titipan uang dari keluarga pembesuk diterima melalui loket pendaftaran kunjungan atau transfer Virtual Account Bank resmi.

### 5.1 Alur Setoran di Loket Fisik (Cash Counter):
1. Buka menu **Loket Setor Titipan & VA** &rarr; **Formulir Setor Titipan**.
2. Cari WBP tujuan dengan mengetikkan Nama, No. Registrasi, atau Nomor Register D.
3. Data WBP beserta sisa saldo berjalan akan langsung ditampilkan.
4. Masukkan rincian titipan:
   - **Nominal Setoran**: Contoh `Rp 200.000` (dapat menggunakan tombol chip cepat).
   - **Nama Penyetor / Pengirim**: Nama keluarga yang menitipkan.
   - **Hubungan Keluarga**: Contoh: *Istri Kandung*.
   - **Nomor HP & KTP Penyetor**.
   - **Catatan**: Keterangan tambahan jika ada.
5. Klik tombol **Proses Setor Titipan (Kredit Saldo)**.
6. Saldo WBP bertambah secara *real-time*.
7. Klik tombol **Cetak Bukti Setoran (Kwitansi Register D)** untuk mencetak slip bukti bermaterai/stempel loket untuk diserahkan kepada keluarga sebagai bukti sah.

### 5.2 Setoran via Virtual Account Bank:
1. Keluarga dapat melakukan transfer antar-bank ke nomor Virtual Account resmi WBP:
   `[Kode Bank Prefix][Nomor Registrasi WBP]`
2. Sistem UangPas menerima webhook notifikasi otomatis dan menambahkan saldo WBP dalam hitungan detik tanpa perlu konfirmasi manual.

---

## BAB VI: BUKU REGISTER D ELEKTRONIK & MINI CORE-BANKING

Modul **Buku Register D** merupakan implementasi digital dari Buku Register D Kemenkumham, bertindak layaknya sistem *Core-Banking* perbankan nasional.

### 6.1 Membuka Menu Register D:
Buka menu utama **Buku Register D** di bilah navigasi kiri.

### 6.2 Fitur yang Tersedia:
1. **Pencarian Multi-Kriteria**: Cari berdasarkan Nama WBP, No. Registrasi, Nomor Register D, Blok/Kamar, dan Status (Aktif/Bebas/Isolasi).
2. **Rekening Koran Per WBP (Statement of Account)**:
   - Klik nama WBP atau tombol **Rincian Register D**.
   - Menampilkan saldo berjalan, total setoran masuk, total debet belanja, dan riwayat mutasi debit/kredit dengan saldo akumulatif berurutan.
   - Tombol **🖨️ Cetak Rekening Koran (A4)**: Menghasilkan lembar rekening koran resmi format A4 ber-Kop Surat Kementerian Hukum dan HAM RI untuk keperluan audit atau permintaan resmi WBP.
3. **Cetak Buku Tabungan Fisik (Passbook Printer Friendly)**:
   - Klik tombol **📘 Cetak Buku Tabungan**.
   - Tata letak khusus dirancang pas dengan buku tabungan saku WBP (memuat Tanggal, Uraian, Debet, Kredit, Saldo, dan Paraf Petugas).
4. **Buku Jurnal Umum Register D (Master Ledger)**:
   - Pada halaman utama Register D, klik tombol **📑 Cetak Buku Jurnal Induk (Ledger A4)**.
   - Mencetak seluruh lalu lintas uang masuk dan keluar pada rentang tanggal tertentu yang ditandatangani oleh Bendahara Register D dan Ka. KPLP.

---

## BAB VII: TERMINAL EDC CEPAT KANTIN (DEBIT BIOMETRIK STANDALONE)

Terminal EDC Cepat dirancang bagi kasir kantin untuk memotong saldo WBP secara mandiri dalam waktu kurang dari 3 detik.

### 7.1 Tampilan Antarmuka EDC:
1. Buka menu **Terminal EDC Cepat** di browser kasir (`/edc`).
2. Tampilan dilengkapi:
   - **Virtual Numpad & Quick Nominal Chips** (Rp 5.000, Rp 10.000, Rp 20.000, Rp 50.000, Rp 100.000).
   - **Pemilih Lokasi Stan/Kantin** (Kantin Utama, Kantin Blok A, Blok B, Wartelsuspas, Laundry, Bimker).
   - **Status Koneksi Biometrik Bridge** (Port 8080).
   - **Bidang Scan Barcode Kartu / No. Registrasi**.

### 7.2 Prosedur Transaksi Kasir:
1. **Langkah 1**: Kasir melihat total tagihan belanja WBP pada nota/program POS kantin milik mereka sendiri (contoh: `Rp 24.500`).
2. **Langkah 2**: Kasir mengetik nominal `24500` di Terminal EDC UangPas (atau klik tombol numpad layar sentuh).
3. **Langkah 3**: Lakukan otentikasi identitas WBP menggunakan salah satu dari 2 metode:
   - **Metode A - Scan Sidik Jari U.are.U 4500 (Direkomendasikan)**:
     - Minta WBP meletakkan jari di sensor U.are.U 4500.
     - Klik tombol **👆 Scan Sidik Jari (1:N Otomatis)** atau lakukan *tap* kartu terlebih dahulu lalu klik **Verifikasi Jari**.
   - **Metode B - Scan Kartu Barcode / Input No. Registrasi**:
     - Kasir mengarahkan barcode scanner USB ke kartu WBP atau mengetikkan nomor registrasi, lalu menekan **Enter**.
4. **Langkah 4**: Sistem memvalidasi secara instan:
   - Apakah status WBP Aktif? (Jika berstatus Isolasi Register F, transaksi langsung **DITOLAK**).
   - Apakah saldo WBP mencukupi?
   - Apakah nominal transaksi melebihi sisa batas belanja harian?
5. **Langkah 5 (Selesai)**:
   - Jika valid, saldo dipotong secara atomik dalam database.
   - Layar menampilkan modal hijau sukses beserta sisa saldo terkini WBP.
   - Suara konfirmasi audio berbunyi: *"Transaksi berhasil. Sisa saldo Warga Binaan Rp X"*.
   - Printer thermal otomatis mencetak struk belanja (atau klik tombol **Cetak Struk**).
   - Layar otomatis ter-reset kembali ke Rp 0 dalam hitungan mundur 4 detik siap untuk antrean berikutnya.

---

## BAB VIII: REKONSILIASI & SETTLEMENT KANTIN KOPERASI

Modul ini digunakan oleh Bendahara Koperasi untuk menghitung dan mencairkan dana omzet yang menjadi hak masing-masing pengelola stan/kantin rekanan.

### 8.1 Menjalankan Rekonsiliasi:
1. Buka menu **Keuangan & Audit Dana** &rarr; **Rekonsiliasi & Settlement Kantin**.
2. Pilih filter rentang periode (Hari Ini, Minggu Ini, Bulan Ini, atau Kustom Tanggal).
3. Sistem secara otomatis menyajikan:
   - **Total Omzet Terdebit Seluruh Stan**.
   - **Jumlah Transaksi EDC**.
   - **Rekapitulasi Omzet Per Stan / Titik Layanan**:
     - Kantin Koperasi Utama
     - Kantin Blok A, Blok B, Blok C
     - Wartelsuspas (Telepon Narapidana)
     - Unit Laundry & Bimker
4. Klik tombol **🖨️ Cetak Berita Acara Rekonsiliasi (A4)**.
5. Dokumen resmi Berita Acara Rekonsiliasi tercetak lengkap dengan nomor registrasi surat dinas, rincian transaksi, nilai rupiah terbilang, dan format 3 tanda tangan:
   - **Pihak Penyedia / Pengelola Kantin**
   - **Bendahara Koperasi / Petugas Register D**
   - **Mengetahui: Ka. KPLP / Pejabat Berwenang**
6. Bendahara mentransfer nominal settlement dari Rekening Escrow ke rekening pengelola stan secara non-tunai.

---

## BAB IX: PEMBEBASAN WBP & PENCAIRAN SISA SALDO (REGISTER D CHECKOUT)

Ketika Warga Binaan mendapatkan Surat Keputusan Pembebasan Bersyarat (PB), Cuti Menjelang Bebas (CMB), atau Bebas Murni, seluruh sisa dana titipan di Register D wajib dikembalikan secara utuh tanpa potongan.

### 9.1 Prosedur Pengembalian Dana:
1. Buka menu **Buku Register D** &rarr; Cari WBP yang akan bebas.
2. Klik tombol **Rincian Register D**.
3. Periksa saldo akhir WBP pada panel ringkasan.
4. Klik tombol **💸 Cairkan Sisa Saldo (Bebas / Mutasi)**.
5. Masukkan catatan administrasi (contoh: *Bebas Bersyarat No. Surat W10.PAS.PK.01-XX*).
6. Konfirmasi pencairan. Sistem akan:
   - Memotong sisa saldo menjadi `Rp 0`.
   - Mengubah status rekening menjadi *Ditutup / Dicairkan*.
   - Mencatat transaksi debet tipe `release` dalam buku kas.
7. Klik tombol **🖨️ Cetak Bukti Pengeluaran Dana Bebas**.
8. WBP menandatangani bukti tanda terima pencairan fisik bersama petugas Register D untuk diarsipkan ke dalam berkas berkas berkala WBP.

---

## BAB X: KIOSK MANDIRI CEK SALDO WBP (TOUCHSCREEN / TV)

Untuk menjaga transparansi dan ketertiban di blok hunian, UangPas menyediakan layar anjungan mandiri (*Self-Service Kiosk*).

### 10.1 Pengoperasian Layar Kiosk:
1. Layar Kiosk dibuka pada browser layar penuh (*F11 Kiosk Mode*) di alamat:
   `http://[IP-SERVER-LAPAS]/kiosk`
2. Kiosk terhubung ke sensor sidik jari **Solution X100C** (via kabel LAN IP) atau barcode scanner USB.
3. WBP cukup menempelkan jarinya pada sensor.
4. Layar langsung menyala menampilkan:
   - Foto & Nama WBP
   - Nomor Register D & Nomor Kamar
   - **Sisa Saldo Terkini** (angka besar dan jelas)
   - **Sisa Kuota Belanja Hari Ini**
   - **5 Riwayat Transaksi Terakhir**
5. Pengucapan audio (*Text-to-Speech*) otomatis menyebutkan saldo WBP dalam bahasa Indonesia ramah tuna netra.
6. **Perlindungan Privasi**: Layar otomatis tertutup kembali (*auto-reset*) ke posisi siaga dalam tempo 6 detik agar saldo tidak terbaca oleh narapidana di belakangnya.

---

## BAB XI: PENANGANAN MASALAH (TROUBLESHOOTING & FAQ)

### Q1: Sensor U.are.U 4500 tidak merespons di halaman web EDC?
* **Penyebab**: Service UangPasBridge belum aktif atau kabel USB longgar.
* **Solusi**:
  1. Pastikan lampu biru sensor U.are.U menyala.
  2. Buka terminal atau jalankan `UangPasBridge.exe` di folder `c:\laragon\www\uangpas\UangPasBridge`.
  3. Buka tab baru browser dan akses `http://127.0.0.1:8080/api/status`. Pastikan muncul pesan `{"status":"ready","connected":true}`.
  4. Segarkan (*refresh*) halaman EDC (`Ctrl + F5`).

### Q2: Muncul peringatan "Transaksi Ditolak: Melebihi Batas Belanja Harian"?
* **Penyebab**: Akumulasi belanja WBP pada hari ini telah mencapai batas limit harian yang ditetapkan Kalapas (misal Rp 150.000).
* **Solusi**: WBP tidak diizinkan berbelanja lagi hingga esok hari pukul 00:00 WIB. Limit dapat diubah secara khusus oleh Admin jika terdapat kebutuhan darurat medis/kesehatan atas persetujuan Ka. KPLP.

### Q3: Mengapa saldo WBP tidak bisa didebit di kasir dan muncul pesan status "Isolasi"?
* **Penyebab**: WBP sedang tercatat menjalani masa hukuman disiplin Register F di sel pengasingan (*straft cell*).
* **Solusi**: Sesuai SOP Ditjenpas, hak belanja koperasi WBP dibekukan secara sistematis hingga petugas mencabut status isolasi pada panel Data Warga Binaan.

### Q4: Apakah data transaksi hilang jika jaringan mati?
* **Solusi**: Sistem UangPas menerapkan database lokal terenkripsi di server fisik Lapas (*on-premise offline resilience*). Semua data tersimpan aman dan tidak bergantung pada sambungan internet publik.

---
*Manual Book ini disusun sebagai pedoman operasional baku UangPas versi 2.4 - Ditjen Pemasyarakatan Kemenkumham RI.*

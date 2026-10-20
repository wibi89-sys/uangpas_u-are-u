# 📋 Panduan Akun Pengguna & Hak Akses (RBAC) - UangPas Lapas

Dokumen ini mencatat seluruh akun pengguna bawaan (*default credentials*), hierarki peran (*role hierarchy*), matriks hak akses (*permissions matrix*), dan panduan keamanan produksi untuk aplikasi **UangPas Web & Desktop POS**.

---

## 🔑 1. Daftar Akun & Password Bawaan (Default Credentials)

Semua akun di bawah ini telah disiapkan secara otomatis melalui database seeder (`UserRoleSeeder.php` & `DatabaseSeeder.php`).

> [!IMPORTANT]
> **Password Default untuk SEMUA Akun:** `password`  
> Demi keamanan operasional di lingkungan produksi Lapas, **segera ubah password default** melalui menu **Petugas & Hak Akses** (`/users`) setelah instalasi pertama selesai.

| No | Peran / Jabatan | Nama Petugas | NIP Login | Email Login | Password Default | Hak Akses Utama |
|:---:|:---|:---|:---|:---|:---:|:---|
| **1** | **Superadmin Kanwil** | Bpk. Kakanwil / Kadivpas | `197501011999031001` | `kanwil@lapas.go.id` | `password` | Akses penuh seluruh UPT, monitoring omset terpusat, sinkronisasi data antar-Lapas. |
| **2** | **Kalapas / Ka. UPT** | Bpk. Kepala Lapas Kelas I | `198002022005011002` | `kalapas@lapas.go.id` | `password` | Pengawasan manajerial, otorisasi mutasi, persetujuan status Register F / Isolasi. |
| **3** | **Admin Utama UPT & Koperasi** | Petugas Koperasi Lapas | `198705052010011003` | `admin@lapas.go.id` | `password` | Manajemen data WBP, kelola akun petugas, audit keuangan, konfigurasi sistem. |
| **4** | **Pengelola Gudang Koperasi** | Pengelola Koperasi & Gudang | `198705052010011003` | `gudang@lapas.go.id` | `password` | Katalog barang, input restock barang masuk, audit stock opname, supplier rekanan. |
| **5** | **Petugas Loket & Registrasi** | Petugas Loket Kunjungan | `199208082015021004` | `loket@lapas.go.id` | `password` | Pendaftaran WBP baru, cetak smart card, setoran tunai keluarga, top-up saldo titipan. |
| **6** | **Kasir Toko Koperasi** | Petugas Kasir Toko Koperasi | `199511112019031005` | `kasir@lapas.go.id` | `password` | Terminal Kasir POS (Desktop C# & Web), scan barcode barang, otorisasi sidik jari WBP. |
| **7** | **Auditor / Pengawas Internal** | Tim Auditor / Pengawas | `198304042008011006` | `auditor@lapas.go.id` | `password` | Pemeriksaan buku kasir, audit laba rugi, HPP, gross margin, dan arus kas titipan dana. |

---

## 🛡️ 2. Matriks Hak Akses Berjenjang (Role-Based Access Control)

Aplikasi UangPas mengadopsi kontrol akses berjenjang (RBAC) dengan dukungan **Centang Hak Akses Kustom** (*Granular Permissions*) per petugas:

| Modul & Fitur Sistem | Superadmin Kanwil (`kanwil_admin`) | Kalapas (`kalapas`) | Admin Koperasi (`admin_koperasi`) | Petugas Loket (`petugas_loket`) | Kasir POS (`kasir`) | Auditor (`auditor`) |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard Ringkasan & Statistik** | ✅ Full Regional | ✅ Full UPT | ✅ Full UPT | ✅ Ringkasan | ✅ Ringkasan | ✅ Ringkasan |
| **Data WBP & Perekaman Biometrik** (`/inmates`) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Cetak Kartu WBP & Import Excel** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Loket Setor Tunai & Top-Up** (`/topup`) | ✅ | ❌ | ✅ | ✅ | ✅ (Opsi) | ❌ |
| **Terminal Kasir POS Desktop & Web** (`/pos`) | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| **Katalog Produk & Kartu Stok** (`/products`) | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Restock Barang Masuk & Opname** (`/inventory/*`) | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Laporan Laba Rugi & HPP Koperasi** (`/reports/*`) | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| **Buku Transaksi Kasir & Arus Kas** (`/transactions`) | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| **Pengaturan Struk, Alat & UPT** (`/settings/*`) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Manajemen Akun Petugas & Password** (`/users`) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Sinkronisasi Multi-UPT & Cloud** (`/sync`) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 📲 3. Cara Login ke Aplikasi

### A. Login ke Portal Web Manajemen (Laravel)
1. Buka browser dan akses alamat:
   - **Local Laragon:** `http://uangpas.test/login` atau `http://localhost:8000/login`
2. Masukkan **NIP** atau **Email** pada kolom *NIP Dinas atau Email Petugas*.
3. Masukkan kata sandi (default: `password`).
4. Klik tombol **"Masuk ke Sistem"**.
5. Sistem otomatis mengarahkan ke dashboard yang disesuaikan dengan peran pengguna.

### B. Login ke Terminal Kasir Desktop (C# .NET)
1. Buka aplikasi **UangPas Desktop POS** (`01_Buka_Kasir_POS_Lapas.bat`).
2. Masukkan NIP atau Email kasir (contoh: `kasir@lapas.go.id` atau `199511112019031005`).
3. Masukkan password (default: `password`).
4. Klik **"🔑 Masuk Terminal"**.
5. Antarmuka kasir belanja siap digunakan dengan scanner barcode USB dan sensor sidik jari U.are.U 4500.

---

## 🔒 4. Panduan Keamanan untuk Lingkungan Produksi (Production Hardening)

Sebelum sistem dioperasikan secara resmi di Lapas:

1. **Ubah Semua Password Bawaan**:
   - Masuk sebagai Admin (`admin@lapas.go.id`), buka menu **Petugas & Hak Akses** (`/users`).
   - Klik tombol **Edit** pada setiap akun dan masukkan password baru yang kuat (kombinasi huruf besar, kecil, angka, dan simbol).
   - Atau ganti via command line Artisan:
     ```sh
     php artisan tinker --execute="App\Models\User::where('email', 'admin@lapas.go.id')->first()->update(['password' => Hash::make('PasswordBaruKoperasi2026!')]);"
     ```

2. **Nonaktifkan Chip Akun Uji Coba di Halaman Login**:
   - Pada file `.env`, atur:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     SHOW_DEMO_CREDENTIALS=false
     ```
   - Dengan konfigurasi ini, tombol bantuan login instan pada halaman login akan otomatis disembunyikan.

3. **Gunakan Zona Waktu Indonesia (WIB)**:
   - Pastikan konfigurasi `.env` memiliki:
     ```env
     APP_TIMEZONE=Asia/Jakarta
     APP_LOCALE=id
     ```
   - Ini memastikan tanggal struk belanja, jam absensi kasir, dan laporan laba rugi tercatat tepat waktu (WIB).

4. **Cadangkan (Backup) Database Secara Berkala**:
   - Manfaatkan menu **Sinkron & Mutasi Antar-UPT** (`/sync`) untuk mengunduh salinan cadangan (*backup snapshot*) database MySQL harian.

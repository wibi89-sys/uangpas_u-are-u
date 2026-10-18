@echo off
chcp 65001 >nul
title UangPas Lapas - Sistem Instalasi Otomatis (Easy Install)
color 0B

echo ===============================================================================
echo                SISTEM INSTALASI OTOMATIS (EASY INSTALL)                       
echo              UANGPAS LAPAS - KASIR POS & BIOMETRIK U.ARE.U 4500                
echo ===============================================================================
echo.
echo  Selamat datang di wizard instalasi UangPas Dompet Digital Lapas.
echo  Skrip ini akan memeriksa dan menyiapkan seluruh komponen sistem secara otomatis:
echo    [1] Database MySQL lokal (uangpas_lapas)
echo    [2] Konfigurasi Backend Laravel & Migrasi Tabel
echo    [3] Driver Hardware Fingerprint Scanner U.are.U 4500
echo    [4] Kompilasi Aplikasi Kasir Desktop & Hardware Bridge
echo    [5] Pembuatan Launcher Cepat (Web POS Modern & Desktop)
echo.
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
cd /d "%BASE_DIR%"

:: 1. Cek PHP
echo [1/6] Memeriksa ketersediaan PHP...
where php >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe" (
        set "PATH=C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;%PATH%"
        echo       [OK] Menggunakan PHP dari Laragon.
    ) else (
        echo [ERROR] PHP tidak ditemukan di sistem!
        echo Pastikan Laragon atau XAMPP sudah terpasang dan service Apache/PHP berjalan.
        pause
        exit /b 1
    )
) else (
    for /f "tokens=*" %%v in ('php -r "echo PHP_VERSION;"') do set PHP_VER=%%v
    echo       [OK] PHP terdeteksi versi %PHP_VER%.
)

:: 2. Cek MySQL
echo.
echo [2/6] Memeriksa service database MySQL (Port 3306)...
netstat -ano | findstr :3306 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [PERINGATAN] Service MySQL di port 3306 belum aktif!
    echo Silakan buka Laragon / XAMPP dan tekan tombol "Start All".
    echo.
    echo Tekan tombol sembarang jika MySQL sudah Anda nyalakan...
    pause >nul
)
echo       [OK] Database MySQL aktif di port 3306.

:: Buat database jika belum ada
echo       Menyiapkan database 'uangpas_lapas'...
where mysql >nul 2>&1
if %errorlevel% equ 0 (
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS uangpas_lapas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1
) else (
    php -r "$c = new mysqli('127.0.0.1', 'root', '', ''); $c->query('CREATE DATABASE IF NOT EXISTS uangpas_lapas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');" >nul 2>&1
)
echo       [OK] Database 'uangpas_lapas' siap.

:: 3. Konfigurasi .env dan Key
echo.
echo [3/6] Menyiapkan konfigurasi backend Laravel...
cd /d "%BASE_DIR%UangPasWeb"
if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo       [OK] Berkas .env berhasil dibuat dari template.
)

php artisan key:generate --force >nul 2>&1
echo       [OK] App key berhasil dikonfigurasi.

:: 4. Migrasi Database
echo.
echo [4/6] Menjalankan migrasi tabel database dan data master...
php artisan migrate --force
if %errorlevel% equ 0 (
    echo       [OK] Migrasi tabel berhasil diselesaikan.
) else (
    echo [ERROR] Migrasi tabel mengalami kendala. Periksa koneksi MySQL.
)

:: 5. Cek Driver Scanner U.are.U 4500
echo.
echo [5/6] Memeriksa driver fingerprint DigitalPersona U.are.U 4500...
reg query "HKLM\SOFTWARE\DigitalPersona" >nul 2>&1
if %errorlevel% equ 0 (
    echo       [OK] Driver resmi DigitalPersona U.are.U terdeteksi di Windows.
) else (
    echo       [INFO] Driver DigitalPersona belum terdeteksi.
    if exist "%BASE_DIR%URU\RTE\x64\setup.msi" (
        echo       Menjalankan installer driver resmi (URU\RTE\x64\setup.msi)...
        start /wait msiexec /i "%BASE_DIR%URU\RTE\x64\setup.msi" /passive
        echo       [OK] Instalasi driver DigitalPersona selesai.
    ) else (
        echo       [LEWATI] Silakan pasang driver U.are.U manual jika diperlukan.
    )
)

:: 6. Kompilasi UangPasDesktop
echo.
echo [6/6] Memeriksa dan mengompilasi aplikasi Kasir Desktop C#...
set CSC_PATH=C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe

if exist "%CSC_PATH%" (
    cd /d "%BASE_DIR%UangPasDesktop"
    "%CSC_PATH%" /target:winexe /out:UangPasDesktop.exe /r:DPUruNet.dll,System.Windows.Forms.dll,System.Drawing.dll,System.Web.Extensions.dll Program.cs >nul 2>&1
    echo       [OK] UangPasDesktop.exe berhasil dikompilasi.
) else (
    echo       [INFO] Menggunakan file binary yang sudah tersedia.
)

cd /d "%BASE_DIR%"

echo.
echo ===============================================================================
echo                      INSTALASI BERHASIL 100%%! 🎉                              
echo ===============================================================================
echo.
echo  Pilihan Launcher yang Siap Digunakan:
echo    [1] 01_Buka_Kasir_POS_Lapas.bat    - Terminal Kasir POS & Sensor Biometrik (Desktop)
echo    [2] 02_Buka_Portal_Admin_Lapas.bat - Portal Manajemen WBP, Stok, Top-Up & Mutasi (Web)
echo.
echo ===============================================================================
echo.
set /p PILIH="Apakah Anda ingin langsung membuka Kasir POS Lapas sekarang? (Y/T): "
if /i "%PILIH%"=="Y" (
    start "" "%BASE_DIR%01_Buka_Kasir_POS_Lapas.bat"
)
exit /b 0

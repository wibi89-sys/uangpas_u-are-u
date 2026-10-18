@echo off
chcp 65001 >nul
title UangPas Lapas - Terminal Kasir POS & Biometrik
color 0A

echo ===============================================================================
echo            UANGPAS LAPAS - TERMINAL KASIR KOPERASI & BIOMETRIK                  
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
cd /d "%BASE_DIR%"

:: Cek & Jalankan Service Database MySQL (Port 3306) jika belum aktif
netstat -ano | findstr :3306 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Menyalakan service database MySQL di background...
    if exist "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" (
        start /min "MySQL Database" "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini"
        timeout /t 2 >nul
    ) else if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start /min "MySQL Database" "C:\xampp\mysql\bin\mysqld.exe"
        timeout /t 2 >nul
    ) else (
        echo [PERINGATAN] Service MySQL belum aktif! Pastikan Laragon/XAMPP sudah Anda nyalakan.
    )
) else (
    echo [OK] Database MySQL aktif di port 3306.
)

:: Cek & Jalankan Backend Laravel API di Port 8000 jika belum aktif
netstat -ano | findstr :8000 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Menjalankan service backend API UangPas di background...
    start /min "UangPas Backend API" php "%BASE_DIR%UangPasWeb\artisan" serve --host=0.0.0.0 --port=8000
    timeout /t 2 >nul
) else (
    echo [OK] Backend API UangPas aktif di port 8000.
)

:: Pastikan UangPasBridge yang mengunci USB sensor tidak berjalan
taskkill /f /im UangPasBridge.exe >nul 2>&1

:: Buka Aplikasi Kasir Desktop
echo [INFO] Membuka Terminal Kasir POS Lapas...
cd /d "%BASE_DIR%UangPasDesktop"
start "" "%BASE_DIR%UangPasDesktop\UangPasDesktop.exe"
timeout /t 2 >nul
exit

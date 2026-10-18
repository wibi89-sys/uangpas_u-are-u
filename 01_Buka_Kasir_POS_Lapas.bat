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

:: Cek & Jalankan Backend Laravel API di Port 8000 jika belum aktif
netstat -ano | findstr :8000 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Menjalankan service backend API UangPas di background...
    start /min "UangPas Backend API" php "%BASE_DIR%UangPasWeb\artisan" serve --host=127.0.0.1 --port=8000
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

@echo off
chcp 65001 >nul
title UangPas Lapas - Kasir POS Modern
color 0A

echo ===============================================================================
echo                UANGPAS LAPAS - KASIR POS MODERN (CSS)                         
echo           Koperasi Dompet Digital & Biometrik DigitalPersona U.are.U           
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
cd /d "%BASE_DIR%"

:: 1. Cek & Nyalakan Backend Laravel di Port 8000
echo [1/3] Memeriksa backend server Laravel (Port 8000)...
netstat -ano | findstr :8000 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo       Menjalankan server Laravel di background...
    start /min "UangPas Laravel Backend" php "%BASE_DIR%UangPasWeb\artisan" serve --host=127.0.0.1 --port=8000
    timeout /t 2 >nul
) else (
    echo       [OK] Server Laravel sudah aktif.
)

:: 2. Cek & Nyalakan UangPas Hardware Bridge di Port 8080
echo.
echo [2/3] Memeriksa Hardware Bridge Scanner U.are.U 4500 (Port 8080)...
netstat -ano | findstr :8080 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo       Menjalankan Hardware Bridge service...
    start /min "UangPas Biometric Bridge" "%BASE_DIR%UangPasBridge\UangPasBridge.exe"
    timeout /t 1 >nul
) else (
    echo       [OK] Hardware Bridge aktif di port 8080.
)

:: 3. Buka Halaman POS Modern di Browser
echo.
echo [3/3] Membuka antarmuka Kasir POS Modern di browser...
start http://localhost:8000/pos

timeout /t 2 >nul
exit

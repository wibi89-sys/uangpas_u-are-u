@echo off
title UangPas Lapas - Terminal Kasir POS & Biometrik
echo ========================================================
echo   UANGPAS LAPAS - TERMINAL KASIR KOPERASI & BIOMETRIK
echo ========================================================
echo.

:: Cek apakah server backend Laravel port 8000 sudah berjalan
netstat -ano | findstr :8000 | findstr LISTENING >nul
if %errorlevel% neq 0 (
    echo [INFO] Menjalankan service backend Laravel di port 8000...
    start /min "UangPas Backend Server" php "%~dp0UangPasWeb\artisan" serve --host=127.0.0.1 --port=8000
    timeout /t 2 >nul
) else (
    echo [OK] Backend server aktif di port 8000.
)

cd /d "%~dp0UangPasDesktop"
echo Membuka aplikasi kasir POS desktop...
start "" "%~dp0UangPasDesktop\UangPasDesktop.exe"
timeout /t 2 >nul
exit


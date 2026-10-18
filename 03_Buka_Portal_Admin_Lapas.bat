@echo off
chcp 65001 >nul
title UangPas Lapas - Portal Manajemen & Mutasi
color 0E

echo ===============================================================================
echo            UANGPAS LAPAS - PORTAL MANAJEMEN WBP & MUTASI                      
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
cd /d "%BASE_DIR%"

:: Cek & Nyalakan Backend Laravel di Port 8000
netstat -ano | findstr :8000 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Menjalankan server Laravel di background...
    start /min "UangPas Laravel Backend" php "%BASE_DIR%UangPasWeb\artisan" serve --host=127.0.0.1 --port=8000
    timeout /t 2 >nul
)

start http://localhost:8000/
timeout /t 1 >nul
exit

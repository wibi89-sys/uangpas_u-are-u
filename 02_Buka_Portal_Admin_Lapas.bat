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

:: Cek & Jalankan Backend Laravel di Port 8000 jika belum aktif
netstat -ano | findstr :8000 | findstr LISTENING >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Menjalankan server portal admin di background...
    start /min "UangPas Laravel Backend" php "%BASE_DIR%UangPasWeb\artisan" serve --host=0.0.0.0 --port=8000
    timeout /t 2 >nul
) else (
    echo [OK] Server portal admin aktif di port 8000.
)

:: Buka Portal Admin di Web Browser
echo [INFO] Membuka Portal Admin di browser default...
start http://localhost:8000/
timeout /t 1 >nul
exit

@echo off
chcp 65001 >nul
title UangPas Lapas - Buat Shortcut Desktop Berlogo Sidik Jari
color 0B

echo ===============================================================================
echo        PEMBUAT SHORTCUT DESKTOP UANGPAS DENGAN LOGO SIDIK JARI
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
set DESKTOP_DIR=%USERPROFILE%\Desktop
set EXE_PATH=%BASE_DIR%UangPasDesktop\UangPasDesktop.exe
set ICO_PATH=%BASE_DIR%UangPasDesktop\app.ico
set BAT_PATH=%BASE_DIR%01_Buka_Kasir_POS_Lapas.bat

echo Membuat shortcut di Desktop: %DESKTOP_DIR%...
echo.

:: 1. Buat Shortcut untuk Launcher Utama (01_Buka_Kasir_POS_Lapas.bat) yang otomatis cek server + berlogo sidik jari
powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%DESKTOP_DIR%\UangPas Kasir POS.lnk'); $s.TargetPath = '%BAT_PATH%'; $s.WorkingDirectory = '%BASE_DIR%'; $s.IconLocation = '%ICO_PATH%,0'; $s.Description = 'Terminal Kasir & Biometrik UangPas Lapas'; $s.Save()"

:: 2. Buat Shortcut langsung ke file Exe (UangPasDesktop.exe)
powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%DESKTOP_DIR%\UangPas Desktop (Direct EXE).lnk'); $s.TargetPath = '%EXE_PATH%'; $s.WorkingDirectory = '%BASE_DIR%UangPasDesktop'; $s.IconLocation = '%EXE_PATH%,0'; $s.Description = 'Aplikasi Kasir Desktop UangPas Lapas'; $s.Save()"

echo [OK] Shortcut Desktop berhasil dibuat/diperbarui!
echo      - UangPas Kasir POS.lnk         (Rekomendasi: otomatis nyalakan server + logo fingerprint)
echo      - UangPas Desktop (Direct EXE).lnk (Langsung ke file UangPasDesktop.exe)
echo.
echo ===============================================================================
if /i "%1"=="/silent" exit /b 0
if /i "%1"=="--silent" exit /b 0
pause

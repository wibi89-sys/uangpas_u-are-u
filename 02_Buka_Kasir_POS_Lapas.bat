@echo off
title UangPas Lapas - Terminal Kasir POS & Biometrik
echo ========================================================
echo   UANGPAS LAPAS - TERMINAL KASIR KOPERASI & BIOMETRIK
echo ========================================================
echo.
cd /d "%~dp0UangPasDesktop"
echo Membuka aplikasi kasir POS desktop...
start "" "%~dp0UangPasDesktop\UangPasDesktop.exe"
timeout /t 2 >nul
exit

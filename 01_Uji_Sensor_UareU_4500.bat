@echo off
title UangPas - Uji Sensor Sidik Jari
echo ========================================================
echo   UANGPAS LAPAS - PENGUJIAN SCANNER SIDIK JARI
echo ========================================================
echo.
cd /d "%~dp0UangPasDesktop"
echo Menjalankan aplikasi pengujian sensor...
start "" "%~dp0UangPasDesktop\Uji_Sensor_UareU.exe"
timeout /t 2 >nul
exit

@echo off
title UangPas - Uji Sensor U.are.U 4500
echo ========================================================
echo   UANGPAS LAPAS - PENGUJIAN SCANNER SIDIK JARI U.ARE.U
echo ========================================================
echo.
cd /d "%~dp0UangPasDesktop"
echo Menjalankan aplikasi pengujian sensor...
start "" "%~dp0UangPasDesktop\Uji_Sensor_UareU.exe"
timeout /t 2 >nul
exit

@echo off
title DigitalPersona SDK - Official Test Utility
echo ========================================================
echo   DIGITALPERSONA U.ARE.U SDK - OFFICIAL SAMPLE SUITE
echo ========================================================
echo.
cd /d "%~dp0UangPasDesktop"
if exist "%~dp0UangPasDesktop\UareUSampleCSharp.exe" (
    echo Menjalankan aplikasi uji resmi bawaan SDK...
    start "" "%~dp0UangPasDesktop\UareUSampleCSharp.exe"
) else (
    echo [ERROR] File sample tidak ditemukan!
    pause
)
timeout /t 2 >nul
exit

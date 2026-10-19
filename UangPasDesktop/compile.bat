@echo off
title Kompilasi UangPas Desktop POS
color 0A

echo ===============================================================================
echo                KOMPILASI TERMINAL KASIR DESKTOP UANGPAS
echo ===============================================================================
echo.

set CSC_PATH=C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe
if not exist "%CSC_PATH%" set CSC_PATH=C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe

if not exist "%CSC_PATH%" (
    echo [ERROR] Kompiler C# .NET Framework tidak ditemukan di:
    echo %CSC_PATH%
    pause
    exit /b 1
)

echo Mengompilasi kode sumber C# (Modular Architecture)...
"%CSC_PATH%" @"%~dp0UangPasDesktop.rsp"
if %errorlevel% equ 0 (
    echo.
    echo [OK] UangPasDesktop.exe BERHASIL DIKOMPILASI!
    echo Lokasi: %~dp0UangPasDesktop.exe
) else (
    echo.
    echo [ERROR] Kompilasi mengalami kendala. Periksa pesan error di atas.
)

echo.
echo ===============================================================================
pause
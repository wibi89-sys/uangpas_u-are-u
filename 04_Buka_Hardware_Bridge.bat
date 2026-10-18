@echo off
chcp 65001 >nul
title UangPas Hardware Bridge - U.are.U 4500
color 0D

echo ===============================================================================
echo            UANGPAS BIOMETRIC HARDWARE BRIDGE - U.ARE.U 4500                   
echo                     REST API Service Port 8080                                
echo ===============================================================================
echo.

set BASE_DIR=%~dp0
cd /d "%BASE_DIR%UangPasBridge"

start "" "%BASE_DIR%UangPasBridge\UangPasBridge.exe"
timeout /t 1 >nul
exit

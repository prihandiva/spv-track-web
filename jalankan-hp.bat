@echo off
title SPV-Track Mobile Tunnel
color 0b
cls
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-tunnel.ps1"
pause

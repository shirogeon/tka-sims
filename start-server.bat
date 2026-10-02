@echo off
title CBT Pusmendik Server
echo ========================================================
echo   SIMULASI CBT TKA PUSMENDIK (Kemendikbud)
echo   Server siap dijalankan di http://localhost:8000
echo ========================================================
echo.
echo Membuka browser...
start http://localhost:8000
echo Menjalankan PHP Server (Tekan Ctrl+C untuk berhenti)...
d:\xampp\php\php.exe -S localhost:8000
pause

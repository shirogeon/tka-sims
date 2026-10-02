@echo off
title Console Pengawas CBT Pusmendik - Token Unlock
color 1F

:loop
cls
echo ======================================================================
echo    PANEL PENGAWAS CBT PUSMENDIK - KONSOL TOKEN RESET UJIAN
echo ======================================================================
echo.

if exist "database\TOKEN_PENGAWAS.txt" (
    type "database\TOKEN_PENGAWAS.txt"
    echo.
    echo   [!] Berikan 6 karakter TOKEN di atas kepada peserta yang terkunci.
    echo.
) else (
    echo   [ STATUS: AMAN ]
    echo   Saat ini tidak ada peserta yang terkunci karena pelanggaran.
    echo.
)

echo ======================================================================
echo   Tekan [R] untuk Refresh token terbaru, atau [Q] untuk Keluar.
choice /c rq /n /t 3 /d r >nul
if errorlevel 2 goto exit
goto loop

:exit
exit

@echo off
setlocal
cd /d "%~dp0"

if not exist "C:\php84\php.exe" (
    echo PHP 8.4 tidak ditemukan di C:\php84.
    echo Periksa instalasi PHP sebelum menjalankan aplikasi.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo Dependensi PHP belum terpasang. Jalankan composer install terlebih dahulu.
    pause
    exit /b 1
)

if not exist "node_modules\.bin\vp.cmd" (
    echo Dependensi frontend belum terpasang. Jalankan npm.cmd install terlebih dahulu.
    pause
    exit /b 1
)

set "PATH=C:\php84;%PATH%"

echo Menjalankan G-Laundry menggunakan PHP 8.4...
echo Pastikan MySQL XAMPP sudah aktif.
echo Buka http://127.0.0.1:8000 setelah server siap.
echo.

php artisan dev --inline

echo.
echo Proses web sudah berhenti.
pause

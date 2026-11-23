PERBAIKAN DROPDOWN DURASI PAKET LAUNDRY

Salin kedua file di dalam ZIP ke root folder project Laravel dan izinkan file lama ditimpa:
- resources/views/paket-laundry/form.blade.php
- public/css/admin.css

Perubahan: daftar durasi 1-7 hari memakai menu yang posisinya menyesuaikan ruang layar, bisa digulir jika ruang terbatas, dan tidak lagi menutupi tombol form secara tidak terduga. Pemilihan tetap mengirim nilai durasi ke server dan validasi wajib tetap berlaku.

Setelah menyalin, refresh halaman dengan Ctrl+F5. Jika Laravel masih menampilkan cache lama, jalankan `php artisan view:clear` dari folder project.

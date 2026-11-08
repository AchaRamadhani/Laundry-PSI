<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CucianController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UpdateStatusController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PaketLaundryController;
use App\Http\Controllers\SegmentasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\KontakController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
Route::get('/kontak', [KontakController::class, 'index'])->name('kontak.index');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('cucian', CucianController::class);

    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/cetak', [TransaksiController::class, 'cetak'])->name('transaksi.cetak');
    Route::get('/transaksi/aktivitas', [TransaksiController::class, 'aktivitas'])->name('transaksi.aktivitas');

    Route::get('/update-status', [UpdateStatusController::class, 'index'])->name('update-status.index');
    Route::put('/update-status/{transaksi}', [UpdateStatusController::class, 'update'])->name('update-status.update');
    Route::get('/update-status/riwayat', [UpdateStatusController::class, 'riwayat'])->name('update-status.history');

    Route::get('/pelanggan/aktivitas', [PelangganController::class, 'aktivitas'])->name('pelanggan.aktivitas');

    // Modul Pelanggan
    Route::resource('pelanggan', PelangganController::class);

    Route::resource('paket-laundry', PaketLaundryController::class)
        ->parameters(['paket-laundry' => 'paket']);

    Route::get('/segmentasi', [SegmentasiController::class, 'index'])->name('segmentasi.index');

    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('settings.update');

});

require __DIR__.'/settings.php';

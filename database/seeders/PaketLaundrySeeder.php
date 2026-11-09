<?php

namespace Database\Seeders;

use App\Models\PaketLaundry;
use Illuminate\Database\Seeder;

class PaketLaundrySeeder extends Seeder
{
    /**
     * Add or refresh the package records shown in the G'Laundry prototype.
     */
    public function run(): void
    {
        $packages = [
            [
                'nama_paket' => 'Cuci Kering',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pakaian dicuci dan dikeringkan.',
                'harga_mulai' => 9000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Cuci Lipat',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pakaian dicuci dan dilipat rapi.',
                'harga_mulai' => 12000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Cuci Setrika Lipat',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pakaian dicuci, disetrika, dan dilipat.',
                'harga_mulai' => 21000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Setrika',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pakaian hanya disetrika.',
                'harga_mulai' => 12000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Pengering & Lipat',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pakaian dikeringkan dan dilipat.',
                'harga_mulai' => 9000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Baju Bayi',
                'kategori' => 'Kiloan',
                'deskripsi' => 'Pencucian khusus pakaian bayi.',
                'harga_mulai' => 15000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Satuan',
                'kategori' => 'Satuan',
                'deskripsi' => 'Cucian dihitung berdasarkan jumlah item. Tarif pada prototipe berkisar Rp 8.000–Rp 60.000.',
                'harga_mulai' => 8000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Express',
                'kategori' => 'Khusus',
                'deskripsi' => 'Layanan prioritas, harga mulai Rp 25.000 per 3 kg.',
                'harga_mulai' => 25000,
                'durasi' => 2,
            ],
            [
                'nama_paket' => 'Treatment',
                'kategori' => 'Khusus',
                'deskripsi' => 'Perawatan khusus noda dan bahan. Tarif pada prototipe berkisar Rp 7.000–Rp 25.000 per pcs.',
                'harga_mulai' => 7000,
                'durasi' => 2,
            ],
        ];

        foreach ($packages as $package) {
            PaketLaundry::query()->updateOrCreate(
                ['nama_paket' => $package['nama_paket']],
                [...$package, 'status' => 'aktif'],
            );
        }
    }
}

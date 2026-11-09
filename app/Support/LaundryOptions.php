<?php

namespace App\Support;

final class LaundryOptions
{
    public const STATUSES = [
        'antrean' => 'Antrean',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    /**
     * Canonical services shown in public pages and admin service filters.
     * Filter options remain available even before package rows have been added.
     */
    public const SERVICES = [
        'cuci-kering' => [
            'nama' => 'Cuci Kering',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'washer',
            'deskripsi' => 'Pakaian dicuci bersih menggunakan detergen berkualitas.',
            'aliases' => [],
        ],
        'cuci-lipat' => [
            'nama' => 'Cuci Lipat',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'fold',
            'deskripsi' => 'Cucian bersih dan wangi, kemudian dilipat dengan rapi.',
            'aliases' => [],
        ],
        'cuci-setrika-lipat' => [
            'nama' => 'Cuci Setrika Lipat',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'shirt',
            'deskripsi' => 'Cuci bersih, disetrika, dan dilipat agar siap disimpan.',
            'aliases' => [],
        ],
        'setrika' => [
            'nama' => 'Setrika',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'iron',
            'deskripsi' => 'Pakaian disetrika rapi sesuai jenis kain.',
            'aliases' => ['setrika saja'],
        ],
        'pengering-lipat' => [
            'nama' => 'Pengering & Lipat',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'dryer',
            'deskripsi' => 'Pakaian dikeringkan lalu dilipat dengan rapi.',
            'aliases' => ['pengering dan lipat'],
        ],
        'baju-bayi' => [
            'nama' => 'Baju Bayi',
            'kategori' => 'kiloan',
            'unit' => 'kg',
            'icon' => 'baby',
            'deskripsi' => 'Perawatan lembut untuk pakaian bayi.',
            'aliases' => [],
        ],
        'satuan' => [
            'nama' => 'Satuan',
            'kategori' => 'non-kiloan',
            'unit' => 'pcs',
            'icon' => 'hanger',
            'deskripsi' => 'Layanan pencucian dihitung per potong atau per pcs.',
            'aliases' => [],
        ],
        'express' => [
            'nama' => 'Express',
            'kategori' => 'non-kiloan',
            'unit' => 'kg',
            'icon' => 'express',
            'deskripsi' => 'Pengerjaan lebih cepat untuk kebutuhan mendesak.',
            'aliases' => [],
        ],
        'treatment' => [
            'nama' => 'Treatment',
            'kategori' => 'non-kiloan',
            'unit' => 'pcs',
            'icon' => 'treatment',
            'deskripsi' => 'Perawatan khusus sesuai kebutuhan cucian Anda.',
            'aliases' => [],
        ],
    ];

    /**
     * Mengambil daftar nama layanan untuk filter.
     *
     * @return list<string>
     */
    public static function serviceNamesForFilter(?string $key): array
    {
        $service = self::SERVICES[$key ?? ''] ?? null;

        if (! $service) {
            return [];
        }

        return array_values(array_unique(array_map(
            static fn (string $name): string => strtolower(trim($name)),
            array_merge([$service['nama']], $service['aliases'])
        )));
    }
}

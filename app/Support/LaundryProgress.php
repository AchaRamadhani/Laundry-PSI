<?php

namespace App\Support;

use App\Models\Transaksi;

final class LaundryProgress
{
    public const STAGES = [
        'antrean' => 'Antrean',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    /**
     * Tahapan yang diperlukan berdasarkan layanan pada transaksi.
     * Jika transaksi memiliki beberapa layanan, tahap yang diperlukan digabung.
     *
     * @return list<string>
     */
    public static function requiredStages(Transaksi $transaksi): array
    {
        $services = $transaksi->detailTransaksi
            ->map(fn ($detail) => self::normalizeService((string) $detail->paketLaundry?->nama_paket))
            ->filter()
            ->unique()
            ->values();

        if ($services->isEmpty()) {
            return array_keys(self::STAGES);
        }

        $required = [];
        foreach ($services as $service) {
            $stages = match (true) {
                str_contains($service, 'cuci setrika lipat') => array_keys(self::STAGES),
                $service === 'setrika' || str_contains($service, 'setrika saja') => [
                    'antrean', 'diproses', 'disetrika', 'selesai', 'diambil',
                ],
                str_contains($service, 'pengering') => [
                    'antrean', 'diproses', 'dikeringkan', 'selesai', 'diambil',
                ],
                str_contains($service, 'cuci kering')
                    || str_contains($service, 'cuci lipat')
                    || str_contains($service, 'baju bayi') => [
                        'antrean', 'diproses', 'dicuci', 'dikeringkan', 'selesai', 'diambil',
                    ],
                default => array_keys(self::STAGES),
            };

            $required = array_merge($required, $stages);
        }

        // Keep any stage already recorded so edits to a package never erase real history.
        foreach ($transaksi->statusLaundry as $entry) {
            $key = self::normalizeStatus((string) $entry->status);
            if (isset(self::STAGES[$key])) {
                $required[] = $key;
            }
        }

        $current = self::normalizeStatus((string) $transaksi->status_cucian);
        if (isset(self::STAGES[$current])) {
            $required[] = $current;
        }

        $required = array_unique($required);

        return array_values(array_filter(
            array_keys(self::STAGES),
            static fn (string $key): bool => in_array($key, $required, true)
        ));
    }

    /** @return list<string> */
    public static function availableTargets(Transaksi $transaksi): array
    {
        $current = self::normalizeStatus((string) $transaksi->status_cucian);
        $currentIndex = array_search($current, array_keys(self::STAGES), true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $required = self::requiredStages($transaksi);

        return array_values(array_filter(
            array_keys(self::STAGES),
            static function (string $key, int $index) use ($current, $currentIndex, $required): bool {
                if ($key === $current) {
                    return true;
                }

                return $index > $currentIndex && in_array($key, $required, true);
            },
            ARRAY_FILTER_USE_BOTH
        ));
    }

    public static function normalizeStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'antrian' => 'antrean',
            'proses' => 'diproses',
            'pengeringan' => 'dikeringkan',
            default => strtolower(trim($status)),
        };
    }

    private static function normalizeService(string $service): string
    {
        $service = mb_strtolower(trim($service));
        $service = str_replace(['&', '+'], ' dan ', $service);
        $service = preg_replace('/\s+/u', ' ', $service) ?? $service;

        return trim($service);
    }
}

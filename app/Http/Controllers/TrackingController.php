<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Models\Transaksi;
use App\Support\OperationalHours;
use App\Support\WhatsAppContact;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    private const STAGES = [
        'antrean' => 'Antrean',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    private const STATUS_ALIASES = [
        'antrian' => 'antrean',
        'diterima' => 'antrean',
        'proses' => 'diproses',
        'pengeringan' => 'dikeringkan',
        'kering' => 'dikeringkan',
        'siap diambil' => 'selesai',
        'picked up' => 'diambil',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'token_tracking' => ['nullable', 'string', 'max:12'],
        ]);

        $tokenTracking = strtoupper(trim($validated['token_tracking'] ?? ''));
        $transaksi = null;

        if ($tokenTracking !== '') {
            $transaksi = Transaksi::query()
                ->with([
                    'pelanggan:id,nama_pelanggan,no_telp',
                    'detailTransaksi.paketLaundry:id,nama_paket',
                    'statusLaundry' => fn ($query) => $query->orderBy('waktu_status'),
                ])
                ->where('tracking_token', $tokenTracking)
                ->first();
        }

        $currentKey = $transaksi ? $this->normalizeStatus($transaksi->status_cucian) : null;
        $currentIndex = $currentKey ? array_search($currentKey, array_keys(self::STAGES), true) : false;
        $currentIndex = $currentIndex === false ? -1 : $currentIndex;
        $historyByStage = [];

        if ($transaksi !== null) {
            foreach ($transaksi->statusLaundry as $entry) {
                $key = $this->normalizeStatus($entry->status);

                if (isset(self::STAGES[$key])) {
                    $tanggal = Carbon::parse($entry->waktu_status);
                    $tanggal->locale('id');

                    $historyByStage[$key] = $tanggal->translatedFormat('d M Y, H.i');
                }
            }
        }

        $settings = LaundrySetting::query()->first();
        $whatsappData = WhatsAppContact::forAdmin($settings);
        $operationalData = OperationalHours::forSettings($settings);

        return view('tracking.index', array_merge([
            'tokenTracking' => $tokenTracking,
            'transaksi' => $transaksi,
            'stages' => self::STAGES,
            'currentKey' => $currentKey,
            'currentIndex' => $currentIndex,
            'historyByStage' => $historyByStage,
            'address' => $settings?->address ?: 'Jl. Poros Hartaco Indah, Kel. Sudiang Raya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242',
        ], $operationalData, $whatsappData));
    }

    private function normalizeStatus(?string $status): string
    {
        $key = strtolower(trim((string) $status));

        return self::STATUS_ALIASES[$key] ?? $key;
    }
}

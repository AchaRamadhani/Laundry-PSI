<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Support\LaundryDateFormatter;
use App\Support\LaundryProgress;
use App\Support\OperationalHours;
use App\Support\TrackingToken;
use App\Support\WhatsAppContact;
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

    public function index(Request $request, ?string $token = null): View
    {
        $tokenInput = $token ?? $request->query('token_tracking');
        $tokenTracking = strtoupper(trim(is_string($tokenInput) ? $tokenInput : ''));
        $tokenValidationError = null;
        $tokenWasSubmitted = $token !== null || $request->query->has('token_tracking');

        if ($tokenWasSubmitted && $tokenTracking === '') {
            $tokenValidationError = 'Token tracking wajib diisi.';
        } elseif ($tokenTracking !== '' && ! preg_match('/\A[A-Z0-9]{20}\z/', $tokenTracking)) {
            $tokenValidationError = 'Token tracking harus terdiri dari 20 karakter berupa huruf dan angka.';
        }

        $transaksi = null;

        if ($tokenTracking !== '' && $tokenValidationError === null) {
            $transaksi = TrackingToken::resolve($tokenTracking);
            $transaksi?->load([
                'pelanggan:id,nama_pelanggan,no_telp',
                'detailTransaksi.paketLaundry:id,nama_paket',
                'statusLaundry' => fn ($query) => $query->orderBy('waktu_status'),
            ]);
        }

        $currentKey = $transaksi ? $this->normalizeStatus($transaksi->status_cucian) : null;
        $currentIndex = $currentKey ? array_search($currentKey, array_keys(self::STAGES), true) : false;
        $currentIndex = $currentIndex === false ? -1 : $currentIndex;
        $historyByStage = [];
        $applicableStages = [];

        if ($transaksi !== null) {
            $applicableStages = LaundryProgress::requiredStages($transaksi);
            foreach ($transaksi->statusLaundry as $entry) {
                $key = $this->normalizeStatus($entry->status);

                if (isset(self::STAGES[$key])) {
                    $historyByStage[$key] = LaundryDateFormatter::format($entry->waktu_status, true);
                }
            }
        }

        $settings = LaundrySetting::query()->first();
        $whatsappData = WhatsAppContact::forAdmin($settings);
        $operationalData = OperationalHours::forSettings($settings);

        return view('tracking.index', array_merge([
            'tokenTracking' => $tokenTracking,
            'tokenValidationError' => $tokenValidationError,
            'transaksi' => $transaksi,
            'stages' => self::STAGES,
            'currentKey' => $currentKey,
            'currentIndex' => $currentIndex,
            'historyByStage' => $historyByStage,
            'applicableStages' => $applicableStages,
            'address' => $settings?->address ?: 'Jl. Poros Hartaco Indah, Kel. Sudiang Raya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242',
        ], $operationalData, $whatsappData));
    }

    private function normalizeStatus(?string $status): string
    {
        $key = strtolower(trim((string) $status));

        return self::STATUS_ALIASES[$key] ?? $key;
    }
}

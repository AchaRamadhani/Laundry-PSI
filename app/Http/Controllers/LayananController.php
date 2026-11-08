<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Support\LaundryOptions;
use App\Support\OperationalHours;
use App\Support\WhatsAppContact;

class LayananController extends Controller
{
    public function index()
    {
        $settings = LaundrySetting::query()->first();
        $whatsappData = WhatsAppContact::forAdmin($settings);
        $operationalData = OperationalHours::forSettings($settings);
        $services = array_values(LaundryOptions::SERVICES);

        return view('layanan.index', array_merge([
            'layananKiloan' => array_values(array_filter($services, fn ($service) => $service['kategori'] === 'kiloan')),
            'layananNonKiloan' => array_values(array_filter($services, fn ($service) => $service['kategori'] === 'non-kiloan')),
            'address' => $settings?->address ?: 'Jl. Poros Hartaco Indah, Kel. Sudiang Raya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242',
        ], $operationalData, $whatsappData));
    }
}

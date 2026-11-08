<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Support\OperationalHours;
use App\Support\WhatsAppContact;

class HomeController extends Controller
{
    public function index()
    {
        $settings = LaundrySetting::query()->first();
        $whatsappData = WhatsAppContact::forAdmin($settings);
        $operationalData = OperationalHours::forSettings($settings);

        return view('welcome', array_merge($operationalData, [
            'address' => $settings?->address ?: 'Jl. Poros Hartaco Indah, Kel. Sudiang Raya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242',
        ], $whatsappData));
    }
}

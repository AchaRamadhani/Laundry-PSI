<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Support\OperationalHours;
use App\Support\WhatsAppContact;
use Illuminate\Contracts\View\View;

class KontakController extends Controller
{
    public function index(): View
    {
        $settings = LaundrySetting::query()->first();
        $whatsappData = WhatsAppContact::forAdmin($settings);
        $operationalData = OperationalHours::forSettings($settings);

        return view('kontak.index', array_merge($operationalData, $whatsappData, [
            'address' => $settings?->address ?: 'Jl. Poros Hartaco Indah, Kel. Sudiang Raya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242',
            'faqs' => [
                [
                    'question' => 'Berapa lama proses cucian selesai?',
                    'answer' => 'Waktu pengerjaan mengikuti layanan yang dipilih. Estimasi selesai tercantum pada informasi transaksi dan statusnya dapat dilihat menggunakan token tracking.',
                ],
                [
                    'question' => 'Apakah tersedia layanan antar jemput?',
                    'answer' => 'Tanyakan ketersediaan layanan antar jemput langsung kepada admin melalui WhatsApp.',
                ],
                [
                    'question' => 'Apakah saya akan mendapatkan pemberitahuan ketika cucian selesai?',
                    'answer' => 'Anda dapat memeriksa perubahan status cucian melalui halaman Tracking Cucian. Hubungi admin melalui WhatsApp jika Anda memerlukan bantuan atau pembaruan.',
                ],
                [
                    'question' => 'Berapa lama proses pengerjaan laundry?',
                    'answer' => 'Durasi bergantung pada jenis layanan dan jumlah cucian. Estimasi yang berlaku akan dicantumkan pada transaksi Anda.',
                ],
            ],
        ]));
    }
}

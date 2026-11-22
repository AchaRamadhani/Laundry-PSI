<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Models\Transaksi;
use App\Support\TrackingToken;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Response;

class NotaDigitalController extends Controller
{
    private const STATUS_LABELS = [
        'antrean' => 'Antrean',
        'antrian' => 'Antrean',
        'proses' => 'Diproses',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'pengeringan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    public function show(string $token): Response
    {
        return response()
            ->view('nota.digital', $this->notaData($this->findTransaksi($token)))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function image(string $token): Response
    {
        return response()
            ->view('nota.image', $this->notaData($this->findTransaksi($token)))
            ->header('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function findTransaksi(string $token): Transaksi
    {
        $transaksi = TrackingToken::resolve($token);
        abort_if($transaksi === null, 404);

        return $transaksi->load([
            'pelanggan:id,nama_pelanggan,no_telp',
            'detailTransaksi.paketLaundry:id,nama_paket,kategori',
        ]);
    }

    /** @return array<string, mixed> */
    private function notaData(Transaksi $transaksi): array
    {
        $statusKey = strtolower(trim($transaksi->status_cucian));
        $statusLabel = self::STATUS_LABELS[$statusKey] ?? ucfirst($statusKey);
        $settings = LaundrySetting::query()->first();
        $trackingToken = TrackingToken::forTransaction($transaksi);
        $catatanLines = $this->wrapNote($transaksi->catatan);
        $detailCount = max(1, $transaksi->detailTransaksi->count());
        $trackingUrl = route('tracking.token', ['token' => $trackingToken]);
        $qrSvg = (new Writer(new ImageRenderer(new RendererStyle(240, 2), new SvgImageBackEnd)))
            ->writeString(route('home'));
        $logoPath = public_path('images/logo-g-laundry-footer.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        return [
            'transaksi' => $transaksi,
            'trackingToken' => $trackingToken,
            'statusLabel' => $statusLabel,
            'laundryName' => $settings?->laundry_name ?: "G'Laundry",
            'trackingTokenLines' => str_split($trackingToken, 38),
            'catatanLines' => $catatanLines,
            'address' => $settings?->address,
            'whatsappNumber' => $settings?->whatsapp_number,
            'trackingUrl' => $trackingUrl,
            'trackingHomeUrl' => route('home'),
            'receiptImageUrl' => route('nota.digital.image', ['token' => $trackingToken]),
            'receiptImageHeight' => 956 + (54 * $detailCount) + ($catatanLines !== [] ? 40 + (18 * count($catatanLines)) : 0),
            'qrSvgDataUri' => 'data:image/svg+xml;base64,'.base64_encode($qrSvg),
            'logoDataUri' => $logoDataUri,
        ];
    }

    /** @return list<string> */
    private function wrapNote(?string $note): array
    {
        $note = trim(str_replace(["\r\n", "\r"], "\n", (string) $note));
        if ($note === '') {
            return [];
        }

        $lines = [];
        foreach (explode("\n", $note) as $paragraph) {
            if ($paragraph === '') {
                $lines[] = '';

                continue;
            }

            array_push($lines, ...explode("\n", wordwrap($paragraph, 84, "\n", true)));
        }

        return $lines;
    }
}

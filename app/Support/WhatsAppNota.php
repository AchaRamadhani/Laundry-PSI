<?php

namespace App\Support;

use App\Models\LaundrySetting;
use App\Models\Transaksi;
use Carbon\Carbon;

class WhatsAppNota
{
    /**
     * @return array{url: string, message: string}|null
     */
    public static function forTransaction(Transaksi $transaksi): ?array
    {
        $phone = self::recipientPhone($transaksi);
        if ($phone === null) {
            return null;
        }

        $token = TrackingToken::forTransaction($transaksi);
        $websiteUrl = route('home');
        $namaPelanggan = trim((string) $transaksi->pelanggan?->nama_pelanggan) ?: 'Pelanggan';
        $noTelepon = trim((string) $transaksi->pelanggan?->no_telp) ?: '-';
        $namaAdmin = trim((string) $transaksi->admin?->name) ?: "Admin G'Laundry";
        $statusPembayaran = $transaksi->status_pembayaran_label;
        $metodePembayaran = $transaksi->metode_pembayaran_label;

        $tanggalMasuk = LaundryDateFormatter::format($transaksi->tanggal_masuk)
            .', '.($transaksi->created_at?->format('H.i') ?? '00.00');

        $estimasiSelesai = $transaksi->estimasi_selesai
            ? LaundryDateFormatter::format($transaksi->estimasi_selesai)
            : 'Belum ditentukan';

        $rincianLayanan = $transaksi->detailTransaksi
            ->values()
            ->map(function ($detail, int $index): string {
                $kategori = strtolower((string) $detail->paketLaundry?->kategori);
                $unit = $kategori === 'satuan' ? 'pcs' : 'kg';
                $jumlah = number_format((float) $detail->berat_qty, 2, ',', '.');
                $jumlah = rtrim(rtrim($jumlah, '0'), ',');
                $namaLayanan = $detail->paketLaundry?->nama_paket ?: 'Layanan laundry';
                $subtotal = number_format((float) $detail->subtotal, 0, ',', '.');
                $labelJumlah = $unit === 'pcs' ? 'Jumlah' : 'Berat';

                return implode("\n", [
                    ($index + 1).". {$namaLayanan}",
                    "   {$labelJumlah} : {$jumlah} {$unit}",
                    "   Subtotal : Rp {$subtotal}",
                ]);
            })
            ->implode("\n");
        $catatanTransaksi = trim((string) $transaksi->catatan);
        $bagianCatatan = $catatanTransaksi !== ''
            ? [str_repeat('-', 34), '', '*CATATAN PESANAN*', $catatanTransaksi]
            : [];

        $message = implode("\n", array_merge([
            "G'LAUNDRY",
            'Nota Transaksi Laundry',
            '',
            "*Nomor Nota* : {$transaksi->no_nota}",
            "*Tanggal Masuk* : {$tanggalMasuk}",
            "*Estimasi Selesai* : {$estimasiSelesai}",
            "*Admin* : {$namaAdmin}",
            str_repeat('-', 34),
            '',
            '*INFORMASI PELANGGAN*',
            "Nama Pelanggan : {$namaPelanggan}",
            "No. Telepon : {$noTelepon}",
            str_repeat('-', 34),
            '',
            '*RINCIAN LAYANAN*',
            $rincianLayanan ?: 'Rincian layanan belum tersedia.',
        ], $bagianCatatan, [
            str_repeat('-', 34),
            '',
            '*TOTAL PEMBAYARAN*',
            'Rp '.number_format((float) $transaksi->total_harga, 0, ',', '.'),
            "*Status Pembayaran* : {$statusPembayaran}",
            "*Metode Pembayaran* : {$metodePembayaran}",
            str_repeat('-', 34),
            '',
            '*TOKEN TRACKING*',
            $token,
            '_Masukkan token ini pada halaman Tracking Cucian dan jaga kerahasiaannya agar data pribadi Anda tetap terlindungi._',
            str_repeat('-', 34),
            '',
            "*TAUTAN WEB G'LAUNDRY*",
            $websiteUrl,
            str_repeat('-', 34),
            '',
            '*CARA MELACAK STATUS CUCIAN*',
            "1. Buka tautan web G'Laundry di atas.",
            '2. Pilih menu Tracking Cucian pada halaman website.',
            '3. Masukkan token tracking pada kolom yang tersedia.',
            '4. Klik tombol Cek Status untuk melihat informasi dan perkembangan cucian.',
            '5. Sistem akan menampilkan status cucian terbaru.',
            '6. Jika ada pertanyaan, klik tombol Hubungi Admin WhatsApp di bawah tombol Cek Status.',
        ]));

        return [
            'url' => self::whatsappUrl($phone, $message),
            'message' => $message,
        ];
    }

    /**
     * @return array{url: string, message: string}|null
     */
    public static function forCompletion(Transaksi $transaksi): ?array
    {
        $phone = self::recipientPhone($transaksi);
        if ($phone === null) {
            return null;
        }

        $namaPelanggan = trim((string) $transaksi->pelanggan?->nama_pelanggan) ?: 'Pelanggan';
        $token = TrackingToken::forTransaction($transaksi);
        $statusPembayaran = $transaksi->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas';
        $metodePembayaran = $transaksi->metode_pembayaran_label;
        $totalPembayaran = 'Rp '.number_format((float) $transaksi->total_harga, 0, ',', '.');
        $websiteUrl = route('home');
        $catatanTransaksi = trim((string) $transaksi->catatan);
        $settings = LaundrySetting::query()->first();
        $jamBuka = Carbon::parse($settings?->opening_time ?: '07:00')->format('H.i');
        $mulaiIstirahat = Carbon::parse($settings?->break_start ?: '12:00')->format('H.i');
        $selesaiIstirahat = Carbon::parse($settings?->break_end ?: '13:00')->format('H.i');
        $jamTutup = Carbon::parse($settings?->closing_time ?: '21:00')->format('H.i');
        $layanan = $transaksi->detailTransaksi
            ->map(fn ($detail) => trim((string) $detail->paketLaundry?->nama_paket))
            ->filter()
            ->unique()
            ->implode(', ') ?: 'Layanan laundry';
        $template = self::completionTemplateFor($settings?->completion_message);

        $message = strtr($template, [
            '{nama_pelanggan}' => $namaPelanggan,
            '{no_nota}' => $transaksi->no_nota,
            '{layanan}' => $layanan,
            '{status_cucian}' => 'SELESAI',
            '{status_pembayaran}' => $statusPembayaran,
            '{metode_pembayaran}' => $metodePembayaran,
            '{total_harga}' => $totalPembayaran,
            '{token_tracking}' => $token,
            '{url_tracking}' => $websiteUrl,
            '{url_web}' => $websiteUrl,
            '{jam_buka}' => $jamBuka,
            '{jam_mulai_istirahat}' => $mulaiIstirahat,
            '{jam_selesai_istirahat}' => $selesaiIstirahat,
            '{jam_tutup}' => $jamTutup,
            '{rincian_catatan}' => $catatanTransaksi !== ''
                ? "Catatan Pesanan : {$catatanTransaksi}"
                : '',
            '{catatan_pembayaran}' => $transaksi->status_pembayaran === 'lunas'
                ? ''
                : 'Bagi pelanggan yang belum melakukan pembayaran, pelunasan dapat dilakukan saat pengambilan cucian.',
        ]);
        $message = preg_replace("/\n{3,}/", "\n\n", trim($message)) ?? trim($message);

        if (! str_contains($template, '{status_pembayaran}')) {
            $message .= "\nStatus Pembayaran: {$statusPembayaran}";
        }
        if (! str_contains($template, '{total_harga}')) {
            $message .= "\nTotal Pembayaran: {$totalPembayaran}";
        }
        if ($catatanTransaksi !== '' && ! str_contains($template, '{rincian_catatan}')) {
            $message .= "\nCatatan Pesanan: {$catatanTransaksi}";
        }

        return [
            'url' => self::whatsappUrl($phone, $message),
            'message' => $message,
        ];
    }

    private static function recipientPhone(Transaksi $transaksi): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $transaksi->pelanggan?->no_telp) ?? '';
        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    public static function completionTemplate(): string
    {
        return implode("\n", [
            "_G'LAUNDRY_",
            '_Pemberitahuan Status Cucian_',
            '',
            'Halo, Kak {nama_pelanggan}!',
            '',
            'Kami ingin menginformasikan bahwa cucian Anda telah _SELESAI_ diproses dan sudah siap untuk diambil.',
            '',
            'Berikut detail transaksi Anda:',
            '',
            '_INFORMASI TRANSAKSI_',
            '',
            'Nomor Nota : {no_nota}',
            'Nama Pelanggan : {nama_pelanggan}',
            'Layanan : {layanan}',
            'Total Pembayaran : {total_harga}',
            'Status Cucian : {status_cucian}',
            'Status Pembayaran : {status_pembayaran}',
            '{rincian_catatan}',
            '',
            "Silakan datang ke lokasi G'Laundry untuk mengambil cucian Anda sesuai dengan jam operasional yang berlaku.",
            '',
            '_INFORMASI PENGAMBILAN_',
            '',
            '_Jam Operasional:_',
            'Buka : {jam_buka} WITA',
            'Istirahat : {jam_mulai_istirahat} - {jam_selesai_istirahat} WITA',
            'Tutup : {jam_tutup} WITA',
            '',
            "Informasi jam operasional terbaru juga dapat dilihat melalui website G'Laundry.",
            '',
            "_TAUTAN WEB G'LAUNDRY_",
            '{url_web}',
            '',
            '_CATATAN PENGAMBILAN_',
            '',
            'Mohon menunjukkan nomor nota atau bukti transaksi kepada admin saat pengambilan.',
            '',
            'Jika ada pertanyaan terkait pengambilan cucian, silakan membalas pesan ini untuk menghubungi admin.',
            '',
            "Terima kasih telah mempercayakan cucian Anda kepada _G'Laundry_.",
            '',
            '_Kami tunggu kedatangan Anda!_',
        ]);
    }

    public static function completionTemplateFor(?string $configuredTemplate): string
    {
        $configuredTemplate = trim((string) $configuredTemplate);
        $hasRequiredSections = str_contains($configuredTemplate, '_INFORMASI TRANSAKSI_')
            && str_contains($configuredTemplate, '_CATATAN PENGAMBILAN_')
            && str_contains($configuredTemplate, '{jam_buka}')
            && str_contains($configuredTemplate, '{url_web}')
            && str_contains($configuredTemplate, '{rincian_catatan}');

        return $hasRequiredSections ? $configuredTemplate : self::completionTemplate();
    }

    private static function whatsappUrl(string $phone, string $message): string
    {
        return 'https://web.whatsapp.com/send?phone='.$phone.'&text='.rawurlencode($message);
    }
}

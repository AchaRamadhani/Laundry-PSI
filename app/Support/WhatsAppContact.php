<?php

namespace App\Support;

use App\Models\LaundrySetting;

class WhatsAppContact
{
    private const DEFAULT_ADMIN_NUMBER = '0812 4291 0340';

    /**
     * @return array{
     *     whatsappDisplay: string,
     *     whatsappUrl: string,
     *     customerWhatsappUrl: string,
     *     customerWhatsappMessage: string
     * }
     */
    public static function forAdmin(?LaundrySetting $settings): array
    {
        $display = trim((string) (
            $settings?->whatsapp_number
            ?: self::DEFAULT_ADMIN_NUMBER
        ));

        $digits = preg_replace('/\D+/', '', $display) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if ($digits === '') {
            $display = self::DEFAULT_ADMIN_NUMBER;
            $digits = '6281242910340';
        }

        $whatsappUrl = 'https://wa.me/'.$digits;
        $customerMessage = self::customerMessage();

        return [
            'whatsappDisplay' => $display,
            'whatsappUrl' => $whatsappUrl,
            'customerWhatsappUrl' => $whatsappUrl.'?text='.rawurlencode($customerMessage),
            'customerWhatsappMessage' => $customerMessage,
        ];
    }

    public static function customerMessage(): string
    {
        return implode("\n", [
            '*Hai Min!* Mau tanya soal cucian saya, nih!'."\n",
            '*Atas nama*:   _[isi nama yang tertera di nota transaksi Anda]_',
            '*No. Nota*:   _[isi nomor nota transaksi Anda]_'."\n",
            '_[tulis pertanyaan atau pesan Anda di sini]_'."\n",
            'Ditunggu balasannya ya, Min. *Terima kasih!*',
        ]);
    }
}

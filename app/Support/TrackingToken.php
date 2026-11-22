<?php

namespace App\Support;

use App\Models\Transaksi;

/**
 * Creates verifiable tracking tokens without persisting token material.
 * The token contains a deterministic pseudorandom transaction segment and a
 * keyed hash of the customer's last four phone digits. It is never persisted.
 */
class TrackingToken
{
    private const TOKEN_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const RANDOM_LENGTH = 12;

    private const PHONE_HASH_LENGTH = 8;

    public static function forTransaction(Transaksi $transaksi): string
    {
        $noNota = trim((string) $transaksi->no_nota);
        if ($noNota === '') {
            throw new \InvalidArgumentException('Nomor nota diperlukan untuk membuat token tracking.');
        }

        $key = self::key();
        $phoneHash = self::phoneHash((string) ($transaksi->pelanggan->no_telp ?? ''), $key);
        $randomPart = self::pseudorandomPart($noNota, $key);

        return $randomPart.$phoneHash;
    }

    public static function resolve(string $token): ?Transaksi
    {
        $token = strtoupper(trim($token));
        if (! preg_match('/\A[A-HJ-NP-Z2-9]{20}\z/', $token)) {
            return null;
        }

        $found = null;
        Transaksi::query()
            ->with('pelanggan:id,no_telp')
            ->orderBy('no_nota')
            ->chunk(500, function ($transactions) use ($token, &$found): bool {
                foreach ($transactions as $transaksi) {
                    if (hash_equals(self::forTransaction($transaksi), $token)) {
                        $found = $transaksi;

                        return false;
                    }
                }

                return true;
            });

        return $found;
    }

    private static function phoneHash(string $phoneNumber, string $key): string
    {
        $phoneDigits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        $lastFourDigits = str_pad(substr($phoneDigits, -4), 4, '0', STR_PAD_LEFT);

        return self::hmacCharacters('tracking-phone:'.$lastFourDigits, self::PHONE_HASH_LENGTH, $key);
    }

    private static function pseudorandomPart(string $noNota, string $key): string
    {
        return self::hmacCharacters('tracking-transaction:'.strtoupper($noNota), self::RANDOM_LENGTH, $key);
    }

    private static function hmacCharacters(string $message, int $length, string $key): string
    {
        $digest = hash_hmac('sha256', $message, $key, true);
        $bits = '';

        foreach (str_split($digest) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $bits = substr($bits, 0, $length * 5);
        $encoded = '';

        for ($offset = 0; $offset < strlen($bits); $offset += 5) {
            $index = (int) bindec(substr($bits, $offset, 5));

            $encoded .= self::TOKEN_ALPHABET[$index];
        }

        return $encoded;
    }

    private static function key(): string
    {
        return (string) config('app.key');
    }
}

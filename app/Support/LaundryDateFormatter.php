<?php

namespace App\Support;

use App\Models\LaundrySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class LaundryDateFormatter
{
    private const DEFAULT_FORMAT = 'd/m/Y';

    private const AVAILABLE_FORMATS = [
        'd/m/Y' => 'DD/MM/YYYY',
        'd-m-Y' => 'DD-MM-YYYY',
        'd F Y' => 'DD MMMM YYYY',
        'Y-m-d' => 'YYYY-MM-DD (ISO)',
        'm/d/Y' => 'MM/DD/YYYY',
        'F d, Y' => 'MMMM D, YYYY',
    ];

    public static function availableFormats(): array
    {
        return self::AVAILABLE_FORMATS;
    }

    public static function inputPlaceholder(): string
    {
        return [
            'd/m/Y' => 'DD/MM/YYYY',
            'd-m-Y' => 'DD-MM-YYYY',
            'd F Y' => 'DD MMMM YYYY',
            'Y-m-d' => 'YYYY-MM-DD',
            'm/d/Y' => 'MM/DD/YYYY',
            'F d, Y' => 'MMMM D, YYYY',
        ][self::pattern()];
    }

    public static function pattern(): string
    {
        return Cache::rememberForever('laundry.date_format', static function (): string {
            $format = LaundrySetting::query()->value('date_format');

            return array_key_exists($format, self::AVAILABLE_FORMATS) ? $format : self::DEFAULT_FORMAT;
        });
    }

    public static function format(mixed $value, bool $includeTime = false): string
    {
        if (blank($value)) {
            return '—';
        }

        $date = Carbon::parse($value);
        $formatted = self::formatDate($date, self::pattern());

        return $formatted.($includeTime ? ', '.$date->format('H.i') : '');
    }

    public static function formatInput(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        $normalized = self::normalizeInput($value);
        if (is_string($normalized) && preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $normalized)) {
            return self::formatDate(Carbon::parse($normalized), self::pattern());
        }

        return is_scalar($value) ? (string) $value : '';
    }

    public static function normalizeInput(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '' || preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $value)) {
            return $value;
        }

        try {
            $pattern = self::pattern();
            $date = match ($pattern) {
                'd F Y' => Carbon::createFromLocaleIsoFormat('D MMMM YYYY', 'id', trim($value)),
                'F d, Y' => Carbon::createFromLocaleIsoFormat('MMMM D, YYYY', 'en', trim($value)),
                default => Carbon::createFromFormat('!'.$pattern, trim($value)),
            };

            if ($date && mb_strtolower(self::formatDate($date, $pattern)) === mb_strtolower(trim($value))) {
                return $date->toDateString();
            }
        } catch (Throwable) {
            // Leave invalid input untouched so Laravel's date validation can report it.
        }

        return $value;
    }

    public static function forgetCachedPattern(): void
    {
        Cache::forget('laundry.date_format');
    }

    private static function formatDate(Carbon $date, string $pattern): string
    {
        return match ($pattern) {
            'd F Y' => $date->locale('id')->isoFormat('DD MMMM YYYY'),
            'F d, Y' => $date->locale('en')->isoFormat('MMMM D, YYYY'),
            default => $date->format($pattern),
        };
    }
}

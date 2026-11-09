<?php

namespace App\Support;

use App\Models\LaundrySetting;

class OperationalHours
{
    /**
     * @return array{
     *     openingTime: string,
     *     breakStartTime: string,
     *     breakEndTime: string,
     *     closingTime: string,
     *     todayStatus: string,
     *     operationalTimezone: string
     * }
     */
    public static function forSettings(?LaundrySetting $settings): array
    {
        $openingTime = substr((string) ($settings?->opening_time ?: '07:00'), 0, 5);
        $breakStartTime = substr((string) ($settings?->break_start ?: '12:00'), 0, 5);
        $breakEndTime = substr((string) ($settings?->break_end ?: '13:00'), 0, 5);
        $closingTime = substr((string) ($settings?->closing_time ?: '21:00'), 0, 5);

        $operationalTimezone = 'Asia/Makassar';
        $currentTime = now($operationalTimezone)->format('H:i');

        $todayStatus = match (true) {
            $currentTime < $openingTime || $currentTime >= $closingTime => 'Tutup',
            $currentTime >= $breakStartTime && $currentTime < $breakEndTime => 'Istirahat',
            default => 'Buka',
        };

        return compact(
            'openingTime',
            'breakStartTime',
            'breakEndTime',
            'closingTime',
            'todayStatus'
        ) + compact('operationalTimezone');
    }
}

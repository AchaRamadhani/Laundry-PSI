<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'laundry_name',
    'whatsapp_number',
    'address',
    'opening_time',
    'break_start',
    'break_end',
    'closing_time',
    'completion_message',
    'date_format',
    'confirm_logout',
])]
class LaundrySetting extends Model
{
    protected function casts(): array
    {
        return [
            'confirm_logout' => 'boolean',
        ];
    }
}

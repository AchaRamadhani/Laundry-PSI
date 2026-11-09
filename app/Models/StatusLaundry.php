<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLaundry extends Model
{
    protected $table = 'status_laundry';

    protected $fillable = [
        'no_nota',
        'id_admin',
        'status',
        'catatan_status',
        'waktu_status',
    ];

    /**
     * Relasi status laundry ke transaksi.
     *
     * @return BelongsTo<Transaksi, $this>
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(
            Transaksi::class,
            'no_nota',
            'no_nota'
        );
    }

    /**
     * Relasi status laundry ke admin.
     *
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_admin'
        );
    }
}

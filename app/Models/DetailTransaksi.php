<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'no_nota',
        'paket_laundry_id',
        'berat_qty',
        'subtotal',
    ];

    /**
     * Relasi detail transaksi ke transaksi utama.
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
     * Relasi detail transaksi ke paket laundry.
     *
     * @return BelongsTo<PaketLaundry, $this>
     */
    public function paketLaundry(): BelongsTo
    {
        return $this->belongsTo(
            PaketLaundry::class,
            'paket_laundry_id'
        );
    }
}

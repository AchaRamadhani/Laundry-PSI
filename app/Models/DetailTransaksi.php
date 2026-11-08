<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'no_nota',
        'paket_laundry_id',
        'berat_qty',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(
            Transaksi::class,
            'no_nota',
            'no_nota'
        );
    }

    public function paketLaundry()
    {
        return $this->belongsTo(
            PaketLaundry::class,
            'paket_laundry_id'
        );
    }
}
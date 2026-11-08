<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'nama_pelanggan',
        'no_telp',
    ];

    public function transaksi()
    {
        return $this->hasMany(
            Transaksi::class,
            'pelanggan_id'
        );
    }
}
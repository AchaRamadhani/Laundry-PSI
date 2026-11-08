<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketLaundry extends Model
{
    protected $table = 'paket_laundry';

    protected $fillable = [
        'nama_paket',
        'kategori',
        'deskripsi',
        'harga_mulai',
        'durasi',
        'status',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'paket_laundry_id'
        );
    }
}

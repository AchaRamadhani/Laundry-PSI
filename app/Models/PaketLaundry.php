<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relasi paket laundry dengan detail transaksi.
     *
     * @return HasMany<DetailTransaksi, $this>
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'paket_laundry_id'
        );
    }
}

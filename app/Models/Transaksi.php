<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $primaryKey = 'no_nota';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_nota',
        'pelanggan_id',
        'id_admin',
        'tanggal_masuk',
        'estimasi_selesai',
        'total_harga',
        'status_cucian',
        'catatan',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaksi $transaksi): void {
            if (blank($transaksi->tracking_token)) {
                $transaksi->tracking_token = static::generateTrackingToken();
            }
        });
    }

    private static function generateTrackingToken(): string
    {
        do {
            $token = Str::upper(Str::random(12));
        } while (static::query()->where('tracking_token', $token)->exists());

        return $token;
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'no_nota',
            'no_nota'
        );
    }

    public function statusLaundry()
    {
        return $this->hasMany(
            StatusLaundry::class,
            'no_nota',
            'no_nota'
        );
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}

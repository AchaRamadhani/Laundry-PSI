<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
                $transaksi->tracking_token = self::generateTrackingToken();
            }
        });
    }

    private static function generateTrackingToken(): string
    {
        do {
            $token = Str::upper(Str::random(12));
        } while (self::query()->where('tracking_token', $token)->exists());

        return $token;
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * @return HasMany<DetailTransaksi, $this>
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'no_nota',
            'no_nota'
        );
    }

    /**
     * @return HasMany<StatusLaundry, $this>
     */
    public function statusLaundry(): HasMany
    {
        return $this->hasMany(
            StatusLaundry::class,
            'no_nota',
            'no_nota'
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}

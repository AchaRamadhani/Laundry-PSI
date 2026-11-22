<?php

namespace App\Models;

use App\Support\TrackingToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status_pembayaran',
        'metode_pembayaran',
        'completion_whatsapp_sent_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'completion_whatsapp_sent_at' => 'datetime',
        ];
    }

    public function getStatusPembayaranLabelAttribute(): string
    {
        return $this->status_pembayaran === 'lunas' ? 'Lunas (Sudah Dibayar)' : 'Belum Lunas (Bayar saat Pengambilan)';
    }

    public function getMetodePembayaranLabelAttribute(): string
    {
        return match ($this->metode_pembayaran) {
            'tunai' => 'Tunai / Cash',
            'qris' => 'QRIS',
            default => 'Bayar saat Pengambilan',
        };
    }

    public function getTrackingTokenAttribute(): string
    {
        return TrackingToken::forTransaction($this);
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

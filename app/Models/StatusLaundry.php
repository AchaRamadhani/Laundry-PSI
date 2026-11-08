<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function transaksi()
    {
        return $this->belongsTo(
            Transaksi::class,
            'no_nota',
            'no_nota'
        );
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}
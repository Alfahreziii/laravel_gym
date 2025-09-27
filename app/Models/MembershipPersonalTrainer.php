<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPersonalTrainer extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'membership_personal_trainers';

    // Kolom yang bisa diisi
    protected $fillable = [
        'kode_transaksi',
        'name',
        'id_paket_personal',
        'harga',
        'diskon',
        'total_biaya',
        'status_pembayaran',
    ];

    /**
     * Relasi ke paket personal trainer
     * Satu membership personal trainer dimiliki oleh satu paket personal trainer.
     */
    public function paketPersonalTrainer()
    {
        return $this->belongsTo(PaketPersonalTrainer::class, 'id_paket_personal');
    }
}

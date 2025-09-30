<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberTrainer extends Model
{
    use HasFactory;

    protected $table = 'member_trainers';

    protected $fillable = [
        'kode_transaksi',
        'id_anggota',
        'id_paket_personal_trainer',
        'id_trainer',
        'diskon',
        'total_biaya',
        'status_pembayaran',
    ];

    /**
     * Relasi ke Anggota
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    /**
     * Relasi ke Paket Personal Trainer
     */
    public function paketPersonalTrainer()
    {
        return $this->belongsTo(PaketPersonalTrainer::class, 'id_paket_personal_trainer');
    }

    /**
     * Relasi ke Trainer
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'id_trainer');
    }

    public function pembayaranMemberTrainers()
    {
        return $this->hasMany(PembayaranMemberTrainer::class, 'id_member_trainer');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranMemberTrainer extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_member_trainers';

    protected $fillable = [
        'id_member_trainer',
        'tgl_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
    ];

    /**
     * Relasi ke MemberTrainer
     */
    public function memberTrainer()
    {
        return $this->belongsTo(MemberTrainer::class, 'id_member_trainer');
    }
}

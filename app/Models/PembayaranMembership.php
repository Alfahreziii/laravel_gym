<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranMembership extends Model
{
    use HasFactory;

    // Karena nama tabel pakai double underscore
    protected $table = 'pembayaran__memberships';

    // Field yang bisa diisi mass assignment
    protected $fillable = [
        'id_anggota_membership',
        'tgl_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
    ];

    /**
     * Relasi ke AnggotaMembership
     */
    public function anggotaMembership()
    {
        return $this->belongsTo(AnggotaMembership::class, 'id_anggota_membership');
    }
}

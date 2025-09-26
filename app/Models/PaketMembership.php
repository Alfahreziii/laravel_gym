<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketMembership extends Model
{
    use HasFactory;

    protected $table = 'paket_memberships';

    protected $fillable = [
        'id_kategori',
        'nama_paket',
        'durasi',
        'periode',
        'harga',
        'keterangan',
    ];

    /**
     * Relasi ke KategoriPaketMembership
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriPaketMembership::class, 'id_kategori');
    }

    /**
     * Relasi ke AnggotaMembership
     */
    public function anggotaMemberships()
    {
        return $this->hasMany(AnggotaMembership::class, 'id_paket_membership');
    }
}

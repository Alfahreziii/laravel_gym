<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPaketMembership extends Model
{
    use HasFactory;

    // Nama tabel (jika ingin eksplisit, meskipun Laravel sudah otomatis menebak dari nama model)
    protected $table = 'kategori_paket_memberships';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'nama_kategori',
    ];

    public function paketMemberships()
    {
        return $this->hasMany(PaketMembership::class, 'id_kategori');
    }
}

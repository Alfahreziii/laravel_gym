<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AkunKeuangan extends TenantModel
{
    use HasFactory;

    protected $table = 'akun_keuangans';

    protected $fillable = ['kategori_id', 'nama', 'kode'];

    public function kategori()
    {
        return $this->belongsTo(KategoriAkun::class, 'kategori_id');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiKeuangan::class, 'akun_id');
    }
}

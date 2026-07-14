<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriAkun extends TenantModel
{
    use HasFactory;

    protected $table = 'kategori_akuns';

    protected $fillable = ['nama', 'kode'];

    public function akun()
    {
        return $this->hasMany(AkunKeuangan::class, 'kategori_id');
    }
}

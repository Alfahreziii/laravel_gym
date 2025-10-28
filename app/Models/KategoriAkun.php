<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriAkun extends Model
{
    use HasFactory;

    protected $table = 'kategori_akuns';

    protected $fillable = ['nama', 'kode'];

    public function akun()
    {
        return $this->hasMany(AkunKeuangan::class, 'kategori_id');
    }
}

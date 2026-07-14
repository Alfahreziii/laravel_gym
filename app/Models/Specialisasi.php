<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Specialisasi extends TenantModel
{
    use HasFactory;

    protected $table = 'specialisasis';

    protected $fillable = [
        'nama_specialisasi',
    ];

    // Relasi ke Trainer
    public function trainers()
    {
        return $this->hasMany(Trainer::class, 'id_specialisasi');
    }
}

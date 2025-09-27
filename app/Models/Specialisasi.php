<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialisasi extends Model
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

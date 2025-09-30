<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketPersonalTrainer extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'paket_personal_trainers';

    // Kolom yang bisa diisi (mass assignment)
    protected $fillable = [
        'nama_paket',
        'jumlah_sesi',
        'durasi',
        'biaya',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaketPersonalTrainer extends TenantModel
{
    use HasFactory;

    // Nama tabel
    protected $table = 'paket_personal_trainers';

    // Kolom yang bisa diisi (mass assignment)
    protected $fillable = [
        'nama_paket',
        'jumlah_sesi',
        'periode',
        'durasi',
        'biaya',
    ];

    public function memberTrainers()
    {
        return $this->hasMany(MemberTrainer::class, 'id_paket_personal_trainer');
    }

}

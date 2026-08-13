<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SesiTrainer extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'id_trainer',
        'id_riwayat_gaji_trainer',
        'type',
        'sesi',
        'current_sesi',
        'description',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function riwayatGaji()
    {
        return $this->belongsTo(RiwayatGajiTrainer::class, 'id_riwayat_gaji_trainer');
    }
}

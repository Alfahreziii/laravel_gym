<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SesiTrainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_trainer',
        'type',
        'sesi',
        'current_sesi',
        'description',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}

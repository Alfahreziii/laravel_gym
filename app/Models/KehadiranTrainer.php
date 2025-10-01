<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranTrainer extends Model
{
    protected $fillable = ['rfid', 'status'];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'rfid', 'rfid');
    }
}

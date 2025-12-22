<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistTrainer extends Model
{
    use HasFactory;

    protected $table = 'playlist_trainers';

    protected $fillable = [
        'id_trainer',
        'latihan',
    ];

    /**
     * Relasi ke Trainer
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'id_trainer');
    }
}

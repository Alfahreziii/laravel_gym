<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerSchedule extends Model
{
    use HasFactory;

    protected $table = 'trainer_schedules';

    protected $fillable = [
        'trainer_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Relasi ke Trainer
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelTrainer extends Model
{
    use HasFactory;

    protected $table = 'level_trainers';

    protected $fillable = [
        'name',
    ];

    /**
     * Relasi ke SettingParameterGajiTrainer
     */
    public function settingGaji()
    {
        return $this->hasMany(SettingParameterGajiTrainer::class, 'id_level');
    }

    /**
     * Get trainers with this level
     */
    public function trainers()
    {
        return $this->hasManyThrough(
            Trainer::class,
            SettingParameterGajiTrainer::class,
            'id_level',      // Foreign key on setting_parameter_gaji_trainers table
            'id',            // Foreign key on trainers table
            'id',            // Local key on level_trainers table
            'id_trainer'     // Local key on setting_parameter_gaji_trainers table
        );
    }
}
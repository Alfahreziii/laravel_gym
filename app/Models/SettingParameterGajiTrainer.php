<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingParameterGajiTrainer extends Model
{
    use HasFactory;

    protected $table = 'setting_parameter_gaji_trainers';

    protected $fillable = [
        'id_trainer',
        'id_level',
        'base_rate',
        'tgl_gajian',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'tgl_gajian' => 'date',
    ];

    /**
     * Relasi ke Trainer
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'id_trainer');
    }

    /**
     * Relasi ke LevelTrainer
     */
    public function level()
    {
        return $this->belongsTo(LevelTrainer::class, 'id_level');
    }

    /**
     * Get formatted base rate
     */
    public function getFormattedBaseRateAttribute()
    {
        return 'Rp ' . number_format($this->base_rate, 0, ',', '.');
    }

    /**
     * Get tanggal gajian formatted
     */
    public function getFormattedTglGajianAttribute()
    {
        return $this->tgl_gajian->format('d F Y');
    }

    /**
     * Scope untuk filter berdasarkan trainer
     */
    public function scopeByTrainer($query, $trainerId)
    {
        return $query->where('id_trainer', $trainerId);
    }

    /**
     * Scope untuk filter berdasarkan level
     */
    public function scopeByLevel($query, $levelId)
    {
        return $query->where('id_level', $levelId);
    }
}
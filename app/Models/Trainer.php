<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;

    protected $table = 'trainers';

    protected $fillable = [
        'id_specialisasi',
        'rfid',
        'photo',
        'name',
        'no_telp',
        'experience',
        'tgl_gabung',
        'status',
        'keterangan',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'alamat',
    ];

    protected $casts = [
        'tgl_gabung' => 'date',
        'tgl_lahir' => 'date',
    ];

    // Relasi ke Specialisasi
    public function specialisasi()
    {
        return $this->belongsTo(Specialisasi::class, 'id_specialisasi');
    }

    // Relasi ke Jadwal Trainer
    public function schedules()
    {
        return $this->hasMany(TrainerSchedule::class, 'trainer_id');
    }
}

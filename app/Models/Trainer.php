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
        'sesi_sudah_dijalani',
        'sesi_belum_dijalani',
    ];

    protected $casts = [
        'tgl_gabung' => 'date',
        'tgl_lahir' => 'date',
    ];

    protected $appends = ['training_status'];

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

    public function memberTrainers()
    {
        return $this->hasMany(MemberTrainer::class, 'id_trainer');
    }

    public function kehadiranTrainers()
    {
        return $this->hasMany(KehadiranTrainer::class, 'rfid', 'rfid');
    }

    public function sesiLogs()
    {
        return $this->hasMany(SesiTrainer::class, 'id_trainer');
    }

    /**
     * Get active training session (dinamis, tidak disimpan di database)
     */
    public function getActiveSessionAttribute()
    {
        return $this->memberTrainers()
            ->where('is_session_active', true)
            ->with('anggota')
            ->first();
    }

    /**
     * Get training status (dinamis)
     * Returns: 'open' (sedang melatih), 'available' (tidak sedang melatih)
     */
    public function getTrainingStatusAttribute()
    {
        return $this->active_session ? 'open' : 'available';
    }

    /**
     * Check if trainer is currently training someone
     */
    public function isTraining()
    {
        return $this->training_status === 'open';
    }
}
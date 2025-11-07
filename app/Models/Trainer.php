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

    protected $appends = ['training_status', 'name', 'status_label'];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_NONAKTIF = 'nonaktif';
    const STATUS_AKTIF = 'aktif';

    // Relasi ke User
    public function user()
    {
        return $this->hasOne(User::class, 'trainer_id', 'id');
    }

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
     * Get trainer name from user relationship
     */
    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : 'N/A';
    }

    /**
     * Get status label with badge color
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => [
                'text' => 'Menunggu Verifikasi Email',
                'class' => 'bg-warning-600 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm'
            ],
            self::STATUS_NONAKTIF => [
                'text' => 'Tidak Aktif',
                'class' => 'bg-danger-100 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm'
            ],
            self::STATUS_AKTIF => [
                'text' => 'Aktif',
                'class' => 'bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm'
            ],
        ];

        return $labels[$this->status] ?? $labels[self::STATUS_PENDING];
    }

    /**
     * Get active training session
     */
    public function getActiveSessionAttribute()
    {
        return $this->memberTrainers()
            ->where('is_session_active', true)
            ->with('anggota')
            ->first();
    }

    /**
     * Get training status
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

    /**
     * Scope untuk trainer yang sudah verifikasi email
     */
    public function scopeVerified($query)
    {
        return $query->whereHas('user', function($q) {
            $q->whereNotNull('email_verified_at');
        });
    }

    /**
     * Scope untuk trainer yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }
}
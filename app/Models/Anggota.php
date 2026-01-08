<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggotas';

    protected $fillable = [
        'id_kartu',
        'no_telp',
        'alamat',
        'gol_darah',
        'tinggi',
        'berat',
        'tempat_lahir',
        'tgl_lahir',
        'tgl_daftar',
        'jenis_kelamin',
        'riwayat_kesehatan',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_daftar' => 'date',
        'tinggi' => 'integer',
        'berat' => 'integer',
    ];

    protected $appends = ['name', 'photo_url', 'status_keanggotaan'];

    /**
     * Relasi ke User (One to One)
     */
    public function user()
    {
        return $this->hasOne(User::class, 'anggota_id', 'id');
    }

    /**
     * Get anggota name from user relationship
     */
    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : 'N/A';
    }

    /**
     * Get photo URL from user relationship
     */
    public function getPhotoUrlAttribute()
    {
        return $this->user ? $this->user->photo_url : asset('assets/images/user-grid/user-grid-img14.png');
    }

    /**
     * Relasi ke AnggotaMembership
     */
    public function anggotaMemberships()
    {
        return $this->hasMany(AnggotaMembership::class, 'id_anggota');
    }
    
    /**
     * Get status keanggotaan attribute
     */
    public function getStatusKeanggotaanAttribute()
    {
        $latestMembership = $this->anggotaMemberships()->latest('tgl_selesai')->first();

        if (!$latestMembership) {
            return false;
        }

        return $latestMembership->is_active;
    }

    /**
     * Get active membership
     */
    public function getActiveMembershipAttribute()
    {
        $today = Carbon::today();
        
        return $this->anggotaMemberships()
            ->where('tgl_mulai', '<=', $today)
            ->where('tgl_selesai', '>=', $today)
            ->latest('tgl_selesai')
            ->first();
    }

    /**
     * Relasi ke MemberTrainer
     */
    public function memberTrainers()
    {
        return $this->hasMany(MemberTrainer::class, 'id_anggota');
    }

    /**
     * Relasi ke KehadiranMember
     */
    public function kehadirans()
    {
        return $this->hasMany(KehadiranMember::class, 'rfid', 'id_kartu');
    }

    /**
     * Scope untuk anggota yang sudah verifikasi email
     */
    public function scopeVerified($query)
    {
        return $query->whereHas('user', function($q) {
            $q->whereNotNull('email_verified_at');
        });
    }

    /**
     * Scope untuk anggota yang memiliki membership aktif
     */
    public function scopeActiveMembership($query)
    {
        $today = Carbon::today();
        
        return $query->whereHas('anggotaMemberships', function($q) use ($today) {
            $q->where('tgl_mulai', '<=', $today)
              ->where('tgl_selesai', '>=', $today);
        });
    }

    /**
     * Get age from birth date
     */
    public function getAgeAttribute()
    {
        if (!$this->tgl_lahir) {
            return null;
        }
        
        return $this->tgl_lahir->age;
    }

    /**
     * Get BMI (Body Mass Index)
     */
    public function getBmiAttribute()
    {
        if (!$this->tinggi || !$this->berat) {
            return null;
        }
        
        $tinggiMeter = $this->tinggi / 100;
        return round($this->berat / ($tinggiMeter * $tinggiMeter), 2);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'trainer_id',
        'anggota_id',
        'photo',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
        ];
    }

    /**
     * Relasi ke Trainer (One to One)
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    /**
     * Relasi ke Anggota (One to One)
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    /**
     * Check if user is a trainer
     */
    public function isTrainer()
    {
        return $this->hasRole('trainer') && $this->trainer_id !== null;
    }

    /**
     * Check if user is a member (anggota)
     */
    public function isMember()
    {
        return $this->hasRole('member') && $this->anggota_id !== null;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is supervisor
     */
    public function isSupervisor()
    {
        return $this->hasRole('spv');
    }

    /**
     * Check if user is guest
     */
    public function isGuest()
    {
        return $this->hasRole('guest');
    }

    /**
     * Get trainer data if user is trainer
     */
    public function getTrainerDataAttribute()
    {
        return $this->isTrainer() ? $this->trainer : null;
    }

    /**
     * Get anggota data if user is member
     */
    public function getAnggotaDataAttribute()
    {
        return $this->isMember() ? $this->anggota : null;
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        
        // Default avatar
        return asset('assets/images/user-grid/user-grid-img14.png');
    }

    /**
     * Get initials from name
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Get role name
     */
    public function getRoleNameAttribute()
    {
        $role = $this->roles->first();
        return $role ? ucfirst($role->name) : 'No Role';
    }

    /**
     * Get formatted created date
     */
    public function getJoinedDateAttribute()
    {
        return $this->created_at->format('d M Y');
    }

    /**
     * Get last activity human readable
     */
    public function getLastActivityHumanAttribute()
    {
        return $this->last_activity ? $this->last_activity->diffForHumans() : 'Never';
    }
}
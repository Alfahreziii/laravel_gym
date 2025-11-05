<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberTrainer extends Model
{
    use HasFactory;

    protected $table = 'member_trainers';

    protected $fillable = [
        'kode_transaksi',
        'id_anggota',
        'id_paket_personal_trainer',
        'id_trainer',
        'diskon',
        'total_biaya',
        'status_pembayaran',
        'sesi',
        'is_session_active',
        'session_started_at',
    ];

    protected $casts = [
        'is_session_active' => 'boolean',
        'session_started_at' => 'datetime',
    ];

    protected $appends = ['sisa_sesi'];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    public function paketPersonalTrainer()
    {
        return $this->belongsTo(PaketPersonalTrainer::class, 'id_paket_personal_trainer');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'id_trainer');
    }

    public function pembayaranMemberTrainers()
    {
        return $this->hasMany(PembayaranMemberTrainer::class, 'id_member_trainer');
    }

    public function sesiLogs()
    {
        return $this->hasMany(SesiMemberTrainer::class, 'id_member_trainer');
    }
    
    /**
     * Get sisa sesi (field sesi sudah menyimpan sisa sesi)
     */
    public function getSisaSesiAttribute()
    {
        return $this->sesi; // ✅ Langsung return field sesi
    }

    /**
     * Get sesi yang sudah dijalani
     */
    public function getSesiSudahDijalaniAttribute()
    {
        $totalSesi = $this->paketPersonalTrainer->jumlah_sesi ?? 0;
        return $totalSesi - $this->sesi;
    }

    /**
     * Check if all sessions are completed
     */
    public function isSessionsCompleted()
    {
        return $this->sisa_sesi <= 0;
    }
}
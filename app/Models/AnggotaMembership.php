<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AnggotaMembership extends Model
{
    use HasFactory;

    protected $table = 'anggota_memberships';

    protected $fillable = [
        'kode_transaksi',
        'id_anggota',
        'nama_paket',
        'id_paket_membership',
        'tgl_mulai',
        'tgl_selesai',
        'diskon',
        'total_biaya',
        'metode_pembayaran',
        'status_pembayaran',
        'tgl_bayar',
        'total_dibayarkan',
    ];

    /**
     * Relasi ke Anggota
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    /**
     * Relasi ke PaketMembership
     */
    public function paketMembership()
    {
        return $this->belongsTo(PaketMembership::class, 'id_paket_membership');
    }

    /**
     * Relasi ke PaketMembership
     */
    public function pembayaranMemberships()
    {
        return $this->hasMany(PembayaranMembership::class, 'id_anggota_membership');
    }

    public function getIsActiveAttribute()
    {
        $today = Carbon::today();
        return $this->tgl_mulai <= $today && $this->tgl_selesai >= $today;
    }
}

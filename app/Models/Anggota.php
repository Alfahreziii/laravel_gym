<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    // Nama table jika tidak mengikuti konvensi plural default Laravel
    protected $table = 'anggotas';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'id_kartu',
        'name',
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
        'photo',
    ];

    // Casting tipe data
    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_daftar' => 'date',
        'tinggi' => 'integer',
        'berat' => 'integer',
    ];

    public function anggotaMemberships()
    {
        return $this->hasMany(AnggotaMembership::class, 'id_anggota');
    }
    
    public function getStatusKeanggotaanAttribute()
    {
        // Ambil membership terbaru
        $latestMembership = $this->anggotaMemberships()->latest('tgl_selesai')->first();

        if (!$latestMembership) {
            return false; // Tidak ada membership
        }

        return $latestMembership->is_active; // pakai accessor dari atas
    }

}

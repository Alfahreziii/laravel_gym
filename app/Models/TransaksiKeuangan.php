<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKeuangan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keuangans';

    protected $fillable = [
    'akun_id',
    'deskripsi',
    'debit',
    'kredit',
    'tanggal',
    'referensi_id',
    'referensi_tabel',
];


    public function akun()
    {
        return $this->belongsTo(AkunKeuangan::class, 'akun_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlatGym extends Model
{
    use HasFactory;

    // Nama tabel (opsional, karena Laravel bisa tebak dari nama model)
    protected $table = 'alat_gyms';

    // Field yang bisa diisi (mass assignment)
    protected $fillable = [
        'barcode',
        'nama_alat_gym',
        'jumlah',
        'harga',
        'tgl_pembelian',
        'lokasi_alat',
        'kondisi_alat',
        'vendor',
        'kontak',
        'keterangan',
    ];

    // Cast tipe data
    protected $casts = [
        'tgl_pembelian' => 'date',
        'harga' => 'decimal:2',
    ];
}

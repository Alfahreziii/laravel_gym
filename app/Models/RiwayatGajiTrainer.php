<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatGajiTrainer extends Model
{
    use HasFactory;

    protected $table = 'riwayat_gaji_trainers';

    protected $fillable = [
        'id_trainer',
        'jumlah_sesi',
        'tgl_mulai',
        'tgl_selesai',
        'tgl_bayar',
        'total_dibayarkan',
        'base_rate',
        'metode_pembayaran',
        'bonus',
    ];

    protected $casts = [
        'jumlah_sesi' => 'integer',
        'tgl_bayar' => 'date',
        'total_dibayarkan' => 'decimal:2',
        'bonus' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    // Konstanta metode pembayaran
    const METODE_CASH = 'cash';
    const METODE_TRANSFER = 'transfer';
    const METODE_E_WALLET = 'e-wallet';

    /**
     * Relasi ke Trainer
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'id_trainer');
    }

    /**
     * Get formatted total dibayarkan
     */
    public function getFormattedTotalDibayarkanAttribute()
    {
        return 'Rp ' . number_format($this->total_dibayarkan, 0, ',', '.');
    }

    /**
     * Get formatted base rate
     */
    public function getFormattedBaseRateAttribute()
    {
        return 'Rp ' . number_format($this->base_rate, 0, ',', '.');
    }

    /**
     * Get bulan gajian formatted
     */
    public function getFormattedBulanGajianAttribute()
    {
        return $this->bulan_gajian->format('F Y');
    }

    /**
     * Get tanggal bayar formatted
     */
    public function getFormattedTglBayarAttribute()
    {
        return $this->tgl_bayar->format('d F Y');
    }

    /**
     * Get metode pembayaran label
     */
    public function getMetodePembayaranLabelAttribute()
    {
        $labels = [
            self::METODE_CASH => 'Cash',
            self::METODE_TRANSFER => 'Transfer Bank',
            self::METODE_E_WALLET => 'E-Wallet',
        ];

        return $labels[$this->metode_pembayaran] ?? 'Tidak Diketahui';
    }

    /**
     * Scope untuk filter berdasarkan trainer
     */
    public function scopeByTrainer($query, $trainerId)
    {
        return $query->where('id_trainer', $trainerId);
    }

    /**
     * Scope untuk mendapatkan riwayat terbaru
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tgl_bayar', 'desc')
                     ->orderBy('created_at', 'desc');
    }
}
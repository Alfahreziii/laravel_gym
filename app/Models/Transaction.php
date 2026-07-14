<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'customer_name',
        'total_amount',
        'diskon',
        'diskon_barang',
        'harga_sebelum_diskon',
        'status',
        'dibayarkan',
        'metode_pembayaran',
        'kembalian',
    ];

    /**
     * Relasi ke TransactionItem
     * Satu transaksi memiliki banyak item.
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}

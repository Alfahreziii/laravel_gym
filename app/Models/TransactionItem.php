<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'qty',
        'price',
        'subtotal',
        'kategori',
    ];

    /**
     * Relasi ke Transaction
     * Item ini dimiliki oleh satu transaksi.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
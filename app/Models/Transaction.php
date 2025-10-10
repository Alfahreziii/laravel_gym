<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'status',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];
}

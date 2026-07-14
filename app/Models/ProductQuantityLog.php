<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductQuantityLog extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'current_quantity',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'barcode',
        'description',
        'image',
        'price',
        'discount',
        'discount_type',
        'quantity',
        'is_active',
        'kategori_product_id',
    ];

    /**
     * Relasi ke kategori (Many to One)
     * Setiap produk dimiliki oleh satu kategori
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriProduct::class, 'kategori_product_id');
    }
    
    public function quantityLogs()
    {
        return $this->hasMany(ProductQuantityLog::class);
    }

}

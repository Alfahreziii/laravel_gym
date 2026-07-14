<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriProduct extends TenantModel
{
    use HasFactory;

    protected $table = 'kategori_products';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relasi ke produk (One to Many)
     * Satu kategori punya banyak produk
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'kategori_product_id');
    }
}

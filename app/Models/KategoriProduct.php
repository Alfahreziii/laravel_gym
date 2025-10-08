<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriProduct extends Model
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

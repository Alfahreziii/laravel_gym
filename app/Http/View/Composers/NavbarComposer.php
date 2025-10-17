<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Product;

class NavbarComposer
{
    public function compose(View $view)
    {
        // Ambil produk yang stok-nya di bawah reorder level
        $lowStockProducts = Product::whereColumn('quantity', '<', 'reorder')
            ->where('is_active', true) // hanya produk aktif (opsional)
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        // Kirim ke view navbar
        $view->with('lowStockProducts', $lowStockProducts);
    }
}

<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class NavbarComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        
        // Hanya SPV dan Admin yang bisa lihat notifikasi stok
        if ($user && ($user->hasRole('spv') || $user->hasRole('admin'))) {
            $lowStockProducts = Product::whereColumn('quantity', '<', 'reorder')
                ->where('is_active', true)
                ->orderBy('quantity', 'asc')
                ->take(5)
                ->get();
        } else {
            $lowStockProducts = collect([]);
        }

        $view->with('lowStockProducts', $lowStockProducts);
    }
}
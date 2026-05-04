<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Product;
use App\Models\AnggotaMembership;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

            // Membership yang akan habis dalam 7 hari ke depan
            $expiringMemberships = AnggotaMembership::with(['anggota'])
                ->whereBetween('tgl_selesai', [
                    Carbon::today(),
                    Carbon::today()->addDays(7),
                ])
                ->where('status_pembayaran', 'Lunas')
                ->orderBy('tgl_selesai', 'asc')
                ->get();
        } else {
            $lowStockProducts = collect([]);
            $expiringMemberships = collect([]);
        }

        $view->with('lowStockProducts', $lowStockProducts);
        $view->with('expiringMemberships', $expiringMemberships);
    }
}

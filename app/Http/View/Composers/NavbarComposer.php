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

        if ($user && ($user->hasRole('spv') || $user->hasRole('admin'))) {
            $lowStockProducts = Product::whereColumn('quantity', '<', 'reorder')
                ->where('is_active', true)
                ->orderBy('quantity', 'asc')
                ->take(5)
                ->get();

            $expiringMemberships = AnggotaMembership::with(['anggota'])
                ->whereBetween('tgl_selesai', [
                    Carbon::today(),
                    Carbon::today()->addDays(7),
                ])
                ->where('status_pembayaran', 'Lunas')
                ->orderBy('tgl_selesai', 'asc')
                ->get();

            $expiredMemberships = AnggotaMembership::with(['anggota'])
                ->whereBetween('tgl_selesai', [
                    Carbon::today()->subMonths(2),
                    Carbon::today()->subMonth(),
                ])
                ->where('status_pembayaran', 'Lunas')
                ->orderBy('tgl_selesai', 'desc')
                ->get();
        } else {
            $lowStockProducts = collect([]);
            $expiringMemberships = collect([]);
            $expiredMemberships = collect([]);
        }

        $view->with('lowStockProducts', $lowStockProducts);
        $view->with('expiringMemberships', $expiringMemberships);
        $view->with('expiredMemberships', $expiredMemberships);
    }
}

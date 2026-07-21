<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\DatabasePool;
use App\Models\Master\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['package', 'databasePool', 'backups'])
                         ->orderByDesc('created_at')
                         ->get();

        $today = now()->startOfDay();

        $stats = [
            'total'      => $tenants->count(),
            'aktif'      => $tenants->where('status', 'aktif')->count(),
            'nonaktif'   => $tenants->whereIn('status', ['nonaktif', 'suspend'])->count(),
            'archived'   => $tenants->where('status', 'archived')->count(),
            'db_tersisa' => DatabasePool::where('status', 'available')->count(),
        ];

        // Tenant dengan status masih 'aktif' tapi tgl_selesai sudah lewat hari ini
        $lewatTanggal = $tenants->filter(
            fn($t) => $t->status === 'aktif' && $t->tgl_selesai?->lt($today)
        );

        return view('superadmin.dashboard', compact('tenants', 'stats', 'lewatTanggal'));
    }
}

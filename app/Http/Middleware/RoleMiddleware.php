<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Cegah akses jika email belum diverifikasi
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Bisa menerima beberapa role, pisahkan dengan '|'
        $rolesArray = explode('|', $roles);

        foreach ($rolesArray as $role) {
            if (Auth::user()->hasRole($role)) {

                // ==== LOGIKA KHUSUS UNTUK TRAINER ====
                if ($role === 'trainer') {
                    $trainer = Auth::user()->trainer;

                    // Pastikan data relasi trainer ada
                    if (!$trainer) {
                        return redirect()->route('pageError')
                            ->with('error', 'Data trainer tidak ditemukan.');
                    }

                    // Jika belum aktif → hanya boleh akses waiting approval
                    if ($trainer->status !== \App\Models\Trainer::STATUS_AKTIF) {
                        if (!$request->routeIs('trainer.waiting.approval')) {
                            return redirect()->route('trainer.waiting.approval');
                        }
                    }

                    // Jika sudah aktif DAN mencoba akses waiting approval → redirect ke dashboard
                    if (
                        $trainer->status === \App\Models\Trainer::STATUS_AKTIF &&
                        $request->routeIs('trainer.waiting.approval')
                    ) {
                        return redirect()->route('trainer.dashboard');
                    }
                }

                // Role cocok → lanjutkan request
                return $next($request);
            }
        }

        // Jika tidak punya role yang sesuai → redirect ke pageError
        return redirect()->route('pageError')
            ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
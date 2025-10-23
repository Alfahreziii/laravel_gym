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

        if (!Auth::user()->hasVerifiedEmail()) {
            return $next($request);
        }

        // Bisa menerima beberapa role, pisahkan dengan '|'
        $rolesArray = explode('|', $roles);

        foreach ($rolesArray as $role) {
            if (Auth::user()->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses.');
    }
}


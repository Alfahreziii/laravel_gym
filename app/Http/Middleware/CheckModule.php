<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        if (! tenant_module($key)) {
            abort(403, 'Fitur ini tidak tersedia di paket Anda.');
        }

        return $next($request);
    }
}

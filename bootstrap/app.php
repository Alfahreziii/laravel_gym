<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // JANGAN pakai 'web:' — super admin routes harus diregistrasi SEBELUM web.php.
        // Alasan: GET /login di auth.php (require'd dari web.php) tidak punya domain
        // constraint, sehingga akan match admin.{domain}/login duluan jika web.php
        // di-load lebih awal. Dengan 'then:', urutan kita kendalikan sendiri.
        then: function () {
            // 1. Super admin — domain-constrained, WAJIB sebelum web.php
            Route::middleware('web')
                ->domain('admin.' . env('TENANT_BASE_DOMAIN', 'sistemgate.com'))
                ->group(base_path('routes/superadmin.php'));

            // 2. Gym web routes + auth.php (require'd di dalamnya) — setelah super admin
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ResolveTenant jalan sebelum StartSession (awal web group).
        // Ini kritis: switch DB tenant harus selesai sebelum session driver baca
        // tabel 'sessions' dari koneksi yang benar.
        $middleware->prependToGroup('web', \App\Http\Middleware\ResolveTenant::class);

        // Alias tetap ada untuk route eksplisit (mis. /tenant-check sementara)
        $middleware->alias([
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'module' => \App\Http\Middleware\CheckModule::class,
        ]);

        // Redirect unauthenticated users ke halaman login yang tepat per domain.
        // Tanpa ini, auth:super_admin akan redirect ke route('login') = gym login.
        $middleware->redirectGuestsTo(function ($request) {
            $baseDomain = env('TENANT_BASE_DOMAIN', 'sistemgate.com');
            if ($request->getHost() === 'admin.' . $baseDomain) {
                return route('super_admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
<?php

namespace App\Http\Middleware;

use App\Models\Master\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Subdomain yang dilewati tanpa resolusi tenant.
     * Tambahkan di sini jika ada subdomain khusus (mis. panel super-admin).
     */
    private const WHITELIST = ['admin', 'www', 'api'];

    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        // null  = local dev / bare domain / tidak cocok pola → lanjut normal
        // whitelist = subdomain khusus non-tenant → lanjut normal
        if ($subdomain === null || in_array($subdomain, self::WHITELIST, true)) {
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->with(['databasePool', 'module'])
            ->first();

        if (! $tenant) {
            abort(404);
        }

        if ($tenant->status !== 'aktif') {
            return response()->view(
                'errors.tenant-inactive',
                ['tenant' => $tenant],
                402
            );
        }

        // ── Switch koneksi 'tenant' ke pool DB milik tenant ini ──────────
        $pool = $tenant->databasePool;

        config()->set('database.connections.tenant.host',     $pool->db_host);
        config()->set('database.connections.tenant.database', $pool->db_name);
        config()->set('database.connections.tenant.username', $pool->db_username);
        config()->set('database.connections.tenant.password', $pool->db_password);

        // (a) Buang koneksi lama, reconnect dengan config DB tenant baru
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Set session driver pakai koneksi tenant — HARUS sebelum StartSession baca config.
        // Aman karena middleware ini di-prepend ke web group (jalan sebelum StartSession).
        config(['session.connection' => 'tenant']);
        config(['database.default'  => 'tenant']);

        // Simpan tenant aktif agar bisa diakses di mana saja via app('tenant')
        app()->instance('tenant', $tenant);

        // (b) Flush Spatie permission cache — cegah role tenant lain ter-serve ke request ini.
        //     Koneksi Role/Permission ikut User model secara otomatis (morphToMany pakai
        //     $this->getConnection() dari User yang sudah $connection='tenant').
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $next($request);
    }

    private function extractSubdomain(string $host): ?string
    {
        // Local dev tanpa subdomain → skip resolusi
        if (! str_contains($host, '.') || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        $baseDomain = env('TENANT_BASE_DOMAIN', 'sistemgate.com');

        // Host harus diakhiri .{baseDomain} (mis. fithub.sistemgate.com)
        if (! str_ends_with($host, '.' . $baseDomain)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen('.' . $baseDomain));

        // Nested subdomain (mis. a.b.sistemgate.com) tidak didukung
        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return null;
        }

        return $subdomain;
    }
}

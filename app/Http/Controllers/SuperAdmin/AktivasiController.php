<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\DatabasePool;
use App\Models\Master\Package;
use App\Models\Master\Tenant;
use App\Models\Master\TenantModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AktivasiController extends Controller
{
    public function create()
    {
        $pools    = DatabasePool::where('status', 'available')
                                ->orderBy('db_name')
                                ->get();
        $packages = Package::orderBy('nama')->get();

        return view('superadmin.aktivasi', compact('pools', 'packages'));
    }

    public function store(Request $request)
    {
        // ── 1. Validasi ──────────────────────────────────────────────────
        $request->validate([
            'nama_gym'         => ['required', 'string', 'max:255'],
            'subdomain'        => [
                'required', 'string', 'max:63',
                'regex:/^[a-z0-9\-]+$/',
                function ($attr, $value, $fail) {
                    // Lindungi subdomain yang dicadangkan sistem
                    if (in_array($value, ['admin', 'www', 'api'], true)) {
                        $fail('Subdomain ini dicadangkan oleh sistem.');
                        return;
                    }
                    // Cek unik di master DB lewat model (pakai $connection='mysql_master')
                    if (Tenant::where('subdomain', $value)->exists()) {
                        $fail('Subdomain sudah digunakan oleh gym lain.');
                    }
                },
            ],
            'alamat'           => ['nullable', 'string', 'max:1000'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'email'            => ['required', 'email', 'max:255'],
            'no_hp'            => ['required', 'string', 'max:20'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'database_pool_id' => ['required', 'integer'],
            'package_id'       => ['required', 'integer'],
            'tgl_mulai'        => ['required', 'date'],
            'tgl_selesai'      => ['required', 'date', 'after:tgl_mulai'],
            'status'           => ['required', 'in:aktif,nonaktif,suspend'],
        ]);

        // ── 2. Guard pool — cegah race condition ─────────────────────────
        // Query ulang (fresh) agar status paling mutakhir dari DB master
        $pool = DatabasePool::find($request->integer('database_pool_id'));
        if (! $pool || $pool->status !== 'available') {
            return back()->withInput()->withErrors([
                'database_pool_id' => 'Database pool ini sudah digunakan atau tidak tersedia. Pilih pool lain.',
            ]);
        }

        $package = Package::find($request->integer('package_id'));
        if (! $package) {
            return back()->withInput()->withErrors([
                'package_id' => 'Paket tidak ditemukan.',
            ]);
        }

        // ── 3. Upload logo (opsional) ─────────────────────────────────────
        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('tenants/' . $request->subdomain . '/logo', 'public');
        }

        // ── 4. Switch koneksi 'tenant' ke pool DB target ──────────────────
        // Pola sama dengan ResolveTenant::handle()
        config()->set('database.connections.tenant.host',     $pool->db_host);
        config()->set('database.connections.tenant.database', $pool->db_name);
        config()->set('database.connections.tenant.username', $pool->db_username);
        config()->set('database.connections.tenant.password', $pool->db_password);

        try {
            DB::purge('tenant');
            DB::reconnect('tenant');
            // Flush Spatie cache agar lookup role tidak nyasar ke tenant lain
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            return back()->withInput()->withErrors([
                'database_pool_id' => "Tidak dapat terhubung ke database '{$pool->db_name}': " . $e->getMessage(),
            ]);
        }

        // ── 5. Insert user admin di DB TENANT ─────────────────────────────
        // User::$connection = 'tenant', jadi semua write masuk ke DB pool target.
        // Role dicari lewat Role::on('tenant') agar tidak nyasar ke koneksi default.
        $user = null;
        try {
            $adminRole = Role::on('tenant')
                ->where('name', 'admin')
                ->where('guard_name', 'web')
                ->first();

            if (! $adminRole) {
                throw new \RuntimeException(
                    "Role 'admin' tidak ditemukan di database '{$pool->db_name}'. " .
                    "Pastikan database template gym sudah di-seed sebelum dialokasikan ke pool."
                );
            }

            $user = User::create([
                'name'              => $request->nama_gym,
                'email'             => $request->email,
                'password'          => $request->password,   // di-hash otomatis oleh cast 'hashed'
                'email_verified_at' => now(),
            ]);

            // assignRole() pakai model object agar Spatie tidak re-query lewat koneksi default
            $user->assignRole($adminRole);

        } catch (\Throwable $e) {
            DB::purge('tenant');
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            return back()->withInput()->withErrors([
                'database_pool_id' => 'Gagal membuat akun admin di database tenant: ' . $e->getMessage(),
            ]);
        }

        // ── 6. Tulis MASTER dalam satu transaction ─────────────────────────
        // User sudah ada di tenant DB. Kalau master gagal, coba rollback user (best-effort).
        try {
            DB::connection('mysql_master')->transaction(function () use ($request, $pool, $package, $logoPath) {
                $tenant = Tenant::create([
                    'nama_gym'         => $request->nama_gym,
                    'subdomain'        => $request->subdomain,
                    'logo'             => $logoPath,
                    'alamat'           => $request->alamat,
                    'email'            => $request->email,
                    'no_hp'            => $request->no_hp,
                    'database_pool_id' => $pool->id,
                    'package_id'       => $package->id,
                    'status'           => $request->status,
                    'tgl_mulai'        => $request->tgl_mulai,
                    'tgl_selesai'      => $request->tgl_selesai,
                ]);

                TenantModule::create([
                    'tenant_id' => $tenant->id,
                    'trainer'   => $package->trainer,
                    'pos'       => $package->pos,
                    'keuangan'  => $package->keuangan,
                ]);

                // Tandai pool sebagai terpakai — ini yang "mengunci" pool dari aktivasi lain
                $pool->update(['status' => 'used']);
            });

        } catch (\Throwable $e) {
            // Master gagal — coba rollback user & role di tenant (best-effort)
            try {
                $user?->roles()->detach();
                $user?->delete();
            } catch (\Throwable $rollbackError) {
                Log::warning('Aktivasi gym: gagal rollback orphan user di tenant', [
                    'pool'     => $pool->db_name,
                    'email'    => $request->email,
                    'user_id'  => $user?->id,
                    'reason'   => $rollbackError->getMessage(),
                ]);
            }

            DB::purge('tenant');
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            Log::error('Aktivasi gym: master write gagal', [
                'pool'     => $pool->db_name,
                'subdomain' => $request->subdomain,
                'error'    => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                '_master' => 'Gagal menyimpan data gym ke database master: ' . $e->getMessage(),
            ]);
        }

        // ── 7. Reset koneksi tenant ─────────────────────────────────────────
        // Krusial: purge agar request berikutnya tidak mewarisi koneksi pool ini.
        // Config change sudah scoped ke request ini, tapi connection object harus ditutup.
        DB::purge('tenant');

        // ── 8. Redirect sukses ──────────────────────────────────────────────
        $baseDomain = env('TENANT_BASE_DOMAIN', 'sistemgate.com');

        return redirect()->route('super_admin.dashboard')->with(
            'success',
            "Gym \"{$request->nama_gym}\" berhasil diaktifkan! Admin dapat login di: {$request->subdomain}.{$baseDomain}"
        );
    }
}

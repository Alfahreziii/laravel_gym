<?php

namespace Tests\Concerns;

use App\Models\Master\DatabasePool;
use App\Models\Master\Package;
use App\Models\Master\Tenant;
use App\Models\Master\TenantModule;
use App\Models\User;
use Database\Seeders\AkunKeuanganSeeder;
use Database\Seeders\KategoriAkunSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Helper bersama untuk test yang butuh konteks tenant aktif (app('tenant')
 * ter-bind, module trainer/pos/keuangan aktif) + chart of account & role
 * Spatie sudah ter-seed di DB test.
 */
trait InteractsWithTenant
{
    protected Tenant $tenant;

    /**
     * Siapkan baris tenant (di DB master) + module aktif, bind app('tenant'),
     * dan seed COA + role. Panggil ini di awal test (mis. beforeEach()).
     */
    protected function setUpTenant(): Tenant
    {
        $pool = DatabasePool::create([
            'db_name'     => config('database.connections.tenant.database'),
            'db_host'     => config('database.connections.tenant.host'),
            'db_username' => config('database.connections.tenant.username'),
            'db_password' => config('database.connections.tenant.password') ?? '',
            'status'      => 'used',
        ]);

        $package = Package::create([
            'nama'     => 'test-package',
            'label'    => 'Test Package',
            'trainer'  => true,
            'pos'      => true,
            'keuangan' => true,
        ]);

        $tenant = Tenant::create([
            'nama_gym'         => 'Test Gym',
            'subdomain'        => 'testgym-' . uniqid(),
            'email'            => 'test@example.com',
            'no_hp'            => '08123456789',
            'timezone'         => 'Asia/Jakarta',
            'database_pool_id' => $pool->id,
            'package_id'       => $package->id,
            'status'           => 'aktif',
            'tgl_mulai'        => now()->toDateString(),
            'tgl_selesai'      => now()->addYear()->toDateString(),
        ]);

        TenantModule::create([
            'tenant_id' => $tenant->id,
            'trainer'   => true,
            'pos'       => true,
            'keuangan'  => true,
        ]);

        $tenant->load('module');
        app()->instance('tenant', $tenant);
        $this->tenant = $tenant;

        (new KategoriAkunSeeder)->run();
        (new AkunKeuanganSeeder)->run();
        (new RoleSeeder)->run();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $tenant;
    }

    /**
     * Buat user dengan role tertentu (Spatie, guard 'web') dan langsung
     * autentikasi sebagai user itu untuk request test berikutnya.
     */
    protected function actingAsRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        $this->actingAs($user);

        return $user;
    }
}

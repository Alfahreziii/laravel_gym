<?php

namespace Database\Seeders;

use App\Models\Master\DatabasePool;
use App\Models\Master\Package;
use App\Models\Master\Tenant;
use App\Models\Master\TenantModule;
use Illuminate\Database\Seeder;

class TenantTestSeeder extends Seeder
{
    public function run(): void
    {
        $pool = DatabasePool::firstOrCreate(
            ['db_name' => 'gym_kosongan'],
            [
                'db_host'     => '127.0.0.1',
                'db_username' => 'root',
                'db_password' => '',
                'status'      => 'used',
            ]
        );

        $package = Package::firstOrCreate(
            ['nama' => 'A'],
            [
                'label'    => 'Paket A',
                'trainer'  => true,
                'pos'      => true,
                'keuangan' => true,
            ]
        );

        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'fithub'],
            [
                'nama_gym'         => 'Fithub',
                'email'            => 'fithub@gym.test',
                'no_hp'            => '081234567890',
                'database_pool_id' => $pool->id,
                'package_id'       => $package->id,
                'status'           => 'aktif',
                'tgl_mulai'        => now()->toDateString(),
                'tgl_selesai'      => now()->addYear()->toDateString(),
            ]
        );

        TenantModule::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'trainer'  => true,
                'pos'      => true,
                'keuangan' => true,
            ]
        );

        $this->command->info("TenantTestSeeder OK — tenant.id={$tenant->id}, pool.id={$pool->id}, pkg.id={$package->id}");
    }
}

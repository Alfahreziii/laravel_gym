<?php

namespace Database\Seeders;

use App\Models\Master\SuperAdmin;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $sa = SuperAdmin::firstOrCreate(
            ['email' => 'admin@sistemgate.com'],
            [
                'name'     => 'Super Admin',
                'password' => 'SuperAdmin123!',  // model casts password => 'hashed'
            ]
        );

        $this->command->info("SuperAdminSeeder OK — id={$sa->id}, email={$sa->email}");
    }
}

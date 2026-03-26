<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin
        $user = User::updateOrCreate(
            ['email' => 'sulthan.alfahrezy@gmail.com'],
            [
                'name'              => 'Alfahrezis',
                'email'             => 'sulthan.alfahrezy@gmail.com',
                'password'          => Hash::make('12345678'), // ganti sesuai kebutuhan
                'anggota_id'        => null,
                'trainer_id'        => null,
                'photo'             => null,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        // Assign role admin (role_id = 1)
        // Cek dulu apakah sudah ada agar tidak duplikat
        $alreadyHasRole = DB::table('model_has_roles')->where([
            'role_id'    => 1,
            'model_type' => 'App\\Models\\User',
            'model_id'   => $user->id,
        ])->exists();

        if (!$alreadyHasRole) {
            DB::table('model_has_roles')->insert([
                'role_id'    => 1,
                'model_type' => 'App\\Models\\User',
                'model_id'   => $user->id,
            ]);
        }

        $this->command->info('Admin user seeded: ' . $user->email);
    }
}

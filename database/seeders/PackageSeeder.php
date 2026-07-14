<?php

namespace Database\Seeders;

use App\Models\Master\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'nama'     => 'A',
                'label'    => 'Paket A',
                'trainer'  => true,
                'pos'      => true,
                'keuangan' => true,
            ],
            [
                'nama'     => 'B',
                'label'    => 'Paket B',
                'trainer'  => true,
                'pos'      => true,
                'keuangan' => false,
            ],
            [
                'nama'     => 'C',
                'label'    => 'Paket C',
                'trainer'  => false,
                'pos'      => false,
                'keuangan' => false,
            ],
        ];

        foreach ($packages as $data) {
            Package::updateOrCreate(
                ['nama' => $data['nama']],
                [
                    'label'    => $data['label'],
                    'trainer'  => $data['trainer'],
                    'pos'      => $data['pos'],
                    'keuangan' => $data['keuangan'],
                ]
            );
        }

        $this->command->info('PackageSeeder OK — 3 paket di-upsert (A/B/C).');
    }
}

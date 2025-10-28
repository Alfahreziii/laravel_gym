<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriAkun;

class KategoriAkunSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama' => 'Aset', 'kode' => 'AST'],
            ['nama' => 'Kewajiban', 'kode' => 'KEW'],
            ['nama' => 'Modal', 'kode' => 'MOD'],
        ];

        foreach ($kategori as $item) {
            KategoriAkun::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}

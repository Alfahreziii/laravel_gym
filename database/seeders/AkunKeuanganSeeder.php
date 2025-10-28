<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AkunKeuangan;
use App\Models\KategoriAkun;

class AkunKeuanganSeeder extends Seeder
{
    public function run(): void
    {
        $aset = KategoriAkun::where('kode', 'AST')->first();
        $kewajiban = KategoriAkun::where('kode', 'KEW')->first();
        $modal = KategoriAkun::where('kode', 'MOD')->first();

        $akun = [
            // Aset
            ['kategori_id' => $aset->id, 'nama' => 'Kas', 'kode' => 'AST001'],
            ['kategori_id' => $aset->id, 'nama' => 'Piutang Usaha', 'kode' => 'AST002'],
            ['kategori_id' => $aset->id, 'nama' => 'Peralatan Gym', 'kode' => 'AST003'],
            ['kategori_id' => $aset->id, 'nama' => 'Perlengkapan', 'kode' => 'AST004'],

            // Kewajiban
            ['kategori_id' => $kewajiban->id, 'nama' => 'Hutang Usaha', 'kode' => 'KEW001'],
            ['kategori_id' => $kewajiban->id, 'nama' => 'Hutang Gaji', 'kode' => 'KEW002'],

            // Modal / Ekuitas
            ['kategori_id' => $modal->id, 'nama' => 'Modal Pemilik', 'kode' => 'MOD001'],
            ['kategori_id' => $modal->id, 'nama' => 'Laba Ditahan', 'kode' => 'MOD002'],

            // Pendapatan (tambahan penting)
            ['kategori_id' => $modal->id, 'nama' => 'Pendapatan Membership', 'kode' => 'MOD003'],
        ];

        foreach ($akun as $item) {
            AkunKeuangan::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}

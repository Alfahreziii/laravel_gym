<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AkunKeuangan;
use App\Models\KategoriAkun;

class AkunKeuanganSeeder extends Seeder
{
    public function run(): void
    {
        $aset      = KategoriAkun::where('kode', 'AST')->first();
        $kewajiban = KategoriAkun::where('kode', 'KEW')->first();
        $modal     = KategoriAkun::where('kode', 'MOD')->first();
        $beban     = KategoriAkun::where('kode', 'BEB')->first();

        $akun = [
            // Aset
            ['kategori_id' => $aset->id, 'nama' => 'Kas',                      'kode' => 'AST001'],
            ['kategori_id' => $aset->id, 'nama' => 'Piutang Usaha',            'kode' => 'AST002'],
            ['kategori_id' => $aset->id, 'nama' => 'Peralatan Gym',            'kode' => 'AST003'],

            // AST004 diubah namanya: dari "Perlengkapan" → "Persediaan Barang Dagang"
            // Karena ini untuk produk yang dijual ke customer, bukan barang habis pakai
            ['kategori_id' => $aset->id, 'nama' => 'Persediaan Barang Dagang', 'kode' => 'AST004'],

            // AST005 baru: untuk barang habis pakai operasional (sabun, tisu, dll) yang tidak dijual
            ['kategori_id' => $aset->id, 'nama' => 'Perlengkapan',             'kode' => 'AST005'],

            // Kewajiban
            ['kategori_id' => $kewajiban->id, 'nama' => 'Hutang Usaha', 'kode' => 'KEW001'],
            ['kategori_id' => $kewajiban->id, 'nama' => 'Hutang Gaji',  'kode' => 'KEW002'],

            // Modal / Ekuitas
            ['kategori_id' => $modal->id, 'nama' => 'Modal Pemilik', 'kode' => 'MOD001'],
            ['kategori_id' => $modal->id, 'nama' => 'Laba Ditahan',  'kode' => 'MOD002'],

            // Pendapatan
            ['kategori_id' => $modal->id, 'nama' => 'Pendapatan Membership',       'kode' => 'MOD003'],
            ['kategori_id' => $modal->id, 'nama' => 'Pendapatan Personal Trainer', 'kode' => 'MOD004'],
            ['kategori_id' => $modal->id, 'nama' => 'Pendapatan Penjualan Produk', 'kode' => 'MOD005'],

            // Beban
            // BEB001: hanya digunakan saat penjualan produk ke customer
            ['kategori_id' => $beban->id, 'nama' => 'Beban Harga Pokok Penjualan', 'kode' => 'BEB001'],

            // BEB002 baru: untuk pengurangan stok manual (rusak / hilang / susut) — bukan dari penjualan
            ['kategori_id' => $beban->id, 'nama' => 'Beban Kerugian Persediaan',   'kode' => 'BEB002'],
        ];

        foreach ($akun as $item) {
            AkunKeuangan::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}

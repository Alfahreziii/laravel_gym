<?php

use App\Models\KategoriProduct;
use App\Models\Product;
use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
    $this->actingAsRole('admin');
});

it('keeps ASET = KEWAJIBAN + MODAL - BEBAN balanced and hides unused accounts from the view', function () {
    // 1) Setoran modal: Debit Kas (AST001) / Kredit Modal Pemilik (MOD001) = 5.000.000
    $this->post(route('neraca.tambah-kas'), [
        'jumlah'    => 5000000,
        'deskripsi' => 'Setoran modal awal',
    ])->assertRedirect(route('neraca.index'));

    // 2) Beli alat gym: Debit Peralatan Gym (AST003) / Kredit Kas (AST001) = 1.000.000
    $this->post(route('alat_gym.store'), [
        'barcode'       => 'ALT-' . uniqid(),
        'nama_alat_gym' => 'Dumbbell Set',
        'jumlah'        => 1,
        'harga'         => 1000000,
        'tgl_pembelian' => '2026-08-01',
    ])->assertRedirect(route('alat_gym.index'));

    // 3) Penjualan POS: Debit Kas/Kredit Pendapatan Penjualan Produk = 100.000,
    //    Debit Beban HPP / Kredit Persediaan Barang Dagang = 60.000
    $kategori = KategoriProduct::create(['name' => 'Suplemen']);
    $product = Product::create([
        'name'                => 'Protein Bar',
        'barcode'             => 'BC-' . uniqid(),
        'price'               => 50000,
        'hpp'                 => 30000,
        'quantity'            => 10,
        'is_active'           => true,
        'kategori_product_id' => $kategori->id,
    ]);

    $this->postJson(route('kasirbayar'), [
        'cart' => [[
            'id'       => $product->id,
            'name'     => $product->name,
            'qty'      => 2,
            'price'    => 50000,
            'kategori' => ['name' => $kategori->name],
        ]],
        'diskon'            => 0,
        'diskon_barang'     => 0,
        'metode_pembayaran' => 'cash',
        'dibayarkan'        => 100000,
        'kembalian'         => 0,
        'customer_name'     => 'Siti',
        'transaction_id'    => null,
    ])->assertOk();

    // --- Cek neraca ---
    $response = $this->get(route('neraca.index'));
    $response->assertOk();

    $totalAset      = $response->viewData('total_aset');
    $totalKewajiban = $response->viewData('total_kewajiban');
    $totalModal     = $response->viewData('total_modal');
    $totalBeban     = $response->viewData('total_beban');

    // AST001: 5.000.000 - 1.000.000 + 100.000 = 4.100.000
    // AST003: 1.000.000
    // AST004: -60.000 (hanya kredit, tak ada jalur debit persediaan di sistem ini)
    expect((float) $totalAset)->toBe(5040000.0);
    expect((float) $totalKewajiban)->toBe(0.0);
    expect((float) $totalModal)->toBe(5100000.0); // MOD001 5.000.000 + MOD005 100.000
    expect((float) $totalBeban)->toBe(60000.0);    // BEB001

    // Rumus neraca: ASET = KEWAJIBAN + MODAL - BEBAN
    expect((float) $totalAset)->toBe(
        round((float) $totalKewajiban + (float) $totalModal - (float) $totalBeban, 2)
    );

    // --- Akun tersembunyi tidak boleh muncul di data akun per kategori ---
    $kategoriData = $response->viewData('kategori');
    $hidden = ['AST005', 'KEW001', 'KEW002', 'MOD002'];

    $semuaKodeAkun = $kategoriData->flatMap(fn ($kat) => $kat->akun->pluck('kode'));

    foreach ($hidden as $kode) {
        expect($semuaKodeAkun)->not->toContain($kode);
    }

    // AST003 & MOD001 harus TETAP muncul (bukan bagian hidden list)
    expect($semuaKodeAkun)->toContain('AST003');
    expect($semuaKodeAkun)->toContain('MOD001');
});

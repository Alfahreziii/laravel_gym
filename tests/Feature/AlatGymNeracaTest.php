<?php

use App\Models\AkunKeuangan;
use App\Models\AlatGym;
use App\Models\TransaksiKeuangan;
use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
    $this->actingAsRole('admin');
});

it('journals alat gym purchase, update, and delete against AST003/AST001 with no duplicate rows', function () {
    $akunPeralatan = AkunKeuangan::where('kode', 'AST003')->first();
    $akunKas       = AkunKeuangan::where('kode', 'AST001')->first();

    // --- CREATE: harga 1.000.000 x jumlah 2 = 2.000.000 ---
    $createResponse = $this->post(route('alat_gym.store'), [
        'barcode'       => 'ALT-' . uniqid(),
        'nama_alat_gym' => 'Treadmill',
        'jumlah'        => 2,
        'harga'         => 1000000,
        'tgl_pembelian' => '2026-08-01',
        'lokasi_alat'   => 'Lantai 1',
        'kondisi_alat'  => 'Baik',
        'vendor'        => 'PT Alat Gym',
        'kontak'        => '08123456789',
        'keterangan'    => 'Unit baru',
    ]);

    $createResponse->assertRedirect(route('alat_gym.index'));

    $alat = AlatGym::where('nama_alat_gym', 'Treadmill')->first();
    expect($alat)->not->toBeNull();

    $jurnalAwal = TransaksiKeuangan::where('referensi_tabel', 'alat_gyms')
        ->where('referensi_id', $alat->id)
        ->get();

    expect($jurnalAwal)->toHaveCount(2);

    $debitAwal = $jurnalAwal->firstWhere('akun_id', $akunPeralatan->id);
    expect((float) $debitAwal->debit)->toBe(2000000.0);
    expect((float) $debitAwal->kredit)->toBe(0.0);

    $kreditAwal = $jurnalAwal->firstWhere('akun_id', $akunKas->id);
    expect((float) $kreditAwal->kredit)->toBe(2000000.0);
    expect((float) $kreditAwal->debit)->toBe(0.0);

    // --- UPDATE: harga naik jadi 1.500.000 x jumlah 2 = 3.000.000 ---
    $updateResponse = $this->put(route('alat_gym.update', $alat->id), [
        'barcode'       => $alat->barcode,
        'nama_alat_gym' => 'Treadmill',
        'jumlah'        => 2,
        'harga'         => 1500000,
        'tgl_pembelian' => '2026-08-01',
        'lokasi_alat'   => 'Lantai 1',
        'kondisi_alat'  => 'Baik',
        'vendor'        => 'PT Alat Gym',
        'kontak'        => '08123456789',
        'keterangan'    => 'Unit baru',
    ]);

    $updateResponse->assertRedirect(route('alat_gym.index'));

    $jurnalUpdate = TransaksiKeuangan::where('referensi_tabel', 'alat_gyms')
        ->where('referensi_id', $alat->id)
        ->get();

    // Tetap 2 baris (pola hapus-buat-ulang) — tidak dobel jadi 4
    expect($jurnalUpdate)->toHaveCount(2);

    $debitUpdate = $jurnalUpdate->firstWhere('akun_id', $akunPeralatan->id);
    expect((float) $debitUpdate->debit)->toBe(3000000.0);

    $kreditUpdate = $jurnalUpdate->firstWhere('akun_id', $akunKas->id);
    expect((float) $kreditUpdate->kredit)->toBe(3000000.0);

    // ID baris jurnal lama sudah tidak ada (benar-benar dibuat ulang, bukan diedit di tempat)
    expect($jurnalUpdate->pluck('id')->intersect($jurnalAwal->pluck('id')))->toBeEmpty();

    // --- DESTROY: alat + jurnal terkait ikut hilang ---
    $destroyResponse = $this->delete(route('alat_gym.destroy', $alat->id));
    $destroyResponse->assertRedirect(route('alat_gym.index'));

    expect(AlatGym::find($alat->id))->toBeNull();

    $jurnalSetelahHapus = TransaksiKeuangan::where('referensi_tabel', 'alat_gyms')
        ->where('referensi_id', $alat->id)
        ->count();

    expect($jurnalSetelahHapus)->toBe(0);
});

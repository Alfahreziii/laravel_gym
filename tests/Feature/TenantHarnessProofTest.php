<?php

use App\Models\AkunKeuangan;
use App\Models\KategoriAkun;
use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
});

it('boots tenant context and can read/write via the tenant connection', function () {
    expect(app()->bound('tenant'))->toBeTrue();
    expect(app('tenant')->subdomain)->toStartWith('testgym-');
    expect(tenant_module('pos'))->toBeTrue();
    expect(tenant_module('trainer'))->toBeTrue();
    expect(tenant_module('keuangan'))->toBeTrue();

    $kategori = KategoriAkun::where('kode', 'AST')->first();
    expect($kategori)->not->toBeNull();
    expect($kategori->getConnectionName())->toBe('tenant');

    $akun = AkunKeuangan::create([
        'kategori_id' => $kategori->id,
        'nama'        => 'Akun Bukti Harness',
        'kode'        => 'ZZZ999',
    ]);

    $fresh = AkunKeuangan::where('kode', 'ZZZ999')->first();
    expect($fresh)->not->toBeNull();
    expect($fresh->id)->toBe($akun->id);
    expect($fresh->getConnectionName())->toBe('tenant');
});

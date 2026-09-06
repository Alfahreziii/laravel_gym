<?php

use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
});

/*
|--------------------------------------------------------------------------
| Ground truth dari routes/web.php (bukan tebakan) per route:
|--------------------------------------------------------------------------
| - gym_profile.index            -> RoleMiddleware:admin           (admin only)
| - gaji_trainer.create          -> RoleMiddleware:admin           (admin only;
|                                    gaji_trainer.index/.datatable justru admin|spv)
| - neraca.index                 -> RoleMiddleware:admin           (admin only)
| - riwayat_gaji_trainer.index   -> RoleMiddleware:admin           (admin only)
| - keuangan.transaksi.index     -> RoleMiddleware:admin|spv       (BUKAN admin-only —
|                                    beda dari asumsi awal, jadi spv HARUS bisa akses ini)
*/

$adminOnlyRoutes = [
    'gym_profile.index',
    'gaji_trainer.create',
    'neraca.index',
    'riwayat_gaji_trainer.index',
];

foreach ($adminOnlyRoutes as $routeName) {
    it("blocks role spv from admin-only route [{$routeName}]", function () use ($routeName) {
        $this->actingAsRole('spv');

        $response = $this->get(route($routeName));

        $response->assertRedirect(route('pageError'));
    });

    it("allows role admin on admin-only route [{$routeName}]", function () use ($routeName) {
        $this->actingAsRole('admin');

        $response = $this->get(route($routeName));

        $response->assertOk();
    });
}

it('allows role spv on keuangan.transaksi.index (admin|spv, bukan admin-only)', function () {
    $this->actingAsRole('spv');

    $response = $this->get(route('keuangan.transaksi.index'));

    $response->assertOk();
});

<?php

use App\Models\AkunKeuangan;
use App\Models\LevelTrainer;
use App\Models\RiwayatGajiTrainer;
use App\Models\SesiTrainer;
use App\Models\Specialisasi;
use App\Models\SettingParameterGajiTrainer;
use App\Models\Trainer;
use App\Models\TransaksiKeuangan;
use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
    $this->actingAsRole('admin');

    $specialisasi = Specialisasi::create(['nama_specialisasi' => 'Umum']);
    $level = LevelTrainer::create(['name' => 'Level 1']);

    $this->trainer = Trainer::forceCreate([
        'id_specialisasi' => $specialisasi->id,
        'rfid'             => 'RFID-' . uniqid(),
        'no_telp'          => '08123456789',
        'experience'       => '2 tahun',
        'tgl_gabung'       => '2025-01-01',
        'status'           => Trainer::STATUS_AKTIF,
        'keterangan'       => '-',
        'tempat_lahir'     => 'Jakarta',
        'tgl_lahir'        => '1995-01-01',
        'jenis_kelamin'    => 'Laki-laki',
        'alamat'           => 'Jl. Test No. 1',
        'sesi_sudah_dijalani' => 0,
        'sesi_belum_dijalani' => 0,
    ]);

    SettingParameterGajiTrainer::create([
        'id_trainer' => $this->trainer->id,
        'id_level'   => $level->id,
        'base_rate'  => 50000,
        'tgl_gajian' => now()->startOfMonth(),
    ]);
});

function buatSesiSelesai(Trainer $trainer, string $tanggal): SesiTrainer
{
    $sesi = SesiTrainer::create([
        'id_trainer'   => $trainer->id,
        'type'         => 'out',
        'sesi'         => 1,
        'current_sesi' => 1,
        'description'  => 'Sesi training selesai',
    ]);

    $sesi->forceFill(['created_at' => $tanggal])->save();

    return $sesi->fresh();
}

it('pays trainer based on sessions actually conducted and journals Debit Beban Gaji / Kredit Kas', function () {
    $sesi1 = buatSesiSelesai($this->trainer, '2026-08-05 10:00:00');
    $sesi2 = buatSesiSelesai($this->trainer, '2026-08-10 10:00:00');
    $sesi3 = buatSesiSelesai($this->trainer, '2026-08-15 10:00:00');

    $response = $this->postJson(route('riwayat_gaji_trainer.store'), [
        'id_trainer'         => $this->trainer->id,
        'tgl_mulai'          => '2026-08-01',
        'tgl_selesai'        => '2026-08-31',
        'tgl_bayar'          => '2026-08-16',
        'metode_pembayaran'  => 'cash',
        'bonus'              => 0,
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $riwayat = RiwayatGajiTrainer::where('id_trainer', $this->trainer->id)->first();
    expect($riwayat)->not->toBeNull();
    expect((int) $riwayat->jumlah_sesi)->toBe(3);
    expect((float) $riwayat->total_dibayarkan)->toBe(150000.0);
    expect((float) $riwayat->base_rate)->toBe(50000.0);

    // Ketiga sesi ditandai sudah dibayar
    foreach ([$sesi1, $sesi2, $sesi3] as $sesi) {
        expect($sesi->fresh()->id_riwayat_gaji_trainer)->toBe($riwayat->id);
    }

    // Jurnal: Debit BEB003 (Beban Gaji Trainer) / Kredit AST001 (Kas) = 150000
    $akunBebanGaji = AkunKeuangan::where('kode', 'BEB003')->first();
    $akunKas       = AkunKeuangan::where('kode', 'AST001')->first();

    $jurnal = TransaksiKeuangan::where('referensi_tabel', 'riwayat_gaji_trainers')
        ->where('referensi_id', $riwayat->id)
        ->get();

    expect($jurnal)->toHaveCount(2);

    $debitBeban = $jurnal->firstWhere('akun_id', $akunBebanGaji->id);
    expect((float) $debitBeban->debit)->toBe(150000.0);
    expect((float) $debitBeban->kredit)->toBe(0.0);

    $kreditKas = $jurnal->firstWhere('akun_id', $akunKas->id);
    expect((float) $kreditKas->kredit)->toBe(150000.0);
    expect((float) $kreditKas->debit)->toBe(0.0);
});

it('does not pay already-paid sessions again, but still pays newly conducted sessions in the same period', function () {
    $sesi1 = buatSesiSelesai($this->trainer, '2026-08-05 10:00:00');
    $sesi2 = buatSesiSelesai($this->trainer, '2026-08-10 10:00:00');

    $periode = [
        'id_trainer'         => $this->trainer->id,
        'tgl_mulai'          => '2026-08-01',
        'tgl_selesai'        => '2026-08-31',
        'tgl_bayar'          => '2026-08-16',
        'metode_pembayaran'  => 'cash',
        'bonus'              => 0,
    ];

    $first = $this->postJson(route('riwayat_gaji_trainer.store'), $periode);
    $first->assertOk();
    $first->assertJson(['success' => true]);

    expect(RiwayatGajiTrainer::where('id_trainer', $this->trainer->id)->count())->toBe(1);

    // Anti double-pay: ulang periode SAMA PERSIS tanpa sesi baru -> ditolak
    $second = $this->postJson(route('riwayat_gaji_trainer.store'), $periode);
    $second->assertStatus(400);
    $second->assertJson(['success' => false]);

    expect(RiwayatGajiTrainer::where('id_trainer', $this->trainer->id)->count())->toBe(1);

    // Sesi baru muncul belakangan di periode yang sama -> tetap bisa dibayar,
    // dan HANYA sesi baru itu yang terhitung (bukan sesi1/sesi2 yang sudah dibayar)
    $sesi3 = buatSesiSelesai($this->trainer, '2026-08-20 10:00:00');

    $third = $this->postJson(route('riwayat_gaji_trainer.store'), $periode);
    $third->assertOk();
    $third->assertJson(['success' => true]);

    expect(RiwayatGajiTrainer::where('id_trainer', $this->trainer->id)->count())->toBe(2);

    $riwayatKedua = RiwayatGajiTrainer::where('id_trainer', $this->trainer->id)->latest('id')->first();
    expect((int) $riwayatKedua->jumlah_sesi)->toBe(1);
    expect((float) $riwayatKedua->total_dibayarkan)->toBe(50000.0);

    expect($sesi1->fresh()->id_riwayat_gaji_trainer)->not->toBe($riwayatKedua->id);
    expect($sesi2->fresh()->id_riwayat_gaji_trainer)->not->toBe($riwayatKedua->id);
    expect($sesi3->fresh()->id_riwayat_gaji_trainer)->toBe($riwayatKedua->id);
});

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Koneksi yang dibungkus transaksi per-test dan di-rollback setelahnya.
     * 'tenant', 'mysql', dan 'mysql_master' semuanya menunjuk ke satu DB test
     * yang sama (lihat configureTenantTestDatabase()), tapi tetap PDO terpisah
     * sehingga masing-masing perlu transaksi sendiri.
     */
    private const TRANSACT_CONNECTIONS = ['tenant', 'mysql', 'mysql_master'];

    /**
     * migrate:fresh cukup dijalankan sekali untuk seluruh proses test.
     */
    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTenantTestDatabase();
        $this->migrateOnce();
        $this->beginTestTransactions();
    }

    protected function tearDown(): void
    {
        $this->rollbackTestTransactions();

        parent::tearDown();
    }

    /**
     * Isi koneksi 'tenant' (di config/database.php sengaja '' / diisi runtime
     * oleh ResolveTenant) dengan nama DB test yang sama dengan koneksi
     * 'mysql', dan set database.default ke 'tenant' supaya model tanpa
     * $connection eksplisit (mis. Spatie Role/Permission) ikut resolve ke DB
     * test yang sama — meniru apa yang ResolveTenant lakukan di request
     * tenant sungguhan (yang otomatis no-op di host localhost saat test).
     */
    private function configureTenantTestDatabase(): void
    {
        $testDb = config('database.connections.mysql.database');

        config(['database.connections.tenant.database' => $testDb]);
        config(['database.default' => 'tenant']);

        foreach (self::TRANSACT_CONNECTIONS as $name) {
            DB::purge($name);
        }
    }

    /**
     * Migrate fresh SEKALI per proses test: gabungkan migration tenant (root)
     * + master dalam satu panggilan, drop semua tabel via koneksi 'tenant'
     * (DB fisik yang sama dengan mysql/mysql_master, jadi ikut membersihkan
     * tabel master juga).
     */
    private function migrateOnce(): void
    {
        if (self::$migrated) {
            return;
        }

        Artisan::call('migrate:fresh', [
            '--database' => 'tenant',
            '--path'     => ['database/migrations', 'database/migrations/master'],
            '--realpath' => false,
            '--force'    => true,
        ]);

        self::$migrated = true;
    }

    private function beginTestTransactions(): void
    {
        foreach (self::TRANSACT_CONNECTIONS as $name) {
            DB::connection($name)->beginTransaction();
        }
    }

    private function rollbackTestTransactions(): void
    {
        foreach (self::TRANSACT_CONNECTIONS as $name) {
            $connection = DB::connection($name);

            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }

            $connection->disconnect();
        }
    }
}

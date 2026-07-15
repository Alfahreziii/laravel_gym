<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix desain Fase 5 — tenant_backups:
 *
 * TEMUAN: FK constraint tidak pernah terbentuk di DB (hanya index dengan nama
 * konvensi FK). Kolom tenant_id masih NOT NULL. Tidak ada snapshot nama/subdomain.
 *
 * Yang dilakukan migration ini:
 * 1. tenant_id → nullable (agar bisa null saat tenant dihapus)
 * 2. Drop index lama jika ada, lalu tambah FK constraint nullOnDelete yang benar
 * 3. Tambah kolom snapshot nama_gym + subdomain
 *
 * Setiap langkah cek keberadaan dulu → aman dijalankan di kondisi DB apapun.
 */
return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        $db = DB::connection('mysql_master');

        // ── 1. Buat tenant_id nullable ────────────────────────────────────
        // Harus sebelum FK nullOnDelete bisa berfungsi.
        Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
        });

        // ── 2. Drop index lama (bukan FK constraint) jika masih ada ──────
        $indexExists = $db->select(
            "SHOW INDEX FROM tenant_backups WHERE Key_name = 'tenant_backups_tenant_id_foreign'"
        );
        if (! empty($indexExists)) {
            $db->statement('ALTER TABLE tenant_backups DROP INDEX `tenant_backups_tenant_id_foreign`');
        }

        // ── 3. Tambah FK constraint nullOnDelete jika belum ada ───────────
        $fkExists = $db->select("
            SELECT 1
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA        = DATABASE()
              AND TABLE_NAME          = 'tenant_backups'
              AND CONSTRAINT_NAME     = 'tenant_backups_tenant_id_foreign'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        if (empty($fkExists)) {
            Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
                $table->foreign('tenant_id')
                      ->references('id')->on('tenants')
                      ->nullOnDelete();
            });
        }

        // ── 4. Tambah kolom snapshot (idempotent) ─────────────────────────
        Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
            if (! Schema::connection('mysql_master')->hasColumn('tenant_backups', 'nama_gym')) {
                $table->string('nama_gym')->nullable()->after('tenant_id');
            }
            if (! Schema::connection('mysql_master')->hasColumn('tenant_backups', 'subdomain')) {
                $table->string('subdomain')->nullable()->after('nama_gym');
            }
        });
    }

    public function down(): void
    {
        $db = DB::connection('mysql_master');

        // ── Drop FK constraint jika ada ───────────────────────────────────
        $fkExists = $db->select("
            SELECT 1
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA        = DATABASE()
              AND TABLE_NAME          = 'tenant_backups'
              AND CONSTRAINT_NAME     = 'tenant_backups_tenant_id_foreign'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        if (! empty($fkExists)) {
            Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        }

        // ── Drop kolom snapshot ───────────────────────────────────────────
        Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
            $toDrop = array_filter(['nama_gym', 'subdomain'], function (string $col) {
                return Schema::connection('mysql_master')->hasColumn('tenant_backups', $col);
            });
            if ($toDrop) {
                $table->dropColumn(array_values($toDrop));
            }
        });

        // ── Kembalikan tenant_id NOT NULL + recreate index lama ───────────
        Schema::connection('mysql_master')->table('tenant_backups', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->index('tenant_id', 'tenant_backups_tenant_id_foreign');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Dua perubahan untuk mendukung status 'archived' pada tenant:
// (a) database_pool_id: NOT NULL → nullable  (agar bisa di-NULL-kan saat archive)
// (b) status enum: tambah nilai 'archived'
return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        // (a) Drop FK sementara agar bisa ubah nullability kolom
        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->dropForeign(['database_pool_id']);
        });

        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('database_pool_id')->nullable()->change();
        });

        // Re-add FK dengan RESTRICT ON DELETE (sama seperti semula)
        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->foreign('database_pool_id')
                  ->references('id')
                  ->on('database_pool')
                  ->restrictOnDelete();
        });

        // (b) Tambah nilai 'archived' ke enum status
        DB::connection('mysql_master')->statement(
            "ALTER TABLE tenants MODIFY COLUMN `status` ENUM('aktif','nonaktif','suspend','archived') NOT NULL DEFAULT 'aktif'"
        );
    }

    public function down(): void
    {
        // Kembalikan enum (pastikan tidak ada row ber-status 'archived' sebelum rollback)
        DB::connection('mysql_master')->statement(
            "ALTER TABLE tenants MODIFY COLUMN `status` ENUM('aktif','nonaktif','suspend') NOT NULL DEFAULT 'aktif'"
        );

        // Kembalikan NOT NULL (pastikan tidak ada NULL di kolom sebelum rollback)
        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->dropForeign(['database_pool_id']);
        });

        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('database_pool_id')->nullable(false)->change();
        });

        Schema::connection('mysql_master')->table('tenants', function (Blueprint $table) {
            $table->foreign('database_pool_id')
                  ->references('id')
                  ->on('database_pool')
                  ->restrictOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
        // Karena relasi trainer_id sudah ada di users
        if (Schema::hasColumn('anggotas', 'name')) {
            Schema::table('anggotas', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
        });

        // 2. Copy data photo dari anggotas ke users
        DB::statement('
            UPDATE users u
            INNER JOIN anggotas a ON u.anggota_id = a.id
            SET u.photo = a.photo
            WHERE a.photo IS NOT NULL
        ');

        // 3. Hapus kolom photo dari table anggotas
        Schema::table('anggotas', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
        if (!Schema::hasColumn('anggotas', 'name')) {
                $table->string('name', 255)->after('photo');
            }
        });
        // 1. Tambahkan kembali kolom photo ke table anggotas
        Schema::table('anggotas', function (Blueprint $table) {
            $table->string('photo', 100)->after('riwayat_kesehatan');
        });

        // 2. Copy data photo dari users ke anggotas
        DB::statement('
            UPDATE anggotas t
            INNER JOIN users u ON t.id = u.anggota_id
            SET t.photo = u.photo
            WHERE u.photo IS NOT NULL
        ');
    }
};

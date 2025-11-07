<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom photo ke table users
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo', 100)->nullable()->after('trainer_id');
        });

        // 2. Copy data photo dari trainers ke users
        DB::statement('
            UPDATE users u
            INNER JOIN trainers t ON u.trainer_id = t.id
            SET u.photo = t.photo
            WHERE t.photo IS NOT NULL
        ');

        // 3. Hapus kolom photo dari table trainers
        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Tambahkan kembali kolom photo ke table trainers
        Schema::table('trainers', function (Blueprint $table) {
            $table->string('photo', 100)->after('rfid');
        });

        // 2. Copy data photo dari users ke trainers
        DB::statement('
            UPDATE trainers t
            INNER JOIN users u ON t.id = u.trainer_id
            SET t.photo = u.photo
            WHERE u.photo IS NOT NULL
        ');

        // 3. Hapus kolom photo dari table users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
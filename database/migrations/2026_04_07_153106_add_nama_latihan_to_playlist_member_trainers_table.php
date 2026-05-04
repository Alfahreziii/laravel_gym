<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom latihan dulu (nullable sementara)
        Schema::table('playlist_member_trainers', function (Blueprint $table) {
            $table->string('latihan')->nullable()->after('sesi_ke');
        });

        // 2. Salin data latihan dari playlist_trainers
        DB::statement('
            UPDATE playlist_member_trainers pmt
            JOIN playlist_trainers pt ON pmt.id_playlist_trainer = pt.id
            SET pmt.latihan = pt.latihan
        ');

        // 3. Hapus FK dan kolom id_playlist_trainer
        Schema::table('playlist_member_trainers', function (Blueprint $table) {
            $table->dropForeign(['id_playlist_trainer']);
            $table->dropColumn('id_playlist_trainer');
        });

        // 4. Set NOT NULL setelah data terisi
        Schema::table('playlist_member_trainers', function (Blueprint $table) {
            $table->string('latihan')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('playlist_member_trainers', function (Blueprint $table) {
            $table->unsignedBigInteger('id_playlist_trainer')->nullable()->after('id_member_trainer');
        });

        Schema::table('playlist_member_trainers', function (Blueprint $table) {
            $table->foreign('id_playlist_trainer')
                ->references('id')
                ->on('playlist_trainers')
                ->onDelete('cascade');

            $table->dropColumn('latihan');
        });
    }
};

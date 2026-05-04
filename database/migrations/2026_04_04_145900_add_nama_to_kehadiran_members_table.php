<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadiran_members', function (Blueprint $table) {
            // Hapus foreign key relasi ke anggotas
            $table->dropForeign(['rfid']);

            // Tambah kolom nama
            $table->string('nama')->nullable()->after('rfid');
        });
    }

    public function down(): void
    {
        Schema::table('kehadiran_members', function (Blueprint $table) {
            // Hapus kolom nama
            $table->dropColumn('nama');

            // Kembalikan foreign key
            $table->foreign('rfid')
                ->references('id_kartu')->on('anggotas')
                ->onDelete('cascade');
        });
    }
};

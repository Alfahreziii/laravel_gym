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
        Schema::table('sesi_trainers', function (Blueprint $table) {
            $table->foreignId('id_riwayat_gaji_trainer')
                ->nullable()
                ->after('id_trainer')
                ->constrained('riwayat_gaji_trainers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_trainers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_riwayat_gaji_trainer');
        });
    }
};

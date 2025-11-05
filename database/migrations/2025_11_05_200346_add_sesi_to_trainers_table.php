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
        Schema::table('trainers', function (Blueprint $table) {
            $table->integer('sesi_sudah_dijalani');
            $table->integer('sesi_belum_dijalani');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn('sesi_sudah_dijalani');
            $table->dropColumn('sesi_belum_dijalani');
        });
    }
};

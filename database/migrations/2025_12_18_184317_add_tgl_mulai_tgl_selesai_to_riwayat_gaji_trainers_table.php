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
        Schema::table('riwayat_gaji_trainers', function (Blueprint $table) {
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->decimal('bonus', 15, 2);
            $table->dropColumn('bulan_gajian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_gaji_trainers', function (Blueprint $table) {
            $table->dropColumn(['tgl_mulai', 'tgl_selesai', 'bonus']);
            $table->date('bulan_gajian');
        });
    }
};

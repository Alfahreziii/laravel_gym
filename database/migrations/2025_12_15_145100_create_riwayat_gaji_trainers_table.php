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
        Schema::create('riwayat_gaji_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_trainer')->constrained('trainers')->onDelete('cascade');
            $table->integer('jumlah_sesi');
            $table->date('bulan_gajian');
            $table->date('tgl_bayar');
            $table->decimal('total_dibayarkan', 15, 2);
            $table->decimal('base_rate', 15, 2);
            $table->string('metode_pembayaran', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_gaji_trainers');
    }
};

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
        Schema::create('alat_gyms', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 50)->unique();
            $table->string('nama_alat_gym', 100);
            $table->integer('jumlah')->default(0);
            $table->decimal('harga', 15, 2)->default(0);
            $table->date('tgl_pembelian')->nullable();
            $table->string('lokasi_alat', 100)->nullable();
            $table->string('kondisi_alat', 50)->nullable(); // misal: baik, rusak, perbaikan
            $table->string('vendor', 100)->nullable();
            $table->string('kontak', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat_gyms');
    }
};

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
        Schema::create('paket_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')->constrained('kategori_paket_memberships')->onDelete('cascade');
            $table->string('nama_paket', 50);
            $table->smallInteger('durasi')->unsigned();
            $table->string('periode', 20);
            $table->integer('harga')->unsigned();
            $table->string('keterangan', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_memberships');
    }
};

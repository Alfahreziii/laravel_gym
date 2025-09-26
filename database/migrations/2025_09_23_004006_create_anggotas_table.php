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
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();
            $table->string('id_kartu', 30)->unique();
            $table->string('name');
            $table->string('no_telp', 50);
            $table->text('alamat');
            $table->string('gol_darah', 2);
            $table->smallInteger('tinggi')->unsigned();
            $table->smallInteger('berat')->unsigned();
            $table->text('tempat_lahir');
            $table->date('tgl_lahir');
            $table->date('tgl_daftar');
            $table->string('jenis_kelamin', 20);
            $table->text('riwayat_kesehatan')->nullable();
            $table->string('photo', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};

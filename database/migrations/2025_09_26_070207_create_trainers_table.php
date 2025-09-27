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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_specialisasi')->constrained('specialisasis')->onDelete('cascade');
            $table->string('rfid', 30)->unique();
            $table->string('photo', 100);
            $table->string('name');
            $table->string('no_telp', 50);
            $table->string('experience', 100);
            $table->date('tgl_gabung');
            $table->string('status', 20);
            $table->string('keterangan', 100);
            $table->text('tempat_lahir');
            $table->date('tgl_lahir');
            $table->string('jenis_kelamin', 20);
            $table->text('alamat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};

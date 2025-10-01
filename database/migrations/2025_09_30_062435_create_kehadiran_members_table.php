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
        Schema::create('kehadiran_members', function (Blueprint $table) {
            $table->id();
            $table->string('rfid', 30); // isi dengan id_kartu dari anggota
            $table->string('status', 20); // hadir, izin, dsb
            $table->timestamps();

            // optional: kalau mau pastikan rfid yg masuk ada di anggotas
            $table->foreign('rfid')
                  ->references('id_kartu')->on('anggotas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran_members');
    }
};

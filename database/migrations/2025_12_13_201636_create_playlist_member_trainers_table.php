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
        Schema::create('playlist_member_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_playlist_trainer')->constrained('playlist_trainers')->onDelete('cascade');
            $table->foreignId('id_member_trainer')->constrained('member_trainers')->onDelete('cascade');
            $table->string('keterangan', 100);
            $table->integer('sesi_ke');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_member_trainers');
    }
};

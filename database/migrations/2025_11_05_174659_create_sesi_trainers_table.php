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
        Schema::create('sesi_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_trainer')->constrained('trainers')->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->integer('sesi');
            $table->integer('current_sesi');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_trainers');
    }
};

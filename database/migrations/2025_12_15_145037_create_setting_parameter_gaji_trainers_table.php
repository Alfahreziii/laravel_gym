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
        Schema::create('setting_parameter_gaji_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_trainer')->constrained('trainers')->onDelete('cascade');
            $table->foreignId('id_level')->constrained('level_trainers')->onDelete('cascade');
            $table->decimal('base_rate', 15, 2);
            $table->date('tgl_gajian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_parameter_gaji_trainers');
    }
};

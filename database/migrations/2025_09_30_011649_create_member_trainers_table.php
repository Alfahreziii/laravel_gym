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
        Schema::create('member_trainers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique();
            $table->foreignId('id_anggota')->constrained('anggotas')->onDelete('cascade');
            $table->foreignId('id_paket_personal_trainer')->constrained('paket_personal_trainers')->onDelete('cascade');
            $table->foreignId('id_trainer')->constrained('trainers')->onDelete('cascade');
            $table->integer('diskon')->unsigned()->default(0);
            $table->integer('total_biaya')->unsigned();
            $table->string('status_pembayaran', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_trainers');
    }
};

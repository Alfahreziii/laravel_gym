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
        Schema::create('anggota_memberships', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique();
            $table->foreignId('id_anggota')->constrained('anggotas')->onDelete('cascade');
            $table->string('nama_paket', 50);
            $table->foreignId('id_paket_membership')->constrained('paket_memberships')->onDelete('cascade');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->integer('diskon')->unsigned()->default(0);
            $table->integer('total_biaya')->unsigned();
            $table->string('metode_pembayaran', 20);
            $table->string('status_pembayaran', 20);
            $table->date('tgl_bayar');
            $table->integer('total_dibayarkan')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_memberships');
    }
};

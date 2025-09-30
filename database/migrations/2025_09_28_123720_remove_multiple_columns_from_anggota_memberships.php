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
        Schema::table('anggota_memberships', function (Blueprint $table) {
            $table->dropColumn('total_dibayarkan');
            $table->dropColumn('tgl_bayar');
            $table->dropColumn('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggota_memberships', function (Blueprint $table) {
            $table->integer('total_dibayarkan')->nullable(); 
            $table->date('tgl_bayar')->nullable(); 
            $table->string('metode_pembayaran')->nullable(); 
        });
    }
};

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
            $table->dropColumn('nama_paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggota_memberships', function (Blueprint $table) {
            $table->integer('nama_paket')->nullable(); 
        });
    }
};

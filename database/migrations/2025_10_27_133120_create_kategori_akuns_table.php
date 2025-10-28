<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_akuns', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Aset, Kewajiban, Modal
            $table->string('kode', 10)->unique(); // misal: AST, KEW, MOD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_akuns');
    }
};

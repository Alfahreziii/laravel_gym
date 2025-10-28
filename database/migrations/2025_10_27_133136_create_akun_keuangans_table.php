<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_keuangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_akuns')->onDelete('cascade');
            $table->string('nama'); // Kas, Piutang, Peralatan, Hutang, Modal Pemilik
            $table->string('kode', 10)->unique(); // misal: AST001, KEW001, MOD001
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun_keuangans');
    }
};

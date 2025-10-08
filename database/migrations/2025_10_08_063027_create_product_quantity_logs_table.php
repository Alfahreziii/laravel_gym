<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_quantity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // barang masuk / keluar
            $table->integer('quantity'); // jumlah perubahan stok
            $table->integer('current_quantity'); // stok setelah perubahan
            $table->string('description')->nullable(); // alasan perubahan (opsional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_quantity_logs');
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('barcode', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable(); // <- dibuat nullable
            $table->enum('discount_type', ['percent', 'nominal'])->nullable(); 
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('kategori_product_id')->constrained('kategori_products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

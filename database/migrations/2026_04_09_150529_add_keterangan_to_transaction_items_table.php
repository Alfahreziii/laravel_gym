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
        Schema::table('transaction_items', function (Blueprint $table) {
            // Tambah kolom keterangan untuk catatan per item
            $table->text('keterangan')
                ->nullable()
                ->after('diskon')
                ->comment('Catatan/keterangan tambahan untuk setiap item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};

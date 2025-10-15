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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('dibayarkan', 15, 2)->default(0)->after('total_amount');
            $table->decimal('kembalian', 15, 2)->default(0)->after('dibayarkan');
            $table->string('metode_pembayaran', 20)->after('kembalian')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('dibayarkan');
            $table->dropColumn('kembalian');
            $table->dropColumn('metode_pembayaran');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            $table->unsignedBigInteger('referensi_id')->nullable()->after('tanggal');
            $table->string('referensi_tabel')->nullable()->after('referensi_id');
        });
    }

    public function down()
    {
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            $table->dropColumn(['referensi_id', 'referensi_tabel']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('member_trainers', function (Blueprint $table) {
            $table->date('tgl_mulai')->nullable()->after('id_trainer');
            $table->date('tgl_selesai')->nullable()->after('tgl_mulai');
        });
    }

    public function down()
    {
        Schema::table('member_trainers', function (Blueprint $table) {
            $table->dropColumn(['tgl_mulai', 'tgl_selesai']);
        });
    }
};
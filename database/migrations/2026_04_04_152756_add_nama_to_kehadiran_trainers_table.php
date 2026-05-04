<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadiran_trainers', function (Blueprint $table) {
            $table->dropForeign(['rfid']);
            $table->string('nama')->nullable()->after('rfid');
        });
    }

    public function down(): void
    {
        Schema::table('kehadiran_trainers', function (Blueprint $table) {
            $table->dropColumn('nama');
            // Kembalikan foreign key
            $table->foreign('rfid')
                ->references('rfid')->on('trainers')
                ->onDelete('cascade');
        });
    }
};

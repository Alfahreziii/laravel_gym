<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cukup hapus kolom name dari trainers
        // Karena relasi trainer_id sudah ada di users
        if (Schema::hasColumn('trainers', 'name')) {
            Schema::table('trainers', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            if (!Schema::hasColumn('trainers', 'name')) {
                $table->string('name', 255)->after('photo');
            }
        });
    }
};
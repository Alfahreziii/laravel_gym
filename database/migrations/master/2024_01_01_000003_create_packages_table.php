<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        Schema::connection('mysql_master')->create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('label');
            $table->boolean('trainer')->default(false);
            $table->boolean('pos')->default(false);
            $table->boolean('keuangan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_master')->dropIfExists('packages');
    }
};

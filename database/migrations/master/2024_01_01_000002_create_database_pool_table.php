<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        Schema::connection('mysql_master')->create('database_pool', function (Blueprint $table) {
            $table->id();
            $table->string('db_name')->unique();
            $table->string('db_host');
            $table->string('db_username');
            $table->string('db_password');
            $table->enum('status', ['available', 'used'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_master')->dropIfExists('database_pool');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        Schema::connection('mysql_master')->create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->boolean('trainer')->default(false);
            $table->boolean('pos')->default(false);
            $table->boolean('keuangan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_master')->dropIfExists('tenant_modules');
    }
};

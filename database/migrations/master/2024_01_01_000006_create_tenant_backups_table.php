<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        Schema::connection('mysql_master')->create('tenant_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('sql_path')->nullable();
            $table->string('storage_zip_path')->nullable();
            $table->date('tgl_backup')->nullable();
            $table->boolean('downloaded')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_master')->dropIfExists('tenant_backups');
    }
};

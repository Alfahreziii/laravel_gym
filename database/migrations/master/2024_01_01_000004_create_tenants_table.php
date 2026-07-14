<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_master';

    public function up(): void
    {
        Schema::connection('mysql_master')->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gym');
            $table->string('subdomain')->unique();
            $table->string('logo')->nullable();
            $table->text('alamat')->nullable();
            $table->string('email');
            $table->string('no_hp');
            $table->foreignId('database_pool_id')->constrained('database_pool')->restrictOnDelete();
            $table->foreignId('package_id')->constrained('packages')->restrictOnDelete();
            $table->enum('status', ['aktif', 'nonaktif', 'suspend'])->default('aktif');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_master')->dropIfExists('tenants');
    }
};

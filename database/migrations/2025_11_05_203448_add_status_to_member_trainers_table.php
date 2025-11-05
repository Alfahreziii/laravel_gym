<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_trainers', function (Blueprint $table) {
            $table->boolean('is_session_active')->default(false)->after('sesi');
            $table->timestamp('session_started_at')->nullable()->after('is_session_active');
        });
    }

    public function down(): void
    {
        Schema::table('member_trainers', function (Blueprint $table) {
            $table->dropColumn(['is_session_active', 'session_started_at']);
        });
    }
};
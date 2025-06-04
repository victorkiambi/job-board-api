<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['job_seeker', 'company'])->after('password');
            $table->json('profile_data')->nullable()->after('user_type');
            $table->index(['user_type', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'email']);
            $table->dropColumn(['user_type', 'profile_data']);
        });
    }
}; 
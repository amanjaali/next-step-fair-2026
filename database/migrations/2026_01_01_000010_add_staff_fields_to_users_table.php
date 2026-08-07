<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff accounts. Roles come from spatie/laravel-permission: Super Admin,
 * Registration Manager, Content Editor, Check-in Staff and Sponsor Manager.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('email');
            $table->string('phone')->nullable()->after('locale');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('avatar_path')->nullable()->after('job_title');
            $table->string('default_gate', 20)->nullable()->after('avatar_path'); // check-in staff
            $table->boolean('is_active')->default(true)->after('default_gate');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'phone', 'job_title', 'avatar_path', 'default_gate', 'is_active', 'last_login_at']);
        });
    }
};

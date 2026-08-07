<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A registration doubles as the attendee's account.
 *
 * There is no password: the phone number is the identity and a WhatsApp code is
 * the proof, which is the same check registration already performs. These columns
 * are what let that session persist and what records the last time it was used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->rememberToken()->after('user_agent');
            $table->timestamp('last_signed_in_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['remember_token', 'last_signed_in_at']);
        });
    }
};

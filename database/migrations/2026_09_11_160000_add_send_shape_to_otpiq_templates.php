<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-template send shape — must match the approved OTPIQ / Meta template exactly.
 *
 * A mismatch (e.g. sending three body slots when the template only has {{1}})
 * surfaces as Meta error (#132000).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otpiq_templates', function (Blueprint $table) {
            $table->json('body_variables')->nullable()->after('provider_id');
            $table->boolean('send_header')->nullable()->after('body_variables');
            $table->boolean('send_button')->nullable()->after('send_header');
        });
    }

    public function down(): void
    {
        Schema::table('otpiq_templates', function (Blueprint $table) {
            $table->dropColumn(['body_variables', 'send_header', 'send_button']);
        });
    }
};

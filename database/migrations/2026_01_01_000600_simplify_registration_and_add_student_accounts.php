<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students get a real account; everyone else gets a badge.
 *
 * A student registers once and that one Next Step ID carries them through the
 * expo, the panels, the seminars, the workshops, Zankoline and the scholarship
 * application — which runs for months and has to be reachable from any computer,
 * not just the phone that received a code.
 *
 * Parents and delegates keep the badge-only path: nothing to remember, nothing to
 * sign into.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Students only. Null for everyone else, which is also what marks a
            // record as "not an account".
            $table->string('password')->nullable()->after('remember_token');

            // Grade 12, graduated, already at university — the one academic fact
            // the expo needs, and the gate the scholarship checks first.
            $table->string('education_stage', 30)->nullable()->after('current_status');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['password', 'education_stage']);
        });
    }
};

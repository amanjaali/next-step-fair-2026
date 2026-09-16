<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-department seat counts for a scholarship opportunity.
 *
 * The National Scholarship application form needs to offer a student "Pharmacy
 * · 2 seats" rather than just a university name, so a scholarship-kind
 * opportunity with a linked partner can carry its own department breakdown —
 * [{name, seats}, ...] — the same shape config('scholarship.universities')
 * already used, so both sources feed the same dropdown without the view
 * needing to know which one a given entry came from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->json('departments')->nullable()->after('places');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('departments');
        });
    }
};

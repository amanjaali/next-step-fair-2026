<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A picture on the attendee's own page.
 *
 * Separate from `headshot_path`, which a conference speaker supplies for
 * publication in the programme. This one is chosen by the person for their own
 * account and is never published anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('headshot_path');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};

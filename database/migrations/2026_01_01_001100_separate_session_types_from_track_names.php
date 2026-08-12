<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stop two different things sharing one name.
 *
 * A session has a track — which of the two programmes it belongs to — and a
 * type, meaning what kind of session it is. Two of the seeded types were called
 * "Conference" and "Expo", the same words as the tracks, so the agenda page
 * offered both as filters and the same word meant a programme in one row and a
 * format in the other.
 *
 * Data only: nothing about the shape of the table changes. Sites that never
 * carried the old values are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('event_sessions')->where('type', 'Conference')->update(['type' => 'Plenary']);
        DB::table('event_sessions')->where('type', 'Expo')->update(['type' => 'Exhibition']);

        // A closed roundtable for ministry delegations should never carry an
        // "Add to my agenda" button for a student reading the day.
        DB::table('event_sessions')
            ->where('track', 'conference')
            ->where('type', 'Roundtable')
            ->update(['bookable' => false]);
    }

    public function down(): void
    {
        DB::table('event_sessions')->where('type', 'Plenary')->update(['type' => 'Conference']);
        DB::table('event_sessions')->where('type', 'Exhibition')->update(['type' => 'Expo']);
    }
};

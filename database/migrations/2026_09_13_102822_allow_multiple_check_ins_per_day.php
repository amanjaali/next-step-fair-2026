<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gate scanners may record every pass through the door, not only the first
 * of the day. Attendance "checked in on day N" is derived from distinct
 * registration ids; each row is one scan.
 *
 * MySQL uses the composite unique as the index backing the registration_id
 * foreign key, so a plain dropUnique fails with errno 1553. Add a dedicated
 * registration_id index first, then drop the unique, then add the composite
 * lookup index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->index('registration_id', 'check_ins_registration_id_index');
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropUnique(['registration_id', 'day']);
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->index(['registration_id', 'day'], 'check_ins_registration_id_day_index');
        });
    }

    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropIndex('check_ins_registration_id_day_index');
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->unique(['registration_id', 'day']);
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropIndex('check_ins_registration_id_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gate scanners may record every pass through the door, not only the first
 * of the day. Attendance "checked in on day N" is derived from distinct
 * registration ids; each row is one scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropUnique(['registration_id', 'day']);
            $table->index(['registration_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropIndex(['registration_id', 'day']);
            $table->unique(['registration_id', 'day']);
        });
    }
};

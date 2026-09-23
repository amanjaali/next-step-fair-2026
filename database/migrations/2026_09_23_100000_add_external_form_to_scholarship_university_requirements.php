<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A partner's own application form, alongside ours. Whole-university, same as
 * the requirements text this sits beside — one URL regardless of which
 * department a student picks under it, and regardless of locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_university_requirements', function (Blueprint $table) {
            $table->boolean('requires_external_form')->default(false)->after('requirements');
            $table->string('external_form_url')->nullable()->after('requires_external_form');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_university_requirements', function (Blueprint $table) {
            $table->dropColumn(['requires_external_form', 'external_form_url']);
        });
    }
};

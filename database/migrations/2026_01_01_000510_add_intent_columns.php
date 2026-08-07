<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What each side is actually looking for.
 *
 * These are the columns matching runs on, and the ones that answer "which fields,
 * which countries, which sectors" after the event. They are deliberately banded
 * rather than exact — a seventeen-year-old cannot tell you their tuition budget to
 * the dollar, but they can tell you whether they are looking under or over five
 * thousand, and a band is what a recruiter plans against anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // What they want to study and where.
            $table->string('degree_level', 20)->nullable()->after('study_abroad');
            $table->json('preferred_countries')->nullable()->after('degree_level');
            $table->string('language_preference', 10)->nullable()->after('preferred_countries');
            $table->string('budget_band', 20)->nullable()->after('language_preference');
            $table->unsignedSmallInteger('start_year')->nullable()->after('budget_band');
            $table->string('career_goal', 60)->nullable()->after('start_year');

            // Academic background, banded for the same reason.
            $table->string('grade_band', 20)->nullable()->after('stream');

            // Consent to be introduced to matched institutions. Off unless asked for.
            $table->boolean('share_with_institutions')->default(false)->after('career_goal');
            $table->timestamp('profile_completed_at')->nullable()->after('share_with_institutions');

            $table->index('degree_level');
            $table->index('budget_band');
        });

        Schema::table('organizations', function (Blueprint $table) {
            // Recruitment profile — filled in by the institution, not by us.
            $table->json('degree_levels')->nullable()->after('country');
            $table->json('languages')->nullable()->after('degree_levels');
            $table->json('campus_countries')->nullable()->after('languages');
            $table->string('city')->nullable()->after('campus_countries');
            $table->unsignedInteger('tuition_min')->nullable()->after('city');
            $table->unsignedInteger('tuition_max')->nullable()->after('tuition_min');
            $table->string('tuition_currency', 4)->default('USD')->after('tuition_max');
            $table->boolean('offers_scholarships')->default(false)->after('tuition_currency');
            $table->json('scholarship_notes')->nullable()->after('offers_scholarships');
            $table->string('min_grade_band', 20)->nullable()->after('scholarship_notes');
            $table->json('entry_requirements')->nullable()->after('min_grade_band');
            $table->unsignedInteger('intake_capacity')->nullable()->after('entry_requirements');
            $table->date('application_deadline')->nullable()->after('intake_capacity');
            $table->json('recruitment_goals')->nullable()->after('application_deadline');
            $table->string('contact_email')->nullable()->after('recruitment_goals');
            $table->string('contact_phone', 32)->nullable()->after('contact_email');

            // Self-registration lifecycle: an institution claims its entry, we approve it.
            $table->string('claim_status', 20)->default('unclaimed')->after('contact_phone');
            $table->timestamp('claimed_at')->nullable()->after('claim_status');
            $table->timestamp('profile_completed_at')->nullable()->after('claimed_at');

            $table->index('claim_status');
            $table->index('offers_scholarships');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'degree_level', 'preferred_countries', 'language_preference', 'budget_band',
                'start_year', 'career_goal', 'grade_band', 'share_with_institutions',
                'profile_completed_at',
            ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'degree_levels', 'languages', 'campus_countries', 'city', 'tuition_min',
                'tuition_max', 'tuition_currency', 'offers_scholarships', 'scholarship_notes',
                'min_grade_band', 'entry_requirements', 'intake_capacity', 'application_deadline',
                'recruitment_goals', 'contact_email', 'contact_phone', 'claim_status',
                'claimed_at', 'profile_completed_at',
            ]);
        });
    }
};

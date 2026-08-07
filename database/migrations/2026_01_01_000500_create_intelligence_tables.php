<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The data layer that makes the fair worth measuring.
 *
 * Everything here exists to answer two questions after the doors close: what do
 * students actually want, and who is offering it. That only works if both sides
 * describe themselves in the same words, which is what the taxonomy is for — a
 * free-text "interested in engineering" from one side and "Faculty of Civil and
 * Environmental Engineering" from the other can never be counted together.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
        | Sectors group fields. Students think in sectors ("something in health"),
        | universities publish in fields ("BSc Nursing"), and reporting needs both.
        */
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        /*
        | What a student wants, ranked. Rank matters: a first choice and a "maybe"
        | should not carry the same weight in either matching or demand figures.
        */
        Schema::create('field_registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank')->default(1);   // 1 = first choice
            $table->timestamps();

            $table->unique(['registration_id', 'field_id']);
            $table->index(['field_id', 'rank']);
        });

        /*
        | What an institution offers, per field and per level. The same university
        | may teach Civil Engineering at bachelor level only and Public Health all
        | the way to PhD; matching has to know the difference.
        */
        Schema::create('field_organization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();
            $table->string('level', 20)->default('bachelor');  // foundation|diploma|bachelor|master|phd
            $table->string('language', 10)->nullable();        // language of instruction
            $table->unsignedInteger('tuition_min')->nullable();
            $table->unsignedInteger('tuition_max')->nullable();
            $table->boolean('scholarship')->default(false);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'field_id', 'level']);
            $table->index(['field_id', 'level']);
        });

        /*
        | Institution staff accounts. Passwordless like the attendee side, but keyed
        | on the institutional e-mail address rather than a phone number, because
        | that is the credential a university actually controls.
        */
        Schema::create('institution_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('email');                              // encrypted
            $table->string('email_hash', 64)->unique();
            $table->string('job_title')->nullable();
            $table->text('phone')->nullable();                  // encrypted
            $table->string('locale', 5)->default('en');
            $table->string('role', 20)->default('member');      // owner | member
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_signed_in_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Sign-in codes for institution staff — the e-mail equivalent of the OTP.
        Schema::create('institution_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        /*
        | Every touch between a student and an institution, whatever produced it.
        | A booth scan is worth more than a saved shortlist, which is worth more
        | than a profile view, so the type is kept rather than flattened to a count.
        */
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // booth_scan | shortlist | enquiry | profile_view | brochure
            $table->string('type', 20)->index();
            $table->foreignId('field_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day')->nullable();
            $table->foreignId('institution_user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();   // desk's own read: 1–5
            $table->text('notes')->nullable();
            $table->boolean('follow_up')->default(false);
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'type']);
            $table->index(['registration_id', 'organization_id']);
        });

        /*
        | Computed matches. Stored rather than derived on the fly so the numbers a
        | student saw are the numbers reporting sees, and so a recruiter's list does
        | not silently reshuffle between page loads.
        */
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');                // 0–100
            $table->json('reasons')->nullable();                 // why it matched, for display
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'organization_id']);
            $table->index(['organization_id', 'score']);
            $table->index(['registration_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('interactions');
        Schema::dropIfExists('institution_otps');
        Schema::dropIfExists('institution_users');
        Schema::dropIfExists('field_organization');
        Schema::dropIfExists('field_registration');
        Schema::dropIfExists('fields');
        Schema::dropIfExists('sectors');
    }
};

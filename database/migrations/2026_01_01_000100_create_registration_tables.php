<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The registration core.
 *
 * One table carries both tracks with a `track` discriminator, exactly as the
 * brief specifies: the fair and the conference never share a form, but they do
 * share a ticket, a badge pipeline and a check-in desk.
 *
 * Phone numbers and e-mail addresses are encrypted at rest by the model's casts.
 * The `*_hash` columns hold a keyed SHA-256 of the same value so duplicate
 * detection, admin search and the check-in fallback still work against an index
 * without ever storing the plaintext in a queryable column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Ticket identity — this is what the QR resolves to.
            $table->uuid('ticket_id')->unique();
            $table->string('ticket_ref', 20)->unique();      // human-readable, e.g. 8F2C-41A9-D77E

            $table->string('track', 20)->index();            // fair | conference
            $table->string('type', 20)->index();             // student | parent | government | official
            $table->string('status', 24)->default('draft')->index();
            $table->string('locale', 5)->default('en');      // language for badge and messages

            // Identity
            $table->string('full_name');
            $table->text('phone')->nullable();               // encrypted
            $table->string('phone_hash', 64)->nullable()->index();
            $table->string('phone_country', 8)->default('+964');
            $table->text('email')->nullable();               // encrypted
            $table->string('email_hash', 64)->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();

            // Fair — student
            $table->string('school_name')->nullable();
            $table->string('stream', 30)->nullable();
            $table->string('current_status', 30)->nullable();
            $table->json('fields_of_study')->nullable();
            $table->string('study_abroad', 10)->nullable();

            // Fair — parent
            $table->string('relationship', 20)->nullable();
            $table->string('children_count', 10)->nullable();
            $table->string('child_grade')->nullable();
            $table->json('topics')->nullable();

            // Conference — both types
            $table->string('position')->nullable();
            $table->string('organization')->nullable();      // printed as line 2 of the badge
            $table->string('department')->nullable();
            $table->string('org_type', 30)->nullable();
            $table->string('org_website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('delegation_size', 20)->nullable();
            $table->string('interpretation', 20)->nullable();
            $table->boolean('invitation_letter')->default(false);
            $table->string('dietary', 30)->nullable();
            $table->string('dietary_other')->nullable();
            $table->text('accessibility')->nullable();
            $table->boolean('is_speaking')->default(false);
            $table->string('speaking_title')->nullable();
            $table->text('speaking_bio')->nullable();
            $table->string('headshot_path')->nullable();
            $table->boolean('media_accreditation')->default(false);
            $table->string('media_outlet')->nullable();
            $table->unsignedSmallInteger('media_crew')->nullable();
            $table->string('press_id_path')->nullable();
            $table->text('notes')->nullable();

            // Attendance
            $table->json('days')->nullable();                // [1,2,3]
            $table->json('reasons')->nullable();
            $table->string('hear_about', 30)->nullable();

            // Consent, captured per purpose with a timestamp and the source IP.
            $table->json('consents')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->string('consent_ip', 45)->nullable();

            // Attribution
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->foreignId('qr_campaign_id')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            // Lifecycle
            $table->timestamp('verified_at')->nullable();    // OTP verified (fair)
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('approved_at')->nullable();    // protocol approval (conference)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Badge artefacts
            $table->timestamp('badge_generated_at')->nullable();
            $table->string('badge_png_path')->nullable();
            $table->string('badge_pdf_path')->nullable();

            // Walk-ins entered at the door carry the staff member who keyed them in.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_walk_in')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['track', 'status']);
            $table->index(['track', 'type']);
            $table->index('created_at');
        });

        /*
         * One 6-digit code per attempt. The code itself is hashed: a leaked
         * database row cannot be replayed to claim someone else's ticket.
         */
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('phone_hash', 64)->index();
            $table->string('code_hash');
            $table->string('channel', 20)->default('whatsapp');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        /*
         * Check-in is per day: a three-day badge is scanned once on each day it
         * is used, and the row records who scanned it and at which gate.
         */
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day');
            $table->timestamp('checked_in_at');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('gate', 20)->nullable();
            $table->string('method', 20)->default('scan');   // scan | manual
            $table->string('device_id', 64)->nullable();     // for de-duplicating offline queue syncs
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'day']);
            $table->index('checked_in_at');
        });

        // Personal agenda: sessions a registrant saved, and the reminder we owe them.
        Schema::create('registration_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_session');
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('registrations');
    }
};

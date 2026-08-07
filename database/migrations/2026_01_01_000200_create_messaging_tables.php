<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The outbound message queue and its delivery log.
 *
 * Every WhatsApp message and every transactional e-mail is recorded here before
 * it is sent, so the admin can see sent / delivered / read / failed per message,
 * retry a failure, and answer "did this person actually get their badge?".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();                    // registration_confirmed_student
            $table->string('channel', 20)->default('whatsapp'); // whatsapp | email | sms
            $table->string('locale', 5);
            $table->string('name')->nullable();
            $table->string('subject')->nullable();             // e-mail only
            $table->text('body');
            $table->string('meta_template_name')->nullable();  // the name approved by Meta
            $table->json('variables')->nullable();             // ordered {{1}}, {{2}} mapping
            $table->string('approval_status', 20)->default('pending'); // pending | approved | rejected
            $table->timestamp('approved_at')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'channel', 'locale']);
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel', 20)->default('whatsapp');
            $table->string('template_key')->nullable();
            $table->json('filters')->nullable();               // e.g. day 2 + city Sulaimani + track fair
            $table->text('body_override')->nullable();
            $table->unsignedInteger('audience_count')->default(0);
            $table->string('status', 20)->default('draft');    // draft | scheduled | sending | sent | cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('broadcast_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20)->index();            // whatsapp | email | sms
            $table->string('template_key')->nullable()->index();
            $table->string('locale', 5)->default('en');
            $table->text('recipient');                         // encrypted phone or e-mail
            $table->string('recipient_hash', 64)->nullable()->index();
            $table->string('subject')->nullable();
            $table->text('preview')->nullable();               // rendered body, for the admin log
            $table->json('payload')->nullable();               // provider request body
            $table->string('status', 20)->default('queued')->index(); // queued|sent|delivered|read|failed
            $table->string('provider_message_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'channel']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('message_templates');
    }
};

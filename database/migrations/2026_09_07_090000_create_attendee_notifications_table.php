<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Updates a student is shown when they next sign in.
 *
 * A scholarship decision is the most consequential message this site sends, and
 * until now it was sent nowhere: the committee recorded it and the student's
 * tracker simply moved a marker to "Decision". This is where the telling lives.
 *
 * The wording is not stored. What is stored is what happened — a type and the
 * facts behind it — so the same record reads in Kurdish, Arabic or English
 * depending on who opens it, and a change to the wording corrects every
 * notification already sent rather than only the next one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();

            // 'scholarship.decision', 'scholarship.stage' — the key the wording
            // is looked up under, and what the icon and colour are chosen from.
            $table->string('type', 60);

            // The facts: which decision, which stage, which cycle.
            $table->json('data')->nullable();

            // Where "see the update" goes. A route name, not a URL: the URL
            // carries the locale, and the student may read this in another one.
            $table->string('route')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // The two questions asked on every page: how many are unread, and
            // what are the most recent.
            $table->index(['registration_id', 'read_at']);
            $table->index(['registration_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_notifications');
    }
};

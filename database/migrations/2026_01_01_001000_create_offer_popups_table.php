<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The popup that greets somebody on the home page.
 *
 * One popup, several offers inside it — a student should see everything open to
 * them in one interruption rather than be interrupted once per offer. It is
 * switched on and off from the dashboard and expected to change most days, so
 * everything about it is content rather than code.
 *
 * `updated_at` doubles as the version: the browser remembers which version it
 * has already dismissed, so an edit brings it back and an untouched popup stays
 * gone. Without that, editing it daily would change nothing for anybody who had
 * closed it once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_popups', function (Blueprint $table) {
            $table->id();

            $table->json('title');
            $table->json('intro')->nullable();
            $table->json('dismiss_label')->nullable();

            // Who it interrupts. Same vocabulary as the opportunities board.
            $table->string('audience', 30)->default('students');

            $table->boolean('active')->default(false);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            // How many people saw it, and how many followed something out of it.
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();

            $table->index(['active', 'starts_at', 'ends_at']);
        });

        Schema::create('offer_popup_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_popup_id')->constrained()->cascadeOnDelete();

            $table->json('title');
            $table->json('body')->nullable();
            $table->json('action_label')->nullable();

            $table->string('badge', 60)->nullable();
            $table->string('action_url', 500)->nullable();

            $table->unsignedSmallInteger('sort')->default(0);
            $table->unsignedInteger('click_count')->default(0);

            $table->timestamps();

            $table->index(['offer_popup_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_popup_items');
        Schema::dropIfExists('offer_popups');
    }
};

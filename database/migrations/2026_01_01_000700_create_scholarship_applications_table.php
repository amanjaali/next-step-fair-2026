<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One application per student per cycle.
 *
 * It hangs off the registration, so the student who registered for the expo is
 * the student who applies — no second identity, no re-typing a name and a phone
 * number that are already on file and already verified.
 *
 * The form saves as it goes. A student on a school computer with twenty minutes
 * before the bus should be able to stop and come back, which is why every field
 * is nullable and `step` records how far they got.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('cycle', 20);

            $table->string('status', 30)->default('draft');
            $table->unsignedTinyInteger('step')->default(1);

            // The eligibility check, kept so a student can see what they answered
            // and the committee can see what they were told.
            $table->json('eligibility')->nullable();
            $table->timestamp('eligibility_passed_at')->nullable();

            // Which quota they compete in. Set once, at submission.
            $table->string('region_code', 8)->nullable();
            $table->string('district', 60)->nullable();

            $table->decimal('exam_average', 5, 2)->nullable();
            $table->string('exam_status', 20)->nullable();     // published or pending
            $table->string('stream', 30)->nullable();
            $table->string('school_name', 190)->nullable();

            $table->string('first_choice_university', 190)->nullable();
            $table->string('first_choice_department', 120)->nullable();
            $table->string('second_choice_university', 190)->nullable();
            $table->string('second_choice_department', 120)->nullable();

            $table->text('statement')->nullable();
            $table->text('proposal')->nullable();

            // Paths only; the files themselves sit on a private disk.
            $table->json('documents')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('screened_at')->nullable();
            $table->timestamp('shortlisted_at')->nullable();
            $table->timestamp('interviewed_at')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->decimal('score_academic', 5, 2)->nullable();
            $table->decimal('score_feasibility', 5, 2)->nullable();
            $table->decimal('score_interview', 5, 2)->nullable();
            $table->decimal('score_total', 5, 2)->nullable();
            $table->text('committee_notes')->nullable();
            $table->string('decision', 20)->nullable();

            $table->timestamps();

            // One live application per student per cycle.
            $table->unique(['registration_id', 'cycle']);
            $table->index(['cycle', 'region_code', 'status']);
            $table->index(['cycle', 'score_total']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_applications');
    }
};

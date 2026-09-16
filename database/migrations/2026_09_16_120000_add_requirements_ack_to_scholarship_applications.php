<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proof, not just a checkbox: what a student actually saw and agreed to before
 * picking each university, stamped with when. Requirements text can change
 * later — this keeps a snapshot of the version they acknowledged, not just a
 * flag, so a dispute has an answer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->timestamp('first_choice_requirements_ack_at')->nullable()->after('first_choice_department');
            $table->longText('first_choice_requirements_snapshot')->nullable()->after('first_choice_requirements_ack_at');
            $table->timestamp('second_choice_requirements_ack_at')->nullable()->after('second_choice_department');
            $table->longText('second_choice_requirements_snapshot')->nullable()->after('second_choice_requirements_ack_at');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn([
                'first_choice_requirements_ack_at',
                'first_choice_requirements_snapshot',
                'second_choice_requirements_ack_at',
                'second_choice_requirements_snapshot',
            ]);
        });
    }
};

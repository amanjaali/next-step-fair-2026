<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same reasoning as the requirements-ack columns this sits beside: not just a
 * flag, but which URL a student was actually sent to and when they confirmed
 * completing it — a partner changing their form link later must not rewrite
 * what an applicant already agreed to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->timestamp('first_choice_external_form_ack_at')->nullable()->after('first_choice_requirements_snapshot');
            $table->string('first_choice_external_form_url_ack')->nullable()->after('first_choice_external_form_ack_at');
            $table->timestamp('second_choice_external_form_ack_at')->nullable()->after('second_choice_requirements_snapshot');
            $table->string('second_choice_external_form_url_ack')->nullable()->after('second_choice_external_form_ack_at');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn([
                'first_choice_external_form_ack_at',
                'first_choice_external_form_url_ack',
                'second_choice_external_form_ack_at',
                'second_choice_external_form_url_ack',
            ]);
        });
    }
};

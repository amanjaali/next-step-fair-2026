<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A third, fourth and fifth choice — same six columns each as first and
 * second already have (university, department, the requirements ack and its
 * snapshot, the external-form ack and the URL it was given for). All
 * nullable and purely additive: existing applications simply have nothing in
 * these, which is exactly what "only picked two" means.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            foreach (['third', 'fourth', 'fifth'] as $slot) {
                $table->string("{$slot}_choice_university", 190)->nullable();
                $table->string("{$slot}_choice_department", 120)->nullable();
                $table->timestamp("{$slot}_choice_requirements_ack_at")->nullable();
                $table->longText("{$slot}_choice_requirements_snapshot")->nullable();
                $table->timestamp("{$slot}_choice_external_form_ack_at")->nullable();
                $table->string("{$slot}_choice_external_form_url_ack")->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $columns = [];

            foreach (['third', 'fourth', 'fifth'] as $slot) {
                $columns[] = "{$slot}_choice_university";
                $columns[] = "{$slot}_choice_department";
                $columns[] = "{$slot}_choice_requirements_ack_at";
                $columns[] = "{$slot}_choice_requirements_snapshot";
                $columns[] = "{$slot}_choice_external_form_ack_at";
                $columns[] = "{$slot}_choice_external_form_url_ack";
            }

            $table->dropColumn($columns);
        });
    }
};

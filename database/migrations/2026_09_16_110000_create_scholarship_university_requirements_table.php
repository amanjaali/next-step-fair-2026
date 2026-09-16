<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a student needs to read before picking a university on the National
 * Scholarship application form — kept separate from both
 * config('scholarship.universities') and Opportunity so it applies to either
 * source uniformly, keyed by the same `slug` both already carry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_university_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('university_slug')->unique();
            $table->json('requirements')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_university_requirements');
    }
};

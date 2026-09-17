<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Universities that pledge seats to the National Scholarship Program.
 *
 * Seat pledges change between cycles, so they live here rather than in
 * config — the dashboard edits them. Fair exhibitors (organizations) are a
 * different list: a university can hold seats without being on the floor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_universities', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 80)->unique();
            $table->string('name');
            $table->string('city', 80);
            $table->string('language', 80);
            $table->string('tier', 20)->default('donor');
            $table->unsignedSmallInteger('founded')->nullable();
            $table->unsignedInteger('students')->nullable();
            $table->string('housing', 30)->default('none');

            // Short description on the university page, per language.
            $table->json('about')->nullable();

            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('published')->default(true);

            $table->timestamps();

            $table->index(['published', 'sort']);
        });

        Schema::create('scholarship_university_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_university_id')
                ->constrained('scholarship_universities')
                ->cascadeOnDelete();

            $table->string('name');
            $table->unsignedTinyInteger('seats')->default(1);
            $table->unsignedSmallInteger('sort')->default(0);

            $table->timestamps();

            $table->index(['scholarship_university_id', 'sort'], 'sch_uni_dept_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_university_departments');
        Schema::dropIfExists('scholarship_universities');
    }
};

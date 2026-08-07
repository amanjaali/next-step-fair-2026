<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Things a registered student can actually go after.
 *
 * A ministry scholarship, a university offer negotiated through Next Step, an
 * internship, a workshop with places left. They come from partners rather than
 * from us, which is the point: registering is what puts a student in front of
 * them, and that is the honest answer to "what do I get for signing up?".
 *
 * Deliberately not a news post. A post is read once; an opportunity has a
 * deadline, an audience it applies to, and somewhere to go and apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('kind', 30)->default('offer');

            // Whose opportunity it is. Either a partner already in the directory,
            // or a name and logo for one that is not.
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('partner_name', 190)->nullable();
            $table->string('partner_logo_path')->nullable();

            $table->json('title');
            $table->json('summary');
            $table->json('body')->nullable();
            $table->json('eligibility')->nullable();

            // Where it sends them. An external form, or a page on this site.
            $table->string('action_url', 500)->nullable();
            $table->json('action_label')->nullable();

            $table->date('opens_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->unsignedSmallInteger('places')->nullable();

            /*
             * Who it is for. An opportunity aimed at grade 12 has no business
             * appearing to a parent, and showing it anyway is how a feed stops
             * being worth reading.
             */
            $table->string('audience', 30)->default('students');

            $table->boolean('featured')->default(false);
            $table->boolean('published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);

            /*
             * Two different numbers, and a partner is owed both: how many people
             * read it, and how many went on to their form. One column mixing the
             * two would flatter every placement equally.
             */
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('follow_count')->default(0);

            $table->timestamps();

            $table->index(['published', 'audience', 'closes_at']);
            $table->index(['published', 'featured', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};

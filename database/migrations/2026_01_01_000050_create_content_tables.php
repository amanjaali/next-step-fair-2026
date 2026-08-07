<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial and programme content.
 *
 * Every visitor-facing string is a JSON column holding {"en": ..., "ku": ..., "ar": ...}
 * so one row serves all three languages and the admin can show a per-record
 * translation-completeness indicator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('news');     // news | blog | media
            $table->string('slug')->index();
            $table->json('name');
            $table->string('accent', 20)->nullable();        // hex used for the card rule
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('news')->index();   // news | blog
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('standfirst')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();                       // rich text per language
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cover_path')->nullable();
            $table->json('cover_caption')->nullable();
            $table->string('cover_placeholder')->nullable();        // grey-frame label until artwork lands
            $table->string('author_name')->nullable();
            $table->json('author_role')->nullable();
            $table->text('author_bio')->nullable();
            $table->string('author_photo')->nullable();
            $table->json('quote')->nullable();
            $table->json('quote_by')->nullable();
            $table->json('facts')->nullable();                      // [{k,v}] side panel
            $table->json('gallery')->nullable();
            $table->boolean('pinned')->default(false);
            $table->string('status', 12)->default('draft')->index(); // draft | scheduled | published
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('role')->nullable();                       // title / position
            $table->json('organization')->nullable();               // required on the card
            $table->json('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('country', 4)->default('IQ');
            $table->string('track', 20)->default('fair')->index();  // fair | conference
            $table->string('speaker_type', 20)->default('speaker'); // speaker | panelist | moderator | workshop
            $table->json('topics')->nullable();
            $table->json('links')->nullable();                      // [{label,url}]
            $table->unsignedSmallInteger('year')->default(2026)->index();
            $table->boolean('featured')->default(false);
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique();                    // A, B, C, S
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('meta')->nullable();                       // "32 booths · Zones A1–A4"
            $table->string('color', 20)->default('#2C4BE0');
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            // university | institute | exhibitor | strategic | supporter | sponsor | media
            $table->string('kind', 20)->index();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('tier', 20)->nullable()->index();        // platinum | gold | silver | bronze
            $table->json('badge')->nullable();                      // "Since 2024" chip
            $table->string('booth', 20)->nullable();
            $table->foreignId('hall_id')->nullable()->constrained()->nullOnDelete();
            $table->string('country', 4)->default('IQ');
            $table->unsignedSmallInteger('since_year')->nullable();
            $table->unsignedSmallInteger('year')->default(2026)->index();
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->string('code', 12);
            $table->json('name');
            $table->json('kind')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['hall_id', 'code']);
        });

        // `event_sessions` rather than `sessions`: Laravel's session driver owns that name.
        Schema::create('event_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('description')->nullable();
            $table->unsignedTinyInteger('day')->index();
            $table->time('starts_at');
            $table->time('ends_at')->nullable();
            $table->string('duration_label', 20)->nullable();       // "90 min", "All day"
            $table->string('type', 20)->index();                    // Ceremony | Conference | Panel | Workshop | Seminar | Expo | Roundtable | Break
            $table->string('track', 20)->default('fair')->index();
            $table->foreignId('hall_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hall_label')->nullable();               // "Halls A & C"
            $table->string('languages', 30)->nullable();            // "KU · AR · EN"
            $table->string('topic', 40)->nullable();
            $table->json('who')->nullable();                        // free-text credit line
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->unsignedSmallInteger('year')->default(2026)->index();
            $table->boolean('bookable')->default(true);
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('event_session_speaker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->nullable();                 // Keynote | Panelist | Chair | Moderator
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['session_id', 'speaker_id']);
        });

        Schema::create('media_albums', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('day')->nullable();
            $table->string('category', 40)->nullable();             // Opening ceremony | Panels | Booths ...
            $table->string('cover_path')->nullable();
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_album_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 10)->default('photo');           // photo | video | reel
            $table->string('path')->nullable();                     // stored image
            $table->string('url')->nullable();                      // YouTube / Vimeo
            $table->string('provider', 20)->nullable();
            $table->string('poster_path')->nullable();
            $table->json('alt')->nullable();                        // required for every image
            $table->json('caption')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('category', 40)->nullable();
            $table->boolean('downloadable')->default(false);        // press full-resolution download
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        /*
         * A past edition is a small site, not a photo dump: themes, the opening
         * address, speakers, panels and that year's partners all live here. The
         * repeating blocks are JSON so a new year needs data, not a migration.
         */
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedTinyInteger('edition_no');
            $table->json('edition_label');                          // "3rd edition"
            $table->json('dates_label');
            $table->json('venue_label');
            $table->json('headline');
            $table->json('summary');
            $table->json('stats');                                  // [{k,v}]
            $table->json('theme_title')->nullable();
            $table->json('themes')->nullable();                     // [{num,title,body,outcome,accent}]
            $table->string('organizer_name')->nullable();
            $table->json('organizer_role')->nullable();
            $table->json('speech_where')->nullable();
            $table->json('speech_quote')->nullable();
            $table->json('speech')->nullable();                     // rich text
            $table->string('organizer_photo')->nullable();
            $table->json('speaker_count')->nullable();
            $table->json('speakers')->nullable();                   // [{name,role,org,role2,accent}]
            $table->json('panel_note')->nullable();
            $table->json('panels')->nullable();                     // [{day,time,type,hall,title,who,attendance}]
            $table->json('sponsor_note')->nullable();
            $table->json('sponsor_tiers')->nullable();              // [{tier,note,logos[]}]
            $table->string('cover_path')->nullable();
            $table->string('report_path')->nullable();
            $table->string('recap_video')->nullable();
            $table->json('press_links')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        // Long-form editable pages: privacy, terms, press kit, about, fair, conference.
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('kicker')->nullable();
            $table->json('title');
            $table->json('standfirst')->nullable();
            $table->json('body')->nullable();
            $table->json('sections')->nullable();                   // [{n,h,p[],list[]}]
            $table->json('aside')->nullable();                      // {title, body, contact}
            $table->json('foot_note')->nullable();
            $table->date('updated_on')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('sdg_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('color', 20);                            // official UN colour
            $table->json('title');
            $table->json('what')->nullable();
            $table->json('detail')->nullable();
            $table->json('metric')->nullable();
            $table->string('figure', 30)->nullable();
            $table->string('icon_path')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // "Why attend" cards — bar-composition icons drawn from the logo element.
        Schema::create('feature_cards', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30)->default('why_attend')->index();
            $table->json('title');
            $table->json('body')->nullable();
            $table->text('icon_path')->nullable();                  // SVG path data, ink
            $table->text('icon_accent')->nullable();                // SVG path data, magenta
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20)->default('report')->index(); // report | press | deck | floorplan
            $table->json('name');
            $table->json('kind')->nullable();                        // Logos | Photography | Document | Video
            $table->json('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('size_label', 30)->nullable();
            $table->string('accent', 20)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40)->default('general')->index();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'settings', 'downloads', 'feature_cards', 'sdg_goals', 'pages', 'editions',
            'media_items', 'media_albums', 'event_session_speaker', 'event_sessions',
            'booths', 'organizations', 'halls', 'speakers', 'posts', 'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

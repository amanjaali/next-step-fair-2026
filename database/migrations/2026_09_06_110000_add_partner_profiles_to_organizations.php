<?php

use App\Models\Organization;
use Database\Seeders\ProgrammeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A page behind each partner's mark.
 *
 * The ministry's mark is at the top of every page and the students' association
 * is beside it; both were pictures that went nowhere. A partnership shown but
 * never explained asks the visitor to take it on trust — and the partners
 * themselves have nothing to point their own people at.
 *
 * Two long fields, because they answer two different questions: who this
 * organisation is, and what it does with Next Step. Contact details are separate
 * columns rather than prose so they can be a link, a mailto and a tel.
 *
 * Named public_ to keep them apart from contact_email and contact_phone, which
 * an exhibitor types into their own portal and which are for our recruitment
 * team, not for publishing.
 *
 * Dated before the migration that adds the students' association on purpose.
 * That one creates records through the model, and Eloquent caches a table's
 * column list the first time it mass-assigns to it — a cache that outlives the
 * migration and leaves the rest of the process unable to write to a column
 * added afterwards. Adding the columns first means the cache is never stale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->json('about')->nullable()->after('description');
            $table->json('partnership')->nullable()->after('about');
            $table->string('public_email')->nullable()->after('website');
            $table->string('public_phone', 40)->nullable()->after('public_email');
        });

        $this->fillBlankProfiles();
    }

    /**
     * Starter wording for the three marks, on an installation already running.
     *
     * Only where nothing has been written: a partner someone has already
     * described in the dashboard keeps what they wrote. Without this the marks
     * in the header would go on linking nowhere until somebody happened to open
     * the new screen.
     */
    private function fillBlankProfiles(): void
    {
        foreach (ProgrammeSeeder::strategicPartners() as $partner) {
            $record = Organization::where('slug', $partner['slug'])->first();

            if (! $record) {
                continue;
            }

            $changes = [];

            foreach (['about', 'partnership'] as $field) {
                if (blank($record->getTranslations($field))) {
                    $changes[$field] = $partner[$field];
                }
            }

            if ($changes !== []) {
                $record->forceFill($changes)->save();
            }
        }
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['about', 'partnership', 'public_email', 'public_phone']);
        });
    }
};

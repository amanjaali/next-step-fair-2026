<?php

use App\Models\Organization;
use Database\Seeders\ProgrammeSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * The Kurdistan Students Association, added to a site that is already running.
 *
 * Seeders are for a database being built; this one is for the one in service,
 * with real registrations in it, where re-seeding is not an option. It creates
 * only what is missing — the ministry and the regional government keep whatever
 * wording they have been given in the dashboard.
 *
 * The logo is not part of this. It is uploaded on the Brand images screen, in
 * the slot named "ksa", and nothing is drawn for the association anywhere until
 * it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new ProgrammeSeeder)->syncStrategicPartners(overwrite: false);
    }

    public function down(): void
    {
        Organization::where('slug', 'ksa')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A QR code per exhibitor, for the card on their desk.
 *
 * Separate from `interactions` (which records a *known* student meeting an
 * exhibitor, and is what the institution portal's lead scoring reads): a
 * visitor scanning a desk card may not be signed in at all, so this counts
 * every scan regardless, and only carries a registration when one exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('qr_code', 20)->nullable()->unique()->after('slug');
            $table->unsignedInteger('qr_scan_count')->default(0)->after('qr_code');
        });

        // Backfill existing rows — the model's creating hook only covers new ones.
        DB::table('organizations')->whereNull('qr_code')->orderBy('id')->get(['id'])->each(function ($org) {
            DB::table('organizations')->where('id', $org->id)->update([
                'qr_code' => strtolower(Str::random(7)),
            ]);
        });

        Schema::create('booth_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['organization_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booth_scans');

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'qr_scan_count']);
        });
    }
};

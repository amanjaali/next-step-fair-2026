<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Deprecated — OTPIQ settings now live in .env / config/whatsapp.php only.
 * Kept so existing migration history stays valid; the refactor migration drops
 * otpiq_settings when upgrading from an earlier deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        // no-op
    }

    public function down(): void
    {
        // no-op
    }
};

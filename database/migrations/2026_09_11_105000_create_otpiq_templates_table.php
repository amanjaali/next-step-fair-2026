<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTPIQ template identity — one row per logical key × locale.
 *
 * Only the dashboard name and provider id are stored here so they can be updated
 * in Filament when OTPIQ re-issues a template. Body slots and header-image
 * flags live in config/whatsapp.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otpiq_templates', function (Blueprint $table) {
            $table->id();
            $table->string('logical_key')->index();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['logical_key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otpiq_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTPIQ WhatsApp template registry — one row per logical key × locale.
 *
 * Dashboard ids and the exact approved template names live here instead of .env
 * so staging and production can diverge without redeploying config.
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
            $table->json('body_variables');
            $table->boolean('header_image')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['logical_key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otpiq_templates');
    }
};

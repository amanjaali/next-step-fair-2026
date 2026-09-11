<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTPIQ account credentials and badge send flags — one row for the whole site.
 *
 * Replaces OTPIQ_* keys in .env so staging and production can differ without
 * redeploying config files.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otpiq_settings', function (Blueprint $table) {
            $table->id();
            $table->string('base_url')->default('https://api.otpiq.com/api');
            $table->text('api_key')->nullable();
            $table->string('account_id')->nullable();
            $table->string('phone_id')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('send_header_image')->default(false);
            $table->boolean('send_button_link')->default(false);
            $table->string('public_url')->default('https://www.nextstepfair.com');
            $table->string('local_header_image')->nullable();
            $table->boolean('verify_ssl')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otpiq_settings');
    }
};

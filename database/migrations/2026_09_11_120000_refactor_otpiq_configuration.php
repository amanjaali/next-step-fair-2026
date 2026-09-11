<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTPIQ secrets and connection settings live in .env only.
 * The database keeps template identity (name + provider id) per logical key × locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('otpiq_settings');

        if (! Schema::hasTable('otpiq_templates')) {
            return;
        }

        Schema::table('otpiq_templates', function (Blueprint $table) {
            if (Schema::hasColumn('otpiq_templates', 'body_variables')) {
                $table->dropColumn('body_variables');
            }
            if (Schema::hasColumn('otpiq_templates', 'header_image')) {
                $table->dropColumn('header_image');
            }
            if (Schema::hasColumn('otpiq_templates', 'active')) {
                $table->dropColumn('active');
            }
        });
    }

    public function down(): void
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

        if (! Schema::hasTable('otpiq_templates')) {
            return;
        }

        Schema::table('otpiq_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('otpiq_templates', 'body_variables')) {
                $table->json('body_variables')->nullable();
            }
            if (! Schema::hasColumn('otpiq_templates', 'header_image')) {
                $table->boolean('header_image')->default(false);
            }
            if (! Schema::hasColumn('otpiq_templates', 'active')) {
                $table->boolean('active')->default(true);
            }
        });
    }
};

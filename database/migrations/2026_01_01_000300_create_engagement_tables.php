<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads, newsletter, and the campaign QR system.
 *
 * Campaign QR codes are the site's own: staff create one per poster, school
 * visit or social campaign, and every scan is counted and attributed so the
 * registration funnel can answer which channel actually brought people in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();               // contact | exhibit | sponsor
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('role')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();                  // tier interest, booth size, budget
            $table->string('status', 20)->default('new')->index(); // new | contacted | won | closed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('en');
            $table->string('source', 40)->nullable();          // footer | news page | registration
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('qr_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique();              // short code behind /q/{code}
            $table->string('target_url');
            $table->string('medium', 40)->nullable();          // poster | school | social | booth | print
            $table->text('description')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('accent', 20)->default('#B64698');  // magenta for fair, cobalt for conference
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_campaign_id')->constrained()->cascadeOnDelete();
            $table->timestamp('scanned_at')->index();
            $table->string('ip_hash', 64)->nullable();         // hashed: a scan count is not a tracking profile
            $table->string('user_agent', 512)->nullable();
            $table->string('referrer')->nullable();
            $table->boolean('is_unique')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('qr_campaigns');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('leads');
    }
};

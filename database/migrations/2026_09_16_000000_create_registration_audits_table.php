<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who changed what on a registration, and when.
 *
 * The registration desk can edit any record with no restriction — this is the
 * paper trail for that: one row per save that actually changed something,
 * holding only the fields that changed rather than a full before/after dump.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('changes');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_audits');
    }
};

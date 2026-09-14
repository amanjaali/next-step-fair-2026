<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bullet points under the Expo and Conference cards on the home page.
 * Editors add, reorder and translate them in the dashboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_track_points', function (Blueprint $table) {
            $table->id();
            $table->string('track', 20)->index(); // fair | conference
            $table->json('label');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_track_points');
    }
};

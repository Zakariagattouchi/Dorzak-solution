<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 1:1 with stores. Facebook Pixel / GA4 / Meta connect (Settings > Integrations tab). Docs 04. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('facebook_pixel_id', 32)->nullable();
            $table->string('google_analytics_id', 32)->nullable();
            $table->boolean('facebook_connected')->default(false);
            $table->string('facebook_page_name', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};

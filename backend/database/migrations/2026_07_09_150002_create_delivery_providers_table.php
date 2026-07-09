<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-global delivery providers (no store_id — managed by the super admin,
 * shared by every store). Each provider is a distance-pricing formula:
 * fee = max(min_fee, base_fee + per_km_fee × haversine_km), available within
 * max_radius_km of the store. Plan-gated providers ("Dorzak Delivery") require
 * the store's plan to include DELIVERY_SERVICES; open providers serve everyone.
 * Amounts are plain numbers in the store's currency (single-market launch).
 *
 * NOTE: creating the FIRST active provider switches every store from its legacy
 * flat delivery_fee to calculated quotes (DeliveryQuoteService 'flat' mode ends).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->decimal('base_fee', 8, 2)->default(0);
            $table->decimal('per_km_fee', 8, 2)->default(0);
            $table->decimal('min_fee', 8, 2)->default(0);
            $table->decimal('max_radius_km', 6, 2);
            $table->boolean('is_plan_gated')->default(false); // requires DELIVERY_SERVICES
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);        // tie-break for equal quotes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_providers');
    }
};

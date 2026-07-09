<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Delivery-quote snapshot on orders. The provider NAME is denormalised so a
 * deleted provider can't break history (FK nulls out). delivery_fee_status:
 *   QUOTED  — priced by a provider at placement
 *   PENDING — WhatsApp manual-quote flow, merchant must set the fee
 *   SET     — merchant set the fee on a pending order
 *   null    — pickup / dine-in / legacy flat-fee orders
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('delivery_provider_id')->nullable()
                ->constrained('delivery_providers')->nullOnDelete();
            $table->string('delivery_provider_name', 80)->nullable();
            $table->decimal('delivery_distance_km', 6, 2)->nullable();
            $table->string('delivery_fee_status', 12)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('delivery_provider_id');
            $table->dropColumn(['delivery_provider_name', 'delivery_distance_km', 'delivery_fee_status']);
        });
    }
};

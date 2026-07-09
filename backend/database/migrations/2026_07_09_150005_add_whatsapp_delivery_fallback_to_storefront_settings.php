<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-store opt-in for manual WhatsApp delivery quotes: when no courier covers
 * an order (plan not eligible / out of radius / no location), the store can
 * still accept it — the order goes out with the customer's pin and the fee
 * marked PENDING; the merchant sets it in the back office and the customer
 * pays from the public order-status page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            $table->boolean('whatsapp_delivery_fallback')->default(false)->after('whatsapp_ordering_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            $table->dropColumn('whatsapp_delivery_fallback');
        });
    }
};

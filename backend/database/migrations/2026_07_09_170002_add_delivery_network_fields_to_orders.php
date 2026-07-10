<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links an order to the Dorzak delivery network.
 *
 *  delivery_quote_id           the network's quote the customer was charged from.
 *                              A delivery request is only accepted against this.
 *  delivery_provider_code      'dorzak' | 'uber' | 'snoonu' — which carrier.
 *  delivery_external_reference the tender id on the driver board once dispatched.
 *  delivery_external_status    last status the network told us about.
 *  delivery_dispatched_at      when the driver auction was opened.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('delivery_quote_id')->nullable()->after('delivery_provider_name');
            $table->string('delivery_provider_code', 20)->nullable()->after('delivery_quote_id');
            $table->string('delivery_external_reference', 64)->nullable()->after('delivery_provider_code');
            $table->string('delivery_external_status', 40)->nullable()->after('delivery_external_reference');
            $table->timestamp('delivery_dispatched_at')->nullable()->after('delivery_external_status');

            $table->index('delivery_external_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['delivery_external_reference']);
            $table->dropColumn([
                'delivery_quote_id', 'delivery_provider_code',
                'delivery_external_reference', 'delivery_external_status', 'delivery_dispatched_at',
            ]);
        });
    }
};

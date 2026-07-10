<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The pickup point — where the courier collects a delivery order. Snapshotted
 * from the store at placement (not read live) so a store that later moves, or
 * clears its coordinates, can never orphan an in-flight delivery.
 *
 * Together with delivery_latitude/longitude (the drop-off) this gives the
 * delivery system both legs of the trip.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('delivery_longitude');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');
            $table->string('pickup_address', 255)->nullable()->after('pickup_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['pickup_latitude', 'pickup_longitude', 'pickup_address']);
        });
    }
};

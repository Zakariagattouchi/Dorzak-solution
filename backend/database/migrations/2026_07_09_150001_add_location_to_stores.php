<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store origin coordinates — where deliveries start from. Required (by policy)
 * for any store that offers delivery once distance-priced providers exist;
 * nullable at the schema level because existing stores predate the feature.
 * Same decimal(10,7) convention as customers.latitude / orders.delivery_latitude.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('country');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};

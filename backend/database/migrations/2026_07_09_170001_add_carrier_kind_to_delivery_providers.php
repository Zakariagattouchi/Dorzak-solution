<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Carriers split into two kinds:
 *
 *  - network    the Dorzak delivery network. Priced by the delivery system's own
 *               engine (which quotes 20% under the cheapest comparator) and the
 *               ONLY kind that can be dispatched to the driver board.
 *  - comparator third-party carriers (Uber, Snoonu, …). Priced by the local
 *               base + per-km formula; their quotes are sent to the delivery
 *               system so it can undercut them. Never dispatched by us.
 *
 * `code` maps a comparator onto the delivery system's provider vocabulary.
 * Toggling `is_active` turns a carrier on/off platform-wide.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table): void {
            $table->string('code', 20)->nullable()->after('name');
            $table->string('kind', 12)->default('comparator')->after('code');
        });

        // The seeded "Dorzak Delivery" row becomes the network carrier.
        DB::table('delivery_providers')
            ->where('name', 'Dorzak Delivery')
            ->update(['code' => 'dorzak', 'kind' => 'network']);
    }

    public function down(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table): void {
            $table->dropColumn(['code', 'kind']);
        });
    }
};

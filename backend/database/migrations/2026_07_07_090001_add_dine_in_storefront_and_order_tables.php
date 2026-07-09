<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->boolean('allow_dine_in')->default(false)->after('allow_pickup');
            $table->unsignedSmallInteger('dine_in_table_count')->default(0)->after('allow_dine_in');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('table_number')->nullable()->after('fulfillment');
        });

        DB::table('storefront_settings')->where('slug', 'dorzak-merchant')->update([
            'allow_dine_in' => true,
            'dine_in_table_count' => 12,
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('table_number');
        });

        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn(['allow_dine_in', 'dine_in_table_count']);
        });
    }
};

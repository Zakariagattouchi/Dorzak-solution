<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds storefront layout and header visibility preferences so merchants can
 * choose vertical vs horizontal product cards and control the hero header.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->string('product_card_layout', 20)->default('vertical')->after('show_out_of_stock_online');
            $table->boolean('show_store_header')->default(true)->after('product_card_layout');
            $table->boolean('show_store_gradient')->default(true)->after('show_store_header');
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn(['product_card_layout', 'show_store_header', 'show_store_gradient']);
        });
    }
};

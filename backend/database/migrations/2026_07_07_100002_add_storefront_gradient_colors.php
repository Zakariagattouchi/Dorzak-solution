<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds merchant-controlled gradient colors for the storefront hero and category chips.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->char('gradient_start_color', 7)->default('#0f766e')->after('show_store_gradient');
            $table->char('gradient_end_color', 7)->default('#164e63')->after('gradient_start_color');
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn(['gradient_start_color', 'gradient_end_color']);
        });
    }
};

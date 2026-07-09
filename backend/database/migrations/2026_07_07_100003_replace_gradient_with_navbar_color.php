<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the two gradient color columns with a single navbar color so merchants
 * can control the storefront header and cart dock independently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn(['gradient_start_color', 'gradient_end_color']);
            $table->char('navbar_color', 7)->default('#17201e')->after('show_store_gradient');
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn('navbar_color');
            $table->char('gradient_start_color', 7)->default('#0f766e')->after('show_store_gradient');
            $table->char('gradient_end_color', 7)->default('#164e63')->after('gradient_start_color');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1:1 with stores. Public online catalog config (src/pages/storefront/StorefrontPage.tsx).
 * Schema authority: docs 04. NOTE: doc specifies a citext slug with a partial unique
 * index; for pg/sqlite portability we use a plain nullable-unique string and normalize
 * the slug to lowercase in UpdateStorefrontRequest (unique still allows multiple NULLs
 * on both engines).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('online_store_enabled')->default(false);
            $table->string('slug', 40)->nullable()->unique();
            $table->string('bio', 300)->nullable();
            $table->string('banner_path', 255)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->char('accent_color', 7)->default('#1890ff');
            $table->char('secondary_color', 7)->default('#373f4e');
            $table->boolean('allow_delivery')->default(true);
            $table->boolean('allow_pickup')->default(true);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('free_delivery_threshold', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->boolean('whatsapp_ordering_enabled')->default(true);
            $table->boolean('show_out_of_stock_online')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_settings');
    }
};

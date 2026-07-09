<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog products. SKU is unique per store among non-deleted rows — a partial unique
 * index (portable across PostgreSQL + SQLite) so a soft-deleted product's SKU can be
 * reused. Schema authority: docs 04.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('reduced_price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('sku', 64)->nullable();
            $table->string('unit', 12)->default('pcs');
            $table->string('image_path', 255)->nullable();
            $table->string('label_name', 40)->nullable();
            $table->char('label_color', 7)->nullable();
            $table->boolean('taxable')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->boolean('show_in_online_store')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'show_in_online_store']);
            $table->index(['store_id', 'name']);
        });

        // Unique SKU per store among live (non-deleted) rows. Partial indexes are
        // supported by both PostgreSQL and SQLite.
        DB::statement(
            'CREATE UNIQUE INDEX products_store_sku_unique ON products (store_id, sku) WHERE deleted_at IS NULL AND sku IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

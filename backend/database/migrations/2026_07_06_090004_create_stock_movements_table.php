<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only stock ledger. Product.stock is the cached head of this ledger; every
 * change (INITIAL / SALE / CANCEL_RETURN / ADJUSTMENT / RESTOCK) writes a signed row. Docs 04.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('type', 14);
            $table->integer('quantity_change');
            $table->integer('stock_after');
            $table->foreignId('order_id')->nullable(); // FK added in TP-06 when orders exist
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['store_id', 'product_id', 'created_at']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

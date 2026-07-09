<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders (POS sales + online WhatsApp orders). Money and identity are snapshotted:
 * customer name/phone, tax_rate, all totals. No soft deletes — orders are cancelled,
 * not removed. Schema authority: docs 04.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 20);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 32)->nullable();
            $table->string('status', 10);
            $table->string('payment_method', 10);
            $table->string('source', 6);
            $table->string('fulfillment', 8)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('notes', 500)->nullable();
            $table->timestamp('placed_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['store_id', 'order_number']);
            $table->index(['store_id', 'placed_at']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'payment_method']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

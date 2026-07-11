<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premium feature: recurring customer orders. A subscription holds a fixed
 * basket and a cadence; a scheduled job generates a normal order each cycle
 * using the store's existing payment types (no saved-card vault).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->json('items'); // [{product_id, quantity, variant_id?}]
            $table->string('cadence')->default('WEEKLY'); // WEEKLY | MONTHLY
            $table->string('status')->default('active');  // active | paused | cancelled
            $table->timestamp('next_run_at');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_subscriptions');
    }
};

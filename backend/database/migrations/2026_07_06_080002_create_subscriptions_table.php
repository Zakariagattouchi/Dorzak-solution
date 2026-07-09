<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One subscription row per store (Billing page + Settings Subscription tab). Docs 04. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 12)->default('FREE');
            $table->string('status', 12)->default('ACTIVE');
            $table->decimal('price', 8, 2)->default(0);
            $table->string('billing_cycle', 8)->default('monthly');
            $table->timestamp('renews_at')->nullable();
            $table->string('provider', 20)->nullable();
            $table->string('provider_id', 64)->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

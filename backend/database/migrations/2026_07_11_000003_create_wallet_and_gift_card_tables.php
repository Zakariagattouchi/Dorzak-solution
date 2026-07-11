<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premium feature: store-credit wallets + prepaid gift cards. A customer has a
 * wallet balance backed by an append-only ledger; redeeming a gift card credits
 * that wallet, and store credit spends at checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'customer_id']);
        });

        Schema::create('wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2); // signed: + credit, - redemption
            $table->string('reason');
            $table->timestamp('created_at');
        });

        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('active'); // active | redeemed
            $table->foreignId('redeemed_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('wallet_entries');
        Schema::dropIfExists('wallet_accounts');
    }
};

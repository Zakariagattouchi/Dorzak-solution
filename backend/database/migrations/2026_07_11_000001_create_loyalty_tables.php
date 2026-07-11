<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premium feature: points-per-spend loyalty. One config row per store; each
 * customer has a points balance. Points accrue on completed orders and redeem
 * at checkout for a discount.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('earn_points_per_currency')->default(1);
            $table->unsignedInteger('redeem_points')->default(100);
            $table->decimal('redeem_value', 10, 2)->default(5);
            $table->timestamps();

            $table->unique('store_id');
        });

        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('points')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('loyalty_programs');
    }
};

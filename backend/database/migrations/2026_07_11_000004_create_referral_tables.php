<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premium feature: referral program. Each customer has a share code; a new
 * customer's first order placed with it rewards both sides in store credit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->decimal('referrer_reward', 10, 2)->default(15);
            $table->decimal('referee_reward', 10, 2)->default(5);
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('referral_code', 16)->nullable()->after('phone');
            $table->unique(['store_id', 'referral_code']);
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referrer_customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('referred_customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->string('code', 16);
            $table->string('status')->default('pending'); // pending | rewarded
            $table->foreignId('reward_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'referral_code']);
            $table->dropColumn('referral_code');
        });
        Schema::dropIfExists('referral_programs');
    }
};

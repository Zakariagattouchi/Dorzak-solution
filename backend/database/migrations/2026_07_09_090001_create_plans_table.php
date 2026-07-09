<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operator-configurable subscription plans (doc 13 §2). Replaces the hardcoded
 * SubscriptionPlan enum as the source of truth: the platform admin edits rows
 * here to "craft what each plan contains".
 *
 * `code` is a plain unique string normalized to upper-case in the app layer —
 * same pg/sqlite portability choice the storefront slug makes (no citext).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en', 60);
            $table->string('name_ar', 60);
            $table->string('description_en', 255)->nullable();
            $table->string('description_ar', 255)->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('billing_cycle', 8)->default('monthly'); // monthly|yearly
            $table->boolean('is_default')->default(false); // the forever-free signup plan (exactly one)
            $table->boolean('is_active')->default(true);   // inactive = hidden from upgrade UI
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

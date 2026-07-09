<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which capabilities a plan grants (doc 13 §1-2). A row = capability enabled;
 * for limit-kind features (STAFF_SEATS, PRODUCTS_LIMIT) limit_value is the cap
 * (null = unlimited). Composed by the operator in the platform admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('feature', 32); // App\Enums\PlanFeature value
            $table->integer('limit_value')->nullable(); // only meaningful for limit-kind features
            $table->timestamps();

            $table->unique(['plan_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customer CRM. phone_normalized (digits only) is unique per store among live rows
 * via a partial index (pg + sqlite). total_orders / total_spent are cached counters
 * maintained by order events (TP-06). Schema authority: docs 04.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->string('phone', 32);
            $table->string('phone_normalized', 32);
            $table->string('address', 190)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('tax_id', 64)->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'name']);
            $table->index(['store_id', 'total_spent']);
        });

        DB::statement(
            'CREATE UNIQUE INDEX customers_store_phone_unique ON customers (store_id, phone_normalized) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

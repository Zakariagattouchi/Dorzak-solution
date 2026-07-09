<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant root. Holds the store identity + contact + locale + tax config that the
 * frontend surfaces as `accountInfo` (src/data/mockData.ts) across the Settings tabs.
 * The three 1:1 settings tables (storefront/receipt/integration) arrive in TP-02.
 * Schema authority: docs/backend-planning/04-database-schema.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('tagline', 160)->nullable();
            $table->string('owner_name', 120)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('address', 190)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 80)->default('United States');
            $table->string('timezone', 64)->default('UTC');
            $table->string('language', 2)->default('en');
            $table->char('currency', 3)->default('USD');
            $table->string('symbol_placement', 6)->default('BEFORE');
            $table->boolean('charge_sales_tax')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_id', 64)->nullable();
            $table->boolean('tax_included_in_price')->default(false);
            $table->json('accepted_payment_methods')->default(json_encode(['CASH', 'CARD', 'TRANSFER', 'WHATSAPP']));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};

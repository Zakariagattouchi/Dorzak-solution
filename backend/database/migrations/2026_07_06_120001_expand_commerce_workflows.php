<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar', 160)->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
            $table->json('variant_groups')->nullable()->after('is_active');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('option_values')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('sku');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 24)->default('UNPAID')->after('payment_method');
            $table->char('currency_code', 3)->default('QAR')->after('payment_status');
            $table->string('delivery_address', 190)->nullable()->after('fulfillment');
            $table->string('delivery_city', 80)->nullable()->after('delivery_address');
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_city');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            $table->string('payment_reference', 120)->nullable()->after('notes');
            $table->string('payment_proof_path', 255)->nullable()->after('payment_reference');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 24)->change();
        });

        DB::table('orders')->where('status', 'PENDING')->update([
            'status' => 'CONFIRMING',
            'payment_status' => 'UNPAID',
        ]);
        DB::table('orders')->where('status', 'COMPLETED')->update([
            'status' => 'COMPLETE',
            'payment_status' => 'PAID',
        ]);

        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->boolean('fawran_enabled')->default(false)->after('whatsapp_ordering_enabled');
            $table->string('fawran_alias', 80)->nullable()->after('fawran_enabled');
            $table->string('fawran_mobile', 32)->nullable()->after('fawran_alias');
            $table->string('fawran_iban', 34)->nullable()->after('fawran_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $table->dropColumn(['fawran_enabled', 'fawran_alias', 'fawran_mobile', 'fawran_iban']);
        });

        DB::table('orders')->where('status', 'CONFIRMING')->update(['status' => 'PENDING']);
        DB::table('orders')->where('status', 'COMPLETE')->update(['status' => 'COMPLETED']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status', 'currency_code', 'delivery_address', 'delivery_city',
                'delivery_latitude', 'delivery_longitude', 'payment_reference', 'payment_proof_path',
            ]);
            $table->string('status', 10)->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['option_values', 'is_active']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar', 'variant_groups']);
        });
    }
};

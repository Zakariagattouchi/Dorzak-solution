<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_path', 255)->nullable()->after('color');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('address_details', 300)->nullable()->after('address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_address_details', 300)->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('delivery_address_details'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('address_details'));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('image_path'));
    }
};

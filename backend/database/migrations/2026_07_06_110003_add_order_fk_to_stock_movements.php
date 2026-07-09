<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Now that orders exist, constrain stock_movements.order_id. Docs 04. */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot add FK constraints to an existing column; the index already
        // exists and the app always sets a valid order_id, so this is a no-op there.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
    }
};

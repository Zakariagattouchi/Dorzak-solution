<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 1:1 with stores. Receipt header/footer + print toggles (Settings > Receipts tab). Docs 04. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('header', 160)->nullable();
            $table->string('footer', 160)->nullable();
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_address')->default(true);
            $table->boolean('show_tax')->default(true);
            $table->boolean('auto_print')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};

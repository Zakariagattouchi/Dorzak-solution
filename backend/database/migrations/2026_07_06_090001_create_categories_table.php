<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Product categories (POS chips + online catalog). No soft deletes: delete nulls products. Docs 04. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->char('color', 7)->default('#3b82f6');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'name']);
            $table->index(['store_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

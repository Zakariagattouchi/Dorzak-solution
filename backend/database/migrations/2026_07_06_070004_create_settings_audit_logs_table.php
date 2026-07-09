<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit trail for money-affecting settings changes (tax, payments,
 * storefront fees, ...). One row per settings-group write. Docs 04 / 12 §8.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group', 20);
            $table->json('old_values');
            $table->json('new_values');
            $table->timestamp('created_at')->nullable();

            $table->index(['store_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_audit_logs');
    }
};

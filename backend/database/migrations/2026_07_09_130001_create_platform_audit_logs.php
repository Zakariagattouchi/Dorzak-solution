<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only trail of platform (super-admin) actions: impersonation, store
 * suspend/delete, plan overrides, user admin grants, password resets. Every
 * privileged cross-tenant action writes one row here so operator activity is
 * always accountable. See doc 13 (platform layer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);            // e.g. store.impersonate, user.grant_admin
            $table->string('target_type', 20)->nullable(); // 'store' | 'user'
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_label')->nullable();     // denormalised name/email for readability after deletes
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['action', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};

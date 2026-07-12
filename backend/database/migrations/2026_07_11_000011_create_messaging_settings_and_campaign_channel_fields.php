<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real messaging channels. Per-store WhatsApp Business API credentials and an
 * email sender identity (with optional custom SMTP) live in messaging_settings;
 * campaigns gain WhatsApp template fields (Meta requires approved templates for
 * out-of-session marketing sends) and an honest failed_count.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messaging_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            // Email sender identity + optional custom SMTP transport.
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();   // encrypted cast
            $table->string('smtp_encryption')->nullable(); // tls | ssl | null
            // WhatsApp Business Cloud API.
            $table->text('whatsapp_token')->nullable();  // encrypted cast
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('whatsapp_display_number')->nullable();
            $table->timestamp('whatsapp_connected_at')->nullable();
            $table->string('whatsapp_error')->nullable();
            $table->timestamps();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('wa_template_name')->nullable()->after('channel');
            $table->string('wa_template_language', 10)->nullable()->after('wa_template_name');
            $table->unsignedInteger('failed_count')->default(0)->after('sent_count');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['wa_template_name', 'wa_template_language', 'failed_count']);
        });
        Schema::dropIfExists('messaging_settings');
    }
};

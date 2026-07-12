<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-store messaging channel configuration: the email sender identity
 * (optionally with its own SMTP transport) and WhatsApp Business Cloud API
 * credentials. Secrets are encrypted at rest and never round-trip to the UI.
 */
class MessagingSetting extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'email_from_name', 'email_from_address',
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
        'whatsapp_token', 'whatsapp_phone_number_id', 'whatsapp_display_number',
        'whatsapp_connected_at', 'whatsapp_error',
    ];

    protected function casts(): array
    {
        return [
            'smtp_port' => 'integer',
            'smtp_password' => 'encrypted',
            'whatsapp_token' => 'encrypted',
            'whatsapp_connected_at' => 'datetime',
        ];
    }

    public function emailReady(): bool
    {
        return ! empty($this->email_from_address);
    }

    public function whatsappReady(): bool
    {
        return ! empty($this->whatsapp_token)
            && ! empty($this->whatsapp_phone_number_id)
            && $this->whatsapp_connected_at !== null;
    }
}

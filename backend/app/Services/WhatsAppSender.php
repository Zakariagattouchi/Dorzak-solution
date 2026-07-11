<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends WhatsApp messages via the Meta WhatsApp Cloud API. Inert until
 * credentials are configured (WHATSAPP_TOKEN + WHATSAPP_PHONE_NUMBER_ID): it
 * logs the message and reports success so campaigns still record reachable
 * recipients, and starts really sending the moment the account is connected —
 * zero code change. Mirrors the graceful-degradation pattern used elsewhere.
 */
class WhatsAppSender
{
    public function configured(): bool
    {
        return ! empty(config('services.whatsapp.token'))
            && ! empty(config('services.whatsapp.phone_number_id'));
    }

    public function send(string $toPhone, string $body): bool
    {
        if (! $this->configured()) {
            Log::info('[whatsapp:unconfigured] would send', ['to' => $toPhone, 'body' => $body]);

            return true;
        }

        $phoneId = config('services.whatsapp.phone_number_id');

        $response = Http::withToken(config('services.whatsapp.token'))
            ->post("https://graph.facebook.com/v20.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D/', '', $toPhone),
                'type' => 'text',
                'text' => ['body' => $body],
            ]);

        return $response->successful();
    }
}

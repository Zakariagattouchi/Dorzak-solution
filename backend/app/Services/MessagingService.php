<?php

namespace App\Services;

use App\Mail\TestChannelMail;
use App\Models\Campaign;
use App\Models\MessagingSetting;
use App\Models\Store;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * The single gateway to a store's outbound channels. Email sends from the
 * store's configured identity (through its own SMTP when set, else the
 * platform mailer); WhatsApp sends through the store's own Business Cloud API
 * number. Every send returns an honest boolean — an unconfigured channel is
 * unusable, never silently "logged".
 */
class MessagingService
{
    public function settings(Store $store): MessagingSetting
    {
        return MessagingSetting::firstOrCreate(['store_id' => $store->id]);
    }

    /** @return array{email: array<string,mixed>, whatsapp: array<string,mixed>} */
    public function status(Store $store): array
    {
        $s = $this->settings($store);

        return [
            'email' => [
                'ready' => $s->emailReady(),
                'mode' => ! $s->emailReady() ? 'unconfigured' : ($s->smtp_host ? 'smtp' : 'platform'),
                'from_name' => $s->email_from_name,
                'from_address' => $s->email_from_address,
            ],
            'whatsapp' => [
                'ready' => $s->whatsappReady(),
                'display_number' => $s->whatsapp_display_number,
                'error' => $s->whatsapp_error,
            ],
        ];
    }

    /**
     * Confirm the WhatsApp credentials against the Graph API phone-number
     * endpoint, recording the verified display number (or the error).
     *
     * @return array{connected: bool, display_phone_number?: string, error?: string}
     */
    public function verifyWhatsapp(Store $store): array
    {
        $s = $this->settings($store);

        if (empty($s->whatsapp_token) || empty($s->whatsapp_phone_number_id)) {
            $s->update(['whatsapp_connected_at' => null, 'whatsapp_error' => 'Token and phone number ID are required.']);

            return ['connected' => false, 'error' => 'Token and phone number ID are required.'];
        }

        $response = Http::withToken($s->whatsapp_token)
            ->get("https://graph.facebook.com/v20.0/{$s->whatsapp_phone_number_id}", ['fields' => 'display_phone_number,verified_name']);

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? 'Could not reach the WhatsApp API.';
            $s->update(['whatsapp_connected_at' => null, 'whatsapp_error' => $error]);

            return ['connected' => false, 'error' => $error];
        }

        $s->update([
            'whatsapp_connected_at' => now(),
            'whatsapp_display_number' => $response->json('display_phone_number'),
            'whatsapp_error' => null,
        ]);

        return ['connected' => true, 'display_phone_number' => $response->json('display_phone_number')];
    }

    /**
     * Send one WhatsApp message through the store's own number. Uses an
     * approved template when the campaign names one (required by Meta for
     * out-of-session marketing sends); free text otherwise (24h session only).
     */
    public function sendWhatsapp(Store $store, string $toPhone, Campaign $campaign): bool
    {
        $s = $this->settings($store);

        if (! $s->whatsappReady()) {
            return false;
        }

        $payload = $campaign->wa_template_name
            ? [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D/', '', $toPhone),
                'type' => 'template',
                'template' => ['name' => $campaign->wa_template_name, 'language' => ['code' => $campaign->wa_template_language ?: 'en']],
            ]
            : [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D/', '', $toPhone),
                'type' => 'text',
                'text' => ['body' => $campaign->subject.' — '.$campaign->body],
            ];

        return Http::withToken($s->whatsapp_token)
            ->post("https://graph.facebook.com/v20.0/{$s->whatsapp_phone_number_id}/messages", $payload)
            ->successful();
    }

    /**
     * The mailer + from-identity for a store's outbound email. When the store
     * has its own SMTP transport we register it as a runtime mailer; otherwise
     * the platform mailer carries the store's from-identity.
     *
     * @return array{mailer: string, from_address: string, from_name: ?string}|null null when email is unconfigured
     */
    public function emailChannel(Store $store): ?array
    {
        $s = $this->settings($store);

        if (! $s->emailReady()) {
            return null;
        }

        $mailer = config('mail.default');

        if ($s->smtp_host) {
            $mailer = 'store_smtp_'.$store->id;
            config(["mail.mailers.{$mailer}" => [
                'transport' => 'smtp',
                'host' => $s->smtp_host,
                'port' => $s->smtp_port ?? 587,
                'username' => $s->smtp_username,
                'password' => $s->smtp_password,
                'encryption' => $s->smtp_encryption,
                'timeout' => 15,
            ]]);
        }

        return ['mailer' => $mailer, 'from_address' => $s->email_from_address, 'from_name' => $s->email_from_name];
    }

    /** Send a test email to prove the channel end-to-end. */
    public function sendTestEmail(Store $store, string $to): bool
    {
        $channel = $this->emailChannel($store);

        if ($channel === null) {
            return false;
        }

        Mail::mailer($channel['mailer'])->to($to)->send(
            new TestChannelMail($store->name, $channel['from_address'], $channel['from_name']),
        );

        return true;
    }
}

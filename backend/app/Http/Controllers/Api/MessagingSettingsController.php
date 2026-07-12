<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MessagingService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Per-store messaging channel setup: the email sender identity (with optional
 * custom SMTP) and the WhatsApp Business Cloud API connection. Secrets are
 * write-only — reads expose only whether one is stored.
 */
class MessagingSettingsController extends Controller
{
    public function __construct(
        private readonly StoreContext $context,
        private readonly MessagingService $messaging,
    ) {}

    public function show(): JsonResponse
    {
        $s = $this->messaging->settings($this->context->store());

        return response()->json([
            'messaging' => [
                'email_from_name' => $s->email_from_name,
                'email_from_address' => $s->email_from_address,
                'smtp_host' => $s->smtp_host,
                'smtp_port' => $s->smtp_port,
                'smtp_username' => $s->smtp_username,
                'smtp_password' => null,
                'has_smtp_password' => ! empty($s->smtp_password),
                'smtp_encryption' => $s->smtp_encryption,
                'whatsapp_token' => null,
                'has_whatsapp_token' => ! empty($s->whatsapp_token),
                'whatsapp_phone_number_id' => $s->whatsapp_phone_number_id,
                'whatsapp_display_number' => $s->whatsapp_display_number,
                'whatsapp_connected' => $s->whatsappReady(),
                'whatsapp_error' => $s->whatsapp_error,
            ],
            'status' => $this->messaging->status($this->context->store()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'email_from_name' => ['nullable', 'string', 'max:120'],
            'email_from_address' => ['nullable', 'email', 'max:190'],
            'smtp_host' => ['nullable', 'string', 'max:190'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:190'],
            'smtp_password' => ['nullable', 'string', 'max:190'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl'],
            'whatsapp_token' => ['nullable', 'string', 'max:500'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:60'],
        ]);

        $s = $this->messaging->settings($this->context->store());

        // Blank secrets mean "keep the stored one" — the UI never sees them.
        foreach (['smtp_password', 'whatsapp_token'] as $secret) {
            if (! array_key_exists($secret, $data) || $data[$secret] === null || $data[$secret] === '') {
                unset($data[$secret]);
            }
        }

        // Changing the WhatsApp credentials invalidates the previous verification.
        if (isset($data['whatsapp_token']) || (isset($data['whatsapp_phone_number_id']) && $data['whatsapp_phone_number_id'] !== $s->whatsapp_phone_number_id)) {
            $data['whatsapp_connected_at'] = null;
            $data['whatsapp_error'] = null;
            $data['whatsapp_display_number'] = null;
        }

        $s->update($data);

        return $this->show();
    }

    /** Prove the WhatsApp credentials against the Graph API. */
    public function verifyWhatsapp(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $result = $this->messaging->verifyWhatsapp($this->context->store());

        return response()->json($result, $result['connected'] ? 200 : 422);
    }

    /** Prove the email channel by sending a test message to the requester. */
    public function testEmail(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $ok = $this->messaging->sendTestEmail($this->context->store(), $request->user()->email);

        abort_unless($ok, 422, 'Set a sender address first.');

        return response()->json(['ok' => true, 'to' => $request->user()->email]);
    }
}

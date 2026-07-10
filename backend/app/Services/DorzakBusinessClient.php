<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The only boundary between Dorzak Merchant and the Dorzak Business delivery
 * system. Owns short-lived client-credentials tokens, exact-body HMAC signing
 * and idempotency keys. Mirrors the main-Dorzak client so both apps speak the
 * same integration contract.
 *
 * Pricing note: the delivery system quotes 20% below the cheapest comparator
 * we send it. That undercut lives THERE — we only supply Uber/Snoonu prices
 * and use the `customer_price_minor` it returns. A delivery request is only
 * accepted against a live `quote_id`, so a locally-invented price can never be
 * dispatched.
 */
class DorzakBusinessClient
{
    public function enabled(): bool
    {
        return (bool) config('delivery.business.enabled')
            && filled(config('delivery.business.client_id'))
            && filled(config('delivery.business.client_secret'));
    }

    /** POST a trip + comparator prices; returns quote_id, customer_price_minor, expires_at. */
    public function quote(array $payload): array
    {
        return $this->signed('POST', 'api/integration/quotes', $payload);
    }

    public function getQuote(int|string $quoteId): array
    {
        $response = $this->request()
            ->withToken($this->token())
            ->get($this->url("api/integration/quotes/{$quoteId}"));

        return $this->successfulJson($response, 'Unable to load the Dorzak Delivery quote.');
    }

    /** Opens the driver auction — this is what appears on the live board. */
    public function createDelivery(Order $order, ?int $codAmountMinor = null): array
    {
        return $this->signed('POST', 'api/integration/delivery-requests', [
            'quote_id' => (int) $order->delivery_quote_id,
            'source_order_id' => (string) $order->id,
            'dorzak_order_number' => (string) $order->order_number,
            'source_system' => (string) config('delivery.business.source_system'),
            'cod_amount_minor' => $codAmountMinor,
            'vendor_latitude' => $order->pickup_latitude !== null ? (float) $order->pickup_latitude : null,
            'vendor_longitude' => $order->pickup_longitude !== null ? (float) $order->pickup_longitude : null,
        ], "delivery-create:{$order->id}:{$order->delivery_quote_id}");
    }

    public function cancelDelivery(Order $order): array
    {
        return $this->signed(
            'POST',
            "api/integration/delivery-requests/{$order->id}/cancel",
            ['source_system' => (string) config('delivery.business.source_system')],
            "delivery-cancel:{$order->id}",
        );
    }

    private function signed(string $method, string $path, array $payload, ?string $idempotencyKey = null): array
    {
        $body = json_encode($payload);
        $timestamp = now()->timestamp;

        $headers = [
            'Authorization' => 'Bearer '.$this->token(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Dorzak-Timestamp' => (string) $timestamp,
            'X-Dorzak-Signature' => self::sign(
                (string) config('delivery.business.client_secret'),
                $method,
                $path,
                $body,
                $timestamp,
            ),
        ];

        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $response = $this->request()
            ->withHeaders($headers)
            ->withBody($body, 'application/json')
            ->send($method, $this->url($path));

        return $this->successfulJson($response, 'Dorzak Business rejected the integration request.');
    }

    private function token(): string
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Dorzak Business integration is not configured.');
        }

        $cacheKey = 'dorzak-business-token:'.sha1((string) config('delivery.business.client_id'));

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = $this->request()->post($this->url('api/integration/oauth/token'), [
                'grant_type' => 'client_credentials',
                'client_id' => config('delivery.business.client_id'),
                'client_secret' => config('delivery.business.client_secret'),
            ]);

            $data = $this->successfulJson($response, 'Unable to authenticate with Dorzak Business.');

            if (blank($data['access_token'] ?? null)) {
                throw new RuntimeException('Dorzak Business returned no access token.');
            }

            return (string) $data['access_token'];
        });
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout((int) config('delivery.business.timeout', 8))
            ->connectTimeout(3);
    }

    private function successfulJson($response, string $message): array
    {
        if (! $response->successful()) {
            throw new RuntimeException($response->json('message') ?: "{$message} HTTP {$response->status()}");
        }

        return (array) $response->json();
    }

    private function url(string $path): string
    {
        return rtrim((string) config('delivery.business.base_url'), '/').'/'.ltrim($path, '/');
    }

    public static function sign(string $secret, string $method, string $path, string $body, int $timestamp): string
    {
        return hash_hmac('sha256', "{$timestamp}.{$method}.{$path}.{$body}", $secret);
    }
}

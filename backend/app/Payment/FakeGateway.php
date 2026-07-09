<?php

namespace App\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;

/**
 * In-memory fake used in tests and local development.
 *
 * Behaviour:
 * - createSubscription: returns a deterministic redirect URL, records the call.
 * - cancel: immediately marks the subscription as CANCELLED in the local DB.
 * - webhook: parses a signed-fake-payload so tests can trigger lifecycle events.
 *
 * Usage in tests:
 *   $gateway = new FakeGateway();
 *   $this->app->instance(PaymentGateway::class, $gateway);
 *   // … exercise controller … //
 *   $gateway->assertSubscriptionCreated($subscription->id);
 */
final class FakeGateway implements PaymentGateway
{
    private array $subscriptionsCalled = [];

    private array $cancelsCalled = [];

    public function createSubscription(Subscription $subscription, Plan $plan): string
    {
        $this->subscriptionsCalled[] = $subscription->id;

        return "https://fake-gateway.test/pay/{$subscription->id}?plan={$plan->code}";
    }

    public function cancel(Subscription $subscription, bool $immediately = false): string
    {
        $this->cancelsCalled[] = $subscription->id;

        return 'CANCELLED';
    }

    public function webhook(Request $request): GatewayEvent
    {
        $payload = $request->json()->all();

        if (($payload['_fake_sig'] ?? '') !== config('payment.fake_webhook_secret', 'fake-secret')) {
            throw new \InvalidArgumentException('Fake gateway: bad webhook signature.');
        }

        return new GatewayEvent(
            type: $payload['type'] ?? 'subscription.activated',
            providerId: $payload['provider_id'] ?? 'fake-'.$payload['subscription_id'],
            payload: $payload,
        );
    }

    // ─── Test assertions ─────────────────────────────────────────────────────

    public function assertSubscriptionCreated(int $subscriptionId): void
    {
        Assert::assertContains(
            $subscriptionId,
            $this->subscriptionsCalled,
            "Expected createSubscription() to be called with subscription #{$subscriptionId}.",
        );
    }

    public function assertCancelled(int $subscriptionId): void
    {
        Assert::assertContains(
            $subscriptionId,
            $this->cancelsCalled,
            "Expected cancel() to be called with subscription #{$subscriptionId}.",
        );
    }

    public function assertNothingCalled(): void
    {
        Assert::assertEmpty($this->subscriptionsCalled, 'Expected no subscriptions to be created.');
        Assert::assertEmpty($this->cancelsCalled, 'Expected no cancellations.');
    }
}

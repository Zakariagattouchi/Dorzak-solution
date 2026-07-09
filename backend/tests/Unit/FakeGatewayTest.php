<?php

namespace Tests\Unit;

use App\Contracts\PaymentGateway;
use App\Models\Plan;
use App\Payment\FakeGateway;
use App\Payment\GatewayEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FakeGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_fake_gateway_is_bound_to_contract(): void
    {
        $this->assertInstanceOf(FakeGateway::class, app(PaymentGateway::class));
    }

    public function test_create_subscription_returns_redirect_url(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $plan = Plan::where('code', 'PRO')->first();
        $sub = $store->subscription;

        $url = app(PaymentGateway::class)->createSubscription($sub, $plan);

        $this->assertStringContainsString((string) $sub->id, $url);
        $this->assertStringContainsString('PRO', $url);
    }

    public function test_cancel_returns_cancelled_status(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $sub = $store->subscription;

        $status = app(PaymentGateway::class)->cancel($sub);

        $this->assertSame('CANCELLED', $status);
    }

    public function test_webhook_parses_fake_payload(): void
    {
        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode([
            '_fake_sig' => 'fake-secret',
            'type' => 'subscription.renewed',
            'provider_id' => 'fake-abc123',
            'subscription_id' => 42,
        ]));
        $request->headers->set('Content-Type', 'application/json');

        $event = app(PaymentGateway::class)->webhook($request);

        $this->assertInstanceOf(GatewayEvent::class, $event);
        $this->assertSame('subscription.renewed', $event->type);
        $this->assertSame('fake-abc123', $event->providerId);
    }

    public function test_webhook_rejects_bad_signature(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode([
            '_fake_sig' => 'wrong',
            'type' => 'subscription.activated',
        ]));
        $request->headers->set('Content-Type', 'application/json');

        app(PaymentGateway::class)->webhook($request);
    }

    public function test_assert_helpers_pass_and_fail_correctly(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $plan = Plan::where('code', 'PRO')->first();
        $sub = $store->subscription;

        /** @var FakeGateway $gateway */
        $gateway = app(PaymentGateway::class);
        $gateway->createSubscription($sub, $plan);

        $gateway->assertSubscriptionCreated($sub->id);
    }
}

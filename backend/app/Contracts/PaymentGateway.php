<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\Subscription;
use App\Payment\GatewayEvent;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Create a subscription checkout session and return a redirect URL.
     * The caller redirects the merchant to this URL to complete payment.
     */
    public function createSubscription(Subscription $subscription, Plan $plan): string;

    /**
     * Cancel an active subscription immediately or at period end.
     * Returns the updated subscription status string.
     */
    public function cancel(Subscription $subscription, bool $immediately = false): string;

    /**
     * Parse and validate an inbound webhook request into a GatewayEvent.
     * Throws \InvalidArgumentException on signature failure.
     */
    public function webhook(Request $request): GatewayEvent;
}

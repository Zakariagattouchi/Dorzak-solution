<?php

namespace App\Payment;

/**
 * Normalised event emitted by PaymentGateway::webhook().
 *
 * The gateway implementation maps provider-specific event types onto these
 * canonical types. Consumers switch on $event->type and read $event->payload
 * for gateway-specific details.
 */
final class GatewayEvent
{
    public function __construct(
        /** One of: subscription.activated, subscription.renewed, subscription.past_due, subscription.cancelled */
        public readonly string $type,
        /** The provider_id stored on the Subscription model (e.g. Dibsy charge ID). */
        public readonly string $providerId,
        /** Raw gateway payload for audit logging. */
        public readonly array $payload,
    ) {}
}

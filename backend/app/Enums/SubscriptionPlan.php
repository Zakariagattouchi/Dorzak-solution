<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case FREE = 'FREE';
    case PRO = 'PRO';
    case ENTERPRISE = 'ENTERPRISE';

    /** Feature bullets shown on the Billing / Subscription screens. */
    public function features(): array
    {
        return match ($this) {
            self::FREE => ['POS Checkout & Orders', 'Basic Catalog'],
            self::PRO => [
                'Unlimited Products', 'Online Catalog', 'WhatsApp Ordering',
                'Multiple Staff Users', 'Advanced Analytics', 'Priority Support',
            ],
            self::ENTERPRISE => [
                'Everything in Pro', 'Multi-store sync', 'Custom API webhooks',
                'Dedicated account manager',
            ],
        };
    }
}

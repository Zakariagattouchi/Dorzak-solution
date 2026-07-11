<?php

namespace App\Enums;

/**
 * The capability catalog — the fixed set of things a plan can unlock or cap.
 * Plans are composed from these in the `plan_features` table (see doc 13 §1),
 * so *which* plan grants *what* is data the operator edits in the platform
 * admin; adding a brand-new capability is a new case here plus (for enforced
 * ones) one enforcement point.
 *
 * Two kinds:
 *  - toggle — access is on/off (a plan_features row means "on"; no row = off)
 *  - limit  — access is always on but capped (row's limit_value = cap; no row
 *             or null value = unlimited)
 *
 * `descriptor()` carries the human metadata (label/description/group) and an
 * `enforced` flag: true = the server hard-gates it today; false = it's a
 * composable plan attribute the UI/marketing respects but no server gate bites
 * yet (a future packet wires it). The plan editor shows this honestly.
 */
enum PlanFeature: string
{
    // Selling channels
    case POS_ACCESS = 'POS_ACCESS';                 // ring up in-person sales (Sell screen)
    case ONLINE_ORDERING = 'ONLINE_ORDERING';       // public storefront order placement
    case DELIVERY_SERVICES = 'DELIVERY_SERVICES';   // delivery option at checkout
    case DINE_IN = 'DINE_IN';                        // table QR dine-in ordering
    case WHATSAPP_ORDERING = 'WHATSAPP_ORDERING';   // send-cart-over-WhatsApp

    // Storefront
    case BRANDED_STOREFRONT = 'BRANDED_STOREFRONT'; // slug / subdomain storefront (vs anonymous menu)
    case CUSTOM_DOMAIN = 'CUSTOM_DOMAIN';           // point your own domain (deferred)

    // Growth
    case CUSTOMER_CRM = 'CUSTOMER_CRM';             // customer directory + segmentation
    case ADVANCED_REPORTS = 'ADVANCED_REPORTS';     // analytics dashboards + finance export
    case LOYALTY = 'LOYALTY';                       // points-per-spend loyalty program
    case COUPONS = 'COUPONS';                       // customer-facing discount codes
    case GIFT_CARDS = 'GIFT_CARDS';                 // prepaid gift cards → store credit
    case REFERRALS = 'REFERRALS';                   // refer-a-friend rewards
    case SEGMENTS = 'SEGMENTS';                     // saved rule-based customer segments
    case CAMPAIGNS = 'CAMPAIGNS';                   // scheduled marketing campaigns

    // Limits
    case PRODUCTS_LIMIT = 'PRODUCTS_LIMIT';         // products per store
    case CATEGORIES_LIMIT = 'CATEGORIES_LIMIT';     // categories per store
    case PRODUCT_IMAGES_LIMIT = 'PRODUCT_IMAGES_LIMIT'; // extra photos per product
    case STAFF_SEATS = 'STAFF_SEATS';               // active members + pending invites

    /** Limit-kind features cap a quantity; toggle features gate access. */
    public function isLimit(): bool
    {
        return match ($this) {
            self::PRODUCTS_LIMIT, self::CATEGORIES_LIMIT,
            self::PRODUCT_IMAGES_LIMIT, self::STAFF_SEATS => true,
            default => false,
        };
    }

    public function kind(): string
    {
        return $this->isLimit() ? 'limit' : 'toggle';
    }

    /**
     * @return array{label:string, description:string, group:string, enforced:bool, unit:?string}
     */
    public function descriptor(): array
    {
        return match ($this) {
            self::POS_ACCESS => ['label' => 'POS register', 'description' => 'Ring up in-person sales on the Sell screen.', 'group' => 'Selling channels', 'enforced' => true, 'unit' => null],
            self::ONLINE_ORDERING => ['label' => 'Online ordering', 'description' => 'Accept orders from the public storefront.', 'group' => 'Selling channels', 'enforced' => true, 'unit' => null],
            self::DELIVERY_SERVICES => ['label' => 'Delivery', 'description' => 'Access to plan-gated delivery providers (e.g. Dorzak Delivery) at checkout.', 'group' => 'Selling channels', 'enforced' => true, 'unit' => null],
            self::DINE_IN => ['label' => 'Dine-in QR ordering', 'description' => 'Table QR codes for dine-in orders.', 'group' => 'Selling channels', 'enforced' => false, 'unit' => null],
            self::WHATSAPP_ORDERING => ['label' => 'WhatsApp ordering', 'description' => 'Let customers send their cart over WhatsApp.', 'group' => 'Selling channels', 'enforced' => false, 'unit' => null],
            self::BRANDED_STOREFRONT => ['label' => 'Branded storefront', 'description' => 'Your own storefront slug/subdomain instead of the anonymous menu.', 'group' => 'Storefront', 'enforced' => true, 'unit' => null],
            self::CUSTOM_DOMAIN => ['label' => 'Custom domain', 'description' => 'Point your own domain at your storefront.', 'group' => 'Storefront', 'enforced' => false, 'unit' => null],
            self::CUSTOMER_CRM => ['label' => 'Customer directory', 'description' => 'Store and segment customer profiles.', 'group' => 'Growth', 'enforced' => false, 'unit' => null],
            self::ADVANCED_REPORTS => ['label' => 'Advanced reports', 'description' => 'Analytics dashboards and finance exports.', 'group' => 'Growth', 'enforced' => false, 'unit' => null],
            self::LOYALTY => ['label' => 'Loyalty program', 'description' => 'Reward repeat customers with points they redeem for discounts.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::COUPONS => ['label' => 'Coupon codes', 'description' => 'Create discount codes customers apply at checkout.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::GIFT_CARDS => ['label' => 'Gift cards', 'description' => 'Sell prepaid gift cards redeemable as store credit.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::REFERRALS => ['label' => 'Referral program', 'description' => 'Reward customers who refer friends with store credit.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::SEGMENTS => ['label' => 'Customer segments', 'description' => 'Save rule-based customer segments to target campaigns.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::CAMPAIGNS => ['label' => 'Marketing campaigns', 'description' => 'Schedule email campaigns to all customers or a segment.', 'group' => 'Growth', 'enforced' => true, 'unit' => null],
            self::PRODUCTS_LIMIT => ['label' => 'Products', 'description' => 'Maximum products in the catalogue.', 'group' => 'Catalogue limits', 'enforced' => true, 'unit' => 'products'],
            self::CATEGORIES_LIMIT => ['label' => 'Categories', 'description' => 'Maximum categories.', 'group' => 'Catalogue limits', 'enforced' => true, 'unit' => 'categories'],
            self::PRODUCT_IMAGES_LIMIT => ['label' => 'Extra photos per product', 'description' => 'Additional photos allowed on each product beyond the main image.', 'group' => 'Catalogue limits', 'enforced' => true, 'unit' => 'photos'],
            self::STAFF_SEATS => ['label' => 'Staff seats', 'description' => 'Team members plus pending invites.', 'group' => 'Team', 'enforced' => true, 'unit' => 'seats'],
        };
    }

    /**
     * The full catalogue for the plan editor: every capability with its kind and
     * human metadata, in display order.
     *
     * @return list<array{key:string, kind:string, label:string, description:string, group:string, enforced:bool, unit:?string}>
     */
    public static function catalog(): array
    {
        return array_map(
            fn (self $f) => array_merge(['key' => $f->value, 'kind' => $f->kind()], $f->descriptor()),
            self::cases(),
        );
    }
}

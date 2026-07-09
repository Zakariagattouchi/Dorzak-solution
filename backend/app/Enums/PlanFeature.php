<?php

namespace App\Enums;

/**
 * The capability catalog — the fixed set of things a plan can unlock or cap.
 * Plans are composed from these in the `plan_features` table (see doc 13 §1),
 * so *which* plan grants *what* is data the operator edits; adding a brand-new
 * capability is a new case here plus one enforcement point.
 *
 * Two kinds:
 *  - boolean  — access is on/off (a plan_features row means "on"; no row = off)
 *  - limit    — access is always on but capped (row's limit_value = cap; no row
 *               or null value = unlimited)
 */
enum PlanFeature: string
{
    // boolean capabilities
    case ONLINE_ORDERING = 'ONLINE_ORDERING';       // public order placement + customer lookup
    case BRANDED_STOREFRONT = 'BRANDED_STOREFRONT'; // slug / subdomain storefront (vs anonymous menu)
    case CUSTOM_DOMAIN = 'CUSTOM_DOMAIN';           // point your own domain (deferred; key reserved)
    case DELIVERY_SERVICES = 'DELIVERY_SERVICES';   // delivery integrations (future packet)
    case ADVANCED_REPORTS = 'ADVANCED_REPORTS';     // finance/analytics export (viewing stays free)

    // limit capabilities
    case STAFF_SEATS = 'STAFF_SEATS';               // active members + pending invites per store
    case PRODUCTS_LIMIT = 'PRODUCTS_LIMIT';         // products per store

    /** Limit-kind features cap a quantity; boolean features gate access. */
    public function isLimit(): bool
    {
        return match ($this) {
            self::STAFF_SEATS, self::PRODUCTS_LIMIT => true,
            default => false,
        };
    }
}

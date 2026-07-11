<?php

namespace App\Support;

use App\Enums\PlanFeature;

/**
 * Canonical starting plans (doc 13 §2). One source of truth read by both the
 * backfill migration and PlanSeeder, so the two never drift. These are only
 * *starting* values — once live, the operator edits plans in the platform admin
 * and this class is no longer consulted.
 *
 * Feature map: boolean features present => enabled; limit features map to an
 * int cap (null = unlimited, so absent = unlimited for limit kinds).
 */
final class DefaultPlans
{
    /**
     * @return list<array{
     *   code:string, name_en:string, name_ar:string, description_en:string,
     *   description_ar:string, price:float, billing_cycle:string,
     *   is_default:bool, is_active:bool, sort_order:int,
     *   features:array<string,int|null>
     * }>
     */
    public static function definitions(): array
    {
        return [
            [
                'code' => 'FREE',
                'name_en' => 'Free',
                'name_ar' => 'مجاني',
                'description_en' => 'Run your shop and share a menu online.',
                'description_ar' => 'أدر متجرك وشارك قائمتك عبر الإنترنت.',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
                // Full back-office (POS + CRM) with modest catalogue caps; public
                // presence is the anonymous view-only menu. One seat = the owner
                // alone. No online ordering, no branded URL.
                'features' => [
                    PlanFeature::POS_ACCESS->value => null,
                    PlanFeature::CUSTOMER_CRM->value => null,
                    PlanFeature::STAFF_SEATS->value => 1,
                    PlanFeature::PRODUCTS_LIMIT->value => 50,
                    PlanFeature::CATEGORIES_LIMIT->value => 10,
                    PlanFeature::PRODUCT_IMAGES_LIMIT->value => 3,
                ],
            ],
            [
                'code' => 'PRO',
                'name_en' => 'Pro',
                'name_ar' => 'برو',
                'description_en' => 'Your own storefront, online ordering and delivery.',
                'description_ar' => 'واجهة متجرك الخاصة، الطلب عبر الإنترنت والتوصيل.',
                'price' => 99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    PlanFeature::POS_ACCESS->value => null,
                    PlanFeature::ONLINE_ORDERING->value => null,
                    PlanFeature::BRANDED_STOREFRONT->value => null,
                    PlanFeature::DELIVERY_SERVICES->value => null,
                    PlanFeature::DINE_IN->value => null,
                    PlanFeature::WHATSAPP_ORDERING->value => null,
                    PlanFeature::CUSTOMER_CRM->value => null,
                    PlanFeature::ADVANCED_REPORTS->value => null,
                    PlanFeature::LOYALTY->value => null,
                    PlanFeature::COUPONS->value => null,
                    PlanFeature::GIFT_CARDS->value => null,
                    PlanFeature::REFERRALS->value => null,
                    PlanFeature::SEGMENTS->value => null,
                    PlanFeature::CAMPAIGNS->value => null,
                    PlanFeature::RECURRING_ORDERS->value => null,
                    PlanFeature::STAFF_SEATS->value => 5,
                    PlanFeature::PRODUCT_IMAGES_LIMIT->value => 8,
                    // PRODUCTS_LIMIT / CATEGORIES_LIMIT absent => unlimited.
                ],
            ],
            [
                'code' => 'ENTERPRISE',
                'name_en' => 'Enterprise',
                'name_ar' => 'إنتربرايز',
                'description_en' => 'Everything in Pro, your own domain, unlimited staff.',
                'description_ar' => 'كل ما في برو، نطاقك الخاص، عدد غير محدود من الموظفين.',
                'price' => 299,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    PlanFeature::POS_ACCESS->value => null,
                    PlanFeature::ONLINE_ORDERING->value => null,
                    PlanFeature::BRANDED_STOREFRONT->value => null,
                    PlanFeature::CUSTOM_DOMAIN->value => null,
                    PlanFeature::DELIVERY_SERVICES->value => null,
                    PlanFeature::DINE_IN->value => null,
                    PlanFeature::WHATSAPP_ORDERING->value => null,
                    PlanFeature::CUSTOMER_CRM->value => null,
                    PlanFeature::ADVANCED_REPORTS->value => null,
                    PlanFeature::LOYALTY->value => null,
                    PlanFeature::COUPONS->value => null,
                    PlanFeature::GIFT_CARDS->value => null,
                    PlanFeature::REFERRALS->value => null,
                    PlanFeature::SEGMENTS->value => null,
                    PlanFeature::CAMPAIGNS->value => null,
                    PlanFeature::RECURRING_ORDERS->value => null,
                    // All limits absent => unlimited staff/products/categories/photos.
                ],
            ],
        ];
    }
}

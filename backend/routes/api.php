<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisteredStoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryImageController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerImportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDeliveryFeeController;
use App\Http\Controllers\Api\OrderPaymentProofController;
use App\Http\Controllers\Api\OrderPaymentStatusController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\PlanCatalogController;
use App\Http\Controllers\Api\Platform\AnalyticsController as PlatformAnalyticsController;
use App\Http\Controllers\Api\Platform\AuditLogController as PlatformAuditLogController;
use App\Http\Controllers\Api\Platform\DataController as PlatformDataController;
use App\Http\Controllers\Api\Platform\DeliveryProviderController as PlatformDeliveryProviderController;
use App\Http\Controllers\Api\Platform\ImpersonationController as PlatformImpersonationController;
use App\Http\Controllers\Api\Platform\OverviewController as PlatformOverviewController;
use App\Http\Controllers\Api\Platform\PlanController as PlatformPlanController;
use App\Http\Controllers\Api\Platform\StoreController as PlatformStoreController;
use App\Http\Controllers\Api\Platform\UserController as PlatformUserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductTranslationController;
use App\Http\Controllers\Api\Public\MenuController;
use App\Http\Controllers\Api\Public\PublicCustomerLookupController;
use App\Http\Controllers\Api\Public\PublicDeliveryQuoteController;
use App\Http\Controllers\Api\Public\PublicOrderController;
use App\Http\Controllers\Api\Public\PublicOrderPaymentProofController;
use App\Http\Controllers\Api\Public\PublicOrderShowController;
use App\Http\Controllers\Api\Public\StorefrontController as PublicStorefrontController;
use App\Http\Controllers\Api\Reports\AnalyticsController;
use App\Http\Controllers\Api\Reports\FinanceController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffInvitationController;
use App\Http\Controllers\Api\StorefrontMediaController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\Webhook\DorzakBusinessWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 routes
|--------------------------------------------------------------------------
| TP-01 delivers the authentication surface. Later packets add their route
| groups (products, orders, ...) under the same `auth:sanctum` + `store` stack.
| Contract authority: docs/backend-planning/05-api-contracts.md.
*/

Route::prefix('v1')->group(function () {

    // --- Public auth endpoints ---
    Route::post('auth/register', [RegisteredStoreController::class, 'store']);
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/forgot-password', [PasswordController::class, 'forgot']);
    Route::post('auth/reset-password', [PasswordController::class, 'reset']);
    Route::post('staff/invitations/{token}/accept', [StaffInvitationController::class, 'accept'])
        ->middleware('throttle:10,1');

    // --- Session (works with or without a store: members get store context,
    //     store-less platform admins get a platform session). ---
    Route::middleware(['auth:sanctum', 'store.context'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

    // --- Authenticated, store-scoped endpoints ---
    Route::middleware(['auth:sanctum', 'store'])->group(function () {
        // Settings hub (read: any member; writes: settings.manage via FormRequest).
        Route::get('settings', [SettingsController::class, 'show']);
        Route::put('settings/general', [SettingsController::class, 'updateGeneral']);
        Route::put('settings/business', [SettingsController::class, 'updateBusiness']);
        Route::put('settings/currency', [SettingsController::class, 'updateCurrency']);
        Route::put('settings/taxes', [SettingsController::class, 'updateTaxes']);
        Route::put('settings/receipts', [SettingsController::class, 'updateReceipts']);
        Route::put('settings/payments', [SettingsController::class, 'updatePayments']);
        Route::put('settings/integrations', [SettingsController::class, 'updateIntegrations']);
        Route::put('settings/storefront', [SettingsController::class, 'updateStorefront']);
        // Loyalty program config (premium — PlanFeature::LOYALTY).
        Route::get('settings/loyalty', [\App\Http\Controllers\Api\LoyaltyController::class, 'show']);
        Route::put('settings/loyalty', [\App\Http\Controllers\Api\LoyaltyController::class, 'update']);
        // Coupons (premium — PlanFeature::COUPONS).
        Route::get('coupons', [\App\Http\Controllers\Api\CouponController::class, 'index']);
        Route::post('coupons', [\App\Http\Controllers\Api\CouponController::class, 'store']);
        Route::delete('coupons/{coupon}', [\App\Http\Controllers\Api\CouponController::class, 'destroy']);
        // Gift cards + store credit (premium — PlanFeature::GIFT_CARDS).
        Route::get('gift-cards', [\App\Http\Controllers\Api\GiftCardController::class, 'index']);
        Route::post('gift-cards', [\App\Http\Controllers\Api\GiftCardController::class, 'store']);
        Route::post('gift-cards/redeem', [\App\Http\Controllers\Api\GiftCardController::class, 'redeem']);
        // Referral program (premium — PlanFeature::REFERRALS).
        Route::get('settings/referrals', [\App\Http\Controllers\Api\ReferralController::class, 'show']);
        Route::put('settings/referrals', [\App\Http\Controllers\Api\ReferralController::class, 'update']);
        // Customer segments (premium — PlanFeature::SEGMENTS).
        Route::get('segments', [\App\Http\Controllers\Api\SegmentController::class, 'index']);
        Route::post('segments', [\App\Http\Controllers\Api\SegmentController::class, 'store']);
        Route::delete('segments/{segment}', [\App\Http\Controllers\Api\SegmentController::class, 'destroy']);
        // Marketing campaigns (premium — PlanFeature::CAMPAIGNS).
        Route::get('campaigns', [\App\Http\Controllers\Api\CampaignController::class, 'index']);
        Route::post('campaigns', [\App\Http\Controllers\Api\CampaignController::class, 'store']);
        Route::delete('campaigns/{campaign}', [\App\Http\Controllers\Api\CampaignController::class, 'destroy']);
        // Recurring orders (premium — PlanFeature::RECURRING_ORDERS).
        Route::get('recurring-orders', [\App\Http\Controllers\Api\RecurringOrderController::class, 'index']);
        Route::post('recurring-orders', [\App\Http\Controllers\Api\RecurringOrderController::class, 'store']);
        Route::patch('recurring-orders/{subscription}', [\App\Http\Controllers\Api\RecurringOrderController::class, 'update']);
        // Product reviews (premium — PlanFeature::REVIEWS).
        Route::get('reviews', [\App\Http\Controllers\Api\ReviewController::class, 'index']);
        Route::post('reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
        Route::post('reviews/{review}/approve', [\App\Http\Controllers\Api\ReviewController::class, 'approve']);
        Route::delete('reviews/{review}', [\App\Http\Controllers\Api\ReviewController::class, 'destroy']);
        Route::post('settings/storefront/banner', [StorefrontMediaController::class, 'banner']);
        Route::post('settings/storefront/logo', [StorefrontMediaController::class, 'logo']);

        // Staff & invitations (staff.view / staff.manage).
        Route::get('staff', [StaffController::class, 'index']);
        Route::patch('staff/{user}', [StaffController::class, 'update']);
        Route::delete('staff/{user}', [StaffController::class, 'destroy']);
        Route::post('staff/invitations', [StaffInvitationController::class, 'store']);
        Route::post('staff/invitations/{invitation}/resend', [StaffInvitationController::class, 'resend']);
        Route::delete('staff/invitations/{invitation}', [StaffInvitationController::class, 'destroy']);

        // Catalog: categories.
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::patch('categories/reorder', [CategoryController::class, 'reorder']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        Route::post('categories/{category}/image', [CategoryImageController::class, 'store']);

        // Catalog: products.
        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        Route::post('products/{product}/image', [ProductImageController::class, 'store']);
        Route::post('products/{product}/additional-images', [ProductImageController::class, 'storeAdditional']);
        Route::delete('products/{product}/additional-images', [ProductImageController::class, 'destroyAdditional']);
        Route::post('products/translate-arabic', ProductTranslationController::class);

        // Customers (CRM).
        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/export', [CustomerController::class, 'export']);
        Route::post('customers/import', CustomerImportController::class);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
        Route::put('customers/{customer}', [CustomerController::class, 'update']);
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

        // Orders & checkout.
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/export', [OrderController::class, 'export']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::patch('orders/{order}/status', [OrderStatusController::class, 'update']);
        Route::patch('orders/{order}/delivery-fee', [OrderDeliveryFeeController::class, 'update']);
        Route::patch('orders/{order}/payment-status', [OrderPaymentStatusController::class, 'update']);
        Route::get('orders/{order}/payment-proof', OrderPaymentProofController::class);

        // Reports (reports.view: owner/manager/viewer; export: owner/manager).
        Route::get('reports/finance', [FinanceController::class, 'index']);
        Route::get('reports/finance/export', [FinanceController::class, 'export']);
        Route::get('reports/analytics', [AnalyticsController::class, 'index']);

        // Subscription (read: member; trial/portal/invoice: owner).
        Route::get('plans', [PlanCatalogController::class, 'index']);
        Route::get('subscription', [SubscriptionController::class, 'show']);
        Route::post('subscription/trial', [SubscriptionController::class, 'startTrial']);
        Route::post('subscription/portal', [SubscriptionController::class, 'portal']);
        Route::get('subscription/invoice/latest', [SubscriptionController::class, 'invoiceLatest']);
    });
});

/*
|--------------------------------------------------------------------------
| Platform admin (is_platform_admin users only — bypasses store context)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/platform')->middleware(['auth:sanctum', 'platform.admin'])->group(function () {
    // Fleet dashboard + commercial analytics + accountability trail.
    Route::get('overview', [PlatformOverviewController::class, 'index']);
    Route::get('analytics', [PlatformAnalyticsController::class, 'platform']);
    Route::get('audit-logs', [PlatformAuditLogController::class, 'index']);

    // Cross-tenant data browsing, export (CSV/Excel) and import.
    Route::get('customers', [PlatformDataController::class, 'customers']);
    Route::get('customers/export', [PlatformDataController::class, 'exportCustomers']);
    Route::post('customers/import', [PlatformDataController::class, 'importCustomers']);
    Route::get('products', [PlatformDataController::class, 'products']);
    Route::get('products/export', [PlatformDataController::class, 'exportProducts']);
    Route::get('stores/export', [PlatformDataController::class, 'exportStores']);
    Route::get('orders/export', [PlatformDataController::class, 'exportOrders']);

    // Delivery providers: distance-pricing formulas shared by every store.
    Route::get('delivery-providers', [PlatformDeliveryProviderController::class, 'index']);
    Route::post('delivery-providers', [PlatformDeliveryProviderController::class, 'store']);
    Route::put('delivery-providers/{provider}', [PlatformDeliveryProviderController::class, 'update']);
    Route::delete('delivery-providers/{provider}', [PlatformDeliveryProviderController::class, 'destroy']);

    // Plans: CRUD + feature composition.
    Route::get('plan-features', [PlatformPlanController::class, 'catalog']);
    Route::get('plans', [PlatformPlanController::class, 'index']);
    Route::post('plans', [PlatformPlanController::class, 'store']);
    Route::get('plans/{plan}', [PlatformPlanController::class, 'show']);
    Route::put('plans/{plan}', [PlatformPlanController::class, 'update']);
    Route::delete('plans/{plan}', [PlatformPlanController::class, 'destroy']);

    // Stores: list, detail, lifecycle, plan override, impersonation, delete.
    Route::get('stores', [PlatformStoreController::class, 'index']);
    Route::get('stores/{store}', [PlatformStoreController::class, 'show']);
    Route::get('stores/{store}/analytics', [PlatformAnalyticsController::class, 'store']);
    Route::put('stores/{store}/suspend', [PlatformStoreController::class, 'suspend']);
    Route::put('stores/{store}/reactivate', [PlatformStoreController::class, 'reactivate']);
    Route::put('stores/{store}/plan', [PlatformStoreController::class, 'assignPlan']);
    Route::post('stores/{store}/impersonate', [PlatformImpersonationController::class, 'store']);
    Route::delete('stores/{store}', [PlatformStoreController::class, 'destroy']);

    // Users: cross-tenant administration.
    Route::get('users', [PlatformUserController::class, 'index']);
    Route::put('users/{user}/grant-admin', [PlatformUserController::class, 'grantAdmin']);
    Route::put('users/{user}/revoke-admin', [PlatformUserController::class, 'revokeAdmin']);
    Route::put('users/{user}/active', [PlatformUserController::class, 'setActive']);
    Route::post('users/{user}/reset-password', [PlatformUserController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Dorzak Business (delivery network) callbacks — HMAC-signed, no session.
|--------------------------------------------------------------------------
*/
Route::post('webhooks/dorzak-business', DorzakBusinessWebhookController::class)
    ->middleware('throttle:120,1');

/*
|--------------------------------------------------------------------------
| Public storefront (anonymous, slug-resolved, rate-limited) — docs 02 Page 11
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    // Branded slug routes — paid stores with online_store_enabled.
    Route::get('stores/{slug}', [PublicStorefrontController::class, 'show'])->middleware('throttle:60,1');
    Route::get('stores/{slug}/catalog', [PublicStorefrontController::class, 'catalog'])->middleware('throttle:60,1');
    Route::get('stores/{slug}/delivery-quote', PublicDeliveryQuoteController::class)->middleware('throttle:30,1');
    Route::post('stores/{slug}/orders', PublicOrderController::class)->middleware('throttle:5,1');
    Route::get('stores/{slug}/orders/{orderNumber}', PublicOrderShowController::class)->middleware('throttle:60,1');
    Route::post('stores/{slug}/orders/{orderNumber}/payment-proof', PublicOrderPaymentProofController::class)->middleware('throttle:5,1');
    Route::get('stores/{slug}/customers/lookup', PublicCustomerLookupController::class)->middleware('throttle:60,1');

    // Anonymous menu — free-tier stores; view-only, no ordering.
    Route::get('menu/{token}', [MenuController::class, 'show'])->middleware('throttle:60,1');
    Route::get('menu/{token}/catalog', [MenuController::class, 'catalog'])->middleware('throttle:60,1');

    // Subdomain resolution — SPA calls this on mount when running on {slug}.APP_DOMAIN.
    Route::get('resolve', [PublicStorefrontController::class, 'resolveHost'])->middleware('throttle:60,1');
});

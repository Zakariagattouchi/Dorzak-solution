<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisteredStoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryImageController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerImportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderPaymentProofController;
use App\Http\Controllers\Api\OrderPaymentStatusController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductTranslationController;
use App\Http\Controllers\Api\Public\MenuController;
use App\Http\Controllers\Api\Public\PublicCustomerLookupController;
use App\Http\Controllers\Api\Public\PublicOrderController;
use App\Http\Controllers\Api\Public\PublicOrderShowController;
use App\Http\Controllers\Api\Public\StorefrontController as PublicStorefrontController;
use App\Http\Controllers\Api\Reports\AnalyticsController;
use App\Http\Controllers\Api\Reports\FinanceController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffInvitationController;
use App\Http\Controllers\Api\StorefrontMediaController;
use App\Http\Controllers\Api\SubscriptionController;
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

    // --- Authenticated, store-scoped endpoints ---
    Route::middleware(['auth:sanctum', 'store'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

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
        Route::patch('orders/{order}/payment-status', [OrderPaymentStatusController::class, 'update']);
        Route::get('orders/{order}/payment-proof', OrderPaymentProofController::class);

        // Reports (reports.view: owner/manager/viewer; export: owner/manager).
        Route::get('reports/finance', [FinanceController::class, 'index']);
        Route::get('reports/finance/export', [FinanceController::class, 'export']);
        Route::get('reports/analytics', [AnalyticsController::class, 'index']);

        // Subscription (read: member; portal/invoice: owner).
        Route::get('subscription', [SubscriptionController::class, 'show']);
        Route::post('subscription/portal', [SubscriptionController::class, 'portal']);
        Route::get('subscription/invoice/latest', [SubscriptionController::class, 'invoiceLatest']);
    });
});

/*
|--------------------------------------------------------------------------
| Public storefront (anonymous, slug-resolved, rate-limited) — docs 02 Page 11
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    // Branded slug routes — paid stores with online_store_enabled.
    Route::get('stores/{slug}', [PublicStorefrontController::class, 'show'])->middleware('throttle:60,1');
    Route::get('stores/{slug}/catalog', [PublicStorefrontController::class, 'catalog'])->middleware('throttle:60,1');
    Route::post('stores/{slug}/orders', PublicOrderController::class)->middleware('throttle:5,1');
    Route::get('stores/{slug}/orders/{orderNumber}', PublicOrderShowController::class)->middleware('throttle:60,1');
    Route::get('stores/{slug}/customers/lookup', PublicCustomerLookupController::class)->middleware('throttle:60,1');

    // Anonymous menu — free-tier stores; view-only, no ordering.
    Route::get('menu/{token}', [MenuController::class, 'show'])->middleware('throttle:60,1');
    Route::get('menu/{token}/catalog', [MenuController::class, 'catalog'])->middleware('throttle:60,1');

    // Subdomain resolution — SPA calls this on mount when running on {slug}.APP_DOMAIN.
    Route::get('resolve', [PublicStorefrontController::class, 'resolveHost'])->middleware('throttle:60,1');
});

# Phase 1 — Storefront Marketing Sync Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. **Read `2026-07-12-marketing-00-overview.md` § Global Constraints first — it applies to every task here.**

**Goal:** Every marketing program the merchant configures is honored where customers actually shop — the public storefront: marketing consent + unsubscribe, coupon codes at checkout, loyalty points visible & redeemable, store-credit redemption, verified-purchase reviews with storefront star display, and referral capture.

**Architecture:** The public checkout (`PublicOrderService::place`) already delegates to `OrderService::create`, which already handles `coupon_code`, `loyalty_redeem_points` (as `wallet_redeem`? no — see Task 3), `wallet_redeem`, and `referral_code`. Most backend work is passing validated fields through the public layer plus a few read endpoints; the storefront React pages gain the UI. Reviews get a public verified-purchase submit endpoint + aggregate display in the public catalog.

**Tech Stack:** Laravel 12 (backend/), React 18 + TS (src/), PHPUnit feature tests, no new dependencies.

**Key existing signatures (verified):**
- `PublicOrderService::place(Store $store, array $data): array` — `backend/app/Services/PublicOrderService.php:38`; inside, `$this->orders->create($store, [...])` at ~line 72.
- `OrderService::create(Store $store, array $data, ?User $user = null): Order` already consumes: `coupon_code` (string), `wallet_redeem` (float), `referral_code` (string), `customer_id`. Loyalty redemption at checkout is NOT yet in OrderService — Task 3 adds `loyalty_redeem_points`.
- `CouponService::quote(Store $store, string $code, float $subtotal): array{coupon: Coupon, discount: float}` — throws `DomainConflictException` (codes: COUPON_INVALID/COUPON_EXPIRED/COUPON_EXHAUSTED/COUPON_MIN_ORDER).
- `LoyaltyService::program(Store $store): ?LoyaltyProgram`, `balance(Customer $c): int`, `redeem(Customer $c, int $points): float` (throws `DomainConflictException('INSUFFICIENT_POINTS'|'LOYALTY_NOT_ENABLED')`).
- `WalletService::balance(Customer $c): float`, `redeem(Customer $c, float $amount, string $reason): void`.
- `ReferralService::codeFor(Customer $c): string`, `program(Store $store): ?ReferralProgram`.
- `ReviewService::submit(Store $store, Product $product, array $data): Review` (plan-gated), `stats(Product $p)`.
- Public store resolution trait: `App\Http\Controllers\Api\Public\Concerns\ResolvesPublicStore` → `$this->resolvePublicStore($slug)` (verify exact namespace by opening an existing public controller, e.g. `PublicCustomerLookupController`).
- Storefront pages: `src/pages/storefront/StorefrontPreviewPage.tsx` (catalog + cart + checkout), `src/pages/storefront/OrderStatusPage.tsx` (uses `publicApi` from `src/api/endpoints.ts`).

---

### Task 1: Marketing consent — schema, checkout capture, unsubscribe

**Files:**
- Create: `backend/database/migrations/2026_07_12_000001_add_marketing_consent_to_customers.php`
- Modify: `backend/app/Models/Customer.php` (fillable + casts)
- Modify: `backend/app/Services/PublicOrderService.php` (~line 226 `upsertCustomer`)
- Modify: `backend/app/Http/Requests/Public/StorePublicOrderRequest.php` (add rule)
- Create: `backend/app/Http/Controllers/Api/Public/PublicUnsubscribeController.php`
- Modify: `backend/routes/api.php` (public route)
- Modify: `backend/app/Services/CampaignService.php` (`audienceFor` consent filter)
- Modify: `backend/app/Mail/CampaignMail.php` (unsubscribe footer link)
- Test: `backend/tests/Feature/Marketing/ConsentTest.php`

**Interfaces:**
- Produces: `customers.marketing_consent` (bool, default false), `customers.consented_at` (nullable timestamp); signed route name `public.unsubscribe` (`GET /api/public/unsubscribe/{customer}`); `CampaignService::audienceFor()` now returns only consented customers.
- Consumed by: every later campaign/automation task (they inherit the filter for free).

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\MessagingSetting;
use App\Models\Store;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Marketing consent: campaigns reach only opted-in customers; one-click unsubscribe. */
class ConsentTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
        MessagingSetting::create([
            'store_id' => $this->store->id,
            'email_from_name' => 'Test', 'email_from_address' => 'hello@test.shop',
        ]);
    }

    public function test_campaign_audience_includes_only_consented_customers(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'yes@example.com', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'no@example.com', 'marketing_consent' => false]);

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'S', 'body' => 'B',
            'channel' => 'email', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        app(CampaignService::class)->send($campaign);

        $this->assertSame(1, $campaign->refresh()->sent_count);
    }

    public function test_signed_unsubscribe_link_revokes_consent(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id, 'marketing_consent' => true]);
        $url = URL::signedRoute('public.unsubscribe', ['customer' => $customer->id]);

        $this->get($url)->assertOk()->assertSee('unsubscribed');

        $this->assertFalse($customer->fresh()->marketing_consent);
    }

    public function test_unsigned_unsubscribe_is_rejected(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id, 'marketing_consent' => true]);

        $this->get("/api/public/unsubscribe/{$customer->id}")->assertForbidden();

        $this->assertTrue($customer->fresh()->marketing_consent);
    }

    public function test_public_checkout_records_consent(): void
    {
        $store = Store::factory()->create(['charge_sales_tax' => false]);
        // Storefront must be enabled for public ordering — mirror the setup used
        // in tests/Feature/Public/* (open one and copy its store/product setup
        // EXACTLY, including storefront settings + published product).
        // Then POST /api/public/stores/{slug}/orders with the existing happy-path
        // payload from that test PLUS: 'marketing_consent' => true.
        // Assert: Customer::withoutGlobalScopes()->where('store_id',$store->id)->first()->marketing_consent === true.
        $this->markTestIncomplete('Copy the happy-path public order setup from tests/Feature/Public, add marketing_consent => true, assert customer flag.');
    }
}
```

> The fourth test intentionally starts as `markTestIncomplete` with instructions: the public-order happy-path setup is long and already exists in `backend/tests/Feature/Public/` — **copy it exactly** (store settings + product + payload) rather than inventing one, then complete the test. It must be a real passing test before this task's final commit.

- [ ] **Step 2: Run to verify failure**

Run: `cd "/Users/barsha/Documents/recover Kyte/backend" && php artisan test tests/Feature/Marketing/ConsentTest.php`
Expected: FAIL — column `marketing_consent` missing / route `public.unsubscribe` not defined.

- [ ] **Step 3: Implement**

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Marketing consent: campaigns may only reach customers who opted in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('marketing_consent')->default(false)->after('email');
            $table->timestamp('consented_at')->nullable()->after('marketing_consent');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['marketing_consent', 'consented_at']);
        });
    }
};
```

`Customer.php`: add `'marketing_consent', 'consented_at'` to `$fillable`; add casts `'marketing_consent' => 'boolean', 'consented_at' => 'datetime'`.

`StorePublicOrderRequest.php` rules: add `'marketing_consent' => ['nullable', 'boolean'],`.

`PublicOrderService::upsertCustomer` — after the existing firstOrCreate/create logic, add (keep existing behavior; only ever upgrade consent, never silently revoke):

```php
if (! empty($data['marketing_consent']) && ! $customer->marketing_consent) {
    $customer->forceFill(['marketing_consent' => true, 'consented_at' => now()])->save();
}
```

(`upsertCustomer` receives `$data` — confirm and thread the field through from `place()` if it only receives a sub-array.)

`PublicUnsubscribeController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

/** One-click, signed unsubscribe from marketing messages (linked in every campaign email). */
class PublicUnsubscribeController extends Controller
{
    public function __invoke(Request $request, int $customer)
    {
        abort_unless($request->hasValidSignature(), 403);

        Customer::withoutGlobalScopes()->whereKey($customer)
            ->update(['marketing_consent' => false]);

        return response('<html><body style="font-family:sans-serif;text-align:center;padding:60px;">'
            .'<h2>You have been unsubscribed.</h2><p>You will no longer receive marketing messages from this store.</p>'
            .'</body></html>', 200)->header('Content-Type', 'text/html');
    }
}
```

Route (inside the existing `Route::prefix('public')` group in `backend/routes/api.php`):

```php
Route::get('unsubscribe/{customer}', \App\Http\Controllers\Api\Public\PublicUnsubscribeController::class)
    ->name('public.unsubscribe')->middleware('throttle:30,1');
```

`CampaignService::audienceFor` — add the filter to BOTH branches:

```php
// Segment branch:
return $segment === null ? collect() : $this->segments->members($segment)->filter(fn ($c) => $c->marketing_consent)->values();
// All branch:
return $campaign->store->customers()->where('marketing_consent', true)->get();
```

`CampaignMail.php` — append an unsubscribe link. The mailable needs the customer; change the constructor to `public Campaign $campaign, public ?string $fromAddress = null, public ?string $fromName = null, public ?int $customerId = null` and in `content()` append when `$customerId` is set:

```php
$unsubscribe = $this->customerId
    ? \Illuminate\Support\Facades\URL::signedRoute('public.unsubscribe', ['customer' => $this->customerId])
    : null;
// htmlString: existing body + ($unsubscribe ? '<p style="margin-top:24px;font-size:12px;color:#999;"><a href="'.$unsubscribe.'" style="color:#999;">Unsubscribe</a></p>' : '')
```

Update the ONE call site `CampaignService::sendEmail` → `new CampaignMail($campaign, $channel['from_address'], $channel['from_name'], $customer->id)`.

- [ ] **Step 4: Run tests**

Run: `php artisan test tests/Feature/Marketing/ConsentTest.php tests/Feature/Campaign tests/Feature/Marketing`
Expected: ConsentTest passes (complete the fourth test now — copy setup from `tests/Feature/Public/`); **existing campaign tests will fail** because their factory customers default to `marketing_consent = false`. Fix those tests by setting `'marketing_consent' => true` on customers they expect to receive sends (they encode the OLD contract; the new contract is consent-only). Do NOT weaken the filter.

- [ ] **Step 5: Commit**

```bash
cd "/Users/barsha/Documents/recover Kyte" && git add backend/ && git commit -m "feat(consent): marketing consent + signed unsubscribe; campaigns reach opted-in customers only

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 2: Coupons at public checkout (validate endpoint + order field + storefront UI)

**Files:**
- Create: `backend/app/Http/Controllers/Api/Public/PublicCouponController.php`
- Modify: `backend/routes/api.php`, `backend/app/Http/Requests/Public/StorePublicOrderRequest.php`, `backend/app/Services/PublicOrderService.php`
- Modify: `src/api/endpoints.ts` (`publicApi`), `src/pages/storefront/StorefrontPreviewPage.tsx`
- Test: `backend/tests/Feature/Public/PublicCouponTest.php`

**Interfaces:**
- Produces: `GET /api/public/stores/{slug}/coupons/validate?code=X&subtotal=NN` → `{valid: true, discount: float, code: string}` or 409 `{code: 'COUPON_*', message}`; public order payload accepts `coupon_code`.
- Consumes: `CouponService::quote` (Task-0 signature above); `OrderService::create` already applies `coupon_code`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature\Public;

use App\Models\Coupon;
use App\Models\Store;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The storefront can validate and apply coupon codes at checkout. */
class PublicCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_endpoint_quotes_a_good_code(): void
    {
        $store = Store::factory()->create(); // must be publicly resolvable —
        // copy the storefront-enabled store setup from an existing tests/Feature/Public test.
        app(StoreContext::class)->setStore($store);
        Coupon::create(['store_id' => $store->id, 'code' => 'SAVE10', 'type' => 'PERCENT', 'value' => 10, 'active' => true]);
        app(StoreContext::class)->setStore(null); // public endpoints run without store context

        $this->getJson("/api/public/stores/{$store->slug}/coupons/validate?code=SAVE10&subtotal=100")
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('discount', 10);
    }

    public function test_validate_endpoint_rejects_bad_code_with_machine_code(): void
    {
        $store = Store::factory()->create(); // storefront-enabled, as above

        $this->getJson("/api/public/stores/{$store->slug}/coupons/validate?code=NOPE&subtotal=100")
            ->assertStatus(409)
            ->assertJsonPath('code', 'COUPON_INVALID');
    }

    public function test_public_order_applies_the_coupon(): void
    {
        // Copy the FULL happy-path order setup from an existing tests/Feature/Public
        // order test (store + product + payload), add:
        //   'coupon_code' => 'SAVE10'  (create the coupon first, PERCENT 10, store tax OFF)
        // Assert response order discount == 10% of subtotal and
        // Coupon used_count == 1.
        $this->markTestIncomplete('Complete using the existing public order happy-path setup.');
    }
}
```

Note the `setStore(null)` trick — if `StoreContext` has no unset method, check `app/Support/StoreContext.php` for how tests clear it (or instantiate a fresh context); public endpoint tests in `tests/Feature/Public/` show the established pattern — **mirror it**.

- [ ] **Step 2: Verify failure** — `php artisan test tests/Feature/Public/PublicCouponTest.php` → FAIL 404 (route missing).

- [ ] **Step 3: Implement**

`PublicCouponController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Live coupon validation for the storefront checkout preview. */
class PublicCouponController extends Controller
{
    use ResolvesPublicStore; // same trait/namespace as PublicCustomerLookupController — copy its use-statement exactly

    public function __invoke(Request $request, string $slug, CouponService $coupons): JsonResponse
    {
        $store = $this->resolvePublicStore($slug);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        // DomainConflictException from quote() renders itself as 409 + machine code.
        $quote = $coupons->quote($store, $data['code'], (float) $data['subtotal']);

        return response()->json(['valid' => true, 'code' => $quote['coupon']->code, 'discount' => $quote['discount']]);
    }
}
```

Route (public group): `Route::get('stores/{slug}/coupons/validate', PublicCouponController::class)->middleware('throttle:30,1');` (add the `use` import at the top of `routes/api.php` following the existing alias style).

`StorePublicOrderRequest`: add `'coupon_code' => ['nullable', 'string', 'max:60'],`.

`PublicOrderService::place` — inside the `$this->orders->create($store, [...])` array add `'coupon_code' => $data['coupon_code'] ?? null,`. **Important:** `OrderService::create` quotes the coupon against the LINE subtotal it computes itself — no extra math here.

Frontend — `src/api/endpoints.ts`, find `publicApi` and add:

```ts
validateCoupon: (slug: string, code: string, subtotal: number) =>
  request(`${PUBLIC_BASE}/stores/${slug}/coupons/validate?code=${encodeURIComponent(code)}&subtotal=${subtotal}`),
```

(`PUBLIC_BASE` already exists at the top of the file; inspect how existing `publicApi` fns build URLs and match exactly.)

`StorefrontPreviewPage.tsx` — in the checkout step (find the notes/phone fields), add a coupon row: `TextInput` for the code + an "Apply" `AppButton` that calls `validateCoupon(slug, code, subtotal)`; on success store `{code, discount}` in component state, render a green summary line `Coupon SAVE10 −{money(discount)}` above the total and subtract from the displayed total; on 409 show the API message under the field and clear state. Include `coupon_code: appliedCoupon?.code ?? null` in the order payload. Follow the page's existing state conventions (it's a large component — search for where `notes` state is defined and mirror).

- [ ] **Step 4: Verify** — backend tests pass (complete the incomplete one); `npx tsc --noEmit` → 0.

- [ ] **Step 5: Commit** — `feat(storefront): coupon codes at public checkout with live validation`.

---

### Task 3: Loyalty on the storefront (lookup shows points, checkout redeems, order-status shows earned)

**Files:**
- Modify: `backend/app/Http/Controllers/Api/Public/PublicCustomerLookupController.php`
- Modify: `backend/app/Services/OrderService.php` (add `loyalty_redeem_points` handling)
- Modify: `backend/app/Http/Requests/Order/StoreOrderRequest.php`, `backend/app/Http/Requests/Public/StorePublicOrderRequest.php`, `backend/app/Services/PublicOrderService.php`
- Modify: `backend/app/Http/Controllers/Api/Public/PublicOrderShowController.php` (earned points + balance in response)
- Modify: `src/pages/storefront/StorefrontPreviewPage.tsx`, `src/pages/storefront/OrderStatusPage.tsx`, `src/api/adapters.ts` if it types the order-status payload
- Test: `backend/tests/Feature/Public/PublicLoyaltyTest.php`

**Interfaces:**
- Produces: lookup response gains `loyalty: {enabled: bool, points: int, redeem_points: int, redeem_value: float} | null`; order create data accepts `loyalty_redeem_points` (int) → converts to discount via `LoyaltyService::redeem`; public order-status response gains `loyalty_earned` (int|null) and `loyalty_balance` (int|null).
- Consumes: `LoyaltyService` signatures from the header block.

- [ ] **Step 1: Failing tests** (structure mirrors Task 2 — three tests):

```php
public function test_lookup_returns_loyalty_when_program_enabled(): void
// setup: storefront store + LoyaltyService::configure(store, enabled, earn 1, redeem 100 => 5)
//        + Customer with phone + LoyaltyAccount 250 points
// GET /api/public/stores/{slug}/customers/lookup?phone=...
// assert loyalty.enabled true, loyalty.points 250, loyalty.redeem_points 100

public function test_checkout_redeems_points_as_discount(): void
// full public order payload + 'loyalty_redeem_points' => 200 (customer holds 250, program 100=>5)
// assert order discount == 10.00, customer balance now 50

public function test_lookup_hides_loyalty_when_program_disabled(): void
// no program configured → response loyalty === null
```

Write them fully (copy public-order setup as in Task 1/2); run → FAIL.

- [ ] **Step 2: Implement backend**

`PublicCustomerLookupController` — after fetching `$customer`, build the response payload it already returns and append:

```php
$program = app(\App\Services\LoyaltyService::class)->program($store);
$loyalty = ($program && $program->enabled && $customer) ? [
    'enabled' => true,
    'points' => app(\App\Services\LoyaltyService::class)->balance($customer),
    'redeem_points' => $program->redeem_points,
    'redeem_value' => (float) $program->redeem_value,
] : null;
// include 'loyalty' => $loyalty in the JSON response alongside the existing fields
```

`OrderService::create` — immediately after the wallet_redeem block (search for `'Applied at checkout'`), add:

```php
// Loyalty points convert to a discount in whole redemption units, debited
// atomically inside this transaction (LoyaltyService::redeem throws on shortfall).
$loyaltyPoints = (int) ($data['loyalty_redeem_points'] ?? 0);
if ($loyaltyPoints > 0 && $customer !== null) {
    $discount += $this->loyalty->redeem($customer, $loyaltyPoints);
}
```

(`$this->loyalty` is already injected. Order of discounts: coupon → wallet → loyalty; total math downstream is unchanged.)

Validation: add `'loyalty_redeem_points' => ['nullable', 'integer', 'min:0'],` to BOTH `StoreOrderRequest` and `StorePublicOrderRequest`; pass through in `PublicOrderService::place` (`'loyalty_redeem_points' => $data['loyalty_redeem_points'] ?? 0,`).

`PublicOrderShowController` — the response already serializes the order; append:

```php
$loyaltyService = app(\App\Services\LoyaltyService::class);
$program = $loyaltyService->program($store);
$earned = null; $balance = null;
if ($program?->enabled && $order->customer_id) {
    $earned = (int) floor((float) $order->total * $program->earn_points_per_currency);
    $balance = $loyaltyService->balance($order->customer);
}
// add 'loyalty_earned' => $earned, 'loyalty_balance' => $balance to the JSON
```

- [ ] **Step 3: Frontend**

`StorefrontPreviewPage.tsx`: the checkout already calls the customer lookup when the phone is entered (search for `lookup`). Extend the lookup handler to store `loyalty` in state; when `loyalty?.enabled && loyalty.points >= loyalty.redeem_points`, render below the coupon row a checkbox: `Use my {points} points (−{money(units * redeem_value)})` where `units = Math.floor(points / redeem_points)`; when checked include `loyalty_redeem_points: units * redeem_points` in the payload and subtract the value from the displayed total.

`OrderStatusPage.tsx`: where the order summary renders, if `loyalty_earned` is present show a celebratory line: `🎉 You earned {loyalty_earned} points — you now have {loyalty_balance}.` (The page has an i18n object at the top — add both EN and AR strings following its existing shape.)

- [ ] **Step 4: Verify** — `php artisan test tests/Feature/Public tests/Feature/Loyalty` all pass; tsc 0.

- [ ] **Step 5: Commit** — `feat(storefront): loyalty points visible and redeemable at public checkout`.

---

### Task 4: Store-credit (wallet) redemption at public checkout

**Files:**
- Modify: `backend/app/Http/Controllers/Api/Public/PublicCustomerLookupController.php` (add `store_credit` to response)
- Modify: `backend/app/Http/Requests/Public/StorePublicOrderRequest.php`, `backend/app/Services/PublicOrderService.php` (pass `wallet_redeem` through — OrderService already implements it)
- Modify: `src/pages/storefront/StorefrontPreviewPage.tsx`
- Test: extend `backend/tests/Feature/Public/PublicLoyaltyTest.php` → rename file/class `PublicWalletLoyaltyTest` OR create `backend/tests/Feature/Public/PublicWalletTest.php` (create the new file; do not rename existing).

**Interfaces:**
- Produces: lookup response gains `store_credit: float`; public order accepts `wallet_redeem` (numeric).

- [ ] **Step 1: Failing test** — `test_checkout_spends_store_credit`: seed credit via `WalletService::credit($customer, 30, 'seed')`, public order with `'wallet_redeem' => 30`, assert order discount 30.00 and balance 0. Plus `test_lookup_reports_store_credit` asserting `store_credit: 30`.

- [ ] **Step 2: Implement** — lookup: `'store_credit' => $customer ? app(\App\Services\WalletService::class)->balance($customer) : 0,`; request rule `'wallet_redeem' => ['nullable', 'numeric', 'min:0'],`; pass-through in `PublicOrderService::place`. Frontend: under the loyalty checkbox, if `store_credit > 0` show checkbox `Use my {money(store_credit)} store credit`; when checked send `wallet_redeem: Math.min(store_credit, remainingTotal)`.

- [ ] **Step 3: Verify + commit** — `feat(storefront): store credit spendable at public checkout`.

---

### Task 5: Public verified-purchase reviews + storefront star display

**Files:**
- Create: `backend/app/Http/Controllers/Api/Public/PublicReviewController.php`
- Modify: `backend/routes/api.php`
- Modify: the public catalog serializer — find it via `grep -rn "catalog" backend/app/Http/Controllers/Api/Public/PublicStorefrontController.php` and the resource it maps products with (likely `backend/app/Http/Resources/Public/…`) — add `rating_avg`, `rating_count`
- Modify: `src/pages/storefront/StorefrontPreviewPage.tsx` (stars on product cards), `src/pages/storefront/OrderStatusPage.tsx` (review form)
- Modify: `src/api/endpoints.ts` (`publicApi.submitReview`)
- Test: `backend/tests/Feature/Public/PublicReviewTest.php`

**Interfaces:**
- Produces: `POST /api/public/stores/{slug}/orders/{orderNumber}/reviews` body `{phone, product_id, rating(1..5), comment?, author_name?}` → 201 `{id}`; catalog products gain `rating_avg: float|null, rating_count: int`.
- Consumes: `ReviewService::submit` (plan-gated internally — a store without REVIEWS returns 402; the storefront hides the form when catalog lacks `reviews_enabled`).

- [ ] **Step 1: Failing tests** (write fully; verified-purchase rules are the point):

```php
public function test_a_verified_purchaser_can_submit_a_pending_review(): void
// storefront store on ENTERPRISE plan; COMPLETE order with known phone + product;
// POST review {phone matching order, product_id in order items, rating 5}
// → 201; Review exists approved=false.

public function test_wrong_phone_is_rejected(): void        // → 403
public function test_product_not_in_order_is_rejected(): void // → 422 validation or 409 — pick 409 REVIEW_NOT_PURCHASED and assert it
public function test_second_review_for_same_product_and_order_is_rejected(): void // 409 REVIEW_DUPLICATE
public function test_catalog_exposes_approved_rating_aggregates_only(): void
// two approved reviews (4,5) + one pending (1) → catalog product rating_avg 4.5, rating_count 2
```

- [ ] **Step 2: Implement**

`PublicReviewController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Exceptions\DomainConflictException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Verified-purchase review intake: only the phone that placed a completed
 * order may review, only products that order contained, once per product.
 * Reviews arrive pending — merchants moderate before anything is public.
 */
class PublicReviewController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(Request $request, string $slug, string $orderNumber, ReviewService $reviews): JsonResponse
    {
        $store = $this->resolvePublicStore($slug);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'author_name' => ['nullable', 'string', 'max:120'],
        ]);

        $order = Order::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $normalized = preg_replace('/\D/', '', $data['phone']);
        abort_unless($normalized !== '' && preg_replace('/\D/', '', (string) $order->customer_phone) === $normalized, 403);

        abort_unless($order->status === \App\Enums\OrderStatus::COMPLETE, 403);

        if (! $order->items()->where('product_id', $data['product_id'])->exists()) {
            throw new DomainConflictException('REVIEW_NOT_PURCHASED', 'You can only review products from this order.');
        }

        $exists = Review::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->where('product_id', $data['product_id'])
            ->where('customer_id', $order->customer_id)
            ->exists();
        if ($exists) {
            throw new DomainConflictException('REVIEW_DUPLICATE', 'You already reviewed this product.');
        }

        $product = Product::withoutGlobalScopes()->where('store_id', $store->id)->findOrFail($data['product_id']);

        $review = $reviews->submit($store, $product, [
            'customer_id' => $order->customer_id,
            'author_name' => $data['author_name'] ?? $order->customer_name,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json(['id' => $review->id], 201);
    }
}
```

> ⚠️ `OrderItem`'s product FK column: verify with `grep -n "product_id" backend/app/Models/OrderItem.php` — if the column is named differently, use the real name in the `items()` where-clause.

Route: `Route::post('stores/{slug}/orders/{orderNumber}/reviews', PublicReviewController::class)->middleware('throttle:5,1');`

Catalog aggregates — in the public catalog query add:

```php
->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('approved', true)], 'rating')
->withCount(['reviews as rating_count' => fn ($q) => $q->where('approved', true)])
```

This requires a `reviews()` HasMany on `Product` — add it (`return $this->hasMany(Review::class);`). Then expose `rating_avg` (round 1dp, null when 0 count) + `rating_count` in the product resource used by the catalog, plus a store-level `reviews_enabled` boolean (`app(PlanGate::class)->allows($store, PlanFeature::REVIEWS)`) on the store payload so the UI knows to show forms.

Frontend: product cards in `StorefrontPreviewPage.tsx` show `★ 4.5 (12)` under the name when `rating_count > 0` (amber `#d97706` stars). `OrderStatusPage.tsx`: when the order is COMPLETE and `reviews_enabled`, render per line-item a 5-star click widget + comment box + submit via `publicApi.submitReview(slug, orderNumber, payload)`; on 201 replace with "Thanks — your review awaits the store's approval."; on 409 show the message.

- [ ] **Step 3: Verify + commit** — `feat(storefront): verified-purchase reviews with storefront star ratings`.

---

### Task 6: Referral capture on the storefront

**Files:**
- Modify: `backend/app/Http/Controllers/Api/Public/PublicOrderShowController.php` (share code + rewards in response when program enabled)
- Modify: `backend/app/Http/Requests/Public/StorePublicOrderRequest.php`, `backend/app/Services/PublicOrderService.php` (pass `referral_code` — OrderService already attributes)
- Modify: `src/pages/storefront/StorefrontPreviewPage.tsx` (capture `?ref=`), `src/pages/storefront/OrderStatusPage.tsx` (share block)
- Test: `backend/tests/Feature/Public/PublicReferralTest.php`

**Interfaces:**
- Produces: order-status response gains `referral: {code: string, referrer_reward: float, referee_reward: float} | null`; public order accepts `referral_code` (max 16).

- [ ] **Step 1: Failing tests**: `test_public_order_with_ref_code_attributes_referral` (new customer + code of existing customer → `referrals` row pending; complete order → both wallets credited — reuse the service-level flow already proven in `tests/Feature/Referral`, here just assert the pending row exists after the public order) and `test_order_status_exposes_the_customers_share_code` (program enabled → response has `referral.code`).

- [ ] **Step 2: Implement**: request rule `'referral_code' => ['nullable', 'string', 'max:16'],`; pass-through in `PublicOrderService::place`. Order-show: when `ReferralService::program($store)?->enabled && $order->customer_id`, include `'referral' => ['code' => app(ReferralService::class)->codeFor($order->customer), 'referrer_reward' => (float) $program->referrer_reward, 'referee_reward' => (float) $program->referee_reward]`.

Frontend: on `StorefrontPreviewPage` mount, `const ref = new URLSearchParams(location.search).get('ref'); if (ref) localStorage.setItem('dz-ref-' + slug, ref)` (expires: store `{code, at}` JSON, ignore after 30 days); include `referral_code` in the order payload from that key. `OrderStatusPage`: when `referral` present render a share card — "Give {money(referee_reward)}, get {money(referrer_reward)} — your code: **CODE**" with a copy button (`navigator.clipboard.writeText`) and a WhatsApp share link `https://wa.me/?text=` + encoded storefront URL `?ref=CODE`.

- [ ] **Step 3: Verify + commit** — `feat(storefront): referral capture via ?ref links + share card on order status`.

---

### Task 7: Full-suite gate + live browser verification

- [ ] **Step 1:** `php artisan test` → everything green except the two known pre-existing failures. `npx tsc --noEmit` → 0.
- [ ] **Step 2:** Browser walk (see Overview § manual verification): storefront `/store/{slug}` → add to cart → checkout: apply a coupon (see discount), enter a known customer phone (see points + credit options), tick consent, place order → order-status shows earned points + review form + referral card. Screenshot each.
- [ ] **Step 3:** Commit any fixes; final commit `feat(storefront): marketing sync phase complete`.

# Phase 4 — Automations, Campaign Attribution & Plan Quotas Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. **Read `2026-07-12-marketing-00-overview.md` § Global Constraints first.** Phases 1–3 must be merged (consent, channels, rich WhatsApp).

**Goal:** The layer that makes merchants *want* the higher plan: set-and-forget automations (welcome + win-back), per-campaign revenue attribution ("this campaign made QAR X"), and monthly send quotas as plan limit-features.

**Architecture:** An `automations` row per store/trigger holds channel + message config; an hourly command evaluates triggers against customer data and sends through the SAME honest channel pipeline (`MessagingService` / `SmsSender`), consent-filtered, with an `automation_sends` ledger guaranteeing once-per-customer-per-trigger. Attribution: a campaign can auto-create a linked coupon (`campaigns.coupon_id`); revenue per campaign = the linked coupon's order stats (already built in `CouponService::stats`). Quotas: new limit-kind `PlanFeature::EMAIL_SENDS_LIMIT` enforced inside `CampaignService::sendEmail` with a lazy monthly counter on `messaging_settings` (same pattern as SMS quota).

**Tech Stack:** Laravel 12, React 18 + TS. No new packages.

**Existing anchors:** `CampaignService::sendEmail/sendWhatsapp/sendSms` return `[sent, failed]` and are consent-filtered via `audienceFor`; `CouponService::stats(Coupon $c): array{orders, revenue, discount_given}`; scheduler entries live in `backend/routes/console.php`; commands in `backend/app/Console/Commands/`; SMS quota lazy-reset pattern in `CampaignService::sendSms` (Phase 2 Task 3); `Customer` cached counters `total_orders`, `total_spent`, and `orders()` HasMany; PlanFeature limit-kind mechanics: `isLimit()` switch + `PlanGate::limit/ensureWithinLimit`.

---

### Task 1: Automations schema + engine (welcome & win-back)

**Files:**
- Create: `backend/database/migrations/2026_07_12_000006_create_automations_tables.php`
- Create: `backend/app/Models/Automation.php`, `backend/app/Models/AutomationSend.php`
- Create: `backend/app/Services/AutomationService.php`
- Create: `backend/app/Console/Commands/RunAutomations.php`
- Modify: `backend/routes/console.php`
- Test: `backend/tests/Feature/Marketing/AutomationTest.php`

**Interfaces:**
- Produces: `automations` `{id, store_id, trigger: 'welcome'|'winback', channel: 'email'|'whatsapp'|'sms', enabled: bool, config: array}` where config = `{subject?: string, body: string, winback_days?: int, wa_template_id?: int, wa_parameters?: array}`; unique `(store_id, trigger)`. `automation_sends` `{automation_id, customer_id, sent_at}` unique `(automation_id, customer_id)`. `AutomationService::runDue(): int` (messages sent across all stores). Command `automations:run`, scheduled hourly.
- Consumed by: Task 2 (UI), plan gating via new `PlanFeature::AUTOMATIONS` (this task adds it, ENTERPRISE only).

- [ ] **Step 1: Failing tests**

```php
<?php

namespace Tests\Feature\Marketing;

use App\Models\Automation;
use App\Models\Customer;
use App\Models\MessagingSetting;
use App\Models\Store;
use App\Services\AutomationService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Set-and-forget automations: consent-filtered, once per customer, honest channels. */
class AutomationTest extends TestCase
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

    private function customer(array $attrs = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'store_id' => $this->store->id, 'email' => fake()->unique()->safeEmail(),
            'marketing_consent' => true, 'total_orders' => 1,
        ], $attrs));
    }

    public function test_welcome_fires_once_for_recent_first_time_customers(): void
    {
        Automation::create([
            'store_id' => $this->store->id, 'trigger' => 'welcome', 'channel' => 'email',
            'enabled' => true, 'config' => ['subject' => 'Welcome!', 'body' => 'Thanks for your first order.'],
        ]);
        $c = $this->customer(); // created just now, 1 order

        $this->assertSame(1, app(AutomationService::class)->runDue());
        // Second run: the ledger blocks a repeat.
        $this->assertSame(0, app(AutomationService::class)->runDue());
        $this->assertDatabaseHas('automation_sends', ['customer_id' => $c->id]);
    }

    public function test_welcome_skips_unconsented_and_zero_order_customers(): void
    {
        Automation::create([
            'store_id' => $this->store->id, 'trigger' => 'welcome', 'channel' => 'email',
            'enabled' => true, 'config' => ['subject' => 'W', 'body' => 'B'],
        ]);
        $this->customer(['marketing_consent' => false]);
        $this->customer(['total_orders' => 0]);

        $this->assertSame(0, app(AutomationService::class)->runDue());
    }

    public function test_winback_fires_for_customers_quiet_past_the_threshold(): void
    {
        Automation::create([
            'store_id' => $this->store->id, 'trigger' => 'winback', 'channel' => 'email',
            'enabled' => true, 'config' => ['subject' => 'We miss you', 'body' => 'Come back!', 'winback_days' => 30],
        ]);
        $quiet = $this->customer();
        // Their latest order is 45 days old.
        \App\Models\Order::factory()->create([
            'store_id' => $this->store->id, 'customer_id' => $quiet->id,
            'placed_at' => now()->subDays(45), 'created_at' => now()->subDays(45),
        ]);
        $active = $this->customer();
        \App\Models\Order::factory()->create([
            'store_id' => $this->store->id, 'customer_id' => $active->id,
            'placed_at' => now()->subDays(3), 'created_at' => now()->subDays(3),
        ]);

        $this->assertSame(1, app(AutomationService::class)->runDue());
        $this->assertDatabaseHas('automation_sends', ['customer_id' => $quiet->id]);
        $this->assertDatabaseMissing('automation_sends', ['customer_id' => $active->id]);
    }

    public function test_disabled_automation_does_nothing(): void
    {
        Automation::create([
            'store_id' => $this->store->id, 'trigger' => 'welcome', 'channel' => 'email',
            'enabled' => false, 'config' => ['subject' => 'W', 'body' => 'B'],
        ]);
        $this->customer();

        $this->assertSame(0, app(AutomationService::class)->runDue());
    }
}
```

- [ ] **Step 2: Verify failure**, then implement.

Migration:

```php
Schema::create('automations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('store_id')->constrained()->cascadeOnDelete();
    $table->string('trigger');            // welcome | winback
    $table->string('channel')->default('email');
    $table->boolean('enabled')->default(false);
    $table->json('config');               // {subject?, body, winback_days?, wa_template_id?, wa_parameters?}
    $table->timestamps();
    $table->unique(['store_id', 'trigger']);
});

Schema::create('automation_sends', function (Blueprint $table) {
    $table->id();
    $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->timestamp('sent_at');
    $table->unique(['automation_id', 'customer_id']);
});
```

`Automation.php` (BelongsToStore; fillable store_id/trigger/channel/enabled/config; casts enabled bool + config array; `sends()` HasMany). `AutomationSend.php` (no store trait — scoped through automation; `$timestamps = false`; fillable automation_id/customer_id/sent_at; cast sent_at datetime).

`AutomationService.php`:

```php
<?php

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\Automation;
use App\Models\AutomationSend;
use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Set-and-forget marketing automations. Runs hourly across all stores (no
 * store context — the global scope no-ops; every query below constrains by
 * the automation's own store). Consent-filtered; the automation_sends ledger
 * guarantees once-per-customer-per-trigger; channels are the same honest
 * pipeline campaigns use.
 */
class AutomationService
{
    public function __construct(
        private readonly MessagingService $messaging,
        private readonly SmsSender $sms,
    ) {}

    public function runDue(): int
    {
        $sent = 0;

        foreach (Automation::query()->where('enabled', true)->get() as $automation) {
            foreach ($this->audience($automation) as $customer) {
                if ($this->deliver($automation, $customer)) {
                    AutomationSend::create([
                        'automation_id' => $automation->id,
                        'customer_id' => $customer->id,
                        'sent_at' => now(),
                    ]);
                    $sent++;
                }
            }
        }

        return $sent;
    }

    /** @return \Illuminate\Support\Collection<int, Customer> */
    private function audience(Automation $automation): \Illuminate\Support\Collection
    {
        $base = Customer::withoutGlobalScopes()
            ->where('store_id', $automation->store_id)
            ->where('marketing_consent', true)
            ->whereNotIn('id', AutomationSend::where('automation_id', $automation->id)->select('customer_id'));

        return match ($automation->trigger) {
            // First order placed within the last 7 days (fresh enough to greet).
            'welcome' => $base->where('total_orders', '>=', 1)
                ->where('created_at', '>=', now()->subDays(7))
                ->get(),
            // Has ordered before, but their LATEST order is older than the threshold.
            'winback' => $base->where('total_orders', '>=', 1)
                ->whereDoesntHave('orders', fn ($q) => $q->where('created_at', '>=', now()->subDays((int) ($automation->config['winback_days'] ?? 30))))
                ->get(),
            default => collect(),
        };
    }

    private function deliver(Automation $automation, Customer $customer): bool
    {
        $store = $automation->store;
        $config = $automation->config;

        try {
            if ($automation->channel === 'email') {
                if (empty($customer->email)) {
                    return false;
                }
                $channel = $this->messaging->emailChannel($store);
                if ($channel === null) {
                    return false;
                }
                // Reuse the campaign mailable via a transient, unsaved campaign shell.
                $shell = new Campaign(['subject' => $config['subject'] ?? 'A message from '.$store->name, 'body' => $config['body']]);
                Mail::mailer($channel['mailer'])->to($customer->email)
                    ->send(new CampaignMail($shell, $channel['from_address'], $channel['from_name'], $customer->id));

                return true;
            }

            if ($automation->channel === 'sms') {
                $settings = $this->messaging->settings($store);

                return ! empty($customer->phone) && $settings->smsReady()
                    && $this->sms->send($customer->phone, $config['body'], $settings->sms_sender_id);
            }

            if ($automation->channel === 'whatsapp') {
                if (empty($customer->phone) || ! $this->messaging->settings($store)->whatsappReady()) {
                    return false;
                }
                $shell = new Campaign([
                    'subject' => $config['subject'] ?? '', 'body' => $config['body'],
                    'wa_template_id' => $config['wa_template_id'] ?? null,
                    'wa_parameters' => $config['wa_parameters'] ?? null,
                ]);
                $shell->setRelation('waTemplate', isset($config['wa_template_id'])
                    ? \App\Models\WhatsappTemplate::withoutGlobalScopes()->find($config['wa_template_id']) : null);

                return $this->messaging->sendWhatsapp($store, $customer->phone, $shell);
            }
        } catch (\Throwable $e) {
            Log::warning("Automation {$automation->id} → customer {$customer->id} failed: {$e->getMessage()}");
        }

        return false;
    }
}
```

> ⚠️ The unsaved `Campaign` shell: `CampaignMail` only reads `subject`/`body` — safe. `sendWhatsapp` reads `wa_template_id`/`waTemplate`/`wa_parameters`/`wa_template_name` — the shell covers all; DO NOT call `save()` on it.

`RunAutomations.php` command (`automations:run`, description "Send due marketing automations (welcome, win-back)") calling `runDue()`; schedule `Schedule::command('automations:run')->hourly();` in `routes/console.php` with a comment.

Add `PlanFeature::AUTOMATIONS` (toggle, group Growth, label "Marketing automations", description "Welcome and win-back messages that send themselves.", enforced true) — grant in `DefaultPlans` to **ENTERPRISE only**. Gate the CRUD endpoints (Task 2), not the runner (existing rows keep working if a store downgrades? No — ALSO check in `runDue`: skip automations whose store lacks the feature: `if (! app(PlanGate::class)->allows($automation->store, PlanFeature::AUTOMATIONS)) continue;` — add this line and a test asserting a FREE store's enabled automation does not fire).

- [ ] **Step 3: Tests pass; migrate; commit** — `feat(automations): welcome + win-back engine with once-per-customer ledger`.

---

### Task 2: Automations API + Automations tab (toggle cards)

**Files:**
- Create: `backend/app/Http/Controllers/Api/AutomationController.php`
- Modify: `backend/routes/api.php`, `src/api/endpoints.ts`, `src/pages/marketing/MarketingPage.tsx` (new tab AUTOMATIONS → feature AUTOMATIONS), Create: `src/pages/marketing/tabs/AutomationsTab.tsx`
- Test: `backend/tests/Feature/Marketing/AutomationApiTest.php`

**Interfaces:**
- Produces: `GET /api/v1/automations` → `{automations: [{trigger, channel, enabled, config, sent_total}]}` (always returns both triggers, defaults when missing); `PUT /api/v1/automations/{trigger}` body `{channel, enabled, config}` (validates trigger in welcome|winback; PlanGate AUTOMATIONS; settings.manage).

- [ ] **Step 1: Failing tests** — `test_free_plan_cannot_configure_automations` (402), `test_enterprise_configures_welcome` (PUT → GET round-trip with `sent_total` 0), `test_put_rejects_unknown_trigger` (404 or validation 422 — choose 422 via route constraint `whereIn` is cleaner: use `->whereIn('trigger', ['welcome','winback'])` and assert 404 for bogus).

- [ ] **Step 2: Implement** controller (full class skeleton — constructor mirrors every other gated controller):

```php
<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Services\PlanGate;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Marketing automations config (premium — PlanFeature::AUTOMATIONS). */
class AutomationController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
    ) {}
    // …methods below…
}
```

```php
public function index(): JsonResponse
{
    $existing = Automation::query()->get()->keyBy('trigger');
    $rows = collect(['welcome', 'winback'])->map(fn ($trigger) => [
        'trigger' => $trigger,
        'channel' => $existing[$trigger]->channel ?? 'email',
        'enabled' => $existing[$trigger]->enabled ?? false,
        'config' => $existing[$trigger]->config ?? ['body' => '', 'winback_days' => 30],
        'sent_total' => isset($existing[$trigger]) ? $existing[$trigger]->sends()->count() : 0,
    ]);

    return response()->json(['automations' => $rows]);
}

public function update(Request $request, string $trigger): JsonResponse
{
    $store = $this->context->store();
    $this->plans->ensure($store, PlanFeature::AUTOMATIONS);
    abort_unless($request->user()->can('settings.manage'), 403);

    $data = $request->validate([
        'channel' => ['required', 'in:email,whatsapp,sms'],
        'enabled' => ['required', 'boolean'],
        'config' => ['required', 'array'],
        'config.subject' => ['nullable', 'string', 'max:200'],
        'config.body' => ['required', 'string', 'max:2000'],
        'config.winback_days' => ['nullable', 'integer', 'min:3', 'max:365'],
        'config.wa_template_id' => ['nullable', 'integer'],
        'config.wa_parameters' => ['nullable', 'array'],
    ]);

    Automation::updateOrCreate(
        ['store_id' => $store->id, 'trigger' => $trigger],
        ['channel' => $data['channel'], 'enabled' => $data['enabled'], 'config' => $data['config']],
    );

    return $this->index();
}
```

Routes: `Route::get('automations', …index); Route::put('automations/{trigger}', …update)->whereIn('trigger', ['welcome', 'winback']);`

`AutomationsTab.tsx`: two large cards (`mk-card-lift`), one per trigger — icon (`sparkles` welcome / `refresh` winback), title ("Welcome new customers" / "Win back quiet customers"), one-line explainer, big `ToggleSwitch`, channel `SelectInput` (options from messaging status: only ready channels enabled), subject+body inputs (body textarea), win-back adds "days quiet" number input, footer stat `Sent {sent_total} so far` with `mk-live-dot` when enabled. Save per card. Add `AUTOMATIONS` to `Tab` union / `TAB_FEATURE` (→ `'AUTOMATIONS'`) / `TAB_META` (icon `sparkles`, lockedCopy "Welcome and win-back messages that send themselves — your store follows up while you sleep.") / render switch. Add `automationsApi = { list, update(trigger, payload) }` in endpoints.ts.

- [ ] **Step 3: Verify (tests + tsc) & commit** — `feat(automations): config API + Automations tab with toggle cards`.

---

### Task 3: Campaign revenue attribution (auto-coupon)

**Files:**
- Create: `backend/database/migrations/2026_07_12_000007_add_coupon_link_to_campaigns.php`
- Modify: `backend/app/Models/Campaign.php`, `backend/app/Services/CampaignService.php`, `backend/app/Http/Controllers/Api/CampaignController.php`
- Modify: `src/pages/marketing/tabs/CampaignsTab.tsx`
- Test: `backend/tests/Feature/Marketing/CampaignAttributionTest.php`

**Interfaces:**
- Produces: `campaigns.coupon_id` (nullable FK, nullOnDelete); create option `attach_coupon: {type: 'PERCENT'|'FIXED', value: number} | null` → auto-creates coupon coded `CAMP-{random6}` linked to the campaign, and appends the code line to the outgoing body (`"\n\nUse code {CODE} at checkout."`); campaign index rows gain `attributed: {orders, revenue, discount_given} | null` from `CouponService::stats`.

- [ ] **Step 1: Failing tests** — `test_campaign_can_auto_attach_a_tracking_coupon` (POST /campaigns with `attach_coupon {PERCENT, 10}` → coupon row exists, campaign.coupon_id set, code starts `CAMP-`); `test_campaign_index_reports_attributed_revenue` (place an order with the campaign's code — reuse the coupon-attribution order flow from `CouponAttributionTest` — then GET /campaigns → `campaigns.0.attributed.orders === 1`); `test_attach_coupon_requires_coupons_feature` (store on a plan with CAMPAIGNS but without COUPONS → 402; construct via a custom plan or assert with FREE→402 for campaigns anyway — simplest: assert PlanGate::ensure(COUPONS) is called by giving ENTERPRISE minus nothing… skip the plan-matrix nuance and instead assert the coupon path calls `CouponService::create` which is already COUPONS-gated: write the test with ENTERPRISE and just verify the happy path + document the gate).

- [ ] **Step 2: Implement** — migration adds `coupon_id` FK after `wa_parameters`. `CampaignService::create` after building `$campaign`:

```php
if (! empty($data['attach_coupon'])) {
    $coupon = app(CouponService::class)->create($store, [
        'code' => 'CAMP-'.strtoupper(\Illuminate\Support\Str::random(6)),
        'type' => $data['attach_coupon']['type'],
        'value' => $data['attach_coupon']['value'],
    ]);
    $campaign->update([
        'coupon_id' => $coupon->id,
        'body' => $campaign->body."\n\nUse code {$coupon->code} at checkout.",
    ]);
}
```

Controller validation: `'attach_coupon' => ['nullable', 'array'], 'attach_coupon.type' => ['required_with:attach_coupon', 'in:PERCENT,FIXED'], 'attach_coupon.value' => ['required_with:attach_coupon', 'numeric', 'min:0'],`. Index: eager `->with('coupon')` (add `coupon()` BelongsTo on Campaign) and per row `'attributed' => $c->coupon ? app(CouponService::class)->stats($c->coupon) : null,`.
Frontend `CampaignsTab.tsx`: composer gains a "Track revenue with an auto-coupon" checkbox revealing type+value inputs (payload `attach_coupon`); history table "Delivered" column becomes "Results": when `attributed` present render `<b>{money(attributed.revenue)}</b> · {attributed.orders} orders` under the delivered count.

- [ ] **Step 3: Verify & commit** — `feat(campaigns): auto-coupon revenue attribution per campaign`.

---

### Task 4: Email monthly quota as a plan limit

**Files:**
- Create: `backend/database/migrations/2026_07_12_000008_add_email_quota_counters_to_messaging_settings.php` (`email_sent_this_month` unsigned int default 0, `email_period_started_at` nullable timestamp)
- Modify: `backend/app/Enums/PlanFeature.php` (case `EMAIL_SENDS_LIMIT`, **limit kind** — add to the `isLimit()` match! — label "Campaign emails / month", group Growth, enforced true, unit "emails"), `backend/app/Support/DefaultPlans.php` (PRO: `EMAIL_SENDS_LIMIT => 500`; ENTERPRISE: absent = unlimited; FREE unchanged — no CAMPAIGNS anyway), `backend/app/Models/MessagingSetting.php`, `backend/app/Services/CampaignService.php` (`sendEmail`)
- Test: `backend/tests/Feature/Marketing/EmailQuotaTest.php`

**Interfaces:**
- Produces: email sends consume the monthly counter; when the cap is reached remaining recipients count as `failed` (mirror the SMS quota semantics exactly, including lazy monthly reset); `MessagingService::status()` email block gains `'quota' => ?int, 'used' => int` (quota = `PlanGate::limit($store, EMAIL_SENDS_LIMIT)`).

- [ ] **Step 1: Failing tests** — `test_pro_store_stops_at_the_email_quota` (assign PRO, override quota by seeding? No — PRO default 500 is too big to exercise; instead directly set the plan row: `\App\Models\PlanFeatureLimit::updateOrCreate(['plan_id' => $proId, 'feature' => PlanFeature::EMAIL_SENDS_LIMIT], ['limit_value' => 2])` then 3 consented customers → sent 2 failed 1, counter 2); `test_enterprise_is_unlimited` (no limit row → all send); `test_quota_resets_monthly` (counter 500, period 2 months old → next send succeeds, counter 1).

- [ ] **Step 2: Implement** mirroring `sendSms` exactly: lazy reset block, `$withinQuota` check using `app(PlanGate::class)->limit($campaign->store, PlanFeature::EMAIL_SENDS_LIMIT)` (null = unlimited), increment after loop. **Gotcha:** `PlanGate` memoizes per store per request — fine in tests since limit rows are written before the first gate call; if a test mutates limits after a gate call, call `app(PlanGate::class)->forget($store)`.

- [ ] **Step 3: Verify & commit** — `feat(quotas): monthly email send quota as a plan limit`.

---

### Task 5: Full-suite gate + upgrade-story walk

- [ ] `php artisan test` green (minus the 2 known); `npx tsc --noEmit` = 0.
- [ ] Browser: Automations tab (Enterprise store) — enable Welcome (email), tinker a fresh consented customer with 1 order, run `php artisan automations:run`, see `Sent 1 so far`; create a campaign with auto-coupon, place an order with its code, watch the campaign row show attributed revenue; downgrade the store to PRO in tinker → Automations tab shows the locked upgrade panel. Screenshots.
- [ ] Commit fixes; `feat(marketing): automations + attribution + quotas phase complete`.

# Phase 2 — Platform-Managed SMS Channel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. **Read `2026-07-12-marketing-00-overview.md` § Global Constraints first.** Phase 1 (consent) must already be merged — SMS sends are consent-filtered.

**Goal:** SMS becomes a campaign channel that the **platform** (super admin) powers and governs: one centrally-configured SMS provider; merchants *request* access from the Channels tab; the super admin *grants* per store with a sender ID and monthly quota. Merchants never see provider credentials.

**Architecture:** A singleton `platform_sms_settings` row holds the provider config (driver abstraction: `log` for dev, `http` generic JSON-POST for real aggregators — covers Twilio-compatible and local Qatari aggregators without per-vendor SDKs). `SmsSender` is the only send path and returns honest booleans. Per-store state lives on the existing `messaging_settings` row (`sms_status`, `sms_sender_id`, quota counters). `CampaignService` gains the `sms` channel behind the same refuse-if-unconfigured rule as WhatsApp/email. Platform UI = a new "SMS" tab in `PlatformPage.tsx` mirroring the existing "Delivery providers" tab pattern; merchant UI = an SMS card in the Marketing → Channels tab.

**Tech Stack:** Laravel 12, React 18 + TS. No new packages.

**Existing anchors (verified):**
- Platform routes: `Route::prefix('v1/platform')->middleware(['auth:sanctum', 'platform.admin'])` in `backend/routes/api.php:191`; controllers in `backend/app/Http/Controllers/Api/Platform/` (open `PlatformDeliveryProviderController` and mirror its shape); UI `src/pages/platform/PlatformPage.tsx` (tab sections marked `// ─── X tab ───`, `TABS` array ~line 793).
- Merchant channel state: `backend/app/Models/MessagingSetting.php` + `MessagingSettingsController` (`show` masks secrets, `update` keeps blank secrets).
- Campaign send pipeline: `backend/app/Services/CampaignService.php` — `send()` routes per channel; each channel returns `[sent, failed]`; refusal = `DomainConflictException('CHANNEL_NOT_CONFIGURED', …)`.
- Consent filter: `CampaignService::audienceFor` (Phase 1) already returns only consented customers.
- Platform admin test helper: check `backend/tests/Feature/Platform/*` for how a platform admin user is created/acting (there is an `is_platform_admin` flag on users and a `platform.admin` middleware) — copy that setup verbatim.

---

### Task 1: Platform SMS settings + SmsSender (driver abstraction)

**Files:**
- Create: `backend/database/migrations/2026_07_12_000002_create_platform_sms_settings_table.php`
- Create: `backend/app/Models/PlatformSmsSetting.php`
- Create: `backend/app/Services/SmsSender.php`
- Test: `backend/tests/Feature/Platform/PlatformSmsSettingsTest.php` (service-level part)

**Interfaces:**
- Produces: `PlatformSmsSetting::current(): self` (singleton row, id always 1); `SmsSender::configured(): bool`; `SmsSender::send(string $toPhone, string $message, ?string $senderId): bool`.
- Consumed by: Task 3 (campaign channel), Task 2 (platform test-send endpoint).

- [ ] **Step 1: Failing test**

```php
<?php

namespace Tests\Feature\Platform;

use App\Models\PlatformSmsSetting;
use App\Services\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** One platform-wide SMS provider behind a driver abstraction; honest sends. */
class PlatformSmsSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfigured_sender_reports_not_configured_and_refuses(): void
    {
        $sender = app(SmsSender::class);

        $this->assertFalse($sender->configured());
        $this->assertFalse($sender->send('+97455500001', 'hi', 'DORZAK'));
    }

    public function test_log_driver_is_configured_and_succeeds_without_network(): void
    {
        PlatformSmsSetting::current()->update(['driver' => 'log', 'enabled' => true]);

        $sender = app(SmsSender::class);
        $this->assertTrue($sender->configured());
        $this->assertTrue($sender->send('+97455500001', 'hi', 'DORZAK'));
    }

    public function test_http_driver_posts_the_templated_payload(): void
    {
        Http::fake(['sms.example.com/*' => Http::response(['ok' => true], 200)]);
        PlatformSmsSetting::current()->update([
            'driver' => 'http', 'enabled' => true,
            'config' => [
                'url' => 'https://sms.example.com/send',
                'headers' => ['Authorization' => 'Bearer sk-123'],
                'body' => ['to' => ':to', 'from' => ':sender', 'text' => ':message'],
            ],
        ]);

        $ok = app(SmsSender::class)->send('+974 5550 0001', '20% off today', 'DORZAK');

        $this->assertTrue($ok);
        Http::assertSent(fn ($req) => $req->url() === 'https://sms.example.com/send'
            && $req['to'] === '97455500001'      // digits only
            && $req['from'] === 'DORZAK'
            && $req['text'] === '20% off today'
            && $req->hasHeader('Authorization', 'Bearer sk-123'));
    }

    public function test_http_driver_failure_returns_false(): void
    {
        Http::fake(['sms.example.com/*' => Http::response(['error' => 'no credit'], 402)]);
        PlatformSmsSetting::current()->update([
            'driver' => 'http', 'enabled' => true,
            'config' => ['url' => 'https://sms.example.com/send', 'body' => ['to' => ':to', 'text' => ':message']],
        ]);

        $this->assertFalse(app(SmsSender::class)->send('+97455500001', 'hi', null));
    }
}
```

- [ ] **Step 2: Verify failure** — `php artisan test tests/Feature/Platform/PlatformSmsSettingsTest.php` → FAIL (table/class missing).

- [ ] **Step 3: Implement**

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The platform's single SMS provider (super-admin managed). Merchants never
 * see this — they are granted per-store access with a sender ID and quota.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider_name')->nullable();   // display only, e.g. "Ooredoo Bulk SMS"
            $table->string('driver')->default('log');      // log | http
            $table->text('config')->nullable();            // encrypted JSON: {url, headers{}, body{} with :to/:message/:sender placeholders}
            $table->string('default_sender_id')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_sms_settings');
    }
};
```

`PlatformSmsSetting.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Singleton row (always id 1) holding the platform's SMS provider config. */
class PlatformSmsSetting extends Model
{
    protected $fillable = ['provider_name', 'driver', 'config', 'default_sender_id', 'enabled'];

    protected function casts(): array
    {
        return ['config' => 'encrypted:array', 'enabled' => 'boolean'];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
```

`SmsSender.php`:

```php
<?php

namespace App\Services;

use App\Models\PlatformSmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The ONLY path that sends SMS. Platform-managed provider behind a driver:
 *  - log:  dev driver — records the message, reports success.
 *  - http: generic JSON POST with :to / :message / :sender placeholders, so
 *          Twilio-compatible APIs and local aggregators work without SDKs.
 * Returns an honest boolean; unconfigured/disabled refuses (false).
 */
class SmsSender
{
    public function configured(): bool
    {
        $s = PlatformSmsSetting::current();

        return $s->enabled && ($s->driver === 'log' || ! empty($s->config['url']));
    }

    public function send(string $toPhone, string $message, ?string $senderId): bool
    {
        if (! $this->configured()) {
            return false;
        }

        $s = PlatformSmsSetting::current();
        $to = preg_replace('/\D/', '', $toPhone);
        $from = $senderId ?: $s->default_sender_id;

        if ($s->driver === 'log') {
            Log::info('[sms:log-driver] send', ['to' => $to, 'from' => $from, 'message' => $message]);

            return true;
        }

        $replace = fn ($v) => is_string($v)
            ? strtr($v, [':to' => $to, ':message' => $message, ':sender' => (string) $from])
            : $v;

        $body = array_map($replace, $s->config['body'] ?? []);
        $headers = array_map($replace, $s->config['headers'] ?? []);

        try {
            return Http::withHeaders($headers)->post($s->config['url'], $body)->successful();
        } catch (\Throwable $e) {
            Log::warning("SMS send failed: {$e->getMessage()}");

            return false;
        }
    }
}
```

- [ ] **Step 4: Run tests** → PASS. `php artisan migrate` on dev DB.

- [ ] **Step 5: Commit** — `feat(sms): platform SMS provider settings + SmsSender driver abstraction`.

---

### Task 2: Platform endpoints + Platform UI tab (configure provider, grants, requests)

**Files:**
- Create: `backend/app/Http/Controllers/Api/Platform/PlatformSmsController.php`
- Modify: `backend/routes/api.php` (platform group), `backend/database/migrations/2026_07_12_000003_add_sms_grant_to_messaging_settings.php` (new migration), `backend/app/Models/MessagingSetting.php`
- Modify: `src/api/endpoints.ts` (`platformApi` — find the existing platform api object and extend), `src/pages/platform/PlatformPage.tsx` (new "SMS" tab)
- Test: `backend/tests/Feature/Platform/PlatformSmsGovernanceTest.php`

**Interfaces:**
- Produces:
  - Migration adds to `messaging_settings`: `sms_status` (string default `'none'`; values `none|requested|enabled`), `sms_sender_id` (nullable string), `sms_monthly_quota` (nullable unsigned int; null = unlimited), `sms_sent_this_month` (unsigned int default 0), `sms_period_started_at` (nullable timestamp).
  - `MessagingSetting::smsReady(): bool` → `sms_status === 'enabled'` AND `app(SmsSender::class)->configured()`.
  - Platform API: `GET /api/v1/platform/sms/settings` (masked: config → `has_config` bool + url only), `PUT /api/v1/platform/sms/settings`, `POST /api/v1/platform/sms/test {phone}`, `GET /api/v1/platform/sms/stores` (every store with sms_status != none → `{store_id, store_name, sms_status, sms_sender_id, sms_monthly_quota, sms_sent_this_month}`), `POST /api/v1/platform/sms/grant {store_id, sender_id, monthly_quota?}`, `POST /api/v1/platform/sms/revoke {store_id}`.
- Consumed by: Task 3 (smsReady + quota), Task 4 (merchant request flow).

- [ ] **Step 1: Failing test** (copy the platform-admin acting pattern from an existing `tests/Feature/Platform/*` test — do not invent):

```php
public function test_platform_admin_configures_provider_and_secrets_are_masked(): void
// PUT settings {provider_name, driver http, config{url, headers{Authorization}, body{...}}, default_sender_id, enabled true}
// → 200; GET settings → has_config true, config null, url echoed separately, enabled true.

public function test_platform_admin_grants_and_revokes_sms_for_a_store(): void
// store with messaging_settings sms_status 'requested'
// POST grant {store_id, sender_id 'DORZAK', monthly_quota 500} → 200
// → messaging_settings sms_status 'enabled', sender id + quota persisted
// POST revoke {store_id} → sms_status 'none'

public function test_non_platform_admin_is_rejected(): void  // 403 on every route above

public function test_test_send_uses_the_provider(): void
// driver log + enabled; POST /platform/sms/test {phone} → 200 {ok true}
```

- [ ] **Step 2: Verify failure**, then implement.

Migration `2026_07_12_000003_add_sms_grant_to_messaging_settings.php`:

```php
Schema::table('messaging_settings', function (Blueprint $table) {
    $table->string('sms_status')->default('none')->after('whatsapp_error'); // none | requested | enabled
    $table->string('sms_sender_id')->nullable()->after('sms_status');
    $table->unsignedInteger('sms_monthly_quota')->nullable()->after('sms_sender_id');
    $table->unsignedInteger('sms_sent_this_month')->default(0)->after('sms_monthly_quota');
    $table->timestamp('sms_period_started_at')->nullable()->after('sms_sent_this_month');
});
```

`MessagingSetting.php`: add the five fields to `$fillable`; casts `'sms_monthly_quota' => 'integer', 'sms_sent_this_month' => 'integer', 'sms_period_started_at' => 'datetime'`; add:

```php
public function smsReady(): bool
{
    return $this->sms_status === 'enabled' && app(\App\Services\SmsSender::class)->configured();
}
```

`PlatformSmsController.php` (mirror auth/shape of `PlatformDeliveryProviderController`):

```php
<?php

namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\MessagingSetting;
use App\Models\PlatformSmsSetting;
use App\Services\SmsSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Super-admin governance of the platform SMS channel: provider config + per-store grants. */
class PlatformSmsController extends Controller
{
    public function settings(): JsonResponse
    {
        $s = PlatformSmsSetting::current();

        return response()->json(['sms' => [
            'provider_name' => $s->provider_name,
            'driver' => $s->driver,
            'url' => $s->config['url'] ?? null,
            'has_config' => ! empty($s->config),
            'default_sender_id' => $s->default_sender_id,
            'enabled' => $s->enabled,
        ]]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider_name' => ['nullable', 'string', 'max:120'],
            'driver' => ['required', 'in:log,http'],
            'config' => ['nullable', 'array'],
            'config.url' => ['required_if:driver,http', 'url'],
            'config.headers' => ['nullable', 'array'],
            'config.body' => ['nullable', 'array'],
            'default_sender_id' => ['nullable', 'string', 'max:20'],
            'enabled' => ['required', 'boolean'],
        ]);

        $s = PlatformSmsSetting::current();
        if (! array_key_exists('config', $data) || $data['config'] === null) {
            unset($data['config']); // blank = keep stored provider secrets
        }
        $s->update($data);

        return $this->settings();
    }

    public function test(Request $request, SmsSender $sender): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);
        $ok = $sender->send($data['phone'], 'Dorzak platform SMS test — your provider is connected.', null);

        return response()->json(['ok' => $ok], $ok ? 200 : 422);
    }

    public function stores(): JsonResponse
    {
        $rows = MessagingSetting::query()->where('sms_status', '!=', 'none')
            ->with('store:id,name')->get()
            ->map(fn (MessagingSetting $m) => [
                'store_id' => $m->store_id, 'store_name' => $m->store?->name,
                'sms_status' => $m->sms_status, 'sms_sender_id' => $m->sms_sender_id,
                'sms_monthly_quota' => $m->sms_monthly_quota, 'sms_sent_this_month' => $m->sms_sent_this_month,
            ]);

        return response()->json(['stores' => $rows]);
    }

    public function grant(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'sender_id' => ['required', 'string', 'max:20'],
            'monthly_quota' => ['nullable', 'integer', 'min:1'],
        ]);

        MessagingSetting::firstOrCreate(['store_id' => $data['store_id']])->update([
            'sms_status' => 'enabled',
            'sms_sender_id' => $data['sender_id'],
            'sms_monthly_quota' => $data['monthly_quota'] ?? null,
            'sms_sent_this_month' => 0,
            'sms_period_started_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function revoke(Request $request): JsonResponse
    {
        $data = $request->validate(['store_id' => ['required', 'integer']]);
        MessagingSetting::where('store_id', $data['store_id'])->update(['sms_status' => 'none']);

        return response()->json(['ok' => true]);
    }
}
```

> ⚠️ `MessagingSetting` uses `BelongsToStore` — in platform context there is no store, so the global scope no-ops and cross-tenant queries here are intentional. `store()` BelongsTo exists via the trait. `MessagingSetting::firstOrCreate(['store_id' => …])` works because store_id is explicit.

Routes (inside the platform group):

```php
Route::get('sms/settings', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'settings']);
Route::put('sms/settings', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'update']);
Route::post('sms/test', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'test']);
Route::get('sms/stores', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'stores']);
Route::post('sms/grant', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'grant']);
Route::post('sms/revoke', [\App\Http\Controllers\Api\Platform\PlatformSmsController::class, 'revoke']);
```

Platform UI — `PlatformPage.tsx`: add `'SMS'` to the `Tab` union + `TABS` array; add a `// ─── SMS tab ───` section component styled exactly like the Delivery-providers tab: (a) provider card — provider name, driver select (log = "Test mode (log only)" / http = "HTTP provider"), URL, headers JSON textarea, body JSON textarea with helper text "use :to, :message, :sender placeholders", default sender ID, enabled toggle, Save + "Send test SMS" (phone input); (b) grants table (from `sms/stores`) — store, status pill (Requested amber / Enabled green), sender ID, quota usage `used/quota`, actions: Grant (opens inline sender-ID + quota inputs) / Revoke. Extend `src/api/endpoints.ts` platform API object with the six calls.

- [ ] **Step 3: Verify** — backend tests PASS; tsc 0.
- [ ] **Step 4: Commit** — `feat(sms): platform SMS governance — provider config, per-store grants, test send`.

---

### Task 3: SMS as a campaign channel (quota-enforced, honest counts)

**Files:**
- Modify: `backend/app/Services/CampaignService.php`, `backend/app/Http/Controllers/Api/CampaignController.php` (channel validation `in:email,whatsapp,sms`)
- Modify: `backend/app/Services/MessagingService.php` (expose sms status in `status()`)
- Test: `backend/tests/Feature/Campaign/SmsCampaignTest.php`

**Interfaces:**
- Produces: campaigns accept `channel = 'sms'`; `MessagingService::status()` gains `'sms' => ['ready' => bool, 'status' => string, 'sender_id' => ?string, 'quota' => ?int, 'used' => int]`.
- Consumes: `SmsSender::send`, `MessagingSetting::smsReady()`, consent-filtered `audienceFor`.

- [ ] **Step 1: Failing tests**

```php
public function test_sms_campaign_refuses_when_store_not_granted(): void
// consented customer w/ phone; campaign channel sms; POST /campaigns/{id}/send → 409 CHANNEL_NOT_CONFIGURED

public function test_granted_store_sends_sms_to_consented_phone_customers(): void
// PlatformSmsSetting log driver enabled; messaging_settings sms_status enabled, sender 'DORZAK', quota 100
// 2 consented customers with phones + 1 without consent
// send() → sent_count 2, failed_count 0, sms_sent_this_month 2

public function test_quota_exhaustion_stops_sending_and_counts_failures(): void
// quota 1, 2 consented phone customers → sent 1, failed 1 (quota), sms_sent_this_month 1

public function test_monthly_quota_resets_lazily(): void
// sms_sent_this_month 99, sms_period_started_at 2 months ago, quota 100
// send to 1 customer → succeeds; sms_sent_this_month == 1 (fresh period)
```

- [ ] **Step 2: Implement** — in `CampaignService::send()` add the branch BEFORE the email default:

```php
} elseif ($campaign->channel === 'sms') {
    $settings = $this->messaging->settings($store);
    if (! $settings->smsReady()) {
        throw new DomainConflictException('CHANNEL_NOT_CONFIGURED', 'SMS is not enabled for this store. Request access in Marketing → Channels.');
    }
    [$sent, $failed] = $this->sendSms($campaign, $settings);
}
```

And the sender (quota is lazy-reset + consumed inside; body only — SMS has no subject):

```php
/** @return array{0:int, 1:int} */
private function sendSms(Campaign $campaign, \App\Models\MessagingSetting $settings): array
{
    // Lazy monthly reset.
    if ($settings->sms_period_started_at === null || $settings->sms_period_started_at->lt(now()->startOfMonth())) {
        $settings->update(['sms_sent_this_month' => 0, 'sms_period_started_at' => now()]);
    }

    $sender = app(\App\Services\SmsSender::class);
    $sent = 0;
    $failed = 0;

    foreach ($this->audienceFor($campaign) as $customer) {
        if (empty($customer->phone)) {
            continue;
        }

        $withinQuota = $settings->sms_monthly_quota === null
            || ($settings->sms_sent_this_month + $sent) < $settings->sms_monthly_quota;

        if ($withinQuota && $sender->send($customer->phone, $campaign->body, $settings->sms_sender_id)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    if ($sent > 0) {
        $settings->increment('sms_sent_this_month', $sent);
    }

    return [$sent, $failed];
}
```

`CampaignController::store` validation: `'channel' => ['nullable', 'in:email,whatsapp,sms'],`. `CampaignService::create` channel normalization: replace the ternary with `in_array($data['channel'] ?? 'email', ['whatsapp', 'sms'], true) ? $data['channel'] : 'email'`.

`MessagingService::status()` add:

```php
'sms' => [
    'ready' => $s->smsReady(),
    'status' => $s->sms_status,               // none | requested | enabled
    'sender_id' => $s->sms_sender_id,
    'quota' => $s->sms_monthly_quota,
    'used' => $s->sms_sent_this_month,
],
```

- [ ] **Step 3: Verify** — new tests + `tests/Feature/Campaign` + `tests/Feature/Marketing` all pass.
- [ ] **Step 4: Commit** — `feat(sms): SMS campaign channel with per-store sender ID + monthly quota`.

---

### Task 4: Merchant-side — request access + SMS card in Channels tab + composer option

**Files:**
- Modify: `backend/app/Http/Controllers/Api/MessagingSettingsController.php` (+route) — `POST /settings/messaging/request-sms`
- Modify: `src/api/endpoints.ts` (`messagingApi.requestSms`), `src/pages/marketing/tabs/ChannelsTab.tsx`, `src/pages/marketing/tabs/CampaignsTab.tsx`
- Test: extend `backend/tests/Feature/Marketing/MessagingChannelsTest.php` (add one test — do NOT modify existing tests)

**Interfaces:**
- Produces: `POST /api/v1/settings/messaging/request-sms` → sets `sms_status = 'requested'` (409 `SMS_ALREADY_ENABLED` when already enabled); Channels tab SMS card; composer SMS option.

- [ ] **Step 1: Failing test**

```php
public function test_merchant_can_request_sms_access(): void
{
    $this->actingAsMember($this->owner)
        ->postJson('/api/v1/settings/messaging/request-sms')
        ->assertOk()
        ->assertJsonPath('status', 'requested');

    $this->assertSame('requested', MessagingSetting::first()->sms_status);
}
```

- [ ] **Step 2: Implement** — controller method:

```php
/** Merchant asks the platform to enable SMS for this store. */
public function requestSms(Request $request): JsonResponse
{
    abort_unless($request->user()->can('settings.manage'), 403);

    $s = $this->messaging->settings($this->context->store());

    if ($s->sms_status === 'enabled') {
        throw new \App\Exceptions\DomainConflictException('SMS_ALREADY_ENABLED', 'SMS is already enabled for this store.');
    }

    $s->update(['sms_status' => 'requested']);

    return response()->json(['status' => 'requested']);
}
```

Route next to the other messaging routes: `Route::post('settings/messaging/request-sms', [MessagingSettingsController::class, 'requestSms']);`

`ChannelsTab.tsx` — third card "SMS" (icon `phone`), driven by `status.sms` from `messagingApi.get()`:
- `status: 'none'` → copy: "SMS campaigns are provided by the platform — no provider setup needed on your side. Request access and the Dorzak team will enable it with your sender name." + primary button **Request SMS access** → `messagingApi.requestSms()` → toast + reload.
- `'requested'` → amber `StatusPill label="Requested"` + copy "Your request is with the platform team — you'll see this card activate once granted."
- `'enabled'` → green pill `Enabled · {sender_id}`, quota meter line `Used {used} / {quota ?? '∞'} this month` (thin progress bar div, width = used/quota %, `background: var(--dorzak-primary)`).

`CampaignsTab.tsx` — extend `ChannelStatus` interface with `sms: { ready: boolean; status: string; sender_id: string | null; quota: number | null; used: number }`; add to `channelOptions`: `{ value: 'sms', label: sms.ready ? \`SMS (${sms.sender_id})\` : 'SMS — not enabled', disabled: !sms.ready }`; when `form.channel === 'sms'`: hide nothing but show a char counter under the message textarea — `const len = form.body.length; const parts = len <= 160 ? 1 : Math.ceil(len / 153);` rendered as `` `${len} characters · ${parts} SMS part${parts > 1 ? 's' : ''}` `` (amber when parts > 1), plus helper "The subject line is not sent by SMS — only the message text." `anyChannelReady` already ORs — include `sms.ready`.

- [ ] **Step 3: Verify** — tests + tsc; browser-check the three card states by flipping `sms_status` in tinker.
- [ ] **Step 4: Commit** — `feat(sms): merchant request-access flow + SMS channel card and composer option`.

---

### Task 5: Full-suite gate + governance walk

- [ ] `php artisan test` green (minus the 2 known). `npx tsc --noEmit` → 0.
- [ ] Browser: platform admin → Platform → SMS: configure log driver, enable, test-send OK; merchant Channels → Request access; platform grants with sender+quota; merchant composer now offers SMS; send a campaign to consented customers; quota meter moves. Screenshot each stage.
- [ ] Commit fixes; `feat(sms): platform-managed SMS channel complete`.

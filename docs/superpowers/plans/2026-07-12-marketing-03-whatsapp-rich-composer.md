# Phase 3 — Rich WhatsApp Campaigns + Transformed Composer + Visual Life Layer

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. **Read `2026-07-12-marketing-00-overview.md` § Global Constraints first.** Phases 1–2 must be merged (consent filter + `messagingApi` exist).

**Goal:** WhatsApp campaigns use Meta's real rich features — approved templates synced from the store's WhatsApp Business Account, **image headers**, and **buttons** — and when the admin picks WhatsApp, the composer transforms into a WhatsApp-native builder with a live, animated phone preview. The whole Marketing console gains a visual life layer (motion, skeletons, count-ups) so it feels like a premium product, not a flat form.

**Architecture:** Templates are authored/approved in Meta Business Manager (that's Meta's rule — we cannot create them via checkout of scope), so our job is: (1) **sync** the approved template catalog per store via `GET /{WABA_ID}/message_templates` into a local `whatsapp_templates` table; (2) let the composer **pick a template and fill its parameters** (header image URL, body `{{n}}` variables, dynamic URL-button suffixes) stored on the campaign as `wa_parameters` JSON; (3) build the exact Cloud-API `components` payload at send time. The phone preview renders the template's real structure client-side. All motion is CSS-only in a new `src/styles/marketing.css` — no animation libraries.

**Tech Stack:** Laravel 12, React 18 + TS, CSS keyframes. No new packages.

**Meta Cloud API facts the implementer must not get wrong:**
- Listing templates requires the **WhatsApp Business Account ID (WABA ID)** — NOT the phone number ID. We must collect it in Channels settings: `GET https://graph.facebook.com/v20.0/{WABA_ID}/message_templates?fields=name,status,language,category,components&limit=100` with the same bearer token.
- A template's `components` array (as returned by Meta) looks like:
  ```json
  [
    {"type": "HEADER", "format": "IMAGE"},                       // or format TEXT with "text"
    {"type": "BODY", "text": "Hi {{1}}, get {{2}} off this weekend!"},
    {"type": "FOOTER", "text": "Reply STOP to opt out"},
    {"type": "BUTTONS", "buttons": [
      {"type": "QUICK_REPLY", "text": "Show me"},
      {"type": "URL", "text": "Shop now", "url": "https://shop.example/{{1}}"},
      {"type": "PHONE_NUMBER", "text": "Call us", "phone_number": "+97444…"}
    ]}
  ]
  ```
- SENDING that template requires a `components` **parameters** array (different shape!):
  ```json
  [
    {"type": "header", "parameters": [{"type": "image", "image": {"link": "https://…/photo.jpg"}}]},
    {"type": "body", "parameters": [{"type": "text", "text": "Sara"}, {"type": "text", "text": "20%"}]},
    {"type": "button", "sub_type": "url", "index": "0", "parameters": [{"type": "text", "text": "summer-sale"}]}
  ]
  ```
  Rules: header parameters only when HEADER format is IMAGE/VIDEO/DOCUMENT (TEXT headers with `{{1}}` also take a text param — support IMAGE + parameterless TEXT only in this phase; reject templates with VIDEO/DOCUMENT headers with a clear composer message "not supported yet"). Body parameters must match the highest `{{n}}` in the body text. Button parameters are sent ONLY for URL buttons whose stored `url` contains `{{1}}`; `index` is the button's position (string, 0-based) **within the buttons array**.
- QUICK_REPLY / PHONE_NUMBER / static-URL buttons need **no** send-time parameters — they render automatically from the approved template.

---

### Task 1: WABA ID + template sync (backend)

**Files:**
- Create: `backend/database/migrations/2026_07_12_000004_create_whatsapp_templates_table.php`
- Create: `backend/app/Models/WhatsappTemplate.php`
- Modify: `backend/app/Models/MessagingSetting.php`, `backend/app/Services/MessagingService.php`, `backend/app/Http/Controllers/Api/MessagingSettingsController.php`, `backend/routes/api.php`
- Test: `backend/tests/Feature/Marketing/WhatsappTemplateSyncTest.php`

**Interfaces:**
- Produces: `messaging_settings.whatsapp_business_account_id` (nullable string, plain — not a secret); `whatsapp_templates` rows `{id, store_id, name, language, status, category, components: array}`; `MessagingService::syncWhatsappTemplates(Store $store): int` (returns synced count, throws `DomainConflictException('CHANNEL_NOT_CONFIGURED'|'WABA_ID_MISSING'|'TEMPLATE_SYNC_FAILED', …)`); endpoints `POST /api/v1/settings/messaging/sync-templates` → `{synced: int}` and `GET /api/v1/whatsapp-templates` → `{templates: [{id, name, language, status, category, components}]}` (APPROVED only).
- Consumed by: Task 2 (send path), Task 3 (composer picker).

- [ ] **Step 1: Failing test**

```php
<?php

namespace Tests\Feature\Marketing;

use App\Models\MessagingSetting;
use App\Models\Store;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The composer offers Meta-approved templates, synced per store from the WABA. */
class WhatsappTemplateSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
        $this->assignPlan($this->store, 'ENTERPRISE');
        app(StoreContext::class)->setStore($this->store);
    }

    private function connected(array $extra = []): MessagingSetting
    {
        return MessagingSetting::create(array_merge([
            'store_id' => $this->store->id, 'whatsapp_token' => 't',
            'whatsapp_phone_number_id' => '123', 'whatsapp_connected_at' => now(),
            'whatsapp_business_account_id' => 'WABA9',
        ], $extra));
    }

    public function test_sync_upserts_approved_templates_from_the_waba(): void
    {
        $this->connected();
        Http::fake(['graph.facebook.com/v20.0/WABA9/message_templates*' => Http::response([
            'data' => [
                ['name' => 'weekend_sale', 'status' => 'APPROVED', 'language' => 'en', 'category' => 'MARKETING',
                 'components' => [
                     ['type' => 'HEADER', 'format' => 'IMAGE'],
                     ['type' => 'BODY', 'text' => 'Hi {{1}}, get {{2}} off!'],
                     ['type' => 'BUTTONS', 'buttons' => [['type' => 'URL', 'text' => 'Shop', 'url' => 'https://x.test/{{1}}']]],
                 ]],
                ['name' => 'pending_one', 'status' => 'PENDING', 'language' => 'en', 'category' => 'MARKETING', 'components' => []],
            ],
        ], 200)]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/messaging/sync-templates')
            ->assertOk()
            ->assertJsonPath('synced', 2);

        $this->assertSame(2, WhatsappTemplate::count());
        $this->assertSame('IMAGE', WhatsappTemplate::firstWhere('name', 'weekend_sale')->components[0]['format']);

        // The composer list exposes APPROVED only.
        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/whatsapp-templates')
            ->assertOk()
            ->assertJsonCount(1, 'templates')
            ->assertJsonPath('templates.0.name', 'weekend_sale');
    }

    public function test_resync_updates_in_place_no_duplicates(): void
    {
        $this->connected();
        $payload = ['data' => [['name' => 'weekend_sale', 'status' => 'APPROVED', 'language' => 'en', 'category' => 'MARKETING', 'components' => [['type' => 'BODY', 'text' => 'v2 {{1}}']]]]];
        Http::fake(['graph.facebook.com/*' => Http::response($payload, 200)]);

        $this->actingAsMember($this->owner)->postJson('/api/v1/settings/messaging/sync-templates')->assertOk();
        $this->actingAsMember($this->owner)->postJson('/api/v1/settings/messaging/sync-templates')->assertOk();

        $this->assertSame(1, WhatsappTemplate::count());
        $this->assertSame('v2 {{1}}', WhatsappTemplate::first()->components[0]['text']);
    }

    public function test_sync_without_waba_id_is_a_clear_409(): void
    {
        $this->connected(['whatsapp_business_account_id' => null]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/messaging/sync-templates')
            ->assertStatus(409)
            ->assertJsonPath('code', 'WABA_ID_MISSING');
    }
}
```

- [ ] **Step 2: Verify failure** — `php artisan test tests/Feature/Marketing/WhatsappTemplateSyncTest.php` → FAIL (column/table/route missing).

- [ ] **Step 3: Implement**

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Local mirror of each store's Meta-approved WhatsApp templates (synced, read-only). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messaging_settings', function (Blueprint $table) {
            $table->string('whatsapp_business_account_id')->nullable()->after('whatsapp_phone_number_id');
        });

        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('language', 10);
            $table->string('status');   // APPROVED | PENDING | REJECTED …
            $table->string('category')->nullable();
            $table->json('components'); // Meta's structure, verbatim
            $table->timestamps();

            $table->unique(['store_id', 'name', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
        Schema::table('messaging_settings', function (Blueprint $table) {
            $table->dropColumn('whatsapp_business_account_id');
        });
    }
};
```

`WhatsappTemplate.php`:

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** A Meta-approved message template, synced from the store's WABA. Read-only mirror. */
class WhatsappTemplate extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'name', 'language', 'status', 'category', 'components'];

    protected function casts(): array
    {
        return ['components' => 'array'];
    }

    public function bodyText(): ?string
    {
        return collect($this->components)->firstWhere('type', 'BODY')['text'] ?? null;
    }

    /** Highest {{n}} placeholder in the body — the number of body parameters required. */
    public function bodyParamCount(): int
    {
        preg_match_all('/\{\{(\d+)\}\}/', (string) $this->bodyText(), $m);

        return empty($m[1]) ? 0 : max(array_map('intval', $m[1]));
    }

    public function headerFormat(): ?string
    {
        return collect($this->components)->firstWhere('type', 'HEADER')['format'] ?? null;
    }

    /** @return list<array{type:string, text:string, url?:string, index:int, needs_param:bool}> */
    public function buttons(): array
    {
        $buttons = collect($this->components)->firstWhere('type', 'BUTTONS')['buttons'] ?? [];

        return collect($buttons)->values()->map(fn ($b, $i) => [
            'type' => $b['type'], 'text' => $b['text'], 'url' => $b['url'] ?? null,
            'index' => $i,
            'needs_param' => ($b['type'] === 'URL') && str_contains($b['url'] ?? '', '{{1}}'),
        ])->all();
    }
}
```

`MessagingSetting.php`: add `'whatsapp_business_account_id'` to `$fillable`.
`MessagingSettingsController::update` validation: add `'whatsapp_business_account_id' => ['nullable', 'string', 'max:60'],`; `show()` messaging payload: add `'whatsapp_business_account_id' => $s->whatsapp_business_account_id,`. (It is NOT a secret — echo it.) Changing it should NOT invalidate verification (verification is about token+phone id).

`MessagingService::syncWhatsappTemplates`:

```php
/** Pull the store's approved template catalog from Meta. Returns synced count. */
public function syncWhatsappTemplates(Store $store): int
{
    $s = $this->settings($store);

    if (empty($s->whatsapp_token)) {
        throw new \App\Exceptions\DomainConflictException('CHANNEL_NOT_CONFIGURED', 'Connect WhatsApp first.');
    }
    if (empty($s->whatsapp_business_account_id)) {
        throw new \App\Exceptions\DomainConflictException('WABA_ID_MISSING', 'Add your WhatsApp Business Account ID in Channels, then sync.');
    }

    $response = Http::withToken($s->whatsapp_token)->get(
        "https://graph.facebook.com/v20.0/{$s->whatsapp_business_account_id}/message_templates",
        ['fields' => 'name,status,language,category,components', 'limit' => 100],
    );

    if (! $response->successful()) {
        throw new \App\Exceptions\DomainConflictException('TEMPLATE_SYNC_FAILED', $response->json('error.message') ?? 'Could not fetch templates from Meta.');
    }

    $count = 0;
    foreach ($response->json('data', []) as $tpl) {
        \App\Models\WhatsappTemplate::updateOrCreate(
            ['store_id' => $store->id, 'name' => $tpl['name'], 'language' => $tpl['language']],
            ['status' => $tpl['status'], 'category' => $tpl['category'] ?? null, 'components' => $tpl['components'] ?? []],
        );
        $count++;
    }

    return $count;
}
```

Controller methods (in `MessagingSettingsController`) + routes next to the messaging ones:

```php
public function syncTemplates(Request $request): JsonResponse
{
    abort_unless($request->user()->can('settings.manage'), 403);

    return response()->json(['synced' => $this->messaging->syncWhatsappTemplates($this->context->store())]);
}

public function templates(): JsonResponse
{
    $rows = \App\Models\WhatsappTemplate::query()->where('status', 'APPROVED')
        ->orderBy('name')->get()
        ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'language' => $t->language,
            'category' => $t->category, 'components' => $t->components]);

    return response()->json(['templates' => $rows]);
}
```

```php
Route::post('settings/messaging/sync-templates', [MessagingSettingsController::class, 'syncTemplates']);
Route::get('whatsapp-templates', [MessagingSettingsController::class, 'templates']);
```

- [ ] **Step 4: Run tests** → PASS (whole `tests/Feature/Marketing` too). `php artisan migrate`.
- [ ] **Step 5: Commit** — `feat(whatsapp): sync Meta-approved template catalog per store (WABA)`.

---

### Task 2: Rich send path — image header, body variables, URL-button params

**Files:**
- Create: `backend/database/migrations/2026_07_12_000005_add_wa_rich_fields_to_campaigns.php`
- Modify: `backend/app/Models/Campaign.php`, `backend/app/Services/MessagingService.php` (`sendWhatsapp`), `backend/app/Services/CampaignService.php` (`create` + validation of params), `backend/app/Http/Controllers/Api/CampaignController.php`
- Test: `backend/tests/Feature/Marketing/WhatsappRichSendTest.php`

**Interfaces:**
- Produces: campaigns gain `wa_template_id` (nullable FK → whatsapp_templates, nullOnDelete) and `wa_parameters` (JSON: `{header_image_url?: string, body_params?: string[], button_url_params?: Record<string,string>}`); `MessagingService::sendWhatsapp` builds the full components payload when the campaign references a template row. Legacy `wa_template_name` path keeps working (backward compat) but new campaigns use `wa_template_id`.
- Consumes: `WhatsappTemplate` helpers from Task 1.

- [ ] **Step 1: Failing tests**

```php
public function test_rich_template_send_builds_the_full_components_payload(): void
{
    // connected setting (as Task 1) + template weekend_sale:
    //   HEADER IMAGE, BODY 'Hi {{1}}, get {{2}} off!', BUTTONS [URL 'Shop' url 'https://x.test/{{1}}']
    // campaign: channel whatsapp, wa_template_id = tpl->id,
    //   wa_parameters = ['header_image_url' => 'https://cdn.test/sale.jpg',
    //                    'body_params' => ['Sara', '20%'],
    //                    'button_url_params' => ['0' => 'summer']]
    // one consented customer with phone; Http::fake 200 {messages:[{id:'wamid.1'}]}
    // send() → sent 1; Http::assertSent verifying:
    //   $req['template']['name'] === 'weekend_sale'
    //   && $req['template']['components'][0] == ['type'=>'header','parameters'=>[['type'=>'image','image'=>['link'=>'https://cdn.test/sale.jpg']]]]
    //   && $req['template']['components'][1]['parameters'][1]['text'] === '20%'
    //   && $req['template']['components'][2] == ['type'=>'button','sub_type'=>'url','index'=>'0','parameters'=>[['type'=>'text','text'=>'summer']]]
}

public function test_send_rejects_missing_required_parameters(): void
{
    // same template; campaign with wa_parameters body_params ['Sara'] (needs 2)
    // send() → DomainConflictException WA_PARAMS_INVALID (assert 409 via the send endpoint)
    // campaign stays draft
}

public function test_create_endpoint_validates_params_against_the_template(): void
{
    // POST /campaigns with wa_template_id + body_params count mismatch → 409 WA_PARAMS_INVALID
    // POST with correct params → 201
}
```

Write them fully (mirror `WhatsappTemplateSyncTest` setup); run → FAIL.

- [ ] **Step 2: Implement**

Migration:

```php
Schema::table('campaigns', function (Blueprint $table) {
    $table->foreignId('wa_template_id')->nullable()->after('wa_template_language')
        ->constrained('whatsapp_templates')->nullOnDelete();
    $table->json('wa_parameters')->nullable()->after('wa_template_id');
});
```

`Campaign.php`: add `'wa_template_id', 'wa_parameters'` to fillable; cast `'wa_parameters' => 'array'`; relation `public function waTemplate() { return $this->belongsTo(\App\Models\WhatsappTemplate::class, 'wa_template_id'); }`.

`MessagingService` — extract a payload builder + validation (public static so CampaignService::create reuses it):

```php
/**
 * Build the Cloud-API template components parameters for a campaign that
 * references a synced template. Throws WA_PARAMS_INVALID when required
 * parameters are missing.
 *
 * @return array{name: string, language: array{code: string}, components: list<array<string,mixed>>}
 */
public static function buildTemplatePayload(\App\Models\WhatsappTemplate $template, array $params): array
{
    $components = [];

    $headerFormat = $template->headerFormat();
    if ($headerFormat === 'IMAGE') {
        $link = $params['header_image_url'] ?? null;
        if (! $link) {
            throw new \App\Exceptions\DomainConflictException('WA_PARAMS_INVALID', 'This template needs a header image URL.');
        }
        $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['link' => $link]]]];
    } elseif (in_array($headerFormat, ['VIDEO', 'DOCUMENT'], true)) {
        throw new \App\Exceptions\DomainConflictException('WA_PARAMS_INVALID', 'Video/document header templates are not supported yet.');
    }

    $needed = $template->bodyParamCount();
    $bodyParams = array_values($params['body_params'] ?? []);
    if (count($bodyParams) !== $needed) {
        throw new \App\Exceptions\DomainConflictException('WA_PARAMS_INVALID', "This template needs {$needed} text value(s).");
    }
    if ($needed > 0) {
        $components[] = ['type' => 'body',
            'parameters' => array_map(fn ($v) => ['type' => 'text', 'text' => (string) $v], $bodyParams)];
    }

    foreach ($template->buttons() as $button) {
        if ($button['needs_param']) {
            $suffix = $params['button_url_params'][(string) $button['index']] ?? null;
            if ($suffix === null) {
                throw new \App\Exceptions\DomainConflictException('WA_PARAMS_INVALID', "Button '{$button['text']}' needs a link value.");
            }
            $components[] = ['type' => 'button', 'sub_type' => 'url', 'index' => (string) $button['index'],
                'parameters' => [['type' => 'text', 'text' => $suffix]]];
        }
    }

    return ['name' => $template->name, 'language' => ['code' => $template->language], 'components' => $components];
}
```

`sendWhatsapp(Store $store, string $toPhone, Campaign $campaign)` — replace the payload selection:

```php
if ($campaign->wa_template_id && $campaign->waTemplate) {
    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => preg_replace('/\D/', '', $toPhone),
        'type' => 'template',
        'template' => self::buildTemplatePayload($campaign->waTemplate, $campaign->wa_parameters ?? []),
    ];
} elseif ($campaign->wa_template_name) {
    // legacy name-only path — unchanged
} else {
    // free-text session path — unchanged
}
```

> ⚠️ `buildTemplatePayload` throws — call it ONCE before the customer loop (in `CampaignService::sendWhatsapp`) so an invalid campaign fails atomically with 409 instead of counting every recipient as failed. Restructure: `CampaignService::sendWhatsapp` pre-builds `$payloadTemplate = $campaign->wa_template_id ? MessagingService::buildTemplatePayload(...) : null;` then per-customer the MessagingService just posts (add an optional prebuilt-payload argument or a `sendWhatsappPayload(Store, string $to, array $payload): bool` method — implement the latter and route template sends through it).

`CampaignService::create`: accept `wa_template_id`, `wa_parameters`; when `wa_template_id` present, load the template (must belong to the store — `WhatsappTemplate::query()->findOrFail` under store scope) and call `MessagingService::buildTemplatePayload($tpl, $data['wa_parameters'] ?? [])` purely for validation (discard result).
`CampaignController::store` validation additions:

```php
'wa_template_id' => ['nullable', 'integer'],
'wa_parameters' => ['nullable', 'array'],
'wa_parameters.header_image_url' => ['nullable', 'url'],
'wa_parameters.body_params' => ['nullable', 'array'],
'wa_parameters.body_params.*' => ['string', 'max:200'],
'wa_parameters.button_url_params' => ['nullable', 'array'],
```

Index response: add `'wa_template_id' => $c->wa_template_id, 'wa_parameters' => $c->wa_parameters,`.

- [ ] **Step 3: Run tests** → all `tests/Feature/Marketing` + `tests/Feature/Campaign` PASS.
- [ ] **Step 4: Commit** — `feat(whatsapp): rich template sends — image headers, body variables, URL buttons`.

---

### Task 3: The transformed WhatsApp composer + live phone preview

**Files:**
- Create: `src/pages/marketing/WhatsAppComposer.tsx` (template picker + parameter inputs)
- Create: `src/pages/marketing/WhatsAppPreview.tsx` (phone-frame live preview)
- Create: `src/styles/marketing.css` (imported once — add `import '../styles/marketing.css';` in `src/pages/marketing/MarketingPage.tsx`)
- Modify: `src/pages/marketing/tabs/CampaignsTab.tsx` (swap the whatsapp branch for the composer + preview), `src/pages/marketing/tabs/ChannelsTab.tsx` (WABA ID field + Sync templates button), `src/api/endpoints.ts` (`messagingApi.syncTemplates`, `whatsappTemplatesApi.list`)
- Test: `npx tsc --noEmit` = 0 + browser verification (Task 5)

**Interfaces:**
- Consumes: `GET /api/v1/whatsapp-templates` (Task 1), campaign create fields (Task 2).
- Produces: `<WhatsAppComposer templates={WaTemplate[]} value={WaComposerValue} onChange={(v) => void} />` and `<WhatsAppPreview template={WaTemplate | null} params={WaComposerValue} storeName={string} />` where:

```ts
export interface WaTemplate {
  id: number; name: string; language: string; category: string | null;
  components: Array<{ type: string; format?: string; text?: string; buttons?: Array<{ type: string; text: string; url?: string }> }>;
}
export interface WaComposerValue {
  wa_template_id: number | null;
  header_image_url: string;
  body_params: string[];
  button_url_params: Record<string, string>;
}
```

- [ ] **Step 1: `marketing.css`** — create with EXACTLY these blocks (the visual-life foundation used by Tasks 3–4):

```css
/* ── Marketing console motion & life ─────────────────────────────── */

@keyframes mk-fade-slide { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
@keyframes mk-pop        { from { opacity: 0; transform: scale(0.94) translateY(6px); } to { opacity: 1; transform: none; } }
@keyframes mk-pulse      { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }
@keyframes mk-shimmer    { from { background-position: -400px 0; } to { background-position: 400px 0; } }
@keyframes mk-typing     { 0%, 60%, 100% { transform: none; } 30% { transform: translateY(-4px); } }

.mk-panel { animation: mk-fade-slide 0.25s ease both; }

.mk-card-lift { transition: transform 0.18s ease, box-shadow 0.18s ease; }
.mk-card-lift:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); }

.mk-live-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--dorzak-success, #16a34a); animation: mk-pulse 1.8s ease infinite; }

.mk-skeleton { border-radius: 8px; background: linear-gradient(90deg, rgba(0,0,0,0.05) 25%, rgba(0,0,0,0.1) 37%, rgba(0,0,0,0.05) 63%); background-size: 800px 100%; animation: mk-shimmer 1.3s linear infinite; }

/* ── WhatsApp phone preview ──────────────────────────────────────── */

.wa-phone { width: 300px; border-radius: 28px; border: 8px solid #1f2c34; background: #0b141a; overflow: hidden; box-shadow: 0 14px 40px rgba(0, 0, 0, 0.25); }
.wa-topbar { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #1f2c34; color: #e9edef; font-size: 0.8rem; font-weight: 600; }
.wa-avatar { width: 26px; height: 26px; border-radius: 50%; background: #00a884; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.7rem; }
.wa-chat { min-height: 300px; padding: 14px 10px; background: #0b141a url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" opacity="0.04"><circle cx="12" cy="12" r="2" fill="white"/><circle cx="42" cy="36" r="2" fill="white"/></svg>'); }
.wa-bubble { max-width: 92%; background: #202c33; color: #e9edef; border-radius: 10px; border-top-left-radius: 2px; overflow: hidden; animation: mk-pop 0.3s ease both; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3); }
.wa-bubble-img { width: 100%; height: 130px; object-fit: cover; display: block; background: #2a3942; }
.wa-bubble-body { padding: 8px 10px 4px; font-size: 0.82rem; line-height: 1.45; white-space: pre-wrap; }
.wa-bubble-footer { padding: 0 10px 4px; font-size: 0.7rem; color: #8696a0; }
.wa-bubble-time { text-align: right; padding: 0 10px 6px; font-size: 0.65rem; color: #8696a0; }
.wa-btn { display: flex; align-items: center; justify-content: center; gap: 6px; border-top: 1px solid rgba(134, 150, 160, 0.25); padding: 9px; color: #53bdeb; font-size: 0.82rem; font-weight: 600; }
.wa-var { background: rgba(0, 168, 132, 0.25); border-radius: 4px; padding: 0 3px; }
.wa-typing { display: inline-flex; gap: 3px; padding: 10px 14px; background: #202c33; border-radius: 10px; }
.wa-typing i { width: 6px; height: 6px; border-radius: 50%; background: #8696a0; animation: mk-typing 1.1s ease infinite; }
.wa-typing i:nth-child(2) { animation-delay: 0.15s; }
.wa-typing i:nth-child(3) { animation-delay: 0.3s; }
```

- [ ] **Step 2: `WhatsAppPreview.tsx`** — full component:

```tsx
import React from 'react';
import { AppIcon } from '../../components/icons/AppIcon';
import type { WaTemplate, WaComposerValue } from './WhatsAppComposer';

/**
 * Live phone-frame preview of the selected template, parameters substituted
 * in real time. Pure CSS (marketing.css .wa-*), bubble pops on template change.
 */
export const WhatsAppPreview: React.FC<{ template: WaTemplate | null; params: WaComposerValue; storeName: string }> = ({ template, params, storeName }) => {
  const body = template?.components.find(c => c.type === 'BODY')?.text ?? '';
  const footer = template?.components.find(c => c.type === 'FOOTER')?.text;
  const headerFormat = template?.components.find(c => c.type === 'HEADER')?.format;
  const buttons = template?.components.find(c => c.type === 'BUTTONS')?.buttons ?? [];

  // Substitute {{n}} with the entered value or a highlighted placeholder chip.
  const parts: React.ReactNode[] = [];
  body.split(/(\{\{\d+\}\})/g).forEach((seg, i) => {
    const m = seg.match(/^\{\{(\d+)\}\}$/);
    if (!m) { parts.push(<span key={i}>{seg}</span>); return; }
    const val = params.body_params[Number(m[1]) - 1];
    parts.push(<span key={i} className="wa-var">{val || `{{${m[1]}}}`}</span>);
  });

  return (
    <div className="wa-phone">
      <div className="wa-topbar"><span className="wa-avatar">{storeName.slice(0, 1).toUpperCase()}</span>{storeName}<span style={{ marginLeft: 'auto', opacity: 0.6, fontWeight: 400 }}>online</span></div>
      <div className="wa-chat">
        {!template ? (
          <div className="wa-typing"><i /><i /><i /></div>
        ) : (
          <div className="wa-bubble" key={template.id /* re-pop on template change */}>
            {headerFormat === 'IMAGE' && (
              params.header_image_url
                ? <img className="wa-bubble-img" src={params.header_image_url} alt="" onError={e => { (e.target as HTMLImageElement).style.display = 'none'; }} />
                : <div className="wa-bubble-img" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#8696a0', fontSize: '0.75rem' }}>image header</div>
            )}
            <div className="wa-bubble-body">{parts}</div>
            {footer && <div className="wa-bubble-footer">{footer}</div>}
            <div className="wa-bubble-time">{new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
            {buttons.map((b, i) => (
              <div key={i} className="wa-btn">
                <AppIcon name={b.type === 'PHONE_NUMBER' ? 'phone' : b.type === 'URL' ? 'link' : 'chevronRight'} size={13} />
                {b.text}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};
```

- [ ] **Step 3: `WhatsAppComposer.tsx`** — full component (template picker cards + auto-generated parameter inputs; exports the two interfaces from the Interfaces block verbatim):

```tsx
import React from 'react';
import { TextInput } from '../../components/forms/TextInput';
import { AppIcon } from '../../components/icons/AppIcon';

export interface WaTemplate { /* exactly as in the Interfaces block */ }
export interface WaComposerValue { /* exactly as in the Interfaces block */ }

export const emptyWaValue: WaComposerValue = { wa_template_id: null, header_image_url: '', body_params: [], button_url_params: {} };

export const WhatsAppComposer: React.FC<{
  templates: WaTemplate[];
  value: WaComposerValue;
  onChange: (v: WaComposerValue) => void;
}> = ({ templates, value, onChange }) => {
  const selected = templates.find(t => t.id === value.wa_template_id) ?? null;
  const bodyText = selected?.components.find(c => c.type === 'BODY')?.text ?? '';
  const paramCount = Math.max(0, ...[...bodyText.matchAll(/\{\{(\d+)\}\}/g)].map(m => Number(m[1])), 0);
  const headerFormat = selected?.components.find(c => c.type === 'HEADER')?.format;
  const urlButtons = (selected?.components.find(c => c.type === 'BUTTONS')?.buttons ?? [])
    .map((b, index) => ({ ...b, index }))
    .filter(b => b.type === 'URL' && (b.url ?? '').includes('{{1}}'));

  const pick = (t: WaTemplate) => onChange({
    wa_template_id: t.id, header_image_url: '', button_url_params: {},
    body_params: Array(Math.max(0, ...[...(t.components.find(c => c.type === 'BODY')?.text ?? '').matchAll(/\{\{(\d+)\}\}/g)].map(m => Number(m[1])), 0)).fill(''),
  });

  if (templates.length === 0) {
    return (
      <div className="card" style={{ padding: 18, textAlign: 'center', color: 'var(--text-muted)' }}>
        No approved templates yet. Create them in Meta Business Manager, then use <b>Sync templates</b> in the Channels tab.
      </div>
    );
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <label className="form-label">Approved template</label>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 10 }}>
        {templates.map(t => (
          <button key={t.id} type="button" onClick={() => pick(t)} className="mk-card-lift"
            style={{
              textAlign: 'left', padding: 12, borderRadius: 10, cursor: 'pointer',
              border: `2px solid ${value.wa_template_id === t.id ? '#00a884' : 'var(--color-border)'}`,
              background: value.wa_template_id === t.id ? 'rgba(0,168,132,0.06)' : 'var(--color-surface, #fff)',
            }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontWeight: 700, fontSize: '0.85rem' }}>
              <AppIcon name="whatsapp" size={14} /> {t.name}
            </div>
            <div style={{ fontSize: '0.72rem', color: 'var(--text-muted)', marginTop: 4 }}>
              {t.language.toUpperCase()} · {t.category ?? 'template'}
            </div>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: 6, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
              {t.components.find(c => c.type === 'BODY')?.text}
            </div>
          </button>
        ))}
      </div>

      {selected && headerFormat === 'IMAGE' && (
        <TextInput label="Header image URL" value={value.header_image_url}
          onChange={e => onChange({ ...value, header_image_url: e.target.value })}
          placeholder="https://…/promo.jpg" />
      )}

      {selected && paramCount > 0 && (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 10 }}>
          {Array.from({ length: paramCount }, (_, i) => (
            <TextInput key={i} label={`Variable {{${i + 1}}}`} value={value.body_params[i] ?? ''}
              onChange={e => { const next = [...value.body_params]; next[i] = e.target.value; onChange({ ...value, body_params: next }); }} />
          ))}
        </div>
      )}

      {selected && urlButtons.map(b => (
        <TextInput key={b.index} label={`Link value for button "${b.text}"`} value={value.button_url_params[String(b.index)] ?? ''}
          onChange={e => onChange({ ...value, button_url_params: { ...value.button_url_params, [String(b.index)]: e.target.value } })}
          placeholder="e.g. summer-sale" />
      ))}
    </div>
  );
};
```

(Where the comment says "exactly as in the Interfaces block", paste those interface bodies — no shortcuts.)

- [ ] **Step 4: Wire into `CampaignsTab.tsx`** — when `form.channel === 'whatsapp'`: REPLACE the old template-name/language TextInputs with a two-column layout: left = `<WhatsAppComposer templates={waTemplates} value={waValue} onChange={setWaValue} />`, right = `<WhatsAppPreview template={selectedTemplate} params={waValue} storeName={/* store name from authStore: useAuthStore(s => s.store?.name) ?? 'Store' */} />` (stack on narrow screens: `display:grid; gridTemplateColumns:'minmax(0,1fr) 300px'; gap:16; alignItems:'start'` + `flexWrap` fallback). Load templates on mount alongside channels: `whatsappTemplatesApi.list()` → state `waTemplates`. Subject/message stay (subject = internal name; add helper text "WhatsApp sends the template content — subject and message are for your records and the email fallback"). Payload: include `wa_template_id: waValue.wa_template_id, wa_parameters: { header_image_url: waValue.header_image_url || null, body_params: waValue.body_params, button_url_params: waValue.button_url_params }` when channel === 'whatsapp' && waValue.wa_template_id. Keep legacy fields null.
Also wrap each tab's outer `<div>` in `className="mk-panel"` (the fade-slide entrance) across ALL tab files, and replace the plain "Loading …" divs with two `mk-skeleton` divs (height 60 + 120).

`ChannelsTab.tsx`: WhatsApp card gains `TextInput label="WhatsApp Business Account ID"` bound to `whatsapp_business_account_id` (echoed by GET, saved via the same update payload), plus after Verify a secondary `AppButton "Sync templates"` calling `messagingApi.syncTemplates()` → toast `"{synced} templates synced."`; show template count line under the card when > 0. Status pill row gains `<span className="mk-live-dot" />` next to Connected.

`endpoints.ts`:

```ts
export const whatsappTemplatesApi = { list: () => request('/whatsapp-templates') };
// messagingApi additions:
syncTemplates: () => request('/settings/messaging/sync-templates', { method: 'POST' }),
```

- [ ] **Step 5: Verify** — `npx tsc --noEmit` = 0.
- [ ] **Step 6: Commit** — `feat(whatsapp): template composer with live phone preview + channels sync UI`.

---

### Task 4: Console-wide visual life pass

**Files:**
- Modify: `src/pages/marketing/MarketingPage.tsx`, `src/pages/marketing/marketingShared.tsx`, all `src/pages/marketing/tabs/*.tsx` (only class/markup touches — no logic)

**Interfaces:** none new — purely presentational, uses `marketing.css` classes from Task 3.

- [ ] **Step 1:** `marketingShared.tsx`:
  - `StatCard`: add `className="card mk-card-lift"` and a **count-up**: replace the raw `{value}` render for NUMERIC values with a `useCountUp` hook defined in the same file:

```tsx
export function useCountUp(target: number, ms = 600): number {
  const [n, setN] = React.useState(0);
  React.useEffect(() => {
    if (!Number.isFinite(target)) { setN(target); return; }
    const t0 = performance.now();
    let raf = 0;
    const tick = (t: number) => {
      const p = Math.min(1, (t - t0) / ms);
      setN(Math.round(target * (1 - Math.pow(1 - p, 3)))); // ease-out cubic
      if (p < 1) raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [target, ms]);
  return n;
}
```

  StatCard accepts `value: React.ReactNode` today — add an optional `countTo?: number` prop; when provided render `useCountUp(countTo)` (formatted with `toLocaleString()`) instead of `value`. Update MarketingPage overview cards to pass `countTo` for the numeric ones (messages delivered, points held, reviews pending) — money strings keep `value`.
  - `LockedFeature`: wrap icon circle with a subtle float: add inline `style={{ animation: 'mk-pop 0.35s ease both' }}` on the card.
- [ ] **Step 2:** `MarketingPage.tsx` tab nav — active tab underline becomes animated: give the button style `transition: 'color 0.15s ease'`, and add to the container div `className="mk-panel"` on the content wrapper `<>` → wrap in a keyed div `<div key={tab} className="mk-panel">…</div>` so every tab switch re-triggers the entrance animation.
- [ ] **Step 3:** Empty states across tabs: prepend a small inline SVG illustration (one shared component in `marketingShared.tsx`):

```tsx
export const EmptyArt: React.FC = () => (
  <svg width="120" height="72" viewBox="0 0 120 72" fill="none" aria-hidden style={{ margin: '0 auto 10px', display: 'block', opacity: 0.85 }}>
    <rect x="14" y="14" width="64" height="44" rx="8" fill="var(--color-border)" opacity="0.35" />
    <rect x="42" y="26" width="64" height="32" rx="8" fill="var(--dorzak-primary)" opacity="0.14" />
    <circle cx="94" cy="24" r="10" fill="var(--dorzak-primary)" opacity="0.35" />
    <path d="M90 24l3 3 6-6" stroke="var(--dorzak-primary)" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
  </svg>
);
```

Use it inside every "No X yet" empty-state card (`<EmptyArt />` above the text).
- [ ] **Step 4:** `npx tsc --noEmit` = 0; quick browser eyeball (tab switching animates, cards lift, stats count up, skeletons shimmer).
- [ ] **Step 5:** Commit — `feat(marketing): visual life layer — motion, skeletons, count-ups, empty-state art`.

---

### Task 5: Full-suite gate + live verification

- [ ] `php artisan test` green (minus the 2 known). `npx tsc --noEmit` = 0.
- [ ] Browser (token method from Overview): Channels → add WABA ID → Sync templates (fake by inserting 2 `WhatsappTemplate` rows via tinker if no real Meta account: one with IMAGE header + 2 body vars + URL button, one text-only) → Campaigns → New campaign → channel WhatsApp → picker cards render → select → variable inputs appear → typing updates the phone preview live (image, vars highlighted, buttons) → attempt send with a missing variable → toast shows the 409 message → fill → Send now (Http will fail against real Meta without creds — acceptable: failed_count counts it honestly; with log-driver-style fake not available for WhatsApp, verify at least the validation + preview interactions). Screenshot the composer + preview.
- [ ] Commit fixes; `feat(whatsapp): rich composer phase complete`.

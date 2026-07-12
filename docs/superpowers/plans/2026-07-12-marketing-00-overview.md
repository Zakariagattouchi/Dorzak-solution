# Marketing Platform — Master Overview

> **For agentic workers:** This is the index. Execute the four phase plans **in order** — each produces working, tested software on its own. REQUIRED SUB-SKILL per plan: superpowers:subagent-driven-development or superpowers:executing-plans.

**Goal:** Turn the Marketing console into a revenue platform: every program is honored on the public storefront, SMS becomes a platform-managed channel, WhatsApp campaigns get Meta's rich features with a transformed composer, and the console gets visual life + automations that justify plan upgrades.

**Phase plans (execute in order):**
1. `2026-07-12-marketing-01-storefront-sync.md` — consent + coupons/loyalty/wallet/reviews/referrals honored at the public storefront.
2. `2026-07-12-marketing-02-sms-platform-channel.md` — super-admin-managed SMS provider; merchants request access, get sender ID + quota.
3. `2026-07-12-marketing-03-whatsapp-rich-composer.md` — template sync from Meta, image headers + buttons, WhatsApp-specific composer with live phone preview, visual life layer for the whole console.
4. `2026-07-12-marketing-04-automations-attribution.md` — welcome/win-back automations, per-campaign revenue attribution, send quotas as plan limits.

## Global Constraints (apply to EVERY task in EVERY phase plan)

**Project layout:** repo root `/Users/barsha/Documents/recover Kyte`. Laravel backend in `backend/` (PHP 8.3, Laravel 12, sqlite dev DB). React 18 + TypeScript + Vite frontend in `src/` (Zustand stores, no CSS framework — plain CSS files in `src/styles/` + inline styles with CSS variables).

**Run tests:** `cd "/Users/barsha/Documents/recover Kyte/backend" && php artisan test <path>` (output is a JSON summary line). Frontend typecheck: `cd "/Users/barsha/Documents/recover Kyte" && npx tsc --noEmit` — must stay at **0 errors**.

**Two pre-existing test failures are NOT yours:** `CommerceImprovementsTest::test_category_photo_upload…` and `DemoSeederParityTest::test_store_and_subscription`. Everything else must pass.

**Codebase rules — violating any of these is a review-rejection:**
1. **Tenant scoping:** every store-owned model uses the `App\Models\Concerns\BelongsToStore` trait (global `StoreScope` filters by `App\Support\StoreContext`; `store_id` auto-fills on create). Tests set context via `app(StoreContext::class)->setStore($store)`. The scope **no-ops when no store is set** (scheduler context) — relations still constrain, so cross-tenant sweeps are safe if you iterate rows and use their relations.
2. **Business-rule errors:** throw `App\Exceptions\DomainConflictException(string $errorCode, string $message)` → renders **HTTP 409** with machine `code`. Do NOT use 422 for business rules (422 is validation only).
3. **Plan gating:** capabilities live in `App\Enums\PlanFeature` (add case + `descriptor()` entry, `enforced => true`). Grant in `App\Support\DefaultPlans` (PRO + ENTERPRISE unless the plan says otherwise). Enforce with `App\Services\PlanGate::ensure($store, PlanFeature::X)` → 402. After changing DefaultPlans, tests must `$this->seed(Database\Seeders\PlanSeeder::class)` if they assert plan contents (test helpers already seed plans).
4. **Secrets:** encrypted at rest via `'encrypted'` Eloquent cast; **never round-trip to the UI** — GET returns `null` for the secret plus a `has_<secret>` boolean; PUT treats blank/missing secret as "keep stored value" (see `MessagingSettingsController::update`).
5. **Channel honesty:** a send method returns a real boolean; an unconfigured channel **refuses** (`DomainConflictException('CHANNEL_NOT_CONFIGURED', …)`), the scheduler **skips** (stays `scheduled`), and `sent_count`/`failed_count` are actual results. Never log-and-report-success.
6. **HTTP tests:** helpers from `backend/tests/TestCase.php`: `['user' => $u, 'store' => $s] = $this->createStoreWithOwner(array $storeAttrs = [])`, `$this->assignPlan($store, 'FREE'|'PRO'|'ENTERPRISE')`, `$this->actingAsMember($user)`. Fake externals: `Http::fake(['graph.facebook.com/*' => …])`, `Mail::fake()`.
7. **API resources:** `SubscriptionController` returns a Laravel Resource → frontend must unwrap `res.data`. Plain controllers return bare JSON. Follow whichever the endpoint you touch already does.
8. **Frontend API:** add endpoint fns to `src/api/endpoints.ts` (they return `unknown` — cast `(await api.x()) as any` at call sites). Auth token lives in localStorage key `dorzak-token`.
9. **Frontend components:** reuse `AppButton` (variants primary/secondary/tertiary/danger, `loading`), `TextInput`, `SelectInput` (options support `disabled`), `ToggleSwitch`, `DataTable` (`selectable={false}` for read tables), `StatusPill`, `AppIcon` (IconName union — check `src/components/icons/AppIcon.tsx` before using a name), `useMoney()`, `useToastStore().addToast(msg, 'success'|'danger'|'warning'|'info')` — **'danger', not 'error'**.
10. **Marketing console shared bits:** `src/pages/marketing/marketingShared.tsx` exports `StatCard`, `TabIntro`, `ConfirmButton`, `LockedFeature`, `apiErrorMessage`. Tabs live in `src/pages/marketing/tabs/*.tsx`, wired in `src/pages/marketing/MarketingPage.tsx` (`Tab` union + `TAB_FEATURE` + `TAB_META` + render switch).
11. **Public storefront API** is under `Route::prefix('public')` in `backend/routes/api.php` (throttled, no auth). Public controllers live in `backend/app/Http/Controllers/Api/Public/` and resolve the store with the `ResolvesPublicStore` trait. **Never leak cross-tenant data or secrets through public endpoints.**
12. **Consent:** once Phase 1 lands, every campaign/automation audience MUST filter `marketing_consent = true`. Transactional messages (order status) are exempt.
13. **Commits:** small, per task, message style `feat(scope): what and why`, ending with `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`. Never commit `backend/database/database.sqlite` or `public/build`.

**Manual browser verification (used at the end of each plan):** dev backend usually runs on :8000; start frontend via the session's preview tooling (Vite, port 3001, proxy to :8000 already configured). Auth for verification WITHOUT typing credentials: mint a token `cd backend && php artisan tinker --execute="echo App\Models\User::where('email','merchant@dorzak.com')->first()->createToken('web')->plainTextToken;"` then in the browser console `localStorage.setItem('dorzak-token', '<token>')` and navigate. Note: the embedded browser pane's native scroll can time out — use `scrollIntoView` via JS and click by element ref instead.

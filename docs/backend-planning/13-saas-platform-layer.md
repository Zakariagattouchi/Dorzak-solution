# 13 — SaaS Platform Layer (plans, free-tier menu, subdomains, platform admin, billing port)

**Status**: approved by product owner 2026-07-09. Supersedes the "subscription display only" scope of
TP-03 and the Stripe assumption in TP-09/12. Everything else in the blueprint stands.

## Product decisions (final, from owner session 2026-07-09)

1. **Open self-serve SaaS.** Anyone registers, builds their store (logo, catalog, settings), gets their
   own login and public presence. Thousands of tenants expected; single-DB `store_id` tenancy confirmed
   as the architecture (no change).
2. **Free plan forever = hosted menu only.** Free merchants get the *full back-office experience*
   (POS, catalog, customers, orders, reports) but their public presence is a **view-only menu at an
   anonymous URL** — no store name in the URL, no online ordering, no customer lookup. "It's just a
   menu hosted online; they cannot order, they cannot do anything."
3. **Paid plans unlock the storefront identity**: branded **subdomain** (`mystore.dorzak.com`) from
   day one, online ordering / WhatsApp checkout, delivery services. **Custom domain** is reserved as a
   premium (higher-tier) feature, deferred.
4. **Plans are operator-configurable, not hardcoded.** The owner must be able to "craft the trial and
   what each plan contains" from an admin screen: a code-defined **capability catalog** + DB-composed
   plans (same pattern as Durzak's MerchantFeature catalog). Changing what FREE/PRO contain is a form
   edit, not a deploy.
5. **Billing = regional payment gateway (Qatar-capable), chosen later.** Candidates: Dibsy, MyFatoorah,
   Tap. Until selected, code against a `PaymentGateway` contract; plan changes happen manually via the
   platform admin.
6. **Platform admin console (new).** Kyte was single-merchant; the platform operator needs: plans CRUD +
   feature composition, stores list/suspend, subscriptions overview with manual plan assignment.

## 1. Capability catalog — `PlanFeature` enum

`app/Enums/PlanFeature.php` (string-backed, SCREAMING values per API convention):

| Case | Kind | Gates |
|---|---|---|
| `ONLINE_ORDERING` | boolean | public order placement + customer lookup routes |
| `BRANDED_STOREFRONT` | boolean | storefront slug/subdomain (settings write + public resolution) |
| `CUSTOM_DOMAIN` | boolean | deferred feature; key reserved now |
| `DELIVERY_SERVICES` | boolean | delivery integrations (future packet) |
| `ADVANCED_REPORTS` | boolean | finance/analytics export endpoints (view stays free) |
| `STAFF_SEATS` | limit | active staff members per store (null = unlimited) |
| `PRODUCTS_LIMIT` | limit | product count per store (null = unlimited) |

Helper: `public function isLimit(): bool`. Adding a capability = new enum case + enforcement point;
composing it into plans is data.

## 2. Schema

### TABLE: plans
| column | type | notes |
|---|---|---|
| id | bigint pk | |
| code | citext unique | FREE, PRO, ENTERPRISE seeds; operator can add more |
| name_en / name_ar | varchar(60) | bilingual display |
| description_en / description_ar | varchar(255) nullable | |
| price | decimal(8,2) default 0 | |
| billing_cycle | varchar(8) | monthly\|yearly |
| is_default | boolean default false | the forever-free signup plan; **exactly one** enforced in service layer |
| is_active | boolean default true | inactive = hidden from upgrade UI, existing subscribers keep it |
| sort_order | smallint | |
| timestamps | | |

### TABLE: plan_feature
| column | type | notes |
|---|---|---|
| plan_id | fk plans cascade | |
| feature | varchar(32) | `PlanFeature` value |
| limit_value | int nullable | only for limit-kind features; null = unlimited |
| unique(plan_id, feature) | | |

### Changes to existing tables
- `subscriptions`: add `plan_id` fk → plans (restrict on delete); **backfill** from the `plan` varchar
  (FREE/PRO/ENTERPRISE → seeded plan rows); then drop the `plan` column. `SubscriptionPlan` enum is
  retired (delete; seeder references plan codes directly).
- `stores`: add `menu_token` char(20) unique (URL-safe random, generated in `RegisterStoreAction` and
  backfilled for existing stores in the same migration); add `suspended_at` timestamptz nullable.
- `users`: add `is_platform_admin` boolean default false.

Seeds: FREE (default; no boolean features; STAFF_SEATS=1), PRO (ONLINE_ORDERING, BRANDED_STOREFRONT,
DELIVERY_SERVICES, ADVANCED_REPORTS, STAFF_SEATS=5), ENTERPRISE (all PRO + CUSTOM_DOMAIN, unlimited
seats). These are *starting values* — owner edits them in platform admin.

## 3. `PlanGate` service

`app/Services/PlanGate.php`:
- `allows(Store $store, PlanFeature $f): bool`
- `limit(Store $store, PlanFeature $f): ?int` (null = unlimited)
- `ensure(Store $store, PlanFeature $f): void` — throws `PlanUpgradeRequiredException`
- `ensureWithinLimit(Store $store, PlanFeature $f, int $current): void`
- Resolution: store → ACTIVE subscription → plan → features; cached per-request (memoized array),
  no Redis needed. A store with no ACTIVE subscription resolves to the default plan.

`PlanUpgradeRequiredException` renders **402** `{ "message": …, "code": "PLAN_UPGRADE_REQUIRED",
"feature": "ONLINE_ORDERING" }` — the frontend turns this into an upgrade prompt.

### Enforcement points (initial)
| Where | Feature |
|---|---|
| `Public/PublicOrderController` + `PublicCustomerLookupController` | ONLINE_ORDERING |
| `SettingsController` storefront group (slug write) | BRANDED_STOREFRONT |
| Public slug/subdomain resolution (`Public/StorefrontController`) | BRANDED_STOREFRONT (fallback: 404, menu token still works) |
| `InviteStaffAction` | STAFF_SEATS (count active pivots + pending invites) |
| `ProductController@store` / import | PRODUCTS_LIMIT |
| Reports export endpoints | ADVANCED_REPORTS |

## 4. Public routing — anonymous menu + subdomains

- **Free tier**: `GET /api/v1/public/menu/{menu_token}` (+ `/catalog`) → same resources as the slug
  storefront but **view-only**: response flags `ordering_enabled: false`; order/lookup routes simply do
  not exist under `/menu/`. This URL works for *every* store (paid included — it's the QR-menu URL).
- **Paid tier**: existing `stores/{slug}` path routes stay; additionally the public API resolves the
  tenant from the request `Host` subdomain (`mystore.dorzak.com`) when present — same controller, a
  `ResolvesPublicStore` concern checks subdomain → slug lookup. Reserved-slug list already implemented.
- Ordering routes call `PlanGate::ensure(ONLINE_ORDERING)` → 402 with upgrade code (storefront UI
  hides ordering when `ordering_enabled=false`, so 402 is the belt-and-braces layer).
- **Infra prerequisites (deploy-time, documented in RUN.md)**: wildcard DNS `*.dorzak.com` A/CNAME,
  wildcard TLS cert, web server passes Host through to Laravel; SPA storefront served on subdomains.

## 5. Platform admin (V1)

- Guard: `is_platform_admin` on users + `EnsurePlatformAdmin` middleware; routes under
  `/api/v1/platform/*` (auth:sanctum, **not** store-scoped — platform admin bypasses `SetStoreContext`).
- Endpoints:
  - `GET|POST|PATCH /platform/plans`, `PUT /platform/plans/{plan}/features` (bulk feature+limit set)
  - `GET /platform/stores` (search, plan filter, status), `PATCH /platform/stores/{store}` (suspend /
    reactivate)
  - `GET /platform/subscriptions`, `PATCH /platform/subscriptions/{sub}` (manual plan assignment,
    status change) — the interim billing mechanism until the gateway lands
- Suspension semantics: `suspended_at` set → members 403 `STORE_SUSPENDED` on login/api, public
  storefront + menu 404.
- Frontend: `/platform` section in the React app, route-guarded by `is_platform_admin` from `/auth/me`.

## 6. Billing port (contract only now)

`app/Contracts/PaymentGateway.php`: `createSubscription(Store, Plan, cycle): RedirectResponse-ish DTO`,
`cancel(Subscription): void`, `handleWebhook(Request): GatewayEvent`. `FakeGateway` binds in tests/dev.
Gateway selection (Dibsy / MyFatoorah / Tap — verify recurring support + QAR settlement + Qatar
onboarding) is a separate decision packet; nothing else may depend on gateway specifics.

## 7. Tests (added to the 11-test-plan catalog)

- Unit: PlanGate (allows/limit/default-plan fallback/memoization), PlanFeature::isLimit.
- Feature: free store menu-token renders catalog + `ordering_enabled=false`; POST order on free store
  402 PLAN_UPGRADE_REQUIRED; slug/subdomain resolution 404 for free store, 200 for PRO; staff invite at
  seat limit 402; product create at limit 402; plan edit in platform admin immediately changes gating;
  platform routes 403 for non-admin owners; suspended store: login 403, public 404; register assigns
  default plan + menu_token; subscriptions backfill migration test.
- Full existing suite must stay green (blanket tenant/role tests unchanged).

## Out of scope (explicit)
Gateway integration + webhooks; custom-domain implementation; marketing/landing site; wildcard DNS/SSL
provisioning (documented only); per-plan AI features.

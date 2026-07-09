# 06 — Laravel Implementation Strategy

Target: **Laravel 11** (current LTS-track), PHP 8.3, PostgreSQL 16, Redis (queues+cache), Pest or PHPUnit (pick Pest — terser feature tests).

## 1. Auth strategy — Laravel Sanctum, SPA cookie mode

- The React app is a first-party SPA on the same top-level domain (`app.dorzak.com` → `api.dorzak.com`): Sanctum's cookie/session mode gives CSRF-protected, httpOnly-cookie auth with zero token storage in JS — strictly safer than localStorage bearer tokens.
- Also enable **personal access tokens** for CLI/testing and a future mobile app; the login endpoint returns a token only when `device_name` is provided.
- Why not Passport/JWT: no third-party OAuth clients exist; Passport is overhead. Original app used Firebase Auth — Sanctum replaces it 1:1 for email+password (social login = later packet).
- Middleware stack for `/api/v1`: `auth:sanctum` → `EnsureStoreMember` (resolves current store + role, rejects `is_active=false` with 403 ACCOUNT_DISABLED) → `SetStoreContext`.

## 2. Multi-tenancy — single database, `store_id` column scoping

- Tenancy root = `stores`. Current UI is one store per user; pivot allows many later.
- **Current store resolution**: `$user->stores()->first()` for now (single store); header `X-Store-Id` reserved for future multi-store, validated against pivot.
- `BelongsToStore` model trait: global scope `where store_id = context store` + auto-fill `store_id` on create. Applied to Category, Product, Customer, Order, StockMovement, StaffInvitation.
- **Isolation rules**: cross-tenant ids yield **404** (scoped route-model binding), never 403 (no existence leak). Public endpoints resolve tenant by `slug` and expose only whitelisted fields.
- No stancl/tenancy or DB-per-tenant: one small-business dataset per store, shared-schema is simpler, cheaper, and reporting-friendly.

## 3. Roles & permissions — enum role on pivot + Policies (no spatie)

The vocabulary is fixed and small (4 roles from `UsersPage.tsx` ROLE_CONFIG). A `role` column + a static ability map is simpler and faster than spatie/laravel-permission, which earns its keep only with dynamic per-user permissions. Revisit if custom roles ever appear in UI.

Ability matrix (derives `abilities[]` in `/auth/me`; single source: `App\Support\RoleMatrix`):

| Ability | OWNER | MANAGER | CASHIER | VIEWER |
|---|---|---|---|---|
| orders.view | ✓ | ✓ | ✓ | ✓ |
| orders.create | ✓ | ✓ | ✓ | – |
| orders.update_status | ✓ | ✓ | – | – |
| products.view / categories.view | ✓ | ✓ | ✓ | ✓ |
| products.manage (products+categories) | ✓ | ✓ | ✓ ("Manage products") | – |
| customers.view | ✓ | ✓ | ✓ | ✓ |
| customers.manage | ✓ | ✓ | ✓ | – |
| customers.delete / import / export | ✓ | ✓ | – | – |
| reports.view (finance+analytics) | ✓ | ✓ | – ("No settings", not listed) | ✓ ("Reports") |
| reports.export | ✓ | ✓ | – | – |
| settings.manage (all groups + storefront) | ✓ | ✓ ("Settings") | – | – |
| staff.view / staff.manage | ✓ | ✓ ("Staff management") | – | – |
| billing.manage | ✓ | – ("no billing") | – | – |

Implementation: `Gate::define` per ability in `AuthServiceProvider` reading `RoleMatrix`; Policies for model-level checks (`OrderPolicy`, `ProductPolicy`, `CustomerPolicy`, `StaffPolicy` incl. last-owner rule). FormRequests call `$this->user()->can('…')` in `authorize()`.

## 4. Module structure

```
app/
  Enums/                 StaffRole, OrderStatus, PaymentMethod, OrderSource,
                         StockMovementType, Currency, Language, SymbolPlacement,
                         SubscriptionPlan, Unit
  Models/                User, Store, StoreUser, StaffInvitation, Category, Product,
                         ProductVariant, Customer, Order, OrderItem, StockMovement,
                         StorefrontSetting, ReceiptSetting, IntegrationSetting,
                         Subscription, SettingsAuditLog
  Http/
    Controllers/Api/     (per 05 controller map; Public/ + Reports/ subfolders)
    Requests/            one FormRequest per write endpoint (Store*/Update* naming)
    Resources/           per 05 resource map
    Middleware/          EnsureStoreMember, SetStoreContext
  Services/              OrderService, OrderTotalsService, StockService, ProductService,
                         CustomerImportService, ReportService, SettingsService,
                         PublicOrderService, WhatsAppMessageBuilder, AiDescriptionService
  Actions/               RegisterStoreAction, InviteStaffAction, AcceptInvitationAction
  Policies/              OrderPolicy, ProductPolicy, CategoryPolicy, CustomerPolicy,
                         StaffPolicy, SettingsPolicy, SubscriptionPolicy
  Jobs/                  ImportCustomersJob, SendStaffInvitationJob (or queued mailable)
  Events/                OrderCreated, OrderCancelled, OrderCompleted, OnlineOrderPlaced,
                         LowStockDetected, CustomerCreated
  Listeners/             UpdateCustomerStats, RecordStockSale, RestoreStockOnCancel,
                         CheckLowStock, NotifyStaffOfOnlineOrder
  Notifications/         LowStockNotification (db), OnlineOrderNotification (db+mail),
                         StaffInvitationMail
  Support/               RoleMatrix, Money (rounding helpers), StoreContext, CsvStreamer
database/
  migrations/ factories/ seeders/
routes/  api.php (v1 group), public.php (storefront)
tests/   Feature/<module>/  Unit/
```

## 5. Validation
FormRequest per write endpoint; shared rules extracted (`ColorHexRule`, `StoreScopedExists` rule wrapping `Rule::exists()->where('store_id', …)`). `authorize()` does policy check so 403s are uniform. Custom `INSUFFICIENT_STOCK` etc. thrown as domain exceptions (`App\Exceptions\DomainConflictException` → 409 renderer).

## 6. Serialization
API Resources exclusively (no `->toArray()` model leaks). `OrderResource` conditionally embeds `receipt` block (`whenLoaded`/flag). Money cast: `decimal:2` casts + resources emit strings. `placed_at_local` computed with store timezone.

## 7. Business logic — services + a few single-purpose actions
Controllers: authorize → validated DTO → service → resource. All multi-table writes in `DB::transaction`. Critical services:
- **OrderTotalsService**: pure, unit-tested — input lines(+taxable flags), discount, store tax config → `{subtotal, tax_amount, total}` handling tax-included/excluded and non-taxable lines. Reused by POS create, online create, and any future edit.
- **StockService**: `deductForOrder`, `restoreForOrder`, `adjust` — always `SELECT … FOR UPDATE` on product/variant rows, writes `stock_movements` + cached `stock`/`stock_after` atomically.
- **OrderService::create**: lock store row → next order_number → totals → stock (POS immediately; ONLINE deferred to completion) → counters → events.

## 8. Events / jobs / notifications (all supported by UI evidence)
| Event | Listeners | UI evidence |
|---|---|---|
| OrderCreated | UpdateCustomerStats, CheckLowStock | customer "Balance/Total Orders" columns; low-stock alerts card |
| OrderCancelled | RestoreStockOnCancel, ReverseCustomerStats | cancelled status pill |
| OrderCompleted (online) | DeductStockOnCompletion | storefront pending→completed flow |
| OnlineOrderPlaced | NotifyStaffOfOnlineOrder (db + optional mail) | WhatsApp toggle copy: "order summary sent to your WhatsApp number" |
| LowStockDetected | LowStockNotification (db) | "LOW STOCK ALERTS" list in Analytics |
| StaffInvited | queued StaffInvitationMail | "Invite sent to …" toast, "Invite pending" badge |
Queues: Redis; mail + import on `default`; everything else sync-in-transaction listeners (counter/stock updates must be transactional, not queued).

## 9. Testing
- **Pest Feature tests** per module hitting real routes w/ RefreshDatabase (pgsql in CI, sqlite acceptable only if no PG-specific SQL leaks — CI uses postgres service container).
- **Unit tests**: OrderTotalsService (rounding, tax modes), StockService, RoleMatrix, WhatsAppMessageBuilder.
- Two-store fixture in `TestCase` helper (`actingAsRole($role)`) — every module gets at least one cross-tenant 404 test and one per-role 403 test. Full catalog in `11-test-plan.md`.

## 10. Seeders
`DemoSeeder` reproduces `src/data/mockData.ts` exactly (same products/customers/orders/settings values) so the React app renders identically against the real API — this is the acceptance harness. Factories for volume testing. Detail in `10-seeding-plan.md`.

## 11. Cross-cutting
- **CORS**: `config/cors.php` allow app origin + credentials (cookie mode).
- **Rate limiting**: `throttle:api` 120/min authed; login 5/min; public storefront per 05.
- **Uploads**: `storage/app/public/stores/{store_id}/…`, validated mimes+size, served via `/storage` symlink (S3 driver swap for prod — path stays).
- **Config**: `.env` keys — `AI_FEATURES_ENABLED`, `ANTHROPIC_API_KEY` (description suggester), `BILLING_PORTAL_URL`.
- **OpenAPI**: generate from `api-contracts.json` later; keep contracts file authoritative during build.

# Dorzak Merchant — Laravel API

Backend rebuild of the recovered Kyte SaaS, implemented from
[`../docs/backend-planning/`](../docs/backend-planning/) (start at `MASTER_BACKEND_BLUEPRINT.md`).

## Status — backend complete (TP-01 … TP-11)

- **TP-01 Foundation & Auth** — Sanctum SPA auth, single-DB tenancy (`StoreScope` + `StoreContext`), role/ability gate matrix.
- **TP-02 Settings** — grouped `GET /settings` + 8 group writes, uploads, append-only audit log.
- **TP-03 Staff & Subscription** — invite → accept lifecycle, member CRUD with owner/last-owner guards, subscription read + portal stub.
- **TP-04 Catalog** — categories, products, variants, image upload, and the append-only stock ledger (INITIAL/ADJUSTMENT).
- **TP-05 Customers** — CRM CRUD, search/sort, whole-store summary, CSV export/import (queued over 500 rows).
- **TP-06 Orders & Checkout (core)** — `OrderTotalsService` money kernel, `OrderService`, stock deduct/restore
  (SALE/CANCEL_RETURN), sequential per-store order numbers, status transitions, low-stock notifications, CSV export.
- **TP-07 Reports** — `/reports/finance` + `/reports/analytics` with period/timezone handling and cost-snapshot profit.
- **TP-08 Public storefront** — anonymous slug-resolved store card + catalog + WhatsApp online-order checkout.
- **TP-09 Billing** — subscription summary + owner-only portal/invoice stubs (Stripe deferred).
- **TP-11 Hardening** — security-headers middleware, `DemoSeeder` (mockData parity harness) + parity test,
  `customers:recalculate-stats` and `stock:reconcile` commands, CSV formula-escaping.
- **TP-10 React client** — `src/api/apiClient.ts` (Sanctum-cookie fetch wrapper) + `src/api/endpoints.ts`
  in the frontend app. Remaining: rewire the Zustand stores off `mockApi` and add the login/edit pages
  (see docs/backend-planning/09-react-laravel-integration-map.md).

**179 tests / 683 assertions passing; Pint clean. 56 API routes.**

Seed the demo store (mockData parity): `php artisan migrate:fresh --seed`
(owner `merchant@dorzak.com` / `password`).

### Settings notes
- General / Business / Currency / Taxes / Payments persist on the `stores` row; Receipts / Integrations /
  Storefront are separate 1:1 tables.
- Business rules enforced: payments require ≥1 in-person POS method (WhatsApp is online-only), QAR forces the
  symbol before the amount, storefront slug is lowercased + regex + globally unique + reserved-word blocked.
- Writes require `settings.manage` (owner/manager); reads are open to any active member.

## Stack & deviations from the blueprint

| Blueprint said | Built with | Why |
|---|---|---|
| Laravel 11 | **Laravel 13.18** | Latest stable that installs cleanly on this box's PHP 8.5; same streamlined skeleton the blueprint's `bootstrap/app.php` guidance targets. |
| PostgreSQL 16 | **pgsql in `.env.example` (prod/CI); sqlite locally + in tests** | No Postgres server on the dev box. TP-01's three tables use no PG-specific features (partial indexes first appear in TP-04), so sqlite is a faithful test substrate. Switch to the pgsql block in `.env` for production. |
| Pest | **PHPUnit** | Already installed; the blueprint permits either. |

Everything else follows the blueprint: Sanctum SPA cookie auth (+ bearer tokens for
tooling), `store_id` tenancy via `BelongsToStore` + `StoreContext`, the `RoleMatrix`
ability map (docs 06 §3) driving gates and `/auth/me` abilities.

## What TP-01 delivers

- **Migrations**: `stores`, `store_user` (+ framework tables). `users` matches the schema doc as-shipped.
- **Models**: `User` (HasApiTokens + memberships), `Store` (tenant root), `StoreUser` (role pivot).
- **Support**: `StaffRole` enum, `RoleMatrix`, `StoreContext`, `Concerns\BelongsToStore` trait.
- **Middleware**: `SetStoreContext` (populate) + `EnsureStoreMember` (guard, 403 `ACCOUNT_DISABLED`), grouped as `store`.
- **Auth endpoints** (`/api/v1`): `POST auth/register`, `POST auth/login` (throttled 5/min),
  `GET auth/me`, `POST auth/logout`, `POST auth/forgot-password`, `POST auth/reset-password`.
- **Gates**: one per ability, resolving the user's role in the current store.

## Run it

```bash
cp .env.example .env        # then set DB_* (or keep sqlite for local)
composer install
php artisan key:generate
touch database/database.sqlite   # if using sqlite locally
php artisan migrate
php artisan serve
php artisan test             # 30 passing
./vendor/bin/pint --test     # style check
```

### Smoke test
```bash
curl -X POST localhost:8000/api/v1/auth/register -H 'Accept: application/json' \
  -d 'name=Owner&email=o@x.com&password=secret-password&password_confirmation=secret-password&business_name=My+Store&device_name=cli'
# → 201 with { data: { token, user, store, role: "OWNER", abilities: [17] } }
```

## Next

TP-02 (settings + staff + subscription). `RegisterStoreAction` is the extension point:
it must additionally seed the 1:1 settings rows (storefront/receipt/integration) and a
FREE subscription once those tables land.

# 07 — Implementation Roadmap

Nine milestones; each ends green (`php artisan test`) and demoable. Task-packet ids (doc 08) in brackets.

## Milestone 1 — Laravel foundation & auth  [TP-01]
- New Laravel 11 app, PostgreSQL config, Pest, Pint, Redis queue config.
- Sanctum SPA setup (`sanctum/csrf-cookie`, session domain, CORS credentials).
- Migrations: users, stores, store_user, framework tables.
- Enums: StaffRole (+ RoleMatrix support class, Gate definitions).
- Endpoints: login/logout/me/register/forgot/reset. Middleware EnsureStoreMember + SetStoreContext. `BelongsToStore` trait.
- Tests: auth happy/sad, register bootstraps store+pivot, disabled member 403, gate matrix unit test.
- **Acceptance**: `POST /auth/register` then `GET /auth/me` returns store + abilities; test suite green.

## Milestone 2 — Store settings, staff, subscription display  [TP-02, TP-03]
- Migrations: storefront_settings, receipt_settings, integration_settings, staff_invitations, subscriptions, settings_audit_logs.
- `GET /settings` + 8 group PUTs + audit logging; upload endpoints (banner/logo).
- Staff module: list, invite (mail), accept (public), patch, delete, resend/cancel; last-owner guard; token revocation on deactivate.
- `GET /subscription` (seeded PRO row) + portal/invoice stubs.
- **Acceptance**: React Settings page + Users page fully functional against API; invitation email delivered (Mailpit); settings persist across sessions (replaces localStorage).

## Milestone 3 — Catalog  [TP-04]
- Migrations: categories, products, product_variants, stock_movements.
- Category CRUD + reorder; Product CRUD + variant sync + image upload; StockService (INITIAL/ADJUSTMENT); AI description endpoint behind flag.
- **Acceptance**: Products page, Product create page, Categories page, POS grid all render/write real data; stock pills correct; variant sum rule enforced.

## Milestone 4 — Customers  [TP-05]
- Migration: customers. CRUD, search/sort/pagination + meta summary, detail w/ recent orders (empty until M5), CSV export/import (+ queued path).
- **Acceptance**: Customers page fully wired incl. delete confirm, summary cards, sorting.

## Milestone 5 — Orders & checkout  [TP-06]
- Migrations: orders, order_items. OrderTotalsService (unit-tested first), OrderService, stock deduction, counters, events/listeners, order_number sequence.
- Endpoints: orders list (+summary meta), show (+receipt block), create, status patch, export CSV.
- **Acceptance**: POS end-to-end sale → order appears in Orders + Transactions with correct totals/tax; receipt modal renders from `GET /orders/{id}`; cancel restores stock; Sales page filters work.

## Milestone 6 — Reports  [TP-07]
- ReportService; `/reports/finance` (+export) and `/reports/analytics`; period/timezone handling; low-stock + top-products queries.
- **Acceptance**: Finances and Analytics pages show numbers matching seeded orders (hand-verified fixture expectations in tests).

## Milestone 7 — Public storefront  [TP-08]
- Public routes: store card, catalog, online order create; WhatsAppMessageBuilder; OnlineOrderPlaced notification; slug/reserved validation already in M2 — wire 404-when-disabled; rate limits.
- **Acceptance**: Storefront preview page runs on public API; online order lands as PENDING/WHATSAPP; wa.me URL opens with correct prefilled text; completing it deducts stock.

## Milestone 8 — Billing & integrations hardening  [TP-09]
- Stripe Cashier (or documented stub) for portal/invoice; Facebook connect stub persisting page name; feature list on subscription resource.
- **Acceptance**: Billing page + Subscription tab fully rendered from API; owner-only enforcement verified.

## Milestone 9 — Frontend integration & hardening  [TP-10, TP-11]
- Replace `src/api/mockApi.ts` with `apiClient.ts` (fetch wrapper: base URL, CSRF, 401→login redirect, error normalization); wire stores; loading/error/empty states; role-gated sidebar from `abilities`.
- Frontend gaps: product edit mode (G-1), customer edit (G-2), product delete confirm (G-3), discount input on POS, date-range filter wiring.
- Backend hardening: `customers:recalculate-stats` + `stock:reconcile` artisan commands, index review (EXPLAIN on report queries), throttle tuning, security checklist (doc 12), DemoSeeder parity check, Playwright e2e against seeded API.
- **Acceptance**: whole app usable with mock API deleted; Playwright suite green; security checklist signed off.

Dependency graph: M1 → M2 → {M3, M4} → M5 → {M6, M7} → M8 → M9. M3/M4 parallelizable; M6/M7 parallelizable.

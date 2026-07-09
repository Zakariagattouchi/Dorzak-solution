# MASTER BACKEND BLUEPRINT — Dorzak Merchant (recovered Kyte SaaS)

**Audience**: the backend sub-agent building the Laravel API. Read this file, then open the linked
documents per task packet. You should never need to re-analyze the React frontend — but it lives at
the repo root (`src/`) and is the final arbiter when a contract detail feels ambiguous.

## What this product is
A merchant commerce SaaS recovered from an authorized deployment of Kyte-web and rebranded
**Dorzak Merchant**: POS checkout ("Sell"), product catalog with variants + stock ledger, customer CRM,
orders/transactions, finance & analytics reports, a public online storefront with WhatsApp checkout,
staff roles (OWNER/MANAGER/CASHIER/VIEWER), 9-tab business settings, and a subscription plan display.
Bilingual EN/AR, multi-currency (QAR-aware), single store per account (tenant-ready schema).

## Stack decisions (final)
- **Laravel 11**, PHP 8.3, **PostgreSQL 16** (justified in 04 — partial unique indexes, reporting), Redis, Pest.
- **Sanctum SPA cookie auth** (tokens available for tooling) — 06 §1.
- Single-DB tenancy via `store_id` + `BelongsToStore` global scope; cross-tenant = 404 — 06 §2.
- Roles: enum on `store_user` pivot + Gate/Policy matrix (no spatie) — 06 §3.
- Controllers thin → FormRequests → Services (`OrderTotalsService` is the money kernel) → API Resources.
- Money computed **server-side only**; orders snapshot everything (names, prices, cost, tax rate).

## Document index
| Doc | Contents |
|---|---|
| [01-frontend-analysis.md](01-frontend-analysis.md) | evidence-linked forensic analysis: pages, forms, tables, modals, roles, mock→real state map, gaps G-1…G-8 |
| [02-page-backend-map.md](02-page-backend-map.md) + [page-backend-map.json](page-backend-map.json) | per-page: endpoints, payloads, validation, permissions, side effects, edge cases, tests |
| [03-domain-model.md](03-domain-model.md) | entities, relationships, enums, 9 domain invariants |
| [04-database-schema.md](04-database-schema.md) + [database-schema.json](database-schema.json) | 16 tables, columns/types/indexes/FKs/cascades, engine rationale |
| [05-api-contracts.md](05-api-contracts.md) + [api-contracts.json](api-contracts.json) | 57 endpoints with full request/response/validation/status codes + controller/service/resource map |
| [06-laravel-implementation-strategy.md](06-laravel-implementation-strategy.md) | auth, tenancy, role matrix, module layout, events/jobs, testing strategy |
| [07-implementation-roadmap.md](07-implementation-roadmap.md) | 9 milestones with acceptance criteria + dependency graph |
| [08-sub-agent-task-packets.md](08-sub-agent-task-packets.md) | 11 task packets (TP-01…TP-11): exact files, migrations, tests, acceptance |
| [09-react-laravel-integration-map.md](09-react-laravel-integration-map.md) | per React page: API functions, states, cache rules; apiClient design |
| [10-seeding-plan.md](10-seeding-plan.md) | DemoSeeder = mockData parity harness; volume seeder |
| [11-test-plan.md](11-test-plan.md) | full test catalog (unit money/stock kernels + feature per module + blanket tenant/role rules) |
| [12-security-plan.md](12-security-plan.md) | authz/tenancy/uploads/rate-limits/PII + launch checklist |

## Build order (from 07/08)
TP-01 Foundation+Auth → TP-02 Settings → TP-03 Staff+Subscription → TP-04 Catalog ∥ TP-05 Customers
→ **TP-06 Orders (core)** → TP-07 Reports ∥ TP-08 Public storefront → TP-09 Billing → TP-10 React integration → TP-11 Hardening.

## Non-negotiable invariants (repeated because they get violated)
1. Never trust client money: recompute subtotal/tax/total in `OrderTotalsService`; ignore client-sent totals.
2. Stock changes only via `StockService` inside a transaction with a `stock_movements` row (POS deducts on create; ONLINE deducts on completion; cancel restores exactly).
3. Tax applies only to `taxable` line items; support tax-included and tax-excluded modes (store setting).
4. Enum casing in API responses is SCREAMING (COMPLETED, CARD) — the typed frontend depends on it.
5. Cross-tenant access = 404. Every module ships the blanket tenant + role tests.
6. Order rows snapshot customer_name/phone, product_name, variant_name, unit_price, unit_cost, tax_rate — receipts must survive catalog/customer deletion.
7. DemoSeeder must keep the React UI pixel-identical to the mock-data version (parity test pins it).

## Known assumptions to confirm with the product owner
- Store timezone column (reports need it) — default per country.
- Duplicate customer phone blocks creation (422 + duplicate_customer_id) rather than warns.
- Online orders deduct stock at completion, not placement.
- Discount reduces the tax base proportionally (tax-excluded mode).
- Stripe as billing provider (portal/invoice endpoints stubbed until then).
- Branch/multi-store ("All branches" topbar text) deferred; schema is ready.
- AI endpoints (description suggest, photo auto-registration) optional/flagged.

## Summary metrics
- Pages analyzed: **15** (14 routed views + auth) · Entities: **16** · DB tables: **16 domain + 6 framework**
- API endpoints: **57** · Task packets: **11** · Roles: **4** · Events: **6** · Seeded demo parity: mockData.ts

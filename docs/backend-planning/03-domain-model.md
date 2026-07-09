# 03 — Domain Model

## Bounded context overview

One Laravel app, five cohesive modules. Everything hangs off the tenant root **Store**.

```
Identity & Tenancy      Catalog             Sales                CRM            Platform
────────────────────    ─────────────────   ──────────────────   ────────────   ─────────────────
User                    Category            Order                Customer       Subscription
Store (tenant root)     Product             OrderItem                           StaffInvitation
StoreUser (role pivot)  ProductVariant      StockMovement                       SettingsAuditLog
Store settings (1:1s):
  StorefrontSetting
  ReceiptSetting
  IntegrationSetting
```

## Entities and why they exist (evidence-linked)

| Entity | Evidence | Notes |
|---|---|---|
| **User** | Users page staff list; login route | Global identity; a user can in principle belong to several stores (pivot), but UI shows one. |
| **Store** | `accountInfo` object (mockData.ts lines 71–115) — business identity, contact, address, locale, currency, tax config | Tenant root. All domain rows carry `store_id`. `accountInfo`'s ~40 fields split across Store + three 1:1 settings models to keep the table sane. |
| **StoreUser** (pivot) | ROLE_CONFIG roles OWNER/MANAGER/CASHIER/VIEWER; active toggle | `role` enum + `is_active` on pivot; a store must always have ≥1 active owner. |
| **StaffInvitation** | Invite form creates `active:false` member with "Invite pending" badge | Token-based email invite; on accept becomes User + StoreUser. |
| **Category** | Category chips, CategoriesPage, CategoryModal (name, color) | `products_count` is derived (withCount), never stored. |
| **Product** | Product interface (mockData) + both product forms | Includes commerce flags (taxable, show_in_online_store, is_featured), stock config, presentation (label_name/color, image), `reduced_price` from create page. |
| **ProductVariant** | `ProductVariant` interface; variant repeater in both forms | Own price/stock/sku. Rule: when variants exist, parent `stock` = Σ variants (server-maintained). |
| **Customer** | Customer interface + CRM page (address, city, taxId, notes) | `total_orders`/`total_spent` are **cached counters** (mockApi mutates them on sale) maintained by order events + reconcile command. |
| **Order** | Order interface; PaymentModal payload; HAR `POST /sale` | Snapshots: customer_name/phone, tax_rate, all money columns. `source` pos|online distinguishes POS sales from storefront WhatsApp orders. |
| **OrderItem** | OrderItem interface (productId, productName, quantity, unitPrice, variantName) | Adds `unit_cost` snapshot (for gross-profit report) and `line_total`. FK `product_id` nullOnDelete so receipts outlive catalog changes. |
| **StockMovement** | Stock pills, low-stock alerts, order→stock deduction implied by "Unlimited Catalog & Stock Sync" | Append-only ledger: initial / sale / cancel_return / adjustment / restock. Product.stock is the cached head of this ledger. |
| **StorefrontSetting** | StorefrontPage 4 tabs | 1:1 with Store; global-unique `slug`. |
| **ReceiptSetting** | Settings RECEIPTS tab; ReceiptModal | 1:1. |
| **IntegrationSetting** | Settings INTEGRATIONS tab | 1:1; pixel/GA ids, facebook_connected. |
| **Subscription** | BillingPage; HAR `/subscription/summary` | One active row per store; provider fields nullable until Stripe wired. |
| **SettingsAuditLog** | ASSUMPTION (cheap safety for money-affecting settings) | who/when/group/old/new JSON. |

## Relationship map

```
User          ─ belongsToMany ─ Store (store_user: role, is_active)
Store         ─ hasOne  ─ StorefrontSetting, ReceiptSetting, IntegrationSetting, Subscription(active)
Store         ─ hasMany ─ Category, Product, Customer, Order, StockMovement, StaffInvitation, SettingsAuditLog
Category      ─ hasMany ─ Product                     (products.category_id nullable, nullOnDelete)
Product       ─ belongsTo ─ Category
Product       ─ hasMany ─ ProductVariant (cascade), OrderItem (nullOnDelete), StockMovement
Customer      ─ hasMany ─ Order                       (orders.customer_id nullable, nullOnDelete)
Order         ─ belongsTo ─ Customer(nullable), User creator(nullable)
Order         ─ hasMany ─ OrderItem (cascade), StockMovement (via reference)
OrderItem     ─ belongsTo ─ Product(nullable), ProductVariant(nullable)
StaffInvitation ─ belongsTo ─ Store, User inviter
```

## Enums (app/Enums, backed strings, mirrored in API casing the frontend uses)

- `StaffRole`: OWNER, MANAGER, CASHIER, VIEWER
- `OrderStatus`: PENDING, COMPLETED, CANCELLED
- `PaymentMethod`: CASH, CARD, TRANSFER, WHATSAPP
- `OrderSource`: POS, ONLINE
- `StockMovementType`: INITIAL, SALE, CANCEL_RETURN, ADJUSTMENT, RESTOCK
- `SymbolPlacement`: BEFORE, AFTER
- `Language`: EN, AR
- `Currency`: QAR, USD, EUR, GBP, CAD, BRL, MXN, COP, ARS, AUD (symbol map lives on the enum)
- `SubscriptionPlan`: FREE, PRO, ENTERPRISE (UI shows PRO + Enterprise upsell)
- `Unit`: PCS, KG, G, L, ML, BOX, M, BOTTLE, OTHER

## Domain invariants (enforce in services + DB where possible)

1. Every store has exactly one active OWNER at minimum; owner pivot cannot be deactivated/removed while sole owner.
2. `orders.total = subtotal − discount + tax_amount (+ delivery_fee for online)`; recomputed server-side, checked by DB-level `CHECK` (PG) and unit tests. Client-provided totals are ignored.
3. Tax base = Σ taxable line totals only. When `tax_included_in_price`, tax_amount is extracted (`base − base/(1+rate)`) and total is unchanged by tax.
4. Stock never mutated except through `StockService` writing a `stock_movements` row in the same transaction (POS sale deducts on create; online deducts on transition to COMPLETED; CANCELLED restores exactly what was deducted).
5. When a product has variants, parent stock = Σ variant stock; direct parent stock writes are rejected for variant products.
6. `order_number` is per-store sequential (`ORD-{n}` starting 1000), assigned inside the create transaction (store-row lock) — replaces mock's random 4-digit id.
7. Money: `decimal(12,2)`, half-up rounding at line level then summed (documented so the React formatter always agrees).
8. Soft-deleted products/customers stay resolvable for historical orders via snapshots; unique indexes on sku/slug/phone are partial (`WHERE deleted_at IS NULL`).
9. All queries are store-scoped: `BelongsToStore` trait (global scope on authenticated store) + route-model binding scoped to store — a foreign id from another store is a 404, never 403.

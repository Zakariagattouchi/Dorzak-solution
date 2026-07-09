# 02 — Page-by-Page Backend Requirement Map

Conventions used below:
- All endpoints are prefixed `/api/v1` and require Sanctum auth + store scoping unless marked **PUBLIC**.
- "Perm" values reference the ability matrix in `06-laravel-implementation-strategy.md` §3.
- Machine-readable mirror: `page-backend-map.json`.
- Money is decimal string in responses (`"49.99"`), computed server-side.

---

## PAGE 1: POS / Sell
- **Frontend route**: `/checkout` (index redirect) — `src/pages/pos/POSPage.tsx`, `src/components/modals/PaymentModal.tsx`
- **Purpose**: cashier selects products into a cart, optionally attaches a customer, charges via CASH/CARD/TRANSFER, produces a completed order + receipt.
- **User roles**: owner, manager, cashier (viewer: no).
- **Displayed data**: product grid (image, name, SKU, price, stock), category chips w/ product counts, customer dropdown (name + phone), cart lines, subtotal/total.
- **Components**: product grid cards, category chips, search input, CartPanel, quantity steppers, customer select, PaymentModal, ReceiptModal (after sale, G: mock closes without showing — receipt endpoint still needed).
- **Forms**: PaymentModal (F-4); inline "New" customer button opens CustomerModal (F-2).
- **Buttons/actions**:
  - product card click → cart add (frontend only)
  - qty +/−, remove line, customer select, discount (cartStore supports `discount`; no visible input in reconstructed UI — **ASSUMPTION: discount input existed; keep `discount` in the order payload**)
  - `Charge $X` → opens PaymentModal → `Complete Sale` → **POST /orders**
  - `Add Product` → PRODUCT_CREATE modal (see Page 3)
- **Backend entities**: products, categories, customers, orders, order_items, stock_movements.
- **API endpoints**:
  - `GET /products?per_page=200&active=1` (boot; POS needs the sellable set)
  - `GET /categories`
  - `GET /customers?search=` (dropdown; server search for scale)
  - `POST /orders` (source=pos)
- **POST /orders request** (canonical; server recomputes all money):
```json
{
  "customer_id": 3,
  "items": [
    {"product_id": 101, "variant_id": null, "quantity": 2},
    {"product_id": 103, "variant_id": 7,   "quantity": 1}
  ],
  "discount": 5.00,
  "payment_method": "CARD",
  "status": "COMPLETED",
  "notes": null,
  "source": "pos"
}
```
- **Response 201**: full `OrderResource` — id, order_number, status, payment_method, customer{id,name,phone}, items[{product_id, product_name, variant_name, quantity, unit_price, line_total}], subtotal, discount, tax_rate, tax_amount, total, placed_at, created_by.
- **Validation**:
  - items required|array|min:1; items.*.product_id required|exists(store-scoped)|product is_active
  - items.*.variant_id nullable|exists and must belong to that product
  - items.*.quantity required|integer|min:1|max:9999
  - customer_id nullable|exists (store-scoped)
  - discount nullable|numeric|min:0 and ≤ recomputed subtotal
  - payment_method required|in enabled store methods (settings PAYMENTS tab!)
  - status in:COMPLETED,PENDING (cancelled not creatable)
  - stock check when product.track_stock: reject with **409** `INSUFFICIENT_STOCK` listing offending lines (or accept-with-negative if store setting later — default: reject)
- **Permissions**: `orders.create` (owner/manager/cashier).
- **Side effects**: deduct stock (product or variant level) + `stock_movements` rows; bump customer cached counters; fire `OrderCreated`; low-stock check → `LowStockDetected`.
- **Edge cases**: empty cart (422), inactive/deleted product mid-session (422 w/ per-item error), walk-in (customer_id null → customer_name "Walk-in Customer" snapshot), disabled payment method (422), tax-included pricing (taxIncludedInPrice=true → extract tax, don't add), non-taxable items excluded from tax base (frontend mock taxed everything — fix server-side), concurrent stock race (row lock in transaction).
- **Tests**: create order happy path; walk-in; totals recomputed and client totals ignored; rejects empty cart / bad product / qty 0 / disabled method; deducts stock & writes movement; 409 on insufficient stock; variant pricing used when variant_id set; tax excludes non-taxable items; tax-included mode; discount > subtotal rejected; cashier can create, viewer 403.
- **Priority**: M5 (core), depends on M3 catalog + M4 customers.

---

## PAGE 2: Orders & Sales History
- **Frontend route**: `/orders` — `src/pages/orders/OrdersPage.tsx`, `ReceiptModal.tsx`
- **Purpose**: full order history w/ status filter, summary pills, receipt view/print, CSV export.
- **User roles**: owner, manager, cashier (view); viewer (view).
- **Displayed data**: summary pills (total revenue, completed count, pending count — computed over the filtered set), table rows.
- **Tables**: orders table (columns §6 of 01-frontend-analysis).
- **Modals**: RECEIPT (order snapshot + store header info + print) — non-destructive.
- **Buttons**: Export CSV; status chips; search; row → receipt.
- **API endpoints**:
  - `GET /orders?search=&status=&date_from=&date_to=&payment_method=&page=&per_page=&sort=-placed_at`
    - response: `{data: OrderResource[], meta:{current_page,last_page,per_page,total, summary:{revenue,completed_count,pending_count,cancelled_count,tax_total,discount_total}}}` — summary computed over the *filtered* query so pills stay correct.
  - `GET /orders/{order}` (receipt detail incl. items + store receipt settings snapshot)
  - `PATCH /orders/{order}/status` body `{"status":"COMPLETED|CANCELLED"}` (G-5; PENDING→COMPLETED marks transfer confirmed; →CANCELLED restores stock, reverses customer counters)
  - `GET /orders/export?same-filters` → streamed CSV (columns: order_number, date, customer, phone, payment_method, status, subtotal, discount, tax, total, items_count)
- **Validation**: status transition matrix — PENDING→COMPLETED, PENDING→CANCELLED, COMPLETED→CANCELLED (owner/manager only); CANCELLED terminal.
- **Permissions**: `orders.view` all roles; `orders.update_status` owner/manager; export `reports.export` owner/manager.
- **Events**: `OrderCancelled` → restore stock movement (`type=cancel_return`), decrement customer counters.
- **Edge cases**: date-range filter uses `placed_at` in store timezone (**ASSUMPTION: store timezone column, default from country**); cancelling already-cancelled = 409; receipt for order w/ deleted product still renders (snapshots).
- **Tests**: list w/ each filter combo; summary matches filters; pagination meta; status transition matrix incl. forbidden ones; cancel restores stock; CSV has header + rows and respects filters; viewer can list but cannot patch.
- **Priority**: M5.

---

## PAGE 3: Products Catalog
- **Frontend route**: `/products` (+ modal PRODUCT_CREATE from POS/topbar) — `ProductsPage.tsx`, `ProductModal.tsx`
- **Purpose**: inventory table; create/edit/delete products; stock pills.
- **User roles**: owner, manager, cashier ("Manage products" per ROLE_CONFIG); viewer read-only.
- **API endpoints**:
  - `GET /products?search=&category_id=&status=&stock=low|out&page=&per_page=&sort=`
  - `POST /products` (F-1 payload below)
  - `GET /products/{product}`
  - `PUT /products/{product}` (full form re-submit; variants synced by id: missing=delete, id present=update, no id=create)
  - `DELETE /products/{product}` → soft delete (order_items keep snapshots)
  - `POST /products/{product}/image` multipart (replaces `imageUrl` free-text; store keeps accepting `image_url` string for remote images) 
  - `POST /ai/product-description` body `{"name": "..."}` → `{description}` (**optional packet; UI: "Suggest description (AI)"**)
- **POST /products request**:
```json
{
  "name": "Dorzak Signature Cotton Hoodie",
  "description": "…",
  "price": 49.99, "reduced_price": null, "cost": 18.00,
  "category_id": 1,
  "sku": "HOOD-001", "unit": "pcs",
  "image_url": "https://…",
  "label_name": "NEW", "label_color": "#1890ff",
  "taxable": true, "track_stock": true, "stock": 45, "min_stock": 10,
  "show_in_online_store": true, "is_featured": true,
  "variants": [{"name":"Small / Black","price":49.99,"stock":15,"sku":"HOOD-S-BLK"}]
}
```
- **Validation**: name required|max:160; price required|numeric|min:0|max:999999.99; reduced_price nullable|lt:price; cost nullable|numeric|min:0; category_id nullable|exists store-scoped; sku nullable|unique per store (auto-generate `PROD-###` when blank — mirror frontend default); unit in:pcs,kg,g,l,ml,box,m,bottle,other; stock/min_stock required_if:track_stock,true|integer|min:0; label_color & colors regex `^#[0-9a-fA-F]{6}$`; variants.*.name required|distinct; variants.*.price nullable (fallback product price); variants.*.sku unique per store.
- **Side effects**: initial `stock_movements` row (`type=initial`); when stock edited later, write `type=adjustment` diff.
- **Permissions**: `products.view` all; `products.manage` owner/manager/cashier.
- **Edge cases**: delete product referenced by orders (soft delete; snapshots keep receipts intact); duplicate SKU 422; variant stock vs product stock (**rule: when variants exist, product.stock = Σ variant stock, maintained server-side**); reduced_price display (UI shows one price — return both, `effective_price` accessor).
- **Tests**: CRUD; sku uniqueness per store (two stores may share); variant sync add/update/remove; soft-delete keeps order history; stock pill data (low/out filters); viewer 403 on write.
- **Priority**: M3.

## PAGE 4: Product Create/Edit (full page)
- **Route**: `/products/create` — `ProductCreatePage.tsx`. Same endpoints as Page 3. Extra UI: label preview (frontend-only), "Automatic Registration" AI photo scan (BETA — **deferred packet**, endpoint reserved `POST /ai/product-from-photo`), Highlight toggle (=is_featured), Show on Online Catalog toggle. Edit mode = G-1: page must load `GET /products/{id}` and `PUT`. Priority M3.

---

## PAGE 5: Categories
- **Route**: `/categories` — `CategoriesPage.tsx`, `CategoryModal.tsx`
- **API**:
  - `GET /categories` → `[{id,name,color,products_count,sort_order}]` (withCount)
  - `POST /categories` `{name required|max:80|unique per store, color hex default #3b82f6}`
  - `PUT /categories/{category}`, `DELETE /categories/{category}` (G-4; delete sets products.category_id=null, 200 with `{reassigned_products: n}`)
  - `PATCH /categories/reorder` body `{ids:[…]}` (**ASSUMPTION — chips have a stable order; cheap to include**)
- **Permissions**: view all; manage owner/manager/cashier (products bundle).
- **Tests**: create/unique/delete-nulls-products/count accuracy.
- **Priority**: M3.

---

## PAGE 6: Customers CRM
- **Route**: `/customers` — `CustomersPage.tsx`, `CustomerModal.tsx`
- **Displayed**: summary cards (total customers, total spent, avg LTV), sortable table, detail side panel w/ last 3 orders, WhatsApp deep link, maps link.
- **API**:
  - `GET /customers?search=&sort=name|-name|total_spent|-total_spent&page=&per_page=` → meta.summary `{count,total_spent,avg_ltv}`
  - `POST /customers` `{name req|max:120, phone req|max:32, email nullable|email, address, city, tax_id, notes nullable}`
  - `GET /customers/{customer}` → resource + `recent_orders` (last 3, id/order_number/total)
  - `PUT /customers/{customer}` (G-2)
  - `DELETE /customers/{customer}` → soft delete; orders keep name/phone snapshot; confirm dialog exists in UI
  - `GET /customers/export` → CSV; `POST /customers/import` multipart CSV → `{imported, skipped, errors[]}` (queued job when >500 rows)
- **Validation extras**: phone: strip non-digits for wa.me — store raw + `phone_normalized` for uniqueness check (`unique per store on phone_normalized`, ASSUMPTION: duplicates warned not blocked → return 422 with `duplicate_customer_id` so UI can decide; default block).
- **Side effects**: none on CRUD; counters maintained by order events; `customers:recalculate-stats` artisan reconcile command.
- **Permissions**: view owner/manager/cashier/viewer; manage owner/manager/cashier; delete owner/manager; import/export owner/manager.
- **Tests**: search on 3 fields; both sorts; summary math; delete keeps order snapshots; counters update on order create/cancel; import happy/malformed rows.
- **Priority**: M4.

---

## PAGE 7: Transactions Log
- **Route**: `/sales` — `SalesPage.tsx`
- Same data source as orders. **API**: reuse `GET /orders` with `payment_method` filter; summary must include `tax_total`, `discount_total`, `revenue` (already specified). No new endpoints. Viewer allowed. Priority M5 (free once orders list lands).

---

## PAGE 8: Finances & Cash Flow
- **Route**: `/finances` — `FinancesPage.tsx`
- **Displayed**: gross revenue, net revenue (gross − tax), tax collected (+rate), pending revenue; cash-flow by method w/ % bars; adjustments (discounts, taxes owed, estimated net); recent entries list; DAILY/WEEKLY/MONTHLY chips (not wired in mock → backend param).
- **API**: `GET /reports/finance?period=daily|weekly|monthly|all&date_from=&date_to=`
```json
{"data":{
  "gross_revenue":"278.77","net_revenue":"256.93","tax_collected":"21.84","tax_rate":8.5,
  "pending_revenue":"68.90","discount_total":"5.00",
  "completed_orders":2,
  "by_method":{"CARD":"112.27","CASH":"97.60","TRANSFER":"68.90","WHATSAPP":"0.00"},
  "entries":[{"order_id":1,"order_number":"ORD-9821","customer_name":"Sarah Jenkins","items_count":2,"date":"2026-07-05 14:32","payment_method":"CARD","tax_amount":"8.79","total":"112.27"}]
}}
```
- Export CSV button → `GET /reports/finance/export?…`.
- **Permissions**: `reports.view` owner/manager/viewer (cashier: **no** — ROLE_CONFIG gives cashier no reports).
- **Tests**: aggregates per period boundary (store timezone), pending excludes cancelled, method breakdown sums to gross.
- **Priority**: M6.

---

## PAGE 9: Analytics & Reports
- **Route**: `/analytics` (alias `/reports`) — `ReportsPage.tsx`
- **Displayed**: KPI cards (net revenue, gross profit, AOV, total orders), revenue by payment method, inventory health (total products, low-stock count + top 3), top selling products (real sales, replacing mock math), products by category.
- **API**: `GET /reports/analytics?period=today|week|month|all`
```json
{"data":{
  "kpis":{"revenue":"278.77","orders":3,"avg_order_value":"92.92","gross_profit":"152.47"},
  "by_method":{"CARD":"112.27","CASH":"97.60","TRANSFER":"68.90"},
  "inventory":{"total_products":6,"low_stock_count":1,"out_of_stock_count":0,
    "low_stock":[{"id":5,"name":"Ergonomic Desk Mat","stock":18,"min_stock":5}]},
  "top_products":[{"id":1,"name":"…","image_url":"…","quantity_sold":2,"revenue":"99.98"}],
  "by_category":[{"id":1,"name":"Apparel & Fashion","products_count":14,"revenue":"99.98"}]
}}
```
  - gross_profit = Σ over completed order items of `(unit_price − product.cost) × qty` (cost snapshot on order_items — add `unit_cost` snapshot column).
- **Permissions**: `reports.view` (owner/manager/viewer).
- **Tests**: top products ranked by revenue from order_items; low stock uses `stock <= min_stock AND track_stock`; period filters; profit uses cost snapshot.
- **Priority**: M6.

---

## PAGE 10: Online Storefront Settings
- **Route**: `/catalog` — `StorefrontPage.tsx`
- **API**:
  - `GET /settings` (single settings envelope, see Page 12)
  - `PUT /settings/storefront` body: `{online_store_enabled, store_slug, store_bio, banner_url, logo_url, accent_color, allow_delivery, allow_pickup, delivery_fee, free_delivery_threshold, min_order_amount, whatsapp_ordering_enabled, show_out_of_stock_online}`
  - `POST /settings/storefront/banner` + `/logo` multipart uploads (front now uses URL inputs; keep both).
- **Validation**: store_slug required_if enabled|regex `^[a-z0-9-]{3,40}$`|unique global (it's a subdomain)|not-in reserved list (www, api, admin, app…); delivery_fee/free_delivery_threshold/min_order_amount numeric|min:0; accent_color hex.
- **Side effect**: slug change invalidates old public URL (301 optional — deferred).
- **Permissions**: owner/manager (settings).
- **Tests**: slug uniqueness/reserved; toggle persists; validation bounds.
- **Priority**: M7.

---

## PAGE 11: Public Storefront (preview today, real public site tomorrow)
- **Route**: `/catalog/preview` — `StorefrontPreviewPage.tsx` (renders outside AppShell = truly public layout)
- **PUBLIC API** (no auth, rate-limited, keyed by slug):
  - `GET /api/public/stores/{slug}` → store card: business_name, phone, whatsapp, bio, banner_url, logo_url, accent_color, currency/symbol/placement, fulfillment config, whatsapp_ordering_enabled. 404 when `online_store_enabled=false`.
  - `GET /api/public/stores/{slug}/catalog?category_id=&search=` → categories + products where `show_in_online_store=true` (+ out-of-stock included only when `show_out_of_stock_online`, flagged `in_stock:false`). Product fields: id, name, description, price, reduced_price, image_url, category, in_stock, variants (name/price/stock>0).
  - `POST /api/public/stores/{slug}/orders` — online order (WhatsApp checkout):
```json
{"customer":{"name":"…","phone":"…"},"fulfillment":"delivery|pickup",
 "items":[{"product_id":1,"variant_id":null,"quantity":2}],"notes":"…"}
```
    - server computes subtotal, delivery_fee (0 when pickup or subtotal ≥ free_delivery_threshold), tax, total; enforces min_order_amount; creates order `status=PENDING, payment_method=WHATSAPP, source=online`; response includes `whatsapp_url` — `https://wa.me/{store.whatsapp}?text={prefilled order summary}` (mirrors "pre-formatted order summary message" toggle description).
- **Validation**: items in-catalog + in-stock (unless out-of-stock display w/ block on order), fulfillment allowed by settings, subtotal ≥ min_order_amount (422 `MIN_ORDER_NOT_MET`).
- **Side effects**: stock **reserved not deducted** for PENDING online orders? **Decision: deduct on COMPLETED only for online orders; POS deducts immediately.** Fire `OnlineOrderPlaced` → optional owner notification (mail/db).
- **Tests**: hidden products excluded; disabled store 404; delivery fee waived at threshold; min order enforced; whatsapp_url encoding; rate limit.
- **Priority**: M7.

---

## PAGE 12: Settings Hub
- **Route**: `/config` (alias `/settings`) — `SettingsPage.tsx`
- **API** — one read, group writes (matches tab saves; single Save posts union → frontend calls the groups it dirtied):
  - `GET /settings` → envelope `{general:{…},business:{…},currency:{…},taxes:{…},receipts:{…},payments:{…},integrations:{…}}` + `storefront:{…}`
  - `PUT /settings/general` `{business_name req|max:120, tagline nullable|max:160, phone, whatsapp, language in:en,ar}`
  - `PUT /settings/business` `{owner_name, email nullable|email, address, city, state, zip_code, country in:whitelist}`
  - `PUT /settings/currency` `{currency in:QAR,USD,EUR,GBP,CAD,BRL,MXN,COP,ARS,AUD, symbol_placement in:BEFORE,AFTER}` (symbol derived server-side; QAR forces BEFORE per UI logic — mirror rule)
  - `PUT /settings/taxes` `{charge_sales_tax bool, tax_rate numeric|min:0|max:100 required_if charge, tax_id nullable|max:64, tax_included_in_price bool}`
  - `PUT /settings/receipts` `{header nullable|max:160, footer nullable|max:160, show_logo, show_address, show_tax, auto_print bools}`
  - `PUT /settings/payments` `{cash:bool, card:bool, transfer:bool, whatsapp:bool}` — at least one of cash/card/transfer true (POS must have a method)
  - `PUT /settings/integrations` `{facebook_pixel_id nullable|regex ^\d{5,20}$, google_analytics_id nullable|regex ^G-[A-Z0-9]{4,}$ , facebook_connected bool}` (real Meta OAuth deferred)
- **Side effects**: currency/tax changes apply to *future* orders only (orders snapshot tax_rate + totals); audit log row per settings write (**ASSUMPTION: audit worth having; cheap**).
- **Permissions**: owner/manager; billing tab visible to owner only.
- **Tests**: each group validation; tax change doesn't mutate old orders; disabling all POS methods rejected; language switch persists.
- **Priority**: M2 (settings scaffolding) + M7 polish.

---

## PAGE 13: Users & Staff
- **Route**: `/users` (+ STAFF tab in settings) — `UsersPage.tsx`
- **API**:
  - `GET /staff` → `[{id,name,email,role,is_active,joined_at,invitation_pending}]`
  - `POST /staff/invitations` `{name req, email req|email|not already member, role in:MANAGER,CASHIER,VIEWER}` → creates invitation (token, 7-day expiry), sends mail, returns pending member row
  - `POST /staff/invitations/{token}/accept` **PUBLIC** `{name?, password required|min:8|confirmed}` → creates/attaches user, role from invitation
  - `PATCH /staff/{user}` `{role?, is_active?}` — cannot modify owner; cannot demote last owner
  - `DELETE /staff/{user}` — detach from store (keep user row + their created orders' created_by)
  - `POST /staff/invitations/{id}/resend`, `DELETE /staff/invitations/{id}`
- **Permissions**: owner/manager (`staff.manage`); role changes to/from OWNER: owner only.
- **Side effects**: `StaffInvited` mail (queued); deactivation revokes Sanctum tokens.
- **Edge cases**: invite email already a member (422); accept expired token (410); toggle owner blocked (403); plan seat limits (UI says "unlimited" on Pro — no cap now).
- **Tests**: invite→accept flow; expired token; owner immutability; deactivated user gets 401 on next request; manager can invite but cannot touch owner.
- **Priority**: M2.

---

## PAGE 14: Billing / Subscription
- **Routes**: `/billing` + settings SUBSCRIPTION tab — `BillingPage.tsx`
- **API**:
  - `GET /subscription` → `{plan:"PRO", status:"ACTIVE", price:"19.99", billing_cycle:"monthly", renews_at:"2027-07-05", features:[…]}` (mirrors HAR `/subscription/summary`)
  - `POST /subscription/portal` → `{url}` (Stripe customer-portal link — **ASSUMPTION: Stripe/Cashier; phase-2 packet; stub returns configured URL**)
  - `GET /subscription/invoice/latest` → PDF download (stub until provider wired)
- **Permissions**: owner only (`billing.manage`); MANAGER explicitly excluded ("no billing").
- **Priority**: M8 (display endpoint in M2 seed so UI renders).

---

## AUTH (no reconstructed page; required)
- `POST /auth/login` `{email, password}` → Sanctum (SPA cookie mode; token mode for tooling) + user + store + role
- `POST /auth/logout`; `GET /auth/me` → `{user, store, role, abilities[]}` (drives sidebar/role gating)
- `POST /auth/forgot-password`, `POST /auth/reset-password` (standard)
- Registration = store signup: `POST /auth/register` `{name,email,password,business_name}` → creates user + store + owner pivot + default settings. (**ASSUMPTION: original had signup; SaaS requires it.**)
- Tests: login/logout/me; wrong creds 422; inactive staff 403; register bootstraps settings rows.
- Priority: M1.

# 01 — Frontend Forensic Analysis (Backend Perspective)

> Source of truth: the reconstructed React app in `src/` (which was itself rebuilt from HAR,
> screenshots, and JS bundles of the authorized Kyte-web deployment — see `docs/01-recovery-analysis.md`),
> plus the extracted API contracts in `docs/06-api-contract-map.md`.
> Every claim below cites a concrete file. Items not observable in the evidence are tagged **ASSUMPTION**.

## 1. Product identity

- **Domain**: merchant commerce SaaS — POS checkout ("Sell"), product catalog with variants and stock,
  customer CRM, orders/transactions history, finance & analytics dashboards, a public online storefront
  with WhatsApp checkout, staff management, business settings, and a subscription plan.
  This is **retail/general-merchant** (apparel, electronics, coffee in seed data), *not* restaurant-specific:
  there are no tables/kitchen/course concepts anywhere in the UI.
- **Tenancy**: one *store* (business) per account is what the UI exposes. The topbar shows a business
  switcher shell with the literal text `All branches · {country} · {currency}` (`src/components/navigation/Topbar.tsx`),
  which implies the original product had multi-store/branch ambitions, but **no branch selector, branch CRUD,
  or branch-scoped data exists anywhere in the recovered UI**. → Model a `stores` tenant table from day one,
  scope all data by `store_id`, but ship **single store per account**; branches = ASSUMPTION deferred.
- **Frontend stack**: React 18 + TypeScript + Vite + React Router v6 + Zustand. All data flows through
  `src/api/mockApi.ts` — an in-memory client that is the exact seam where the Laravel API plugs in.
- **Locale**: bilingual EN/AR with RTL flip (`settingsStore.setLanguage` sets `dir=rtl`;
  `src/i18n/LocaleBridge.tsx` holds 646 lines of translations). Currency list includes QAR with special
  symbol handling (`useMoney.ts`) — Qatar market focus.

## 2. Recovered network evidence (original backend)

From `docs/06-api-contract-map.md` (HAR: `web.kyteapp.com.har`, 111 API requests):

| Original endpoint | Meaning for the rebuild |
|---|---|
| `GET /api/kyte-web/account-info/:uid` | store profile: businessName, email, currency, symbol, phone, country |
| `GET /api/kyte-web/products/:uid` + `/count` | product list w/ `name, price, cost, stock, category (string), code, imageUrl` |
| `GET /api/kyte-web/customer/:uid` + `/count` | customers w/ `name, email, phone, totalOrders, totalSpent` |
| `GET /api/kyte-web/sale/:uid/count`, `POST /api/kyte-web/sale` | sale payload: `customerId, items[{productId,quantity,unitPrice}], paymentMethod, subtotal, discount, total` |
| `GET /subscription/summary` | `{plan: "PRO", status: "ACTIVE", renewsAt}` |

Key observations to carry into the Laravel design:
- **`count` endpoints exist separately** → original used count-first pagination; we will return `meta.total` in paginated responses instead.
- **Category is a string on the product payload**, not an id → the new backend normalizes to `category_id` FK but the API keeps returning the category name inside the product resource for the UI.
- **Sale POST carries client-computed totals** → the rebuilt backend must *recompute and validate* totals server-side (never trust `subtotal/total` from the client).
- Auth was Firebase; media was Google Cloud Storage → replaced with **Laravel Sanctum** and local/S3 disk uploads.

## 3. Page inventory (14 routed views + auth)

Routes from `src/router/routes.tsx` and nav from `src/config/navigation.ts` (sidebar groups in parentheses):

| # | Route | Page component | Nav label (group) | Purpose |
|---|---|---|---|---|
| 1 | `/checkout` (index redirect target) | `POSPage` | Sell (Operations) | POS grid + cart + payment |
| 2 | `/orders` | `OrdersPage` | Orders (Operations) | order history, status filters, receipt |
| 3 | `/products` | `ProductsPage` | Products (Commerce) | catalog table, delete, search |
| 4 | `/products/create` | `ProductCreatePage` | (topbar "Add Product") | full-page product form (also used for edit — see gap G-1) |
| 5 | `/categories` | `CategoriesPage` | (reached from products area) | category list + create modal |
| 6 | `/catalog` | `StorefrontPage` | Online Catalog (Commerce) | storefront settings (4 tabs) |
| 7 | `/catalog/preview` | `StorefrontPreviewPage` | (button) | public storefront simulation w/ bag |
| 8 | `/customers` | `CustomersPage` | Customers (Customers) | CRM table + detail panel + delete confirm |
| 9 | `/sales` | `SalesPage` | Transactions (Finance) | audit log w/ payment-method filter |
| 10 | `/finances` | `FinancesPage` | Finances (Finance) | cash-flow breakdown, net revenue |
| 11 | `/analytics` (+alias `/reports`) | `ReportsPage` | Analytics (Finance) | KPIs, top products, inventory health |
| 12 | `/users` | `UsersPage` | Users (System) | staff list, invite, role legend |
| 13 | `/config` (+alias `/settings`) | `SettingsPage` | Settings (System) | 9-tab settings hub |
| 14 | `/billing` | `BillingPage` | (from settings) | plan card, manage billing |
| 15 | `/login` | (route-map doc only; `AuthLayout` referenced, page not reconstructed) | — | owner/staff login — **must exist in backend regardless** |

## 4. Global shell components and their backend dependencies

- **AppShell** (`src/layouts/AppShell.tsx`): on mount fires `fetchProducts, fetchCategories, fetchCustomers, fetchOrders, fetchSettings`
  → the SPA boot sequence needs 5 fast list endpoints plus the authenticated-user/store endpoint.
- **Topbar**: business avatar + name + `country · currency` (from account/settings endpoint); "Quick Sale" → `/checkout`; "Add Product" → `/products/create`. No backend action of its own.
- **Sidebar**: nav grouped Operations/Commerce/Customers/Finance/System; items must be filterable by role (see §8).
- **ModalHost** (`modalStore`): modal types `PRODUCT_CREATE | PRODUCT_EDIT | CUSTOMER_CREATE | CATEGORY_CREATE | PAYMENT | RECEIPT`.
- **ToastHost**: success/error feedback after every mutation → API must return actionable error messages (422 field errors + human message).

## 5. Forms inventory (all fields, from source)

### F-1 Product form (two variants: `ProductModal.tsx` tabbed modal, `ProductCreatePage.tsx` full page)
Union of fields observed:
| Field | Type | Required | Notes |
|---|---|---|---|
| name | text | **yes** | toast "Product name and price are required" |
| price | decimal ≥0 | **yes** | |
| reducedPrice | decimal | no | sale price; page currently *replaces* price with it when > 0 — backend keeps both (`price`, `reduced_price`) |
| cost | decimal | no | modal-only; drives profit-margin preview |
| category | select (name) | no (defaults to first) | normalize to `category_id` |
| description | textarea | no | "Suggest description (AI)" button → optional AI endpoint |
| code (SKU/barcode) | text | no (auto `PROD-xxx`) | unique per store |
| unit | select | no, default `pcs` | seen values: pcs, kg, box, m, bottle |
| imageUrl | text/url | no | becomes file upload in rebuild (evidence: original used GCS uploads) |
| labelName / labelColor | text / color | no | printed price-label preview (create page) |
| taxable | toggle | default true | |
| trackStock (manageStock) | toggle | default true | when off, page saved stock=999 (mock hack) → backend: nullable stock when not tracked |
| stock (on hand) | int ≥0 | when trackStock | |
| minStock (safety alert) | int ≥0 | when trackStock | drives low-stock alerts |
| showInOnlineStore | toggle | default true | |
| isFeatured (highlight) | toggle | default false | |
| variants[] | repeater | no | each: name (req), price (defaults to product price), stock, auto code `SKU-NAME` |

### F-2 Customer form (`CustomerModal.tsx`) — name **req**, phone **req**, email optional.
Customer *entity* additionally carries address, city, taxId, notes (`mockData.ts`) → full CRUD form needed even though modal only exposes 3 fields (edit UI is a gap, see G-2).

### F-3 Category form (`CategoryModal.tsx`) — name **req**, color (hex, default `#3b82f6`).

### F-4 Payment / complete-sale (`PaymentModal.tsx`) — paymentMethod ∈ CASH|CARD|TRANSFER (WHATSAPP appears only on online orders); submits full cart snapshot. Tax is hardcoded `subtotal * 0.085` in the mock — **must** come from store tax settings server-side.

### F-5 Staff invite (`UsersPage.tsx`) — inviteName **req**, inviteEmail **req**, role ∈ CASHIER|MANAGER|VIEWER (OWNER not assignable). Creates member with `active:false` + "Invite pending" badge → invitation token + email flow.

### F-6 Settings hub (`SettingsPage.tsx`, 9 tabs, single Save submits the union):
- GENERAL: language (en|ar), businessName **req**, tagline, phone, whatsapp
- BUSINESS: ownerName, email, address, city, state, zipCode, country (12-country list incl. Qatar)
- CURRENCY: currency (QAR/USD/EUR/GBP/CAD/BRL/MXN/COP/ARS/AUD), symbolPlacement (BEFORE|AFTER)
- TAXES: chargeSalesTax toggle, taxRate %, taxId, taxIncludedInPrice toggle
- RECEIPTS: receiptHeader, receiptFooter, showLogo, showAddress, showTax, autoPrint toggles
- PAYMENTS: acceptCash/acceptCard/acceptTransfer/acceptWhatsapp toggles (**frontend-only state today → must persist**)
- INTEGRATIONS: facebookPixelId, googleAnalyticsId, facebookConnected toggle (**frontend-only today → must persist**)
- STAFF: read-only duplicate of Users page + invite button
- SUBSCRIPTION: display-only plan card + Manage Billing / Download Invoice / Cancel buttons (toast stubs)

### F-7 Storefront settings (`StorefrontPage.tsx`, 4 tabs, one Save):
- STATUS: onlineStoreEnabled toggle (persists immediately via `toggleOnlineStore`), storeSlug (lowercased, `[a-z0-9-]` only, public URL `https://{slug}.dorzak.com`), Copy Link
- BRANDING: storeBio, bannerUrl, logoUrl, accentColor
- FULFILLMENT: allowDelivery, allowPickup, deliveryFee, freeDeliveryThreshold, minOrderAmount
- WHATSAPP: whatsappOrderingEnabled, showOutOfStockOnline

## 6. Tables inventory

| Table | Page | Columns | Filters/sort | Row actions |
|---|---|---|---|---|
| Products | `/products` | image+name+SKU+variant count, category, price, stock pill (success >minStock / warning >0 / danger =0), actions | text search over name/code/category | edit (→ create page), delete (immediate, **no confirm** — G-3) |
| Categories | `/categories` | color dot+name, productCount | — | — (no edit/delete in UI — G-4) |
| Orders | `/orders` | order id, customer(+phone), date, payment method icon, status pill, total, receipt btn | search id/customer; status chips ALL/COMPLETED/PENDING/CANCELLED; dateRange state exists (TODAY/WEEK/MONTH/ALL) but not wired — backend must support it | row click → receipt modal; Export CSV (stub) |
| Transactions | `/sales` | id, customer, date, payment pill, status, discount, tax, net total | search; payment chips ALL/CARD/CASH/TRANSFER/WHATSAPP | — |
| Customers | `/customers` | avatar+name (sortable A-Z/Z-A), phone (wa.me link), email, balance (=totalSpent, sortable), notes icon, address (maps link), delete | search name/email/phone | row click → detail side panel; delete w/ confirm modal |
| Finances entries | `/finances` | description ("Sale to X", id, item count), date, method, tax, amount | period chips DAILY/WEEKLY/MONTHLY (not wired) | Export CSV (stub) |
| Staff list | `/users` | avatar, name, email, joined date, role badge, active toggle, remove | — | toggle active, remove (owner immutable) |

All client-side today (full list fetched, filtered in JS). Backend should still implement **server-side search/filter/sort/pagination** query params so the app scales; page-size defaults in Phase 4 docs.

## 7. Buttons & actions with backend side effects

- POS: product card click → cart add (frontend); **Charge** → PaymentModal → `POST orders` (creates order, deducts stock, updates customer stats, returns receipt payload).
- Orders: Export CSV; Receipt print (GET single order); status chips (query param).
- Customers: Export CSV, Import (stub → CSV import endpoint), Register Customer, delete (destructive, confirmed).
- Products: delete (destructive), AI "Suggest description", AI "Automatic Registration" photo scan (BETA card — mark optional).
- Users: Send Invitation (email), active toggle (suspend), Remove.
- Storefront: online-store toggle (immediate persist), Copy Link (frontend), Live Catalog Preview (public data).
- Settings: Save per tab-union; language switch (also client-side RTL flip); Facebook Connect/Disconnect (OAuth — deferred, persist a boolean+page name).
- Billing: Manage Billing / Download Invoice / Cancel / Contact Sales — all toast stubs → thin endpoints or provider portal links (ASSUMPTION: Stripe customer portal).

## 8. Roles observed (source: `UsersPage.tsx` ROLE_CONFIG — authoritative)

| Role | Permissions text in UI |
|---|---|
| OWNER | Full access, Billing management, Delete account, All features |
| MANAGER | Full access (no billing), Staff management, Reports, Settings |
| CASHIER | POS Checkout, View orders, Manage products, No settings |
| VIEWER | View-only access, Reports, No editing |

No customer-facing login exists: the public storefront is anonymous (bag + WhatsApp checkout). → 4 staff roles only.

## 9. Mock state that must become real backend state

| Mock location | Becomes |
|---|---|
| `mockApi` in-memory arrays (products/categories/customers/orders/account) | MySQL→ *(decision: PostgreSQL, see 04)* tables + REST endpoints |
| `settingsStore` persisting to `localStorage['dorzak-merchant-settings']` | `PUT /api/v1/settings/*`; localStorage becomes only a cache of language |
| `UsersPage` local `staff` useState array | `users` + `store_user` + `staff_invitations` tables & endpoints |
| Settings PAYMENTS + INTEGRATIONS tab local state | persisted settings columns |
| POS tax `subtotal * 0.085` hardcode | server-side `OrderTotalsService` using store tax config |
| `createOrder` matching customer **by lowercase name** | `customer_id` FK on order create |
| Order id `ORD-` + random 4 digits | per-store sequential `order_number` |
| Customer `totalSpent/totalOrders` mutation in mockApi | cached counters maintained by `OrderCreated`/`OrderCancelled` events + reconcile command |
| Reports "top products" fake math (`price * max(1, 20-stock)`) | real aggregate over `order_items` |
| Storefront preview local `bagItems` | public cart → `POST /api/public/{slug}/orders` (WhatsApp checkout) |
| Subscription card static "PRO / $19.99 / renews Jul 5 2027" | `subscriptions` table (+ payment provider later) |

## 10. Gaps in evidence (each carried into the plan as marked assumptions)

- **G-1 Product edit**: table's edit button just navigates to `/products/create` (blank). Backend must still provide `GET/PUT /products/{id}`; frontend packet includes wiring the page for edit mode.
- **G-2 Customer edit**: `customerStore.updateCustomer` exists but no UI calls it. Provide `PUT /customers/{id}`.
- **G-3 Product delete has no confirm dialog** (customers delete does). Backend uses soft deletes as a safety net either way.
- **G-4 Category edit/delete/reorder**: no UI. Provide standard endpoints; deleting a category must not orphan products (`category_id` → null).
- **G-5 Order status transitions**: pills show COMPLETED/PENDING/CANCELLED but no UI changes status. PENDING+TRANSFER seed order implies "awaiting transfer confirmation" flow → provide `PATCH /orders/{id}/status`.
- **G-6 Auth screens** not reconstructed; login/logout/password reset specified from scratch (Sanctum SPA).
- **G-7 Refund action** mentioned in `docs/03-route-map.md` ("refund action" on order detail) but absent from the reconstructed UI → model as status `CANCELLED` + stock restore; money refunds deferred.
- **G-8 Multi-branch** ("All branches" text) → deferred; schema keeps `store_id` scoping so branches can be added as a second store-like level later.

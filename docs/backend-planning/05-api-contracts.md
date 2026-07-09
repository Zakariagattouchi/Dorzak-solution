# 05 — API Contract Design

## Global conventions

- Base: `/api/v1` (authed, Sanctum) and `/api/public` (anonymous storefront). JSON only.
- **Envelope**: single resource `{"data": {…}}`; collection `{"data":[…], "meta":{…}, "links":{…}}` (Laravel API Resources + `paginate()`).
- **Errors**:
  - 422: `{"message":"The given data was invalid.","errors":{"field":["…"]}}` (Laravel default — toasts show `message`, forms map `errors`)
  - 401 unauthenticated, 403 `{"message":"This action is unauthorized."}`, 404 (incl. cross-tenant ids), 409 domain conflicts: `{"message":"…","code":"INSUFFICIENT_STOCK","details":[…]}`
- **Pagination**: `?page=&per_page=` (per_page max 200, default 25; POS product boot uses per_page=200).
- **Filtering/sorting** per list endpoint below; `sort` uses `field` / `-field`.
- **Money**: strings with 2 decimals in responses; numbers accepted in requests.
- **Enum casing**: SCREAMING (COMPLETED, CARD) — matches frontend types exactly.
- **Dates**: ISO-8601 UTC; plus `placed_at_local` formatted `YYYY-MM-DD HH:mm` in store timezone (UI table shows exactly this format).
- Machine-readable mirror: `api-contracts.json`.

---

## Module: Authentication (`routes/api.php` → `Api\Auth\*`)

### POST /api/v1/auth/login
Auth: none. Throttle 5/min per email+IP.
Request `{"email":"owner@dorzak.com","password":"secret"}`
Response 200:
```json
{"data":{"token":"1|…","user":{"id":1,"name":"Barsha Admin","email":"…"},
 "store":{"id":1,"name":"Dorzak Merchant","currency":"USD","language":"en"},
 "role":"OWNER","abilities":["orders.create","products.manage","…"]}}
```
(SPA cookie mode: same body sans token, after `GET /sanctum/csrf-cookie`.)
Validation: email required|email; password required. 422 invalid creds; 403 `{"code":"ACCOUNT_DISABLED"}` when pivot is_active=false.
Controller `AuthController@login` · Tests: ok / bad password / disabled staff / throttle.

### POST /auth/logout — 204; revokes current token/session.
### GET /auth/me — same shape as login minus token. Used on app boot before the 5 store fetches.
### POST /auth/register — none-auth. `{name, email unique, password min:8 confirmed, business_name required}` → creates user, store, OWNER pivot, default settings rows (storefront/receipt/integration/subscription FREE). Response 201 like login. `RegisterStoreAction`.
### POST /auth/forgot-password / POST /auth/reset-password — standard Laravel; 200 always (no email enumeration).

---

## Module: Settings (`SettingsController`, `Settings\*Request`)

### GET /api/v1/settings
Perm: any active member (viewer needs currency/receipt data to render money).
Response 200 groups exactly mirroring `accountInfo` (see 02 Page 12). Frontend hydrates `settingsStore` from this.

### PUT /settings/{group} where group ∈ general|business|currency|taxes|receipts|payments|integrations|storefront
Perm: `settings.manage` (owner/manager). Each has its own FormRequest (validation in 02 Page 12/10).
Response 200: the updated **full** settings envelope (so the store re-hydrates once).
Side effects: audit log row; `payments` guarded "≥1 POS method"; `storefront.slug` global-unique + reserved list.
Tests per group: happy, each rule, 403 cashier/viewer.

### POST /settings/storefront/banner | /logo | POST /stores/logo
multipart `file` image|max:4096|mimes:jpg,jpeg,png,webp → `{"data":{"path":"/storage/stores/1/banner.webp","url":"https://…"}}`.

---

## Module: Staff (`StaffController`, `StaffInvitationController`)

### GET /staff — perm `staff.view` (owner/manager; UI page is System-group)
`{"data":[{"id":1,"name":"Barsha Admin","email":"…","role":"OWNER","is_active":true,"joined_at":"2024-01-01","invitation_pending":false}]}` — pending invitations appended with `invitation_pending:true, invitation_id`.

### POST /staff/invitations — perm `staff.manage`
Req `{"name":"John Smith","email":"john@example.com","role":"CASHIER"}`
Validation: name required|max:120; email required|email, not an existing member, no pending invite; role in MANAGER,CASHIER,VIEWER.
201 → pending row. Side effect: queued `StaffInvitationMail`.

### POST /staff/invitations/{token}/accept — PUBLIC, throttle 10/min
Req `{"password":"…","password_confirmation":"…","name":"optional override"}`
200 login-shaped response. 410 expired, 404 bad token, 409 already accepted.

### POST /staff/invitations/{id}/resend (202) · DELETE /staff/invitations/{id} (204)

### PATCH /staff/{user} — perm `staff.manage`; body `{role?, is_active?}`
Rules: cannot target OWNER unless actor is OWNER; cannot deactivate/demote the last active owner (409 `LAST_OWNER`); deactivation revokes tokens.
### DELETE /staff/{user} — detach pivot (owner-protected as above). 204.

---

## Module: Categories (`CategoryController`)

| Method/Path | Perm | Notes |
|---|---|---|
| GET /categories | member | `[{id,name,color,sort_order,products_count}]`, ordered by sort_order,name |
| POST /categories | products.manage | `{name req|max:80|unique-in-store, color hex}` → 201 |
| PUT /categories/{id} | products.manage | same rules (unique ignores self) |
| DELETE /categories/{id} | products.manage | 200 `{"data":{"reassigned_products":3}}` — products.category_id→null |
| PATCH /categories/reorder | products.manage | `{ids:[3,1,2]}` all-ids-of-store validation |

Tests: unique per store not global; delete nulls products; reorder persists.

---

## Module: Products (`ProductController`, `ProductImageController`)

### GET /products
Params: `search` (name|sku|category name, ILIKE), `category_id`, `status=active|inactive`, `stock=low|out`, `sort=name|-name|price|-price|stock|-stock|-created_at` (default), `page`, `per_page`.
Item resource:
```json
{"id":101,"name":"Dorzak Signature Cotton Hoodie","description":"…",
 "price":"49.99","reduced_price":null,"effective_price":"49.99","cost":"18.00",
 "category":{"id":1,"name":"Apparel & Fashion","color":"#3b82f6"},
 "sku":"HOOD-001","unit":"pcs","image_url":"https://…",
 "label_name":null,"label_color":null,
 "taxable":true,"track_stock":true,"stock":45,"min_stock":10,"stock_status":"IN_STOCK|LOW|OUT",
 "show_in_online_store":true,"is_featured":true,"is_active":true,
 "variants":[{"id":1,"name":"Small / Black","price":"49.99","stock":15,"sku":"HOOD-S-BLK"}],
 "created_at":"…"}
```
### POST /products → 201 (payload + validation in 02 Page 3). Service `ProductService::create` (writes INITIAL stock movement, syncs variants, recomputes parent stock).
### GET /products/{id} · PUT /products/{id} (variant sync semantics: ids present=update, missing=delete, new=create; stock diff → ADJUSTMENT movement) · DELETE /products/{id} → 204 soft delete.
### POST /products/{id}/image — multipart, replaces image_path, deletes old file.
### POST /ai/product-description — `{name req}` → `{"data":{"description":"…"}}`. Throttle 20/day/store. Implementation: Claude API (haiku) behind `AiDescriptionService`; feature-flagged `AI_FEATURES_ENABLED`.

Requests: `StoreProductRequest`, `UpdateProductRequest`. Resource: `ProductResource`, `ProductVariantResource`.
Tests: full matrix in 11-test-plan §Products.

---

## Module: Customers (`CustomerController`, `CustomerImportController`)

### GET /customers — params `search` (name|email|phone ILIKE), `sort=name|-name|total_spent|-total_spent`, pagination.
`meta.summary = {"count":4,"total_spent":"1151.40","avg_ltv":"287.85"}` (whole store, not page).
Resource: `{id,name,email,phone,address,city,tax_id,notes,total_orders,total_spent,created_at}`.
### POST /customers → 201. Validation 02 Page 6; duplicate phone → 422 `{"errors":{"phone":["…"]},"duplicate_customer_id":3}`.
### GET /customers/{id} → resource + `recent_orders:[{id,order_number,total,placed_at}]` (last 3).
### PUT /customers/{id} · DELETE /customers/{id} (soft) → 204.
### GET /customers/export → text/csv stream, filename `customers-{date}.csv`, respects `search`.
### POST /customers/import — multipart csv (headers name,phone,email,address,city,notes) → 200 `{"data":{"imported":120,"skipped":3,"errors":[{"row":7,"message":"phone required"}]}}`; >500 rows → 202 queued + notification on completion.

---

## Module: Orders & Checkout (`OrderController`, `OrderStatusController`, `OrderExportController`)

### GET /orders
Params: `search` (order_number|customer_name), `status`, `payment_method`, `date_from`, `date_to` (store-tz dates on placed_at), `source`, `sort=-placed_at` default, pagination.
`meta.summary` (filtered set): `{"revenue":"278.77","completed_count":2,"pending_count":1,"cancelled_count":0,"tax_total":"21.84","discount_total":"5.00"}` — feeds both Orders pills and Transactions cards.
Order resource:
```json
{"id":1,"order_number":"ORD-1000","status":"COMPLETED","payment_method":"CARD","source":"POS",
 "customer":{"id":3,"name":"Sarah Jenkins","phone":"+1 555-0144"},
 "customer_name":"Sarah Jenkins","customer_phone":"+1 555-0144",
 "items":[{"id":1,"product_id":101,"product_name":"…","variant_name":"Medium / Black",
           "quantity":2,"unit_price":"49.99","line_total":"99.98"}],
 "subtotal":"108.48","discount":"5.00","tax_rate":"8.50","tax_amount":"8.79",
 "delivery_fee":"0.00","total":"112.27","notes":"…","fulfillment":null,
 "placed_at":"2026-07-05T14:32:00Z","placed_at_local":"2026-07-05 14:32",
 "created_by":{"id":1,"name":"Barsha Admin"}}
```
### POST /orders — perm `orders.create`. Full contract in 02 Page 1. 201 / 422 / 403 / 409 INSUFFICIENT_STOCK `{details:[{product_id,requested,available}]}`.
Controller thin → `OrderService::create(CreateOrderData)`; totals via `OrderTotalsService`; stock via `StockService` (row-locks products in tx).
### GET /orders/{id} — includes `receipt` block: `{business_name,owner_name,phone,address,header,footer,show_logo,show_address,show_tax,logo_url}` so ReceiptModal renders offline.
### PATCH /orders/{id}/status — `{status: COMPLETED|CANCELLED}`; matrix in 02 Page 2; 200 updated resource; CANCELLED → stock CANCEL_RETURN + counter reversal; ONLINE PENDING→COMPLETED → stock SALE deduction at that moment.
### GET /orders/export — CSV, same filters, perm `reports.export`.

---

## Module: Reports (`Reports\FinanceController`, `Reports\AnalyticsController`)

### GET /reports/finance?period=daily|weekly|monthly|all&date_from&date_to
Perm `reports.view` (owner/manager/viewer). Response in 02 Page 8. Period resolves to placed_at range in store tz (daily=today, weekly=ISO week, monthly=calendar month); explicit dates override period.
### GET /reports/finance/export — CSV of `entries`.
### GET /reports/analytics?period=today|week|month|all — response in 02 Page 9.
- `top_products`: `order_items` joined non-cancelled orders in period, group by product, rank by SUM(line_total) desc limit 5.
- `gross_profit`: SUM((unit_price − unit_cost) × quantity) on COMPLETED orders.
- `inventory`: current state (period-independent).
Tests: seeded fixture orders across boundary dates.

---

## Module: Public storefront (`Public\StorefrontController`, `Public\PublicOrderController`)
Throttle: 60/min/IP reads; 5/min/IP order create. No auth. 404 unless `online_store_enabled`.

### GET /api/public/stores/{slug} — store card (02 Page 11).
### GET /api/public/stores/{slug}/catalog?category_id&search
`{"data":{"categories":[{id,name,color}],"products":[{id,name,description,price,"reduced_price",image_url,category_id,in_stock,variants:[{id,name,price,in_stock}]}]}}` — only `show_in_online_store`, active, non-deleted; out-of-stock included w/ `in_stock:false` only when store setting allows.
### POST /api/public/stores/{slug}/orders — contract in 02 Page 11; 201:
```json
{"data":{"order_number":"ORD-1007","status":"PENDING","subtotal":"59.98","delivery_fee":"5.00",
 "tax_amount":"5.10","total":"70.08",
 "whatsapp_url":"https://wa.me/15552345678?text=New%20order%20ORD-1007%3A%0A2x%20Hoodie%20…"}}
```
Errors: 422 validation, 409 MIN_ORDER_NOT_MET / ITEM_UNAVAILABLE, 404 store disabled.
Side effects: creates order (PENDING/WHATSAPP/ONLINE) + customer upsert-by-phone (links or creates a customer row), `OnlineOrderPlaced` event → database notification to store staff.

---

## Module: Subscription (`SubscriptionController`)
- GET /subscription — perm member (settings tab renders card; write actions owner-only). Response in 02 Page 14 + `features` string array.
- POST /subscription/portal — perm `billing.manage` (owner). Returns `{url}`; stub: `config('services.billing.portal_url')` until Stripe Cashier packet.
- GET /subscription/invoice/latest — owner; 501 until provider wired (UI toast handles).

---

## Endpoint census
Auth 6 · Settings 10 · Staff 7 · Categories 5 · Products 7 · Customers 7 · Orders 5 · Reports 3 · Public 3 · Subscription 3 · AI 1 = **57 endpoints**.

## Controller/Service/Resource map
| Module | Controllers | Services/Actions | Resources |
|---|---|---|---|
| Auth | AuthController, RegisteredStoreController, PasswordController | RegisterStoreAction | UserResource, AuthSessionResource |
| Settings | SettingsController, StorefrontMediaController | SettingsService (+audit) | SettingsResource |
| Staff | StaffController, StaffInvitationController | InviteStaffAction, AcceptInvitationAction | StaffMemberResource |
| Catalog | CategoryController, ProductController, ProductImageController | ProductService, StockService, AiDescriptionService | CategoryResource, ProductResource, ProductVariantResource |
| CRM | CustomerController, CustomerImportController | CustomerImportService | CustomerResource |
| Sales | OrderController, OrderStatusController, OrderExportController | OrderService, OrderTotalsService, StockService | OrderResource, OrderItemResource |
| Reports | Reports\FinanceController, Reports\AnalyticsController | ReportService | (array resources) |
| Public | Public\StorefrontController, Public\PublicOrderController | PublicOrderService, WhatsAppMessageBuilder | PublicStoreResource, PublicProductResource |
| Billing | SubscriptionController | — | SubscriptionResource |

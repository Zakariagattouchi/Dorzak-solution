# 09 — React ⇄ Laravel Integration Map

## Client architecture

Replace `src/api/mockApi.ts` with:
- `src/api/apiClient.ts` — fetch wrapper: `credentials:'include'`, `X-Requested-With`, CSRF bootstrap (`GET /sanctum/csrf-cookie` before first mutation), JSON parse, error normalization `{status, message, errors, code}`, 401 → redirect `/login`, 419 → refresh CSRF + retry once.
- `src/api/endpoints/{auth,settings,staff,catalog,customers,orders,reports,storefront,subscription}.ts` — typed functions listed per page below.
- Zustand stores keep their current shape; only their fetch/mutate internals swap to endpoint functions. Add `error: string|null` to each store (currently missing).
- New: `src/stores/authStore.ts` — `{user, store, role, abilities, login(), logout(), bootstrap()}`; `AppShell` waits for `bootstrap()` (calls `/auth/me`) before firing the 5 data fetches; sidebar filters `mainNavigation` by ability.

Per-page map (component → API functions → endpoints → states):

## AppShell (`src/layouts/AppShell.tsx`)
- `getMe()` → GET /auth/me — blocking; on 401 redirect to /login.
- then parallel: `getProducts()`, `getCategories()`, `getCustomers()`, `getOrders()`, `getSettings()`.
- Loading: full-screen skeleton until me+settings resolve (money formatting depends on settings). Error: retry banner.

## POSPage (`/checkout`)
- Needs: `getProducts({per_page:200, active:1})`, `getCategories()`, `getCustomers({search})`, `createOrder(payload)` → POST /orders.
- States: products loading skeleton grid; empty catalog CTA ("Add Product"); cart empty state (exists); submit `loading` on PaymentModal button (exists); success → toast + `RECEIPT` modal with returned order (improves current mock which skips receipt); error → 409 INSUFFICIENT_STOCK maps to per-line toast + cart badge; 422 field toast.
- Optimistic: **no** (money+stock must be authoritative). Cache invalidation: after sale, refetch products (stock changed) — or patch stocks from response items.

## OrdersPage (`/orders`)
- `getOrders({search,status,date_from,date_to,page})` (debounce search 300ms, server-side now), `getOrder(id)` for receipt, `updateOrderStatus(id, status)`, `exportOrdersCsv(filters)` (anchor download w/ credentials).
- Summary pills read `meta.summary` (stop computing client-side). Pagination component wired to `meta`.
- Empty: "No orders yet" vs "no matches for filters" (mirror customers pattern).

## SalesPage (`/sales`)
- `getOrders({payment_method, search})` — same hook as OrdersPage (`useOrdersQuery`), different default columns. Summary cards from `meta.summary.tax_total/discount_total/revenue`.

## ProductsPage / ProductCreatePage (`/products`, `/products/create`)
- `getProducts({search,page,sort})`, `deleteProduct(id)` (**add confirm dialog — G-3**), `createProduct(payload)`, `getProduct(id)` + `updateProduct(id,payload)` (**wire edit mode — G-1**: route `/products/:id/edit` or query param), `uploadProductImage(id, file)`, `suggestDescription(name)`.
- Mutation behavior: create/update navigate back + toast; server 422 maps to field errors (add inline errors — currently toast-only).
- Cache: productStore refetch after any mutation (list is source for POS too).

## CategoriesPage (`/categories`)
- `getCategories()`, `createCategory({name,color})`, `updateCategory`, `deleteCategory` (show `reassigned_products` in toast), `reorderCategories(ids)`.

## CustomersPage (`/customers`)
- `getCustomers({search, sort, page})` (summary from meta), `createCustomer(payload)`, `getCustomer(id)` (detail panel + recent_orders — replaces client-side name-matching join), `updateCustomer(id,payload)` (**G-2: add edit affordance**), `deleteCustomer(id)` (confirm modal exists), `exportCustomersCsv()`, `importCustomersCsv(file)` (wire the Import button to a file picker + results toast).

## FinancesPage (`/finances`)
- `getFinanceReport({period})` — wire DAILY/WEEKLY/MONTHLY chips to the param (currently dead state). Entries table from `data.entries`. `exportFinanceCsv({period})`.
- Loading: card skeletons; error: inline retry.

## ReportsPage (`/analytics`)
- `getAnalyticsReport({period})` — wire TODAY/WEEK/MONTH/ALL chips. All fake client math (top products, category revenue, gross profit) replaced by response fields.

## StorefrontPage (`/catalog`)
- `getSettings()` (shared), `updateStorefrontSettings(payload)` → PUT /settings/storefront, `toggleOnlineStore()` → same endpoint single-field, `uploadBanner(file)`, `uploadLogo(file)`.
- Slug conflict: 422 on slug field → inline error under the slug input.

## StorefrontPreviewPage (`/catalog/preview`)
- Switch to **public client** (no credentials): `getPublicStore(slug)`, `getPublicCatalog(slug,{category_id,search})`, `createPublicOrder(slug, payload)` → then `window.open(whatsapp_url)`.
- This page becomes the seed of the real customer-facing storefront app.

## SettingsPage (`/config`)
- `getSettings()`, per-tab `updateSettings(group, payload)` — split the current single `handleSave` union into the dirty tab's group call (keeps optimistic simplicity, avoids clobbering concurrent edits).
- PAYMENTS + INTEGRATIONS tabs move from local useState to settingsStore (persisted now).
- Language change: still applies dir/lang locally immediately, then persists via general group.

## UsersPage (`/users`)
- `getStaff()`, `inviteStaff({name,email,role})`, `updateStaffMember(id,{role?,is_active?})`, `removeStaffMember(id)`, `resendInvitation(id)`, `cancelInvitation(id)`.
- Replace the local `staff` useState entirely. Pending invitations render `invitation_pending` badge. LAST_OWNER 409 → toast.

## BillingPage (`/billing`)
- `getSubscription()`, `openBillingPortal()` (POST → open url), `downloadLatestInvoice()` (501 → toast "coming soon").

## LoginPage (new)
- `login({email,password})`, `forgotPassword`, `resetPassword`. On success `authStore.bootstrap()` → navigate `/checkout`.

## Global rules
- Every mutate: button `loading` prop (already supported by AppButton), success toast (existing copy), error toast from normalized `message`.
- Money formatting stays in `useMoney` fed by settingsStore from GET /settings.
- Role gating: hide nav items + guard routes by `abilities`; 403 responses render a "no access" panel as a fallback.
- Optimistic updates: allowed only for reorder (categories) and toggles that are trivially reversible; everything money/stock waits for the server.

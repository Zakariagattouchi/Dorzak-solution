# 10 — Data Seeding Plan

Two seeders + factories. **DemoSeeder is the acceptance harness**: it reproduces `src/data/mockData.ts` byte-for-byte where visible in the UI, so the React app renders identically the moment mockApi is swapped out.

## Factories (database/factories)
- `UserFactory` (default), `StoreFactory` (faker business + sane defaults incl. settings relations via `has()`), `CategoryFactory` (name+hex color), `ProductFactory` (price 5–200, cost 30–60% of price, stock 0–150, states: `lowStock()`, `outOfStock()`, `withVariants(n)`, `hiddenOnline()`, `nonTaxable()`), `ProductVariantFactory`, `CustomerFactory`, `OrderFactory` (states: `pending()`, `cancelled()`, `online()`, `forCustomer()`, computes valid totals via OrderTotalsService — never hand-set money), `OrderItemFactory`, `StaffInvitationFactory`, `SubscriptionFactory` (`pro()` state).

## DemoSeeder (exact mockData parity)
1. **Owner user**: Barsha Admin / merchant@dorzak.com / password `password`.
2. **Store**: "Dorzak Merchant", tagline "Commerce made simple", phone/whatsapp `+1 (555) 234-5678`, address `742 Evergreen Terrace, San Francisco, CA 94107, United States`, language en, currency USD/BEFORE, tax 8.5% id `US-991827364` excluded-from-price, accepted methods all four.
3. **Settings rows**: storefront (enabled, slug `dorzak-merchant`, bio + banner/logo Unsplash URLs from mockData, accent `#1890ff`, secondary `#373f4e`, delivery+pickup on, fee 5.00, free ≥50.00, min 10.00, whatsapp ordering on, show OOS on); receipts (header "Thank you for supporting our local business!", footer "Returns accepted within 30 days with receipt.", logo/address/tax on, autoprint off); integrations (empty ids).
4. **Staff**: Alex Cashier (alex@example.com, CASHIER, active), Maria Manager (maria@example.com, MANAGER, active) — password `password`; plus one pending invitation to demonstrate the badge.
5. **Categories** (5): Apparel & Fashion #3b82f6, Electronics & Tech #10b981, Coffee & Beverages #f59e0b, Accessories #ec4899, Home & Office #8b5cf6. (mockData shows inflated productCounts 14/8/12/6/8 — derived counts will show real numbers; parity applies to *names/colors*.)
6. **Products** (6): exact mockData rows prod_101…106 incl. hoodie's 3 variants (parent stock = 45 = 15+20+10 ✔), units, images, flags. INITIAL stock movements written through StockService.
7. **Customers** (4): Sarah Jenkins, David Miller, Elena Rostova, Michael Vance with mockData contact fields. Counters left at 0 then corrected by step 8 + `customers:recalculate-stats`.
8. **Orders** (3): ORD-9821 (Sarah, CARD, COMPLETED, hoodie ×2 Medium/Black + cold brew ×1, discount 5.00, note "Customer requested gift receipt"), ORD-9820 (David, CASH, COMPLETED, earbuds ×1), ORD-9819 (Elena, TRANSFER, **PENDING**, cardholder + desk mat). Created **through OrderService** with placed_at backdated (2026-07-05 14:32 / 11:15, 2026-07-04 16:45 store-tz) so stock movements, counters, and totals are internally consistent — note: mockData's stored totals (e.g. tax 8.79 on a 108.48 subtotal that includes a non-taxable coffee) will be *corrected* by the totals service; the UI reads whatever the API returns, so parity is structural, not to the buggy mock cents.
9. **Subscription**: PRO / ACTIVE / 19.99 / monthly / renews 2027-07-05.

## DevVolumeSeeder (optional, perf/pagination testing)
Second store ("Second Shop", owner second@dorzak.com) to prove tenant isolation in manual QA + 12 categories, 300 products (10% low-stock, 5% OOS, 20% with variants), 500 customers, 2,000 orders spread over 90 days across all methods/statuses/sources. Runs factories through services where money/stock correctness matters (orders), bulk-inserts the rest.

## Guardrails
- Seeders idempotent per environment (`firstOrCreate` on natural keys) — safe to rerun.
- A `DemoSeederParityTest` asserts: product count 6, hoodie has 3 variants and stock 45, ORD-9821 discount 5.00 and status COMPLETED, settings envelope matches expected JSON fixture. This pins the harness against drift.
- Never run DevVolumeSeeder in production; DemoSeeder only behind `app()->environment(['local','staging'])`.

# 08 — Backend Sub-Agent Task Packets

Each packet is self-contained: a sub-agent reads MASTER_BACKEND_BLUEPRINT.md + the referenced sections and implements without re-analyzing the frontend. Every packet ends with the full test suite green.

---

## TP-01 — Foundation & Auth (M1)
**Objective**: bootable Laravel 11 API with Sanctum SPA auth, tenancy trait, role gates.
**Migrations**: `create_users_table`, `create_stores_table`, `create_store_user_table` (+ Sanctum/session/queue framework migrations).
**Models**: User, Store, StoreUser (pivot w/ casts). **Enums**: StaffRole. **Support**: RoleMatrix, StoreContext. **Trait**: BelongsToStore.
**Middleware**: EnsureStoreMember, SetStoreContext.
**Controllers**: Api/Auth/{AuthController, RegisteredStoreController, PasswordController}. **Action**: RegisterStoreAction.
**Requests**: LoginRequest, RegisterRequest. **Resources**: UserResource, AuthSessionResource.
**Routes**: per api-contracts.json auth block.
**Tests**: `tests/Feature/Auth/{LoginTest, RegisterTest, MeTest, PasswordResetTest}`, `tests/Unit/RoleMatrixTest`.
**Dependencies**: none.
**Acceptance**: register→me flow returns store+abilities; disabled pivot 403; login throttle; gates match matrix (06 §3).

## TP-02 — Settings module (M2)
**Objective**: full settings envelope + 8 group writes + uploads + audit.
**Migrations**: storefront_settings, receipt_settings, integration_settings, settings_audit_logs.
**Models**: StorefrontSetting, ReceiptSetting, IntegrationSetting, SettingsAuditLog (+ Store hasOne relations, `Store::settingsEnvelope()`).
**Controller**: SettingsController, StorefrontMediaController. **Service**: SettingsService (group update + audit + envelope).
**Requests**: Settings/{UpdateGeneralRequest, UpdateBusinessRequest, UpdateCurrencyRequest, UpdateTaxesRequest, UpdateReceiptsRequest, UpdatePaymentsRequest, UpdateIntegrationsRequest, UpdateStorefrontRequest, UploadStorefrontMediaRequest}.
**Resource**: SettingsResource. **Enums**: Currency, Language, SymbolPlacement.
**Key rules**: payments ≥1 POS method; slug regex+global unique+reserved list (`www,api,app,admin,mail,store,shop,dorzak`); QAR→BEFORE; RegisterStoreAction now seeds the three settings rows (amend TP-01 action).
**Tests**: Feature/Settings per group (happy+rules+403 cashier/viewer), audit row assertions, upload validation.
**Dependencies**: TP-01.
**Acceptance**: GET /settings mirrors `initialAccountInfo` shape after DemoSeeder; every UI tab saves.

## TP-03 — Staff & subscription display (M2)
**Objective**: staff CRUD + invitation lifecycle + subscription read.
**Migrations**: staff_invitations, subscriptions.
**Models**: StaffInvitation, Subscription. **Enums**: SubscriptionPlan.
**Controllers**: StaffController, StaffInvitationController, SubscriptionController.
**Actions**: InviteStaffAction, AcceptInvitationAction. **Mail**: StaffInvitationMail (queued, markdown).
**Requests**: StoreStaffInvitationRequest, AcceptInvitationRequest, UpdateStaffMemberRequest.
**Resources**: StaffMemberResource, SubscriptionResource. **Policy**: StaffPolicy (owner-protection, last-owner).
**Tests**: invite→mail queued→accept→login; expired 410; duplicate invite 422; owner immutability; last-owner 409; deactivate revokes tokens; subscription read seeded PRO; portal owner-only.
**Dependencies**: TP-01 (TP-02 for mail branding optional).
**Acceptance**: Users page flows complete; settings STAFF tab list matches.

## TP-04 — Catalog (M3)
**Objective**: categories, products, variants, stock ledger, image upload, AI description.
**Migrations**: categories, products, product_variants, stock_movements.
**Models**: Category, Product, ProductVariant, StockMovement (+ scopes: `active`, `lowStock`, `search`). **Enums**: StockMovementType, Unit.
**Controllers**: CategoryController, ProductController, ProductImageController, AiController.
**Services**: ProductService (create/update/variant-sync/parent-stock rule), StockService (adjust w/ row lock + movement), AiDescriptionService (flagged).
**Requests**: StoreCategoryRequest, UpdateCategoryRequest, ReorderCategoriesRequest, StoreProductRequest, UpdateProductRequest, UploadProductImageRequest, SuggestDescriptionRequest.
**Resources**: CategoryResource, ProductResource, ProductVariantResource. **Policies**: ProductPolicy, CategoryPolicy.
**Tests**: Feature/Catalog/{CategoryApiTest, ProductApiTest, ProductVariantSyncTest, ProductImageTest, StockAdjustmentTest}; Unit/StockServiceTest.
**Dependencies**: TP-01, TP-02 (settings for currency display none needed server-side — soft dep).
**Acceptance**: matrix in 02 Pages 3–5; sku partial-unique proven (delete→recreate same sku ok); cross-tenant 404s.

## TP-05 — Customers (M4)
**Objective**: CRM endpoints + import/export.
**Migration**: customers. **Model**: Customer (phone_normalized mutator).
**Controllers**: CustomerController, CustomerImportController. **Service**: CustomerImportService. **Job**: ImportCustomersJob. **Support**: CsvStreamer.
**Requests**: StoreCustomerRequest, UpdateCustomerRequest, ImportCustomersRequest.
**Resource**: CustomerResource. **Policy**: CustomerPolicy.
**Tests**: search 3 fields; sorts; meta summary math; duplicate phone 422 + duplicate_customer_id; soft delete; export CSV shape; import happy/errors/async threshold.
**Dependencies**: TP-01.
**Acceptance**: Customers page complete against API.

## TP-06 — Orders & checkout (M5) ← the core packet
**Objective**: order lifecycle with correct money and stock.
**Migrations**: orders, order_items.
**Models**: Order, OrderItem. **Enums**: OrderStatus, PaymentMethod, OrderSource.
**Services**: OrderTotalsService (build FIRST, TDD: tax excluded/included, non-taxable lines, rounding, discount clamp), OrderService (create/cancel/complete), extend StockService (deductForOrder/restoreForOrder).
**Controllers**: OrderController, OrderStatusController, OrderExportController.
**Events/Listeners**: OrderCreated→{UpdateCustomerStats, CheckLowStock}; OrderCancelled→{RestoreStockOnCancel, ReverseCustomerStats}; OrderCompleted→DeductStockOnCompletion (online); LowStockDetected→LowStockNotification.
**Requests**: StoreOrderRequest (StoreScopedExists rules, payment-method-enabled rule), UpdateOrderStatusRequest.
**Resources**: OrderResource (+receipt block), OrderItemResource. **Policy**: OrderPolicy.
**Artisan**: `customers:recalculate-stats`, `stock:reconcile`.
**Tests**: Feature/Orders/{CreateOrderTest, OrderListTest (filters+summary), OrderStatusTest, OrderExportTest, WalkInOrderTest, StockRaceTest (parallel-ish via transactions)}; Unit/OrderTotalsServiceTest (exhaustive cases table).
**Dependencies**: TP-04, TP-05.
**Acceptance**: 02 Page 1–2 matrices green; POS sale demo end-to-end; totals CHECK constraint holds under seeder.

## TP-07 — Reports (M6)
**Objective**: finance + analytics aggregates.
**Controllers**: Reports/{FinanceController, AnalyticsController}. **Service**: ReportService.
**Tests**: fixture with orders straddling day/week/month boundaries in a non-UTC store timezone; method breakdown sums to gross; pending excludes cancelled; top products by real revenue; gross profit from unit_cost snapshots; viewer allowed, cashier 403.
**Dependencies**: TP-06.
**Acceptance**: Finances + Analytics pages numerically match hand-computed fixtures.

## TP-08 — Public storefront (M7)
**Objective**: anonymous catalog + WhatsApp checkout.
**Controllers**: Public/{StorefrontController, PublicOrderController}. **Services**: PublicOrderService, WhatsAppMessageBuilder.
**Routes**: routes/public.php w/ throttles.
**Events**: OnlineOrderPlaced → NotifyStaffOfOnlineOrder (database notification).
**Requests**: StorePublicOrderRequest.
**Resources**: PublicStoreResource, PublicProductResource.
**Tests**: disabled store 404; hidden/out-of-stock filtering both setting states; delivery fee threshold math; min order 409; customer upsert by phone; wa.me URL encoding (unicode product names!); rate limit 429; PENDING online order does NOT deduct stock, completion does.
**Dependencies**: TP-06.
**Acceptance**: StorefrontPreviewPage runs against `/api/public/*`; full online order→complete→stock flow.

## TP-09 — Billing (M8)
**Objective**: real (or cleanly stubbed) billing portal.
**Files**: SubscriptionController@portal/invoice; optional laravel/cashier install + webhook route; config/services.billing.
**Tests**: owner-only 403 matrix; stub returns configured URL; webhook signature check (if Cashier).
**Dependencies**: TP-03.
**Acceptance**: Billing page buttons functional or gracefully stubbed with clear messaging.

## TP-10 — React integration (M9, frontend agent)
**Objective**: delete mockApi; live API client.
**Files**: `src/api/apiClient.ts`, `src/api/endpoints/*.ts` (per-module functions from 09-integration map), auth pages (LoginPage), route guard, role-gated nav (abilities from /auth/me), store rewires, error toast normalization, G-1/G-2/G-3 gap fixes, POS discount input, orders date-range wiring.
**Tests**: Playwright e2e updated to run against seeded backend (`npm run test:e2e` with API_URL env).
**Dependencies**: TP-01…TP-08 per page.
**Acceptance**: `mockApi.ts` deleted; all pages function; Playwright green.

## TP-11 — Hardening & ops (M9)
**Objective**: production readiness.
**Files**: rate-limit config, cors, security headers middleware, backup config, Horizon (optional), DemoSeeder parity assertions test, EXPLAIN review notes, deploy script/Envoyer notes, `12-security-plan.md` checklist executed.
**Dependencies**: all.
**Acceptance**: security checklist items each verified w/ a test or manual note; seeder produces UI-identical data.

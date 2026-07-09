# 04 — Database Schema

## Engine decision: **PostgreSQL 16**

Justification (vs MySQL 8):
1. **Partial unique indexes** — we need "unique per store *among non-deleted rows*" on `products.sku`, `customers.phone_normalized`, and a globally unique live `storefront_settings.slug`. Native in PG (`UNIQUE … WHERE deleted_at IS NULL`); in MySQL this requires generated-column hacks.
2. **Reporting** — finances/analytics pages are aggregate-heavy (`FILTER (WHERE …)` aggregates, window functions for top-products ranking) and PG's planner handles them better without denormalizing.
3. **JSONB** with indexing for the audit log payloads and future integration configs.
4. **CHECK constraints enforced** (money invariants), `citext` for case-insensitive email/slug.
5. Laravel supports both equally; ops cost identical on Forge/RDS. Nothing in this domain favors MySQL.

Conventions: bigint identity PKs; `timestamps()` everywhere; `deleted_at` only where noted; FK cascade rules explicit; all tenant tables carry `store_id` FK → `stores.id` cascadeOnDelete + index (composite indexes lead with `store_id`).

---

### TABLE: users
Global identities (staff). No store data here.
| column | type | null | notes |
|---|---|---|---|
| id | bigint pk | | |
| name | varchar(120) | no | |
| email | citext | no | unique |
| password | varchar(255) | no | bcrypt |
| remember_token | varchar(100) | yes | |
| email_verified_at | timestamptz | yes | |
| created_at / updated_at | | | |

Indexes: unique(email). Soft deletes: no (detach via pivot instead).

### TABLE: stores  (tenant root — maps `accountInfo` identity/contact/locale/tax fields)
| column | type | null | default | notes |
|---|---|---|---|---|
| id | bigint pk | | | |
| name | varchar(120) | no | | businessName |
| tagline | varchar(160) | yes | | |
| owner_name | varchar(120) | yes | | display field on receipts |
| email | citext | yes | | contact email |
| phone | varchar(32) | yes | | |
| whatsapp | varchar(32) | yes | | drives wa.me checkout |
| address | varchar(190) | yes | | |
| city / state / zip_code | varchar(80/80/20) | yes | | |
| country | varchar(80) | no | 'United States' | whitelist validated |
| timezone | varchar(64) | no | 'UTC' | ASSUMPTION — needed for period reports |
| language | varchar(2) | no | 'en' | en\|ar |
| currency | char(3) | no | 'USD' | enum list |
| symbol_placement | varchar(6) | no | 'BEFORE' | BEFORE\|AFTER |
| charge_sales_tax | boolean | no | true | |
| tax_rate | decimal(5,2) | no | 0 | percent |
| tax_id | varchar(64) | yes | | VAT/registration id |
| tax_included_in_price | boolean | no | false | |
| accepted_payment_methods | jsonb | no | '["CASH","CARD","TRANSFER","WHATSAPP"]' | PAYMENTS tab toggles |
| created_at / updated_at | | | | |

Indexes: none beyond pk (small table). CHECK: tax_rate between 0 and 100.

### TABLE: store_user (pivot)
| column | type | null | notes |
|---|---|---|---|
| id | bigint pk | | |
| store_id | fk stores | no | cascadeOnDelete |
| user_id | fk users | no | cascadeOnDelete |
| role | varchar(10) | no | OWNER\|MANAGER\|CASHIER\|VIEWER |
| is_active | boolean no default true | | toggle in Users page |
| joined_at | timestamptz | yes | shown as "Joined {date}" |
| created_at / updated_at | | | |

Indexes: unique(store_id, user_id); index(user_id).

### TABLE: staff_invitations
| column | type | null | notes |
|---|---|---|---|
| id | bigint pk | | |
| store_id | fk | no | |
| invited_by | fk users | no | restrictOnDelete |
| name | varchar(120) | no | |
| email | citext | no | |
| role | varchar(10) | no | MANAGER\|CASHIER\|VIEWER |
| token | varchar(64) | no | unique, random(48) |
| expires_at | timestamptz | no | now()+7d |
| accepted_at | timestamptz | yes | |
| created_at / updated_at | | | |

Indexes: unique(token); unique(store_id, email) WHERE accepted_at IS NULL (one pending invite per email).

### TABLE: storefront_settings (1:1 store)
| column | type | null | default |
|---|---|---|---|
| id, store_id fk unique | | | |
| online_store_enabled | boolean | no | false |
| slug | citext | yes | | globally unique (subdomain); partial unique WHERE slug IS NOT NULL |
| bio | varchar(300) | yes | |
| banner_path / logo_path | varchar(255) | yes | uploaded file path or absolute URL |
| accent_color / secondary_color | char(7) | no | '#1890ff' / '#373f4e' |
| allow_delivery / allow_pickup | boolean | no | true |
| delivery_fee | decimal(8,2) | no | 0 |
| free_delivery_threshold | decimal(10,2) | yes | null=never free |
| min_order_amount | decimal(10,2) | no | 0 |
| whatsapp_ordering_enabled | boolean | no | true |
| show_out_of_stock_online | boolean | no | true |

### TABLE: receipt_settings (1:1 store)
| column | type | default |
|---|---|---|
| id, store_id fk unique | | |
| header | varchar(160) null | |
| footer | varchar(160) null | |
| show_logo / show_address / show_tax | boolean | true |
| auto_print | boolean | false |

### TABLE: integration_settings (1:1 store)
| column | type |
|---|---|
| id, store_id fk unique | |
| facebook_pixel_id | varchar(32) null |
| google_analytics_id | varchar(32) null |
| facebook_connected | boolean default false |
| facebook_page_name | varchar(120) null |

### TABLE: categories
| column | type | null | notes |
|---|---|---|---|
| id, store_id fk | | | |
| name | varchar(80) | no | unique(store_id, name) |
| color | char(7) | no | default '#3b82f6' |
| sort_order | int | no | default 0 |
| timestamps | | | soft deletes: **no** (delete nulls products) |

Indexes: unique(store_id,name); index(store_id, sort_order).

### TABLE: products
| column | type | null | default | notes |
|---|---|---|---|---|
| id, store_id fk | | | | |
| category_id | fk categories | yes | | nullOnDelete |
| name | varchar(160) | no | | |
| description | text | yes | | |
| price | decimal(12,2) | no | | ≥0 CHECK |
| reduced_price | decimal(12,2) | yes | | CHECK < price when set |
| cost | decimal(12,2) | no | 0 | |
| sku | varchar(64) | yes | | partial unique(store_id, sku) WHERE deleted_at IS NULL |
| unit | varchar(12) | no | 'pcs' | |
| image_path | varchar(255) | yes | | path or URL |
| label_name | varchar(40) | yes | | price-label text |
| label_color | char(7) | yes | | |
| taxable | boolean | no | true | |
| track_stock | boolean | no | true | |
| stock | int | no | 0 | cached ledger head; Σ variants when has variants |
| min_stock | int | no | 0 | low-stock threshold |
| show_in_online_store | boolean | no | true | |
| is_featured | boolean | no | false | |
| is_active | boolean | no | true | |
| timestamps, deleted_at | | | | soft deletes: **yes** |

Indexes: (store_id, category_id); (store_id, is_active); (store_id, show_in_online_store); partial unique sku; GIN trigram on name (search) — optional, plain index(store_id,name) first.
CHECK: price ≥ 0, cost ≥ 0, stock ≥ 0 (drop this CHECK if negative-stock setting ever added), min_stock ≥ 0.

### TABLE: product_variants
| column | type | null | notes |
|---|---|---|---|
| id | pk | | |
| product_id | fk products | no | cascadeOnDelete |
| name | varchar(120) | no | "Small / Black" |
| price | decimal(12,2) | no | falls back to product price at creation |
| stock | int | no | default 0 |
| sku | varchar(64) | yes | partial unique per store via join-checked rule (validate in app) |
| sort_order | int | no | 0 |
| timestamps | | | soft deletes: no (cascade w/ product) |

Indexes: (product_id, sort_order).

### TABLE: customers
| column | type | null | notes |
|---|---|---|---|
| id, store_id fk | | | |
| name | varchar(120) | no | |
| email | citext | yes | |
| phone | varchar(32) | no | required in UI |
| phone_normalized | varchar(32) | no | digits-only, generated in app; partial unique(store_id, phone_normalized) WHERE deleted_at IS NULL |
| address | varchar(190) | yes | |
| city | varchar(80) | yes | |
| tax_id | varchar(64) | yes | |
| notes | text | yes | |
| total_orders | int | no default 0 | cached counter |
| total_spent | decimal(14,2) | no default 0 | cached counter ("Balance" column) |
| timestamps, deleted_at | | | soft deletes: **yes** |

Indexes: (store_id, name); (store_id, total_spent) for balance sort; partial unique phone.

### TABLE: orders
| column | type | null | notes |
|---|---|---|---|
| id, store_id fk | | | |
| order_number | varchar(20) | no | 'ORD-1000'… unique(store_id, order_number) |
| customer_id | fk customers | yes | nullOnDelete |
| customer_name | varchar(120) | no | snapshot; 'Walk-in Customer' when null customer |
| customer_phone | varchar(32) | yes | snapshot |
| status | varchar(10) | no | PENDING\|COMPLETED\|CANCELLED |
| payment_method | varchar(10) | no | CASH\|CARD\|TRANSFER\|WHATSAPP |
| source | varchar(6) | no | POS\|ONLINE |
| fulfillment | varchar(8) | yes | DELIVERY\|PICKUP (online only) |
| subtotal | decimal(12,2) | no | Σ line_total |
| discount | decimal(12,2) | no default 0 | |
| tax_rate | decimal(5,2) | no | snapshot of store rate |
| tax_amount | decimal(12,2) | no default 0 | |
| delivery_fee | decimal(8,2) | no default 0 | online orders |
| total | decimal(12,2) | no | CHECK total = subtotal − discount + tax_amount + delivery_fee (skip check when tax_included) — enforce in service + test if CHECK too rigid |
| notes | varchar(500) | yes | |
| placed_at | timestamptz | no | index; UI "Date & Time" |
| completed_at / cancelled_at | timestamptz | yes | |
| created_by | fk users | yes | nullOnDelete; POS operator |
| timestamps | | | soft deletes: **no** (cancel, don't delete) |

Indexes: unique(store_id, order_number); (store_id, placed_at desc); (store_id, status); (store_id, payment_method); (customer_id).

### TABLE: order_items
| column | type | null | notes |
|---|---|---|---|
| id | pk | | |
| order_id | fk orders | no | cascadeOnDelete |
| product_id | fk products | yes | nullOnDelete (snapshot survives) |
| variant_id | fk product_variants | yes | nullOnDelete |
| product_name | varchar(160) | no | snapshot |
| variant_name | varchar(120) | yes | snapshot |
| unit_price | decimal(12,2) | no | snapshot (variant price when variant) |
| unit_cost | decimal(12,2) | no default 0 | snapshot for profit report |
| quantity | int | no | ≥1 CHECK |
| line_total | decimal(12,2) | no | unit_price × quantity |
| taxable | boolean | no default true | snapshot of product.taxable |
| timestamps | | | |

Indexes: (order_id); (product_id) for top-products aggregate.

### TABLE: stock_movements
| column | type | null | notes |
|---|---|---|---|
| id, store_id fk | | | |
| product_id | fk products | no | cascadeOnDelete |
| variant_id | fk product_variants | yes | cascadeOnDelete |
| type | varchar(14) | no | INITIAL\|SALE\|CANCEL_RETURN\|ADJUSTMENT\|RESTOCK |
| quantity_change | int | no | signed (−2 for sale of 2) |
| stock_after | int | no | ledger head snapshot |
| order_id | fk orders | yes | nullOnDelete |
| user_id | fk users | yes | nullOnDelete |
| note | varchar(255) | yes | |
| created_at | | | no updated_at (append-only) |

Indexes: (store_id, product_id, created_at); (order_id).

### TABLE: subscriptions
| column | type | null | notes |
|---|---|---|---|
| id, store_id fk | | | |
| plan | varchar(12) | no | FREE\|PRO\|ENTERPRISE |
| status | varchar(12) | no | ACTIVE\|PAST_DUE\|CANCELLED |
| price | decimal(8,2) | no default 0 | 19.99 seed |
| billing_cycle | varchar(8) | no | monthly\|yearly |
| renews_at | timestamptz | yes | |
| provider / provider_id | varchar(20)/varchar(64) | yes | Stripe later |
| timestamps | | | |

Index: (store_id, status).

### TABLE: settings_audit_logs (ASSUMPTION, recommended)
store_id fk, user_id fk nullOnDelete, group varchar(20), old_values jsonb, new_values jsonb, created_at. Index (store_id, created_at).

### Framework tables
`personal_access_tokens` (Sanctum), `password_reset_tokens`, `sessions` (SPA cookie mode), `jobs`, `failed_jobs`, `notifications` (database channel for low-stock/online-order alerts), `media`/`uploads` handled by simple `image_path` columns (no medialibrary dependency required — decision left to implementer, plan assumes plain disk storage `storage/app/public/stores/{id}/…`).

## Relationship summary (FK / cascade)
| From | To | FK | On delete |
|---|---|---|---|
| store_user.store_id/user_id | stores/users | | cascade |
| categories.store_id | stores | | cascade |
| products.store_id | stores | | cascade |
| products.category_id | categories | | **set null** |
| product_variants.product_id | products | | cascade |
| customers.store_id | stores | | cascade |
| orders.store_id | stores | | cascade |
| orders.customer_id | customers | | **set null** (snapshot keeps name) |
| orders.created_by | users | | set null |
| order_items.order_id | orders | | cascade |
| order_items.product_id / variant_id | products / variants | | set null |
| stock_movements.* | see table | | cascade product, set null order/user |
| staff_invitations.store_id | stores | | cascade |
| subscriptions.store_id | stores | | cascade |

Machine-readable mirror: `database-schema.json`. Migration order = the table order above (respects FK deps).

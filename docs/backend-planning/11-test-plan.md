# 11 — Quality & Test Plan

Framework: Pest, `RefreshDatabase`, PostgreSQL service container in CI (schema uses PG partial indexes — do not test on sqlite).
Shared fixture: `tests/TestCase` helpers — `createStoreWithOwner()`, `secondStore()`, `actingAsRole(StaffRole $role)`.
Blanket rules applied to **every module** (write once as shared/architecture tests where possible):
1. Cross-tenant probe: acting as store A, request store B's resource id → **404**.
2. Role matrix: for each write endpoint, the roles outside its ability get **403** (data-provider style).
3. Unauthenticated → 401; disabled member → 403 ACCOUNT_DISABLED.
4. Validation errors return Laravel 422 shape.

## Unit tests
### OrderTotalsService (the money kernel — exhaustive table)
- test_subtotal_is_sum_of_line_totals
- test_tax_applies_only_to_taxable_lines
- test_tax_excluded_mode_adds_tax_on_discounted_base *(decision: discount reduces tax base proportionally — document + pin)*
- test_tax_included_mode_extracts_tax_without_changing_total
- test_charge_sales_tax_disabled_yields_zero_tax
- test_rounding_half_up_at_line_level (0.005 cases)
- test_discount_cannot_exceed_subtotal (throws)
- test_variant_price_overrides_product_price
- test_delivery_fee_added_after_tax_base (online)
- test_free_delivery_threshold_waives_fee / test_threshold_null_never_waives
### StockService
- test_deduct_locks_and_writes_sale_movement_with_stock_after
- test_deduct_variant_updates_variant_and_parent_sum
- test_insufficient_stock_throws_domain_conflict
- test_untracked_product_skips_stock_checks_and_movements
- test_restore_for_cancelled_order_mirrors_deductions_exactly
- test_adjustment_records_signed_diff
### RoleMatrix — test_matrix_matches_spec (fixture of doc 06 §3 table)
### WhatsAppMessageBuilder — test_message_contains_order_lines_and_total / test_unicode_product_names_urlencoded / test_uses_store_whatsapp_number_digits_only

## Feature tests by module

### Auth
- test_user_can_login / test_login_rejects_wrong_password / test_login_throttled_after_5_attempts
- test_register_creates_store_owner_pivot_and_default_settings
- test_me_returns_store_role_and_abilities
- test_disabled_staff_cannot_authenticate_requests
- test_logout_revokes_session

### Settings
- per group: happy save + response is full envelope; key rules:
- test_payments_requires_at_least_one_pos_method
- test_storefront_slug_must_be_globally_unique / …rejects_reserved_words / …regex
- test_currency_qar_forces_symbol_before
- test_tax_rate_bounds_0_100
- test_settings_write_creates_audit_log_row
- test_cashier_cannot_update_settings / test_viewer_can_read_settings

### Staff
- test_owner_can_invite_staff_and_mail_is_queued
- test_invite_rejects_existing_member_and_duplicate_pending
- test_invitation_accept_creates_user_with_role / test_expired_token_returns_410
- test_cannot_deactivate_last_owner / test_manager_cannot_modify_owner
- test_deactivated_member_tokens_revoked
- test_remove_staff_keeps_their_orders_created_by_null

### Categories
- test_categories_listed_with_product_counts_ordered
- test_category_name_unique_per_store_not_globally
- test_delete_category_nulls_product_category_and_reports_count
- test_reorder_persists

### Products
- test_products_can_be_listed / filtered_by_category / searched_by_name_sku / filtered_low_and_out_stock / sorted / paginated
- test_product_requires_name_and_price
- test_reduced_price_must_be_lower_than_price
- test_sku_unique_per_store_and_reusable_after_delete (partial index!)
- test_create_writes_initial_stock_movement
- test_variant_sync_creates_updates_deletes_by_id
- test_parent_stock_equals_variant_sum_and_direct_write_rejected
- test_stock_edit_writes_adjustment_movement
- test_soft_deleted_product_hidden_from_lists_but_receipts_intact
- test_image_upload_validates_mime_and_size_and_replaces_old_file
- test_cashier_can_manage_products (explicit — unusual grant)

### Customers
- test_search_matches_name_email_phone / test_sort_by_name_and_balance
- test_meta_summary_counts_whole_store_not_page
- test_customer_requires_name_and_phone
- test_duplicate_phone_returns_422_with_duplicate_customer_id
- test_detail_includes_last_three_orders
- test_delete_soft_and_orders_keep_snapshot_name
- test_export_csv_has_expected_header_and_rows
- test_import_creates_rows_and_reports_errors / test_large_import_is_queued

### Orders / Checkout
- test_can_create_order_and_totals_are_recomputed_server_side (client-sent totals ignored)
- test_order_cannot_be_created_with_empty_cart
- test_validates_missing_or_foreign_store_product
- test_rejects_disabled_payment_method
- test_walk_in_order_snapshots_customer_name
- test_order_number_sequential_per_store (two stores interleaved)
- test_stock_deducted_and_movement_written / test_insufficient_stock_returns_409_with_details
- test_customer_counters_incremented / …reversed_on_cancel
- test_low_stock_notification_fired_when_threshold_crossed
- test_order_list_supports_status_filter / payment_filter / date_range_in_store_timezone / search
- test_list_meta_summary_matches_filters
- test_status_transition_matrix (allowed + forbidden each)
- test_cancel_restores_stock_exactly
- test_receipt_payload_includes_store_receipt_settings
- test_export_csv_respects_filters
- test_viewer_can_list_but_not_create

### Reports
- test_finance_periods_bound_in_store_timezone (fixture across midnight/week/month boundaries, store tz Asia/Qatar)
- test_by_method_sums_to_gross / test_pending_revenue_excludes_cancelled
- test_analytics_top_products_ranked_by_real_revenue
- test_gross_profit_uses_cost_snapshots_not_current_cost
- test_inventory_health_uses_track_stock_only
- test_manager_cannot_access_other_business_data (cross-tenant aggregates return zero, not leak)
- test_cashier_gets_403_viewer_gets_200

### Public storefront
- test_disabled_store_returns_404_everywhere
- test_catalog_hides_offline_products / test_out_of_stock_visibility_follows_setting
- test_public_order_computes_delivery_fee_and_waives_over_threshold
- test_public_order_enforces_min_order_amount
- test_public_order_upserts_customer_by_phone
- test_public_order_is_pending_whatsapp_online_and_returns_wa_url
- test_pending_online_order_does_not_deduct_stock_until_completed
- test_rate_limit_returns_429
- test_no_private_fields_leak_in_public_resources (assert exact key set)

### Subscription
- test_subscription_summary_shape_matches_contract
- test_portal_and_invoice_owner_only

## Integration with frontend expectations
- `DemoSeederParityTest` (doc 10) pins seeded data to UI expectations.
- Contract snapshot tests: for each list endpoint, assert exact JSON key set of one item (catches accidental field renames that would break the typed frontend).
- Playwright e2e (existing `tests/e2e/`) re-pointed at seeded backend in TP-10: navigation, POS sale flow, product create, customer create, settings save, storefront preview order.

## CI gates
`pint --test` → `phpstan analyse (level 6+)` → `pest --parallel` with postgres service → frontend `tsc && vite build` → Playwright (nightly or pre-merge on integration branches).

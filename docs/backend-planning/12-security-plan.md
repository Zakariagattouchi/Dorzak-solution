# 12 — Security & Data Protection Plan

## 1. Authentication
- Sanctum SPA cookie mode: httpOnly, `SameSite=Lax`, `Secure` cookies; session driver database/redis; `SESSION_DOMAIN=.dorzak.com`; `SANCTUM_STATEFUL_DOMAINS` limited to the app origin.
- Passwords: bcrypt (default), `Password::defaults()` rule (min 8 + uncompromised check in prod).
- Login throttle 5/min per email+IP (`RateLimiter::for('login')`); generic failure message (no user enumeration); forgot-password always 200.
- Personal access tokens only issued when explicitly requested (`device_name`); listed abilities; revoke on staff deactivation and password change.

## 2. Authorization
- Every route inside `auth:sanctum` + `EnsureStoreMember`; **no route without a policy/gate check** — enforce with an architecture test asserting each Api controller method calls `authorize`/gate (or FormRequest::authorize ≠ `return true` blindly).
- Role matrix (06 §3) is the single source; last-owner and owner-target protections in StaffPolicy.
- Billing endpoints owner-only; settings owner/manager; reports deny cashier (matrix-tested).

## 3. Tenant isolation
- `BelongsToStore` global scope + scoped route-model binding; cross-tenant = 404 (tested per module, doc 11 blanket rule 1).
- Public API resolves tenant strictly by slug; responses use dedicated Public resources with an exact whitelisted key set (leak test in 11).
- Aggregates/reports always start from `Store::current()` relations — never raw table queries without store_id.
- File storage namespaced `stores/{id}/…`; download/serve paths validated to that prefix.

## 4. Request validation & mass assignment
- FormRequest on every write; models use `$guarded = []` **only** behind DTO-fed services, otherwise explicit `$fillable`.
- `StoreScopedExists` rule for all foreign ids in payloads (product_id, category_id, customer_id, variant_id).
- Reject unknown enum values (backed enums in casts + `Rule::enum`).
- Numeric bounds everywhere money/qty appears (min:0 / min:1, max caps) — prevents negative-price or negative-qty total manipulation.
- **Never trust client totals**: POS/online order money recomputed server-side (OrderTotalsService); client-sent subtotal/total fields are not even read.

## 5. Rate limiting
- Authed API 120/min/user; login 5/min; invitation accept 10/min/IP; AI endpoints 20/day/store.
- Public storefront: reads 60/min/IP, order create 5/min/IP + 20/day/IP per store — WhatsApp checkout is unauthenticated and abusable; also add a honeypot field and (if abused) Turnstile. Log 429s.

## 6. Sensitive data
- PII held: customer name/phone/email/address, staff emails. No card data anywhere — POS "CARD" is a *label* for an external terminal payment; keep it that way (**never** collect PANs; if online card payments ever come, use a hosted provider page — SAQ-A scope).
- `.env` secrets (DB, ANTHROPIC_API_KEY, mail, Stripe) never in repo; rotate on staff departure.
- Logs: scrub request bodies on auth routes; no PII in log context beyond ids.
- Backups encrypted at rest; retention policy documented; customer delete = soft delete now, add hard-purge command for data-subject requests (GDPR-ish hygiene even if not strictly required in Qatar/US markets).

## 7. Payment/billing caution
- Stripe (TP-09): webhooks verified by signature; portal URLs generated server-side per store; no plan changes trusted from client.

## 8. Audit logging
- settings_audit_logs for all settings groups (money-affecting: tax, payments, storefront fees).
- stock_movements is itself an immutable audit trail (append-only, `user_id` recorded).
- orders record `created_by`; status changes stamp completed_at/cancelled_at (add `status_changed_by` if disputes arise — deferred).

## 9. CORS / CSRF
- CORS: exact app origin(s), `supports_credentials: true`, no wildcard with credentials.
- CSRF: Sanctum's cookie flow (`/sanctum/csrf-cookie` + `X-XSRF-TOKEN`); public POST endpoints are stateless token-less but rate-limited + validated (CSRF not applicable, no session).

## 10. File uploads
- Images only: `mimes:jpg,jpeg,png,webp|max:4096`, re-encoded server-side (Intervention/GD `encode()`) to strip EXIF/polyglot payloads; random hashed filenames; served from /storage (or S3) — never executed paths; `X-Content-Type-Options: nosniff` header.
- CSV import: parsed with league/csv, size cap 5 MB, row cap, values treated as data (also prefix `=,+,-,@` cells with `'` on **export** to prevent CSV formula injection in Excel).

## 11. API error handling
- Production: `APP_DEBUG=false`; exception handler returns uniform JSON (401/403/404/409/422/429/500) without stack traces or SQL; 500s reported to Sentry/Flare.
- Domain conflicts use stable `code` strings (INSUFFICIENT_STOCK, LAST_OWNER, MIN_ORDER_NOT_MET, ACCOUNT_DISABLED) — frontend copy keyed off codes, not messages.

## 12. Transport & headers
- HTTPS only (HSTS), secure cookies, security headers middleware: nosniff, frame-ancestors 'none' (API), referrer-policy.
- The public storefront slug is a subdomain (`{slug}.dorzak.com`): wildcard TLS cert + reserved-word list (doc 05) to prevent cookie-scope/subdomain-takeover style issues; never set app cookies on the wildcard scope.

## Launch checklist (executed in TP-11)
- [ ] All blanket tests (tenant 404, role 403) green per module
- [ ] `APP_DEBUG=false`, debugbar absent in prod deps
- [ ] Throttles verified with tests
- [ ] Upload re-encoding verified
- [ ] CSV export formula-escape verified
- [ ] Public resource key-set leak tests green
- [ ] Secrets in env/manager only; `.env.example` complete
- [ ] Error responses carry no stack traces
- [ ] Backups scheduled + restore rehearsed

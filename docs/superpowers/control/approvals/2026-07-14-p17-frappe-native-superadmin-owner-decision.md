# P17 Frappe-Native Superadmin Owner Decision

## Decision metadata

- Approver: Dorzak owner
- Recorded: 2026-07-14, Asia/Qatar
- Scope: P17 architecture direction only
- Lifecycle effect: Approved decision; P17 remains Not started and Not authorized
- Supersedes: Any assumption that Dorzak's internal Superadmin must be a custom Dorzak-branded interface

## Approved decision

Dorzak's internal Superadmin control plane will be Frappe-native. It will use a pinned, supported ERPNext/Frappe release plus only the approved CRM and operational applications required by the final P17 capability matrix.

The internal interface may keep Frappe's professional Desk visual and interaction patterns with only minimal Dorzak branding such as logo, color tokens, navigation labels and domain. Merchant-management interfaces and merchant-customer websites remain fully Dorzak-branded. Merchants and their customers do not see Frappe, require Frappe accounts or navigate to Frappe Desk.

## Site and data isolation

- Every paid merchant retains an isolated Frappe site, database, file boundary, backup identity, encryption context and integration principal.
- The internal Dorzak Superadmin site is a separate site and database.
- The Superadmin site must not query merchant databases directly, share credentials or combine raw merchant business records in one tenant database.
- Cross-merchant visibility and intervention use tenant-bound Dorzak gateway and `dorzak_core` APIs with explicit authorization, reason, correlation ID and audit.
- Fleet dashboards may store redacted health summaries and immutable external identifiers, not uncontrolled copies of merchant business records.

## Source-of-truth boundary

Dorzak SaaS remains authoritative for:

- platform identities, MFA, organizations and memberships;
- plans, immutable plan versions, subscriptions, billing and entitlements;
- merchant-site registry, provisioning and lifecycle state;
- Superadmin and delegated-access grants plus the cross-site audit index;
- branding, public content, merchant/customer sessions, release and incident controls.

Each isolated merchant Frappe site remains authoritative for the approved ERP business records provided by its installed application manifest, including applicable accounting, inventory, commerce, CRM, support, HR and operational documents.

The internal Superadmin Frappe site is authoritative only for Dorzak's internal operating records, such as internal CRM, support cases, assignments and service workflows, plus redacted projections. When its UI edits Dorzak-platform data, it calls governed Dorzak APIs; it does not become a second authority for the same data.

## Access governance

- The platform owner has full governed Superadmin access through an explicit owner policy.
- Sensitive merchant access still requires tenant selection, a stated reason, and MFA or re-authentication where required.
- Dorzak teammates have no ambient merchant access.
- Every delegated grant records the grantee, merchant/site, purpose, ticket or reference, permitted modules/actions, read/write level, issuer, start, expiry and revocation.
- Delegated access is explicit, revocable, time-bound, read-only by default and audited on every use.
- Revocation or expiry takes effect immediately.
- Shared Administrator accounts, reusable merchant credentials and direct database access are prohibited.

## Frappe application policy

ERPNext is the core. Frappe CRM, Helpdesk, HRMS, Insights and other operational applications are candidates, not automatically installed on every site.

P17 must define versioned site profiles with an explicit application manifest. An application is included only after license, security, supported-version, migration, backup, localization and contract-compatibility review. Merchant sites receive only the applications required by their approved category and plan profile; the internal Superadmin site receives only Dorzak-internal operational applications.

## Required P17 design outcomes

The later P17 design and plan must cover governed visibility and editing for organizations, subscriptions, the plan catalogue, site provisioning, fleet health, migrations, queues, backups, support, intervention, delegated access, audits, incidents and release controls.

It must include cross-tenant denial tests, grant expiry and revocation tests, owner-access tests, site-isolation tests, API source-of-truth tests and application-manifest compatibility tests.

## Authorization effect and exclusions

This decision authorizes only durable recording and later P17 design alignment. It does not authorize P17 planning, implementation, application installation, provisioning, data migration, credentials, supplied-source edits or public release. Existing P17 dependency and milestone gates remain unchanged.

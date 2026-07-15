# Dorzak Context

## Product and release boundary

Dorzak is one branded multi-vertical business operating platform. It has one complete public launch gate; internal packets are not partial public releases. P00 stabilizes the recovered React/Laravel starting point and does not advertise later roadmap capability.

## Current P00 system

The merchant management surface is React 18, TypeScript and Vite. Laravel 13 is the current modular monolith and API. SQLite is fast feedback; PostgreSQL 16 is qualification. The public media contract is origin-relative `/storage/<disk-relative-key>`. Canonical demo and browser commerce use Qatar/QAR.

## Target authority

Dorzak owns identity, plans and immutable entitlements, experience, orchestration, public content, vertical-native domains, consent and governed support. ERPNext is the operational and financial core for every paid organization. Each paid organization has one isolated Frappe site/data boundary; one or many locations belong to that organization and Enterprise has no location minimum. Each field and business fact has one writer.

## Bounded contexts

- Execution context resolves actor, organization, authorized location, plan version, country pack and correlation ID and fails closed.
- The current Vite/React app is the merchant desktop, POS and builder-editor surface; it is not the P17 Superadmin target.
- The internal Superadmin target is a separate Frappe-native site/database using Frappe Desk with minimal Dorzak branding and only a versioned approved application manifest.
- Superadmin cross-merchant visibility and intervention use tenant-bound Dorzak gateway and `dorzak_core` APIs plus explicit reason-bound, time-limited, audited grants. The internal site never queries merchant databases directly, shares merchant credentials, or stores uncontrolled raw merchant records.
- Laravel owns Dorzak platform authority and native-domain rules and exposes governed Dorzak DTO/API contracts.
- P17 remains Not started and Not authorized; P00 records this boundary only.
- ERPNext owns paid operational/financial records after their approved cutovers.
- Payments, storage, messaging and future ERP commands sit behind narrow versioned adapters.
- The public/customer surface is a separate server-rendered React deployment; its final framework waits for the measured P05 decision.

## Invariants

- No dual-write stock, invoice, payment, customer-account, plan or workflow truth.
- No database transaction remains open across ERPNext/provider HTTP.
- UI consumes Dorzak DTOs and never raw provider/ERPNext shapes.
- Tenant/location/plan authority is server-side and never inferred from a request body.
- Publication and plan versions are immutable after activation.
- P00 uses zero browser retries, explicit destructive-database guards and evidence tied to exact SHAs.

## Decision index

ADRs 0001–0007 record authority, tenancy, modularity, launch policy, immutable plans, cutover and frontend surface boundaries. The approved product baseline and technical roadmap remain the higher-level sources when a later plan conflicts.

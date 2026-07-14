# Dorzak Complete Launch Baseline v1 — Product Requirements Document

| Field | Decision |
|---|---|
| Status | Review candidate assembled from approved owner decisions |
| Date | 14 July 2026 |
| Product | Dorzak Merchant SaaS and vertical business operating platform |
| Initial countries | Qatar and Tunisia |
| Required languages | English, French, and Arabic |
| Release policy | One complete public launch; no paid plan or merchant category is sold before its advertised journeys pass the global launch gate |
| Technical core decision | One isolated ERPNext/Frappe site and database per paid Dorzak merchant organization |

---

## 1. Document authority and supersession

This document becomes the product source of truth after the owner reviews and approves this written version. It consolidates the owner decisions made across the Dorzak planning sessions and resolves contradictions between older documents.

The following rules apply:

1. This PRD supersedes the three-plan `Free / Pro / Scale` proposal, the QAR 299 Scale assumption, the three-location Enterprise assumption, and any preview or partial-public-launch proposal in `docs/DORZAK_MERCHANT_SCALE_PLAN_PRD.md`.
2. The public commercial hierarchy is **Free Tools, Pro, Business, and Enterprise**.
3. Enterprise never requires a minimum location count. One location with a large team, complex operation, governance need, or high transaction volume can qualify.
4. Prices, currencies, billing cycles, trials, limits, add-ons, availability, and plan composition are Superadmin-managed versioned data. No commercial price is hard-coded in application source.
5. The approved Work/Gantt design remains valid for its product behavior, plan progression, Superadmin controls, accessibility, and quality requirements, except where it describes ERPNext as optional or Dorzak/Laravel as the final project/commerce source of truth. The ERPNext-core boundary in this PRD supersedes those clauses.
6. The July 12 Marketing plans are design inputs only. They must be rewritten for Laravel 13, Organization tenancy, Business packaging, immutable plan versions, and ERPNext-backed commerce before execution.
7. Supplied business repositories remain read-only. Reuse requires a recorded source audit, license decision, version pin, security review, and an explicit Dorzak integration boundary.
8. Internal development may merge behind disabled capabilities. Public pricing, trials, sales, and merchant access remain unavailable until the complete-launch release gate passes.

### 1.1 Requirement language

- **Must** is a launch requirement.
- **Should** is expected unless a documented architecture decision replaces it.
- **May** is optional and cannot be marketed before it is enabled and tested.
- Requirement identifiers are stable and should be referenced by designs, implementation plans, tests, analytics, release evidence, and support runbooks.

---

## 2. Executive product decision

### 2.1 Problem statement

The current Dorzak application is closest to an incomplete Pro commerce product. Its present plan model concentrates too much value in Pro, does not contain the approved Business tier, does not justify Enterprise through operational depth and service, and still reflects older Free/Scale/location assumptions. The product also lacks the complete corporate acquisition site, scalable Free Tools hub, advanced builder, isolated ERPNext fleet, vertical operating products, French catalogue, governed Superadmin intervention, and end-to-end release evidence required by the approved vision.

Without one canonical boundary, Dorzak would risk selling overlapping plan promises, duplicating stock and finance between Laravel and ERPNext, exposing partial merchant categories, and operating support/customization through ungoverned manual access. Those risks would make the price difficult to justify regardless of the amount charged.

### 2.2 Solution

Dorzak will provide one coherent four-level commercial system, a merchant-branded mobile customer experience, a desktop-first management application, and a separate Superadmin control plane. Every paid organization receives an isolated ERPNext operational/financial core behind Dorzak APIs; Dorzak owns identity, plans, experience, vertical domains, orchestration, public content and governed support. Shared kernels, immutable plan versions, country/language release packs and one global launch gate keep all advertised experiences consistent.

Dorzak will be a branded, multi-vertical business operating platform that lets an organization create its public website, sell products or services, manage customers and operations, receive payments, use plan-appropriate ERP and growth functions, and work with the Dorzak team without leaving the Dorzak experience.

The product has four connected surfaces:

1. **Corporate Dorzak website:** public acquisition, free tools, solutions, pricing, client proof, resources, account creation, trial selection, and login.
2. **Merchant management application:** desktop-first operational workspace for the merchant owner, managers, teams, and delegated Dorzak staff.
3. **Merchant-branded customer experience:** mobile-first website, storefront, booking, giving, membership, education, healthcare, account, checkout, status, and support journeys. The end customer does not know that unrelated businesses share Dorzak infrastructure.
4. **Dorzak Superadmin control plane:** commercial catalogue, subscriptions, provisioning, tenant health, support intervention, public-site content, client proof, audit, and release controls.

Dorzak is not an ERPNext skin and will not expose ERPNext Desk to normal merchants or customers. ERPNext is the isolated operational and financial engine behind the Dorzak interface.

### 2.3 Product promise by plan

| Plan | Core promise | Primary buyer reason |
|---|---|---|
| Free Tools | Useful business tools forever, with an account but no merchant workspace or ERP tenant | Experience Dorzak value and prepare business documents without commitment |
| Pro | Launch and operate a professional single business through Dorzak | Sell, book, serve, receive payments, manage customers, and use a strong business back office |
| Business | Coordinate a growing team and automate more of the operation | Collaboration, stronger builder, advanced workflows, marketing, reporting, scheduling, and controls |
| Enterprise | Run a complex organization with governance, integration, portfolio control, advanced branding, and Dorzak-delivered assistance | Operational scale, customization, security, capacity, integration, managed delivery, and accountability |

The price justification is an outcome ladder, not a list of hidden menu items:

- Pro creates a functioning digital business.
- Business reduces coordination work and increases repeatable growth.
- Enterprise controls complexity, risk, customization, integration, and service delivery.

---

## 3. Goals, non-goals, and launch boundaries

### 3.1 Goals

- **GL-001:** Deliver every advertised Pro, Business, and Enterprise journey end to end in one public launch.
- **GL-002:** Make the plan difference understandable within 30 seconds on the pricing page.
- **GL-003:** Give every paid merchant a fully Dorzak-branded experience backed by an isolated ERPNext site/database.
- **GL-004:** Support retail/shop, supplier/wholesale, restaurant/F&B, appointments/services, coiffeur/salon, beauty center, doctor/small clinic/health center, education/school, gym/fitness, nonprofit organization, and general business profiles.
- **GL-005:** Provide English, French, and Arabic parity, including true RTL application behavior where appropriate.
- **GL-006:** Make customer-facing journeys mobile-first and merchant/Superadmin management desktop-first.
- **GL-007:** Give Superadmin complete visibility and governed intervention without giving ordinary Dorzak teammates ambient merchant access.
- **GL-008:** Allow Dorzak to build or modify an Enterprise merchant website and functionality through a governed shared-delivery workflow.
- **GL-009:** Keep pricing and packaging commercially editable without corrupting existing subscriptions.
- **GL-010:** Achieve zero open Severity 1 and Severity 2 defects at launch and no known cross-merchant data path.
- **GL-011:** Preserve a distinctive, premium, high-technology brand while meeting accessibility and performance targets.
- **GL-012:** Make the public claims, plan entitlements, APIs, background workers, and ERPNext enforcement agree.

### 3.2 Non-goals for this launch

- **NG-001:** Dorzak will not expose ERPNext Desk as the ordinary merchant interface.
- **NG-002:** Dorzak will not use one shared ERPNext Company record per unrelated merchant as a tenant boundary.
- **NG-003:** Dorzak will not hold card data or act as a licensed payment acquirer.
- **NG-004:** Dorzak will not provide autonomous AI that publishes, spends, refunds, changes stock, changes a schedule, sends a campaign, or makes a healthcare/education/nonprofit decision without authorized approval.
- **NG-005:** Dorzak will not permit arbitrary merchant JavaScript, HTML, or CSS in the website builder.
- **NG-006:** Dorzak will not advertise tax deductibility, medical compliance, legal compliance, delivery coverage, or 24/7 support without a verified country/provider/operational basis.
- **NG-007:** Dorzak Work/Gantt will not replace appointment, class, provider, classroom, restaurant dispatch, volunteer shift, or clinical scheduling domains.
- **NG-008:** Mautic Advanced Automation, a broad third-party marketplace, autonomous operations agents, and unverified parcel-carrier coverage are not launch dependencies and must not appear in launch marketing.

### 3.3 Complete-launch definition

“One complete launch” means the release switch remains off until:

- all four plan experiences match the published capability manifest;
- every listed merchant category passes its category fixture and named SME review;
- the public website, pricing, signup, trials, payments, ERPNext provisioning, merchant app, customer mobile experience, Superadmin, and support journeys work together;
- English, French, and Arabic catalogues are complete;
- Qatar and Tunisia country rules are verified;
- accessibility, security, privacy, performance, backup, restore, rollback, observability, and incident runbooks pass;
- no advertised feature is marked “coming soon,” silently disabled, or represented only by a mock UI.

---

## 4. Actors and ownership boundaries

### 4.1 Dorzak platform actors

| Actor | Access |
|---|---|
| Platform owner / Superadmin | Full platform control through explicit platform policies; every sensitive access and mutation is audited |
| Dorzak operations administrator | Provisioning, subscription operations, provider status, and approved operational tools; no implicit merchant-data access |
| Dorzak support observer | Read-only tenant/project access only through a reason-bound, time-limited grant |
| Delegated Dorzak teammate | Exact organization, project/service request, actions, and time window granted by Superadmin |
| Marketing/content administrator | Corporate content, free tools, translations, client proof, and publishing workflows; no merchant operational access |
| Finance administrator | Dorzak subscriptions, invoices, payment exceptions, credits, and commercial reports; no unnecessary merchant customer content |

### 4.2 Merchant actors

| Actor | Access |
|---|---|
| Organization owner | Organization, locations, billing, plans, teams, modules, data, integrations, exports, and support grants |
| Organization administrator | Configured administrative scope excluding owner-only/legal actions unless granted |
| Location manager | Authorized locations, departments, staff, operations, approvals, and reports |
| Specialist | Role-specific surfaces such as cashier, waiter, kitchen user, provider, teacher, trainer, fundraiser, volunteer coordinator, or inventory user |
| Contributor | Assigned tasks, checklists, comments, files, approvals, and narrow operational updates |
| Auditor/viewer | Read-only, export, or audit scope as explicitly configured |

### 4.3 Merchant-customer actors

Customers, patients, learners/guardians, members, donors, volunteers, applicants, and B2B buyers are separate role records inside one merchant organization. A person can hold multiple roles, but Dorzak must never merge roles silently or infer identity, role or record access from a phone number alone.

Each merchant owns an isolated customer database. The same mobile number used with two merchants creates two independent customer principals and records. No customer receives a Dorzak-wide directory of businesses or cross-business purchase/account history.

### 4.4 Customer account creation

- **ID-001:** Checkout requires customer name and mobile number; email is optional unless a country/provider/legal workflow requires it.
- **ID-002:** A successful first purchase, booking, membership, donation, enrollment, or other authenticated merchant journey automatically creates a merchant-scoped account. It links to an existing account only after proof defined for that role and risk level.
- **ID-003:** An opaque merchant-scoped principal ID is canonical. Name and normalized mobile number are the initial user-facing account details; the verified mobile is an authentication factor, not a globally unique person identifier, and authentication uses OTP or another verified challenge, never name alone.
- **ID-004:** Challenges are random, hashed, short-lived, single-use, attempt-limited, rate-limited, and scoped by merchant organization plus mobile.
- **ID-005:** Merchant staff identities and customer identities use separate guards, tokens, permissions, and storage.
- **ID-006:** The customer experience carries only the merchant brand unless the merchant’s plan/template deliberately retains a small Dorzak attribution.
- **ID-007:** Recycled/shared numbers, dependants and sensitive roles require an explicit profile selection plus step-up, guardian/organizational proof or staff-reviewed match. Revoking one role or guardian link cannot delete or expose another role.
- **ID-008:** OTP proof for Merchant A can never link, authenticate or reveal an account at Merchant B, even when the same number and name are used.

---

## 5. Target architecture: Dorzak control plane plus isolated ERPNext cores

### 5.1 Approved architecture

```text
Corporate website / Merchant desktop / Customer mobile / Superadmin
                              |
                       Dorzak API/BFF
                              |
        identity + plans + tenant routing + policy + audit
                              |
                organization-to-site resolver
                              |
           custom, versioned dorzak_core Frappe API
                              |
          one isolated ERPNext site/database per merchant
```

- **ARC-001:** Every paid merchant organization receives one isolated ERPNext/Frappe site, database, files boundary, integration principal, encryption context, backup identity, and lifecycle record.
- **ARC-002:** Multiple legal companies may share one site only when they belong to the same merchant security boundary.
- **ARC-003:** An ERPNext `Company` represents a legal/accounting entity, never an unrelated Dorzak tenant.
- **ARC-004:** Dorzak derives the ERPNext site from the authenticated organization context. A browser/request body can never nominate a site or tenant as authority.
- **ARC-005:** Browsers call only Dorzak. Generic ERPNext resource APIs and site credentials are not exposed to customers or ordinary merchant clients.
- **ARC-006:** A separate versioned `dorzak_core` Frappe app exposes constrained business commands, actor audit, health checks, webhooks, and contract versions.
- **ARC-007:** Upstream ERPNext core remains unmodified wherever possible. Dorzak custom behavior belongs in `dorzak_core`, migrations, fixtures, and adapters.
- **ARC-008:** Production uses a pinned supported ERPNext/Frappe release. The supplied `develop` checkout requiring Frappe 17 development versions is reference code, not a launch image.
- **ARC-009:** ERPNext GPL and trademark obligations receive legal review. GPL source is not copied into proprietary Dorzak/Laravel code; service and custom-app boundaries are documented.
- **ARC-010:** Dorzak-to-ERP traffic uses mutually authenticated transport and a per-site signed actor envelope containing issuer, audience, organization, site, actor, grant where used, allowed action, correlation ID, issued time, expiry no longer than 60 seconds, and one-time `jti`. `dorzak_core` rejects a wrong-site, wrong-action, expired or replayed envelope; tenant signing keys rotate independently.

### 5.2 Tenant mapping

| Dorzak concept | ERPNext representation |
|---|---|
| Merchant organization | One ERPNext site/database |
| Legal entity | Company |
| Branch/location | Dorzak Location plus Warehouse and Cost Center; optional `Dorzak Branch` DocType where needed |
| Merchant staff identity | Dorzak identity; mapped shadow ERPNext actor only when required for audit/SSO |
| Merchant customer principal | Dorzak authentication/consent principal mapped to an ERPNext Customer/Contact where commercial activity exists |
| Product/service | ERPNext Item/variant/UOM/price-list record plus Dorzak presentation projection |
| Operational inventory | ERPNext warehouse and stock ledger |
| Sale/purchase/financial document | ERPNext transaction document |
| Vertical-specific booking/membership/giving/education/health record | Dorzak vertical domain, with financial/inventory consequences posted to ERPNext |

### 5.3 System-of-record map

ERPNext is canonical for:

- companies, charts of accounts, fiscal settings, cost centers, accounts, taxes, warehouses, and accounting dimensions;
- items, variants, UOMs, price lists, landed costs, and operational stock;
- suppliers, purchase orders, receipts, supplier invoices, stock transfers, stock counts, and COGS;
- commercial customers/suppliers and their account relationships;
- sales orders, POS/sales invoices, delivery notes, returns, payment entries, expenses, settlements, and GL consequences;
- assets, maintenance, and ERP Projects/Tasks/Timesheets for every paid Dorzak Work record;
- final financial effects of discounts, loyalty, gift cards, donations, memberships, delivery, fees, and refunds.

Dorzak is canonical for:

- organization/site registry, countries, vertical profile, plan subscriptions, entitlements, trials, Dorzak billing, and release state;
- Dorzak staff identities, merchant staff sessions, customer mobile identities, authentication, consent, preferences, and channel eligibility;
- public website content, storefront themes, domains, SEO, navigation, page-builder schema, translations, and media presentation;
- carts and pre-submission commercial drafts, idempotency receipts, integration outbox, webhook receipts, synchronization state, and rebuildable read projections;
- appointment, class, resource, waitlist, check-in, membership entitlement, donor/volunteer/application, education, clinic, restaurant-dispatch, and other vertical-specific domains that ERPNext does not safely model;
- campaigns, referrals, reviews, customer-experience rules, support workflows, Superadmin grants, and Dorzak-managed delivery;
- provider orchestration for subscriptions, messaging, delivery, OTP, media scanning, and external integrations.

There must never be two independently editable versions of the same stock balance, invoice total, payment, or ERP transaction.

#### 5.3.1 Field-level authority and write direction

| Record/field family | Authoritative owner | Allowed write direction |
|---|---|---|
| Login credential, opaque customer principal, verified contact channel, consent and preference | Dorzak | Dorzak writes; ERPNext may receive a purpose-limited projection but never authenticates or merges the principal |
| Customer-supplied display name, mobile, email and delivery/contact address | Dorzak Party/Contact | Dorzak command updates the verified Party record and projects approved fields to the mapped ERPNext Customer/Contact |
| Customer/supplier legal name, tax identity, credit limit, payment terms, accounting account and receivable/payable status | ERPNext | Merchant uses a Dorzak command that commits ERPNext; Dorzak keeps a read-only projection |
| Item code, variant, UOM, operational price list, tax template, warehouse and stock | ERPNext | All mutations commit through `dorzak_core`; Dorzak presentation records key to immutable ERP identities |
| Product/site presentation copy, translated media, SEO and page placement | Dorzak | Dorzak only; it references the ERP Item identity and cannot overwrite operational fields |
| Cart, RFQ/quote draft before submission | Dorzak | Dorzak only until submission |
| Submitted Quotation, Sales/Purchase document, invoice, payment and GL/stock consequence | ERPNext | Submission creates the canonical ERPNext document; revise, cancel, convert, return and correct operations go through ERPNext |
| Work Project/Task core identity, title, status, dates, progress, hierarchy and canonical assignee reference | ERPNext | Every paid Work mutation commits through ERPNext; Dorzak projects it |
| Work portfolio, baseline, scenario, approval, customer-publication and managed-delivery extensions not represented safely by ERPNext | Dorzak | Dorzak extension keys to immutable ERP Project/Task IDs and cannot duplicate an editable core field |
| Appointment, class, membership entitlement, donor/volunteer, education, clinic and restaurant-native state | Dorzak vertical domain | Dorzak owns the domain state; resulting invoice, payment, stock, asset or accounting command commits to ERPNext and stores its immutable mapping |

A field may have only one authority entry. Adapters reject an update that attempts to mutate a projection-owned field. Mapping changes are versioned and reconciled before release.

### 5.4 Transaction flow

1. Dorzak validates identity, tenant, plan, permission, request version, sensitive activation and vertical rules.
2. Dorzak creates an idempotent intent and the audit/outbox evidence required by the authoritative owner.
3. For an ERP-owned record, Dorzak sends a constrained command to the correct site; ERPNext commits transactionally, returns the document identity and Dorzak updates its projection.
4. For a Dorzak-native vertical record with no financial/stock consequence, Dorzak commits the native aggregate and outbox transactionally.
5. For a Dorzak-native action that requires an ERP invoice/payment/stock/accounting consequence, the native aggregate remains in an explicit `POSTING_PENDING`/held state while the saga commits ERPNext. Success that implies paid, invoiced, stocked or financially final is shown only after ERP acceptance; failure compensates or remains visibly recoverable according to the domain state machine.
6. Provider payment is an additional reconcilable fact and cannot make an ERP or native record silently final through an unsigned/duplicate event.
7. The client receives canonical success only after every authority needed for the claimed outcome commits, except for the explicit provisional offline POS flow below.
8. Webhooks and reconciliation continuously prove that Dorzak projections and cross-system mappings match their owners.

### 5.5 Offline POS exception

- **ARC-011:** Offline POS uses an enrolled/revocable device identity, rotating device key, append-only journal, monotonic sequence and locally unique idempotency key. Every sale signs the device, register, organization, location, operator, time and approved catalogue/price/tax snapshot version.
- **ARC-012:** Offline eligibility expires with the signed snapshot. Only cash and explicitly approved non-network local tenders are allowed; card/provider payments, credit, loyalty/gift redemption, refunds, transfers and entitlement changes remain unavailable.
- **ARC-013:** The UI and provisional receipt state “Recorded offline — not yet posted,” carry a local serial, and never claim a final ERP invoice, synchronized stock or settled provider payment.
- **ARC-014:** Reconnection validates key, sequence, snapshot and replay status, then posts exactly once. If canonical stock is insufficient, Dorzak does not invent or silently allow negative stock: the journal enters `STOCK_CONFLICT`, remains non-final, and requires an authorized manager to correct warehouse/batch/quantity or perform an audited cancellation/refund before posting.
- **ARC-015:** A final fiscal/customer receipt is issued only after ERPNext accepts the canonical document. Both the offline evidence and posting/correction audit remain retained according to the country pack.

### 5.6 ERPNext failure behavior

- Reads use explicit freshness timestamps and may use last-known Dorzak projections.
- Critical stock, payment, and availability reads may bypass stale projections.
- Online writes remain pending or fail recoverably; Dorzak does not create a second local financial truth.
- Idempotent retries use bounded backoff and dead-letter queues.
- Site-specific failures do not block unrelated merchants.
- Superadmin sees provisioning, queue, migration, contract, backup, storage, worker, and reconciliation health per site.

### 5.7 Existing-commerce cutover contract

Every existing merchant migrates through:

`PREPARED → WRITE_FROZEN → EXPORTED → IMPORTED → RECONCILED → ROUTED → VERIFIED`

The default launch policy is intentionally non-duplicating:

- closed historical Laravel orders, invoices and stock movements remain readable/receiptable/exportable in an immutable Dorzak Legacy History archive and are not reposted into ERPNext;
- ERPNext receives approved master data, open operational documents, outstanding balances, assets where applicable, current valued stock through an opening Stock Reconciliation, and opening accounting balances through accountant-approved entries at the cutover instant;
- a legally required full-history import is a separately rehearsed mutually exclusive policy; it replaces the opening-history policy and can never be combined with it for the same document/ledger range;
- every imported or archived entity receives an immutable legacy source ID, checksum, cutover batch, policy and ERP mapping where applicable;
- reconciliation must prove entity counts/hashes, open-document totals, customer/supplier balances, stock quantity and valuation by warehouse/item, tax totals, trial balance and retained earnings before routing changes;
- ERP routing cannot enable until merchant owner and finance/data migration approvers sign the reconciliation evidence;
- rollback is allowed only before the first canonical post-cutover write and before the recorded rollback deadline. After that point, recovery is forward through compensating, audited corrections—not a silent switch back to Laravel authority.

---

## 6. Invisible ERPNext provisioning and lifecycle

### 6.1 Provisioning state machine

`REQUESTED → ALLOCATING → INSTALLING → CONFIGURING → VERIFYING → READY`

Provisioning exception states are `RETRYABLE_FAILURE` and `MANUAL_REVIEW`. Every transition is idempotent and audited.

For each paid signup or approved trial:

1. Create the Dorzak organization, owner identity, selected plan version, vertical profile, and initial location.
2. Allocate an internal site name, database, storage namespace, secrets, queue class, and backup policy.
3. Install pinned Frappe, ERPNext, `dorzak_core`, required approved apps, and exact schema/contract versions.
4. Bootstrap company, chart/account template, currency, fiscal settings, taxes, warehouse/location, cost center, price list, payment modes, roles, print formats, language and timezone.
5. Create a unique integration principal and vault its credential.
6. Run tenant-routing, contract, permission, financial, stock, backup, translation, and health probes.
7. Mark the organization ready and continue Dorzak onboarding without showing ERPNext setup screens.

After readiness, the site lifecycle is:

`READY → ACTIVE ↔ SUSPENDED / QUARANTINED → ARCHIVING → ARCHIVED → RESTORING → ACTIVE`, or `ARCHIVED → TERMINATION_PENDING → DELETED`.

| Subscription/billing fact | Site and entitlement behavior |
|---|---|
| Trial or paid checkout pending provisioning | Site provisions; paid features and trial clock remain inactive |
| Trial site reaches `READY` | Subscription enters `TRIALING`, trial clock starts, owner receives access/notification; provisioning time consumes no trial days |
| Immediate paid checkout | Provider authorizes before provisioning and captures only when the site is `READY`; an SLA breach voids the authorization or automatically refunds any captured amount |
| `ACTIVE` subscription | Site is `ACTIVE`; published PlanVersion entitlements apply |
| `PAST_DUE` / `GRACE` | PlanVersion-defined warning and restricted-new-action policy applies; owner retains invoices, required records, support and export access |
| `SUSPENDED` | New merchant/customer transactions and automations stop according to each CapabilityDefinition; statutory records, receipts, safe owner access and export remain available |
| End-of-cycle cancellation | Site becomes read-only, then `ARCHIVED` under published retention; reactivation restores before deletion |
| Security incident | Site enters `QUARANTINED`; public/write routes fail safely while evidence is preserved |
| Legal hold | Archive/deletion clock pauses; access remains least-privilege and audited |
| `TERMINATION_PENDING` | Export, retention, legal-hold, backup and dual-approval checks must pass before irreversible deletion |

Provisioning, payment and subscription changes form one compensating saga with a single correlation ID. A failure cannot leave a charge without a usable site or a usable site without the intended subscription state.

### 6.2 User friction rule

Normal merchants and customers create only Dorzak accounts. They never receive an ERPNext Administrator password, ERPNext login prompt, ERP hostname, integration token, or setup wizard.

A native ERPNext Desk account is permitted only through a separately approved Enterprise exception using SSO, exact roles, plan enforcement inside `dorzak_core`, and complete audit. It is not a launch-default journey.

### 6.3 Site operations

Superadmin must support:

- create, retry, pause, resume, migrate, back up, restore, quarantine, and terminate site lifecycle;
- canary and wave-based ERPNext/app upgrades;
- contract compatibility and schema-drift checks;
- per-site and fleet health, capacity, queue, database, storage, and backup status;
- rollback evidence before every production wave;
- noisy-neighbor quotas and optional dedicated infrastructure for Enterprise;
- export/offboarding and verified deletion after retention/legal obligations.

### 6.4 Release bill of materials and software supply chain

Every production wave requires a signed, reproducible release BOM containing:

- exact ERPNext/Frappe/app source tags and commits, immutable container image digests, base OS/packages, migrations, `dorzak_core` contract version and Dorzak gateway compatibility;
- generated SBOM, provenance/signature, dependency and container vulnerability results, and a written exception for every unresolved finding;
- supported-version window and maximum fleet skew. A site may not remain more than one approved compatible release behind except in a time-bound quarantine approved by the platform owner;
- emergency patch policy: actively exploited or critical issues are remediated within 72 hours or receive an owner/security/legal risk acceptance; high-severity issues within 14 days;
- canary, backup, restore, rollback and contract evidence;
- copyright/license notices, written `dorzak_core` license outcome, any required source-offer/source-delivery package, and approved ERPNext/Frappe trademark wording.

Missing BOM, legal outcome, compatibility evidence or security acceptance blocks the wave and public launch.

---

## 7. Commercial catalogue, plans, and editable pricing

### 7.1 Commercial objects

| Object | Purpose |
|---|---|
| CapabilityDefinition | Code-reviewed catalogue key, kind, dependency, minimum plan/category bundle, enforcement status, copy keys, measurement unit, lifecycle behavior and data classification |
| PlanFamily | Stable public family: Free Tools, Pro, Business, Enterprise |
| PlanVersion | Immutable commercial snapshot: localized name/copy, prices, currency, billing cycles, trials, capabilities, limits, allowances, support, and availability |
| PlanPublication | Impact preview, approvals, effective date, target audience/country, migration policy, and published evidence |
| Subscription | Organization’s current pinned plan version, state, billing cycle, renewal, trial, payment and cancellation facts |
| PlanChange | Upgrade/downgrade/consolidation request, preview, payment, acceptance, effective time, and result |
| AddOnVersion | Optional isolated product/usage/service package with its own price and compatibility rules |
| UsageLedger | Metered quantity, allowance, period, source event, adjustment, and overage state |

### 7.2 Superadmin editable fields

Superadmin may edit through a draft version:

- public name, internal code alias, descriptions, outcome copy, “best for” copy, ordering, recommendation badge, and availability;
- monthly and annual prices, currencies, taxes/fees display policy, annual discount, and country-specific publication;
- default trial days and vertical/country trial overrides;
- capability grants, numeric limits, explicit unlimited state, included usage, overage price, add-on eligibility, support level, and onboarding package;
- whether the plan is self-serve, assisted, quote-based, or temporarily unavailable;
- effective date, grandfather/migration policy, and merchant change summary.

For every capability, the draft must define behavior for create, update, finish/cancel, read, export, queued workers/automations, customer-public access, over-limit state, downgrade, suspension, retention and reactivation. A capability cannot publish while any behavior is implicit.

Superadmin cannot directly mutate a published version. Publishing always creates an immutable version.

### 7.3 Safe publication workflow

`DRAFT → VALIDATED → IMPACT_REVIEW → APPROVED → SCHEDULED → PUBLISHED → RETIRED`

- Dependency and minimum-bundle validation prevents impossible feature combinations and stops a version from weakening the family promise in Section 2.3.
- Impact preview includes affected subscribers, MRR/ARR effect, over-limit organizations, add-on conflicts, country/currency effects, support/infrastructure cost, and merchants requiring consent.
- Upgrades take effect after confirmed payment.
- Downgrades and consolidations default to renewal unless the merchant accepts an earlier effective date.
- Existing records remain readable/exportable; a plan change never silently deletes merchant data.
- Rollback creates a new version cloned from the prior safe definition.
- Two-person approval is required for public price, trial, entitlement, or migration changes.
- Every publication and migration emits an immutable platform audit record.
- Every affected category × plan journey reruns entitlement, denial, upgrade and public-copy regression before publication. EN/FR/AR comprehension testing must show that representative buyers can identify the correct plan and next upgrade reason within 30 seconds.

### 7.4 Pricing benchmark policy

Dorzak should appear modestly less expensive than a genuinely comparable Lightspeed tier where one exists while Dorzak is a newer company, but must not underprice below sustainable delivery cost or compare unlike products.

As of 14 July 2026, Lightspeed’s official US pages list Retail Basic/Core/Plus at USD 89/149/289 per month and Restaurant Starter/Essential/Premium at USD 69/189/399. These values are research references, not Dorzak hard-coded prices.

Before a PlanVersion is published, Superadmin’s pricing review must record:

- comparison date, country/region, competitor, tier, included locations/registers and material features;
- currency conversion source and timestamp where a cross-currency view is used;
- Dorzak infrastructure, ERPNext-site, support, onboarding, messaging, storage, payment, delivery and managed-service cost envelope;
- target gross margin and maximum included usage;
- a comparable competitor tier and evidence that Dorzak’s advertised price is lower on a like-for-like basis when a defensible country/vertical comparison exists or Dorzak makes a public comparison; use Lightspeed for applicable retail/restaurant comparisons and a relevant approved benchmark elsewhere;
- owner and finance approval.

Absence of a defensible competitor tier does not block publication. Cost, capacity, gross-margin and value review is mandatory for every PlanVersion; competitor evidence is conditional and can never be fabricated merely to satisfy a workflow.

The public site never claims “cheaper than Lightspeed” unless the evidence is current, localized, methodologically comparable, and approved for publication.

### 7.5 Trial policy

- Initial published Pro, Business and Enterprise trial default: 14 days.
- Qatar and Tunisia healthcare organizations: one 30-day initial trial.
- Trial rules are versioned by country, vertical, plan family, effective date, and reuse policy.
- Trial time starts only after the isolated ERPNext site reaches `READY` and owner access is issued; provisioning time never consumes evaluation time.
- A trial cannot be obtained repeatedly by changing a form category.
- The trial clock, grace period, expiry, payment confirmation, data access, and paused capabilities are enforced server-side.
- Trial-end UX shows achieved outcomes, pending setup, what will pause, data retention, and available plans.

### 7.6 Plan invariants

- Free Tools has no merchant workspace, storefront, operational database, or ERPNext site.
- All paid plans receive an isolated ERPNext site because ERPNext is the operational core.
- Enterprise has no minimum locations.
- Security, privacy, accessibility, accurate financial/stock calculations, export, backups, consent, and honest errors are never premium paywalls.
- Variable-cost services—SMS, WhatsApp, AI, email at scale, delivery, payment fees, storage, and high API volume—use explicit allowances and metered overages.
- Public pricing and in-app billing read the same published capability manifest that server enforcement uses.
- Free Tools is a public entry level, not a merchant subscription: account creation does not create an Organization, Subscription, storefront or ERPNext site.

### 7.7 Cross-plan capability baseline

| Capability family | Free Tools | Pro | Business | Enterprise |
|---|---|---|---|---|
| Free document/templates/articles/tools | Full free catalogue | Included | Included | Included |
| Merchant workspace and ERPNext site | No | Yes | Yes | Yes |
| Branded public presence | No merchant site | Dorzak subdomain and curated builder | Advanced section builder | Custom domain, advanced branding, managed assistance |
| Customer transactions | No | Core vertical transactions | Advanced rules and automation | Governed/custom/high-complexity workflows |
| Team | No operational team | Entry roles/seats | Collaboration, custom workflow and wider limits | Advanced scope, SSO, audit, governance and service grants |
| Reporting | Free calculators/templates only | Operational reports | Advanced/saved/scheduled analysis | Consolidated, portfolio, audit and governed exports |
| Marketing | Free learning/tools | Manual programs and core campaigns | Automations, segmentation and attribution | Advanced governance, custom journeys and managed service |
| Work management | No app; downloadable templates | Projects/tasks/checklists/board/calendar | Collaboration, files, approvals, Gantt, dependencies and workload warnings | Portfolio, baselines, critical path, capacity, scenarios and managed delivery |
| Locations | Not applicable | Configurable published limit | Higher/configurable limit | Configurable or unlimited; never an eligibility minimum |
| Integrations/API | No | Essential payment/delivery connectors | Selected connectors/webhooks | Advanced APIs, SSO, custom integration and dedicated options |
| Support | Self-help | Standard | Priority | Governed priority/dedicated service as published |

Exact quantities are PlanVersion data and must not be duplicated in static frontend arrays.

### 7.8 Non-removable plan floors and upgrade justification

Superadmin can change quantities, price, availability and optional capabilities, but cannot publish below these category-complete floors without an owner-approved new PRD/version of this baseline:

| Family | Non-removable floor | What it deliberately does not promise | Evidence that justifies its price/upgrade |
|---|---|---|---|
| Free Tools | Account, complete free utility/content catalogue, secure saved history where opted in | Merchant workspace, website, transactions, operational database, staff or ERP site | Completed useful outputs and transparent path to a paid business outcome |
| Pro | Isolated ERP core, category’s complete primary transaction, professional mobile website, customer/party records, payment/receipt, core operational report, essential owner/staff role and support | Advanced team automation, broad approvals, sophisticated builder, portfolio/governance or custom delivery | Website published, first transaction completed, money/stock/service posted correctly and daily operation managed end to end |
| Business | Pro plus multi-user coordination, plan-appropriate scheduling/resources, advanced section builder, workflow/approval, automation/segmentation, advanced reporting and Business Work/Gantt | Enterprise SSO, portfolio governance, dedicated infrastructure, highly custom integration and managed implementation | Coordination steps/hours reduced, fewer errors/no-shows, measurable automation and repeatable team control |
| Enterprise | Business plus complex-operation governance, configurable high limits, SSO/audit/integration, portfolio/capacity, advanced branding, Dorzak-managed delivery and published service commitment | Uncontracted unlimited variable-cost use or arbitrary unsafe code | Controlled operational risk, integration/portfolio value, service outcomes, capacity and accountable Dorzak intervention; no location minimum |

Each vertical table in Section 10 specializes these floors. A plan cannot be made sellable for a category until that category’s complete floor and acceptance matrix pass.

### 7.9 Plan-family retirement or consolidation

Superadmin may reduce the number of public families only through an approved PlanPublication that:

- retires rather than deletes a referenced family/version;
- maps every affected subscription to grandfathering, renewal migration, assisted upgrade/downgrade or cancellation;
- previews revenue, entitlements, over-limit data, add-ons, trials, invoices, public URLs/copy and support impact;
- preserves read/export and never deletes merchant data;
- updates pricing, signup, billing, manifests, analytics and support language from the same effective version;
- obtains owner, finance, operations and product approval and proves rollback through a new immutable publication.

---

## 8. Organization, location, party, and data-isolation model

### 8.1 Organization and location

- An Organization is the tenant and subscription owner.
- A Location is an optional operational dimension belonging to one Organization.
- Every existing Dorzak Store migrates non-destructively into one Organization and remains its first Location.
- An Organization may have one location, two locations, many locations, a branch plus warehouse, or no customer-facing branch.
- Users receive organization, location, department, project, and action scopes.
- Every query fails closed without a valid tenant context.

### 8.2 Party and role kernel

Dorzak must not overload commerce `Customer` to represent every human or organization. The shared party kernel contains:

- organization-scoped Party and Contact identities;
- explicit roles such as customer, B2B buyer, patient, learner, guardian, member, donor, volunteer, applicant, beneficiary, trainer, teacher, provider, supplier contact, and client approver;
- role-specific records and access policies;
- phone/email/address verification and history;
- consent by purpose, channel, locale, policy version, source, evidence, and revocation;
- mappings to ERPNext Customer, Contact, Supplier, Employee, Student, Patient, or approved custom DocTypes where applicable;
- duplicate review without silent cross-role or cross-merchant merging.

### 8.3 Isolation invariants

- A merchant can never query another merchant’s identities, customers, ERPNext site, files, metrics, campaigns, support rooms, or public unpublished content.
- Cross-tenant resource identifiers return 404.
- Site routing is server-derived; request-supplied organization/site IDs are filters only after authorization.
- Per-site credentials, keys, cookies, databases, files and backup identities are never shared.
- Superadmin cross-tenant reads require platform policy, stated reason, correlation ID, and audit.
- Delegated Dorzak access is tenant-scoped, purpose-bound, time-limited, revocable, read-only by default, and visually disclosed to merchant staff when active.

---

## 9. Merchant-category framework

### 9.1 Supported primary categories

1. Retail / Shop
2. Supplier / Wholesale / B2B
3. Restaurant / Café / Food and Beverage
4. Appointments / Professional Services
5. Coiffeur / Salon
6. Beauty Center / Spa
7. Healthcare — Doctor, Small Clinic, Health Center
8. Education / School
9. Gym / Fitness
10. Nonprofit Organization
11. General Business

### 9.2 Category behavior

- Category selection chooses onboarding, terminology, navigation defaults, templates, demo fixtures, website sections, dashboards, status vocabulary, and recommended capabilities.
- Category is not an authorization boundary and does not replace PlanVersion entitlements.
- A merchant can enable an approved secondary capability pack without changing its tenant.
- Category change is audited, affects future defaults, and never rewrites or deletes existing records.
- Common kernels—identity, party, consent, payments, files, messaging, scheduling, workflow, audit, ERP posting, website schema, and analytics—must be reused rather than rebuilt by category.
- Category-specific states and rules stay inside dedicated domains; one generic `OrderStatus` or `AppointmentStatus` enum must not be forced across unrelated businesses.
- Category, plan or secondary-pack selection can never activate a sensitive/regulatory capability by itself. Clinical functions, education involving minors, nonprofit beneficiary intake/disbursement, regulated fundraising and any future biometric function require a server-enforced `SensitiveCapabilityActivation` containing jurisdiction, verified organization/provider credentials or authority, approved service taxonomy, KYC where applicable, data/retention policy, roles, reviewer, evidence, start, expiry and revocation.
- Expiry, failed re-verification or revocation disables new sensitive actions safely without deleting records; Superadmin and merchant receive an audited remediation path.

### 9.3 Common plan progression by category

- **Pro:** the smallest complete, professionally usable workflow for that category.
- **Business:** team coordination, automation, advanced builder, approvals, workload, reporting, and configurable workflows.
- **Enterprise:** governance, complex operations, portfolios, advanced analytics, integration, SSO/security, custom branding, managed delivery, and higher/configurable limits.

---

## 10. Vertical product requirements

### 10.1 Retail / Shop

**Customer mobile journey:** discover catalog → search/filter → product/variant → cart → pickup/delivery → name/mobile checkout → hosted payment or configured method → merchant-scoped account → order status → receipt → loyalty/review/reorder.

**Merchant journey:** catalog/variants/barcodes → stock/locations → POS and online orders → fulfillment → returns/refunds → customers/loyalty → purchasing → reconciliation → reports.

**ERPNext core:** Item, Item Variant, Price List, Warehouse, Stock Ledger, POS/Sales Invoice, Sales Order, Delivery Note, Return, Customer, Supplier, Purchase Order, Purchase Receipt, Payment Entry, accounting and assets.

| Plan | Retail scope |
|---|---|
| Pro | Complete catalog, POS, online storefront, core stock, order/return, customers, receipts, basic loyalty/coupons and operational reports |
| Business | Suppliers, POs/partial receiving, stock counts, transfers within allowed locations, advanced promotions/segments, automation, scheduled reports, custom workflows and Gantt collaboration |
| Enterprise | Central catalogue with location overrides, advanced procurement/costing, portfolio/consolidated control, B2B options, APIs/SSO/audit, advanced capacity, custom website and Dorzak-managed delivery |

Launch-critical states include draft/confirmed/paid/processing/ready/fulfilled/cancelled/partially-refunded/refunded, with separate payment, fulfillment and ERP posting state machines.

### 10.2 Supplier / Wholesale / B2B

**Buyer journey:** authenticated/private catalog → account-specific price/terms → RFQ or quote → approval/PO reference → deposit/partial payment → fulfillment/shipment → invoice/statement → reorder.

**Merchant journey:** company accounts → contacts/credit → price books/volume rules → quotes/orders → procurement/warehouse → receivables/aging → account reporting.

**ERPNext core:** Customer Groups, Price Lists/Pricing Rules, Quotations, Sales Orders, invoices, credit limits, Payment Terms, warehouses, buying, receivables and GL.

Dorzak owns an RFQ/quotation draft only until submission. Successful submission creates the canonical ERPNext Quotation; every revision, approval, cancellation, conversion and commercial total thereafter is an ERPNext command and Dorzak projection.

| Plan | Supplier scope |
|---|---|
| Pro | Public/basic B2B catalog, account requests, RFQ/quotation, standard pricing and order/invoice flow |
| Business | Private catalogs, customer groups, price books, terms, deposits, approvals, sales representatives, recurring orders and receivable reminders |
| Enterprise | Complex contracts, multi-company/location operations, governed credit/approval rules, bulk import/export, advanced audit, API/EDI-style integration and managed onboarding |

### 10.3 Restaurant / Café / Food and Beverage

**Guest journey:** menu/QR → modifiers/allergens → dine-in/pickup/delivery → order/pay → preparation status → pickup/table/delivery status → receipt/feedback.

**Merchant journey:** menu/modifiers/recipes → POS/table/order intake → kitchen display and routing → preparation/ready/service → ingredient stock/waste → shifts/cash close → purchasing and margin.

**ERPNext core:** Items, variants/modifiers mapping, stock/warehouses, recipes/BOM where appropriate, POS/Sales Invoice, purchases, costs, assets and accounting.

**Dorzak-native:** tables, order courses, kitchen stations, tickets, pacing, queue, preparation routing, QR sessions, delivery orchestration and guest status.

Allergen information is safety-controlled, not ordinary marketing copy. A versioned Dorzak `FoodSafetyProfile` maps canonical ERPNext items/BOM ingredients to declared allergens and separately labelled cross-contact risk. Only authorized trained roles may edit/approve it; menu publication snapshots the approved version, shows localized source/freshness and a merchant-approved fallback warning, and retains change/approval history. Unknown or unapproved allergen state cannot be presented as “allergen free.”

| Plan | Restaurant scope |
|---|---|
| Pro | Menu, modifiers, counter/online/QR ordering, pickup/delivery, basic kitchen queue, receipts and operational reports |
| Business | Floor/table controls, KDS stations, recipes/ingredients/waste, advanced routing, team permissions, campaigns, reports and launch/project workflows |
| Enterprise | Multiple revenue centers/complex kitchens, location control, advanced inventory/cost, API/integrations, governance, custom website, portfolio and managed setup |

### 10.4 Appointments / Professional Services

**Customer journey:** discover services/team → choose service/provider/resource/time → provide name/mobile → deposit/payment where required → confirmation → reschedule/cancel → reminders → check-in → completion → receipt/rebook/review.

**Merchant journey:** services/durations/buffers → providers/resources/rooms → availability/time off → booking rules/capacity → deposits/no-show rules → check-in/service → payments → utilization/rebooking.

**ERPNext core:** Items/services, Customer, invoices/payments, employees where mapped, accounting and assets.

**Dorzak-native:** recurrence, availability, resource collision prevention, capacity, waitlists, booking holds, timezone, cancellation/no-show, reminders and check-in.

| Plan | Appointment scope |
|---|---|
| Pro | Service catalog, provider availability, online booking, reminders, basic deposit/payment and customer records within the published location/resource limits |
| Business | Multiple providers/resources, recurring availability, waitlist, packages, no-show/cancellation policies, automation, utilization and team workflows |
| Enterprise | Complex teams/departments, cross-location or high-volume scheduling, SSO, custom workflows/integrations, advanced audit, branded site and managed configuration |

### 10.5 Coiffeur / Salon and Beauty Center / Spa

These profiles reuse the appointment kernel with category-specific service menus, staff/room/equipment resources, service add-ons, packages, memberships, product retail, patch/consent forms where lawful, before/after media consent, commissions and rebooking.

The beauty domain is non-clinical. Patch-test outcome, allergy/contraindication declaration, before/after media and intimate/body imagery are nevertheless sensitive: collection must be structured and minimized, use purpose-specific consent, least-privilege roles, private media delivery, DLP where applicable, access audit, retention and revocation. Clinical/invasive records are prohibited unless the approved Clinical Pack and healthcare service policy are active.

| Plan | Beauty/coiffeur scope |
|---|---|
| Pro | Branded services, team booking, reminders, deposits, customer notes/preferences, receipts and retail products |
| Business | Rooms/equipment, packages/memberships, waitlists, staff utilization, commissions, campaigns, consent/media workflow and advanced builder |
| Enterprise | Large team or complex single site, multi-location operation, advanced permissions/audit, integrations, custom workflows/website and managed assistance |

### 10.6 Healthcare — Doctors, Small Clinics, Health Centers

**Patient mobile journey:** clinic/provider/service discovery → appointment request/booking → verified patient account → approved administrative intake/consent → reminders → check-in/queue → visit attendance/service → invoice/payment → approved secure documents/instructions → follow-up.

**Clinic desktop journey:** patient registration → provider/resource calendar → appointment/queue → attendance/service workspace → secure approved-document delivery → billing → follow-up → privacy/audit/retention.

**ERPNext core:** company/accounting, healthcare service items, invoices/payments, stock for approved supplies/pharmacy workflows, procurement, assets and maintenance.

**Dorzak healthcare domain:** patient identity, appointments, queue, administrative intake/consent, visit attendance/service code, protected documents, provider access, minimum-necessary disclosure, retention, access log and patient portal. The baseline healthcare launch is clinic administration, not an electronic health record.

A separately activated **Clinical Pack** may add clinical encounters, diagnoses, orders, medication/allergy data, prescriptions and clinical documents only after the Qatar or Tunisia pack defines and proves provider-licence verification, high-assurance patient matching, authorship/signing/amendment, document provenance, medication/allergy safety, prescription issuance/cancellation, critical-result/escalation/emergency handling, break-glass access, clinical retention and country-specific safety fixtures. These functions are absent from UI/API/marketing until that complete activation passes.

| Plan | Healthcare scope |
|---|---|
| Pro | Small-practice patient registry, provider calendar, booking, reminders, check-in/queue, service billing, protected documents and patient portal |
| Business | Multi-provider/room scheduling, approved administrative forms/consent, attendance/service workflow, team roles, follow-up automation, operational reports and advanced website |
| Enterprise | Complex clinic/health-center departments, advanced governance/audit, SSO, retention/legal hold, integration, managed configuration and dedicated controls |

Healthcare launch rules:

- Qatar and Tunisia are the only initial healthcare countries.
- First trial is 30 days.
- Health data is classified sensitive, encrypted, purpose-scoped, access-logged, retention-controlled, excluded from ordinary marketing, search snippets, task titles, and non-health analytics.
- Country-specific legal, clinical, hosting/data-transfer, consent, professional-secrecy and patient-rights reviews are release gates, not marketing claims.
- No clinical AI diagnosis, treatment recommendation, autonomous triage, or automated eligibility decision is included.
- Diagnosis, treatment, medication, allergy, prescription, clinical order/result and free-text clinical note fields do not exist in the baseline pack. They can exist only inside an approved Clinical Pack and its separate access, audit, safety and test boundary.

### 10.7 Education / School

**Learner/guardian mobile journey:** discover program/course → inquiry/application → account/guardian link where needed → enrollment/payment → schedule/announcements → attendance/assignments/results according to permissions → invoices/documents/support.

**Institution desktop journey:** programs/courses/cohorts → applicants/admissions → learners/guardians → timetable/resources → attendance → assignments/assessment → fees → staff → communications → reports/accreditation projects.

**ERPNext core:** organization/accounting, fees/invoices/payments, buying/assets, employees, projects and approved education records/adapters.

**Dorzak-native:** branded admissions, guardian/student identities, timetable collision rules, attendance experience, assessment permissions, communications consent and mobile portal.

| Global plan | Education presentation | Scope |
|---|---|---|
| Pro | Education Core | Admissions/enrollment, core courses/cohorts, learner/guardian portal, fees, timetable, attendance, communications and basic Work |
| Business | Education Plus | Advanced scheduling/resources, assignments/assessments, collaboration, files/approvals, Gantt, staff workload warnings, automation and builder |
| Enterprise | Education Enterprise | Institution/campus portfolio, governance, SSO, advanced audit, integrations, capacity/scenarios, custom website and managed delivery |

Public marketing may use the contextual Education Core/Plus/Enterprise labels, but the underlying subscription remains Pro/Business/Enterprise.

Education involving minors requires a Qatar/Tunisia education-and-minor activation pack before enrollment opens. The pack defines institutional authority, guardian evidence, multiple/custodial guardian scope, revocation/dispute handling, learner self-access age/rules, safeguarding escalation, consent, communications, retention/deletion, staff screening/roles, result visibility and named child-privacy/legal approval. A guardian link grants only the exact learner/functions approved and never derives automatically from a shared mobile number.

Authoritative education lifecycles include:

- attendance `DRAFT → SUBMITTED → APPROVED`, with reasoned correction and immutable history;
- assignment `DRAFT → PUBLISHED → SUBMITTED → LATE / RETURNED → GRADED`, with versioned files and deadlines;
- assessment `DRAFT → MODERATION → APPROVED → PUBLISHED → CORRECTED / APPEALED → FINAL`, with separation of author/moderator where configured;
- learner/guardian visibility only after publication and according to the current relationship/permission, including immediate revocation behavior.

### 10.8 Gym / Fitness

**Member mobile journey:** discover timetable/pricing → trial/pass/membership → hosted payment and versioned waiver → book/cancel/waitlist → QR/OTP check-in → attendance/credits → renew/freeze/cancel/refund.

**Gym desktop journey:** locations/rooms/trainers/equipment → memberships/passes → recurring class schedule/capacity → booking/waitlist → check-in/no-show → renewal/payment recovery → equipment maintenance → merchandise/POS → reports.

**ERPNext core:** Subscription/Invoice/Payment concepts where approved, Customer/Contact, Items/Price Lists, POS/merchandise, stock, accounting, assets/maintenance, employees and projects.

**Dorzak-native:** membership entitlement and credits, freeze/proration policy, class capacity, trainer/room collision rules, waitlist promotion, QR replay prevention, attendance, no-show and access-device control.

Membership state is canonical in the Gym domain:

`DRAFT → PAYMENT_PENDING → ACTIVE → GRACE / FROZEN → CANCEL_AT_PERIOD_END → EXPIRED`, with `CANCELLED`, `REFUNDED` and `CHARGEBACK_REVIEW` terminal/exception paths. Every transition stores mandate/payment evidence, entitlement/credit effect, proration policy, effective time and idempotency key. ERP invoices/payments and reversals are mapped and reconciled before access changes become final; payment failure cannot silently leave both paid access and an unpaid ERP record.

| Plan | Gym scope |
|---|---|
| Pro | Branded timetable, member CRM, passes/memberships, recurring payments, basic classes/capacity, booking/cancel, check-in, receipts and reminders |
| Business | Recurrence rules, waitlists, credits, trainer/room assignment, kiosk/QR check-in, freezes/upgrades, payment recovery, automations and utilization |
| Enterprise | Corporate memberships, complex/high-volume scheduling, cross-location policies where used, SSO, devices, audit, integrations and managed delivery |

Launch excludes body/health measurements, biometric/facial access, medical records and clinical coaching. The initial launch is adult-only unless a separate guardian/minor design passes review.

### 10.9 Nonprofit Organization

**Donor journey:** discover campaign/fund → one-time/recurring gift → hosted payment → acknowledgement/receipt → manage anonymity/preferences/recurrence.

**Volunteer journey:** browse opportunity → apply with skills/availability → screening → shift/check-in/hours → communications.

**Applicant/beneficiary journey:** separate private intake → review → decision/status → approved service/disbursement milestones. This journey is compartmentalized from donor marketing.

**Organization desktop journey:** chapters/program sites → campaigns/funds/appeals → donor/member CRM → donations/reconciliation → acknowledgements → volunteers/shifts → grant/program applications/approvals → budgets/outcomes → events/procurement.

**ERPNext core:** accounting, Budget, Cost Center, Accounting Dimensions, Payment Entry, GL, Projects, buying, assets and final restricted/designated financial consequences.

**Dorzak-native:** giving pages, donor/member/volunteer/beneficiary role separation, recurring gift state, campaign/fund designations, consent/anonymity, screening/shifts, beneficiary privacy, program outcomes and localized receipts.

Contribution state is canonical in the Nonprofit domain:

`INITIATED → PROVIDER_PENDING → RECEIVED → ACKNOWLEDGED → RECONCILED`, with `FAILED`, `REFUNDED`, `CHARGEBACK_REVIEW` and `REVERSED` exception paths. Recurring mandates have `ACTIVE / PAUSED / CANCEL_AT_PERIOD_END / CANCELLED / FAILED` states. Every gift freezes amount, currency, donor/anonymity/consent, campaign/fund designation and receipt wording; refunds/chargebacks reverse operational designation totals and ERP postings idempotently before a revised acknowledgement is exposed.

| Plan | Nonprofit scope |
|---|---|
| Pro | Branded campaigns/giving, donor/member CRM, one-time/recurring gifts, designation, acknowledgements/receipts and basic volunteer applications |
| Business | Donor journeys/segments, volunteer opportunities/shifts/hours, approved non-sensitive applicant intake/review, configured approvals, operational designation reports and chapter rollups |
| Enterprise | Grant/program portfolio, advanced governance, retention/legal hold, SSO, accounting/project integration, audit/export and managed delivery |

Dorzak cannot claim that a contribution is tax deductible or a designation is a legally available restricted-fund balance unless the organization’s country/legal configuration and ERPNext reconciliation support the claim.

Launch applicant intake permits identity/contact, program requested, eligibility declarations, consent and low-risk supporting documents explicitly approved by the country/organization policy. It excludes case notes, medical/legal records, minors, vulnerable-person records and automated eligibility. Donor/fundraiser, volunteer and beneficiary roles are compartmentalized on every plan where intake exists; access logging and dual authorization for disbursement are safety baselines, not Enterprise paywalls.

### 10.10 General Business

**Customer mobile journey:** discover company/offering → view product/service/project proof → inquiry or request for quote → merchant response/approval → configured checkout/deposit/invoice → merchant-scoped account → status/documents/support.

**Merchant desktop journey:** website/offering → leads/parties → inquiry/RFQ/quotation → order or service engagement → invoice/payment → customer status → documents/communications → operational Work → finance/reporting.

**ERPNext core:** Items/services, Customers/Contacts, Leads/Opportunities where adopted, Quotations, Sales Orders, invoices/payments, buying, expenses, projects/tasks, assets and accounting.

**Dorzak-native:** branded customer account, pre-submission inquiry/draft, website/builder, consent/communications, customer-facing status, plan policy, managed delivery and vertical-pack activation.

| Plan | General Business scope |
|---|---|
| Pro | Professional mobile website, offerings, inquiries/RFQs, quotations, configured order/service billing, customers, receipts, basic CRM/reporting and Work |
| Business | Team pipeline, approvals, advanced builder, automation/segments, reusable workflows, files, scheduling where enabled, advanced reports and Gantt collaboration |
| Enterprise | Complex organization/governance, SSO/audit, advanced ERP/integrations, portfolio/capacity, custom website/configuration and Dorzak-managed delivery |

The core commercial lifecycle is `INQUIRY → QUALIFIED → DRAFT_QUOTE → ERP_QUOTATION → ACCEPTED / DECLINED → ERP_ORDER_OR_ENGAGEMENT → INVOICED → PAID / CLOSED`, with cancellation/correction through the authoritative owner. The Today dashboard shows open inquiries/quotes, overdue actions/invoices, recent payments, Work risks and website/customer activity within the actor’s scope.

General Business avoids irrelevant vertical navigation. A later approved category/pack change adds specialized states without re-tenanting or rewriting existing records.

---

## 11. Website, storefront, and page-builder product

The website is the merchant’s public business, not a secondary brochure attached to the back office. Each paid category receives an appropriate transaction-ready website whose data and actions remain synchronized with Dorzak and ERPNext.

### 11.1 Builder architecture

- **WEB-001:** Pages are stored as a versioned, schema-validated tree of approved sections and content, not arbitrary HTML.
- **WEB-002:** Sections render through a Dorzak component registry with typed settings, responsive rules, accessibility metadata, localization, plan eligibility, and migration version.
- **WEB-003:** Merchant content is separated from operational data; product, service, booking, class, course, donation, clinic and order components use authorized live projections.
- **WEB-004:** Draft and preview-before-publish are safety baselines. Basic restore is Pro; complete version history, schedule and compare/restore are Business; approval/controlled rollout is Enterprise, exactly as the published PlanVersion and table below specify.
- **WEB-005:** Publishing validates broken links, missing translations, contrast, alt text, required legal content, plan entitlements, mobile layout, page weight, forms and transaction routes.
- **WEB-006:** One responsible merchant editor is expected per page, but optimistic versions protect against a second tab or delegated Dorzak editor overwriting work.
- **WEB-007:** Enterprise Dorzak-managed editing uses the same versioned builder, comments, approvals and audit as the merchant; it is never an untracked database change.
- **WEB-008:** Every merchant receives staging/preview separate from public production according to plan.
- **WEB-009:** Merchant-defined scripts, arbitrary iframe sources, unrestricted CSS and unsafe embeds are prohibited.

### 11.2 Builder progression

| Capability | Free Tools | Pro | Business | Enterprise |
|---|---|---|---|---|
| Merchant website | No | Yes | Yes | Yes |
| Hosting | Free-tool pages only | Dorzak subdomain | Dorzak subdomain | Custom domain and advanced domain controls |
| Theme | Free-tool account theme only | Curated vertical templates | Advanced theme presets and tokens | Expanded governed tokens and managed design |
| Content | Free downloads/articles/tools | Logo, hero, contact, about, catalog/service sections, policies, basic pages | Schema-based section builder, collections, promo blocks, forms, scheduled content, richer pages | Multi-site/brand governance, approval roles, custom section configuration and Dorzak assistance |
| Localization | EN/FR/AR free-tool content | EN/FR/AR merchant content | EN/FR/AR per-section content and workflow | Translation governance, review and managed localization |
| Commerce/vertical actions | None | Core plan/category transactions | Advanced workflows | Governed/custom/high-complexity workflows |
| Versioning | Not applicable | Draft/publish and basic restore | Version history, staging, schedule and restore | Approval workflow, branch/site themes, controlled rollout and managed launch |
| Dorzak attribution | On free tools | Retained | Configurable published policy | Optional removal when PlanVersion allows |

Accessibility, correct RTL, security, privacy, legal requirements, image optimization and data portability are never paid branding options.

### 11.3 Standard section registry

The launch registry includes:

- navigation, announcement, hero, outcome/proof strip, rich text, image/media, features, category cards, products, services, menus, team/providers, class/course timetable, campaigns/funds, appointments, pricing/packages, locations/map, opening hours, gallery, testimonial, case study, FAQ, forms, newsletter/consent, contact, policies, footer and plan-appropriate transaction widgets;
- vertical sections for restaurant menu/order mode, appointment booking, provider profile, healthcare service/provider, education program/course, gym class/membership, nonprofit campaign/giving and supplier RFQ/private-catalog entry;
- accessible fallback content for every animated or visual section.

### 11.4 Domain and publishing lifecycle

Enterprise custom domains require DNS ownership verification, reserved-domain checks, TLS issuance/renewal, routing, primary-domain selection, redirect rules, email/link implications, health checks, detachment and incident handling.

No plan may market custom domains before automated certificate, routing, renewal, rollback and cross-tenant host tests pass.

---

## 12. Free Tools growth hub

Free Tools is a scalable, free-forever acquisition and utility product. It requires a Dorzak account but does not create a merchant workspace, ERPNext site, storefront, operational records, or paid subscription.

### 12.1 Initial catalogue

- QR Studio for URLs, contact details, Wi-Fi, menus, events and approved structured uses;
- image background removal and basic image preparation;
- invoice, quotation, RFQ, purchase-order, receipt, delivery note, inventory count, business plan, project plan, checklist, appointment, class timetable, campaign budget, donation acknowledgement and other downloadable templates;
- articles, guides, calculators and checklists for Qatar/Tunisia business operations;
- AI-assisted drafting skills that operate on user-provided content with clear limits, privacy notice and no autonomous publication;
- vertical starter packs for each supported category.

Website templates are not part of Free Tools. “Templates” means business documents, operational templates and content resources.

### 12.2 Free-tool platform requirements

- **FREE-001:** Superadmin can add, localize, categorize, schedule, feature, archive and measure tools without a frontend deploy when the tool uses an approved tool type.
- **FREE-002:** Tools have stable SEO URLs, EN/FR/AR metadata, structured data, share previews, accessible inputs, mobile layouts, privacy classification and abuse limits.
- **FREE-003:** Saved history is opt-in and account-scoped; sensitive uploads follow purpose-specific retention and deletion.
- **FREE-004:** Background removal and AI processing never retain or train on user assets beyond the published policy.
- **FREE-005:** Free-tool activity can personalize Dorzak onboarding only with transparent consent; it cannot leak to a merchant account or external customer database.
- **FREE-006:** Upgrade prompts connect the completed free task to a relevant paid outcome without falsely implying that a merchant workspace already exists.

---

## 13. Corporate website and acquisition architecture

The corporate website should be an SEO-focused public deployment such as `www.dorzak.com`; the authenticated product should use an application host such as `app.dorzak.com`. Merchant domains/subdomains resolve separately through the public-site gateway.

### 13.1 Global navigation

- Product
- Solutions
- Clients
- Pricing
- Free Tools
- Resources
- English / French / Arabic
- Log in
- Get started / Start trial

### 13.2 Homepage content order

1. **Hero:** one clear outcome statement, primary CTA, secondary demo CTA, and an animated system graphic showing orders/services/operations converging into one controlled business stream.
2. **Verified trust strip:** approved client logos and evidence-backed platform/market proof only.
3. **Product bento:** Sell, Operate, Understand, Grow, Build, and Get Support.
4. **Category selector:** changes copy, imagery, proof and product walkthrough for each merchant profile without changing the core navigation.
5. **ERP-powered explanation:** communicates serious operational depth without exposing ERPNext interfaces or suggesting affiliation beyond truthful open-source use.
6. **Website-builder showcase:** mobile customer site and desktop merchant management, with category-specific examples.
7. **Our Clients preview:** approved logos, two stories, verified outcomes and link to the client library.
8. **Local readiness:** EN/FR/AR, Qatar/Tunisia, payment/delivery/provider facts, privacy/security and support claims that have evidence.
9. **Plan outcome comparison:** Free Tools, Pro, Business and Enterprise from the published capability manifest.
10. **Final CTA:** create account, start eligible trial or contact Enterprise sales.

### 13.3 Required public pages

- `/product`
- `/website-builder`
- `/commerce`
- `/appointments`
- `/operations`
- `/marketing`
- `/work-management`
- `/erp-core`
- `/solutions/<category>` for every supported category
- `/pricing`
- `/free-tools` and one page per tool
- `/clients` and one page per approved case study
- `/resources`, article/category pages and downloadable resources
- `/security`, `/privacy`, `/accessibility`, `/status`, `/support`, legal pages and localized equivalents
- `/login`, `/signup`, `/trial`, `/contact-sales` and recovery/verification journeys

### 13.4 Solution landing-page pattern

Each category page must contain:

1. category-specific outcome headline;
2. recognizable pains and current-workflow costs;
3. customer mobile journey demonstration;
4. merchant desktop workflow demonstration;
5. category modules and exact plan progression;
6. ERPNext-backed operational depth plus Dorzak-native category functions;
7. approved client proof for that category or an honest absence of proof;
8. integrations/country availability;
9. FAQ and objections;
10. plan-aware CTA and trial terms.

Content never promises an unavailable country, provider, category workflow or plan entitlement.

### 13.5 Pricing page

The pricing page is rendered from the same published manifest as billing and server enforcement. It includes:

- monthly/annual display where published;
- explicit currency and tax/fee context;
- one outcome card per plan;
- “best for” and upgrade justification;
- plan/category-aware comparison groups;
- limits, included usage, overage principles and add-ons;
- trial duration from server policy, including 30-day Qatar/Tunisia healthcare rules;
- no Enterprise location minimum language;
- plan selection, checkout/contact path and saved selection;
- accessible table equivalent for every infographic;
- FAQ covering ERPNext provisioning invisibility, data ownership, cancellation, downgrade, migration, support and billing.

### 13.6 Copy, proof and infographic standard

- The homepage states one concrete business outcome in the first viewport, identifies the intended merchant, and uses one dominant CTA plus one lower-commitment alternative.
- Category pages use customer-research language for pains, triggers, objections and desired outcomes; they do not repeat generic “all-in-one” claims.
- Every factual, savings, client, provider, country, security and comparison claim links internally to an evidence owner, source, verification date and expiry.
- French and Arabic are market-aware transcreations reviewed for meaning, tone and CTA clarity—not literal afterthought translations.
- Each plan infographic explains the workflow/value gained when moving upward and has an equivalent semantic heading/list/table; visuals never carry plan differences alone.
- Product graphics use real localized states and credible data, with privacy-safe fixtures and no invented client screen.
- Before launch, five-second message recall and 30-second plan-choice tests run with representative Qatar/Tunisia merchants in EN/FR/AR. Failed comprehension changes copy/visual hierarchy and reruns the test.
- SEO titles, descriptions, headings, internal links, structured data and social previews must agree with the published capability/country manifest and cannot index unavailable or sensitive workflows.

---

## 14. Our Clients proof platform

### 14.1 Public experience

The homepage includes a stable grid of approved client logos, any available verified metric/story cards, and a link to the client library. It must not use an auto-scrolling logo marquee.

The `/clients` page includes:

- evidence-backed headline totals;
- category, use-case and region filters;
- approved logo directory;
- featured and standard case studies;
- outcome cards with baseline, result, period and methodology;
- approved quotes with name, role and organization;
- CTA to start or contact sales.

The launch minimum is one permission-backed ClientProfile and one truthful approved placement. Metrics and stories render only when their individual evidence/consent records pass; the layout collapses cleanly without fixed-count language. If that minimum does not exist, the Clients navigation and module remain unpublished and the complete-launch gate remains blocked rather than showing a fake or empty claim. Fake logos, invented metrics, anonymous-logo combinations and unapproved quotes are prohibited.

### 14.2 Content model

- `ClientProfile`
- `BrandAsset` with light/dark variants, clear-space rules and alt text
- `ConsentGrant`
- `CaseStudy`
- `Quote`
- `OutcomeMetric`
- `LocaleContent`
- `PlacementSchedule`

### 14.3 Approval workflow

`DRAFT → INTERNAL_REVIEW → CLIENT_REVIEW → APPROVED → SCHEDULED → PUBLISHED → EXPIRED / REVOKED`

Each publishable item records:

- client legal/display name and authorized approver;
- exact asset/copy/metric approved;
- channels and placements;
- languages, territories, start, expiry and embargo;
- edit/crop/translation permission;
- revocation method and contact;
- signed/auditable evidence;
- metric source, formula, denominator, baseline, window, evidence owner and last verification date.

Client approval is separate from service terms and plan benefits. Revocation immediately unpublishes the item and triggers CDN/cache purge. EN/FR/AR rendered versions require approval; translations cannot improve a claim without renewed approval.

Release tests reject missing, expired, revoked or placement-incompatible consent. Expiry/revocation must remove every locale, origin response, CDN/cache object, social preview, structured-data claim and search-feed placement. A purge failure blocks further publication and raises a release/incident alert until no public copy is reachable.

### 14.4 Superadmin public-site controls

- nomination/self-submission without self-publication;
- image/SVG validation, focal point, clear-space and alt-text preview;
- desktop/mobile and EN/FR/AR/RTL preview;
- feature/order/category/use-case controls;
- SEO and social metadata;
- schedule/publish/unpublish;
- consent-expiry alerts and emergency kill switch;
- immutable approval/audit history and CDN purge status;
- no arbitrary public HTML, script or CSS.

---

## 15. Precision Commerce OS design system

Dorzak’s target style is **Precision Commerce OS**: a distinctive, instrument-grade, high-technology operating system rather than generic neon glassmorphism.

### 15.1 Visual direction

- Graphite/ink structural foundations and off-white operational surfaces.
- Dorzak red for decisive brand and primary actions.
- Restrained cyan/violet signal accents for live data, automation and system graphics.
- Data-aware bento layouts, crisp SVG iconography, credible product UI, merchant photography, maps and modular category illustrations.
- Subtle glass only in public heroes, overlays and special emphasis—not ordinary tables, forms or mobile scrolling screens.
- Hanken Grotesk plus the existing Arabic-compatible Araboto family at launch unless typography testing approves a fully language-equivalent replacement.
- Tabular figures for prices, counts, stock, time and finance.
- One icon family and stroke system; no emoji as structural UI icons.

### 15.2 Three token layers

1. **Dorzak core tokens:** logo, brand color, typography, spacing, elevation, radius, icons, semantic state, focus and accessibility.
2. **Experience tokens:** corporate display treatment, data-grid motifs, visual graphics, illustration, motion and marketing surfaces.
3. **Merchant theme tokens:** constrained accent, secondary, surface warmth, hero treatment, radius and approved font preset.

Category skins may change imagery, accent support, example data and section order; they may not create inconsistent navigation or bypass the common accessibility system.

### 15.3 Graphics system

Launch graphics must show the product working:

- POS, website, WhatsApp, booking and delivery activity converging into one operations stream;
- inventory or capacity moving from availability through transaction and replenishment;
- modular category scenes for retail, supplier, restaurant, appointments, beauty, healthcare, education, gym and nonprofit;
- real interface frames with credible localized data;
- verified Qatar/Tunisia payment, delivery, language and trust cues.

All visuals require a static semantic alternative. Decorative graphics are hidden from assistive technology; meaningful graphics have localized alt text or adjacent text explanation.

### 15.4 Motion system

Motion communicates causality, status, hierarchy and continuity. It does not decorate every card.

| Motion layer | Target duration | Examples |
|---|---:|---|
| Press/focus feedback | 80–120 ms | Button, icon and row response |
| Standard state change | 160–200 ms | Tab, filter, cart, status and inline confirmation |
| Panel/navigation | 180–260 ms | Drawer, sheet, route content and detail transition |
| Public emphasis | Up to 320 ms | Hero, proof card and section reveal |
| Ambient system graphic | 8–12 second loop | Data/order signal with no required information hidden in motion |

Rules:

- animate transform and opacity rather than layout properties;
- maximum two simultaneous motion groups per viewport;
- ambient motion pauses offscreen and when the tab is hidden;
- animation is interruptible and never blocks input;
- no scroll-jacking, forced parallax, autoplay video or auto-advancing testimonial carousel;
- `prefers-reduced-motion` removes pulses, parallax, fly-to-cart, stagger and loops; content appears immediately with at most a short crossfade;
- error rollback and state changes include text and status semantics, not motion alone.

### 15.5 Surface-specific behavior

- **Corporate website:** most expressive visual storytelling and one signature animated hero system.
- **Merchant desktop:** dense, calm, fast, keyboard-capable operational UI with restrained microinteractions.
- **Customer mobile:** merchant-first branding, 44px minimum targets, vertical scrolling, fast transaction feedback and no heavy dashboard motion.
- **Superadmin:** operational clarity, explicit danger states, audit visibility and no decorative effects competing with tenant health.

### 15.6 Design/performance budgets

The release repository contains a versioned lab profile and CI configuration. Mobile measurement uses a clean-cache 360×800 Android-class profile with four CPU cores, 4 GB RAM equivalent, 4× CPU slowdown, 1.6 Mbps down, 750 Kbps up and 150 ms round-trip latency. Desktop uses 1366×768, four cores/8 GB equivalent, 10 Mbps down and 40 ms RTT. CI records at least five cold and five warm runs per page/template and evaluates the median plus p75; profile or browser changes require an approved baseline update.

Budgets:

- LCP at or below 2.5 seconds;
- INP at or below 200 milliseconds;
- CLS at or below 0.10;
- corporate initial JavaScript at or below 180 KB gzip;
- merchant-customer initial JavaScript at or below 180 KB gzip;
- merchant desktop authenticated shell at or below 250 KB gzip, with no ordinary route adding more than 150 KB gzip;
- Superadmin authenticated shell at or below 220 KB gzip, with no ordinary route adding more than 160 KB gzip;
- animation-specific JavaScript at or below 40 KB gzip;
- above-fold hero media at or below 250 KB;
- initial image payload at or below 600 KB;
- initial public-page transfer at or below 1.2 MB;
- no decorative animation library in the merchant core bundle;
- route-level splitting, fixed media dimensions, responsive AVIF/WebP, sanitized SVG and below-fold lazy loading;
- no repeated task over 50 ms during ordinary interaction and 60fps target for supported motion.

Prelaunch synthetic evidence covers every corporate template, paid customer transaction shell and representative merchant/Superadmin route. After controlled enablement, real-user monitoring evaluates rolling seven-day p75 Core Web Vitals by surface, country, device class and locale; two consecutive 30-minute severe-threshold breaches or a material transaction-error increase pauses rollout and triggers rollback/incident review. A 24-hour representative-load soak with stable memory, queue age and error rate is required before the final switch.

### 15.7 Accessibility baseline

- WCAG 2.2 AA on every launch page, route, modal, empty/loading/error/permission/upgrade state and completed workflow in the public, merchant, customer and Superadmin surfaces;
- 4.5:1 normal-text contrast and 3:1 component/large-graphic contrast;
- visible keyboard focus, logical focus order and route-change focus management;
- keyboard alternatives for drag, resize and pointer-only interactions;
- 44×44 CSS pixel minimum customer-mobile targets and appropriate desktop spacing;
- 200% text scaling without lost function;
- semantic labels, headings, landmarks, errors and live regions;
- color is never the only status indicator;
- light/dark and RTL states tested independently;
- accessible text/table alternatives for charts, timelines and infographics.

The repository carries a versioned support matrix covering the latest two approved releases of Chrome, Edge, Firefox and Safari; current supported Android Chrome and iOS Safari; desktop widths 1280/1366/1440, mobile widths 360/390/430 and 200% zoom/text scaling; mouse, touch and keyboard-only input; Windows NVDA with Firefox/Chrome, macOS VoiceOver with Safari and iOS VoiceOver with Safari. Every release records keyboard, screen-reader, EN/FR/AR, RTL, reduced-motion and zoom evidence on each surface. Unsupported combinations are documented publicly rather than silently excluded from conformance claims.

---

## 16. Merchant and customer experience architecture

### 16.1 Merchant desktop information architecture

Navigation is generated from category, role, location scope and published capabilities. A typical organization receives:

- Home / Today
- Website
- Sell or Services
- Orders / Bookings / Classes / Contributions / Enrollments / Clinic, according to category
- Catalog / Programs / Services
- Customers / Parties
- Inventory / Resources
- Purchasing
- Marketing
- Work
- Finance
- Analytics
- Team
- Integrations
- Settings
- Billing and Plan
- Help / Dorzak Delivery Room

Unavailable modules explain plan/category requirements where discovery is useful; sensitive modules are omitted when even their existence should not be disclosed.

### 16.2 Customer mobile shell

Merchant public experiences prioritize:

- business identity and trusted domain;
- search/discovery;
- the category’s main transaction;
- account/status;
- contact/support;
- no more than five persistent mobile navigation destinations;
- merchant brand before Dorzak platform identity;
- state preservation through checkout/booking and reliable back navigation.

### 16.3 Onboarding

Paid onboarding gathers progressively:

1. owner identity and mobile verification;
2. organization legal/display name, country, language and timezone;
3. primary category and outcome goal;
4. selected plan/trial and payment state;
5. ERPNext provisioning progress;
6. first location/legal company and financial basics;
7. category-specific business setup;
8. website template/brand;
9. product/service/data import;
10. staff roles;
11. payment/delivery/messaging connectors;
12. preview, test transaction and launch checklist.

Onboarding resumes safely after interruption, shows useful progress, and never asks the merchant to complete ERPNext’s setup wizard.

---

## 17. Superadmin control plane

The Superadmin is a separate platform product, not a hidden collection of database actions.

### 17.1 Platform overview

The home dashboard must show:

- active/trialing/past-due/suspended organizations by plan, country and category;
- MRR/ARR, trials, conversions, churn, failed payments and upcoming renewals;
- ERPNext sites by lifecycle/health/version/backup/migration state;
- queue, webhook, integration, messaging, storage, certificate, domain and reconciliation incidents;
- organizations at risk because of provisioning, usage, payment, failed jobs, stale projection or support escalation;
- open delegated-access grants and active Dorzak interventions;
- release readiness and disabled public-launch state.

No metric links directly to sensitive content without a separate authorized action and reason.

### 17.2 Organization control

Superadmin can:

- search/filter organizations by public ID, legal/display name, owner, country, category, plan, state and ERP site health;
- view organization, locations, subscription, usage, integrations, domains, latest audit, support, ERP version and non-sensitive health;
- suspend/reactivate platform access with reason and documented customer/public behavior;
- retry or escalate provisioning and integration operations;
- initiate governed export/offboarding;
- open an authorized tenant inspection through the platform policy;
- create a delegated teammate grant for exact scope/time/purpose;
- revoke every session/grant in an emergency;
- never reveal raw credentials, health/clinical notes, customer message bodies, payment secrets or unnecessary personal data in fleet views.

### 17.3 Commercial control

- plan families, versions, prices, currencies, billing cycles, trials, limits, allowances, add-ons, dependencies and availability;
- publication impact, approvals, schedules, subscriber migration and rollback;
- subscriptions, invoices, credits, payment events, dunning and corrections;
- usage adjustments with reason, dual approval and audit;
- capability enforcement inventory identifying server, worker and public-manifest enforcement points;
- plan consolidation without data deletion.

### 17.4 ERPNext fleet control

- site lifecycle, health, release/channel, contract version, database/storage/queue class and last successful backup;
- migration waves, canary results, block/retry/rollback;
- per-site reconciliation and projection lag;
- integration-principal rotation;
- no one-click destructive termination without typed confirmation, impact report, backup/export evidence, retention check and second approver.

### 17.5 Delegated intervention

Superadmin has full platform authority through explicit audited policies. Other Dorzak teammates have no ambient access.

A grant contains:

- organization, optional location/project/service request;
- grantee, approver and role;
- reason/purpose and support ticket;
- allowed actions and data classes;
- start and expiry;
- read-only default and separate write approval;
- first/last use, revoke and resulting audit events.

The merchant desktop displays an intervention banner while a Dorzak teammate is actively operating. Customer websites never reveal Dorzak administration or other merchants.

### 17.6 Public-site and client-proof control

The platform includes corporate pages, navigation, localized content, SEO, Free Tools, client proof, consent evidence, publishing schedules, previews, redirects and emergency unpublish/CDN purge.

### 17.7 Support and incident control

- support cases, priority/SLA according to published plan;
- merchant-visible status and internal incident state;
- safe diagnostic snapshot without secret/content leakage;
- links to affected sites, jobs, webhooks and release changes;
- escalation, owner, communication timeline, workaround, resolution and post-incident evidence.

---

## 18. Dorzak subscriptions and payment processing

Dorzak subscription billing is separate from each merchant’s customer payments. Both use licensed provider abstractions, but they have distinct accounts, ledgers, webhooks, permissions and reconciliation.

### 18.1 Subscription lifecycle

`CREATED → TRIALING → CHECKOUT_PENDING → ACTIVE → PAST_DUE → GRACE → SUSPENDED → CANCELLED / EXPIRED`

Recovery can transition `PAST_DUE` or `GRACE` back to `ACTIVE`. Every transition records provider evidence, idempotency key, actor/source and effective time.

### 18.2 Required billing journeys

- monthly/annual checkout using a Qatar-capable provider and approved Tunisia path;
- trial without accidental immediate charge when policy says so;
- payment-method setup/update through hosted/tokenized provider UI;
- signed/replay-protected webhook activation;
- renewals, invoice/receipt creation and download;
- failed payment, retries, grace, merchant notification, recovery and suspension;
- upgrade with proration/credit policy and confirmed payment before entitlement;
- downgrade/consolidation preview and renewal-effective change;
- cancellation now/end-of-cycle according to published policy;
- refunds, corrections, disputes and manual finance review;
- plan/add-on/usage line-item reconciliation;
- provider outage and duplicated/out-of-order webhook handling.

### 18.3 Payment invariants

- no raw PAN/card data stored or proxied by Dorzak;
- hosted/tokenized surfaces only;
- webhook signature, timestamp, event ID and replay verification before state change;
- money stored in integer minor units with ISO currency;
- subscription currency never inherits the merchant’s selling currency;
- provider payment facts, Dorzak invoice, entitlement transition and accounting entry are separate reconcilable facts;
- a failed/forged/duplicate webhook cannot activate or duplicate a subscription;
- secrets are encrypted, masked, rotated and absent from logs/client responses;
- merchant customer payments settle/post to the merchant’s isolated ERPNext site, not Dorzak’s subscription ledger.

### 18.4 Provider selection gate

Implementation must evaluate licensed regional providers for:

- recurring QAR and TND support;
- merchant/platform onboarding model;
- tokenization/mandates, hosted checkout and 3DS;
- invoices, refunds, disputes, settlement and webhook quality;
- API/test environment, uptime, data residency/transfer and support;
- fees and commercial sustainability.

The selected adapter is replaceable and no business domain may depend on proprietary provider fields.

---

## 19. Shared deep modules

Each module has one stable, testable boundary and is reused across verticals.

### 19.1 Tenant and site router

Resolves authenticated organization, authorized location, plan version, ERPNext site, country pack and actor. It fails closed and never trusts a body/query tenant identifier.

### 19.2 Identity, party, role and consent

Owns merchant/customer identity boundaries, OTP, role records, contact verification, guardians where approved, consent evidence, suppression and ERPNext mappings.

### 19.3 Capability and commercial policy

Resolves immutable PlanVersion entitlements, limits, dependencies, usage, trial and add-on state. It produces the public manifest and the same server enforcement decisions.

### 19.4 Provider-neutral payment kernel

Owns payment intents, hosted-session references, signed webhook receipts, refunds, disputes, mandates, recurring events and reconciliation. Membership, donation, order and Dorzak subscription domains retain separate business ledgers.

### 19.5 Scheduling kernel

Owns recurrence, timezones, resources, availability, capacity, holds, collision prevention, waitlists, attendance/check-in and cancellation windows. Appointment, class, timetable and volunteer-shift domains expose separate aggregates and policies.

### 19.6 Workflow, approval and version kernel

Owns configurable states/transitions, permissions, version conflicts, approvals, comments, files and audit for builder publishing, purchasing, Work, applications, clinical processes and managed delivery.

### 19.7 ERP command and projection gateway

Owns constrained `dorzak_core` commands, idempotency, actor envelope, ERPNext document mapping, outbox, retry/dead letter, webhook receipt, projection rebuild, reconciliation and contract compatibility.

### 19.8 Media and document safety

Owns private uploads, MIME/size/checksum validation, malware scan, category-specific DLP, image transforms, signed URLs, retention, deletion, export and access logging.

### 19.9 Communications

Owns template versions, locale, channel/provider routing, consent/suppression, transactional/marketing purpose, quotas, frequency caps, quiet hours, delivery events, retries and attribution.

### 19.10 Audit and observability

Owns immutable actor/tenant/action/reason/correlation/version events, provider receipts, security events, operational metrics, tracing, redaction and retention.

---

## 20. Work management and Dorzak-managed delivery

The approved Work product is retained with the following plan ladder:

- **Pro:** projects, task lists, checklists, dates, one accountable owner plus followers, reminders, recurring checklists, comments, board/calendar and Dorzak starter templates.
- **Business:** collaboration, files, approvals, interactive Gantt, milestones, dependencies, saved views, merchant templates and workload warnings.
- **Enterprise:** portfolios, baselines, critical path, capacity/leveling suggestions, scenarios, advanced audit/export, governed workflows and Dorzak-managed delivery rooms.

Frappe Gantt is a pinned, internally hardened presentation renderer only. ERPNext Projects/Tasks are the canonical core records for every paid Work project/task. Dorzak retains plan policy, customer publication, managed-delivery grants, safe projections and advanced extension records keyed to immutable ERP identities; an extension cannot duplicate an editable ERP core field.

Work does not replace live appointment, class, classroom, kitchen, volunteer or clinical scheduling. It coordinates operational initiatives such as openings, campaigns, accreditation, implementation, website delivery and remediation.

Dorzak-managed Enterprise service flow:

`REQUESTED → QUALIFIED → SCOPED → ESTIMATED → APPROVED → IN_PROGRESS → CLIENT_REVIEW → ACCEPTED → CLOSED`

Every change request, estimate, deliverable, approval, file, comment, actor and intervention is visible and audited. Enterprise customers may request Dorzak to create or modify their website or configuration; Dorzak does not hand off the product and disappear.

---

## 21. Marketing, CRM, loyalty, and communications

### 21.1 Launch-native scope

- customer consent capture and preference management;
- customer 360 projection and explicit role separation;
- segments with explainable criteria;
- coupons/promotions, loyalty, wallet/store credit, gift cards, referrals and verified reviews where relevant;
- email, platform-governed SMS and WhatsApp channels;
- approved WhatsApp template synchronization and parameterized composition;
- campaigns, welcome/win-back and category-appropriate lifecycle automations;
- frequency caps, quiet hours, suppression, retries, delivery results and real send counts;
- order/booking/membership/giving attribution from Dorzak and ERPNext evidence;
- plan quotas and usage meters;
- customer mobile redemption and preference journeys.

Marketing plans must move from Store tenancy to Organization/site-aware architecture and define Business packaging. A campaign cannot report success when a channel is unconfigured or a provider failed.

### 21.2 Mautic boundary

- Initial launch keeps merchant marketing Dorzak-native.
- Dorzak may use one isolated Mautic environment internally for its own leads, free-tool accounts, trials and win-back.
- A later Advanced Automation product may use one isolated Mautic environment per merchant organization behind Dorzak APIs.
- Mautic UI, credentials, cookies and database never reach merchants.
- Mautic GPL source is not copied into Dorzak.
- Mautic does not replace website builder, loyalty, wallet, referrals, reviews, WhatsApp governance, plan enforcement or ERPNext-linked revenue attribution.

### 21.3 Healthcare, education, gym and nonprofit marketing restrictions

- health/clinical content is not sent to ordinary marketing automation;
- education involving minors uses guardian and institutional consent rules and avoids behavioral profiling of children;
- gym health/biometric data is not collected for marketing;
- donor, volunteer, applicant and beneficiary communications remain purpose-separated;
- transactional reminders are distinguishable from marketing and fundraising consent.

---

## 22. Delivery, parcel shipping, and fulfillment

Local delivery, merchant pickup and category fulfillment remain launch scope where providers/operations are approved.

If parcel shipping is marketed, Dorzak must build a native organization-scoped domain with:

- carrier accounts, package templates, server-authoritative rate quotes, shipments/parcels, private labels and append-only events;
- integer minor-unit/currency handling;
- idempotent booking/cancellation, signed webhooks, bounded provider-isolated polling, exceptions and dead-letter handling;
- strict label-host allowlists, private storage, MIME/size/checksum validation;
- Qatar/Tunisia address/phone/currency/unsupported-provider tests;
- Business one-carrier operations and Enterprise multi-carrier/routing/analytics/Superadmin assistance as published.

The supplied ERPNext Shipping repository is a provider/payload reference only. It is not installed or copied as-is, and it does not control Work/Gantt.

---

## 23. Source-reuse register

| Source | Launch decision | Reuse boundary | Required before use |
|---|---|---|---|
| Existing Dorzak `/Users/barsha/Downloads/web` / current repo | Primary product starting point; closest to Pro commerce | Preserve/rework current React/Laravel features and migrate authoritative commerce to ERPNext | Baseline tests, organization migration, data migration/reconciliation and current-state gap tests |
| ERPNext + Frappe Docker | Core for every paid merchant | Pinned stable image, one site/database per organization, `dorzak_core`, constrained API, ERP documents | Version selection, license/trademark review, provisioning, fleet operations, contract and migration tests |
| Website builder `/builder` | Candidate builder acceleration | Audit and adapt section schema/editor/runtime concepts; Dorzak owns components, publishing, plans, localization and security | Dedicated source/license/security/architecture audit |
| Frappe UI | Candidate component/reference library | Use only audited compatible components/patterns behind Dorzak tokens; no Frappe visual identity | License/version/a11y/React compatibility audit |
| CRM | Candidate relationship/workflow reference | Map useful CRM workflows into Party/CRM domains or integrate server-to-server | Source audit; no shared database or exposed foreign UI |
| Helpdesk | Candidate support engine/integration | Support cases, SLAs and Dorzak-managed service reference or isolated adapter | Source audit, tenant model, privacy and identity decision |
| HRMS | Candidate staff/availability source | Employee/leave/availability projections and Enterprise integration | Source audit and role/tenant isolation |
| Insights | Candidate analytics engine | Governed Enterprise/internal analytics against approved projections | Source audit, tenant row security, redaction and freshness |
| Education | Candidate education-domain acceleration | Admissions/course/timetable/attendance/fees functions only after gap and license audit | Full education source audit and country/minor privacy design |
| Payments and `nextjs-subscription-payments` | Provider/UI reference | Hosted checkout, subscription state and webhook patterns where compatible | Source/license/security/provider audit; no unverified gateway code copied |
| Frappe Gantt | Approved renderer reference | Pinned internal hardened fork, presentation only | MIT notice, security/a11y/RTL/performance patches and tests |
| ERPNext Shipping | Reference only | Provider vocabulary/mappings and safe concepts | Separate shipping PRD and connector implementation |
| Mautic | Internal launch marketing; merchant Advanced Automation later | Isolated service behind Dorzak APIs; no UI/database sharing | Stable version, GPL boundary, provisioning, consent, deliverability and isolation tests |

Source repositories are never edited. “Candidate” means no implementation authorization until the named audit is approved.

---

## 24. Localization and country packs

### 24.1 Language requirements

- Every public, merchant, customer, Superadmin, validation, email/SMS/WhatsApp, invoice, receipt, export, audit label and empty/error state has typed English, French and Arabic keys.
- No DOM text mutation is accepted as the target localization architecture.
- Arabic uses a true RTL shell; timelines/time axes may remain chronologically LTR with mirrored controls and clear testing.
- Locale-aware currency, number, phone, address, time, date and plural formats are required.
- Translation completeness and forbidden-fallback checks block release.
- Merchant-authored content supports per-locale variants and explicit fallback controlled by the merchant.

### 24.2 Country packs

Qatar defaults:

- `Asia/Qatar`;
- Friday/Saturday business weekend where applicable;
- QAR;
- `+974` phone rules;
- approved Qatar provider, legal, tax/invoice, delivery and healthcare policies.

Tunisia defaults:

- `Africa/Tunis`;
- Saturday/Sunday business weekend where applicable;
- TND;
- `+216` phone rules;
- approved Tunisia provider, legal, tax/invoice, delivery and healthcare policies.

Country packs are versioned configuration and policy, not scattered conditionals.

### 24.3 Healthcare compliance gates

Qatar’s Personal Data Privacy Protection framework and Tunisia’s personal-data and health-data framework require dedicated legal/privacy review. The product must provide configurable consent/information, processing records, access control, retention/deletion, incident handling, transfer controls, audit and responsible-contact information. These requirements are verified with local counsel and designated healthcare/privacy owners before the healthcare release switch is enabled.

---

## 25. Security, privacy, and trust requirements

### 25.1 Authentication and sessions

- MFA for Superadmin and merchant owners; configurable stronger requirements for Enterprise and sensitive healthcare/nonprofit roles;
- short-lived access, refresh/session revocation, device/session view and recent-authentication checks;
- rate limits and abuse controls by actor, organization, IP, device and route risk;
- separate platform, merchant staff and customer guards;
- SSO only through organization-approved Enterprise configuration.

### 25.2 Authorization

Effective access is the intersection of identity, organization role, location/department scope, object membership, plan entitlement, data classification, delegated grant and requested action.

React visibility, ERPNext UI permissions, builder controls and Gantt `readonly` are not sufficient authorization. Dorzak and `dorzak_core` enforce sensitive actions server-side.

### 25.3 Data protection

- encryption in transit and at rest, with per-tenant secret boundaries;
- encrypted/rotatable provider credentials and masked APIs;
- private files and signed expiring URLs;
- malware and category-specific DLP scanning before persistence/indexing/notification where required;
- minimization, purpose, consent, retention, export, correction, deletion and legal hold;
- no production secrets/PII/health content in logs, traces, fixtures, screenshots or analytics;
- AI providers cannot train cross-customer models on merchant/customer data without explicit contractual approval.

### 25.4 Financial and inventory trust

- transactional ERPNext writes and idempotent external commands;
- integer minor-unit money at integration/payment boundaries;
- immutable historical snapshots and corrections rather than destructive rewrites;
- traceable cost, tax, fee, discount, refund, settlement and stock evidence;
- daily reconciliation and visible exceptions;
- no client-calculated authoritative totals.

### 25.5 Audit

Sensitive audit contains actor, tenant, site, impersonation/grant context, device/session, entity/action, before/after or event payload, reason/approval, request/version, timestamp and correlation ID. Redaction policies prevent the audit itself from becoming a secret/health-data leak.

---

## 26. Reliability, performance, and operability

### 26.1 Availability and recovery objectives

- Initial paid-platform availability target: 99.9% monthly, excluding announced maintenance.
- ERPNext/site failure is isolated per organization wherever infrastructure permits.
- Recovery Point Objective target: 15 minutes for launch-critical operational data.
- Recovery Time Objective target: 4 hours, with plan/service-specific improvements allowed.
- Backups are encrypted, monitored, retention-controlled and restored in recurring rehearsals.
- A restore is not “tested” until Dorzak routing, `dorzak_core`, files, ERPNext documents, projections and credentials pass a post-restore journey.

Recovery uses one coordinated epoch for the merchant, not independent “latest backups.” Operations must quiesce writes, choose and record a common recovery point for ERPNext plus Dorzak-owned identities/mappings/intents/outbox/webhook receipts, restore both sides, rotate or validate credentials, reset event cursors without skipping/duplicating, replay provider receipts idempotently, rebuild projections and reconcile source-ID/document mapping, payments, stock and trial balance. Writes reopen only after the epoch invariants pass and an authorized operator signs the evidence.

### 26.2 API and UX performance

- ordinary operational API p95 below 750 ms excluding external providers;
- cached/aggregated dashboard p95 below 2 seconds;
- location stock/projection update visible within 5 seconds online;
- analytics freshness within 15 minutes unless labelled real-time;
- POS search remains responsive with at least 10,000 products per location;
- public performance budgets in Section 15.6;
- mobile customer journeys tested on representative low/mid-tier devices and constrained networks;
- large lists use server pagination and UI virtualization where appropriate;
- all external-provider waits have deadlines, retry policy and visible recovery state.

### 26.3 Queue and integration operation

- outbox dispatch only after local commit;
- idempotent jobs and webhook receipts;
- per-provider/per-site concurrency and rate controls;
- exponential backoff, dead-letter, manual replay and reconciliation;
- no broad exception swallowing or log-and-report-success behavior;
- every failed external action has owner, merchant impact, safe error code and retry path;
- secrets and third-party response bodies are redacted.

### 26.4 Observability

Platform telemetry includes:

- tenant/site-aware request and command correlation;
- ERPNext command latency/error/contract mismatch;
- projection lag and reconciliation drift;
- queue depth/age/dead jobs;
- payment/webhook/dunning health;
- domain/certificate/storage/backup/media-scanner/OTP/messaging/provider health;
- browser web vitals, route errors and localized journey failures;
- security signals and cross-tenant probe alerts;
- release/version/canary correlation.

Fleet dashboards use non-sensitive identifiers and aggregates. Opening sensitive evidence requires explicit authorization.

---

## 27. Analytics, value proof, and product instrumentation

### 27.1 Platform success metrics

- visitor → free account → qualified paid trial → activated merchant → paid conversion;
- trial activation by plan/category/country;
- Pro → Business and Business → Enterprise upgrade;
- MRR/ARR, gross margin, payment recovery, churn and retention;
- ERPNext provisioning time/success and time to first useful outcome;
- plan-feature adoption and outcome completion;
- support load/response against the published promise;
- zero entitlement mismatch and cross-tenant incidents.

### 27.2 Merchant value ledger

Where evidence exists, the billing/plan page may show:

- attributed or recovered revenue;
- payment failures recovered;
- stock risks, POs, transfers and exceptions actioned;
- campaign conversions;
- utilization/no-show improvements;
- renewal/member/donor recovery;
- delivery/reconciliation improvements;
- estimated hours saved, clearly labelled as estimated.

Verified, attributed and estimated values are separate. Every metric has a definition, source, freshness, confidence and drill-through. Dorzak never presents a client-side estimate as authoritative profit.

### 27.3 Required event families

- public navigation, category and pricing comparison;
- free-tool discovery/use/save/share/account conversion;
- signup, verification, trial, plan checkout and billing lifecycle;
- ERPNext provisioning and onboarding milestones;
- website draft/preview/publish/domain events;
- category-specific transaction and activation events;
- plan-gate/upgrade/downgrade previews;
- campaigns/messages/attribution;
- Work and managed-delivery milestones;
- delegated access and support outcomes;
- consent, privacy and security operations;
- release health and error recovery.

Events include plan version, category, country, role and organization pseudonymous identifier where lawful. They exclude message bodies, clinical content, beneficiary content, secrets and raw payment details.

---

## 28. Current-state gap assessment

The current Dorzak application is closest to an incomplete Pro commerce product. It has meaningful Laravel/React foundations, store-scoped commerce, public storefront, marketing concepts, plan gating and platform-admin beginnings. It is not yet the approved complete launch.

### 28.1 What exists and should be preserved or migrated

- React merchant application and component/token foundation;
- Laravel authentication, store context, plan-gate patterns and platform routes;
- products/variants/categories/stock ledger/order/customer/receipt foundations;
- public bilingual storefront/order concepts;
- settings, delivery boundary, loyalty/coupons/gift cards/referrals/segments/campaigns/recurring orders/reviews foundations;
- existing Superadmin plans/stores/subscriptions concepts;
- approved Work/Gantt design and detailed draft implementation tracks;
- audited Mautic, ERPNext Shipping and Frappe Gantt decisions.

### 28.2 Launch-critical gaps

| Program | Material gap |
|---|---|
| Canonical architecture | Current Store/Laravel commerce is authoritative; approved target is Organization plus isolated ERPNext core |
| Plans | Current code seeds Free/Pro/Enterprise; no Business, immutable commercial versions, complete dependencies or shared public manifest |
| Payments | Fake/manual subscription gateway; no selected production provider, complete webhook/dunning/invoice/refund lifecycle |
| ERPNext SaaS | No per-organization provisioning, `dorzak_core`, site router, fleet health, migration or reconciliation |
| Free Tools | No scalable account-based free utility/content platform matching the approved boundary |
| Corporate site | No complete corporate homepage, solutions, pricing, clients, resources or acquisition journeys |
| Builder | Existing storefront settings are not the required schema-based Pro/Business/Enterprise website builder |
| Our Clients | No evidence/consent CMS, client library or verified case-study workflow |
| Verticals | No complete restaurant, appointment/beauty, healthcare, education, gym or nonprofit operating products |
| Customer identity | No complete merchant-scoped auto-account/OTP/party-role kernel for all verticals |
| Marketing | Older plans are Store-scoped, Laravel-12-oriented, lack Business and contain incomplete placeholders |
| Superadmin | No complete fleet/provisioning/health/content/grant/release control plane |
| Localization | Current bridge is primarily EN/AR; no complete typed EN/FR/AR architecture and release checks |
| Quality | Backend baseline has 443 tests with two known expectation failures; frontend lacks the planned unit-test toolchain; browser coverage is incomplete |
| Performance | Current main frontend bundle is approximately 778.88 kB and raises a chunk warning; route/bundle budgets are not enforced |

### 28.3 Baseline stabilization before implementation

1. Preserve the existing `MediaUrl.php` user change and establish its intended contract.
2. Resolve the media URL and QAR/USD demo expectation discrepancies in a dedicated stabilization change.
3. Add/freeze frontend unit-test infrastructure before frontend feature work.
4. Record the current data model and migration/reconciliation rehearsal.
5. Create a clean isolated worktree from an approved documentation/baseline commit.
6. Never begin foundation implementation inside the current dirty worktree.

---

## 29. Implementation decisions and program decomposition

This PRD is intentionally a whole-product contract, not a single executable implementation plan. Each program receives its own approved design, implementation plan, traceability matrix and release evidence.

### 29.1 Program map

1. **P00 — Baseline stabilization and source-of-truth cleanup**
2. **P01 — Organization, identity, party, consent and tenant migration**
3. **P02 — ERPNext platform core, `dorzak_core`, provisioning and fleet operations**
4. **P03 — Immutable plans, Superadmin pricing, Dorzak subscriptions and regional payment lifecycle**
5. **P04 — ERPNext commerce migration, projections, offline journal and reconciliation**
6. **P05 — Corporate website, Free Tools, signup, pricing and Our Clients CMS**
7. **P06 — Website/storefront builder and custom-domain platform**
8. **P07 — Retail, supplier/B2B and shared commerce depth**
9. **P08 — Restaurant/F&B operating product**
10. **P09 — Scheduling kernel, appointments, coiffeur and beauty**
11. **P10 — Healthcare Qatar/Tunisia release pack**
12. **P11 — Education/school operating product**
13. **P12 — Gym/fitness operating product**
14. **P13 — Nonprofit operating product**
15. **P14 — Marketing/CRM/communications and attribution rewrite**
16. **P15 — Work/Gantt, Enterprise planning and Dorzak-managed delivery, revised for ERPNext core**
17. **P16 — Delivery/shipping/fulfillment where provider scope is approved**
18. **P17 — Complete Superadmin control plane**
19. **P18 — EN/FR/AR, country packs, security, privacy, accessibility and performance qualification**
20. **P19 — Global integration, migration, operational rehearsal and one-complete-launch release**

### 29.2 Dependency graph

```text
P00 Baseline
  -> P01 Organization / identity / party
       -> P02 ERPNext platform core
       -> P03 Plans / billing / subscriptions

P02 + P03 -> P04 Commerce migration and reconciliation
P03       -> P05 Corporate / Free Tools / pricing / clients
P01 + P03 -> P06 Website builder
P04 + P06 -> P07 Shared commerce
P04 + P06 -> P08 Restaurant
P01 + P03 -> P09 Scheduling kernel + appointments/beauty

P02 + P04 + P06 + P09 -> P10 Healthcare
P02 + P04 + P06 + P09 -> P11 Education
P02 + P04 + P06 + P09 -> P12 Gym
P02 + P04 + P06 + P09 -> P13 Nonprofit

P04 + P06 -> P14 Marketing / CRM
P02 + P04 -> P15 Work / Gantt / managed delivery
P04       -> P16 Delivery / shipping
P02 + P03 -> P17 Superadmin completion

P05 + P06 + P07 + P08 + P09 + P10 + P11 + P12 + P13 + P14 + P15 + P16 + P17
  -> P18 Cross-cutting qualification
       -> P19 Integrated rehearsal and single release
```

P05 public visual design may begin after P03 freezes the manifest contract. P09’s shared scheduling kernel is a hard prerequisite for healthcare, education, gym and nonprofit scheduling. P18 is a fan-in gate from every required launch program, not merely Superadmin; P19 cannot begin while any P05–P17 program remains incomplete. Controlled parallel work starts only after each listed interface dependency is frozen.

### 29.3 First implementation slice

After this written PRD is approved and the required program designs/plans exist, the safest first code slice is additive Organization → Location migration only:

- create Organization and organization membership;
- add nullable Organization relation to existing stores;
- backfill one organization per existing store;
- synchronize registration, invitations and staff role changes;
- preserve existing `store_id`, subscriptions and commerce behavior;
- add an idempotent read-only migration audit;
- prove Organization/Auth/Staff/Onboarding behavior with TDD.

It must not include homepage, pricing UI, ERPNext provisioning, Work UI or immutable plan versions in the same commit.

---

## 30. Testing decisions and strategy

Tests verify externally observable behavior and cross-system invariants rather than private implementation details.

### 30.1 Required test layers

- pure domain/unit tests for money, plans, dependencies, schedules, capacity, permissions, state machines and mappings;
- Laravel/Frappe service and contract tests;
- database migration, idempotency and rollback tests;
- API feature tests for tenant, role, plan, stale version and error contracts;
- ERPNext site provisioning and `dorzak_core` integration tests against pinned images;
- webhook/provider contract tests with signed/replay/out-of-order/duplicate fixtures;
- projection/reconciliation and failure-recovery tests;
- React component and integration tests;
- Playwright journeys on desktop and representative mobile viewports;
- EN/FR/AR, LTR/RTL and missing-key tests;
- accessibility automation plus keyboard/screen-reader/manual review;
- performance, bundle, load, queue, large-data and soak tests;
- security, cross-tenant, IDOR, credential, file, CSP, SSRF, injection and abuse tests;
- backup, restore, migration-wave and rollback rehearsals;
- chaos/failure tests for ERPNext, payment, OTP, messaging, storage, scanner and network degradation.

### 30.2 Mandatory tenant matrix

Every tenant-owned API/domain tests:

- correct organization and authorized location;
- another organization’s public and internal IDs;
- missing context;
- inactive membership;
- suspended organization;
- Superadmin with reason;
- delegated teammate active/expired/revoked grant;
- customer role versus merchant staff role;
- recycled/shared mobile number, explicit same-merchant link proof, dependant/guardian profile selection, role-link revocation and cross-merchant same-number isolation;
- plan denial and over-limit behavior;
- ERPNext site-route mismatch injection.

### 30.3 Mandatory commercial matrix

- Free Tools has no merchant or ERPNext access;
- Pro/Business/Enterprise receive only the published version;
- draft/scheduled/retired versions cannot leak into public sale;
- 14-day default and Qatar/Tunisia healthcare 30-day policy;
- upgrade after confirmed payment;
- downgrade at renewal and safe over-limit read/export;
- grandfather/migrate/consolidate/rollback;
- public pricing equals server/worker/ERPNext enforcement;
- Enterprise never requires a minimum location count.

### 30.4 Vertical and launch traceability matrix

The release matrix contains every sellable `category × plan family × country availability × locale/direction` combination. Every row links public claims and requirement IDs to a full mobile-customer happy path, desktop-merchant happy path, role/limit denial, upgrade reason, active-data downgrade, suspension/public behavior, worker/automation behavior, ERP posting/reversal/reconciliation, provider/ERP failure recovery, accessibility/device evidence and an accountable SME reviewer. Sensitive packs run separately for Qatar and Tunisia. One representative fixture is not a substitute for this matrix.

Examples:

- Retail: variant sale, stock, return and purchase receipt.
- Supplier: private price/quote/partial payment/receivable.
- Restaurant: modifier order, kitchen routing, approved allergen/cross-contact version and fallback display, stock consequence and refund.
- Appointment/beauty: concurrent slot hold, deposit, waitlist, check-in and no-show; beauty additionally proves purpose consent/revocation, unauthorized media denial, DLP handling and retention deletion.
- Healthcare baseline: opaque/verified patient, appointment, minimum-necessary access, administrative intake, attendance/service, protected document, billing, consent/access log and prohibited marketing leakage. Any advertised Clinical Pack adds patient-match, signature/amendment, medication/allergy, prescription/order/result, cancellation, break-glass and emergency-safety fixtures.
- Education: institutional/guardian proof and revocation, admission, schedule collision, attendance correction/approval, assignment submission, assessment moderation, result publish/correct/appeal, fee and learner/guardian visibility.
- Gym: concurrent capacity, deterministic waitlist, QR replay prevention, membership/payment/entitlement state, freeze/grace/cancel, refund/chargeback/reversal and utilization.
- Nonprofit: idempotent one-time/recurring contribution states, designation, acknowledgement, anonymity, refund/chargeback/ERP reversal, volunteer scope, approved non-sensitive intake, dual-control disbursement and beneficiary compartment.
- General Business: inquiry, RFQ/draft, canonical ERP Quotation, acceptance/order or engagement, invoice/payment, customer status, Work and correction/close flow.

### 30.5 Defect gate

- zero open Severity 1: security breach, cross-tenant exposure, irreversible data loss or complete outage;
- zero open Severity 2: core paid journey unavailable, incorrect billing/authorization, materially incorrect financial/stock/schedule/clinical state;
- Severity 3 requires owner, workaround, evidence and Product/Engineering acceptance;
- no quarantined/ignored launch-critical test without release-board approval and replacement evidence.

---

## 31. Launch acceptance and sign-off

### 31.1 Automated evidence

- all unit, backend, Frappe, frontend, E2E, contract, security, accessibility, performance and migration suites pass;
- zero missing EN/FR/AR keys and no unauthorized fallback;
- capability manifest, pricing page and enforcement parity pass;
- every row in the category × plan × country × locale traceability matrix passes;
- ERPNext provisioning/backup/restore/upgrade/rollback evidence is current;
- release BOM, SBOM/provenance, compatibility, security-patch and licensing/trademark evidence is approved;
- payment lifecycle simulation passes;
- client-proof consent expiry/revocation and complete origin/CDN/structured-data purge tests pass;
- queues, webhooks, integrations, certificates, storage, scanners and reconciliation are healthy;
- release switch defaults off and can only enable with complete evidence.

### 31.2 Human sign-off

- product owner;
- engineering lead;
- ERPNext/Frappe platform owner;
- security/privacy owner;
- finance/billing owner;
- operations/support owner;
- EN, FR and AR reviewers;
- Qatar and Tunisia country reviewers;
- retail/supplier, restaurant, appointments/beauty/coiffeur, healthcare, education, gym, nonprofit and General Business SMEs;
- accessibility reviewer;
- legal/compliance reviewers for healthcare/clinical scope, education/minors, nonprofit fundraising/beneficiary scope, payments, privacy, licenses and trademarks.

### 31.3 Operational readiness

- staffed support promise and escalation roster;
- incident, breach, payment outage, ERPNext outage, restore, certificate, provider, compromised account and delegated-access runbooks;
- status communication and merchant/customer message templates in all languages;
- monitored backups and restore drill;
- capacity forecast for per-merchant ERPNext sites;
- on-call dashboards and alerts;
- approved launch communications and client proof;
- go/no-go record and rollback authority.

---

## 32. Extensive user stories

### 32.1 Visitor and free-tool user

1. As a visitor, I want to understand Dorzak’s outcome in one screen, so that I can decide whether it fits my business.
2. As a visitor, I want to select my business category, so that examples and workflows become relevant to me.
3. As an Arabic visitor, I want a complete RTL experience, so that I can evaluate Dorzak naturally.
4. As a French visitor, I want complete French content, so that I do not need to interpret English product terms.
5. As a visitor, I want to compare plans by outcomes and limits, so that I understand why an upgrade costs more.
6. As a visitor, I want plan prices in an explicit currency, so that I am not surprised at checkout.
7. As a healthcare visitor in Qatar or Tunisia, I want the 30-day trial displayed accurately, so that I can evaluate clinic setup responsibly.
8. As a visitor, I want to see real approved clients and measurable stories, so that I can trust the product.
9. As a free user, I want an account for my saved tools, so that I can return to my work.
10. As a free user, I want to create QR codes, remove image backgrounds and use business-document templates, so that Dorzak is valuable before I subscribe.
11. As a free user, I want my uploads deleted according to a clear policy, so that I can use tools safely.
12. As a free user, I want relevant upgrade guidance without losing my completed work, so that moving to a paid plan feels useful.

### 32.2 Merchant owner and administrator

13. As an owner, I want one Dorzak account, so that I never manage a separate ERPNext login.
14. As an owner, I want Dorzak to provision my business core automatically, so that I can focus on setup rather than infrastructure.
15. As an owner, I want my data isolated in its own ERPNext site/database, so that unrelated merchants cannot access it.
16. As an owner, I want one or many locations without Enterprise eligibility rules, so that the plan reflects complexity rather than branch count.
17. As an owner, I want to invite staff with exact roles and locations, so that access follows responsibility.
18. As an owner, I want to know which system action failed and how to recover, so that integrations do not create silent errors.
19. As an owner, I want my operational and financial reports based on ERPNext records, so that stock and money have one truth.
20. As an owner, I want to preview upgrades/downgrades and data effects, so that plan changes are predictable.
21. As an owner, I want existing data retained after downgrade, so that commercial changes do not destroy business history.
22. As an owner, I want usage and overage visibility before charges, so that variable-cost services remain controllable.
23. As an owner, I want a mobile-ready business website, so that customers can transact easily.
24. As an owner, I want Dorzak to build or modify my Enterprise website when requested, so that I can obtain expert assistance.
25. As an owner, I want every Dorzak intervention visible and audited, so that managed service does not remove accountability.

### 32.3 Website editor and merchant marketer

26. As an editor, I want approved sections and category templates, so that I can build a professional website without coding.
27. As an editor, I want EN/FR/AR content and RTL preview, so that every language is intentionally published.
28. As an editor, I want mobile/desktop preview, so that I catch layout problems before launch.
29. As an editor, I want draft, staging, schedule and restore, so that content changes are safe.
30. As an editor, I want concurrent-change protection, so that another tab or Dorzak teammate cannot overwrite me silently.
31. As a marketer, I want consent-aware segments and campaigns, so that I communicate lawfully.
32. As a marketer, I want actual send and revenue results, so that the dashboard does not claim false success.
33. As a marketer, I want WhatsApp templates rendered accurately, so that approved messages send as expected.
34. As a marketer, I want limits and frequency caps, so that costs and customer fatigue remain controlled.

### 32.4 Retail, supplier and restaurant users

35. As a retailer, I want POS and online stock to post to one ledger, so that I do not oversell because of duplicate truths.
36. As an inventory manager, I want partial PO receiving and cost history, so that stock and margin remain accurate.
37. As a manager, I want stock counts and reasoned adjustments, so that loss and errors are accountable.
38. As a B2B buyer, I want my own catalog prices and terms, so that I can order without repeated negotiation.
39. As a supplier sales user, I want quotes, deposits, invoices and aging, so that I can convert and collect orders.
40. As a restaurant guest, I want a fast mobile menu and clear status, so that ordering requires little effort.
41. As a kitchen user, I want tickets routed to the correct station, so that preparation is coordinated.
42. As a restaurant owner, I want ingredient, waste and contribution information, so that revenue is not confused with profit.

### 32.5 Appointment, beauty and healthcare users

43. As a customer, I want to see real availability and reserve a slot, so that double bookings do not occur.
44. As a provider, I want availability, buffers, leave and resource rules, so that my schedule is workable.
45. As a salon manager, I want packages, rooms, equipment and team utilization, so that I can manage more than a simple calendar.
46. As a customer, I want reminders, rescheduling and cancellation policy, so that I can manage my appointment.
47. As a patient, I want a merchant-scoped verified account, so that my clinic information is private.
48. As a patient, I want informed consent and an access path to approved records, so that I understand how my data is used.
49. As a clinician, I want minimum-necessary patient access and an access log, so that professional confidentiality is protected.
50. As a clinic administrator, I want provider/room/queue/billing coordination, so that the clinic runs from one interface.
51. As a privacy owner, I want health data excluded from ordinary marketing and logs, so that sensitive content does not leak.

### 32.6 Education users

52. As a guardian, I want a separate authorized relationship to a learner, so that a child’s account is not treated like an adult customer account.
53. As an applicant, I want to submit and track admission information, so that enrollment is transparent.
54. As a scheduler, I want teacher/room/cohort collision detection, so that the timetable is feasible.
55. As a teacher, I want attendance, assignment and result tools within my role, so that I cannot access unrelated records.
56. As a learner, I want a mobile schedule and announcements, so that I know what is expected.
57. As a finance user, I want fees and payments posted to ERPNext, so that the institution’s accounts remain authoritative.

### 32.7 Gym users

58. As a gym visitor, I want to compare passes and memberships, so that I can choose the right commitment.
59. As a member, I want to book, cancel and join a waitlist, so that I can use available capacity fairly.
60. As a member, I want a secure replay-resistant check-in, so that my membership cannot be shared improperly.
61. As a gym manager, I want class, trainer and room capacity rules, so that no class is overbooked.
62. As a gym owner, I want renewal, freeze, failed-payment and churn visibility, so that recurring revenue is manageable.
63. As a facility manager, I want equipment maintenance linked to ERP assets, so that safety and availability are visible.

### 32.8 Nonprofit users

64. As a donor, I want to give once or repeatedly and receive an accurate acknowledgement, so that I trust the organization.
65. As an anonymous donor, I want my publication preference respected, so that giving does not expose my identity.
66. As a fundraiser, I want campaign/fund designation and reconciliation, so that reports match collected money.
67. As a volunteer, I want opportunities, screening status and shifts, so that participation is organized.
68. As a volunteer coordinator, I want availability, skills and hours without beneficiary access, so that roles remain separated.
69. As an applicant or beneficiary, I want a private status journey, so that fundraising users cannot read sensitive information.
70. As a nonprofit finance user, I want final accounting consequences in ERPNext, so that operational fundraising reports reconcile to accounts.

### 32.9 Business and Enterprise collaboration

71. As a Pro user, I want projects and tasks, so that I can organize operational work without another tool.
72. As a Business user, I want files, approvals, Gantt dependencies and workload warnings, so that my team can coordinate complex delivery.
73. As an Enterprise user, I want portfolio, baselines, critical path, capacity and scenarios, so that I can govern major initiatives.
74. As an Enterprise client, I want a shared Dorzak delivery room, so that custom website/integration work remains transparent.
75. As a customer approver, I want only published milestones and deliverables, so that I do not see internal merchant/Dorzak work.

### 32.10 Superadmin and Dorzak team

76. As Superadmin, I want to edit plan drafts and publish immutable versions, so that pricing is flexible without corrupting subscriptions.
77. As Superadmin, I want impact previews and two-person approval, so that commercial changes are deliberate.
78. As Superadmin, I want every merchant’s ERPNext site health, so that I can intervene before an outage harms the merchant.
79. As Superadmin, I want to grant narrow teammate access, so that Dorzak can help without ambient internal access.
80. As Superadmin, I want to revoke all access immediately, so that an incident can be contained.
81. As a support observer, I want a read-only diagnostic grant, so that I can assess a problem safely.
82. As a delegated teammate, I want exact task and time scope, so that I know what I am authorized to change.
83. As a content administrator, I want consent-backed client stories, so that public proof is accurate and revocable.
84. As a release owner, I want one readiness dashboard, so that no partial plan/category is accidentally sold.

### 32.11 General Business and regulated-scope safety

85. As a General Business customer, I want my inquiry, quote, payment and status in one mobile journey, so that a non-specialized company still feels complete.
86. As a General Business owner, I want ERP-backed quotes/invoices plus Dorzak website, CRM and Work, so that I can operate without irrelevant vertical menus.
87. As a regulated business owner, I want sensitive capabilities activated only after verified approval, so that a category or plan edit cannot expose unsafe functions.
88. As a person using a shared or recycled mobile number, I want an explicit profile/linking challenge, so that OTP access never reveals another person’s patient, learner, beneficiary or customer record.

---

## 33. Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Per-merchant ERPNext operations become expensive | Margin and reliability pressure | Automated provisioning, fleet control, shared immutable images, per-site quotas, cost-aware plans, dedicated Enterprise infrastructure |
| Dual truth during migration | Wrong stock, money and orders | Explicit source map, idempotent migration, read-only cutover windows, projection rebuild and reconciliation gates |
| Historical documents and opening balances are both imported | Duplicated stock, receivables or GL | Mutually exclusive cutover policy, immutable source mappings, freeze/reconcile/sign-off and no ERP routing before parity |
| Too many verticals produce shallow products | Poor launch quality | Shared kernels, exact category acceptance fixtures, separate programs and SME sign-off |
| ERPNext version drift/custom fork | Upgrade failure and security risk | Pin stable releases, keep custom behavior in `dorzak_core`, canary waves, contract tests and rollback |
| Pricing edits harm subscribers | Revenue/trust incident | Immutable versions, impact preview, dual approval, effective dates, grandfather/migration policy and audit |
| High-tech visuals create slow or inaccessible UX | Conversion and usability loss | Performance/motion budgets, semantic fallbacks, reduced motion, automated/manual accessibility gates |
| “Our Clients” claims lack permission/evidence | Legal and trust damage | Consent/evidence CMS, expiry/revocation, final-language approval and emergency purge |
| Health/education/nonprofit data crosses ordinary CRM | Severe privacy breach | Purpose-separated roles, classification, compartment policies, DLP, audit and release-specific legal review |
| Shared/recycled mobile silently links the wrong person | Patient/learner/customer disclosure | Opaque principal IDs, explicit role proof, step-up/profile selection, revocation and cross-merchant identity tests |
| Category or plan edit activates regulated capability | Unlicensed or unsafe operation | Server-enforced sensitive activation with evidence, country scope, expiry and revocation |
| Shared ERPNext site used for unrelated merchants | Cross-tenant leakage | One site/database per organization and routing tests; Company never used as tenant |
| Provider outages create silent success | Financial/operational inconsistency | Pending state, idempotent retry, honest UI, dead-letter/reconciliation and provider isolation |
| Offline POS evidence is forged/replayed or cannot post | Financial/stock inconsistency | Device keys, sequence/snapshot validation, restricted tenders, provisional receipt and manager-owned conflict workflow |
| Unlimited messaging/AI/storage destroys margin | Unsustainable plans | Published allowances, meters, overages, caps and cost review |
| Support promise exceeds staffing | Churn and reputational damage | Publish only staffed service levels, capacity dashboard and operational sign-off |
| Existing GPL/trademark obligations ignored | Legal/release risk | Separate-service/custom-app boundary, notices/source duties and legal review |

---

## 34. Out of scope / explicit future scope

The following may be designed later and must remain absent from launch marketing until approved:

- merchant-facing Mautic Advanced Automation;
- autonomous AI operations;
- biometric gym access and health/body tracking;
- diagnostic or treatment-recommendation AI;
- broad telemedicine;
- sensitive nonprofit case management and automated eligibility decisions;
- broad app marketplace/Zapier-style ecosystem;
- unverified international parcel carrier network;
- native ERPNext Desk access as a standard plan feature;
- on-premise Dorzak distribution;
- unsupported countries or languages.

---

## 35. Definition of done

This baseline is delivered when a visitor can discover Dorzak in English, French or Arabic; use valuable free tools; see verified client proof; select an accurate plan/trial; complete payment; receive an invisibly provisioned isolated ERPNext core; build and publish a high-quality category-appropriate mobile website; operate the category’s complete Pro, Business or Enterprise workflows through Dorzak; and receive governed Dorzak support—while Superadmin can safely control pricing, tenants, ERPNext fleet health, subscriptions, content, intervention and release evidence.

The end customer experiences only the merchant’s business and data. The merchant experiences one Dorzak product. Dorzak operates one governed platform. ERPNext supplies each paid merchant’s isolated operational and financial core.

---

## 36. Evidence and source register

### 36.1 Dorzak repository

- current application source under `backend/` and `src/`;
- `docs/DORZAK_MERCHANT_SCALE_PLAN_PRD.md` for audited current-state gaps and feature research, excluding superseded plan/price/release decisions;
- `docs/backend-planning/13-saas-platform-layer.md` for configurable-plan and platform-admin foundations, excluding superseded Free/Store/three-plan architecture;
- `docs/superpowers/specs/2026-07-13-work-management-gantt-design.md` for Work product behavior, excluding superseded ERPNext-optional/source-of-truth clauses;
- `docs/source-audits/2026-07-13-mautic-fit.md`;
- `docs/source-audits/2026-07-13-erpnext-shipping-fit.md`;
- Work and Marketing implementation-plan drafts, which require reconciliation against this PRD.

### 36.2 Supplied source repositories

- ERPNext and Frappe Docker: `/Users/barsha/Documents/ِERP Next`;
- website builder: `/Users/barsha/Documents/build website /builder`;
- CRM, Helpdesk, HRMS, Insights, Education, Payments, Frappe UI, Gantt, ERPNext Shipping and Mautic under `/Users/barsha/Documents/build website `.

### 36.3 External official evidence refreshed 14 July 2026

- Lightspeed Retail pricing: https://www.lightspeedhq.com/pos/retail/pricing/
- Lightspeed Restaurant pricing: https://www.lightspeedhq.com/pos/restaurant/pricing/
- Qatar Personal Data Privacy Protection guidance: https://mot.gov.qa/en/news/motc-releases-guidelines-personal-data-privacy-protection-law
- Tunisia INPDP health-data guidance collection: https://www.inpdp.tn/bos.pdf

External pricing, provider, regulatory and competitor evidence must be refreshed before public publication and at each material commercial/country release.

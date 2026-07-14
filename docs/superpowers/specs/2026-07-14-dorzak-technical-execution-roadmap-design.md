# Dorzak Complete Launch — Technical Execution Roadmap

**Date:** 14 July 2026

**Status:** Proposed execution design for owner approval

**Product baseline:** [Dorzak Complete Launch Baseline v1](./2026-07-14-dorzak-complete-launch-baseline-v1.md); it becomes formal product authority when the owner approves its written review candidate
**Delivery rule:** One complete public launch only. Internal milestones are not partial public releases.

---

## 1. Outcome

This document turns the consolidated owner decisions and product-baseline review candidate into one engineering operating system. It defines:

- the target technical architecture;
- the coding and algorithm standards;
- the order in which the system is built;
- the measurable evidence required to close each milestone;
- how many agents work on one task and when parallel work is safe;
- which skills are mandatory for each class of work;
- which local and external products are implementation sources, references, or inspiration only;
- how Dorzak prevents duplicated work, conflicting authority, unsafe copying, and unreviewed code.

It is a roadmap and architecture design, not an executable implementation plan. Each P00–P19 program and the mandatory shared WP-M2 Contract Freeze work package receive an approved design and file-by-file TDD plan immediately before implementation. This prevents a large plan from becoming stale while the foundation evolves.

---

## 2. Decisions that clear the noise

1. **On written owner approval, the complete-launch PRD is the product authority.** Earlier PRDs and plans are evidence only where that PRD explicitly preserves them.
2. **ERPNext is the operational and financial core for every paid merchant.** Dorzak remains the only experience exposed to merchants and customers.
3. **One paid organization maps to one isolated Frappe site and data boundary.** A site may serve one or several locations of the same organization. Enterprise never requires three locations.
4. **Dorzak is a modular monolith plus external systems, not a microservice estate.** Laravel owns the control plane and Dorzak-native domains. ERPNext, payment providers, object storage, messaging, and approved marketing systems sit behind narrow Interfaces.
5. **There is one authority for every field and business fact.** No dual-write stock, invoice, payment, customer-account, plan, or workflow truth is permitted.
6. **The existing merchant React application is preserved and incrementally modularized.** It is not rewritten before the first ERP-owned commerce tracer succeeds.
7. **The public/customer web surface is a separate server-rendered React deployment.** It will host the corporate site, Free Tools, client websites, and merchant-customer mobile journeys. Next.js App Router is the preferred direction because SEO, localization, image performance, and merchant-site rendering are first-class requirements, but it becomes final only after the architecture spike and ADR in this roadmap pass.
8. **The builder editor lives in the merchant application; published sites render in the public/customer web surface.** The publication artifact is a validated immutable schema, never arbitrary merchant JavaScript.
9. **Superadmin is a governed product, not ambient god mode.** Access is read-only by default, reason-bound, audited, time-limited, and delegable by the Superadmin.
10. **Internal feature flags protect incomplete work, but no incomplete plan or category is marketed or released publicly.**
11. **We do not copy source code merely because it exists locally.** Every candidate passes license, security, architecture, tenant, localization, accessibility, upgrade, and rollback review first.
12. **Milestones close by evidence, not by percentage or optimism.** A milestone is either open or its exit gate is proven.
13. **Merchant-customer identity is merchant-local and merchant-branded.** Checkout requires name and verified mobile, keeps email optional, and creates the customer account automatically. The same mobile at another merchant is a separate tenant relationship and cannot authenticate, reveal, or merge the first merchant’s data. The public experience does not expose a shared Dorzak customer umbrella.

---

## 3. Verified starting baseline

The following evidence was refreshed on 14 July 2026:

| Area | Current evidence | Meaning |
|---|---|---|
| Laravel tests | 441 of 443 pass | Two known contract/fixture discrepancies: media URL shape and QAR versus USD demo expectation |
| Playwright | 0 of 7 pass | Tests are stale against the current authenticated login flow; they wait on pages that redirect to login |
| TypeScript | Compilation succeeds | Static typing exists, but external DTOs still degrade to loose types in several seams |
| Production frontend build | Succeeds | Main JavaScript is about 778.88 kB minified and 216.70 kB gzip; Vite raises the large-chunk warning |
| PHP formatting | Pint reports 16 affected files | Formatting baseline is not clean |
| Frontend unit tests | No runner configured | There is no reliable component, hook, reducer, policy, or pure-function test layer |
| CI | Not configured | Green status is not reproducible or enforced |
| Database tests | Primarily SQLite | PostgreSQL locking, constraints, leases, and concurrency behavior are not proven |
| Localization | EN/AR DOM-rewrite bridge | French, typed catalogues, static extraction, and complete RTL testing are absent |
| Tenancy | Store is tenant root and location | Tenant safety partly depends on a global scope that becomes inert without context |
| Commerce authority | Laravel | Products, stock, orders, customers, and totals are not yet ERPNext-owned |
| Commercial catalogue | Mutable Free/Pro/Enterprise | Business, immutable PlanVersion publication, and explicit-unlimited semantics are absent |

Therefore feature velocity is not the first engineering objective. The first objective is to create a trustworthy baseline that can detect regressions.

---

## 4. Delivery approach comparison

| Approach | Advantage | Failure mode | Decision |
|---|---|---|---|
| Vertical-by-vertical | Early category demos | Repeats identity, money, scheduling, permissions, ERP, and plan logic; produces inconsistent products | Rejected |
| Horizontal big bang | Layers appear orderly | Backend, frontend, ERP, and billing meet too late; integration risk remains hidden | Rejected |
| Rewrite first | Clean-looking folders | Discards verified behavior and delays business proof | Rejected |
| **Foundation plus tracer bullets** | Freezes shared Interfaces, proves one real transaction, then safely fans out | Requires discipline before visual feature volume appears | **Selected** |

The selected sequence is:

1. make the existing baseline trustworthy;
2. migrate Store tenancy additively to Organization and Location;
3. freeze cross-system value objects and Interfaces;
4. build ERP and commercial foundations in two controlled streams;
5. prove one Retail Pro transaction from customer action to ERPNext and back;
6. build shared product kernels;
7. complete every category and plan as end-to-end vertical slices;
8. complete Superadmin, cross-cutting qualification, rehearsal, and one public launch.

---

## 5. Target system shape

~~~text
Corporate + Free Tools + merchant public websites + customer mobile web
                    Next.js public/customer surface
                                  |
Merchant desktop + Builder editor + governed Superadmin
                       React/Vite application
                                  |
                         Dorzak Laravel API/BFF
        identity | tenancy | plans | policy | verticals | audit
                                  |
              authenticated organization-to-site resolver
                                  |
            signed, versioned dorzak_core business commands
                                  |
               isolated ERPNext/Frappe site per organization
                                  |
   payment | messaging | storage | scanning | approved integrations
~~~

### 5.1 Deployment units

| Deployment | Responsibility | Must not own |
|---|---|---|
| Dorzak Laravel API/BFF | Organization, identity, plans, subscriptions, policies, vertical domains, orchestration, projections, audit, Superadmin APIs | ERP accounting/stock truth; public page rendering |
| Merchant/Superadmin React app | Desktop management, POS where applicable, builder editor, operational dashboards, governed platform operations | Direct ERPNext or provider credentials; server authority |
| Preferred public/customer Next.js app | Corporate pages, pricing, Free Tools, Our Clients, published merchant sites, customer mobile journeys, SEO and localized rendering | Financial/stock authority; direct ERPNext access |
| dorzak_core Frappe app | Narrow ERP commands, mappings, audit envelope, contract version, health and controlled webhooks | Dorzak authentication UI, arbitrary generic CRUD exposure |
| ERPNext site fleet | Accounting, stock, items, purchasing, canonical commercial documents, Projects/Tasks/Timesheets | Dorzak plan, identity, website, consent, or vertical-native truth |
| Provider Adapters | Hosted/tokenized payment, SMS/email/OTP, scanning, delivery, storage | Domain decisions or direct entitlement grants |

### 5.2 Repository evolution

The repository evolves without a disruptive rewrite:

~~~text
backend/
  app/
    Domain/                 new deep Modules, introduced program by program
    Infrastructure/         external Adapters and provider-specific code
    Http/                   thin controllers, requests, resources, middleware
  tests/

src/                       existing merchant/Superadmin app; migrate by feature
  features/
    organization/
    commercial/
    erp/
    website/
    scheduling/
    verticals/
    platform/

apps/
  public-web/              added when P05 begins under the approved rendering-stack ADR

frappe_apps/
  dorzak_core/             versioned custom Frappe app and contract tests

packages/                  created only when a second real consumer exists
  contracts/
  design-tokens/
  localization/
~~~

M0–M4 do not move the entire current React tree merely to make the folders look new. New or materially changed work follows feature locality. Existing pages are decomposed when a task touches them or a measurable bundle/test problem requires it.

---

## 6. Architecture vocabulary and rules

The engineering team uses the following terms precisely:

- A **Module** owns one coherent domain capability and hides its internal complexity.
- An **Interface** is the smallest stable surface another Module can depend on. The Interface is also the contract-test surface.
- An **Implementation** realizes an Interface.
- **Depth** means a small Interface provides substantial useful behavior and enforces strong invariants.
- A **Seam** is a deliberate place where an Implementation can change.
- An **Adapter** translates between Dorzak’s model and an external system.
- **Leverage** measures how much product behavior a small, stable Module unlocks.
- **Locality** means code, tests, types, policies, UI, and documentation for one behavior live close enough to change safely.

### 6.1 Deletion test

A proposed abstraction is accepted only if deleting or replacing an Implementation leaves the caller dependent on a coherent Interface. If deleting it merely moves miscellaneous helpers elsewhere, it is not a useful Module.

### 6.2 Real seams only

We create an Interface when:

- the dependency is external;
- two Implementations genuinely exist, such as production and deterministic fake;
- replacement is expected and contract testing matters;
- a high-risk domain must be isolated behind a policy surface.

We do not create generic repositories, BaseService hierarchies, or one-method wrappers around Eloquent merely to claim clean architecture. One Adapter is a hypothetical seam; two contract-tested Adapters make it real.

### 6.3 Deep Modules

| Module | Stable Interface | Hidden Implementation depth |
|---|---|---|
| Execution Context | Resolve actor, organization, location, plan version, country pack, delegated grant, correlation ID | Session, membership, role, location authorization, platform mode, fail-closed rules |
| Identity / Party / Consent | Register, verify, link merchant-local Party, authorize channel/purpose | OTP, guardians, suppression, consent evidence, ERP customer/contact mapping |
| Commercial Policy | Decide capability, action, limit, usage, upgrade reason | Immutable publications, add-ons, trials, overrides, dependency validation, usage ledger |
| ERP Command Gateway | Execute a versioned command in one organization context | Site routing, signed actor envelope, idempotency, retries, receipt, mapping, projection |
| ERP Fleet | Provision, upgrade, probe, back up, restore, quarantine | Site lifecycle saga, release cohorts, secrets, storage, workers, health evidence |
| Payment Kernel | Create intent/session, record provider fact, reconcile, refund | Provider signatures, replay protection, settlement facts, subscription and ERP consequences |
| Workflow / Version | Validate and apply an allowed transition | permissions, optimistic version, approval, comments, immutable history |
| Scheduling | Query availability, hold, confirm, cancel, attend | recurrence, timezone, capacity, resources, collision prevention, waitlist |
| Website Publication | Validate draft, preview build, promote, roll back pointer | schema registry, localization, media, domain, deployment, cache invalidation |
| Host / Publication Resolver | Resolve a verified host to one tenant, publication build, locale policy, cookie policy, and CSP | custom-domain proof, canonical redirects, cache namespace, certificate state, unknown-host denial, purge isolation |
| Media / Documents | Ingest, validate, transform, authorize, retain, delete | checksums, scan, DLP, signed URL, category rules, access logging |
| Communications | Send a purpose-bound localized message | template versions, provider route, consent, quiet hours, retries, attribution |
| Audit / Observability | Record immutable action and correlate a flow | redaction, trace propagation, retention, metrics, alerts, evidence export |

Vertical Modules may compose these Interfaces. They may not clone their Implementations.

---

## 7. Coding style

### 7.1 General

- Prefer explicit, boring, readable code over clever abstraction.
- One task changes one externally observable behavior and one authority boundary.
- Names use domain language from the PRD. Generic names such as Manager, Handler, Utility, Data, or Common require a clear domain qualifier.
- Comments explain invariants, authority, compensation, or non-obvious risk; they do not narrate syntax.
- No hidden network calls in model accessors, render functions, or serialization.
- No hard-coded prices, plan arrays, countries, currencies, locales, permissions, or status strings outside their published registries.
- No production TODO that can change security, money, tenancy, or source-of-truth behavior.
- Generated code, migrations, contract manifests, and source notices are committed and reviewed with their generators or origin.

### 7.2 Laravel / PHP

- Use typed PHP compatible with the pinned production runtime, PSR-12, and Pint.
- Preserve the useful flow: Controller → FormRequest → Action/Application operation → Domain Module → Resource.
- Controllers authenticate, authorize, validate, call one operation, and serialize. They do not contain business state transitions.
- Backed enums and explicit transition maps replace scattered strings.
- Local database invariants use transactions, unique constraints, foreign keys, checks, and row locking where required.
- A database transaction never remains open across ERPNext or provider HTTP calls.
- Tenant resolution fails closed. Background, public, and Superadmin operations use explicit context modes rather than an absent global scope.
- Cross-tenant queries are available only through governed platform query Modules and audited grants.
- Policies authorize server actions even when the UI hides a control.
- New shared behavior goes in a deep Domain Module. Existing conventional Actions and Services may remain until deliberately migrated.
- Provider/Frappe code belongs in Infrastructure Adapters, not Domain models or controllers.
- PHPStan/Larastan is added in P00 at an initially achievable baseline; new code may not add violations, and debt must trend downward.

### 7.3 React / TypeScript

- TypeScript remains strict. Domain and external seams do not use any. Unknown external payloads are runtime-validated before adaptation.
- UI consumes Dorzak DTOs, never raw ERPNext or payment-provider shapes.
- Feature locality is the default: api, model, policy, state, UI, route, and tests for a feature live together.
- Zustand is limited to cross-route client state. Forms and ephemeral UI remain local. Server data is not copied into multiple mutable stores.
- A new global server-state library is not introduced until the M4 tracer demonstrates a concrete cache/invalidation need and an ADR approves it.
- Routes are lazy-loaded and receive route-level loading, error, empty, permission, offline, and upgrade states.
- Large pages are decomposed by behavior and data ownership, not by arbitrary visual fragments.
- All user-visible strings use typed EN/FR/AR catalogues. DOM text mutation and MutationObserver translation are removed.
- Direction is a first-class layout input. Chronological axes may remain LTR inside an RTL shell when this is clearer and tested.
- Use semantic HTML and accessible names before test IDs. Playwright selectors prefer roles and labels.
- Motion uses transform/opacity, honors reduced motion, is interruptible, and stays within the PRD budgets.
- Customer web is mobile-first; merchant and Superadmin operations are desktop-first but responsive.

### 7.4 Frappe / ERPNext

- Production pins a supported stable ERPNext/Frappe release and immutable image digest. The supplied develop checkout is reference code only.
- Upstream core is not edited. dorzak_core uses hooks, patches, fixtures, extend_doctype_class where supported, and narrow whitelisted business commands.
- Generic resource CRUD, Desk, credentials, and site headers are never exposed to a browser.
- Every command validates contract version, site, organization, actor, role/grant, action, expiry, jti, idempotency key, and correlation ID.
- ERP patches are forward-only, idempotent where possible, rehearsed on representative sites, and compatible with fleet cohort upgrades.
- Contract tests run against the deterministic fake and the pinned real Frappe image.

### 7.5 Data and migrations

- Dorzak’s launch database is PostgreSQL. Concurrency tests run against PostgreSQL, not only SQLite.
- Schema change follows expand → backfill → verify → switch reads/writes → contract.
- Backfills are resumable, idempotent, observable, rate-limited, and accompanied by a read-only audit command.
- Destructive contraction happens only after a measured compatibility window and restore rehearsal.
- Public identifiers are non-sequential UUID/ULID values. Internal database keys may remain integers where no boundary leaks them.
- Optimistically edited aggregates carry a version. Stale writes return a typed conflict with current state.
- Sensitive data is minimized, classified, encrypted as required, redacted in logs, and excluded from analytics by default.

---

## 8. Algorithm and distributed-systems rules

### 8.1 Money

- Authoritative money is never a JavaScript or PHP float.
- Dorzak uses a Money value with currency and integer minor units; adapters explicitly convert currencies whose minor-unit conventions differ.
- ERP decimal values are parsed and serialized exactly.
- Tax, discount, proration, refund, rounding, and allocation are pure deterministic functions with example, boundary, and property tests.
- Provider amount, Dorzak payment fact, ERP document, settlement, and entitlement are separate reconcilable facts.

### 8.2 Execution context

Every protected operation resolves:

~~~text
session or signed internal principal
  → actor
  → organization membership
  → authorized location scope
  → immutable plan version and capability decision
  → country-pack version
  → delegated grant when applicable
  → correlation ID
~~~

If any required element is absent or inconsistent, the operation fails closed. Body and query tenant IDs are selectors only after server authorization; they are never authority.

### 8.3 Idempotent command

1. Validate ExecutionContext and command schema.
2. Build a fingerprint from tenant, operation, canonical payload, and idempotency key.
3. Begin a local transaction and insert or lock an IN_PROGRESS command receipt containing fingerprint, lease owner, lease expiry, attempt, and recovery state.
4. If a completed matching receipt exists, return its original result.
5. If the same key has a different fingerprint, reject it.
6. If another unexpired lease owns the command, return a typed in-progress response; if the lease expired, take it over through compare-and-swap and continue deterministically.
7. Persist receipt, local intent, and outbox atomically, then commit.
8. Deliver at least once with a signed actor envelope.
9. Let the authoritative system commit once and return its receipt.
10. Store completion receipt, mapping, and projection with the authoritative version in a local transaction.
11. Recover safely after a crash by replaying the outbox/expired lease and querying the authoritative receipt where needed.
12. Reconcile until terminal; dead-letter after bounded retries.

Dorzak never claims exactly-once transport. It guarantees idempotent effects, durable evidence, and reconciliation.

### 8.4 Webhook ingestion

1. Capture bounded raw body and provider headers.
2. Verify signature, timestamp, endpoint, and supported version.
3. Insert an immutable receipt under a provider/event unique key.
4. A duplicate acknowledges without reapplying effects.
5. Queue processing after the receipt commits.
6. Resolve events in provider-independent state machines; tolerate out-of-order delivery.
7. Reconcile provider, Dorzak, ERPNext, and entitlement facts.
8. Record failure, retry count, correlation, and dead-letter reason.

A webhook never directly grants a subscription or declares an ERP invoice paid.

### 8.5 State machines

Payment, fulfillment, ERP posting, subscription, provisioning, publishing, migration, appointment, class, healthcare, education, membership, donation, shipment, and Work each have separate explicit states.

Every transition has:

- allowed source states;
- actor/capability rule;
- required version;
- preconditions;
- atomic local effects;
- outbox effects;
- compensation or recovery route;
- audit event;
- terminal/non-terminal classification.

No universal status enum is permitted.

### 8.6 Concurrency

- Use database uniqueness for uniqueness, not check-then-create.
- Use row locks or compare-and-swap for stale transitions.
- Use atomic counters/reservations for plan seats, capacity, inventory-facing holds, and quotas.
- Workers claim batches with leases or SKIP LOCKED behavior and observable lease expiry.
- Scheduled jobs use overlap prevention, bounded work, fair per-tenant quotas, retry/backoff, and dead-letter handling.
- No task may rely on UI disabling, readonly state, or one-person expectations for correctness.

### 8.7 Scheduling

Availability is a deterministic function of timezone, recurrence, resource calendars, staff calendars, service duration, buffers, capacity, closures, and existing holds/bookings.

The confirmation algorithm:

1. normalize requested time to the organization timezone and UTC;
2. load the versioned rule/calendar set;
3. create an expiring idempotent hold under database-enforced collision/capacity constraints;
4. complete required payment/approval;
5. atomically convert the hold to the category-specific booking/class/shift;
6. release or expire unused holds;
7. notify from the committed state.

Appointments, school timetables, gym classes, clinical slots, and volunteer shifts share this kernel but retain separate aggregates and policies.

### 8.8 Provisioning

ERP site provisioning is an observable saga:

~~~text
REQUESTED → RESERVED → SITE_CREATING → APPS_INSTALLING
→ BOOTSTRAPPING → HEALTH_CHECKING → INITIAL_BACKUP → READY
~~~

Every step is idempotent and records attempts, timestamps, release bill of materials, evidence, failure reason, and safe compensation. A trial starts only when the site is READY and the merchant can use it.

### 8.9 Commerce cutover

Existing merchants follow:

~~~text
PREPARED → WRITE_FROZEN → EXPORTED → IMPORTED
→ RECONCILED → ROUTED → VERIFIED
~~~

Counts alone are insufficient. Reconciliation proves hashes, open-document totals, receivable/payable balances, stock quantity and valuation, tax totals, trial balance, mapping, and cutover policy. After ROUTED, Laravel refuses new authoritative commerce writes. After the rollback deadline and first canonical ERP write, recovery is forward through audited compensation.

### 8.10 Website publication

~~~text
DRAFT → VALIDATED_BUILD → PREVIEW → APPROVED → PROMOTED
                                      ↘ ROLLED_BACK_POINTER
~~~

Every published build is immutable and content-addressed. Promotion switches a pointer. Schema/data migrations are separately compatible and cannot be assumed reversible merely because a deployment pointer can roll back.

### 8.11 Host, cache, and cookie isolation

1. Accept only a normalized trusted Host value that maps to a verified active domain or a Dorzak-owned route.
2. Resolve exactly one organization, publication ID, immutable build ID, locale policy, certificate state, and canonical host.
3. Reject unknown, ambiguous, disabled, spoofed-forwarded, or cross-environment hosts before loading tenant content.
4. Namespace every CDN, server, image, metadata, and application cache key by surface, organization, publication/build, host, locale, and relevant authorization state.
5. Scope customer cookies to the verified merchant host and purpose. A shared Dorzak cookie never silently authenticates a customer at another merchant.
6. Generate CSP, frame, connection, asset, and form-action policy from the approved publication/provider manifest.
7. Purge by immutable publication/tenant namespace and prove that one tenant's publish, rollback, disable, or deletion cannot purge or reveal another.
8. Log resolution and purge correlation without storing sensitive customer content.

---

## 9. Quality engineering

Zero defects cannot be honestly guaranteed. Dorzak instead uses prevention, detection, containment, recovery, and measurable release gates strong enough that known Severity 1 and Severity 2 defects are zero at launch.

### 9.1 Required test layers

| Layer | Primary purpose |
|---|---|
| Pure unit/property | Money, entitlements, scheduling, transitions, rounding, recurrence, mapping |
| Domain/application | One operation and its invariants without HTTP noise |
| Database integration on PostgreSQL | constraints, locks, leases, idempotency, migrations, tenant isolation |
| Adapter contract | Fake and real ERP/provider implementations obey the same Interface |
| HTTP/API | validation, auth, policy, resource shape, version/conflict behavior |
| React unit/component | states, accessibility, typed adapters, policy presentation, forms |
| End-to-end | real Laravel + database + browser + controlled ERP/provider doubles |
| Real-stack smoke | pinned ERPNext, payment sandbox, storage, queues, callbacks |
| Migration/reconciliation | repeatability, parity, rollback/forward recovery |
| Security/privacy | tenant matrix, role/grant matrix, replay, SSRF, upload, secret and redaction checks |
| Accessibility/locale | EN/FR/AR, RTL, keyboard, screen reader, reduced motion, 200 percent text |
| Performance/reliability | route budgets, Core Web Vitals, queue age, failover, restore and 24-hour soak |

### 9.2 TDD rule

Every behavior change follows:

1. write the smallest failing test that proves the required behavior;
2. run it and capture the expected RED;
3. implement the smallest correct change;
4. run focused and affected suites to GREEN;
5. refactor while green;
6. obtain independent review;
7. run fresh verification from the final SHA.

A bug fix begins with systematic reproduction and root-cause evidence. Three unsuccessful fix attempts trigger an architecture review; a fourth speculative patch is prohibited.

### 9.3 Task Definition of Done

A task is done only when:

- requirement IDs and authority owner are recorded;
- the focused RED and GREEN commands are captured;
- focused, affected, and integration tests pass;
- no new warning, static-analysis issue, accessibility issue, or bundle regression is introduced;
- tenant, permission, failure, retry, and conflict cases exist where relevant;
- migration work proves idempotency and recovery;
- an independent reviewer approves specification compliance and code quality;
- Critical and Important findings are fixed and re-reviewed;
- documentation and operational evidence are updated;
- the progress ledger records the verified SHA.

---

## 10. Agent operating model

The current environment supports four concurrent slots including the controller.

### 10.1 Direct answer: agents on one task

| Task risk | Active code writers | Reviewers | Controller | Rule |
|---|---:|---:|---:|---|
| Normal task | 1 | 1 fresh reviewer after implementation | 1 | Three roles over time; never two writers |
| Cross-system, tenant, money, security, or regulated task | 1 | 1 normal reviewer plus 1 specialist sequentially | 1 | Specialist checks the high-risk contract |
| Independent research | 0 | Up to 3 analysts in parallel | 1 | Read-only and independently synthesizable |
| Debugging several unrelated failures | 1 designated fixer | Up to 3 investigators before the fix | 1 | Investigations may parallelize; writes do not |

**One task has exactly one code-writing agent.** Additional agents review or investigate; they do not simultaneously edit the same task.

### 10.2 Concurrent streams

- **M0 and M1:** one implementation stream only.
- **M2:** one contract stream only.
- **M3:** at most two streams: ERP platform and commercial platform.
- **M4:** one integration stream only.
- **M5 onward:** at most two implementation streams after upstream Interfaces are frozen.
- **M9:** one qualification/release stream only.

Two streams are allowed only when all five are true:

1. separate branch and worktree;
2. frozen upstream Interface;
3. non-overlapping files, migrations, schemas, and registries;
4. independent focused tests;
5. named merge/integration order.

Migrations, routes, lockfiles, ExecutionContext, plan manifest, tenant/party model, ERP command schema, scheduling kernel, localization catalogue, design tokens, and release configuration are serialized.

### 10.3 Task size

The default task represents one observable behavior and one authority boundary, targeting:

- no more than one migration;
- roughly five production files;
- roughly 400 changed lines;
- one focused test command;
- one working day or less for a human engineer.

These are splitting heuristics, not incentives to hide complexity. A task is split if it crosses Dorzak, ERPNext, and a provider in one change; alters multiple shared Interfaces; lacks one focused regression test; or cannot be reviewed independently.

### 10.4 Agent lifecycle

1. Controller prepares a bounded brief with requirement IDs, context, files, authority, test command, and non-goals.
2. Fresh implementer works with TDD in its assigned worktree.
3. Fresh reviewer inspects the recorded merge-base through head range.
4. Implementer/fixer addresses findings.
5. Reviewer re-checks Critical and Important findings.
6. Controller runs final verification and merges into the green integration branch.
7. Whole-program reviewer checks the accumulated branch before program closure.

No completed task is redispatched after context compaction. Git history and the progress ledger are authoritative.

---

## 11. Skill routing

| Work type | Mandatory skill sequence |
|---|---|
| Product or architecture design | using-superpowers → brainstorming → relevant domain skill → owner approval |
| Executable program plan | writing-plans |
| Isolated implementation | using-git-worktrees → subagent-driven-development |
| Feature or refactor | test-driven-development |
| Failure or bug | systematic-debugging → test-driven-development |
| Independent audits | dispatching-parallel-agents |
| Web/mobile interface | ui-ux-pro-max; add app-ui-design for customer-mobile interaction patterns |
| Corporate site structure | site-architecture |
| Homepage/landing/pricing copy | product-marketing → copywriting → cro → pricing |
| Free Tools | free-tools plus relevant SEO/content skills |
| Tracking and value proof | analytics; schema for public structured data |
| Review request | requesting-code-review |
| Review response | receiving-code-review |
| Completion claim | verification-before-completion |
| Branch integration | finishing-a-development-branch |

The marketing skills under the supplied marketingskills installation are primary for corporate, solution, category, pricing, comparison, Our Clients, onboarding, and lifecycle copy. Engineering skills govern implementation and verification. A marketing skill cannot override product truth, capability enforcement, accessibility, privacy, or performance.

---

## 12. Git, worktree, commit, and evidence model

1. Preserve the current dirty checkout and all user-owned changes.
2. Before worktree creation, record the exact MediaUrl user diff. With owner approval, either commit it separately on the baseline branch or export and apply only that reviewed patch in the clean worktree.
3. After owner approval, create a clean isolated worktree from the owner-approved baseline commit. If a manual .worktrees location is used, ignore it first; a sibling/native worktree is preferred.
4. Maintain a green integration branch named **codex/launch-baseline-v1**.
5. Use program branches named **codex/pNN-short-name**.
6. Record branch merge-base before dispatch. Review **BASE..HEAD**, never an assumed last commit.
7. Commit code and its tests together in small conventional commits.
8. Merge only after task review and fresh verification.
9. Re-run integration gates after every merge.
10. Never merge two red branches hoping the failures cancel.
11. Never stage unrelated files from the current dirty checkout.

Each stream maintains **.superpowers/sdd/progress.md** and the controller maintains a program ledger with:

| Field | Purpose |
|---|---|
| Program/task and requirement IDs | Traceability |
| Branch/worktree and BASE..HEAD | Exact review range |
| Dependency Interface versions | Compatibility |
| Authority/system of record | Prevent dual truth |
| Status and blocker owner | Control |
| RED/GREEN/full verification commands and counts | Evidence |
| Review verdict and open Minor debt | Quality |
| Last verified SHA and timestamp | Prevent stale completion claims |

---

## 13. Measurable milestone roadmap

Milestone dates are forecast only after the corresponding program is decomposed and estimated. The sequence and evidence gates below are fixed; public release remains blocked until M9.

### M0 — Trustworthy engineering baseline

**Programs:** P00

**Parallelism:** one stream

**Purpose:** make failures meaningful before feature work.

Outputs:

- clean isolated worktree and green integration branch;
- deliberate media URL and QAR demo contracts;
- frontend unit/component test infrastructure;
- authenticated deterministic Playwright fixtures;
- PostgreSQL integration lane;
- CI for PHP, TypeScript, unit, integration, browser, formatting, static analysis, and build;
- versioned bundle/lab profile;
- CONTEXT.md plus initial ADRs for system authority, tenancy, modular monolith, one launch, plan publication, cutover, and frontend surfaces;
- accurate backend/repository runbook.

Exit evidence:

- current backend baseline is 443/443 and all added tests pass;
- current Playwright baseline is 7/7 and all added smoke tests pass;
- TypeScript, frontend unit suite, Pint, Composer validation, static analysis, and production build exit zero;
- Playwright starts the required backend/data/auth fixtures, not only Vite;
- PostgreSQL concurrency job is reproducible in CI;
- no regression above the recorded current gzip baseline;
- CI is mandatory and repeatable from a clean checkout;
- git status is clean.

### M1 — Organization, identity, Party, consent, and fail-closed tenant kernel

**Programs:** complete P01

**Parallelism:** one stream

**First code slice (M1a):** Organization → existing Store-as-first-Location migration only.

**Second controlled slice (M1b):** merchant/staff/customer identity, Party, role, contact verification, and consent after M1a parity is green.

Outputs:

- Organization and OrganizationMembership;
- nullable Store.organization_id during the additive expand/backfill slice, enforced as required by application policy after parity; a physical NOT NULL change waits for the audited compatibility window;
- resumable backfill and read-only audit command;
- atomic synchronization of registration, invitations, staff role/active changes;
- immutable ExecutionContext;
- explicit background, public, merchant, customer, and platform modes;
- governed platform queries replacing reliance on absent global context;
- separate merchant-staff, merchant-customer, Superadmin, and delegated-team guards;
- merchant-local customer principals and Party relationships created from required name plus verified mobile, with optional email;
- OTP challenge purpose/tenant binding, attempt/rate/replay control, recycled/shared-number handling, contact history, and account recovery;
- consent evidence, purpose/channel preference, suppression, guardian relationship where approved, and purpose-limited ERP Customer/Contact mapping.

Exit evidence:

- zero existing stores without an organization;
- exactly one organization per existing store and exact membership parity;
- rerun produces zero inserts/updates;
- injected drift makes audit fail without mutating data;
- missing context fails closed;
- cross-organization access matrix passes;
- the same phone/name at two merchants cannot link, authenticate, enumerate, or reveal the other merchant relationship;
- merchant, customer, Superadmin, delegated-team, background, and public guards cannot substitute for one another;
- OTP replay, wrong-purpose, wrong-merchant, expired, throttled, recycled-number, and recovery tests pass;
- consent/suppression is immutable evidence and no provider/ERP projection becomes an authentication source;
- existing store_id commerce behavior and API compatibility remain green;
- P01 traceability is complete before M2 begins;
- no ERP, plan, builder, homepage, or vertical behavior is included in the M1a first slice.

### M2 — WP-M2 Cross-system contract freeze

**Work package:** WP-M2, a mandatory shared prerequisite for P02, P03, P04, and P17

**Parallelism:** one stream

**Purpose:** freeze the Interfaces that permit safe parallel work.

Outputs:

- Money/Currency, CorrelationId, IdempotencyKey, Version and authority-map values;
- ErpCommandGateway, CapabilityPolicy, IdempotentCommandBus, audit envelope;
- actor/site envelope contract;
- outbox, webhook receipt, command receipt, projection metadata, dead-letter, and reconciliation schemas;
- contract versioning and compatibility policy;
- deterministic fake Adapters;
- a minimal dorzak_core contract-proving handshake against the pinned candidate ERPNext/Frappe image: version negotiation, one signed mapped command, idempotent replay, wrong-site denial, and replayed-jti denial.

Exit evidence:

- identical duplicate command returns the original result;
- same key with different payload is rejected;
- duplicate and out-of-order webhooks are inert;
- concurrent workers lease a job once under PostgreSQL;
- wrong-site, wrong-action, expired, and replayed envelopes are rejected;
- no float crosses the Money Interface;
- contract tests pass against all deterministic fakes;
- the same handshake contract passes against the real pinned candidate image before the Interface is frozen;
- authority map has no field with two writers.

### M3 — ERP platform and commercial platform

**Programs:** P02 and P03

**Parallelism:** two controlled streams after M2.

Commercial Stream B may implement only the provider-neutral kernel until the Qatar/Tunisia provider-selection gate records sandbox and commercial evidence. No example repository or Stripe pattern counts as regional provider approval.

**Stream A — ERP platform**

- pinned stable ERPNext/Frappe bill of materials;
- dorzak_core app;
- site registry/router;
- provisioning/upgrade/backup/restore/quarantine sagas;
- release cohorts, health probes, queue fairness, contract compatibility;
- Superadmin fleet read model.

**Stream B — Commercial platform**

- CapabilityDefinition;
- immutable PlanVersion and Publication;
- Free Tools public-catalogue definition plus Pro, Business, and Enterprise paid-plan manifests; Free Tools never creates an Organization, Subscription, storefront, or ERP site;
- editable price/currency/trial/marketing presentation under non-removable floors;
- like-for-like Lightspeed/relevant-competitor evidence where defensible, plus cost-to-serve, target margin, usage allowance, support, and infrastructure review;
- provider-neutral Dorzak billing;
- hosted checkout/portal, signed receipts, reconciliation;
- upgrade/downgrade/cancel/failure/grace/renewal lifecycle.

ERP exit evidence:

- two organizations provision isolated sites to READY;
- rerun is idempotent;
- cross-site command injection fails;
- initial backup and representative restore succeed;
- release/contract mismatch prevents unsafe commands;
- merchant never sees Frappe login or Desk.

Commercial exit evidence:

- a published PlanVersion cannot mutate or delete;
- Business exists and no plan requires a location minimum;
- explicit unlimited differs from missing/undefined;
- public manifest, backend policy, worker policy, and UI explanation match;
- duplicate/out-of-order payment events cannot grant twice;
- Pro, Business, and Enterprise default trials are 14 days, while approved Qatar/Tunisia healthcare trials are 30 days; clocks begin only at ERP site READY;
- changing price affects only the intended publication/subscription rules;
- PlanVersion validation prevents any price, capability, limit, trial, availability, or migration edit from violating protected family floors;
- the selected provider path proves QAR/TND as applicable, hosted/tokenized checkout, recurring billing or an approved equivalent, 3DS, signatures/replay defense, refunds, disputes, settlement reports, sandbox quality, webhook behavior, residency/transfer terms, fees, support/SLA, and subscription versus merchant-payment eligibility;
- owner and finance approve the provider and reconciliation evidence before a paid subscription is treated as launch-ready.

### M4 — First ERP-owned P04 retail integration tracer

**Programs:** P04 only; the retail fixture proves P04 integration behavior and is not production P07 scope

**Parallelism:** one integration stream

**Purpose:** prove the ERP commerce integration chain before broad feature construction. P07 remains Not started until both P04 and P06 complete.

Journey:

~~~text
internal administrative/test harness provisions a trial organization
→ organization and READY ERP site
→ ERP Item projected to Dorzak
→ publish one mobile storefront product
→ customer name/mobile account
→ cart and idempotent submit
→ ERP Sales Order/Invoice and stock consequence
→ provider/cash fact
→ Dorzak projection and receipt
→ refund/return and reconciliation
→ Superadmin health/audit visibility
~~~

Outputs:

- existing-commerce cutover state machine;
- Item, customer/contact, order/invoice, payment, stock, return/refund mappings;
- projection rebuild and freshness;
- honest ERP outage/pending states;
- legacy history policy;
- offline POS journal skeleton limited to the approved exception.

Exit evidence:

- two-merchant test proves no cross-site read/write path;
- the same verified mobile at two merchants creates isolated merchant-local relationships and cannot cross-authenticate;
- duplicate submit/refund produces one authoritative effect;
- ERP outage never creates a second local financial truth;
- counts, hashes, stock, receivables, tax, and trial balance reconcile;
- after ROUTED, Laravel rejects authoritative commerce writes;
- mobile customer journey and desktop merchant journey both pass;
- Superadmin identifies the exact failed site/command without receiving merchant credentials.

M4 is the architecture proof. It is not a public release.
It deliberately does not claim the visitor-facing signup/acquisition journey; that complete journey belongs to P05/M5.

### M5 — Shared experience and product kernels

**Programs:** P05, P06, shared P09 foundation, shared Modules for later programs

**Parallelism:** at most two streams with serialized registries.

Outputs:

- typed EN/FR/AR catalogues and RTL architecture;
- Precision Commerce OS tokens, components, graphics, and motion system;
- a production-shaped rendering-stack spike and ADR comparing the preferred Next.js direction against the current stack for SEO, EN/FR/AR and RTL, verified custom domains, cache/cookie isolation, bundle budgets, deployment cost, observability, and rollback;
- the approved public/customer web application;
- corporate navigation, homepage, visitor signup/trial, pricing, category/plan pages, Free Tools framework, Our Clients CMS;
- schema-based builder editor, preview, immutable publication, domains, rollback;
- Scheduling kernel;
- Workflow/Version, Media/Documents, Communications, Audit/Observability depth;
- lazy routes and feature-local refactoring of touched merchant screens.

Exit evidence:

- no DOM-rewrite localization remains in launch routes;
- every public template renders EN/FR/AR with metadata and RTL evidence;
- the rendering-stack ADR is approved from measured spike evidence before production construction;
- Free Tools are independently usable and governed without promising a paid plan;
- visitor signup proves 14-day paid-plan trials and the approved 30-day Qatar/Tunisia healthcare trial, with the clock starting only after ERP READY;
- builder rejects arbitrary script and invalid/unsafe schema;
- preview/promote/rollback preserves immutable builds;
- verified-host resolution fails closed; unknown/spoofed/ambiguous hosts never render tenant content;
- caches are keyed by organization, host, publication/build and locale; publish, purge, rollback, disable and deletion tests prove tenant isolation;
- customer cookies, CSP, canonical redirects and certificate/domain states are merchant-host scoped and cannot authenticate or leak across merchants;
- scheduling collision/capacity tests pass under real concurrency;
- every kernel has tenant, policy, failure, retry, and contract tests;
- public/customer initial JavaScript and merchant route budgets meet the PRD;
- WCAG 2.2 AA evidence exists for all introduced states.

### M6 — Commerce, restaurant, appointment, and beauty completion

**Programs:** complete P07, P08, P09

**Parallelism:** at most two vertical streams; shared kernels serialized.

Outputs:

- Retail/Shop;
- Supplier/Wholesale/B2B;
- Restaurant/Café/F&B;
- Appointments/Professional Services;
- Coiffeur/Salon/Beauty/Spa;
- plan-specific depth for Pro, Business, and Enterprise;
- one-location high-operation Enterprise support;
- relevant website templates, mobile flows, reports, permissions, and Superadmin views.

Exit evidence:

- each category passes its category × plan capability matrix;
- category-specific order/booking states are separate and explicit;
- ERP financial/stock consequences reconcile;
- one-location and multi-location Enterprise scenarios both pass;
- mobile customer transaction, merchant desktop operation, and delegated Dorzak support journey pass;
- no vertical duplicates a shared kernel.

### M7 — Healthcare, education, gym, nonprofit, and general business

**Programs:** P10, P11, P12, P13 plus General Business requirements

**Parallelism:** two vertical streams at a time only after M6 has completed P09 and its scheduling-dependent appointment/beauty acceptance matrix.

Outputs:

- Qatar/Tunisia healthcare pack for doctors, small clinics, and health centers;
- education/school;
- gym/fitness;
- nonprofit;
- general business configuration;
- category-specific privacy, consent, retention, approval, and restricted-marketing rules;
- extended healthcare trial policy;
- required websites, customer portals, dashboards, reports, and Superadmin safety controls.

Exit evidence:

- all vertical user stories and category × plan rows pass;
- healthcare country/legal readiness gates are approved for the exact released scope;
- minor/guardian, patient, student, beneficiary, member, and donor data never leak across purpose or tenant;
- prohibited sensitive segmentation and marketing tests pass;
- clinical/education/gym/nonprofit operational states remain Dorzak-native while ERP financial effects reconcile;
- EN/FR/AR and Qatar/Tunisia packs pass on representative data;
- restore and incident drills protect the relevant sensitive data.

### M8 — Growth, Work, delivery, and complete control plane

**Programs:** P14, P15, P16, P17

**Parallelism:** at most two streams; Superadmin and shared audit changes serialized.

Outputs:

- CRM, loyalty, referrals, reviews, campaigns, communications, attribution;
- Mautic isolated for Dorzak internal marketing only at launch;
- ERP-backed Work/Projects/Tasks/Timesheets with Gantt projection;
- managed website/service Delivery Room;
- approved shipping/delivery provider scope;
- complete Superadmin organization, commercial, ERP fleet, support, intervention, public-content, incident, and delegated-team controls.

Exit evidence:

- consent/suppression/purpose controls precede every marketing send;
- campaign attribution is explainable and idempotent;
- Work core fields remain ERPNext-owned and stale edits conflict safely;
- Gantt is presentation, has keyboard/table alternatives, and never becomes authority;
- shipping/payment callbacks are signed, deduplicated, and reconciled;
- Superadmin grants are reason-bound, time-limited, read-only by default, auditable, revocable, and require explicit elevation for mutation;
- no Dorzak teammate obtains access unless Superadmin grants it;
- tenant health and failure location are visible without exposing secrets.

### M9 — Complete-launch qualification and release

**Programs:** P18 and P19; fan-in from every earlier program

**Parallelism:** one release stream

**Purpose:** prove the whole system as one product.

Outputs:

- final capability and traceability matrices;
- full EN/FR/AR and Qatar/Tunisia qualification;
- browser/device/accessibility evidence;
- security/privacy assessment and remediation;
- performance and capacity evidence;
- migration, backup/restore, provider outage, queue, dead-letter, reconciliation, and incident rehearsals;
- support, on-call, runbooks, status/rollback, launch content, commercial/provider/legal approvals;
- production release candidate and controlled switch plan.

Exit evidence:

- every P00–P19 program and mandatory WP-M2 work package is complete;
- every required category × plan × country × locale path is green;
- zero open Severity 1 or Severity 2 defects;
- zero unresolved Critical or Important review findings;
- mandatory tenant, role, grant, plan, and authority matrices pass;
- WCAG 2.2 AA evidence and PRD performance budgets pass;
- representative backup/restore and recovery objectives pass;
- coordinated recovery-epoch rehearsal proves RPO at or below 15 minutes and RTO at or below 4 hours across Dorzak identities/mappings/intents/receipts/projections, ERPNext, files, credentials, and event cursors;
- ordinary operational API p95 is below 750 ms excluding providers, cached/aggregated dashboard p95 is below 2 seconds, online stock/projection visibility is within 5 seconds, and labelled analytics freshness is within 15 minutes;
- release BOM, SBOM/provenance, pinned compatibility, security-patch, license, and trademark evidence is approved;
- payment lifecycle simulation and client-proof consent expiry/revocation plus origin/CDN/structured-data purge tests pass;
- the release switch defaults off and cannot enable while evidence is incomplete;
- no launch-critical test is quarantined or ignored without release-board approval and replacement evidence;
- 24-hour representative-load soak is stable;
- owner, product, engineering, ERPNext platform, security/privacy, operations/support, finance/billing/migration, accessibility, legal/compliance, EN/FR/AR, Qatar/Tunisia, and every merchant-category reviewer sign-off is recorded;
- rollback/forward-recovery authority and launch stop conditions are rehearsed.

Only after this gate does Dorzak release Pro, Business, Enterprise, and every approved merchant category publicly together.

---

## 14. Dependency and controlled parallelism map

~~~text
M0 trustworthy baseline
  ↓
M1 Organization + identity + Party/consent + ExecutionContext
  ↓
M2 shared contracts and authority freeze
  ├───────────────┐
  ↓               ↓
M3 ERP core     M3 commercial core
  └───────┬───────┘
          ↓
M4 retail ERP tracer
  ↓
M5 shared product/experience kernels
  ↓
M6 commerce + restaurant + complete P09 appointments/beauty
  ↓
M7 healthcare + education + gym + nonprofit + general
  ↓
M8 growth + Work + delivery + Superadmin
  ↓
M9 qualification, rehearsal, one public launch
~~~

Within M6 and M7, only two disjoint vertical streams run at once. M7 cannot begin until P09 is complete. A stream that needs to change a frozen shared Interface pauses and submits an architecture change for review before continuing.

---

## 15. Exact first sequence after roadmap approval

1. Write and approve the P00 baseline stabilization design.
2. Write the file-by-file P00 TDD implementation plan.
3. Record the exact uncommitted MediaUrl diff and obtain owner approval either to commit it separately or to export/apply only that patch into the clean branch.
4. Obtain permission to create the clean worktree/integration branch; do not assume an uncommitted edit appears there.
5. Diagnose and resolve the two Laravel baseline failures without overwriting the approved MediaUrl contract/change.
6. Repair authentication/data fixtures until the existing 7 Playwright tests express current behavior and pass.
7. Add frontend unit/component tests, lint/format/static gates, PostgreSQL integration, and CI.
8. Record CONTEXT.md, ADRs, runbooks, and bundle baseline.
9. Close M0 only after fresh clean-checkout verification.
10. Write and approve P01 design and implementation plan.
11. Implement only the additive Organization migration tracer from PRD section 29.3.
12. Freeze ExecutionContext and tenant safety after M1a parity.
13. Complete the M1b identity, Party, guard, OTP, and consent slice.
14. Proceed to the M2 contract design only when all P01 evidence passes.

No homepage, plan UI, ERP provisioning, Work, builder, or vertical coding begins before its upstream gate.

---

## 16. Inspiration and reuse map

### 16.1 Launch-critical integrations

| Source | Use | Boundary |
|---|---|---|
| Existing Dorzak React/Laravel product | Primary implementation starting point and closest current Pro behavior | Preserve verified behavior, migrate authority deliberately, and refactor only behind tests |
| ERPNext/Frappe | Canonical ERP core | Pinned stable release; one isolated site per organization; dorzak_core commands; no exposed Desk/generic API |
| Approved Qatar/Tunisia payment providers | Dorzak subscription and merchant payment rails where commercially approved | Provider-neutral kernel, hosted/tokenized page, signed receipts, reconciliation |
| Frappe Docker | Deployment/backup/site-operation patterns | Adapt after production hardening; do not deploy the supplied development ERP checkout |

### 16.2 Adapt after audit

| Source | Learn or adapt | Do not do |
|---|---|---|
| Website builder | responsive canvas, layers, section schema, preview/snapshot concepts | embed AGPL runtime or arbitrary HTML/CSS/JS without legal/security approval |
| Frappe UI | semantic tokens, behavior, list/editor interaction patterns | import its Vue beta into React or copy Frappe identity |
| Frappe Gantt | pinned hardened timeline renderer | treat it as Work authority, scheduling engine, or authorization |
| Frappe Payments / local payment examples | gateway vocabulary, webhook and adapter shapes | assume Stripe or a sample provider works in Qatar/Tunisia |
| ERPNext Shipping | provider terminology and payload/test vocabulary | copy its operational engine or mix it with Work |

### 16.3 Product inspiration, not runtime dependencies

| Product/reference | Inspiration |
|---|---|
| Shopify JSON templates/sections/blocks | bounded merchant-editable website schema |
| Vercel deployments and rollback | immutable preview, promote, rollback-pointer workflow |
| Linear | compact command-first work views, saved layouts, accountable ownership |
| Attio | object/record/list separation and unified activity history |
| Intercom | internal/external support states and visible SLA semantics |
| Rippling | scope + action + access policy and permission previews |
| Customer.io | deduplicated events, explainable segments, bounded journey waits |
| Metabase permission model | row/column security requirements for governed analytics |
| Shopify Polaris principles | calm operational clarity, accessibility, consistency |
| Next.js official production guidance | localized server rendering, metadata, image/font optimization, error boundaries and bundle discipline |

### 16.4 Explicitly deferred or isolated

- Mautic is Dorzak’s internal marketing engine at launch. Merchant Mautic requires a later isolated-per-merchant design.
- CRM, Helpdesk, HRMS, and Insights are future server-side integration candidates, not launch-critical engines.
- Education and shipping repositories are domain vocabulary and test references, not Dorzak’s source of truth.
- GPL/AGPL material is never copied before written license review. A service boundary alone does not settle license obligations.

Useful official references:

- [Frappe sites and multitenancy](https://docs.frappe.io/framework/user/en/basics/sites)
- [Frappe REST API boundary](https://docs.frappe.io/framework/user/en/api/rest)
- [Frappe background jobs](https://docs.frappe.io/framework/user/en/api/background_jobs)
- [ERPNext stable releases](https://github.com/frappe/erpnext/releases)
- [Laravel queues](https://laravel.com/docs/13.x/queues)
- [Playwright projects](https://playwright.dev/docs/test-projects)
- [Playwright test isolation](https://playwright.dev/docs/browser-contexts)
- [Next.js internationalization](https://nextjs.org/docs/app/guides/internationalization)
- [Next.js production checklist](https://nextjs.org/docs/app/guides/production-checklist)
- [Shopify JSON template architecture](https://shopify.dev/docs/storefronts/themes/architecture/templates/json-templates)
- [Vercel deployments](https://vercel.com/docs/deployments/overview)

---

## 17. Program scorecard

The controller publishes this scorecard from evidence, never subjective percentages:

| Measure | Target before M9 |
|---|---:|
| Programs with approved design and executable plan | 20 of 20 |
| Programs passing full Definition of Done | 20 of 20 |
| Mandatory shared work packages with approved design/plan and full Definition of Done | 1 of 1: WP-M2 |
| Required capability-matrix rows passing | 100 percent |
| Required user stories traced to automated/human evidence | 100 percent |
| Known Severity 1/2 defects | 0 |
| Open Critical/Important review findings | 0 |
| Tenant/authority violations in mandatory matrix | 0 |
| Published capability mismatches across manifest/API/UI/worker | 0 |
| Unreconciled canonical financial/stock mappings | 0 beyond approved operational SLA |
| EN/FR/AR required strings and journeys | 100 percent |
| WCAG 2.2 AA required surface states | 100 percent evidenced |
| PRD performance budgets | 100 percent passing |
| Backup/restore and recovery rehearsals | 100 percent passing |
| Required runbooks and owner assignments | 100 percent present |

The dashboard also tracks flaky tests, escaped defects, change failure rate, queue age, reconciliation lag, site health, restore success, bundle size, Core Web Vitals, and test duration. A metric without an owner and threshold is informational, not a gate.

---

## 18. Stop, escalation, and change-control rules

Stop the affected stream immediately when:

- cross-tenant access is possible;
- money, stock, payment, or ERP authority becomes ambiguous;
- sensitive health, education, child, donor, or beneficiary data crosses purpose or tenant;
- two streams touch the same migration, registry, shared Interface, or lockfile;
- an external dependency revision/license is unknown;
- a plan/PRD contradiction is discovered;
- a regulated or provider decision requires owner/legal/commercial authority;
- three evidence-based fix attempts fail.

The controller then records the blocker, freezes the risky path, gathers evidence, and either:

- supplies missing context;
- splits the task;
- assigns one designated fixer;
- requests an architecture change;
- seeks owner/legal/provider decision.

An architecture or product decision never changes silently inside an implementation commit. It first updates the controlling design/ADR and affected plan.

---

## 19. Documentation hierarchy

To prevent duplicated or contradictory plans:

1. **Product authority after written owner approval:** complete-launch PRD.
2. **Technical authority after written owner approval:** this roadmap plus approved ADRs and CONTEXT.md.
3. **Program/work-package design:** one design per P00–P19 program plus mandatory WP-M2 and any approved bounded slice.
4. **Executable plan:** one file-by-file TDD plan per approved program/work package or bounded subsystem.
5. **Task brief and progress ledger:** exact current execution evidence.
6. **Code, migrations, tests, runbooks, and release evidence:** implemented truth.

If two documents disagree, the higher level wins until it is deliberately amended. Older Work and Marketing plans must be reconciled to ERPNext authority, Organization tenancy, Business plan, French, Superadmin grants, and one-launch policy before execution.

---

## 20. Approval consequence

Approval of the session/control model may authorize a fresh read-only P00 task to inspect the review-candidate baseline and roadmap and present a non-authoritative proposed P00 design. It does not authorize writing or committing that design.

The owner must explicitly approve both the complete-launch PRD review candidate and this roadmap before they become formal execution authority.

Separate staged approvals are required for:

1. writing and committing the P00 design;
2. writing and committing the exact P00 implementation plan;
3. preserving the current user change and creating the clean integration branch/worktree;
4. starting P00 execution.

These approvals do not authorize editing ERPNext or any supplied source repository, starting broad feature implementation in the dirty checkout, selecting a payment provider without due diligence, or publicly releasing a partial product.

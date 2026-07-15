# Dorzak Master Solution Authority Design

**Date:** 15 July 2026

**Future master path:** docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md

**Status:** Written design specification based on confirmed owner decisions; the future master does not yet exist

**Scope:** The authority, information architecture, production method, validation gates, review model, approval boundary, and revision rules for one self-contained Dorzak master solution

**Does not authorize:** Creation of the future master, implementation planning, P00 Task 16 or later work, P01–P19 work, code or schema changes, provider or GitHub action, protected-checkout changes, application installation, provisioning, migration, or public release

---

## 1. Decision and outcome

Dorzak will eventually maintain one master solution at
docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md. It will give business, product,
design, engineering, security, operations, support, and delivery teams one
complete explanation of what Dorzak is, what exists now, what the approved
complete-launch target is, how the parts work together, and which decisions
remain controlled.

The master is not a summary page, an index of links, a program status board, or
an implementation plan. It is the complete product-and-solution model. A
reader must be able to understand the business, user experience, domain
boundaries, data authority, technical architecture, integration behavior,
security model, operating model, migration path, and release standard without
opening another artifact.

The master becomes the primary product and solution authority only after:

1. its deterministic source set is frozen;
2. every required source statement is extracted and dispositioned;
3. all completeness and consistency gates in this design pass;
4. independent read-only reviewers return zero Critical and zero Important
   findings on one exact candidate;
5. the required team roles sign that exact candidate; and
6. the Control Register records the approval, exact commit, file hash, source
   inventory hash, and authority activation.

Until that sequence completes, the existing approved product baseline,
technical roadmap, accepted ADRs, later durable owner decisions, approved
program designs, and verified implementation evidence retain their present
authority. This design does not create or activate the master.

---

## 2. Purpose, readers, and comprehension standard

### 2.1 Purpose

The master must:

- state the product promise and complete-launch boundary;
- distinguish evidence-backed current reality from the approved target;
- make every system-of-record and write-direction boundary explicit;
- explain the commercial model, roles, surfaces, modules, verticals,
  workflows, integrations, data, security, and operations at useful business
  and engineering depth;
- show how P00–P19 and mandatory work packages move the current system to the
  target;
- preserve the effect of all approved decisions without preserving obsolete
  contradictions;
- expose every genuinely open decision through a governed register;
- provide stable identifiers and traceability for later designs, plans,
  tests, runbooks, support material, and release evidence; and
- remain accurate through a controlled review and revision lifecycle.

### 2.2 Required readers

The document is written simultaneously for:

- owner and executive decision makers;
- product, commercial, marketing, sales, and customer-success teams;
- UX, content, localization, and accessibility teams;
- architects, backend, frontend, Frappe, data, integration, and quality
  engineers;
- security, privacy, compliance, reliability, and operations reviewers;
- implementation planners and work-package owners;
- merchant-support and Dorzak-managed-delivery teams; and
- independent reviewers who must verify completeness and authority.

Business readers receive plain-language outcomes, actors, promises, journeys,
limits, and ownership. Engineering readers receive concrete boundaries,
states, records, interfaces, failure behavior, data rules, and verification.
Neither audience is sent elsewhere for the meaning of a requirement.

### 2.3 Link-free comprehension

The master may include repository-relative source references for auditability,
but no link is required reading. Every acronym, role, system, plan, state,
invariant, workflow, table, and diagram is explained where it is used or in a
self-contained glossary. References prove provenance; they do not carry
missing content.

The link-free comprehension test is passed only when a reviewer can read a
rendered copy with links disabled and still explain:

- the product and commercial promise;
- the current and target system;
- who may do what;
- which system owns each fact;
- how every major journey succeeds, fails, and recovers;
- how data is isolated and protected;
- how the system is operated;
- how the current product reaches the complete-launch target; and
- which decisions remain open, deferred, or superseded.

---

## 3. Authority, status, and conflict model

### 3.1 Authority boundary

Authority depends on the question:

| Question | Authority after master activation |
|---|---|
| What product and solution must Dorzak deliver? | The team-approved master |
| What is currently authorized, active, blocked, complete, or next? | The Control Register |
| What has actually been implemented? | Code, migrations, schema, configuration, tests, and exact-SHA evidence |
| How is an approved program implemented? | Its approved design and implementation plan, within the master and Control boundaries |
| Why was a durable architecture choice made? | The accepted ADR, as incorporated by the master |
| What did an older document once propose? | Historical evidence only, unless the master explicitly preserves it |

The master never grants a writer lease, starts a program, changes task status,
approves a provider action, or proves that code exists. The Control Register
remains the only execution-status and authorization writer. Code and schema
remain implementation truth, but an implementation difference is recorded as
a current-state gap; it does not silently rewrite the approved target.

### 3.2 Activation lifecycle

The master has these lifecycle states:

~~~text
ABSENT
  -> SOURCE-FROZEN CANDIDATE
  -> REVIEWED CANDIDATE
  -> TEAM-APPROVED ACTIVE MASTER
  -> CONTROLLED REVISION CANDIDATE
  -> REVISED ACTIVE MASTER
  -> SUPERSEDED VERSION
~~~

Only TEAM-APPROVED ACTIVE MASTER and REVISED ACTIVE MASTER are primary
product/solution authority. A candidate can guide review but cannot override
an approved source. A superseded version is historical and names the exact
replacement.

### 3.3 Source hierarchy for initial assembly

The production plan must freeze exact commits and hashes for every source.
Within that frozen set, use this precedence:

1. exact later owner decisions durably recorded by the Control Register or an
   approval record, limited to their stated scope;
2. the approved complete-launch product baseline for product requirements;
3. the approved technical roadmap together with accepted ADRs and CONTEXT.md
   for architecture and engineering rules;
4. approved program and work-package designs and approved errata for their
   bounded scope;
5. approved implementation plans for executable detail that does not alter a
   higher-level decision;
6. verified code, schema, tests, runbooks, and exact-SHA evidence for current
   implemented reality;
7. older plans, supplied repositories, research, and source audits only where
   a higher authority explicitly preserves them.

After activation, the master is first for product/solution questions. Later
owner decisions do not silently edit it: they enter the revision process and
become effective product/solution authority in the master when the controlled
revision is approved. The Control Register can still impose a newer execution
pause or narrower lease immediately because execution authority is separate.

### 3.4 Deterministic conflict resolution

For every apparent conflict, the extraction ledger records both statements and
applies this order:

1. determine whether the statements concern the same field, behavior, state,
   country, plan, surface, release, or time period;
2. prefer a later explicit owner decision over an earlier source only inside
   the later decision's exact scope;
3. otherwise prefer the higher source level in Section 3.3;
4. within one source level, do not infer precedence from date alone; a later
   artifact wins only when it explicitly supersedes the earlier statement;
5. treat verified code disagreement as a current-to-target gap, not as a
   target decision;
6. preserve a lower-level detail only when it is compatible with every higher
   boundary;
7. label an obsolete statement Superseded and name its replacement;
8. if authority or intended behavior is still ambiguous, create a governed
   Open decision; and
9. stop approval when the unresolved conflict affects product promise,
   commercial terms, tenancy, system of record, money, security, privacy,
   regulation, data migration, or release safety.

No writer resolves a substantive conflict through wording alone.

### 3.5 Decision status vocabulary

Every normative statement, domain, capability, matrix row, and decision uses
one of these statuses:

| Status | Meaning | Required treatment |
|---|---|---|
| Approved | A durable decision is accepted and controlling in its scope | State the rule, provenance, owner, and verification consequence |
| Planned | The target intent and program placement are accepted, but its bounded design or implementation is not yet complete | State the accepted outcome, dependencies, owning program, and later approval gate |
| Open | A real decision is unresolved | Use the complete Open-decision record below; never infer an answer |
| Deferred | The capability or choice is deliberately outside the current complete-launch target or postponed to a named later gate | State why, the earliest reconsideration gate, and what remains prohibited |
| Superseded | A prior statement is replaced | State the replacement ID and prohibit use as current guidance |

The status words are exact. Synonyms such as decided, mostly approved, in
progress, likely, and future-ready are not substitutes.

### 3.6 Current, target, and out-of-scope axes

Decision status and solution state are separate:

- **Current** means directly evidenced at the master baseline commit and date.
- **Target** means part of the complete-launch solution, with Approved or
  Planned status.
- **Out of scope** means not part of the complete-launch promise. It normally
  carries Deferred status or is an explicitly rejected alternative.
- **Historical** is allowed only for Superseded evidence and is never presented
  as current or target behavior.

Every domain includes a current-versus-target table with current evidence,
target rule, gap, transition owner, program, acceptance gate, and public-claim
effect. Existing but non-target behavior is marked Current plus Superseded,
with the migration or removal path. A Planned target is not described as
implemented. An Open option is not described as the target.

Legal combinations are:

| Decision status | Permitted solution-state treatment |
|---|---|
| Approved | Current when independently evidenced, Target when controlling, or Out of scope when the approved rule is an exclusion |
| Planned | Target only; never Current without implementation evidence |
| Open | No solution-state assignment; record the affected state and option states without selecting one |
| Deferred | Out of scope only, with a named reconsideration gate |
| Superseded | Historical, or Current only while evidenced legacy behavior awaits removal or migration |

### 3.7 Open-decision policy

Open decisions are the only permitted form of intentionally unresolved
content. Each record contains:

- stable decision ID and precise question;
- decision status Open;
- affected domains, requirements, plans, countries, and surfaces;
- known options and material trade-offs;
- source statements that created the question;
- decision owner and required consulted roles;
- safe interim rule that fails closed and makes no new public promise;
- exact decision deadline expressed as a program, design, commercial,
  provider, legal, or release gate;
- implementation and launch work blocked by the decision;
- evidence required to decide; and
- the master sections and matrices that must change after resolution.

An Open decision may remain in an active master only when its safe interim rule
preserves every approved promise and blocks the affected execution. An Open
decision affecting launch scope, advertised capability, price, system of
record, tenant isolation, money, sensitive data, legal compliance, migration
safety, or release acceptance blocks team approval of the master.

---

## 4. Five-part master architecture

The future master uses exactly five top-level parts. Appendices belong to Part
V and cannot become a sixth independent authority layer.

### Part I — Authority, product, and present-to-target orientation

Part I establishes:

- document identity, version, baseline commit/date, target release, lifecycle,
  team approval, and revision history;
- authority boundary with Control and implemented truth;
- source hierarchy and conflict rules;
- status vocabulary and Open-decision policy;
- plain-language executive product decision, goals, non-goals, one-complete-
  launch policy, initial countries, required languages, and product promise;
- glossary and stable identifier conventions;
- current system summary, target system summary, and an explicit gap map;
- actors, organizations, locations, customer relationships, Dorzak roles, and
  external system actors; and
- the five-part reading map.

### Part II — Business, commercial, experience, and vertical solution

Part II explains:

- Free Tools, Pro, Business, and Enterprise, immutable plan publication,
  entitlements, limits, add-ons, trials, billing, upgrade and retirement;
- merchant categories, plan progression, sensitive-capability activation, and
  category-versus-plan behavior;
- every supported vertical and General Business;
- corporate site, acquisition, pricing, signup, onboarding, Free Tools, Our
  Clients, merchant websites, storefront, customer mobile experience, and
  page builder;
- merchant, customer, staff, professional, guardian, learner, patient, member,
  donor, volunteer, beneficiary, Dorzak staff, and provider journeys;
- design system, content, localization, accessibility, motion, and performance
  promises; and
- analytics, value proof, marketing, CRM, loyalty, communications, Work,
  managed delivery, and shipping.

### Part III — Domain, authority, data, workflow, and integration solution

Part III contains:

- the exhaustive domain catalogue in Section 5;
- the uniform domain specification in Section 6;
- organization, location, Party, identity, consent, role, policy, execution
  context, and merchant-local customer account rules;
- the field-level system-of-record and write-direction matrix;
- Dorzak-native, ERPNext-owned, projected, provider-owned, and derived data;
- workflow and state-machine definitions, including success, failure,
  compensation, reconciliation, and recovery;
- ERPNext fleet, dorzak_core, provider adapter, event, webhook, idempotency,
  outbox, and projection contracts;
- commerce cutover, legacy history, migration, rollback deadline, and
  forward-recovery rules; and
- data classification, purpose, retention, residency, deletion, export,
  lineage, quality, and audit.

### Part IV — Technical, security, reliability, and operating solution

Part IV defines:

- deployment units and the allowed ownership of each;
- Laravel modular-monolith modules, merchant React application, separate
  public/customer surface, isolated merchant ERPNext sites, separate
  Frappe-native Superadmin site, dorzak_core, and external adapters;
- repository evolution and interface boundaries;
- tenancy, host, cookie, cache, credential, signing-key, and network isolation;
- authentication, authorization, sensitive-capability, privacy, security,
  financial, inventory, and audit controls;
- money, concurrency, scheduling, provisioning, publication, webhook,
  idempotency, and distributed-system rules;
- availability, recovery, backups, restore, queues, observability, SLOs,
  capacity, performance, accessibility, support, incident response, and
  runbook ownership;
- software supply chain, version pins, source reuse, licenses, upgrades, and
  rollback; and
- environment and release topology without exposing secrets.

### Part V — Delivery, evidence, governance, and evolution

Part V contains:

- P00–P19, WP-M2, WP-P04T, WP-P09A, and WP-P09B outcomes, dependencies,
  current state, target contribution, and acceptance evidence;
- milestones M0–M9 and the one-complete-launch gate;
- current-to-target migration and cutover sequence;
- requirement-to-domain-to-program-to-test-to-evidence traceability;
- plan-by-capability, category-by-plan, role-by-permission, source-of-truth,
  integration, data, country/language, and operational-readiness matrices;
- automated and human verification, operational rehearsal, defect and release
  gates;
- risk, assumption, Open, Deferred, and Superseded registers;
- deterministic source register and extraction/coverage summary;
- revision protocol, change log, team signoff, and authority activation; and
- a self-contained definition of done.

---

## 5. Exhaustive domain catalogue

The master must contain every domain below. A domain may be split for clarity,
but it may not be omitted or merged in a way that hides its authority,
workflow, data, security, or acceptance rules. A newly discovered domain is
added through source reconciliation and receives a stable ID.

| ID range | Domain | Minimum included concerns | Primary program placement |
|---|---|---|---|
| DOM-001 | Product authority and release policy | product promise, scope, one launch, statuses, decisions | Cross-program, P19 |
| DOM-002 | Organization, site, location, legal entity, and country | paid-organization boundary, locations, companies, country packs | P01, P02, P18 |
| DOM-003 | Identity, Party, contact, consent, and preference | staff and customer identity, verified channels, purpose and consent | P01 |
| DOM-004 | Authentication, sessions, OTP, and customer auto-account | merchant-local accounts, session safety, recovery, account linking | P01 |
| DOM-005 | Roles, authorization, execution context, and sensitive activation | actor, organization, location, plan, policy, evidence and fail-closed access | P01, P18 |
| DOM-006 | Commercial catalogue, plans, entitlements, limits, and add-ons | Free Tools, Pro, Business, Enterprise, immutable versions | P03 |
| DOM-007 | Trial, subscription, Dorzak billing, dunning, and refunds | lifecycle, invoices, payment-provider facts, account state | P03 |
| DOM-008 | ERPNext fleet and merchant-site lifecycle | provision, migrate, health, backup, restore, upgrade, retire | P02 |
| DOM-009 | dorzak_core, tenant routing, commands, projections, and reconciliation | constrained ERP contracts, mappings, versioning, drift repair | WP-M2, P02, P04 |
| DOM-010 | Idempotency, outbox, webhook, event, and saga coordination | exactly-once intent, retries, receipts, compensation, dead letters | WP-M2, P03, P04 |
| DOM-011 | Product and service catalogue | items, variants, UOM, operational prices, tax, presentation | P04, P07 |
| DOM-012 | Supplier, procurement, receiving, and landed cost | suppliers, purchase flow, credit, reconciliation | P04, P07 |
| DOM-013 | Inventory, warehouse, transfer, count, and valuation | canonical stock, batches, units, COGS, conflicts | P04, P07 |
| DOM-014 | Cart, quote, order, POS, invoice, receipt, return, and refund | drafts, canonical submission, fiscal effects, correction | P04, P07 |
| DOM-015 | Payment, settlement, wallet, gift card, and loyalty value | provider and ERP facts, redemption, fees, chargeback | P03, P04, P07, P14 |
| DOM-016 | Commercial customer and supplier account | legal/tax/accounting party projections, terms and balances | P01, P04 |
| DOM-017 | Offline POS | device identity, signed journal, provisional receipt, posting conflict | P04 |
| DOM-018 | Commerce migration, cutover, and legacy history | expand, backfill, parity, route, rollback deadline, forward recovery | P04 |
| DOM-019 | Corporate website and acquisition | navigation, pages, solutions, resources, SEO, proof and conversion | P05 |
| DOM-020 | Free Tools | catalogue, accounts, content, utility results, growth boundary | P05 |
| DOM-021 | Pricing, signup, onboarding, and activation | accurate manifest, checkout, provisioning progress, first value | P03, P05 |
| DOM-022 | Our Clients and case-study proof | consent, evidence, approval, expiry, publication controls | P05 |
| DOM-023 | Website and storefront builder | schema, editor, sections, themes, collections and plan progression | P06 |
| DOM-024 | Publication, domains, SEO, translations, and media | immutable release, preview, DNS/TLS, redirects, safe assets | P06, P18 |
| DOM-025 | Storefront, customer mobile shell, and checkout | merchant branding, catalog/service action, account and status | P05, P06, P07–P13 |
| DOM-026 | Design system, content, localization, accessibility, and motion | tokens, components, EN/FR/AR, RTL, budgets and semantics | P05, P06, P18 |
| DOM-027 | Analytics, value ledger, reporting, and instrumentation | definitions, provenance, freshness, plan scope, privacy | P14, P18 |
| DOM-028 | CRM, campaign, segmentation, referral, review, and attribution | eligibility, automation, consent, revenue mapping | P14 |
| DOM-029 | Communications and channel governance | email, SMS, WhatsApp, templates, consent, delivery and cost | P14, P18 |
| DOM-030 | Retail and shop | retail category journey and plan progression | P07 |
| DOM-031 | Supplier, wholesale, and B2B | contracts, ordering, credit, bulk and integration | P07 |
| DOM-032 | Restaurant, café, and food and beverage | menu, kitchen, table, dispatch, inventory and revenue center | P08 |
| DOM-033 | Shared scheduling and resource kernel | availability, holds, booking, waitlist, recurrence and conflicts | WP-P09A |
| DOM-034 | Appointments and professional services | service, practitioner, booking, completion and payment | WP-P09B |
| DOM-035 | Salon, coiffeur, beauty center, and spa | staff/resource scheduling, packages, membership and retail | WP-P09B |
| DOM-036 | Healthcare | patient purpose, appointment/clinical boundary, credentials and country gate | P10 |
| DOM-037 | Education and school | learner, guardian, admission, timetable, attendance, fees and minors | P11 |
| DOM-038 | Gym and fitness | membership, class, trainer, access boundary and safe health scope | P12 |
| DOM-039 | Nonprofit | donor, volunteer, program, application, disbursement and beneficiary safety | P13 |
| DOM-040 | General Business | inquiry, quote, engagement, invoice, payment and broad operations | M7 General Business slice |
| DOM-041 | Work, Gantt, projects, tasks, timesheets, and portfolio | ERP core, Dorzak extensions, plan progression and collaboration | P15 |
| DOM-042 | Dorzak-managed delivery | request, scope, approval, workspace, change, acceptance and support | P15 |
| DOM-043 | Shipping, parcel, delivery, and fulfillment | rates, labels, shipments, events, providers and exceptions | P16 |
| DOM-044 | Internal Superadmin control plane | organization, commercial, fleet, content, release and health control | P17 |
| DOM-045 | Delegated intervention, support, and incident control | reason, grant, time bound, audit, redaction and revocation | P17, P18 |
| DOM-046 | Data architecture and governance | ownership, classification, lineage, retention, residency, export and deletion | Cross-program, P18 |
| DOM-047 | Security, privacy, and trust | identity, authorization, encryption, secrets, threats and sensitive data | Cross-program, P18 |
| DOM-048 | Country, language, regulatory, and legal packs | Qatar, Tunisia, EN/FR/AR, taxes, privacy and sector activation | P18 |
| DOM-049 | Reliability, performance, capacity, backup, and recovery | targets, queues, restore, degradation, budgets and ownership | Cross-program, P18, P19 |
| DOM-050 | Audit, observability, operations, support, and runbooks | events, metrics, logs, traces, alerts, incidents and operator action | Cross-program, P17–P19 |
| DOM-051 | External adapters and integration governance | payment, messaging, storage, scanning, delivery and approved systems | WP-M2, P03, P16, P18 |
| DOM-052 | Source reuse, licenses, supply chain, and upgrades | audit, version, isolation, notices, security and rollback | Cross-program, P18 |
| DOM-053 | Quality, migration rehearsal, evidence, and release | tests, matrices, defects, signoffs, release and rollback | P00, P19 |

### 5.1 Mandatory actor and role inventory

The master must map at least:

- visitor, prospect, Free Tools user, trial user, and merchant customer;
- merchant owner, organization administrator, location manager, cashier/POS
  operator, inventory user, purchasing user, finance user, website editor,
  marketer, support user, analyst, project manager, team member, and auditor;
- restaurant, appointment, beauty, healthcare, education, gym, nonprofit, and
  General Business specialist roles;
- patient, guardian, learner, member, donor, volunteer, beneficiary, and
  authorized representative;
- Dorzak owner, Superadmin, support agent, incident responder, commercial
  operator, content operator, fleet operator, managed-delivery member,
  security/privacy reviewer, and release approver;
- ERPNext/dorzak_core service actor, payment provider, messaging provider,
  storage/scanner, delivery carrier, and scheduled/queue worker.

Each role maps to organization/location scope, allowed surfaces, permissions,
sensitive purposes, required grants, plan/category limits, audit obligations,
and prohibited actions.

### 5.2 Mandatory module inventory

The master names the owner, interfaces, dependencies, authoritative data, and
failure behavior of at least these deep modules:

- Tenant and Site Router;
- Identity, Party, Role, Consent, and Preference;
- Capability and Commercial Policy;
- Provider-neutral Payment Kernel;
- Scheduling and Resource Kernel;
- Workflow, Approval, and Version Kernel;
- ERP Command, Mapping, Projection, and Reconciliation Gateway;
- Media and Document Safety;
- Communications and Channel Governance;
- Audit, Observability, and Evidence;
- Website Publication and Domain Routing;
- Data Migration and Cutover; and
- Superadmin Grant and Intervention.

Repository folders do not define a domain. Domains and modules are defined by
business authority, invariants, records, interfaces, and change ownership.

---

## 6. Uniform per-domain specification

Every DOM entry uses the same ordered template:

1. **Identity and outcome:** stable ID, name, one-sentence business outcome,
   owning team role, and primary programs.
2. **Status and scope:** Approved, Planned, Open, Deferred, or Superseded;
   Current, Target, Out of scope, or Historical; plans, countries, languages,
   categories, surfaces, and exclusions.
3. **Actors and permissions:** human and system actors, organization/location
   scope, grants, duties, and prohibited actions.
4. **Current reality:** exact baseline evidence, implemented capabilities,
   limitations, known debt, and current authority.
5. **Target behavior:** complete-launch capabilities and business rules,
   including plan and vertical variation.
6. **Invariants:** rules that may never be violated and the fail-closed result.
7. **Workflows and states:** entry conditions, happy path, state machine,
   cancellation, conflict, timeout, retry, compensation, recovery, and final
   states.
8. **Data and authority:** entities, key fields, writer, projections, derived
   values, identifiers, classification, purpose, retention, residency,
   deletion/export, and lineage.
9. **Interfaces and events:** commands, queries, APIs, events, webhooks,
   idempotency, versions, consistency, freshness, provider boundaries, and
   error contracts.
10. **Security, privacy, and audit:** threats, authentication, authorization,
    isolation, sensitive activation, encryption/secrets, audit events, and
    abuse controls.
11. **Experience:** merchant, customer, internal and provider surfaces;
    responsive behavior; EN/FR/AR and RTL; accessibility; content; and
    performance.
12. **Operations:** SLOs, capacity, queues, observability, alerts, support,
    backup/restore, incident response, and runbooks.
13. **Dependencies and ownership:** upstream/downstream domains, serialized
    registries, program, work package, milestone, and approval gates.
14. **Current-to-target transition:** gap, expand/backfill/parity/cutover or
    additive path, reconciliation, rollback boundary, and public-claim effect.
15. **Verification:** requirement IDs, automated tests, matrices, manual
    checks, evidence, defect threshold, and signoff roles.
16. **Decisions and provenance:** controlling source IDs, approved decisions,
    Open records, Deferred items, and Superseded replacements.

An inapplicable field says Not applicable and gives the domain reason. Empty
fields, implicit inheritance, and prose that merely says to consult another
document fail validation.

---

## 7. Required embedded matrices, flows, and diagrams

### 7.1 Matrices

The master embeds, at minimum:

1. authority-by-question matrix;
2. current-versus-target gap and transition matrix;
3. actor/role-by-surface-by-permission matrix;
4. organization/location/site/company mapping matrix;
5. field-family system-of-record and allowed-write-direction matrix;
6. plan-by-capability, plan-floor, limit, and upgrade-value matrix;
7. category-by-plan-by-primary-journey matrix;
8. sensitive-capability-by-country-by-activation-evidence matrix;
9. domain-by-module-by-program-by-milestone matrix;
10. integration-by-owner-by-credential-by-data-by-failure matrix;
11. event/webhook-by-idempotency-by-reconciliation matrix;
12. data-classification-by-purpose-by-retention-by-residency-by-export matrix;
13. surface-by-language-by-accessibility-by-performance-budget matrix;
14. P00–P19 and work-package dependency/entry/exit matrix;
15. requirement-by-domain-by-program-by-test-by-evidence matrix;
16. risk-by-control-by-owner-by-detection-by-recovery matrix;
17. Open/Deferred/Superseded decision registers; and
18. release gate and human-signoff matrix.

No matrix cell may conceal a material variation behind a generic qualifier.
Large matrices may be divided by domain, but stable row IDs and complete
cross-references are preserved.

### 7.2 Flows and diagrams

The master embeds text or renderable diagrams for:

- the five-part solution and deployment-unit boundaries;
- authenticated execution-context resolution and fail-closed tenant routing;
- visitor-to-plan-to-payment-to-provisioned-paid-organization activation;
- merchant/customer command-to-Dorzak-to-dorzak_core-to-ERPNext-to-projection
  flow;
- Dorzak-native workflow with ERP financial or stock consequence;
- offline POS provisional-to-canonical posting and conflict recovery;
- ERP fleet provisioning, failure, recovery, backup, upgrade, and retirement;
- website draft-to-approval-to-immutable-publication and rollback;
- existing-commerce prepare-to-cutover-to-forward-recovery;
- Superadmin visibility, time-limited intervention, audit, and revocation;
- event/webhook ingestion, idempotency, retry, dead-letter, and reconciliation;
  and
- M0-to-M9 delivery and one-complete-launch release.

Adjacent prose defines every node, edge, state, owner, and failure path.
Diagrams illustrate requirements; they never contain the only copy of one.

---

## 8. Production and implementation-planning model

### 8.1 One writer, parallel read-only coverage

Exactly one designated writer may edit the future master and its candidate
branch. All other workers are read-only reviewers. Parallel work is permitted
only for source extraction verification, coverage analysis, contradiction
review, and specialist review against an exact frozen candidate.

Read-only workers return structured findings and coverage evidence to the
writer. They do not patch the master, edit supporting evidence, generate a
competing candidate, stage files, or make product decisions. The writer
applies accepted corrections serially and produces a new exact candidate for
re-review.

### 8.2 Required implementation-plan phases

A later, separately authorized implementation plan must contain these phases
in order.

#### Phase 0 — Entry and authority freeze

- verify the Control Register revision and exact lease;
- verify the future path is absent or names the exact approved revision base;
- capture branch, HEAD, worktrees, status, protected state, and writer
  allowlist;
- bind the accepted implementation/evidence SHA used for Current statements;
- enumerate required signoff roles and read-only review lenses; and
- stop if any identity, scope, or authority differs.

#### Phase 1 — Deterministic source inventory

Create a stable, sorted inventory containing for every source:

- source ID, authority level, lifecycle, effective date, and decision scope;
- repository path, exact commit, blob hash, and file hash;
- controlling headings or durable decision record;
- whether it describes Current, Target, Out of scope, or Historical state;
- supersession relationship and bounded exceptions;
- extraction owner and review owner; and
- availability, with an explicit Not created state for later program artifacts
  that do not yet exist.

The inventory includes the approved 36-area product baseline, approved
technical roadmap, approved P00 design, approved P00 database-safety erratum,
approved P00 implementation plan, accepted P00 execution evidence, CONTEXT.md,
ADRs 0001–0007, later durable owner decisions, available P01–P19 and
work-package artifacts, and exact-SHA code/schema/test/runbook evidence. It is
hashed and frozen before extraction. A source change invalidates the inventory
and returns the task to the freeze gate.

#### Phase 2 — Extraction ledger

Extract each normative statement into one ledger row with:

- source ID and exact heading/record coordinate;
- source wording classification as requirement, decision, current fact,
  constraint, non-goal, risk, acceptance gate, or evidence;
- normalized statement without weakening;
- status and current/target/out-of-scope axis;
- affected domain, actor, role, plan, category, country, language, surface,
  integration, data family, and program;
- authority owner and system-of-record effect;
- duplicate/conflict group;
- disposition as preserve, consolidate, supersede, defer, or Open;
- destination master section and stable master ID; and
- reviewer verdict.

Every source statement receives a disposition. Consolidation points multiple
ledger rows to one complete master statement; it never deletes traceability.

#### Phase 3 — Bidirectional coverage matrix

Build both directions:

- every source statement maps to a master section or explicit disposition; and
- every normative master statement maps to an approved source, verified
  Current evidence, or a clearly labeled Open synthesis record.

The matrix includes the 36 PRD areas, all roadmap milestones and architecture
rules, P00–P19, mandatory work packages, ADRs, later owner decisions, all
catalogue domains, roles, modules, verticals, workflows, integrations, data
families, security controls, and operating responsibilities.

#### Phase 4 — Draft assembly

- instantiate the exact five-part skeleton and stable IDs;
- write executive and business explanations from the same ledger as technical
  sections;
- populate every domain through the uniform template;
- insert required matrices, flows, glossary, source register, decision
  registers, and definition of done;
- keep one canonical statement for each rule and use internal references for
  repeated application;
- distinguish Current evidence, Target decision, and Out-of-scope prohibition
  at every boundary; and
- make the rendered document understandable without links.

#### Phase 5 — Deterministic validation

Run mechanical and semantic checks for:

- exact path, file mode, heading structure, five parts, stable unique IDs, and
  one entry for every required domain;
- complete extraction and bidirectional coverage;
- no unfinished markers, empty cells, incomplete sections, or ambiguous
  qualifiers;
- exact status vocabulary and legal status/state combinations;
- terminology consistency, glossary completeness, acronym expansion, and one
  canonical name per concept;
- duplicate or contradictory normative statements;
- link targets and link-free comprehension;
- source-of-truth, tenant, money, sensitive-data, and release invariants;
- Current statements tied to the accepted evidence SHA;
- all Open records complete and no approval-blocking Open decision;
- no secret, personal-data, credential, or unsafe environment content; and
- clean diff and exact writer allowlist.

#### Phase 6 — Independent review and correction

Dispatch independent read-only reviewers against one exact clean candidate and
source-inventory hash. Findings use Critical, Important, and Minor severity,
include section/ID, source evidence, consequence, and precise acceptance
condition. Critical and Important findings require correction and fresh
review. Minor findings are corrected or explicitly accepted in the signoff
record without weakening a requirement.

#### Phase 7 — Team handoff and activation

The handoff packet records:

- candidate commit and parent;
- master path, mode, line count, file hash, and diff identity;
- source-inventory and extraction-ledger hashes;
- coverage counts and zero-gap result;
- validation commands and results;
- independent reviewer identities, lenses, exact candidate, and findings;
- complete Open/Deferred/Superseded registers;
- required team-role signoffs on the same bytes; and
- explicit statement that master approval grants no execution authority.

Control verifies and records activation. The writer then stops. Planning or
execution requires a separate Control transition.

---

## 9. Completeness and acceptance gates

### 9.1 All 36 product-baseline areas

The coverage matrix must contain an independently verified row for every area:

| PRD area | Required master treatment |
|---|---|
| 1. Document authority and supersession | Part I authority, hierarchy, replacements |
| 2. Executive product decision | Parts I–II product problem, solution, plan promise |
| 3. Goals, non-goals, launch boundaries | Part I scope and one-launch gate |
| 4. Actors and ownership boundaries | Parts I–III actor and role matrices |
| 5. Target architecture | Parts I, III, IV system shape and record authority |
| 6. ERPNext provisioning and lifecycle | Parts III–IV fleet states and operations |
| 7. Commercial catalogue and plans | Part II and commercial matrices |
| 8. Organization, location, Party, isolation | Parts I and III tenancy/data model |
| 9. Merchant-category framework | Part II category rules and sensitive activation |
| 10. Vertical requirements | Part II plus DOM-030 through DOM-040 |
| 11. Website, storefront, builder | Parts II–III publication and customer experience |
| 12. Free Tools | Part II and DOM-020 |
| 13. Corporate website | Part II and DOM-019 |
| 14. Our Clients | Part II and DOM-022 |
| 15. Design system | Parts II and IV, DOM-026 |
| 16. Merchant and customer experience | Part II journeys and surface architecture |
| 17. Superadmin control plane | Parts II–IV, DOM-044 and DOM-045 |
| 18. Subscriptions and payment processing | Parts II–III, DOM-007 and DOM-015 |
| 19. Shared deep modules | Parts III–IV module inventory |
| 20. Work and managed delivery | Part II, DOM-041 and DOM-042 |
| 21. Marketing, CRM, loyalty, communications | Part II, DOM-028 and DOM-029 |
| 22. Delivery and fulfillment | Parts II–III, DOM-043 |
| 23. Source reuse | Part IV, DOM-052 |
| 24. Localization and country packs | Parts II–IV, DOM-026 and DOM-048 |
| 25. Security, privacy, trust | Parts III–IV, DOM-047 |
| 26. Reliability, performance, operability | Part IV, DOM-049 and DOM-050 |
| 27. Analytics and instrumentation | Parts II–IV, DOM-027 |
| 28. Current-state gap assessment | Parts I and V current-target matrices |
| 29. Program decomposition | Part V program and dependency model |
| 30. Testing strategy | Parts IV–V verification model |
| 31. Launch acceptance and signoff | Part V release and signoff gates |
| 32. User stories | Parts II–III actor journeys and domain acceptance |
| 33. Risks and mitigations | Parts IV–V risk/control register |
| 34. Out of scope and future scope | Parts I, II, V Deferred register |
| 35. Definition of done | Part V self-contained complete-launch outcome |
| 36. Evidence and sources | Part V deterministic source and traceability register |

A row passes only when every normative subsection and stable requirement ID in
that area has a ledger disposition, not merely when a similarly named heading
exists.

### 9.2 Roadmap, programs, and work packages

The master must map:

| Delivery unit | Required target contribution |
|---|---|
| P00 | trustworthy baseline, quality, context, ADRs, runbooks and evidence |
| P01 | Organization, identity, Party, consent and fail-closed tenancy |
| WP-M2 | money, idempotency, outbox, audit, ERP and capability interface freeze |
| P02 | ERPNext platform core, dorzak_core, provisioning and fleet |
| P03 | immutable plans, subscriptions, pricing and regional billing |
| P04 | commerce cutover, projections, offline flow and reconciliation |
| WP-P04T | bounded first retail ERP integration tracer |
| P05 | corporate site, Free Tools, pricing, signup and Our Clients |
| P06 | builder, publication and custom-domain platform |
| P07 | retail, supplier/B2B and shared commerce |
| P08 | restaurant and food-and-beverage product |
| P09 | scheduling kernel, appointments, coiffeur and beauty |
| WP-P09A | shared scheduling and resource kernel |
| WP-P09B | appointments and beauty completion |
| P10 | healthcare Qatar/Tunisia pack |
| P11 | education/school product |
| P12 | gym/fitness product |
| P13 | nonprofit operating product |
| M7 General Business slice | General Business operating product |
| P14 | marketing, CRM, loyalty, communications and attribution |
| P15 | Work/Gantt and Dorzak-managed delivery |
| P16 | shipping, delivery and fulfillment |
| P17 | Frappe-native internal Superadmin control plane |
| P18 | language, country, security, privacy, accessibility and performance |
| P19 | integration, migration rehearsal, operations and complete release |

The dependency and milestone view covers M0–M9, planning entry, execution
entry, milestone exit, serialized owners, same-milestone parallelism, and
evidence. The master describes solution sequencing, not current authorization.

### 9.3 ADR and later-decision gate

ADRs 0001–0007 must each map to a complete master rule and verification
boundary:

1. system-of-record authority;
2. Organization/Location and isolated ERPNext tenancy;
3. modular monolith and external adapters;
4. one complete public launch;
5. immutable plan publication;
6. commerce cutover with no dual write; and
7. merchant, public/customer, and Frappe-native Superadmin surfaces.

Every later durable owner decision receives a source ID, scope, effective date,
affected master IDs, and conflict disposition. A later decision cannot be lost
because it appears only in Control history.

### 9.4 Cross-cutting catalogue gate

Coverage must be complete for:

- all actors and roles in Section 5.1;
- all modules in Section 5.2;
- Free Tools, Pro, Business, and Enterprise;
- Qatar and Tunisia, English, French, Arabic, and RTL behavior;
- every supported vertical and General Business;
- every material success, failure, compensation, reconciliation, migration,
  rollback, support, and release workflow;
- every external integration class and its provider-neutral boundary;
- every authoritative or projected data family;
- authentication, authorization, tenant isolation, money, inventory,
  sensitive-data, privacy, and audit controls; and
- provisioning, queues, observability, incidents, backups, restores,
  upgrades, support, capacity, performance, accessibility, and release
  operations.

### 9.5 Placeholder and incomplete-content gate

The only intentionally unresolved master content is a complete Open-decision
record under Section 3.7. Validation rejects empty headings or cells, bracketed
writing prompts, fill-later notes, indefinite future language, omitted matrix
values, unexplained ellipses, hidden incomplete comments, and generic
references to another document for required meaning. Not applicable is legal
only with a domain-specific reason.

### 9.6 Contradiction gate

There must be:

- one commercial hierarchy;
- one organization/site/location model;
- one owner for every field and fact;
- one target for each frontend/internal surface;
- one release policy;
- one current baseline stamp;
- one target statement per behavior;
- no long-lived dual write;
- no conflict between prose, matrices, flows, domain templates, glossary, and
  decision registers; and
- no silent conflict between the master and a higher source during candidate
  production.

### 9.7 Terminology and ambiguity gate

The glossary defines and consistently distinguishes at least Organization,
Location, legal Company, merchant, merchant customer, Party, identity,
principal, plan, PlanVersion, entitlement, capability, sensitive capability,
site, database, projection, authoritative owner, provider fact, Dorzak-native
domain, ERPNext-owned record, Superadmin, Dorzak teammate, grant, current,
target, planned, deferred, out of scope, milestone, program, work package,
evidence SHA, and public launch.

Each requirement has one reasonable interpretation. Numeric limits, plan
variations, country variations, owners, states, deadlines, and acceptance
conditions are explicit or governed by a complete Open record.

### 9.8 Link-free comprehension and Open-decision gate

A rendered, links-disabled candidate must pass the comprehension questions in
Section 2.3. Every source reference is then checked for traceability without
being needed for meaning. Every Open record must pass Section 3.7, and the
candidate must contain zero approval-blocking Open decisions. Deferred and
Superseded entries cannot be used to complete an active requirement.

### 9.9 Independent review lenses

At least these read-only lenses review the same exact candidate:

| Lens | Required focus |
|---|---|
| Product and commercial | promise, plans, categories, value, claims, journeys, scope |
| Domain and business operations | vertical completeness, workflows, roles, exception behavior |
| Architecture and integration | modules, deployments, authority, APIs, events, cutover |
| Data and migration | entities, writers, projections, lineage, retention, reconciliation |
| Security, privacy, and regulation | tenancy, access, sensitive purpose, trust, country obligations |
| UX, content, localization, accessibility | surfaces, EN/FR/AR, RTL, semantics, performance |
| Reliability and operations | SLOs, queues, fleet, observability, backup, incidents, support |
| Delivery and quality | P00–P19, matrices, tests, evidence, release and rollback |
| Authority and traceability | source hierarchy, decisions, statuses, conflicts, coverage |
| Link-free comprehension | complete understanding without external documents |

The same person may cover more than one lens only when independence from the
writer remains clear and every lens has a separate verdict.

---

## 10. Team approval, revision, and change control

### 10.1 Required signoff

Activation requires signatures by role on the same commit and file hash:

- Dorzak owner/product authority;
- business/commercial lead;
- product/domain lead;
- engineering/architecture lead;
- security/privacy lead;
- reliability/operations lead; and
- quality/release lead.

Where a regulated vertical is included, its named qualified reviewer also
signs the relevant country and sensitive-capability sections. A missing role
blocks activation. Signoff means the role accepts completeness and correctness
within its lens; it does not grant execution.

### 10.2 Revision rules

Every active master records semantic document version, approval date, commit,
file hash, current-evidence baseline SHA, source-inventory hash, and replacement
history. A revision:

1. begins with a durable change reason and bounded scope;
2. refreshes the deterministic source inventory;
3. identifies affected domains, matrices, flows, programs, tests, operations,
   public claims, and decisions;
4. uses one writer;
5. reruns full bidirectional coverage and all global invariants, even for a
   local wording change that could affect authority;
6. receives the necessary independent lenses and all Critical/Important
   finding corrections;
7. receives role signoff on one exact candidate; and
8. becomes active only when Control records the replacement.

The previous active master remains authority until replacement activation.
Emergency execution pauses belong in Control and do not require waiting for a
master revision.

---

## 11. Failure and stop rules

The production task stops without assumption, scope expansion, or parallel
editing when:

- the Control revision, source commit, inventory hash, current-evidence SHA,
  target path, branch, writer allowlist, protected state, or candidate identity
  differs from the approved entry;
- a required source is missing without an explicit Not created inventory row;
- a source changes after inventory freeze;
- a source statement lacks a ledger disposition or destination;
- a master statement lacks provenance or verified Current evidence;
- a product, authority, commercial, tenancy, data, security, regulatory,
  migration, or release contradiction remains;
- a required domain, role, module, vertical, workflow, integration, data
  family, program, matrix, flow, or PRD area is uncovered;
- the writer would need to invent a decision or weaken a requirement;
- an Open record lacks its safe interim rule, owner, evidence, or gate;
- an approval-blocking Open decision remains;
- current and target states are mixed or a Planned capability is described as
  implemented;
- understanding depends on an external link;
- terminology permits two reasonable interpretations;
- a reviewer mutates the candidate or a second writer appears;
- secret, credential, personal, clinical, child, beneficiary, payment, or
  unsafe environment data enters an artifact;
- Critical or Important findings remain; or
- required team roles do not sign the same bytes.

The worker preserves the candidate and evidence, records the exact blocker,
and returns to Control. It does not choose among unresolved options, edit a
source artifact, alter code, or proceed to planning or execution.

---

## 12. Deliverables

A separately authorized production effort must return:

1. the self-contained master at
   docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md;
2. a deterministic source inventory and hash;
3. a complete extraction ledger and hash;
4. bidirectional source/master coverage matrices with zero unexplained gaps;
5. deterministic structural, terminology, contradiction, safety, traceability,
   and link-free-comprehension validation results;
6. independent read-only review reports for every required lens;
7. the exact corrected candidate commit and file hash;
8. a team-signoff record bound to that exact candidate; and
9. a Control handoff stating whether activation prerequisites passed and
   reiterating that execution remains separately authorized.

The master itself contains the necessary product and solution content,
matrices, flows, decisions, source summary, and signoff identity. Supporting
inventory, ledger, validation, and review artifacts are audit evidence and
must never become required reading for comprehension. Their exact paths and
writer allowlists must be fixed by the later implementation plan and Control
lease.

---

## 13. Non-goals

This design and the future master do not:

- replace the Control Register as execution authority or program-status writer;
- claim that Planned target capability is implemented;
- replace code, migrations, schema, tests, runbooks, or exact-SHA evidence as
  implemented truth;
- authorize P00 resumption, Task 16, P01–P19, P17, provider action, deployment,
  provisioning, migration, or release;
- create a file-by-file implementation plan or assign writer leases;
- select an unresolved provider, framework, application manifest, price,
  regulatory interpretation, or other Open choice by inference;
- copy uncontrolled source material or make supplied repositories writable;
- expose secrets, credentials, personal data, or raw merchant records;
- become a collection of links, a duplicate task register, a marketing-only
  narrative, or an engineering-only architecture document;
- preserve superseded Free/Pro/Scale, location-minimum, optional-ERPNext,
  partial-launch, or ambient-Superadmin assumptions;
- reopen approved decisions merely because an older source disagrees; or
- permit public claims before the one-complete-launch gate.

---

## 14. Design completion gate

This design specification is complete when it:

- fixes the future path and self-contained purpose;
- separates product/solution, execution, and implementation authority;
- defines source precedence, conflict resolution, statuses, current/target/
  out-of-scope semantics, and Open decisions;
- defines the five-part master, exhaustive domain catalogue, uniform domain
  template, and required embedded matrices and flows;
- defines one-writer production with parallel read-only coverage;
- requires deterministic inventory, extraction, coverage, assembly,
  validation, independent review, and team handoff;
- covers all 36 PRD areas, roadmap and P00–P19, work packages, ADRs, later
  owner decisions, roles, modules, verticals, workflows, integrations, data,
  security, and operations;
- defines contradiction, completeness, terminology, link-free comprehension,
  revision, signoff, failure, and stop gates;
- states deliverables and non-goals without granting later authority; and
- contains no intentionally unfinished content outside the governed
  Open-decision policy.

After this exact design is committed and returned to Control, work stops. A
new Control Register transition is required before a separate task may write
an implementation plan or create the future master.

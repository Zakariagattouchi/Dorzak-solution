# Dorzak Launch — Control Room Handoff

**Purpose:** Compact, durable memory for a fresh Dorzak Launch Control Room. Read this file instead of replaying the long planning conversation.

**Prepared:** 14 July 2026, Asia/Qatar

**Repository:** `/Users/barsha/Documents/recover Kyte`

**Branch at handoff:** `feat/premium-features`

**Important:** This is a navigation and decision handoff. It does not replace the linked Product Requirements Document, technical roadmap, Control Register, approved designs, or exact implementation plans. If status differs anywhere, the [Control Register](../superpowers/control/README.md) is the sole lifecycle and authorization authority.

---

## 1. Fresh-session start order

Read only these files first:

1. [This handoff](./CONTROL_ROOM_HANDOFF.md) — compact project memory and protected state.
2. [Control Register](../superpowers/control/README.md) — current authorization, task ownership, blockers, and next permitted action.
3. [Complete-launch product baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md) — what the complete product must contain.
4. [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md) — architecture, P00–P19 sequence, quality gates, and milestone evidence.
5. [Session orchestration design](../superpowers/specs/2026-07-14-dorzak-session-orchestration-design.md) and [lean working method](./WORKING_METHOD.md) — how to divide tasks without long, confusing sessions.
6. Only then read the active work package's approved design, exact approved plan, and evidence.

Do not treat chat memory, an old draft, an untracked file, or a subagent report as authority.

---

## 2. Exact handoff state

### Repository and protected user work

- Plan-correction HEAD: `c3db796d11f50f3ce0f75fe7389d0225c271cd22`.
- Parent: `4442944d8f6162a17cc22d6e5b22ef7a26f63c45`.
- Current branch: `feat/premium-features`.
- The checkout is deliberately dirty with user-owned work. Do not stage, reset, delete, overwrite, format, move, or absorb any of it without exact owner authorization.
- Protected manifest: 16 path-status entries; SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.

Protected entries at the handoff boundary:

~~~text
 M backend/app/Support/MediaUrl.php
 M docs/superpowers/plans/2026-07-12-marketing-00-overview.md
?? .agents/
?? .claude/skills/
?? agent/
?? docs/DORZAK_MERCHANT_SCALE_PLAN_PRD.md
?? docs/source-audits/
?? docs/superpowers/plans/2026-07-13-work-00-overview.md
?? docs/superpowers/plans/2026-07-13-work-01-foundation-pro.md
?? docs/superpowers/plans/2026-07-13-work-02-business-timeline.md
?? docs/superpowers/plans/2026-07-13-work-03-superadmin-managed-delivery.md
?? docs/superpowers/plans/2026-07-13-work-04-enterprise-planning.md
?? docs/superpowers/plans/2026-07-13-work-05-enterprise-integrations.md
?? docs/superpowers/plans/2026-07-13-work-06-localization-launch.md
?? outputs/
?? skills-lock.json
~~~

The `MediaUrl.php` modification is especially protected. P00 execution cannot start until the owner chooses its preservation method and the preserved result is independently verified and recorded with an exact patch or commit hash.

### P00 result

- [Approved P00 written design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md): commit `ea7b8258083231c6a9b7aa7c00d89009e29e696e`; SHA-256 `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`.
- [Proposed database/E2E safety erratum](../superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md): commit `59defd5dd36410d487679250c05d2be1d828c094`; SHA-256 `7af33dd41be2dca4490d512118d86dc14aa48f822a5051215927c88a66cd6024`; **Awaiting owner approval**.
- [Current provider-neutral P00 correction base](../superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md): commit `c3db796d11f50f3ce0f75fe7389d0225c271cd22`; SHA-256 `eecb96af875803f243c7694289526b34fdf0678a8792411fe4f1f41334d83444`; technically reviewed, but **not yet the provider-complete/P17-consistent exact approval artifact**.
- [Final implementation-plan review](../superpowers/control/reviews/2026-07-14-p00-implementation-plan-review.md): three independent exact-byte reviews plus seal checks found **0 Critical and 0 Important findings**.
- Plan measurements: 7,869 lines, 18 ordered tasks, 114 checklist items, and 308 balanced fence delimiters. Embedded changed PHP and shell implementations passed `php -l` and `bash -n`; Git whitespace/diff checks passed.
- Important safety corrections now covered: fixed qualification modes, a unique create-only database candidate per packet, candidate-bound activation nonce, exact live PDO guards, post-bootstrap and application connection verification, reconnect refusal, correct evidence branch handling, typed non-secret orphan reporting, and bounded CI/evidence flow.
- The current provider-neutral P00 correction is technically clean. Its own Task 14 hard stop still mandates a provider-native amendment, fresh exact review and owner approval. The plan/roadmap also retain a superseded shared React Superadmin assumption that must be reconciled to the approved separate Frappe-native P17 decision in the same focused amendment. **P00 implementation has not started and is not authorized**.

### Remaining P00 gates

All must be durable before an execution task exists:

1. Owner approves the exact safety erratum and complete-launch product baseline.
2. Establish the owner-selected canonical remote/integration reference and CI provider.
3. Pin exact PHP, Node.js, Composer, npm and relevant service/tool versions.
4. Authorize one focused documentation-only amendment containing the complete provider-native Task 14 adapter, required status and two-run normalizer **and** reconciling roadmap/plan Superadmin wording to the approved separate Frappe-native P17 direction.
5. Independently review the amended roadmap/plan exact bytes to zero Critical and zero Important findings.
6. Owner formally approves the amended technical roadmap and exact amended plan commit/SHA. Approval of `c3db796d…` alone cannot survive the required amendment and is not execution authority.
7. Owner selects the `MediaUrl.php` preservation method; preserve, test, review and record exact evidence.
8. Record approved `BASE_SHA`.
9. Create a clean, named, isolated P00 execution worktree from that exact base.
10. Owner explicitly authorizes P00 execution.

Until then: no application/test/configuration/CI/dependency changes, no installs, no `MediaUrl` action, no P01 work, and no public release.

---

## 3. Canonical authority and controls

| Question | Authoritative artifact | State at handoff |
|---|---|---|
| What may happen now? | [Control Register](../superpowers/control/README.md) | Control/handoff administration only |
| What must the product become? | [Complete-launch baseline v1](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md) | Awaiting formal owner approval; commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`; SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2` |
| How will it be built? | [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md) | Awaiting focused P17 reconciliation before formal owner approval; current commit `069f4833190c75866494e7ba51bff3021070c0bf`; SHA-256 `e9aa2c7970f9edf08f03177458cb496f979a30dbf3cf7fd96480c0c3b9a5cc60` |
| How are tasks and sessions controlled? | [Session orchestration design](../superpowers/specs/2026-07-14-dorzak-session-orchestration-design.md) | Approved at `069f483` |
| What is the human navigation entry? | [Dorzak Launch Hub](./README.md) | Active |
| What are the lean-session rules? | [Working method](./WORKING_METHOD.md) | Active |
| What exactly governs P00? | [P00 design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md), [erratum](../superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md), [plan](../superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md), [review](../superpowers/control/reviews/2026-07-14-p00-implementation-plan-review.md) | Design approved; erratum awaits owner; technically reviewed provider-neutral base awaits required provider/P17 amendment, fresh review and exact approval; execution prohibited |
| What governs internal Superadmin architecture? | [P17 Frappe-native Superadmin owner decision](../superpowers/control/approvals/2026-07-14-p17-frappe-native-superadmin-owner-decision.md) | Product direction approved at `ec6989f095377118a12aaab5a63f0ed4bed00f33`; P17 planning/execution not authorized |

P00 approval/control trail:

- [P00 proposal](../superpowers/control/proposals/2026-07-14-p00-baseline-stabilization-proposal.md)
- [P00 proposed-design approval](../superpowers/control/approvals/2026-07-14-p00-proposed-design-approval.md)
- [P00 written-specification approval](../superpowers/control/approvals/2026-07-14-p00-written-specification-approval.md)
- [P00 plan-writing-only exception](../superpowers/control/approvals/2026-07-14-p00-plan-writing-exception-approval.md)
- [P00 written-specification review](../superpowers/control/reviews/2026-07-14-p00-written-spec-review.md)
- [P00 implementation-plan review](../superpowers/control/reviews/2026-07-14-p00-implementation-plan-review.md)

If an artifact conflicts with a higher authority, stop and reconcile it through the Control Register. An approval never implies the next approval.

---

## 4. Project direction in plain language

Dorzak is one multi-vertical merchant SaaS. A business gets a mobile-first branded website for its customers and a desktop-first management application for its team. Every paid business also gets an invisible, isolated ERPNext/Frappe operational core. Dorzak presents one seamless product; ordinary merchants and customers do not log in to ERPNext or know that unrelated businesses share Dorzak infrastructure.

The launch is deliberately large but controlled: the company will release publicly only once Pro, Business, Enterprise, every approved merchant category, Qatar/Tunisia country behavior, English/French/Arabic, Superadmin, billing, provisioning, integrations, qualification and release evidence are complete.

---

## 5. Binding product and commercial decisions

### Launch and markets

- **One complete public launch at M9.** No partial plan, category, country, preview, or early public release.
- Initial countries: **Qatar and Tunisia**.
- Whole-system languages: **English, French, and Arabic**, with genuine RTL behavior, translation parity, and localized transactional content.
- Internal work can integrate behind disabled capabilities, but pricing, trials, sales and merchant access stay unavailable until the global launch gate passes.

### Commercial hierarchy

The public families are:

| Family | Promise | Upgrade justification |
|---|---|---|
| Free Tools | Useful business utilities forever after account creation; no merchant workspace, storefront, operational database, staff app or ERP site | Produces useful outputs and builds trust without pretending to be a free merchant plan |
| Pro | A complete professional business launch and its category's primary end-to-end transaction | Publish the site, take the first order/booking/payment, manage customers and daily operation correctly |
| Business | Pro plus growing-team coordination, advanced builder, scheduling/resources, workflows/approvals, automation, segmentation, advanced reporting and Business Work/Gantt | Reduces manual coordination, mistakes and no-shows; creates repeatable growth and stronger team control |
| Enterprise | Business plus complex-operation governance, configurable capacity, SSO/audit/integration, portfolio/capacity tools, advanced branding, custom workflows/site, managed implementation and service commitment | Controls risk and complexity and includes accountable Dorzak intervention and customization |

- Enterprise has **no minimum location count**. One complex or high-volume location, a large team, or multiple operations can qualify; two or more locations can also qualify.
- Pricing, currencies, tax display, billing cycles, trials, discounts, limits, included usage, add-ons, availability, support, onboarding and plan composition are editable, versioned Superadmin data. They are never hard-coded.
- Superadmin may reduce/restructure public plan families only through impact preview, immutable publication, migration policy, rollback proof, and owner/finance/operations/product approval.
- Existing subscriptions remain pinned to an immutable `PlanVersion`; catalogue changes must never silently remove purchased rights or corrupt billing.
- Default Pro/Business/Enterprise trial: **14 days**, starting only when the isolated site reaches `READY`.
- Qatar/Tunisia healthcare trial: **30 days**.
- Dorzak should be modestly less expensive than a truly comparable Lightspeed offer where defensible, but never below sustainable cost and never by comparing unlike products.
- The 14 July 2026 baseline research records official US Lightspeed prices as Retail Basic/Core/Plus USD 89/149/289 monthly and Restaurant Starter/Essential/Premium USD 69/189/399. Refresh the evidence before any publication or comparison.

Official benchmark pages:

- [Lightspeed Retail pricing](https://www.lightspeedhq.com/pos/retail/pricing/)
- [Lightspeed Restaurant pricing](https://www.lightspeedhq.com/pos/restaurant/pricing/)

### Free Tools hub

Free Tools is a scalable account-based acquisition product, not only QR and background removal. It can grow to include:

- QR Studio and related QR utilities;
- image/background utilities;
- business-document templates such as invoices, RFQs, quotations, requests and checklists — **not website templates**;
- calculators, generators and operational checklists;
- useful articles and guides;
- safe AI skills and assistants with explicit limits;
- saved history where the user opts in.

### Merchant categories

The initial complete launch covers:

1. Retail / shop
2. Supplier / wholesale
3. Restaurant / F&B
4. Appointments / professional services
5. Coiffeur / salon
6. Beauty center
7. Doctor / small clinic / health center
8. Education / school
9. Gym / fitness
10. Nonprofit organization
11. General business

Each category has its own complete customer journey, merchant workflow, terminology, permissions, states, reports and plan progression. Do not force unrelated industries into one generic order/status model. Shared kernels are reused only where semantics genuinely match.

Healthcare launches as clinic administration — patient registry, booking, queue, attendance/service, billing, protected documents and portal — **not a full EHR**. Diagnosis, prescriptions, clinical orders/results, medication/allergy data and clinical notes require a separately approved, jurisdiction-specific Clinical Pack. Qatar/Tunisia legal, professional, hosting/data-transfer, consent and patient-rights reviews are release gates.

Useful legal starting points, not final legal opinions:

- [Qatar personal-data protection guidance](https://mot.gov.qa/en/news/motc-releases-guidelines-personal-data-privacy-protection-law)
- [Tunisia INPDP source](https://www.inpdp.tn/bos.pdf)

Refresh law, provider, competitor and regulatory evidence before legal/commercial release.

---

## 6. Product surfaces and user journeys

There are four connected but deliberately separate surfaces:

1. **Dorzak corporate site:** homepage, solutions, categories, pricing, comparison, Free Tools, resources/articles, “Our Clients,” signup/trial, login and commercial content.
2. **Merchant management:** desktop-first, responsive operating application for owner, managers and staff. It exposes Dorzak UX over Dorzak and approved ERPNext capabilities.
3. **Merchant customer site:** merchant-branded and mobile-first; catalog/storefront, booking, membership, education, healthcare, giving, account, checkout, status, receipts and support as applicable. The end user must not discover the Dorzak umbrella or other merchants.
4. **Dorzak internal Superadmin:** Frappe-native internal control plane for catalogue, subscriptions, provisioning, site health, support intervention, content, client proof, audit and release control.

### Customer identity and checkout

- Checkout requires **name and mobile number**. Email is optional unless provider, legal or risk policy requires it.
- First successful purchase, booking, membership, donation, enrollment or equivalent journey can create a merchant-scoped customer account automatically.
- The real credential is a verified mobile/OTP or approved step-up challenge, never the name alone.
- The same mobile used at Merchant A and Merchant B creates two independent opaque principals, histories and database records.
- Dependants, minors, patients, shared/recycled numbers and sensitive roles require explicit profile selection and stronger matching/authorization.
- No end user gets a Dorzak-wide account history or directory of businesses.

### Website and builder

- Pro includes a complete professional mobile website appropriate to its category.
- Business adds an advanced section builder and stronger workflows/automation.
- Enterprise adds advanced branding, complex journeys, integrations, custom workflows/site behavior and a governed Dorzak-managed delivery option.
- Enterprise is not “software and goodbye.” The merchant can request Dorzak help to build, adjust, add or remove approved site functionality through scoped service requests, approvals, preview, audit, delivery and rollback.
- The normal authoring assumption is one responsible editor per page; real-time multi-person editing is not a launch requirement. Ownership/locking/versioning must prevent silent overwrites.

### Brand, visuals and content

- Dorzak's corporate and merchant applications should feel premium, high-tech and visually strong, with purposeful graphics, motion and animation.
- Motion may never sacrifice mobile performance, accessibility, reduced-motion support, clarity, Core Web Vitals or checkout/booking speed.
- Marketing copy must explain the outcome ladder clearly, show category-specific proof, make plan differences understandable within 30 seconds, and never claim unsupported compliance, savings, support or client results.
- “Our Clients” contains only consented, verifiable customer logos, stories and outcomes. No fake logos, metrics or testimonials.

---

## 7. ERPNext/Frappe and data boundaries

### Invisible per-merchant ERP core

- Every paid organization receives its own isolated Frappe/ERPNext site with separate database, files, credentials, backups and encryption boundary.
- Normal browsers, merchants and customers call Dorzak APIs only. They get no ERPNext setup wizard, hostname, token, Administrator password or separate account.
- Native ERPNext Desk access is not the launch default. A future Enterprise exception requires separately approved SSO, exact roles, plan enforcement and complete audit.
- The supplied ERPNext `develop` source is reference code, not the launch image. Production must pin a supported Frappe/ERPNext release.

### Single authority per field

- **Dorzak owns:** identity/authentication, organization and site registry, plans/entitlements/subscriptions/trials, public UX/content/builder, vertical extensions, orchestration, customer consent/preferences, support grants, release state and integration evidence.
- **ERPNext owns:** approved operational/financial master and transaction records such as canonical items/prices/warehouses/stock, quotations/orders/invoices/payments, procurement, accounting, assets and the approved ERP project/task core.
- Dorzak can keep read projections of ERP-owned data. It must not create a second editable truth.
- Mutations to ERP-owned records go through a constrained, versioned `dorzak_core` Frappe API with actor context, idempotency, audit, health and webhook contracts.
- Never dual-write a field. If ownership is unclear, stop and add it to the source-of-truth matrix before implementation.

### Internal Frappe-native Superadmin

The owner approved [P17 direction](../superpowers/control/approvals/2026-07-14-p17-frappe-native-superadmin-owner-decision.md):

- a separate internal Frappe site using ERPNext plus only relevant approved Frappe applications;
- professional Frappe/ERPNext visual language with minimal Dorzak branding;
- visibility over all merchant organizations, plans, subscriptions, provisioning, sites, health, incidents, support/service requests, content and release controls;
- full Superadmin intervention capability, but no implicit access for the ordinary Dorzak team;
- Superadmin can issue a merchant-, project-, action-, reason- and time-scoped grant to a delegated Dorzak teammate;
- every access and action is audited, reviewable and revocable; sensitive actions use stronger approval and break-glass controls;
- Superadmin does not become a second ERP ledger or customer identity authority.

---

## 8. Measurable roadmap

The only public release switch is after M9:

| Milestone | Programs | Outcome |
|---|---|---|
| M0 | P00 | Trustworthy baseline, CI, tests, tooling, context and ADRs |
| M1 | P01 | Organization, identity, Party, consent and fail-closed tenancy |
| M2 | WP-M2 | Money, idempotency, outbox, audit, ERP and capability contract freeze |
| M3 | P02–P03 | Isolated ERPNext fleet plus immutable plans, subscriptions and regional billing |
| M4 | P04 + WP-P04T | Commerce cutover, projections and bounded two-merchant ERP integration proof |
| M5 | P05–P06 + WP-P09A | Corporate/Free Tools/pricing/clients, builder/publication and scheduling foundation |
| M6 | P07–P09 | Retail, supplier, restaurant, appointments and beauty completion |
| M7 | P10–P13 + General | Healthcare, education, gym, nonprofit and general business completion |
| M8 | P14–P17 | Marketing/CRM, Work/Gantt, shipping/delivery and Frappe-native Superadmin |
| M9 | P18–P19 | Language/country/security/accessibility/performance qualification, rehearsal and one complete launch |

Mandatory sequence: `M0 → M1 → M2 → M3 → M4 → M5 → M6 → M7 → M8 → M9`.

The detailed dependency table and evidence gates live only in the [Control Register](../superpowers/control/README.md) and [technical roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md). P01 cannot begin until P00 is registered Complete.

---

## 9. Supplied source and reuse register

All paths below are **read-only source businesses or reference projects**. Inspecting and extracting ideas is allowed; editing them is not. Nothing is copied blindly. Each reuse decision requires license compatibility, supported-version pinning, security/privacy review, tenancy/isolation review, dependency/provenance review, and an explicit Dorzak/ERPNext integration boundary.

| Source | Supplied path | Intended assessment |
|---|---|---|
| Existing Dorzak application | `/Users/barsha/Downloads/web` | Current product baseline; closest to incomplete Pro; mine compatible UX/domain code |
| ERPNext supplied root | `/Users/barsha/Documents/ِERP Next` | Owner-supplied read-only business tree; inspect only approved subpaths |
| ERPNext | `/Users/barsha/Documents/ِERP Next/erpnext` | Core operational/financial reference; use through pinned Frappe service/custom-app boundaries |
| Frappe deployment | `/Users/barsha/Documents/ِERP Next/frappe_docker` | Deployment and isolated-site reference; audit and pin before use |
| Website builder | `/Users/barsha/Documents/build website /builder` | Section-builder and publication inspiration/reuse candidate |
| CRM | `/Users/barsha/Documents/build website /crm` | CRM/lead/customer capabilities and Frappe app fit |
| Helpdesk | `/Users/barsha/Documents/build website /helpdesk` | Support tickets, SLAs and service-request workflows |
| HRMS | `/Users/barsha/Documents/build website /hrms` | Staff/HR capabilities where plan/category appropriate |
| Insights | `/Users/barsha/Documents/build website /insights` | Reporting/analytics and Superadmin observability candidates |
| Education | `/Users/barsha/Documents/build website /education` | School/education vertical reference |
| Payments | `/Users/barsha/Documents/build website /payments` | Subscription/payment-processing concepts; license/security/provider audit required |
| Next.js subscription payments | `/Users/barsha/Documents/build website /nextjs-subscription-payments` | Checkout/subscription UX and provider reference; do not assume stack compatibility |
| Frappe UI | `/Users/barsha/Documents/build website /frappe-ui` | Builder/UI primitives and Frappe-native interface reference |
| Gantt | `/Users/barsha/Documents/build website /gantt` | Work/Gantt scheduling, dependencies and capacity reference |
| ERPNext shipping | `/Users/barsha/Documents/build website /erpnext-shipping` | Shipment/delivery integration reference |
| Mautic | `/Users/barsha/Documents/build website /mautic` | Marketing automation through an API/service boundary; do not lift GPL code into proprietary Dorzak code |
| Marketing skills | `/Users/barsha/Documents/build website /marketingskills` | Primary marketing/copy guidance for homepage and landing pages after the relevant plan is authorized |

- Halo was explicitly excluded by the owner.
- Current application truth is in `backend/` and `src/` inside this repository; verify actual runtime state before planning any implementation.
- `docs/source-audits/2026-07-13-mautic-fit.md` and `docs/source-audits/2026-07-13-erpnext-shipping-fit.md` are presently untracked/user-owned and noncanonical until explicitly reviewed and authorized.
- `docs/DORZAK_MERCHANT_SCALE_PLAN_PRD.md`, July 12 marketing files and July 13 work-package drafts are research/draft inputs. The complete-launch baseline supersedes them wherever they conflict.

---

## 10. Session and task control

Historical task IDs:

| Task | ID | State/purpose |
|---|---|---|
| Original Control Room | `019f5a12-7412-7e53-9a2b-37d2f313628e` | Context-heavy predecessor; retire after successor is verified |
| P00 baseline planning | `019f5e64-9472-7f32-b0a9-77b4b3741864` | Historical planning evidence |
| Superseded final correction | `019f5f82-74f5-7f11-87ab-7b72b5d75bf8` | Archived; uncommitted edit must never be integrated |
| P00 direct final correction | `019f5f92-89a2-7ae1-9c97-91f5986c78b6` | Completed exact one-file correction; archive after handoff |
| Fresh Control Room | Recorded in the Control Register after creation | Sole active control task |

Operating protocol:

- One short task, one bounded outcome, one writer lease, exact allowed paths, exact stop condition.
- The Control Room is the sole writer of lifecycle/status/authorization in the Control Register.
- Subagents can perform independent read-only review in parallel; do not give two agents write authority over the same files.
- Use clean isolated worktrees for implementation; never implement in this dirty control checkout.
- Capture branch, HEAD, worktrees, full status and protected-state hash before every writer lease.
- Integrate controlled streams serially and rerun shared gates after each integration.
- Close/retire completed tasks quickly; durable decisions belong in repository artifacts, not chat memory.
- No task may infer authority from its title, an approved design, a passing review, or a previous phase.

Recommended skill routing for future authorized work:

- `using-superpowers` at session start;
- `brainstorming` before creative/design changes;
- `writing-plans` before implementation;
- `using-git-worktrees` for isolated execution;
- `test-driven-development` for features and fixes;
- `systematic-debugging` for failures;
- `subagent-driven-development` or `dispatching-parallel-agents` only for independent bounded work;
- `verification-before-completion` before any completion claim;
- `/Users/barsha/Documents/build website /marketingskills` plus appropriate `copywriting`, `cro`, `pricing`, `site-architecture`, `free-tools`, `onboarding` and `signup` skills when the P05 marketing surface is authorized.

---

## 11. Fresh Control Room startup checklist

On first turn, the successor must:

1. Read this handoff and the Control Register at their latest committed revisions.
2. Run read-only checks for current branch, `HEAD`, worktrees, Git status and protected-manifest hash.
3. Compare reality with this handoff; report drift without modifying it.
4. Confirm that the current provider-neutral P00 base is the exact commit/hash registered above, that its review says 0 Critical / 0 Important, and that the required provider/P17 amendment is still an explicit hard gate.
5. Confirm the only current work is owner review/approval and control administration.
6. Await owner direction. Do not implement, install, create an execution worktree, alter CI, or touch protected user work.

The immediate owner-facing choices are whether to approve the exact P00 safety erratum and product baseline, and which canonical remote, CI provider and exact runtime/tool inputs should drive one focused Task 14/P17 reconciliation amendment. Only the freshly reviewed amended roadmap/plan can then receive exact owner approval. Execution prerequisites still follow; an approval is not execution authority.

---

## 12. Succession record

The first documentation handoff commit and fresh Control Room task ID are recorded in the [Control Register](../superpowers/control/README.md) after the task is created. The new task must resolve the latest commit affecting this handoff and register rather than relying on a copied SHA in chat.

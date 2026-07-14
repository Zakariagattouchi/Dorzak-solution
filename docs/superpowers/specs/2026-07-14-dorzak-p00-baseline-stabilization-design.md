# Dorzak P00 Baseline Stabilization Design

**Date:** 14 July 2026

**Program:** P00 — Baseline stabilization

**Status:** Proposed design approved; formal written specification awaiting owner approval

**Approval authority:** [P00 Proposed Design Approval Record](../control/approvals/2026-07-14-p00-proposed-design-approval.md) at Control Register commit `4658111c1306133e82f01b83434d63ef1f44b695`

**Source proposal:** [P00 Baseline Stabilization Proposal](../control/proposals/2026-07-14-p00-baseline-stabilization-proposal.md), SHA-256 `551054d063a89b8b361b4dbd45fefa03ec9e91915148b2490e0b454f57704320`

**Scope:** The contracts, architecture, ordering boundaries, safety rules, quality gates, evidence model, and exit criteria for making Dorzak's current baseline trustworthy

**Does not authorize:** An implementation plan; a branch or worktree; application, test, configuration, CI, dependency, or other documentation changes; the existing MediaUrl patch; P00 execution; P01–P19 work; or public release

---

## 1. Decision

P00 will use a serialized, layered, contract-first baseline program.

The program first characterizes and restores the approved behavior already intended by the repository. It then makes backend, frontend, browser, PostgreSQL, static-quality, and build checks deterministic and diagnostic. Finally, it records reproducible clean-checkout evidence and accurate operating documentation.

P00 is not a feature program. Its product is a trusted engineering baseline: one in which a failure identifies a bounded owner, a clean checkout can reproduce the result, and later feature work cannot silently weaken the gates.

The order is mandatory:

1. preserve the existing repository state;
2. lock the two approved baseline contracts;
3. repair the full-stack browser harness;
4. add frontend quality coverage;
5. qualify the backend on PostgreSQL;
6. wrap provider-neutral commands in CI and establish the performance baseline;
7. add context, decisions, runbooks, and evidence;
8. verify the integrated SHA independently.

This ordering is a design decision, not execution authority.

### 1.1 Alternatives considered

| Approach | Benefit | Why it is or is not selected |
|---|---|---|
| Patch the two assertions and add one CI job | Fastest apparent return to green | Rejected because browser fixtures, PostgreSQL behavior, lint/static debt, reproducibility, and stale documentation would remain untrusted |
| **Layered contract-first baseline** | **Gives each failure one owner and makes gates diagnostic and reproducible** | **Selected despite requiring several serialized boundaries before feature work resumes** |
| Containerize everything first | Maximizes environment uniformity | Rejected because it adds platform scope before current contracts and tests are trustworthy |

Container and service isolation are used where they materially protect results, especially PostgreSQL and eventual CI execution. A complete local container platform is not a P00 prerequisite.

---

## 2. Authority and approval boundary

The owner-approved proposal is the source for this specification. The approval selects:

- the layered, contract-first approach;
- origin-relative local media URLs with HTTP(S) pass-through;
- Qatar/QAR as the canonical demo and E2E tenant;
- subscription-currency redesign remaining in P03;
- the eight serialized boundaries in this document;
- PostgreSQL 16 as the P00 database qualification lane;
- provider-neutral quality commands and the required logical CI jobs;
- independent preservation of the existing MediaUrl patch;
- the safety, recovery, evidence, and measurable exit rules below.

The approval grants a narrow exception to use the complete-launch product baseline and technical roadmap review candidates as P00 design inputs. It does not formally approve those program-wide artifacts and does not make them execution authority.

This written specification is itself awaiting the next owner gate. Until written P00 specification approval is durably recorded by the Control Room:

- no implementation plan may be written;
- no implementation input may be chosen by assumption;
- no P00 execution environment may be created or reused;
- no repository change outside this exact specification is authorized.

If this specification, its source proposal, its approval record, or the Control Register conflict, work stops and the Control Room resolves the authority mismatch.

---

## 3. Verified design-entry baseline

The proposal evidence and the authorization preflight establish this starting state:

| Area | Verified state | Design consequence |
|---|---|---|
| Authority | Design entry occurred on `feat/premium-features` at `4658111c1306133e82f01b83434d63ef1f44b695` | P00 may write and commit only this specification |
| Dirty checkout | The registered 16-entry manifest hashes to `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`; nothing was staged | User work must remain byte-for-byte independent of P00 |
| MediaUrl user diff | `backend/app/Support/MediaUrl.php` has a formatting-only user change with no behavioral difference | Preserve it separately; never let formatting work absorb it |
| Linked worktree | A second linked worktree is dirty, 50 commits behind at proposal time, and changes `DemoSeeder.php` plus the two failing tests | Preserve it and never repurpose it for P00 |
| Laravel | 443 tests run; 441 pass | Characterize and reconcile exactly two baseline discrepancies first |
| Media discrepancy | A commerce test expects an absolute media URL while runtime returns `/storage/...` | Lock the approved origin-relative contract |
| Currency discrepancy | A parity test expects USD while the demo seeder creates a Qatar/QAR store | Lock the approved Qatar/QAR contract |
| Browser | The last recorded Playwright run is 0/7 because only Vite starts and every journey first reaches login without backend, data, or authenticated state | Build a guarded full-stack harness before repairing downstream assertions |
| Hidden browser drift | Navigation, product selection, USD pricing, settings-localStorage, and mutable localization assertions are stale behind the login failure | Repair journeys to current behavior only after the harness exposes them |
| Frontend build | TypeScript and production build pass; JavaScript is 778.88 kB minified and 216,700 bytes gzip, with a large-chunk warning | Preserve the measured gzip baseline and record the warning as debt |
| PHP style | Pint reports 16 affected files | Treat cleanup as a separate mechanical change |
| Composer | Strict validation passes | Make it a canonical required gate |
| Missing quality lanes | No frontend unit/component runner, lint/format command, PHP static analysis, PostgreSQL lane, CI definition, runtime pin, or canonical remote exists | Add bounded, reproducible lanes without assuming unresolved infrastructure |
| Database confidence | PHPUnit primarily uses in-memory SQLite; PostgreSQL locking and concurrency are unproved | Retain a fast SQLite lane and add full PostgreSQL 16 qualification |
| Documentation | `CONTEXT.md`, the initial ADR set, P00 evidence, and named runbooks are absent; `backend/README.md` still claims 179 tests and Pint clean | Update documentation only from verified commands and evidence |

The baseline is evidence, not a target to preserve blindly. Known discrepancies must be resolved to the approved contracts, while unrelated behavior remains unchanged.

---

## 4. Goals and non-goals

### 4.1 Goals

P00 will:

- turn the approved media and demo-currency behavior into executable contracts;
- make browser tests hermetic with explicit backend, database, fixture, and authentication ownership;
- retain a fast SQLite feedback lane while proving the complete backend on PostgreSQL 16;
- add bounded frontend unit/component, formatting, linting, typing, build, and bundle gates;
- add reviewed PHP style and static-analysis gates with non-increasing debt;
- expose one provider-neutral quality interface that local and CI execution share;
- prevent destructive tests from targeting a non-E2E database;
- preserve all pre-existing user work and distinguish code, evidence, and integration SHAs;
- make clean-checkout reproduction and two same-SHA CI runs part of completion;
- create enough context, decision, runbook, and evidence material for later programs to inherit a trustworthy baseline.

### 4.2 Non-goals

P00 will not:

- grant program-wide approval to the complete-launch product baseline or technical roadmap;
- begin P01 or any later product program;
- add ERPNext integration or implement launch features;
- redesign subscription billing currency;
- establish a full local container platform as a prerequisite;
- perform a broad framework, dependency, or runtime upgrade;
- convert the entire existing test estate to a new framework;
- perform unrelated refactors, formatting, or cleanup;
- require broad route splitting unless the approved no-growth bundle gate cannot otherwise pass;
- define production migration, deployment, or public-release procedures;
- substitute for final cross-cutting qualification in P18;
- choose a remote, CI provider, runtime pin, integration base, worktree, or MediaUrl preservation action without recorded owner authority.

---

## 5. System shape and ownership

P00 adds quality boundaries around the existing Laravel and React/Vite system; it does not redesign the product architecture.

| Boundary | Responsibility | Depends on | Must not own |
|---|---|---|---|
| Contract characterization | Encode the selected media and Qatar/QAR behavior and protect existing behavior | Current Laravel application and deterministic fixtures | New product behavior or subscription-currency redesign |
| Browser harness | Own E2E environment safety, reset, migration, seeding, service startup, authentication state, and browser execution | Laravel, Vite, an isolated E2E database, Playwright | Shared mutable developer data or implicit service startup |
| Frontend quality lane | Check formatting, linting, TypeScript, focused unit/component behavior, production build, and bundle size | Locked JavaScript dependencies and a confirmed Node runtime | Backend integration truth or a wholesale UI test rewrite |
| Backend quality lane | Check Composer validity, PHP style, static analysis, SQLite behavior, PostgreSQL behavior, and concurrency | Locked Composer dependencies, confirmed PHP runtime, PostgreSQL 16 | Browser fixture ownership or unapproved contract changes |
| Canonical quality interface | Give local execution and the eventual CI wrapper the same logical commands and failure semantics | All quality lanes | Provider-specific business logic |
| Evidence and documentation | Bind commands, versions, results, and decisions to exact SHAs | Successful clean-checkout verification | Unsanitized secrets, optimistic claims, or stale counts |

The principal flows are:

1. A guarded E2E fixture creates one coherent Qatar/QAR tenant and current domain data.
2. Laravel serves the API and media contract; Vite serves the merchant UI and explicitly proxies or serves `/storage`.
3. Playwright obtains authenticated state through a setup project, while a separate smoke test exercises the real login path.
4. Backend behavior runs quickly on SQLite and completely on PostgreSQL 16; concurrency tests use process barriers rather than timing sleeps.
5. Provider-neutral commands feed the selected CI wrapper after the remote and provider are approved.
6. The required-gates aggregator represents the same integrated SHA, and sanitized evidence records exactly what passed.

No lane may hide another lane's failure. Browser tests cannot mock away backend/data/auth startup, CI retries cannot turn a first failure into evidence, and an aggregate gate cannot report green unless every required job reports green for the same SHA.

---

## 6. Approved baseline contracts

### 6.1 Public media URL contract

At public media DTO boundaries:

| Input | Output |
|---|---|
| `null` or empty input | `null` |
| Existing `http://` URL | Unchanged |
| Existing `https://` URL | Unchanged |
| Local storage-disk key | Origin-relative `/storage/<key>` |

A non-empty input that is not HTTP(S) is required to be a storage-disk-relative key. Callers must not supply an already-public `/storage/...` URI or another URI scheme.

This contract keeps cached API payloads portable and avoids embedding an environment-specific `APP_URL`. It also creates an infrastructure obligation: every supported web origin must explicitly proxy or serve `/storage`. That obligation is a deployment and runbook acceptance check, not a reason to convert DTOs to absolute URLs.

P00 characterizes this contract at its normalization boundary and at representative public DTO seams. It does not broaden media storage architecture.

### 6.2 Canonical demo and E2E currency contract

The baseline demo and E2E tenant is Qatar/QAR:

- the tenant's merchant selling currency is QAR;
- orders created for that tenant snapshot QAR;
- USD values in legacy `mockData.ts` are stale fixture data, not authority;
- browser fixtures and assertions must be internally coherent with Qatar/QAR;
- the browser suite receives a dedicated resettable E2E fixture rather than depending on the hybrid, non-resetting `DemoSeeder`.

Merchant selling currency does not become subscription billing currency. The product baseline requires those concepts to remain independent, and their model redesign stays in P03. P00 may characterize the current separation but may not implement the P03 model.

---

## 7. Serialized boundaries

P00 uses one writer stream. A later boundary does not begin until the prior boundary's evidence is accepted. These are design boundaries, not a file-by-file implementation plan.

### 7.1 Preservation preflight

Record both worktrees, their branches, HEADs, statuses, path-level manifests and hashes, and the exact MediaUrl user diff and its hash. Resolve the approved preservation method before any preservation action.

Under a separate preservation authorization, complete the approved action and verify it before execution authorization. The durable preservation record must identify the approved method, the exact preserved patch SHA-256 or commit SHA, the verification result proving that the preserved artifact matches the reviewed user diff, the resulting approved integration `BASE_SHA`, and the clean state of the named execution worktree at that base. Selecting a method without completed, verified, and evidenced preservation does not satisfy this gate.

No existing worktree is repurposed. The future execution worktree and integration base are approved only from the completed preservation record. At the end of every authorized writer boundary, compare final state with the captured pre-existing manifest and stop on any difference outside the approved allowlist.

### 7.2 Baseline contracts

Add characterization coverage for the approved MediaUrl contract and pin the Qatar/QAR fixture contract. Reconcile the two existing Laravel discrepancies and reach 443/443 without unrelated application behavior changes.

### 7.3 Deterministic full-stack browser lane

Create an explicitly guarded E2E environment and database. Before each suite, the harness resets, migrates, and seeds deterministic data. It then starts Laravel and Vite, creates authenticated `storageState` through a Playwright setup project, and retains a separate real-login smoke test.

Initial execution uses one worker and zero retries. Parallel execution is deferred until each worker can receive an isolated tenant/data namespace. Once the harness is authoritative, repair the seven existing journeys to current behavior and semantic selectors.

### 7.4 Frontend quality lane

Introduce Vitest, React Testing Library, accessible component helpers, ESLint, and Prettier. At minimum, cover:

- authentication and bootstrap behavior;
- external DTO adapters;
- money and settings behavior;
- protected-shell states;
- one interactive accessible form.

Tests and configuration remain inside TypeScript checking. Changes to `package.json` and `package-lock.json` have one serialized owner.

### 7.5 Backend quality and PostgreSQL lane

Apply the known 16-file Pint cleanup as an independent mechanical change. Add Larastan/PHPStan at a reviewed achievable level with a versioned legacy baseline that cannot increase silently.

Run the complete Laravel suite on PostgreSQL 16. Add process-level concurrency coverage for order numbering and stock plus representative wallet, loyalty, gift-card, or webhook locking. Concurrent actors synchronize with explicit barriers, never sleeps.

### 7.6 CI and performance baseline

Keep canonical commands provider-neutral and make CI a thin wrapper. The logical required jobs are:

1. Composer validation;
2. PHP style and static analysis;
3. SQLite fast suite;
4. PostgreSQL full and concurrency suite;
5. frontend format, lint, type, unit/component, build, and bundle checks;
6. full-stack Playwright;
7. required-gates aggregator.

Lockfile-only install modes and pinned runtime, PostgreSQL image, and Playwright browser inputs make results reproducible. Exact production PHP and Node pins remain unresolved and cannot be selected in this design. The CI provider also remains unresolved; GitHub Actions is only a conditional recommendation if a GitHub remote is established.

Under the ultimately approved and pinned Node/zlib measurement, initial JavaScript gzip must not exceed 216,700 bytes. The existing large-chunk warning remains explicit debt. Broad route splitting is not P00 scope unless the no-growth gate fails and the smallest contract-preserving response requires it.

### 7.7 Context, ADRs, runbooks, and evidence

Create a repository-root `CONTEXT.md` and these initial ADRs:

- `docs/adr/0001-system-of-record-authority.md`;
- `docs/adr/0002-organization-location-and-isolated-erpnext-tenancy.md`;
- `docs/adr/0003-modular-monolith-and-external-adapters.md`;
- `docs/adr/0004-one-complete-public-launch.md`;
- `docs/adr/0005-immutable-plan-publication.md`;
- `docs/adr/0006-commerce-cutover-and-no-dual-write.md`;
- `docs/adr/0007-frontend-surface-boundaries.md`.

The frontend ADR records Next.js as deferred pending the measured P05 spike. It must not represent Next.js as a completed P00 selection.

Update `RUN.md`, repository setup guidance, and `backend/README.md` only after their commands and counts have been verified. Store sanitized P00 evidence under `docs/superpowers/evidence/p00/`.

### 7.8 Final verification

An independent reviewer verifies a named, clean `CODE_SHA`. The full matrix runs from a fresh checkout. Required CI then runs twice on the same integrated SHA.

Evidence records the code, evidence, and integrated SHAs separately so an evidence-only commit cannot be mistaken for the code that was tested.

---

## 8. Test, quality, and reproducibility design

### 8.1 Browser isolation and safety

The E2E setup is destructive by design and therefore fail-closed:

- reset aborts unless `APP_ENV=e2e`;
- reset aborts unless the resolved database identity passes an explicit E2E safety guard;
- fixture creation is deterministic and repeatable;
- Laravel and Vite health are prerequisites for browser execution;
- authentication setup failure is reported separately from journey failure;
- the login smoke test does not reuse pre-authenticated state;
- retries remain zero and unexplained skips or quarantines are prohibited.

Shared localization or settings state cannot leak between journeys. Semantic selectors identify user intent rather than incidental layout or duplicate text.

### 8.2 Backend database confidence

SQLite remains the fast feedback lane. PostgreSQL 16 is the qualification lane and runs the complete backend suite, not only a small database-specific subset.

Concurrency tests create genuinely overlapping processes, synchronize their critical sections with barriers, and assert both the business result and database invariants. Timing sleeps are not synchronization evidence.

A defect discovered on PostgreSQL may be corrected within P00 only when the correction preserves an already approved current contract. Any behavior choice outside those contracts returns to the Control Room.

### 8.3 Static-quality debt

PHP static-analysis debt is checked into version control as a reviewed baseline. The baseline may shrink automatically but may grow only through an explicit reviewed revision.

Formatting cleanup remains separate from behavior changes. Frontend formatting, linting, unit/component testing, TypeScript, production build, and bundle checks are independently diagnosable even if the CI provider groups them within one logical job.

### 8.4 Reproducibility

The canonical quality interface must run locally and in CI without provider-only semantics. A reproducible result records:

- exact runtime and tool versions;
- lockfile hashes and lockfile-only install modes;
- PostgreSQL image identity;
- Playwright browser identity;
- commands, test counts, durations, and exit status;
- bundle measurement inputs and output;
- exact source and integrated SHAs.

Two passing runs count only when they execute the same integrated SHA with the same declared inputs. CI retries do not replace this requirement.

---

## 9. Dirty-state, recovery, and change-control rules

The repository begins P00 with user-owned work. Preservation is a hard correctness property:

- preserve the MediaUrl diff independently;
- recommended preservation is a dedicated owner-approved commit before selecting the clean execution base;
- the permitted alternative is export and application of the exact reviewed patch;
- the Control Room must approve one method before either action occurs;
- under separate authority, the approved preservation action must be completed and its result verified before execution authorization;
- the durable record must identify the approved method, exact preserved patch SHA-256 or commit SHA, verification result against the reviewed user diff, resulting approved integration `BASE_SHA`, and clean state of the named execution worktree at that base;
- selection of a method alone is insufficient evidence of preservation;
- never let Pint or another mechanical rewrite absorb the diff;
- never reuse the stale linked worktree;
- never use `git add -A`; stage only the approved allowlist;
- compare final branch, HEAD, worktree list, status, and path-level manifest with the captured pre-existing state after each writer boundary;
- any unexpected manifest divergence stops work without reset, stash, deletion, or speculative repair.

Execution also stops when:

- MediaUrl preservation is incomplete, its evidence is missing or mismatched, or the named execution worktree is not clean at the approved integration base;
- an authority or product contradiction appears;
- a destructive database guard cannot prove the E2E target;
- a PostgreSQL finding requires a new behavior decision;
- a static or bundle baseline would increase without explicit review;
- a required job is flaky, skipped, quarantined, retried into green, or associated with a different SHA.

Recovery preserves evidence. It records the failure and returns to the owning boundary; it does not erase local state or weaken the gate.

---

## 10. Evidence model

P00 completion is a chain of attributable evidence:

| Identity | Meaning |
|---|---|
| `BASE_SHA` | Approved clean integration base selected before execution |
| `CODE_SHA` | Exact code independently reviewed and tested from a clean checkout |
| Evidence hash/commit | Sanitized records generated for that code |
| `INTEGRATED_SHA` | Exact integrated commit on which required CI runs |

The evidence manifest records:

- commands and exit codes;
- test counts and unexplained-skip count;
- runtime, dependency-manager, database, and browser versions;
- durations;
- Composer and JavaScript lockfile hashes;
- PostgreSQL image;
- CI run identifiers;
- JavaScript minified and gzip sizes;
- independent review result;
- `BASE_SHA`, `CODE_SHA`, evidence identity, and `INTEGRATED_SHA`.

Evidence must contain no credentials, tokens, personal data, or unsafe environment dumps. Documentation claims are updated from this evidence rather than copied from historical README values.

---

## 11. Future planning and execution gates

This design opens neither implementation planning nor execution.

### 11.1 Implementation-planning entry

No implementation-plan work begins until the owner approves this corrected exact written P00 specification artifact and its hash, and the Control Room durably records a separate plan-writing authorization.

That plan-writing authorization is valid only when it cites one of these two durable prerequisites:

1. formal owner approval of both the complete-launch product baseline at `cc4085c` and the technical roadmap introduced at `d518f92` with the `069f483` correction; or
2. a separate, exact, owner-approved P00 plan-writing exception durably recorded by the Control Room.

The exception must name the source artifacts and versions it permits as planning inputs, limit its scope to writing and reviewing the P00 implementation plan, and explicitly exclude program-wide artifact approval, MediaUrl preservation, branch or worktree creation, application changes, execution authority, P01–P19 work, and public release. It cannot satisfy any execution-entry condition.

Merely recording the two source artifacts' status, or carrying forward the design-writing exception, is insufficient. The plan-writing authorization must also identify which open implementation inputs are resolved and which, if any, are explicitly deferred to the execution gate. An unresolved input that affects an exact planning choice stops the affected plan work; the planning task may not supply it by assumption.

### 11.2 Execution entry

No P00 execution task may begin, and no execution authorization or code-writing lease may be issued, until the Control Room has durably recorded:

1. a separately reviewed and owner-approved P00 implementation plan;
2. formal owner approval of the complete-launch product baseline at `cc4085c` and the technical roadmap introduced at `d518f92` with the `069f483` correction;
3. the canonical Git remote;
4. the CI provider;
5. exact production PHP and Node runtime pins;
6. completed, verified, and evidenced MediaUrl preservation whose record identifies the approved method, exact preserved patch SHA-256 or commit SHA, verification result against the reviewed user diff, resulting approved integration `BASE_SHA`, and clean state of the named execution worktree at that base;
7. owner approval of that resulting integration base, clean execution worktree, and P00 execution.

The P00 plan-writing exception described in Section 11.1 cannot satisfy item 2 or authorize any other execution-entry item. GitHub Actions remains conditional on first establishing an approved GitHub remote. No authority, preservation method, or implementation input in this section is selected by this specification.

---

## 12. Measurable P00 exit gate

P00 closes only when all of the following are true:

- the approved integration worktree is clean;
- all 443 existing Laravel tests and every added test pass on SQLite;
- the complete backend suite and concurrency tests pass on PostgreSQL 16;
- all seven existing Playwright journeys and all added browser smoke tests pass with Laravel, data, and authentication fixtures started by the harness;
- Playwright uses zero retries and has no quarantines or unexplained skips;
- Composer validation, Pint, Larastan/PHPStan, frontend formatting, lint, TypeScript, unit/component tests, production build, and bundle checks all exit zero;
- static-analysis debt is versioned and cannot increase;
- initial JavaScript is at or below the canonical 216,700-byte gzip baseline under the approved pinned measurement;
- two clean CI runs of the same `INTEGRATED_SHA` pass;
- the selected remote marks the aggregate required gate as required;
- `CONTEXT.md`, all seven ADRs, accurate runbooks, and a sanitized evidence manifest exist;
- evidence records commands, counts, versions, durations, lockfile hashes, PostgreSQL image, CI run identifiers, bundle sizes, review result, `BASE_SHA`, `CODE_SHA`, evidence identity, and `INTEGRATED_SHA`.

No percentage-complete claim, local-only green run, retried CI result, or evidence from a different SHA satisfies this gate.

---

## 13. Open owner decisions

The approved contracts are not open. The following decisions remain unresolved and must stay visible:

1. **Program-wide authority:** whether to formally approve the complete-launch baseline at `cc4085c` and the technical roadmap introduced at `d518f92` with the `069f483` correction. The P00 exception is design-only.
2. **Canonical remote:** which remote future P00 work targets.
3. **CI provider:** which provider wraps the canonical commands. GitHub Actions is conditional, not selected.
4. **Production runtime pins:** the exact supported PHP and Node versions.
5. **Integration isolation:** the approved integration base and future clean P00 worktree.
6. **MediaUrl preservation:** a separate owner-approved commit, as recommended, or the exact reviewed-patch alternative.
7. **Written specification approval:** whether this committed formal design is approved for the later implementation-planning gate.

PostgreSQL 16, the media contract, Qatar/QAR fixture contract, subscription-currency deferral to P03, serialized ordering, provider-neutral job model, and measurable exit rules were selected by the proposal approval and are not to be reopened by implementation assumption.

---

## 14. Next gate

Commit only this specification and return its path and exact commit SHA to the Control Room. The Control Room verifies the artifact and asks the owner for written P00 specification approval.

Work stops at that gate. No implementation plan, branch, worktree, preservation action, implementation change, P00 execution, or P01 work follows from this document alone.

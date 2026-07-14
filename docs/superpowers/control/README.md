# Dorzak Launch Control Register

**Purpose:** Single source of truth for current status, active authorization, blockers, and the next permitted Dorzak complete-launch action.

**Last verified:** 2026-07-14 05:11:38 +03

**Register revision:** Resolve with: git log -1 feat/premium-features --format=%H -- docs/superpowers/control/README.md

**Interim canonical control ref:** feat/premium-features, documentation-only control commits

**Future canonical integration ref:** Not established; execution is prohibited until separately approved and created

**Sole register writer:** Dorzak Launch — Control Room

**Control task ID:** 019f5a12-7412-7e53-9a2b-37d2f313628e

**Observed repository HEAD before this control change:** d518f92

**Current lifecycle:** Planning

**Current authorization:** P00 planning task is Approved to analyze read-only and present a non-authoritative proposed design

**Public release:** Blocked until M9. No partial public launch.

---

## 1. Authority and approval register

Authority depends on the question:

| Artifact/domain | Governs | Lifecycle | Approval evidence | Document commit |
|---|---|---|---|---|
| [Complete-launch product baseline](../specs/2026-07-14-dorzak-complete-launch-baseline-v1.md) | Product requirements | Awaiting owner | Formal written-artifact approval not yet recorded | cc4085c |
| [Technical execution roadmap](../specs/2026-07-14-dorzak-technical-execution-roadmap-design.md) | Architecture, sequence and engineering gates | Awaiting owner | Formal written-artifact approval not yet recorded | d518f92 plus current correction |
| [Session orchestration design](../specs/2026-07-14-dorzak-session-orchestration-design.md) | Task/session workflow | Approved | Owner approved the rolling session plan in Control Room on 14 July 2026 | 069f483 |
| This Control Register | Status and next authorization | Approved | Owner approved creation of the control folder/register on 14 July 2026 | Introduced at 069f483; current revision resolves from canonical ref |
| Approved program design/plan | Exact bounded change | Not started | None | N/A |
| Verified code/evidence SHA | Implemented reality | Not started | None | N/A |

A review candidate may guide read-only analysis but is not execution authority. Before any design is committed or implementation plan is written, the Control Room must record the product-baseline and technical-roadmap approvals or the exact approved exception.

If requirements, architecture, plan, status, or implementation evidence disagree, stop the affected work and reconcile the versioned artifacts.

---

## 2. Read this first

When the program becomes confusing:

1. Read this register for status, authorization, blocker, and next action.
2. Read the product baseline for what the product must contain.
3. Read the technical roadmap for architecture and milestone gates.
4. Read the session orchestration design for how tasks are split.
5. Read only the active work-package design and approved plan.
6. Verify actual implementation against the registered clean tested SHA.

This file is the only program-status writer. It links to requirements and designs instead of duplicating them.

---

## 3. Current position and staged gates

~~~text
SESSION MODEL AND CONTROL REGISTER APPROVED
                    current
                       ↓
COMMIT REGISTER TRANSITION AUTHORIZING P00 CREATION
                 complete at f2654ae
                       ↓
CREATE P00 READ-ONLY PLANNING TASK
             active; read-only analysis
                       ↓
PROPOSED P00 DESIGN IN TASK
                       ↓
FORMAL PRODUCT BASELINE + ROADMAP APPROVAL
                       ↓
OWNER APPROVES P00 DESIGN PROPOSAL
                       ↓
CONTROL ROOM COMMITS APPROVAL RECORD
                       ↓
WRITE + COMMIT P00 DESIGN
                       ↓
OWNER APPROVES WRITTEN P00 SPEC
                       ↓
WRITE + COMMIT P00 IMPLEMENTATION PLAN
                       ↓
OWNER APPROVES PLAN + WORKTREE + EXECUTION
                       ↓
P00 EXECUTION TASK
              not yet authorized
~~~

No approval in this chain implies the next approval.

### Current repository facts

- Current branch: **feat/premium-features**.
- Observed HEAD before the uncommitted control artifacts: **d518f92**.
- The checkout contains user-owned modifications and untracked files.
- Pre-existing path-status manifest: **16 entries**, SHA-256 **a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa**. It is the sorted git status --short output excluding this control-register work and records paths/status only, not secret contents.
- Before any writer task, the Control Room must capture HEAD, worktrees, status, and a hash/manifest of the pre-existing changes.
- The existing MediaUrl change requires an explicit preservation decision before an execution worktree.
- Planning is read-only; application implementation is prohibited in this checkout.

---

## 4. Complete-launch phase headlines

Lifecycle, authorization, and Codex app state are tracked separately.

| Milestone | Canonical work | Big headline | Lifecycle | Authorization | Exit headline |
|---|---|---|---|---|---|
| M0 | P00 | Trustworthy baseline, CI, tests, tooling, context and ADRs | Planning | P00 task Approved to analyze read-only only | Clean reproducible green baseline |
| M1 | P01 | Organization, identity, Party, consent and fail-closed tenancy | Not started | Not authorized | Tenant/membership parity and identity isolation |
| M2 | WP-M2 | Money, idempotency, outbox, audit, ERP and capability Interface freeze | Not started | Not authorized | Fake and real ERP contract handshake passes |
| M3 | P02 + P03 | Isolated ERPNext fleet plus immutable plans, billing and regional payments | Not started | Not authorized | Two isolated READY sites and safe paid-plan lifecycle |
| M4 | P04 + WP-P04T | P04 commerce migration plus bounded retail integration proof; no P07 scope | Not started | Not authorized | Reconciled two-merchant integration tracer |
| M5 | P05 + P06 + WP-P09A | Corporate site, Free Tools, pricing, clients, builder, localization and shared scheduling foundation | Not started | Not authorized | Public/customer platform and shared kernels qualified |
| M6 | P07 + P08 + WP-P09B | Retail, supplier, restaurant, appointments and beauty completion | Not started | Not authorized | Category × plan matrices pass |
| M7 | P10 + P11 + P12 + P13 + General | Healthcare, education, gym, nonprofit and general business | Not started | Not authorized | Sensitive and remaining vertical matrices pass |
| M8 | P14 + P15 + P16 + P17 | Growth, Work/Gantt, delivery and complete Superadmin | Not started | Not authorized | Governed operations and intervention pass |
| M9 | P18 + P19 | Cross-cutting qualification, rehearsal and one complete launch | Not started | Not authorized | All gates/sign-offs pass; release switch may enable |

Mandatory milestone sequence:

~~~text
M0 → M1 → M2 → M3 → M4 → M5 → M6 → M7 → M8 → M9
~~~

M3 may contain two controlled streams after M2. Later parallel streams integrate serially onto the registered canonical integration branch and rerun shared gates after each merge. M9 is serialized.

Global execution rule: a program/work package may be planned at its canonical dependency gate, but execution also requires the previous milestone to be registered Complete. Named same-milestone exceptions are P02/P03 inside M3, P05/P06/WP-P09A inside M5, P07/P08/WP-P09B inside M6, P10–P13 inside M7, P14–P17 inside M8, and P18/P19 inside M9; these still obey their program dependencies and permitted stream limits.

---

## 5. Canonical program dependencies

The dependency columns deliberately separate planning entry, execution entry, and milestone exit.

| Program | Outcome | Lifecycle | Planning may begin after | Execution requires | Milestone exit requires | Authorization |
|---|---|---|---|---|---|---|
| P00 | Baseline stabilization and engineering controls | Planning | This committed control transition | Approved source artifacts, P00 design/plan, preserved user diff, clean worktree | M0 evidence | Approved to analyze read-only only |
| P01 | Organization, identity, Party, consent and tenancy | Not started | P00 complete | M0/P00 complete + approved P01 work-package plan | M1 evidence | Not authorized |
| P02 | ERPNext platform core and fleet | Not started | P01 complete + WP-M2 complete | P01 complete + WP-M2 complete + approved P02 plan | M3 ERP evidence | Not authorized |
| P03 | Plans, subscriptions and regional billing | Not started | P01 complete + WP-M2 complete | P01 complete + WP-M2 complete + approved P03 plan | M3 commercial evidence | Not authorized |
| P04 | Commerce cutover, projections and reconciliation | Not started | P02 + P03 complete | M3 complete + P02/P03 + approved P04 plan | M4 evidence | Not authorized |
| P05 | Corporate, Free Tools, signup, pricing and clients | Not started | P03 complete | M4/WP-P04T complete + P03 + approved P05 plan | M5 evidence | Not authorized |
| P06 | Builder and merchant-site publication | Not started | P01 + P03 complete | M4/WP-P04T complete + P01/P03 + approved P06 plan | M5 evidence | Not authorized |
| P07 | Retail, supplier and shared commerce | Not started | P04 + P06 complete | M5 complete + P04/P06 + approved P07 plan | M6 evidence | Not authorized |
| P08 | Restaurant/F&B | Not started | P04 + P06 complete | M5 complete + P04/P06 + approved P08 plan | M6 evidence | Not authorized |
| P09 | Scheduling, appointments and beauty | Not started | P01 + P03 complete | M4 complete for WP-P09A; M5 + WP-P09A/P04/P06 for WP-P09B | P09 complete before P10–P13; M6 evidence | Not authorized |
| P10 | Healthcare Qatar/Tunisia | Not started | P02 + P04 + P06 + P09 complete | M6 complete + same dependencies + approved P10 plan | M7 evidence | Not authorized |
| P11 | Education/school | Not started | P02 + P04 + P06 + P09 complete | M6 complete + same dependencies + approved P11 plan | M7 evidence | Not authorized |
| P12 | Gym/fitness | Not started | P02 + P04 + P06 + P09 complete | M6 complete + same dependencies + approved P12 plan | M7 evidence | Not authorized |
| P13 | Nonprofit | Not started | P02 + P04 + P06 + P09 complete | M6 complete + same dependencies + approved P13 plan | M7 evidence | Not authorized |
| P14 | Marketing, CRM, loyalty and communications | Not started | P04 + P06 complete | M7 complete + P04/P06 + approved rewritten P14 plan | M8 evidence | Not authorized |
| P15 | Work/Gantt and managed delivery | Not started | P02 + P04 complete | M7 complete + P02/P04 + approved rewritten P15 plan | M8 evidence | Not authorized |
| P16 | Shipping/delivery fulfillment | Not started | P04 complete | M7 complete + P04 + provider scope + approved P16 plan | M8 evidence | Not authorized |
| P17 | Complete Superadmin control plane | Not started | P02 + P03 complete | M7 complete + P02/P03 + approved P17 plan | M8 evidence | Not authorized |
| P18 | Language, country, security, accessibility and performance | Not started | P05–P17 complete | M8 + P05–P17 complete + approved P18 plan | M9 qualification evidence | Not authorized |
| P19 | Integration, migration rehearsal and release | Not started | P18 complete | P18 complete + approved P19 plan | M9 release evidence | Not authorized |

P05/P06 may satisfy their canonical dependencies before M4, but the technical roadmap deliberately defers broad production construction until the P04 integration proof has passed. That is milestone ordering, not a rewritten canonical dependency.

---

## 6. Work-package register

Work packages are execution boundaries; their existence does not authorize their task creation.

| Work package | Parent | Outcome | Lifecycle | Planning gate | Execution gate | Design/plan | Task IDs | Branch/worktree | Evidence | Serialized owner |
|---|---|---|---|---|---|---|---|---|---|---|
| WP-P00 | P00 | Baseline design and plan | Planning | This committed control transition | Source + design + plan + worktree approvals | None | Planning: 019f5e64-9472-7f32-b0a9-77b4b3741864 | N/A | Dispatch f2654ae; proposed design pending | Control Room |
| WP-P01A | P01 | Additive Organization migration | Not started | P00 complete | M0 complete + approved WP-P01A plan | None | None | N/A | N/A | Control Room |
| WP-P01B | P01 | Identity, Party, OTP and consent | Not started | WP-P01A complete | WP-P01A complete + approved WP-P01B plan | None | None | N/A | N/A | Control Room |
| WP-M2 | Shared prerequisite | Contract/value/outbox/real ERP handshake freeze | Not started | P01 complete | M1/P01 complete + approved WP-M2 plan | None | None | N/A | N/A | Control Room; all contract registries serialized |
| WP-P04T | P04 | Bounded P04 retail integration fixture, explicitly not P07 | Not started | P02 + P03 complete | M3 + P02/P03 complete + approved WP-P04T plan | None | None | N/A | N/A | P04 integration owner |
| WP-P09A | P09 | Shared scheduling kernel foundation | Not started | P01 + P03 complete | M4/WP-P04T complete + approved WP-P09A plan | None | None | N/A | N/A | Scheduling owner |
| WP-P09B | P09 | Appointment/beauty category completion | Not started | WP-P09A + P04 + P06 complete | M5 + WP-P09A/P04/P06 complete + approved WP-P09B plan | None | None | N/A | N/A | P09 owner |

WP-M2 is mandatory and must be Complete before P02/P03 execution. It receives its own design, plan, planning/execution task IDs, branch/worktree, test evidence, reviewed code SHA, evidence commit/hash, and final integrated SHA.

---

## 7. Task register

| Task title | Task ID | Type | Lifecycle | Outcome | Authorization | App state | Owner | Base/verified SHA | Writes allowed | Next gate |
|---|---|---|---|---|---|---|---|---|---|---|
| Dorzak Launch — Control Room | 019f5a12-7412-7e53-9a2b-37d2f313628e | Control | Planning | Open | Approved to administer control artifacts | Pinned | Control Room | f2654ae / current register revision | Control/spec documents only | Record P00 task and await proposal |
| Dorzak Launch — P00 Baseline Planning | 019f5e64-9472-7f32-b0a9-77b4b3741864 | Planning | Planning | Open | Approved to analyze read-only | Active | P00 planning owner | Dispatch f2654ae | None before durable design authorization | Present proposed design and stop |
| Dorzak Launch — P00 Baseline Execution | Not created | Execution | Not started | Open | Not authorized | Not created | Unassigned | N/A | Isolated worktree only | Source + design + plan + worktree approvals |

No other planning or execution task is authorized.

---

## 8. Protected state and dirty-state manifest

| Item | Observed state | Rule |
|---|---|---|
| backend/app/Support/MediaUrl.php | User-modified | Record exact diff; owner decides separate commit or reviewed patch before execution worktree |
| Marketing overview plan | User-modified | Preserve; exclude from P00 |
| Untracked skills, audits, Work plans and outputs | User-owned/previous work | Do not stage, delete, reset, or overwrite |
| Existing P14/P15 implementation drafts | Architecturally stale | Do not execute before rewrite against current authority |
| Supplied source repositories | Read-only | Inspect/audit only; never edit |
| Approved integration base | Not established | No execution task/worktree until established |

Before any code-writing lease:

1. capture git HEAD, branch, worktree list and status;
2. save a path-level manifest plus hash of pre-existing tracked/untracked changes without exposing secrets;
3. assign allowed files and worktree;
4. preserve unexpected patches without destructive reset;
5. compare final state to the pre-existing manifest.

---

## 9. Legal lifecycle, outcome, authorization and app values

Lifecycle values:

- Not started
- Planning
- Awaiting owner
- Approved
- Executing
- Verifying
- Blocked
- Closed

Planning-task transitions:

~~~text
Not started → Planning → Awaiting owner
  → Planning after Approved to write design
  → Awaiting owner after written-spec review
  → Planning after Approved to plan
  → Awaiting owner after implementation-plan review
  → Approved when execution handoff is accepted
  → Closed when the approved handoff is durably registered
~~~

Execution-task transitions:

~~~text
Not started → Approved → Executing → Verifying
  → Executing when Critical/Important findings exist
  → Verifying after fixes
  → Closed when clean integrated evidence is registered
~~~

Outcome is separate:

- Open
- Complete
- Superseded
- Abandoned

A Superseded or Abandoned decision also moves lifecycle to Closed after its preservation/cleanup evidence is registered.

Authorization:

- Not authorized
- Approved to create
- Approved to analyze read-only
- Approved to administer control artifacts
- Approved to write design
- Approved to plan
- Approved to execute

Codex app state:

- Not created
- Active
- Pinned
- Interrupted
- Archived

Archiving changes app state only; it does not erase the task outcome. Lifecycle, outcome, authorization and app state are never collapsed into “in progress.”

---

## 10. Approval durability

A task message is a proposal or decision signal, not durable execution authority by itself.

After an owner decision, the Control Room must commit an approval record containing:

- approver;
- timestamp/timezone;
- decision scope;
- exact target path/artifact;
- the full approved proposal text or a preserved canonical copy plus its hash;
- artifact commit/hash when it exists;
- next authorization and exclusions.

The proposal remains in the task conversation until the owner decides. After approval and before design writing, the Control Room preserves the exact approved proposal in the approval record and commits its hash. If context compacts before that record is committed, reconfirm the decision. A planning task cannot write the design merely because it remembers an approval.

---

## 11. Integration and verification protocol

- Reviewers are read-only and review a named clean committed SHA.
- Task implementation records BASE..HEAD and focused/full commands.
- Critical/Important findings return the work to Executing and require re-review.
- Evidence is stored/committed separately from the code SHA when appropriate.
- Parallel branches merge serially onto the registered canonical integration ref.
- After each merge, reconcile the next branch against the prior integrated SHA and rerun focused plus shared/integration gates.
- Record tested code SHA, evidence commit/hash, and final integrated SHA separately.
- Do not authorize the next merge from two independently green branches without integrated evidence.

---

## 12. Control Room succession and refresh

If this task is unavailable:

1. open the latest committed register from the interim/canonical control ref;
2. resolve its revision using git log -1 <canonical-control-ref> --format=%H -- docs/superpowers/control/README.md;
3. verify HEAD, branch, status, worktrees and active Codex tasks;
4. compare with the recorded dirty-state manifest;
5. create and pin one replacement Control Room;
6. record old/new task IDs, reason and verification timestamp;
7. commit that transition before authorizing work.

Every context packet names the latest committed register revision. At every owner, execution, merge and completion gate, resolve the latest revision from the canonical ref and compare it with the packet; if it changed, stop and reload before continuing. Two Control Rooms never hold a writer lease simultaneously.

---

## 13. Immediate next action

Current next action:

1. P00 task performs read-only inspection and presents its proposed design;
2. the owner reviews the formal product baseline, technical roadmap, and P00 proposal;
3. the Control Room preserves any approved proposal and approval record before authorizing design writing;
4. no implementation plan, worktree, or code begins.

The P00 planning task may not edit files, commit a design, write an implementation plan, create a worktree, stage the MediaUrl change, alter application code, or start P01.

---

## 14. Recent durable transitions

| Timestamp (+03) | Register/control commit | Transition |
|---|---|---|
| 2026-07-14 05:09:50 | f2654ae | Authorized creation of one read-only P00 planning task |
| 2026-07-14 05:11:38 | Current register revision | Recorded P00 planning task ID, Active app state, read-only authorization, and dispatch revision |

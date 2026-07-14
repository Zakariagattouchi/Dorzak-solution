# Dorzak Launch Session Orchestration Design

**Date:** 14 July 2026

**Status:** Owner approved

**Scope:** Planning-session organization, execution isolation, context preservation, and program control

**Does not authorize:** Product implementation, a worktree, application-code changes, ERPNext changes, or public release

---

## 1. Decision

Dorzak will use a rolling, stage-gated collection of fresh Codex tasks rather than carry the entire program inside one conversation or create every future task in advance.

The current task is the permanent **Dorzak Launch — Control Room**. It owns decisions, approvals, sequence, cross-program dependencies, and the Control Register. It does not become a general implementation task.

Each approved program work package or slice receives:

1. a fresh planning task;
2. owner review and approval of its design and executable plan;
3. a separate execution task in an isolated worktree;
4. short-lived implementer and reviewer agents inside that execution task;
5. independent verification and recorded evidence;
6. closure and archival before the next dependent program begins.

Tasks are created just in time. Planning may run at most one approved dependency step ahead; plans for distant programs are not written against speculative Interfaces.

---

## 2. Goals

- Preserve one reliable answer to “where are we, what is next, and what blocks it?”
- Make every new task understandable without replaying this full conversation.
- Prevent chat compaction from becoming product or engineering authority.
- Prevent plans from becoming stale before their dependencies exist.
- Prevent two writers from changing the same files, schema, Interface, or source-of-truth rule.
- Keep supplied ERPNext and reference repositories read-only.
- Make owner approvals and launch gates explicit.
- Allow safe parallel work only after the roadmap’s dependency gates pass.

---

## 3. Authority matrix and conflict rule

Authority is contextual rather than one linear list:

| Question | Authority |
|---|---|
| What product must exist? | Owner-approved complete-launch product baseline |
| What technical sequence and invariants govern it? | Owner-approved technical roadmap and ADRs |
| What bounded behavior is being designed? | Owner-approved program/work-package design |
| What exact change is authorized? | Owner-approved executable plan and task brief |
| Where are we now and what may happen next? | Latest committed Control Register on its canonical control ref |
| What has actually been implemented? | Clean tested code SHA, evidence commit/hash, and final integrated SHA |

The Control Register is the single status source. It does not override requirements, architecture, an approved change plan, or verified implementation reality.

Any mismatch across authority types blocks the affected work until the Control Room reconciles and versions the artifacts. A draft/review candidate is input, not formal execution authority.

### 3.1 Work unit definitions

| Unit | Meaning | Example |
|---|---|---|
| Program | One canonical launch program from P00–P19 | P01 Organization, identity, Party, consent and tenancy |
| Work package / slice | A bounded, independently approvable part of a program, or a shared milestone prerequisite not owned by one program | P01a Organization migration; WP-M2 Shared Contract Freeze |
| Planning task | A fresh read-only Codex task that produces the proposed design and, after staged approvals, the executable plan for one work package | Dorzak Launch — P01a Organization Planning |
| Execution task | A fresh isolated-worktree Codex task that executes one approved plan | Dorzak Launch — P01a Organization Execution |
| Agent task | One testable implementation/review unit inside an execution task | Add Organization backfill audit command |

Programs may span more than one milestone or work package. The Control Register tracks both levels so partial work is never mistaken for program completion.

Forecast work packages include:

- P01a Organization migration and P01b identity/Party/consent;
- WP-M2 Shared Contract Freeze;
- WP-P04T P04 retail integration tracer, explicitly not P07;
- WP-P09A shared scheduling foundation and WP-P09B P09 category completion.

Each of these receives its own planning/execution pair when dependency-ready. Their names do not authorize early creation.

---

## 4. Task types

### 4.1 Control Room

Responsibilities:

- maintain the Control Register;
- act as the sole writer of the canonical Control Register;
- approve the next task boundary;
- record owner decisions;
- resolve plan/roadmap contradictions;
- assign branch/worktree and file ownership;
- control cross-program Interface changes;
- verify program exit evidence;
- decide when a planning or execution task is closed and archived.

The Control Room may inspect the repository and manage planning documents. It does not write feature code.

### 4.2 Planning task

A planning task:

- is fresh rather than forked from the large Control Room history;
- runs read-only in the project checkout until owner approval;
- owns one work package/slice and receives a compact context packet;
- uses brainstorming before design;
- writes a bounded design only after design approval;
- uses writing-plans only after the written design is approved;
- creates no application code, migrations, dependency changes, branches, or worktrees;
- returns the design path, implementation-plan path, open owner decisions, dependencies, and exact execution entry gate.

Planning tasks are archived when their approved plan and handoff are recorded.

### 4.3 Execution task

An execution task:

- is created only from an approved executable plan;
- runs in a dedicated worktree based on the named green integration commit;
- receives no broad product-discovery mandate;
- uses one code-writing agent per task;
- applies TDD, systematic debugging, independent review, and fresh verification;
- commits task-scoped code and tests;
- updates evidence and the stream progress ledger;
- cannot alter a frozen shared Interface without a Control Room change decision.

### 4.4 Short-lived agents

Agents are not user-visible program tasks. Inside an execution task:

- one implementer writes one bounded task;
- one fresh reviewer checks specification compliance and code quality;
- high-risk tenancy, ERP, payment, money, security, healthcare, education, or privacy work receives a sequential specialist review;
- investigators may work in parallel on independent failures, but only one designated fixer writes.

---

## 5. Lifecycle

~~~text
Control Room selects next dependency-ready program
  → fresh planning task receives compact context
  → proposed design
  → owner design approval
  → written design + self-review
  → owner written-spec approval
  → exact implementation plan
  → owner execution approval
  → clean worktree execution task
  → task-by-task TDD and review
  → program verification and evidence
  → Control Register update
  → archive completed tasks
  → select next dependency-ready program
~~~

No “implementation is nearly ready” state bypasses an approval or evidence gate.

---

## 6. Active-task limits

Before the M2 Interface freeze:

- one Control Room;
- one planning task or one execution task;
- no parallel implementation streams.

After M2:

- one Control Room;
- at most two execution tasks for demonstrably independent programs;
- at most one planning task one dependency step ahead;
- review is staged so the available agent capacity is not oversubscribed.

Two execution tasks are allowed only when:

1. each has a separate worktree and branch;
2. upstream Interfaces are frozen;
3. files, migrations, schemas, registries, and lockfiles do not overlap;
4. each has independent focused tests;
5. the Control Register names the merge order.

Migrations, routes, dependency lockfiles, ExecutionContext, tenant/Party models, plan manifest, ERP command schema, scheduling kernel, localization catalogues, design tokens, and release configuration remain serialized.

Parallel branches integrate serially:

1. merge/reconcile the first dependency-ready branch onto the named canonical integration branch;
2. run its focused tests plus all shared/integration gates;
3. record the tested integrated SHA;
4. update the second branch against that integrated SHA;
5. rerun focused and shared/integration gates before its merge;
6. record the new integrated SHA before authorizing another merge.

Two independently green branches are never assumed green together.

---

## 7. Naming and grouping

The Codex application does not need to carry hierarchy in conversation memory. All related tasks use a common title prefix and the same saved project:

- **Dorzak Launch — Control Room**
- **Dorzak Launch — P00 Baseline Planning**
- **Dorzak Launch — P00 Baseline Execution**
- **Dorzak Launch — P01a Organization Planning**
- **Dorzak Launch — P01a Organization Execution**
- **Dorzak Launch — P01b Identity and Party Planning**
- **Dorzak Launch — P01b Identity and Party Execution**
- **Dorzak Launch — M2 Contract Freeze Planning**
- **Dorzak Launch — M2 Contract Freeze Execution**
- **Dorzak Launch — P02 ERP Platform Planning**
- **Dorzak Launch — P02 ERP Platform Execution**
- **Dorzak Launch — P03 Commercial Platform Planning**
- **Dorzak Launch — P03 Commercial Platform Execution**

Later tasks follow the same prefix and program code. Completed tasks are archived after their evidence and final SHA are registered. The Control Room remains pinned.

Repository organization:

~~~text
docs/superpowers/
  control/
    README.md                 current position and program/task register
  specs/
    ...                      immutable approved designs
  plans/
    ...                      exact executable plans
  evidence/
    pNN-or-work-package/     created when execution evidence exists
~~~

The repository folder is the durable group. The common task-title prefix is the Codex sidebar group until a native task-folder capability is available.

---

## 8. Compact context packet

Every fresh planning or execution task receives only:

| Field | Required content |
|---|---|
| Objective | One bounded outcome |
| Authority | Product baseline, roadmap, relevant design/plan paths and commits |
| Current state | Observed HEAD, approved integration base, status/diff manifest hash, relevant tests, known failures, and source-of-truth owner |
| Scope | Exact requirements and surfaces included |
| Non-goals | Explicit exclusions |
| Dependencies | Frozen Interface versions and predecessor evidence |
| Ownership | Allowed files/modules and prohibited overlaps |
| Output | Exact artifact paths or implementation deliverable |
| Gates | Commands, counts, reviews, and owner approval required |
| Safety | Tenant, money, privacy, license, supplied-repository, and worktree rules |
| Return package | Paths, BASE..HEAD, tests, findings, blockers, next action |

The packet references canonical documents; it does not paste their full contents or repeat old conversation history.

A temporary handoff may be created to seed a fresh task, but durable truth always returns to committed repository artifacts and the Control Register.

Every packet names the exact committed Control Register revision by resolving the latest commit that contains the register. The receiving task refreshes that revision at each owner, execution, merge, and completion gate.

---

## 9. Control Register protocol

The Control Room updates the register:

- when a task is created, renamed, pinned, blocked, approved, completed, or archived;
- when a branch/worktree or file owner is assigned;
- when an owner decision changes a dependency or scope;
- after every program verification;
- before authorizing the next program.

Each row records:

- program, work package/slice, and milestone;
- task ID and title;
- state;
- dependency gate;
- controlling spec/plan;
- base and verified commit;
- tested code SHA, evidence commit/hash, and final integrated SHA as separate values;
- worktree/branch when applicable;
- owner/blocker;
- next action.

Only the Control Room edits program-level status. Execution tasks update their stream ledger and return evidence for the Control Room to register.

The register names one canonical control ref and one sole-writer Control Room task. Until the clean integration branch exists, the interim control ref may be a documentation-only commit on the current branch, but its observed HEAD and dirty-state manifest must be recorded. Planning and execution tasks never edit it directly.

---

## 10. Status vocabulary

Lifecycle, outcome, authorization, and Codex app state are separate fields.

Lifecycle:

| State | Meaning |
|---|---|
| Not started | Dependency or authorization has not opened |
| Planning | Read-only discovery/design/plan work is active |
| Awaiting owner | A proposal, written spec, plan, or decision requires owner review |
| Approved | The task has the authorization needed for its next named transition |
| Executing | Code changes are active in an assigned worktree |
| Verifying | Writes stopped; fresh review/test evidence is being gathered |
| Blocked | A named decision or repeated impasse prevents progress |
| Closed | No further work remains in this task; its terminal outcome and durable evidence are recorded |

Outcome:

- Open;
- Complete;
- Superseded;
- Abandoned.

Authorization:

- Not authorized;
- Approved to create;
- Approved to analyze read-only;
- Approved to administer control artifacts;
- Approved to write design;
- Approved to plan;
- Approved to execute.

App state:

- Not created;
- Active;
- Pinned;
- Interrupted;
- Archived.

Planning alternates between Planning and Awaiting owner for proposed-design, written-spec, and implementation-plan gates, then closes after the approved execution handoff is durably registered. Execution alternates between Executing and Verifying until clean integrated evidence is registered, then closes. Superseded or abandoned work also closes after preservation evidence is registered. Archiving changes app state only and never erases outcome. These fields are never collapsed into “in progress.”

---

## 11. Failure and recovery rules

- If chat context compacts, reload the Control Register and controlling artifacts; do not reconstruct decisions from memory.
- If a planning task discovers a product contradiction, stop and return it to the Control Room.
- If two tasks touch the same owned surface, pause the lower-priority task.
- If a task begins writing outside its authorization, stop it and inspect the diff.
- Before any writer starts, record HEAD, worktree, branch, status, and a hash/manifest of pre-existing user-owned changes.
- If a task writes outside authorization, revoke its writer lease, preserve its patch without reset/delete, compare against the pre-existing manifest, and return ownership to the Control Room.
- If an execution task loses context, create a compact handoff from the committed plan, progress ledger, git range, current status, preserved uncommitted patch/diff hash or path, last command/output, and writer-lease state; do not fork this full Control Room history.
- A successor begins read-only, validates the preserved patch, pre-existing dirty manifest, ownership, and last verification, then receives a new writer lease from the Control Room.
- If a task is abandoned, record its branch/worktree, uncommitted state, last verified command, and safe disposal decision.
- If a plan becomes stale after an Interface change, mark it Superseded and rewrite only the affected bounded plan.
- No task may edit supplied ERPNext, builder, CRM, helpdesk, HRMS, Insights, education, payments, Frappe UI, Gantt, shipping, or Mautic source repositories.

Before a task is archived, its durable artifacts must be committed and the register must record their commit, approval evidence, final task/worktree state, and last verified command.

Reviews are read-only against a named clean committed SHA. A Critical/Important finding returns the lifecycle to Executing. Completion records the reviewed code SHA, evidence commit/hash, and final integrated SHA separately.

An owner decision becomes executable only after the Control Room records the approver, timestamp, scope, target artifact/path and commit/hash in the committed register. If chat compacts before that record is committed, the decision must be reconfirmed.

If the Control Room must be replaced:

1. open the latest committed Control Register from git;
2. verify repository status, worktrees, active tasks, and recorded SHAs rather than trusting chat;
3. create and pin the replacement Control Room;
4. record the old/new task IDs, verification timestamp, and succession reason;
5. commit the register update before authorizing more work.

---

## 12. Initial authorized sequence

The owner supplied the decision signal to:

1. rename and pin the current Control Room;
2. create the control folder and register;
3. commit the corrected control artifacts;
4. commit a register transition that durably authorizes creation of one fresh **P00 Baseline Planning** task;
5. create that task only after the transition commit exists;
6. allow that task to perform read-only discovery and present a proposed P00 design;
7. stop for owner design approval.

Until step 4 is committed, P00 task creation remains Not authorized even though the owner has expressed the decision signal.

The product baseline and technical roadmap remain review candidates until the owner explicitly approves those written artifacts. The P00 task may analyze them and surface contradictions, but it may not treat them as formal execution authority or commit a design until those approvals and its own design approval are recorded.

The approval does not authorize:

- a P00 execution task;
- a worktree or integration branch;
- resolution or staging of the current MediaUrl change;
- application-code changes;
- ERPNext provisioning;
- creation of P01 or later tasks.

---

## 13. Acceptance criteria

This orchestration design is operating correctly when:

- a user can open one register and identify current stage, active task, blocker, and next gate;
- every active task has a unique purpose and owner;
- every execution task has an approved plan and isolated worktree;
- no session requires this full conversation to act safely;
- completed tasks can be archived without losing decisions or evidence;
- at most the permitted implementation streams are active;
- no unapproved future plan is mistaken for executable authority;
- one complete public launch remains blocked until M9.

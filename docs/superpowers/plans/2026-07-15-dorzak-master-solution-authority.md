# Dorzak Master Solution Authority Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Produce, validate, independently review, sign, and hand off one self-contained Dorzak product-and-solution authority that covers the complete-launch target without granting execution authority.

**Architecture:** One persistent designated writer owns the production worktree and serially edits the master, its deterministic audit evidence, and a dependency-free Node validator. Four fresh independent workers remain read-only and review the same immutable candidate; the writer alone records their returned evidence and applies corrections before a fresh review round. The master carries all meaning needed by readers, while the evidence tree proves inventory, extraction, coverage, validation, review, and signoff without becoming required reading.

**Tech Stack:** Markdown; canonical JSON and JSON Lines; Git object identities; SHA-256; Node.js `24.18.0` standard library and built-in test runner; npm `11.16.0` only for version verification, with no install and no dependency change.

## Global Constraints

- The controlling design is `docs/superpowers/specs/2026-07-15-dorzak-master-solution-authority-design.md` at exact commit `bc0c39e7794901d7cd879f2a35bb203bc14c6e88`, blob `843f0ee9a43fc791557503fd97c418ad3685a7b8`, SHA-256 `0c9ee0271808cbb723df641a1ee625f58976d19c2f283845cf9206e12da11cef`.
- Production is prohibited until a later Control transition creates and authorizes the exact entry record `docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json` at the current tip of `codex/p00-entry-setup`. Phase 0 resolves `CONTROL_HEAD` once, requires the entry’s last-change commit to equal that exact tip, and reads the Control README, handoff, and entry only from those bytes; any later transition, revocation, or tip movement invalidates the gate.
- The later Control entry—not this self-referential plan—registers `plan_commit`, `plan_blob_sha1`, `plan_sha256`, and `production_base_commit`. The production checkout starts with HEAD equal to both registered commits, and Git-object verification proves the plan’s exact design parent, subject, sole path, mode, blob, and file hash before any production write.
- The production branch is `codex/dorzak-master-solution-v1`; the only production worktree is `/Users/barsha/.codex/worktrees/recover-kyte-p00/master-solution-v1`.
- The sole master path is `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`, mode `100644`. It is the complete product-and-solution model, not a status board, link index, marketing-only narrative, engineering-only design, or implementation plan.
- The accepted Current-evidence baseline is `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` unless the later Control entry explicitly supersedes this plan with a separately approved revision. A different value stops this plan; it is not accepted dynamically.
- The exact protected checkout is `/Users/barsha/Documents/recover Kyte`, branch `feat/premium-features`, HEAD `cf2f65b2e308bdf4750c3e02dc1aafa7a7a39a4d`, 16 sorted `git status --short` entries, status SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`. It is read-only.
- Exactly one persistent designated writer may modify the production worktree. Subagent-driven execution reuses that writer for every writing task; fresh subagents are used only for read-only review. No reviewer may patch, stage, commit, generate a competing candidate, or decide product behavior.
- Writer staging is limited to the exact production paths listed in “Production file structure.” A need for any other path stops for a new Control transition and plan revision.
- Use the already installed Node.js `24.18.0` runtime selected by the repository pin; verify npm `11.16.0`. Do not run `npm install`, `npm ci`, or any package-manager mutation. Do not modify `package.json`, `package-lock.json`, or `.nvmrc`.
- All JSON is UTF-8, LF-terminated, two-space indented, key-sorted recursively, and contains no timestamps unless the schema explicitly calls for a human decision time. All JSONL rows are one canonical JSON object per LF-terminated line and sort by their primary ID.
- Source precedence is: bounded later durable owner decisions; approved complete-launch baseline; approved roadmap plus accepted ADRs and `CONTEXT.md`; approved bounded designs and errata; approved plans; verified exact-SHA implementation evidence; then compatible preserved historical material.
- Exact decision statuses are `Approved`, `Planned`, `Open`, `Deferred`, and `Superseded`. Exact solution states are `Current`, `Target`, `Out of scope`, and `Historical`; `Open` has no solution state.
- Legal status/state combinations are: Approved with Current, Target, or an approved Out-of-scope exclusion; Planned with Target only; Open with no state; Deferred with Out of scope only; Superseded with Historical or evidenced Current legacy awaiting removal.
- The complete-launch commercial hierarchy is `Free Tools`, `Pro`, `Business`, and `Enterprise`. Initial countries are Qatar and Tunisia. Required languages are English, French, and Arabic, including right-to-left behavior.
- The self-contained commercial catalogue includes every approved sub-plan feature and every measurable quantity, limit, and charge for every plan and vertical: seats, locations, storage, usage, messages, integrations, support/service levels, monthly and annual price, currency, tax treatment, discount, trial, add-on, overage, payment timing, onboarding, managed-service charges, upgrade, downgrade, cancellation, retirement, proration, dunning, and refund behavior. Competitor or market benchmarks are labeled non-authoritative context and never substituted for a Dorzak decision.
- No worker may invent a missing price, quantity, limit, rate, discount, charge, or commercial lifecycle rule. Each missing approved value becomes a complete approval-blocking Open commercial decision; the candidate may be drafted for decision review, but independent approval, team signoff, handoff PASS, and activation require zero such Open decisions.
- The document has exactly five top-level parts. Appendices remain within Part V and never create a sixth authority layer.
- Every one of `DOM-001` through `DOM-053` is present exactly once and contains all 16 ordered domain fields from the design. An inapplicable field uses `Not applicable —` followed by a domain-specific reason.
- Every one of the 36 PRD areas, `P00` through `P19`, `WP-M2`, `WP-P04T`, `WP-P09A`, `WP-P09B`, `M0` through `M9`, ADRs 0001–0007, all required roles, all 13 mandatory modules, all supported verticals, all material workflows, integrations, data families, security controls, and operating responsibilities has bidirectional traceability.
- The master embeds all 18 required matrices and all 12 required flows. Diagrams never contain the sole statement of a rule; adjacent prose defines every node, edge, state, owner, and failure path.
- Every normative master statement has one stable visible ID and maps to an approved source, verified Current evidence, or a complete Open synthesis record. Every extracted source statement has exactly one disposition and destination.
- Current statements cite the accepted evidence SHA. Planned behavior is never described as implemented. Existing non-target behavior is Current plus Superseded and names its removal or migration path.
- The only intentionally unresolved master content is a complete governed Open-decision record. An Open decision affecting promise, price, system of record, tenancy, money, sensitive data, regulation, migration safety, or release acceptance blocks approval.
- The master contains no unfinished marker, empty heading, empty table cell, hidden incomplete comment, unexplained ellipsis, indefinite future claim, generic external-document dependency, or matrix qualifier that hides a material variation.
- Supporting evidence is audit-only. A rendered master with links disabled must still explain product and commercial promise, current and target systems, permissions, fact ownership, journey failure and recovery, isolation and protection, operations, delivery path, and open/deferred/superseded decisions.
- Never copy secrets, credentials, environment values, personal data, clinical data, child data, beneficiary data, payment payloads, raw merchant records, or unsafe host information into any artifact.
- Production does not authorize P00 Task 16, P01–P19 execution, P17 execution, provider or GitHub action, code/schema change, deployment, provisioning, migration, or release. Control remains the only execution-status and authorization writer.
- Stop on any Control-tip movement, lease revocation, authority, plan/base identity, source, inventory, protected-state, branch, worktree, candidate, review-packet, allowlist, or hash mismatch; missing unregistered source; uncovered statement; unresolved contradiction; second writer; reviewer mutation; approval-blocking Open decision; remaining Critical or Important finding; or missing same-byte signoff.
- On stop, preserve the current candidate and evidence, record only the nonsecret blocker, return to Control, and do not infer a decision, edit a source, weaken a requirement, or continue to another phase.
- Do not ask the owner to choose an execution mode again. After a new Control transition, execute with subagent-driven development under the one-writer/read-only-reviewer adaptation defined here.

---

## Production File Structure

Only these paths may be created or modified by the designated writer:

```text
docs/dorzak-launch/
├── DORZAK_MASTER_SOLUTION.md
└── master-solution-evidence/
    ├── README.md
    ├── source-inventory.json
    ├── extraction-ledger.jsonl
    ├── coverage.json
    ├── validation.json
    ├── reviews/
    │   ├── 01-product-business.json
    │   ├── 02-architecture-data.json
    │   ├── 03-security-operations.json
    │   └── 04-delivery-authority.json
    ├── signoff.json
    └── handoff.json
scripts/quality/
├── dorzak-master-solution.mjs
└── dorzak-master-solution.test.mjs
```

Responsibilities are exact:

| Path | Responsibility | Required reading for master comprehension? |
|---|---|---|
| `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md` | The five-part product-and-solution authority and all embedded meaning | Yes; it is the only required document |
| `docs/dorzak-launch/master-solution-evidence/README.md` | Audit boundary, file schemas, reproduction commands, and explicit non-authority warning | No |
| `docs/dorzak-launch/master-solution-evidence/source-inventory.json` | Frozen sorted source identities, authority, availability, state axes, supersession, and ownership | No |
| `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl` | One row per normative source statement with normalization, classification, disposition, destination, and verdict | No |
| `docs/dorzak-launch/master-solution-evidence/coverage.json` | Source-to-master and master-to-source mappings plus the required catalogue coverage sets | No |
| `docs/dorzak-launch/master-solution-evidence/validation.json` | Deterministic validator result bound to exact master/source/ledger/coverage bytes | No |
| `docs/dorzak-launch/master-solution-evidence/reviews/*.json` | Four independent returned review records, ten separate lens verdicts, findings, correction rounds, and exact-candidate bindings | No |
| `docs/dorzak-launch/master-solution-evidence/signoff.json` | Required role and regulated-vertical decisions bound to one candidate commit and master hash | No |
| `docs/dorzak-launch/master-solution-evidence/handoff.json` | Final immutable candidate/evidence identity, zero-gap counts, review results, signoffs, and Control activation request | No |
| `scripts/quality/dorzak-master-solution.mjs` | Standard-library parser, canonicalizer, hash verifier, coverage/structure validator, and evidence validator | No |
| `scripts/quality/dorzak-master-solution.test.mjs` | Built-in Node tests for every validator failure class and success contract | No |

No generated HTML, rendered PDF, cache, temporary extract, alternate master, scratch Markdown, or untracked report is permitted in the repository. Reviewers return structured findings in their task response; the writer records accepted report bytes at the four exact review paths.

## Shared Interfaces and Conventions

The writer and all reviewers use these stable identifiers and schemas.

### Stable master IDs

- `AUTH-NNN`: authority, scope, status, product promise, and present-to-target rules in Part I.
- `BUS-NNN`: business, commercial, experience, journey, and vertical rules in Part II.
- `REQ-DOM-NNN-NNN`: domain rules in Part III, where the middle number is the domain number.
- `TECH-NNN`: technical, security, reliability, and operating rules in Part IV.
- `DEL-NNN`: delivery, evidence, governance, and definition-of-done rules in Part V.
- `MAT-001` through `MAT-018`: required matrices.
- `FLOW-001` through `FLOW-012`: required flows.
- `OPEN-NNN`, `DEFERRED-NNN`, and `SUPERSEDED-NNN`: decision-register entries.
- `SRC-NNNN`: source inventory entries; `LED-NNNNNN`: extraction rows; `COV-NNNNNN`: coverage rows.

IDs are never reused after activation. A consolidation maps multiple ledger rows to one complete stable master ID. Repeated application uses an internal reference to the canonical rule instead of restating it.

### Validator exports

`scripts/quality/dorzak-master-solution.mjs` must export these exact signatures:

```js
export function canonicalJson(value)
export function sha256(bytes)
export function parseJsonl(text, sourceName)
export function validateSourceInventory(inventory, gitReader)
export function validateExtractionLedger(rows, inventory)
export function validateCoverage(coverage, inventory, ledgerRows, masterText)
export function validateMaster(masterText, currentEvidenceSha)
export function validateReviews(reviewFiles, candidateIdentity)
export function validateSignoff(signoff, candidateIdentity)
export function buildValidationReport(inputs)
export async function main(argv, environment)
```

Return shapes are stable across all tasks:

```js
const inventoryResult = { errors: [], available: 1, notCreated: 46 };
const ledgerResult = { errors: [], uncoveredCoordinates: 0, approvalBlockingConflicts: 0 };
const coverageResult = { errors: [], uncoveredSource: 0, unplannedMaster: 0 };
const masterResult = {
  errors: [], errorsForPresentParts: [],
  partial: { partI: "PASS", partII: "PASS", partIII: "PASS", partIV: "PASS", partV: "PASS" },
  parts: 5, matrices: 18, flows: 12, unfinished: 0, emptyCells: 0, blockingOpenDecisions: 0,
  domains: { count: 53, errors: [] }, programs: { count: 20 }, workPackages: { count: 4 }, milestones: { count: 10 },
  modules: { count: 13, errors: [] },
  commercial: { missingCells: 0, blockingOpenDecisions: 0, benchmarkSubstitutions: 0 },
  catalogue: { missingActors: 0, missingVerticals: 0 },
  domainRange(first, last) { return { first, last, errors: [] }; }
};
const reviewResult = { errors: [], reports: 4, lenses: 10, critical: 0, important: 0 };
const signoffResult = { errors: [], required: 7, approved: 7 };
```

The concrete `required` signoff count increases above seven for every qualified regulated-vertical reviewer nominated by Control.

Every review/signoff validator receives this complete identity object; omitting or changing any property is an identity failure:

```js
const candidateIdentity = {
  candidateCommit,
  candidateParent,
  masterBlobSha1,
  masterSha256,
  sourceInventorySha256,
  reviewPacketCommit
};
```

The CLI modes are exact:

```text
node scripts/quality/dorzak-master-solution.mjs inventory
node scripts/quality/dorzak-master-solution.mjs ledger
node scripts/quality/dorzak-master-solution.mjs coverage
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-001 DOM-010
node scripts/quality/dorzak-master-solution.mjs candidate
node scripts/quality/dorzak-master-solution.mjs reviews
node scripts/quality/dorzak-master-solution.mjs handoff
```

Successful candidate validation prints one line:

```text
MASTER_SOLUTION_VALIDATION PASS domains=53 prd=36 programs=20 work_packages=4 matrices=18 flows=12 uncovered_source=0 uncovered_master=0 critical=0 important=0
```

Any failed check prints only nonsecret stable check IDs to stderr and exits `1`. Usage failure exits `2`. The CLI never edits source, ledger, coverage, master, review, signoff, or handoff files; only `candidate` may write canonical `validation.json`, and only when `MASTER_VALIDATION_OUTPUT` equals that exact path.

### Evidence schemas

The later Control entry is outside the production allowlist, but its schema is a prerequisite: in addition to the active task/lease/authorization fields validated in Task 1, it must contain `plan_path`, `plan_commit`, `plan_blob_sha1`, `plan_sha256`, and `production_base_commit`. Control registers those values only after this plan commit exists. Production never derives or hardcodes its own plan commit; it accepts the values only from the frozen current-tip entry and then proves them against Git objects.

`source-inventory.json` has top-level keys in this order after canonical sorting:

```json
{
  "current_evidence_sha": "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf",
  "design_commit": "bc0c39e7794901d7cd879f2a35bb203bc14c6e88",
  "schema_version": 1,
  "sources": []
}
```

Every `sources` row contains exactly: `source_id`, `canonical_identity`, `authority_level`, `lifecycle`, `effective_date`, `decision_scope`, `path`, `commit`, `blob_sha1`, `sha256`, `headings`, `solution_states`, `supersedes`, `exceptions`, `extraction_owner`, `review_owner`, and `availability`. For an available source, `canonical_identity` is the exact path, one NUL separator, and the 40-hex commit; for a source not yet created, it is the planned exact path, one NUL separator, and `Not created`. `availability` is `Available` or `Not created`; unavailable future artifacts use empty `blob_sha1` and `sha256` only because their explicit state is `Not created`.

Every extraction-ledger row contains exactly: `ledger_id`, `source_id`, `coordinate`, `classification`, `normalized_statement`, `decision_status`, `solution_state`, `domains`, `actors`, `roles`, `plans`, `categories`, `countries`, `languages`, `surfaces`, `integrations`, `data_families`, `programs`, `authority_owner`, `system_of_record_effect`, `conflict_group`, `disposition`, `destination_section`, `master_ids`, and `reviewer_verdict`. `solution_state` is `null` only for an Open row; every other row uses one exact legal solution-state string.

`coverage.json` contains sorted `source_to_master`, `master_to_source`, `prd_areas`, `domains`, `actors`, `roles`, `modules`, `verticals`, `workflows`, `integrations`, `data_families`, `security_controls`, `operations`, `programs`, `work_packages`, `milestones`, `adrs`, `matrices`, and `flows`. Each coverage row has `coverage_id`, `subject_id`, `ledger_ids`, `master_ids`, `disposition`, and `verdict`.

Each review report contains `review_id`, `reviewer_identity`, `independent_from_writer`, `candidate_commit`, `candidate_parent`, `master_blob_sha1`, `master_sha256`, `source_inventory_sha256`, `review_packet_commit`, `lenses`, `rounds`, and `final_verdict`. Every lens has a separate verdict even when one reviewer owns multiple lenses. Each finding contains `finding_id`, `severity`, `lens`, `master_ids`, `source_ids`, `consequence`, `acceptance_condition`, `writer_disposition`, `correction_commit`, and `rereview_verdict`.

`signoff.json` contains the complete candidate identity as the six top-level fields `candidate_commit`, `candidate_parent`, `master_blob_sha1`, `master_sha256`, `source_inventory_sha256`, and `review_packet_commit`, plus one row for each required role. Each row contains `role`, `signer_identity`, `scope`, `decision`, `decision_time_plus03`, `master_sha256`, and `limitations`. `decision` must be `Approved` for activation. Qualified regulated-vertical reviewers have additional rows naming country and sensitive-capability scope.

`handoff.json` contains candidate/parent/master identities, review-packet commit, source/ledger/coverage/validation hashes, exact counts, validator commands/results, the four reviewer records and ten lens verdicts, complete Open/Deferred/Superseded IDs, signoff identities, the exact Control activation request, and `execution_authority_granted: false`.

---

### Task 1: Phase 0 — Freeze Entry, Authority, Identity, and Writer Boundary

**Files:**
- Read: `docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json` from the exact committed Control ref
- Read: `docs/superpowers/specs/2026-07-15-dorzak-master-solution-authority-design.md`
- Assert absent: every production path listed above
- No repository write

**Interfaces:**
- Consumes: a new durable Control transition and this approved plan commit.
- Produces: one passed read-only entry gate and a single persistent writer identity used by Tasks 2–20, 22, and 23.

- [ ] **Step 1: Invoke the execution skill without another owner prompt**

Use `superpowers:subagent-driven-development`. The orchestrator must assign one persistent writer task named `/root/dorzak_master_writer`; it must not spawn a fresh writer per task. Fresh subagents are reserved for Task 21 and correction re-review.

Expected: one writer identity, zero reviewer identities with write authority.

- [ ] **Step 2: Resolve one fresh Control HEAD and prove the lease is active at that exact tip**

Run from `/Users/barsha/.codex/worktrees/recover-kyte-p00/master-solution-v1`:

```bash
CONTROL_REF=codex/p00-entry-setup
CONTROL_README=docs/superpowers/control/README.md
CONTROL_HANDOFF=docs/dorzak-launch/CONTROL_ROOM_HANDOFF.md
ENTRY_PATH=docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json
export CONTROL_HEAD="$(git rev-parse "$CONTROL_REF")"
ENTRY_COMMIT="$(git log -1 "$CONTROL_REF" --format=%H -- "$ENTRY_PATH")"
test "$ENTRY_COMMIT" = "$CONTROL_HEAD"
for artifact_path in "$CONTROL_README" "$CONTROL_HANDOFF" "$ENTRY_PATH"; do
  git cat-file -e "$CONTROL_HEAD:$artifact_path"
done
CONTROL_HEAD="$CONTROL_HEAD" node <<'NODE'
const { execFileSync } = require("node:child_process");
const head = process.env.CONTROL_HEAD;
const read = (path) => execFileSync("git", ["show", `${head}:${path}`], { encoding: "utf8" });
const readme = read("docs/superpowers/control/README.md");
const handoff = read("docs/dorzak-launch/CONTROL_ROOM_HANDOFF.md");
const entry = JSON.parse(read("docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json"));
const active = {
  schema_version: 1,
  task_name: "/root/dorzak_master_writer",
  writer_task: "/root/dorzak_master_writer",
  lifecycle: "Approved",
  outcome: "Open",
  lease_status: "Active",
  authorization: "Approved to execute master solution production"
};
for (const [key, value] of Object.entries(active)) {
  if (entry[key] !== value) throw new Error(`CONTROL_LEASE_MISMATCH:${key}`);
}
if (typeof entry.task_id !== "string" || entry.task_id.length === 0) throw new Error("CONTROL_LEASE_MISMATCH:task_id");
for (const [name, text] of [["README", readme], ["HANDOFF", handoff]]) {
  for (const value of [entry.task_id, entry.task_name, entry.authorization, entry.plan_commit]) {
    if (!text.includes(value)) throw new Error(`CONTROL_${name}_MISMATCH`);
  }
}
process.stdout.write(`CONTROL_HEAD PASS head=${head} task=${entry.task_name} lease=Active\n`);
NODE
```

Expected: `ENTRY_COMMIT` equals the freshly resolved `CONTROL_HEAD`; all three paths exist at that exact commit; the exact active task, writer, lifecycle, outcome, lease status, and authorization pass. A later Control commit that does not rewrite/reaffirm the entry, or any revocation/transition value, stops before a production read or write.

- [ ] **Step 3: Validate the registered plan/base identity and exact production checkout**

Run from the production worktree:

```bash
CONTROL_REF=codex/p00-entry-setup
ENTRY_PATH=docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json
test -n "${CONTROL_HEAD:-}"
test "$(git rev-parse "$CONTROL_REF")" = "$CONTROL_HEAD"
test "$(git log -1 "$CONTROL_REF" --format=%H -- "$ENTRY_PATH")" = "$CONTROL_HEAD"
test "$(git branch --show-current)" = "codex/dorzak-master-solution-v1"
test -z "$(git status --short)"
CONTROL_HEAD="$CONTROL_HEAD" node <<'NODE'
const { createHash } = require("node:crypto");
const { execFileSync } = require("node:child_process");
const head = process.env.CONTROL_HEAD;
const run = (args, encoding = "utf8") => execFileSync("git", args, { encoding });
const path = "docs/superpowers/plans/2026-07-15-dorzak-master-solution-authority.md";
const entryPath = "docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json";
const e = JSON.parse(run(["show", `${head}:${entryPath}`]));
const exact = {
  schema_version: 1,
  authorization: "Approved to execute master solution production",
  design_commit: "bc0c39e7794901d7cd879f2a35bb203bc14c6e88",
  current_evidence_sha: "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf",
  production_branch: "codex/dorzak-master-solution-v1",
  production_worktree: "/Users/barsha/.codex/worktrees/recover-kyte-p00/master-solution-v1",
  protected_checkout: "/Users/barsha/Documents/recover Kyte",
  protected_head: "cf2f65b2e308bdf4750c3e02dc1aafa7a7a39a4d",
  protected_status_entries: 16,
  protected_status_sha256: "a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa",
  writer_task: "/root/dorzak_master_writer",
  plan_path: path,
  target_path: "docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md"
};
for (const [key, value] of Object.entries(exact)) {
  if (e[key] !== value) throw new Error(`ENTRY_MISMATCH:${key}`);
}
for (const key of ["plan_commit", "plan_blob_sha1", "production_base_commit"]) {
  if (!/^[0-9a-f]{40}$/.test(e[key])) throw new Error(`ENTRY_MISMATCH:${key}`);
}
if (!/^[0-9a-f]{64}$/.test(e.plan_sha256)) throw new Error("ENTRY_MISMATCH:plan_sha256");
if (e.production_base_commit !== e.plan_commit) throw new Error("ENTRY_MISMATCH:production_base_commit");
if (run(["rev-parse", "HEAD"]).trim() !== e.production_base_commit) throw new Error("PRODUCTION_HEAD_MISMATCH");
if (run(["rev-parse", `${e.plan_commit}^`]).trim() !== "bc0c39e7794901d7cd879f2a35bb203bc14c6e88") throw new Error("PLAN_PARENT_MISMATCH");
if (run(["show", "-s", "--format=%s", e.plan_commit]).trim() !== "docs: plan Dorzak master solution authority") throw new Error("PLAN_SUBJECT_MISMATCH");
if (run(["diff-tree", "--no-commit-id", "--name-status", "-r", e.plan_commit]).trim() !== `A\t${path}`) throw new Error("PLAN_SCOPE_MISMATCH");
const tree = run(["ls-tree", e.plan_commit, path]).trim().split(/\s+/);
if (tree[0] !== "100644" || tree[2] !== e.plan_blob_sha1) throw new Error("PLAN_TREE_MISMATCH");
if (run(["rev-parse", `${e.plan_commit}:${path}`]).trim() !== e.plan_blob_sha1) throw new Error("PLAN_BLOB_MISMATCH");
const planBytes = run(["show", `${e.plan_commit}:${path}`], null);
if (createHash("sha256").update(planBytes).digest("hex") !== e.plan_sha256) throw new Error("PLAN_SHA256_MISMATCH");
if (run(["rev-parse", "bc0c39e7794901d7cd879f2a35bb203bc14c6e88:docs/superpowers/specs/2026-07-15-dorzak-master-solution-authority-design.md"]).trim() !== "843f0ee9a43fc791557503fd97c418ad3685a7b8") throw new Error("DESIGN_BLOB_MISMATCH");
const writerAllowlist = [
  "docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md",
  "docs/dorzak-launch/master-solution-evidence/README.md",
  "docs/dorzak-launch/master-solution-evidence/source-inventory.json",
  "docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl",
  "docs/dorzak-launch/master-solution-evidence/coverage.json",
  "docs/dorzak-launch/master-solution-evidence/validation.json",
  "docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json",
  "docs/dorzak-launch/master-solution-evidence/reviews/02-architecture-data.json",
  "docs/dorzak-launch/master-solution-evidence/reviews/03-security-operations.json",
  "docs/dorzak-launch/master-solution-evidence/reviews/04-delivery-authority.json",
  "docs/dorzak-launch/master-solution-evidence/signoff.json",
  "docs/dorzak-launch/master-solution-evidence/handoff.json",
  "scripts/quality/dorzak-master-solution.mjs",
  "scripts/quality/dorzak-master-solution.test.mjs"
].sort();
if (JSON.stringify(e.writer_allowlist.slice().sort()) !== JSON.stringify(writerAllowlist)) throw new Error("ENTRY_MISMATCH:writer_allowlist");
if (!Array.isArray(e.review_lenses) || e.review_lenses.length !== 10) throw new Error("ENTRY_MISMATCH:review_lenses");
const coreSignoffRoles = [
  "Dorzak owner/product authority",
  "business/commercial lead",
  "product/domain lead",
  "engineering/architecture lead",
  "security/privacy lead",
  "reliability/operations lead",
  "quality/release lead"
];
if (!Array.isArray(e.signoff_nominations)) throw new Error("ENTRY_MISMATCH:signoff_nominations");
for (const role of coreSignoffRoles) {
  if (!e.signoff_nominations.some((row) => row.role === role && row.signer_identity)) throw new Error(`ENTRY_MISMATCH:signoff:${role}`);
}
process.stdout.write(`MASTER_ENTRY PASS plan=${e.plan_commit} base=${e.production_base_commit}\n`);
NODE
```

Expected: `MASTER_ENTRY PASS` with identical registered plan/base commits and exit `0`. The values come from the later Control entry and are checked against Git bytes; the plan never embeds its own commit, blob, or file hash. Any plan/base/entry/checkout mismatch stops before creating a file.

- [ ] **Step 4: Verify the protected checkout without changing it**

Run:

```bash
PROTECTED='/Users/barsha/Documents/recover Kyte'
test "$(git -C "$PROTECTED" branch --show-current)" = "feat/premium-features"
test "$(git -C "$PROTECTED" rev-parse HEAD)" = "cf2f65b2e308bdf4750c3e02dc1aafa7a7a39a4d"
test "$(git -C "$PROTECTED" status --short | LC_ALL=C sort | wc -l | tr -d ' ')" = "16"
test "$(git -C "$PROTECTED" status --short | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = "a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa"
```

Expected: all commands exit `0`; do not print or persist the protected status contents.

- [ ] **Step 5: Verify target absence and runtime pins**

Run:

```bash
for artifact_path in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence \
  scripts/quality/dorzak-master-solution.mjs \
  scripts/quality/dorzak-master-solution.test.mjs; do
  test ! -e "$artifact_path"
done
test "$(cat .nvmrc)" = "24.18.0"
test "$(node --version)" = "v24.18.0"
test "$(npm --version)" = "11.16.0"
```

Expected: all targets absent; exact runtime versions printed only by the command substitutions; exit `0`. If ambient Node is different, select the already installed pinned runtime and rerun; do not install anything.

- [ ] **Step 6: Record the no-write task result in the orchestrator handoff**

Return exactly these nonsecret identities to Control/orchestrator memory: frozen `CONTROL_HEAD`/`ENTRY_COMMIT`, registered plan commit/blob/SHA-256/production base, production `HEAD`, branch, worktree, protected HEAD/status count/status hash, current-evidence SHA, writer task, 14-path allowlist count, ten review-lens count, and `PHASE_0 PASS`.

Expected: no Git diff and no commit.

---

### Task 2: Build the Dependency-Free Deterministic Validator with Tests

**Files:**
- Create: `scripts/quality/dorzak-master-solution.test.mjs`
- Create: `scripts/quality/dorzak-master-solution.mjs`

**Interfaces:**
- Consumes: the schemas and exact exports in “Shared Interfaces and Conventions.”
- Produces: the CLI and exported validation functions used by every later task.

- [ ] **Step 1: Write the validator contract tests before the implementation**

Use `apply_patch` to create `scripts/quality/dorzak-master-solution.test.mjs`. Use `node:test`, `node:assert/strict`, `node:fs`, `node:os`, and `node:path` only. The 18 named tests must cover:

```js
const requiredTests = [
  "canonicalJson recursively sorts keys and ends with one LF",
  "parseJsonl rejects blank rows and duplicate ledger IDs",
  "inventory rejects unsorted IDs and mismatched Git blob or SHA-256",
  "inventory accepts explicit Not created future artifacts",
  "ledger rejects an unknown source and a missing disposition",
  "ledger rejects an illegal decision-status and solution-state pair",
  "coverage rejects an uncovered ledger row",
  "coverage rejects a normative master ID without provenance",
  "master requires exactly five named top-level parts",
  "master requires DOM-001 through DOM-053 once with 16 ordered fields",
  "master requires all 36 PRD IDs, 20 programs, four work packages, and ten milestones",
  "master requires 18 matrices and 12 flows",
  "master rejects an unfinished marker, empty table cell, hidden incomplete comment, and unexplained ellipsis",
  "master rejects a Current statement without the accepted evidence SHA",
  "master rejects approval-blocking Open records and incomplete nonblocking Open records",
  "master rejects duplicate stable IDs and missing glossary terms",
  "reviews require four independent reports and ten separate zero-blocker lens verdicts",
  "signoff and handoff bind every required role to the exact candidate bytes without execution authority"
];
```

The review/signoff test fixtures must supply the complete six-field `candidateIdentity` object and independently prove that omission or mutation of candidate commit, candidate parent, master blob SHA-1, master SHA-256, source-inventory SHA-256, or review-packet commit fails. The handoff fixture must bind the same six fields.

Each test constructs its own temporary fixture under `mkdtemp(join(tmpdir(), "dorzak-master-validator-"))`, deletes it in `afterEach`, and asserts the exact stable error code. Do not read the protected checkout, network, clock, or environment except an explicit test-local object.

- [ ] **Step 2: Run the test to prove the implementation is absent**

Run:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
```

Expected: exit `1`; the sole setup failure is `ERR_MODULE_NOT_FOUND` for `scripts/quality/dorzak-master-solution.mjs`. A syntax error or any different failure stops.

- [ ] **Step 3: Implement the exact exports and CLI**

Use `apply_patch` to create `scripts/quality/dorzak-master-solution.mjs`. Implementation requirements:

```js
import { createHash } from "node:crypto";
import { execFileSync } from "node:child_process";
import { readFileSync, writeFileSync } from "node:fs";
import { resolve } from "node:path";
import { fileURLToPath } from "node:url";

export const MASTER_PATH = "docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md";
export const EVIDENCE_ROOT = "docs/dorzak-launch/master-solution-evidence";
export const CURRENT_EVIDENCE_SHA = "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf";
export const DOMAIN_IDS = Array.from({ length: 53 }, (_, index) => `DOM-${String(index + 1).padStart(3, "0")}`);
export const PRD_IDS = Array.from({ length: 36 }, (_, index) => `PRD-${String(index + 1).padStart(3, "0")}`);
export const PROGRAM_IDS = Array.from({ length: 20 }, (_, index) => `P${String(index).padStart(2, "0")}`);
export const WORK_PACKAGE_IDS = ["WP-M2", "WP-P04T", "WP-P09A", "WP-P09B"];
export const MILESTONE_IDS = Array.from({ length: 10 }, (_, index) => `M${index}`);
export const MATRIX_IDS = Array.from({ length: 18 }, (_, index) => `MAT-${String(index + 1).padStart(3, "0")}`);
export const FLOW_IDS = Array.from({ length: 12 }, (_, index) => `FLOW-${String(index + 1).padStart(3, "0")}`);
```

Implement every export named earlier. The master parser must recognize only ATX Markdown headings outside fenced blocks, visible stable-ID prefixes, pipe tables, and explicit domain field headings. It must not use a Markdown package. Git reads use `execFileSync("git", ["show", `${commit}:${path}`], { encoding: "buffer", stdio: ["ignore", "pipe", "pipe"] })`; never interpolate a shell command.

The 16 exact domain field headings are:

```js
export const DOMAIN_FIELDS = [
  "Identity and outcome",
  "Status and scope",
  "Actors and permissions",
  "Current reality",
  "Target behavior",
  "Invariants",
  "Workflows and states",
  "Data and authority",
  "Interfaces and events",
  "Security, privacy, and audit",
  "Experience",
  "Operations",
  "Dependencies and ownership",
  "Current-to-target transition",
  "Verification",
  "Decisions and provenance"
];
```

The validator must emit stable failures sorted by check ID and must redact input content from errors. It checks secret-shaped assignments and common credential prefixes without echoing matches. It checks links with `git cat-file -e` against the candidate commit and separately performs link-free completeness checks.

- [ ] **Step 4: Run all validator tests**

Run:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node --check scripts/quality/dorzak-master-solution.mjs
```

Expected: Node TAP summary `tests 18`, `pass 18`, `fail 0`; syntax check exits `0` with no output.

- [ ] **Step 5: Verify dependency and scope boundaries**

Run:

```bash
git diff -- package.json package-lock.json .nvmrc
git status --short
```

Expected: first command has no output; status lists only the two new validator paths.

- [ ] **Step 6: Commit the validator**

```bash
git add scripts/quality/dorzak-master-solution.mjs scripts/quality/dorzak-master-solution.test.mjs
test "$(git diff --cached --name-only | LC_ALL=C sort)" = "$(printf '%s\n' scripts/quality/dorzak-master-solution.mjs scripts/quality/dorzak-master-solution.test.mjs | LC_ALL=C sort)"
git commit -m "chore(docs): add master solution validator"
```

Expected: one commit containing exactly two `100644` files; clean worktree.

---

### Task 3: Phase 1 — Freeze the Sorted Deterministic Source Inventory

**Files:**
- Create: `docs/dorzak-launch/master-solution-evidence/README.md`
- Create: `docs/dorzak-launch/master-solution-evidence/source-inventory.json`
- Test: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: exact source objects and current evidence at `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` plus the exact Control/design sources.
- Produces: an immutable sorted source set and SHA-256 consumed by extraction, coverage, drafting, review, and handoff.

- [ ] **Step 1: Extend tests for the real seed identities**

Use `apply_patch` to add one test named `repository seed sources match their pinned commits, blobs, and hashes`. It must build these mandatory unnumbered candidate identities before enumeration and final ID assignment:

| Seed key | Authority | Exact path | Exact commit | Blob SHA-1 | File SHA-256 |
|---|---:|---|---|---|---|
| `CONTROL-README` | 1 | `docs/superpowers/control/README.md` | frozen `CONTROL_HEAD` | derive from `CONTROL_HEAD:path` | SHA-256 of exact `CONTROL_HEAD:path` bytes |
| `CONTROL-HANDOFF` | 1 | `docs/dorzak-launch/CONTROL_ROOM_HANDOFF.md` | frozen `CONTROL_HEAD` | derive from `CONTROL_HEAD:path` | SHA-256 of exact `CONTROL_HEAD:path` bytes |
| `CONTROL-MASTER-ENTRY` | 1 | `docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json` | frozen `CONTROL_HEAD` | derive from `CONTROL_HEAD:path` | SHA-256 of exact `CONTROL_HEAD:path` bytes |
| `OWNER-P17` | 1 | `docs/superpowers/control/approvals/2026-07-14-p17-frappe-native-superadmin-owner-decision.md` | `ec6989f095377118a12aaab5a63f0ed4bed00f33` | `f056d51a9a8c5898ea7708c1bf70bf34c26d70d3` | `4c071b71da8aad85589bbf958168dd3c3953ac75c0efdb0c30a770f57f09713e` |
| `PRODUCT-BASELINE` | 2 | `docs/superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md` | `cc4085cbca11e89257ae8535438db6cfe3dd75cc` | `f55ffa79cdb91e67606be3ee0c3a4797811e12ca` | `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2` |
| `TECHNICAL-ROADMAP` | 3 | `docs/superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md` | `10730b447cc551efc2ebcd1e3e7c42968dc64389` | `341507a8e32101c83a61a5fe7ebcbd279cc4dc7f` | `5e73f8bc9e6a9c6b1e0a36fbbbe3d014c785acd8c5ca73b57e6f1a1bac600cb0` |
| `P00-DESIGN` | 4 | `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md` | `ea7b8258083231c6a9b7aa7c00d89009e29e696e` | `f88407b32366bf13c36451d63f9bd0d2855afd6c` | `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8` |
| `P00-SAFETY-ERRATUM` | 4 | `docs/superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md` | `59defd5dd36410d487679250c05d2be1d828c094` | `2384ef59ee5be04b2607d2fa19d76c33facf0504` | `7af33dd41be2dca4490d512118d86dc14aa48f822a5051215927c88a66cd6024` |
| `P00-PLAN` | 5 | `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md` | `10730b447cc551efc2ebcd1e3e7c42968dc64389` | `bc623c18adc937fcd7b1a9d69ae902ac6c04f58e` | `912bbdee7ee446e4184713e55215c8ffad30a7a3bb62025e19ee68b1bb337c91` |
| `P00-EXECUTION-ENTRY` | 6 | `docs/superpowers/control/execution/p00-execution-entry.json` | `5357ca757fc2ea35e82f5c17680469f486d95143` | `5f74c2ab773f9a1c14e82389873eed97e1890a1b` | `dafd2e01e4fa6a8311554085d78c3d1a034c028dc3f3aac2774fafb32bade8fa` |
| `CONTEXT` | 3 | `CONTEXT.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `f88e4c3be38a9cae34b49e129aa87657f94ef582` | `e924c78987543037a3b1d779777ac9f53d251804b886cd183bc43bb3adfe0eae` |
| `ADR-0001` | 3 | `docs/adr/0001-system-of-record-authority.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `c783fdf2086486fb240db9a75bebc6bae86ea10a` | `cf47024f6457ecf12693bfc9e4293ae4a1a3ef23545fb07b38891e0f675c581b` |
| `ADR-0002` | 3 | `docs/adr/0002-organization-location-and-isolated-erpnext-tenancy.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `9a79706390f2c4f3e36b35ecae68c32416e2d380` | `8c061f8aae74e60a1d7305ffbc72cbca6cabe64884877cc2cca95e19f3253abf` |
| `ADR-0003` | 3 | `docs/adr/0003-modular-monolith-and-external-adapters.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `ddf68cb105694583fe79083866b3b3381a033fce` | `998a1818c1b1965b6b9d51c4901b7485234de9fc30ef704b10a612b207b8ad7a` |
| `ADR-0004` | 3 | `docs/adr/0004-one-complete-public-launch.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `c10e69399a7be2e65b6abf4e054dd6ed81c8cd5f` | `d1360416cfb1270bbcd850b88cf03918ac6bbf02800c69ac0918c0f56c3e9bc6` |
| `ADR-0005` | 3 | `docs/adr/0005-immutable-plan-publication.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `1899e3d2ea8af6a4c2da34e78aba3f52c2d386bc` | `0197b255f2c57eb06ded9081a3658e37918f5bb781275b0d4b0545ca5dd026dc` |
| `ADR-0006` | 3 | `docs/adr/0006-commerce-cutover-and-no-dual-write.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `b31214eb3238ff260241af9ec95ae973bcae3b75` | `ccd5125ffcc56e0ed4dc35404d6f0005c98c0add77080ac13b9122828f1abbe9` |
| `ADR-0007` | 3 | `docs/adr/0007-frontend-surface-boundaries.md` | `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf` | `addfd9286dafc4b2d4acba4182a2b398b3243c5a` | `9816adb50e43cbbbac4b23c1ba2f374d60917c89d00cade9ac23a182d9a04134` |
| `MASTER-AUTHORITY-DESIGN` | 0 | `docs/superpowers/specs/2026-07-15-dorzak-master-solution-authority-design.md` | `bc0c39e7794901d7cd879f2a35bb203bc14c6e88` | `843f0ee9a43fc791557503fd97c418ad3685a7b8` | `0c9ee0271808cbb723df641a1ee625f58976d19c2f283845cf9206e12da11cef` |

The same test must fail on a duplicate `canonical_identity`, incompatible metadata for one canonical identity, ID assignment before the full sort, or any final `SRC-NNNN` sequence that does not exactly follow the one global sort tuple.

- [ ] **Step 2: Run the new seed test before the inventory exists**

Run:

```bash
node --test --test-name-pattern="repository seed sources" scripts/quality/dorzak-master-solution.test.mjs
```

Expected: exit `1` with the exact stable failure `SOURCE_INVENTORY_MISSING`.

- [ ] **Step 3: Create the audit-boundary README**

Use `apply_patch` to create `docs/dorzak-launch/master-solution-evidence/README.md`. It must state:

1. the directory is audit evidence and never required reading for the master;
2. none of its files grants execution or product authority;
3. the master must remain link-free comprehensible;
4. exact schemas, sorting, LF, hashing, and reproduction commands;
5. one-writer and read-only-reviewer boundaries;
6. the accepted Current-evidence SHA;
7. source changes invalidate inventory, ledger, coverage, candidate, validation, reviews, and signoff; and
8. Control alone activates the master and separately authorizes later work.

The README contains no missing-content prompt and no alternate source of product meaning.

- [ ] **Step 4: Create the complete source inventory**

Use `apply_patch` to create `source-inventory.json`. First collect every mandatory seed above and every enumerated source below into an unnumbered map keyed by `canonical_identity`; then:

- every durable approval record under `docs/superpowers/control/approvals/` at its last controlling commit, deduplicated when its exact path+commit is already a mandatory seed such as the P17 decision;
- the approved session-orchestration design;
- all tracked P00 execution evidence and runbook/control artifacts at their controlling commits;
- every tracked `backend/app`, `backend/database`, `backend/routes`, `backend/tests`, `scripts/quality`, `.github/workflows`, frontend `src`, and `tests/e2e` path at the Current-evidence SHA, grouped by exact file rather than a directory wildcard in the final JSON;
- the tracked marketing and Work/Gantt artifacts as authority level 7 Historical material, preserving only compatible details;
- one explicit `Not created` row for each unavailable P01–P19 design and plan; and
- one explicit `Not created` row for each unavailable `WP-M2`, `WP-P04T`, `WP-P09A`, and `WP-P09B` design and plan.

Reject two candidate objects with the same `canonical_identity`; merge compatible metadata into the one exact object and stop on incompatible metadata. The latest Control README, handoff, and master-production entry are only the three `CONTROL_HEAD` objects above. An older Control transition is included only as a separately identified Historical source with its own exact path+commit and explicit reason; it never replaces or duplicates the current Control objects.

Only after the complete candidate map is deduplicated, sort once by this tuple: numeric authority level, Available before Not created, effective date, path, commit. Assign `SRC-0001` upward to that final sorted array. No seed key predetermines a source ID. For code/evidence rows, set state to Current and authority level 6; never infer target behavior from them. For historical rows, name every controlling higher-source boundary and supersession.

- [ ] **Step 5: Validate exact bytes and sorted inventory**

Run:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node scripts/quality/dorzak-master-solution.mjs inventory
shasum -a 256 docs/dorzak-launch/master-solution-evidence/source-inventory.json
git status --short
```

Expected: all validator tests pass; CLI prints `SOURCE_INVENTORY PASS available=N not_created=46 mismatches=0`, where `N` is an integer greater than zero and is frozen in the inventory commit; SHA-256 prints one 64-hex digest; status lists only the README, inventory, and updated validator test.

- [ ] **Step 6: Recheck frozen sources immediately before commit**

Run without editing between checks:

```bash
test "$(git rev-parse codex/p00-entry-setup)" = "$CONTROL_HEAD"
test "$(git log -1 codex/p00-entry-setup --format=%H -- docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json)" = "$CONTROL_HEAD"
node scripts/quality/dorzak-master-solution.mjs inventory
```

Expected: both Control-tip checks pass, followed by an identical inventory PASS line and identical inventory SHA-256. The inventory validator independently rejects a changed Control tip or entry-last-change commit. Any source-ref movement, blob mismatch, hash mismatch, duplicate canonical identity, or non-global source-ID order returns to Task 1.

- [ ] **Step 7: Commit the frozen source inventory**

```bash
git add docs/dorzak-launch/master-solution-evidence/README.md docs/dorzak-launch/master-solution-evidence/source-inventory.json scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: freeze master solution sources"
```

Expected: exactly three changed paths; clean worktree. Record the commit and source-inventory SHA in orchestrator memory.

---

### Task 4: Phase 2A — Extract Authority and All 36 Product-Baseline Areas

**Files:**
- Create: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl`
- Modify: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: the frozen current-Control rows selected by exact path+`CONTROL_HEAD`, the complete-launch baseline row selected by exact path+commit, all bounded later-decision rows, and the frozen inventory hash.
- Produces: reviewed ledger rows for authority plus every normative block and stable requirement in PRD areas 1–36.

- [ ] **Step 1: Add extraction-coordinate and completeness tests**

Use `apply_patch` to add tests that require deterministic coordinates:

```text
Markdown paragraph: heading/path > paragraph:NNN
Markdown list item: heading/path > list:NNN/item:NNN
Markdown table row: heading/path > table:NNN/row:NNN
JSON value: RFC 6901 JSON Pointer
Code or schema fact: repository path > symbol-or-migration-name > fact:NNN
Test evidence: repository path > test-name > assertion:NNN
```

The tests must reject duplicate coordinates within one source, ordinal gaps, unknown classifications, a row with no affected dimension across all scope arrays, a weakened normalized statement, and a row whose reviewer verdict is not `Verified`.

- [ ] **Step 2: Run the new tests before the ledger exists**

Run:

```bash
node --test --test-name-pattern="extraction" scripts/quality/dorzak-master-solution.test.mjs
```

Expected: exit `1` with only `EXTRACTION_LEDGER_MISSING`.

- [ ] **Step 3: Extract source hierarchy, authority, lifecycle, status, and conflict rules**

Use `apply_patch` to create the JSONL file. Set `SOURCE_COMMIT` and `SOURCE_PATH` from the inventory row and read bytes only with `git show "$SOURCE_COMMIT:$SOURCE_PATH"`. Create one row for every normative paragraph, list item, and table row governing:

- product/solution authority versus Control execution authority versus implemented truth;
- lifecycle from absent through superseded;
- source hierarchy and bounded later-decision precedence;
- deterministic conflict resolution;
- the five exact decision statuses and legal solution-state combinations;
- complete Open-decision requirements and approval-blocking subjects;
- one-complete-launch, current-evidence, public-claim, and nonauthorization rules; and
- source change, writer, reviewer, protected-state, and stop boundaries.

Use `preserve`, `consolidate`, `supersede`, `defer`, or `Open` exactly for disposition. A later decision overrides an older row only inside its recorded scope; both rows remain traceable through one conflict group.

- [ ] **Step 4: Extract all 36 PRD areas without heading-only coverage**

For each `PRD-001` through `PRD-036`, extract every normative subsection and stable requirement from the inventory row whose exact identity is path `docs/superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md` plus commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`. Each row must name that row's post-sort source ID, at least one destination stable-ID family, and every affected domain/program dimension. The required area mapping is exact:

```text
PRD-001 authority/supersession       PRD-019 shared modules
PRD-002 executive product decision  PRD-020 Work/managed delivery
PRD-003 goals/non-goals/boundaries   PRD-021 marketing/CRM/loyalty/comms
PRD-004 actors/ownership             PRD-022 delivery/fulfillment
PRD-005 target architecture          PRD-023 source reuse
PRD-006 ERPNext lifecycle            PRD-024 localization/country packs
PRD-007 commercial catalogue/plans   PRD-025 security/privacy/trust
PRD-008 org/location/Party/isolation PRD-026 reliability/performance/operations
PRD-009 merchant categories          PRD-027 analytics/instrumentation
PRD-010 vertical requirements        PRD-028 current-state gap assessment
PRD-011 website/storefront/builder   PRD-029 program decomposition
PRD-012 Free Tools                   PRD-030 testing strategy
PRD-013 corporate website            PRD-031 launch acceptance/signoff
PRD-014 Our Clients                  PRD-032 user stories
PRD-015 design system                PRD-033 risks/mitigations
PRD-016 merchant/customer experience PRD-034 out-of-scope/future scope
PRD-017 Superadmin control plane     PRD-035 definition of done
PRD-018 subscriptions/payments       PRD-036 evidence/sources
```

Do not use a similarly named heading as proof. A row is complete only when its normalized rule preserves numeric limits, plan/country variation, owner, states, deadlines, failure behavior, and acceptance condition from the source.

- [ ] **Step 5: Independently inspect the product extraction slice**

The orchestrator dispatches one read-only extraction checker. It receives the source-inventory hash, exact source commit/hash, and ledger bytes. It returns a sorted list of missing coordinates, weakened rows, conflicts, and wrong dispositions. It must not edit a file.

The writer applies every valid correction serially with `apply_patch`. Any unresolved authority or product conflict stops.

- [ ] **Step 6: Validate and commit the authority/product ledger slice**

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { parseJsonl, validateExtractionLedger } from "./scripts/quality/dorzak-master-solution.mjs";
const inventory = JSON.parse(readFileSync("docs/dorzak-launch/master-solution-evidence/source-inventory.json", "utf8"));
const rows = parseJsonl(readFileSync("docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl", "utf8"), "extraction-ledger.jsonl");
const result = validateExtractionLedger(rows, inventory);
if (result.errors.length) process.exit(1);
console.log(`LEDGER_SLICE PASS rows=${rows.length} product_areas=36`);
'
git diff --check
git status --short
```

Expected: `LEDGER_SLICE PASS rows=N product_areas=36`, where `N` is the frozen positive row count; diff check exits `0`; status lists only ledger and validator test paths.

Commit:

```bash
git add docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: extract master product authority"
```

Expected: exactly two paths; clean worktree.

---

### Task 5: Phase 2B — Extract Roadmap, ADRs, Programs, and Durable Decisions

**Files:**
- Modify: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl`

**Interfaces:**
- Consumes: approved roadmap, `CONTEXT.md`, ADRs 0001–0007, P00 design/erratum/plan, durable approvals, and the Task 4 ledger.
- Produces: complete architecture, program, work-package, decision, and acceptance-gate rows.

- [ ] **Step 1: Extract roadmap and milestone rules**

Append canonical JSONL rows in source-coordinate order for all roadmap requirements covering:

- M0–M9 sequence, planning entry, execution entry, milestone exit, serialized ownership, allowed same-milestone parallelism, and integrated evidence;
- P00–P19 outcome, dependency, target contribution, and acceptance evidence;
- WP-M2, WP-P04T, WP-P09A, and WP-P09B boundaries;
- Organization/Location/Party/tenancy, money, idempotency, outbox, audit, ERP interface, immutable plan, cutover, surface, and release invariants; and
- all architecture, testing, migration, operations, rollback, and signoff gates.

Every future artifact recorded as `Not created` may contribute only an accepted outcome/dependency from a higher source; it cannot contribute nonexistent detail.

- [ ] **Step 2: Extract `CONTEXT.md` and ADRs 0001–0007**

Create complete rows for these seven decision boundaries and their verification consequences:

1. system-of-record authority;
2. Organization/Location and isolated ERPNext tenancy;
3. Laravel modular monolith and external adapters;
4. one complete public launch;
5. immutable plan publication;
6. commerce cutover with no dual write; and
7. merchant, public/customer, and Frappe-native Superadmin surfaces.

Each ADR row names its decision, rationale constraint, scope, incompatible alternative, affected master IDs, and verification boundary. If `CONTEXT.md` repeats an ADR, use `consolidate` and retain both source rows.

- [ ] **Step 3: Extract bounded P00 design, safety erratum, and approved plan**

Extract requirements that define Current-evidence quality, runtime, tenant/database safety, evidence, ADR/runbook, and release-baseline boundaries. The database-safety erratum supersedes only the exact affected P00 plan statements. Execution details never become product target decisions unless a higher source already requires them.

- [ ] **Step 4: Extract every durable later owner decision**

For every available approval/control decision row, record source ID, precise scope, effective date, affected master IDs, conflicts, and replacement. In particular preserve:

- Frappe-native internal Superadmin, strict isolated merchant sites, governed time-limited intervention, and explicit Dorzak/Frappe ownership;
- Free Tools/Pro/Business/Enterprise replacing Free/Pro/Scale;
- one paid Organization with multiple Locations replacing location-minimum assumptions;
- mandatory isolated ERPNext per paid organization replacing optional ERPNext;
- one complete public launch replacing partial launch; and
- no ambient Superadmin access.

Older contradictory rows use `Superseded` and name the replacement ID; they are not silently omitted.

- [ ] **Step 5: Run a read-only architecture/decision extraction check**

Dispatch a fresh read-only checker against exact source and ledger hashes. Require separate verdicts for roadmap completeness, ADR mapping, later-decision survival, and conflict disposition. The writer applies corrections; any unresolved system-of-record, tenancy, money, security, migration, or release conflict stops.

- [ ] **Step 6: Validate and commit**

Run the complete ledger validator and additionally assert:

```bash
node scripts/quality/dorzak-master-solution.mjs ledger
for id in $(printf 'ADR-%04d\n' 1 2 3 4 5 6 7) P00 P01 P02 P03 P04 P05 P06 P07 P08 P09 P10 P11 P12 P13 P14 P15 P16 P17 P18 P19 WP-M2 WP-P04T WP-P09A WP-P09B M0 M1 M2 M3 M4 M5 M6 M7 M8 M9; do
  rg -q "\"$id\"" docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl
done
```

Expected: ledger validation PASS; every ID search exits `0`; no non-ledger diff.

Commit:

```bash
git add docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl
git commit -m "docs: extract master architecture decisions"
```

Expected: exactly one path; clean worktree.

---

### Task 6: Phase 2C — Extract Verified Current Reality and Close the Ledger

**Files:**
- Modify: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl`
- Modify: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: every Current-evidence inventory row at `4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf`, verified P00 execution evidence, historical tracked artifacts, and prior ledger slices.
- Produces: one fully dispositioned ledger with zero source-statement gaps.

- [ ] **Step 1: Add Current-evidence and final-completeness tests**

Tests must reject a Current row unless it has: exact Current-evidence SHA, concrete file/symbol/test coordinate, evidence classification, a factual normalized statement, no Target implication, affected domain/data owner, and reviewer verdict `Verified`. Add a final test that compares the parser’s complete normative-coordinate inventory to ledger coordinates and reports only missing stable coordinate IDs.

- [ ] **Step 2: Extract implementation facts without promoting them into target authority**

For each Current-evidence source, extract public behavior, data/schema ownership, interface, failure, security, operation, and test facts that the master needs to describe present reality. Group internal lines only when they prove one indivisible fact; keep exact symbol/migration/test coordinates. Record limitations and known debt as Current facts. A code disagreement with the approved target becomes a current-to-target gap, never a target rewrite.

At minimum cover current Laravel modules/models/services/routes/migrations, React surfaces/stores/API adapters, quality scripts/workflows, SQLite/PostgreSQL/browser evidence, `CONTEXT.md`, ADRs, and P00 runbook/control evidence.

- [ ] **Step 3: Disposition historical marketing and Work/Gantt material**

Extract only preserved compatible requirements. Mark obsolete commercial hierarchy, partial-launch, optional-ERPNext, location-minimum, or ambient-Superadmin claims `supersede`; mark out-of-complete-launch proposals `defer` with the exact earliest reconsideration gate; never use an older artifact to override a higher source.

- [ ] **Step 4: Close every duplicate and conflict group**

Sort by `ledger_id`. Every row has exactly one disposition, at least one destination master ID, and `reviewer_verdict: "Verified"`. Each conflict group has one controlling row or a complete Open record. Approval-blocking conflicts must be zero.

- [ ] **Step 5: Run two read-only completeness checks in parallel**

One checker compares all authoritative Markdown/JSON coordinates to the ledger. One checker compares Current code/schema/test/runbook facts to ledger Current rows. Both return only structured findings; neither writes. The writer corrects all missing or weakened rows and reruns both checks until each reports zero gap.

- [ ] **Step 6: Validate the complete ledger and frozen inventory**

Run:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node scripts/quality/dorzak-master-solution.mjs inventory
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { parseJsonl, validateExtractionLedger } from "./scripts/quality/dorzak-master-solution.mjs";
const inventory = JSON.parse(readFileSync("docs/dorzak-launch/master-solution-evidence/source-inventory.json", "utf8"));
const rows = parseJsonl(readFileSync("docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl", "utf8"), "extraction-ledger.jsonl");
const result = validateExtractionLedger(rows, inventory);
if (result.errors.length || result.uncoveredCoordinates !== 0 || result.approvalBlockingConflicts !== 0) process.exit(1);
console.log(`EXTRACTION_LEDGER PASS rows=${rows.length} source_gaps=0 blocking_conflicts=0`);
'
shasum -a 256 docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl
```

Expected: all tests pass; inventory remains exact; ledger prints zero gaps/conflicts; one 64-hex ledger hash.

- [ ] **Step 7: Commit the complete extraction ledger**

```bash
git add docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: complete master solution extraction"
```

Expected: exactly two paths; clean worktree. Freeze and record the ledger SHA-256.

---

### Task 7: Phase 3 — Build Bidirectional Coverage Before Drafting

**Files:**
- Create: `docs/dorzak-launch/master-solution-evidence/coverage.json`
- Modify: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: frozen source inventory and complete extraction ledger.
- Produces: a complete predeclared stable-ID map used as the drafting contract and later reconciled against actual master IDs.

- [ ] **Step 1: Add bidirectional and catalogue tests**

Tests must fail when: a ledger row lacks a destination; a planned master ID lacks provenance; a disposition illegally completes an active requirement; one required catalogue subject is missing; two rows claim different controlling target rules; or an ID is duplicated. `Deferred` and `Superseded` rows cannot satisfy an active Target requirement. A complete nonblocking Open record may provide provenance only for its explicit unresolved question and fail-closed interim rule.

- [ ] **Step 2: Run the coverage tests before the file exists**

Expected command and result:

```bash
node --test --test-name-pattern="coverage" scripts/quality/dorzak-master-solution.test.mjs
```

Exit `1` with only `COVERAGE_FILE_MISSING`.

- [ ] **Step 3: Create source-to-master and master-to-source mappings**

Use `apply_patch` to create canonical `coverage.json`. Map every ledger row to exact destination stable IDs or its explicit disposition. Predeclare every normative master ID before drafting; each planned ID names one or more controlling ledger rows. Do not create prose-only destinations.

- [ ] **Step 4: Populate every required catalogue set**

Coverage must enumerate and map:

- `PRD-001`–`PRD-036`;
- `DOM-001`–`DOM-053`;
- `P00`–`P19`, four work packages, and `M0`–`M9`;
- ADRs 0001–0007;
- all actors and roles in design Section 5.1;
- all 13 modules in design Section 5.2;
- Free Tools, Pro, Business, Enterprise;
- Qatar, Tunisia, English, French, Arabic, and right-to-left behavior;
- every supported vertical plus General Business;
- every success, failure, conflict, timeout, retry, compensation, reconciliation, migration, rollback, support, and release workflow;
- every external integration class and provider-neutral boundary;
- every authoritative, projected, provider-owned, derived, and historical data family;
- authentication, authorization, tenant isolation, money, inventory, sensitive data, privacy, and audit controls; and
- provisioning, queues, observability, incidents, backups, restores, upgrades, support, capacity, performance, accessibility, and release operations.

Also reserve `MAT-001`–`MAT-018` and `FLOW-001`–`FLOW-012` with exact ledger provenance.

- [ ] **Step 5: Run a read-only coverage audit**

Dispatch a fresh checker with only source inventory, ledger, coverage, and design bytes. It reports missing subjects, wrong dispositions, circular provenance, contradictory master destinations, and catalogue-count errors. The writer applies all corrections. Zero source and planned-master gaps are required before Part I is written.

- [ ] **Step 6: Validate and commit coverage**

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { parseJsonl, validateCoverage } from "./scripts/quality/dorzak-master-solution.mjs";
const inventory = JSON.parse(readFileSync("docs/dorzak-launch/master-solution-evidence/source-inventory.json", "utf8"));
const rows = parseJsonl(readFileSync("docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl", "utf8"), "extraction-ledger.jsonl");
const coverage = JSON.parse(readFileSync("docs/dorzak-launch/master-solution-evidence/coverage.json", "utf8"));
const result = validateCoverage(coverage, inventory, rows, "");
if (result.errors.length || result.uncoveredSource !== 0 || result.unplannedMaster !== 0) process.exit(1);
console.log("COVERAGE_PLAN PASS prd=36 domains=53 programs=20 work_packages=4 milestones=10 matrices=18 flows=12 uncovered_source=0 unplanned_master=0");
'
shasum -a 256 docs/dorzak-launch/master-solution-evidence/coverage.json
git diff --check
```

Expected: exact PASS line, one 64-hex hash, no diff errors.

Commit:

```bash
git add docs/dorzak-launch/master-solution-evidence/coverage.json scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: map master solution coverage"
```

Expected: exactly two paths; clean worktree. Freeze the coverage hash for drafting.

---

### Task 8: Phase 4A — Assemble Part I Authority, Product, and Orientation

**Files:**
- Create: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: frozen ledger and coverage contract for all `AUTH-*` IDs.
- Produces: a complete Part I with no empty future sections; later parts are not added until their tasks.

- [ ] **Step 1: Revalidate frozen production inputs**

Run the exact pre-draft checks and compare all three hashes with the frozen orchestrator values:

```bash
node scripts/quality/dorzak-master-solution.mjs inventory
node scripts/quality/dorzak-master-solution.mjs ledger
node scripts/quality/dorzak-master-solution.mjs coverage
shasum -a 256 docs/dorzak-launch/master-solution-evidence/source-inventory.json docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl docs/dorzak-launch/master-solution-evidence/coverage.json
```

Expected: identical hashes, zero source/ledger/coverage gaps, clean worktree.

- [ ] **Step 2: Create the master identity and exact five-part reading contract**

Use `apply_patch` to create the master with one H1 title and the completed Part I only. Do not add empty headings for Parts II–V. The identity block states:

- semantic version `1.0.0-candidate`;
- exact Current-evidence baseline and date;
- source-inventory hash;
- complete-launch target and one-launch policy;
- lifecycle rule: these bytes are a candidate until team signoff and Control activation bind their exact hash, then they are the active master;
- product/solution authority versus Control execution authority versus implementation truth; and
- revision/replacement history with `Initial candidate` as the complete present record.

The reading map names all five eventual parts and explains what each answers without relying on a link.

- [ ] **Step 3: Write the authority, status, conflict, and decision model**

Write complete self-contained prose and embedded `MAT-001` authority-by-question matrix. Include exact source hierarchy, bounded later-decision precedence, current/code gap handling, the five decision statuses, legal solution-state combinations, complete Open records, activation lifecycle, and stop rules. Every normative paragraph begins with its assigned `AUTH-NNN` ID.

- [ ] **Step 4: Write the executive product decision and launch boundary**

Explain in plain language what Dorzak is, for whom, why it combines Dorzak-native experience with isolated ERPNext operating records, the plan/vertical promise, Qatar/Tunisia and EN/FR/AR scope, goals, non-goals, one-complete-launch rule, and prohibited public claims. State Current and Target separately and tie Current claims to the accepted evidence SHA.

- [ ] **Step 5: Write actors, organization model, and present-to-target orientation**

Define visitor, prospect, Free Tools user, trial user, merchant customer, merchant staff, specialist actors, Dorzak staff, providers, service actors, one paid Organization, its Locations, legal Companies, isolated merchant ERPNext site/database, merchant-local customer relationships, and Frappe-native internal Superadmin. Add `MAT-002` current-versus-target gap/transition matrix and `MAT-004` organization/location/site/company matrix with exact gaps, owners, programs, gates, and public-claim effects.

- [ ] **Step 6: Write the complete glossary**

Define at least: Organization, Location, legal Company, merchant, merchant customer, Party, identity, principal, plan, PlanVersion, entitlement, capability, sensitive capability, site, database, projection, authoritative owner, provider fact, Dorzak-native domain, ERPNext-owned record, Superadmin, Dorzak teammate, grant, Current, Target, Planned, Deferred, Out of scope, Historical, milestone, program, work package, evidence SHA, and public launch. Expand ERP, API, SLO, RPO, RTO, OTP, POS, CRM, RTL, SEO, UOM, COGS, DNS, TLS, and every later acronym at first use.

- [ ] **Step 7: Reconcile actual Part I IDs to coverage**

Update only `coverage.json`: each actual `AUTH-*`, `MAT-001`, `MAT-002`, and `MAT-004` ID maps both directions to ledger rows. No new rule may lack provenance.

- [ ] **Step 8: Run a focused Part I review and commit**

The orchestrator dispatches a read-only product/authority checker. It answers the nine link-free comprehension questions for Part I, checks source hierarchy/status/state/glossary/gap accuracy, and returns findings. The writer corrects all findings.

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const text = readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8");
const result = validateMaster(text, "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.partial.partI !== "PASS" || result.errorsForPresentParts.length) process.exit(1);
console.log("MASTER_PART_I PASS");
'
git diff --check
```

Expected: `MASTER_PART_I PASS`; no diff errors.

Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: assemble master authority and orientation"
```

Expected: exactly two paths; clean worktree.

---

### Task 9: Phase 4B — Build the Complete Commercial Catalogue and Plan Economics

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`
- Modify: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: every approved sub-plan feature, commercial statement, numeric value, country/currency rule, and historical benchmark in the frozen sources.
- Produces: the self-contained commercial half of Part II and a zero-unresolved-value commercial gate before candidate review.

- [ ] **Step 1: Add exhaustive commercial validation tests**

Add tests that require a row for every plan × capability × vertical × country variation and the exact value dimensions below. Tests reject blank, generic `varies`, unlabeled ranges, hidden qualifiers, or nonnumeric prose where an approved numeric value exists. Tests also reject any competitor/market benchmark used as Dorzak price, limit, entitlement, or decision provenance.

Required value dimensions are:

```text
feature/entitlement          included/excluded/conditional state
seat quantity               location quantity
storage quantity            usage/transaction quantity
message/channel quantity    integration/provider quantity
support channel             support hours/response/service level
monthly price               annual price and annualization basis
currency                    tax inclusion/exclusion and country treatment
discount                    trial length/eligibility/conversion
add-on unit/price            overage unit/rate/cap/fail-closed behavior
payment timing/method        onboarding charge/scope
managed-service charge      upgrade/proration/effective time
downgrade/entitlement loss  cancellation/retirement/data treatment
dunning/grace/suspension     refund/credit/chargeback behavior
```

The final test asserts `blocking_open_commercial_decisions=0` for candidate, review, signoff, and handoff modes.

- [ ] **Step 2: Re-extract every sub-plan feature and commercial value**

Read every approved plan, product-baseline catalogue, vertical section, later commercial decision, and bounded compatible lower-level plan. Append or correct ledger rows so each individual feature, quantity, limit, charge, lifecycle rule, and country variation has one normalized statement, exact coordinate, status/state, plan/vertical/country tags, controlling authority, and destination ID.

Historical competitor or market data receives classification `evidence`, authority level 7, solution state Historical, and an explicit `non_authoritative_benchmark` tag in `system_of_record_effect`. It cannot control a Dorzak master ID.

- [ ] **Step 3: Record missing approved numbers as complete blocking Open decisions**

For every absent approved price, quantity, limit, rate, currency, tax rule, discount, trial term, add-on, overage, payment timing, onboarding charge, managed-service charge, support level, or lifecycle consequence, create a full `OPEN-NNN` row containing question, affected plans/features/verticals/countries/surfaces, known options/trade-offs, source statements, Dorzak decision owner and consulted roles, fail-closed no-sale/no-claim interim rule, decision deadline before commercial approval, blocked work, required evidence, and all sections/matrices to update.

Do not choose a number. Return the sorted blocking decision IDs to Control. Resume this task only after a durable later owner decision is added to a refreshed source inventory, ledger, and coverage set. Because price and advertised capability are approval-blocking, Task 20 cannot produce a candidate PASS while any such row remains Open.

- [ ] **Step 4: Write Part II commercial policy**

Append `## Part II — Business, commercial, experience, and vertical solution` and completed commercial sections. Explain Free Tools, Pro, Business, Enterprise, immutable PlanVersion publication, entitlement evaluation, category/plan progression, sensitive activation, trials, checkout, billing, dunning, refunds, add-ons, overages, upgrades, downgrades, retirement, and public-claim rules in plain business language with engineering consequences.

- [ ] **Step 5: Embed `MAT-006` complete plan/capability/limit/value matrix**

`MAT-006` may be split into internally cross-referenced subtables but retains one stable matrix ID. It includes every sub-plan feature and every required value dimension for Free Tools, Pro, Business, and Enterprise, with Qatar/Tunisia differences and every supported vertical. Each cell contains an explicit approved value/rule or a domain-specific `Not applicable — reason`; no active cell may point outside the master for meaning.

Also embed the commercial portions of `MAT-007` category-by-plan-by-primary-journey and `MAT-008` sensitive-capability-by-country-by-activation-evidence.

- [ ] **Step 6: Add a clearly separate non-authoritative benchmark table**

If frozen sources contain competitor/market benchmarks, include source, observed date, metric, reported value, comparison purpose, and expiration/revalidation boundary. Precede the table with a normative statement that it is context only, does not set Dorzak price/entitlement/limit, and cannot fill a missing decision. If no controlled benchmark source exists, state that no controlled benchmark is included and make no market claim.

- [ ] **Step 7: Validate commercial completeness and resolve the blocking gate**

Run:

```bash
node --test --test-name-pattern="commercial|plan|price|limit" scripts/quality/dorzak-master-solution.test.mjs
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.commercial.missingCells || result.commercial.blockingOpenDecisions || result.commercial.benchmarkSubstitutions) process.exit(1);
console.log(`COMMERCIAL_CATALOGUE PASS plans=4 missing_cells=0 blocking_open=0 benchmark_substitutions=0`);
'
```

Expected: all selected tests pass and exact PASS line. If approved sources do not yet supply every required value, expected result is a controlled stop with sorted `OPEN-*` IDs returned to Control, not a commit presented as commercially complete.

- [ ] **Step 8: Reconcile coverage and commit the resolved commercial catalogue**

Update ledger/coverage for any durable resolutions, then rerun inventory, ledger, coverage, and commercial checks.

Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl docs/dorzak-launch/master-solution-evidence/coverage.json scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: define complete master commercial catalogue"
```

Expected: exactly four paths, commercial PASS, clean worktree.

---

### Task 10: Phase 4C — Complete Part II Experiences, Journeys, and Verticals

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: all `BUS-*`, vertical, journey, surface, localization, accessibility, performance, and growth rows.
- Produces: a complete Part II for business and engineering readers.

- [ ] **Step 1: Write acquisition, signup, activation, and publication experiences**

Complete corporate site, solutions/resources/SEO, pricing, signup, payment, provisioning progress, onboarding, first value, Free Tools, Our Clients proof governance, merchant website builder, custom domains, immutable publication, preview, storefront, customer mobile shell, checkout/account/status, and merchant application journeys. Each journey states actor, entry, success, validation error, authorization denial, provider failure, timeout, retry, recovery, cancellation, and public-claim boundary.

- [ ] **Step 2: Write design-system and inclusive-experience rules**

Define tokens/components/content semantics, responsive surfaces, English/French/Arabic, right-to-left layout, keyboard and assistive-technology behavior, motion preferences, accessibility acceptance, page and interaction performance budgets, media safety, and localization ownership. Populate the experience portion of `MAT-013` surface-by-language-by-accessibility-by-performance-budget.

- [ ] **Step 3: Write all actor journeys**

Include visitor, prospect, Free Tools user, trial user, merchant owner/admin/manager/cashier/inventory/purchasing/finance/editor/marketer/support/analyst/project/auditor, merchant customer, patient, guardian, learner, member, donor, volunteer, beneficiary, representative, Dorzak owner/Superadmin/support/incident/commercial/content/fleet/managed-delivery/security/release roles, provider actors, and queue/scheduled workers. Cross-reference canonical role and domain rules; do not duplicate them inconsistently.

- [ ] **Step 4: Write every supported vertical and General Business**

Give each vertical a plain-language value promise, primary journey, actors, commercial progression, sensitive activation, core records, system-of-record boundary, success/failure/recovery, country constraints, and acceptance evidence:

```text
Retail/shop; supplier/wholesale/B2B; restaurant/café/food-and-beverage;
appointments/professional services; salon/coiffeur/beauty/spa; healthcare;
education/school; gym/fitness; nonprofit; General Business.
```

Cover analytics/value proof, CRM/campaign/segmentation/referral/review/attribution, communications/channel governance, Work/Gantt, Dorzak-managed delivery, and shipping/fulfillment.

- [ ] **Step 5: Complete role, category, and sensitive-capability matrices**

Embed `MAT-003` actor/role-by-surface-by-permission and finish `MAT-007`/`MAT-008`. Every row names organization/location scope, grants, duties, plan/category/country limits, sensitive purpose/evidence, audit duties, and prohibited actions. No generic qualifier may hide a role or vertical variation.

- [ ] **Step 6: Reconcile coverage and run focused read-only review**

Map every `BUS-*` and matrix row both directions. Dispatch a read-only business/domain/UX checker to verify every vertical, actor, journey, plan progression, language, failure, and recovery. Correct every finding.

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.partial.partII !== "PASS" || result.catalogue.missingActors || result.catalogue.missingVerticals) process.exit(1);
console.log("MASTER_PART_II PASS plans=4 verticals=10 missing_actors=0 missing_verticals=0");
'
```

Expected: exact PASS line.

- [ ] **Step 7: Commit complete Part II**

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: assemble master business and experience solution"
```

Expected: exactly two paths; clean worktree.

---

### Task 11: Phase 4D — Start Part III with DOM-001 through DOM-010

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: all ledger rows mapped to DOM-001–DOM-010 and the canonical Part I/II rules.
- Produces: the Part III domain contract plus ten fully specified authority, identity, commercial, fleet, and coordination domains.

- [ ] **Step 1: Add the Part III domain contract**

Append `## Part III — Domain, authority, data, workflow, and integration solution`. Explain that business authority and invariants define domains, not repository folders. Define the exact visible ID syntax and cross-reference rules.

Every domain in this and Tasks 12–16 contains, in order, these completed H4 fields: Identity and outcome; Status and scope; Actors and permissions; Current reality; Target behavior; Invariants; Workflows and states; Data and authority; Interfaces and events; Security, privacy, and audit; Experience; Operations; Dependencies and ownership; Current-to-target transition; Verification; Decisions and provenance.

Every field names concrete rules. Workflow fields include entry, happy path, cancellation, conflict, timeout, retry, compensation, recovery, and final states. Data fields include entities/key fields, writer, projections, derived values, IDs, classification, purpose, retention, residency, deletion/export, and lineage. Verification fields name stable requirements, automated/human tests, matrices, evidence, defect threshold, and signoff role.

- [ ] **Step 2: Write DOM-001 through DOM-005**

Complete:

- `DOM-001` Product authority and release policy — promise, scope, one launch, status, decisions, claims, and Control boundary.
- `DOM-002` Organization, site, location, legal entity, and country — paid Organization, Locations, legal Companies, country packs, isolated site/database, mapping, and lifecycle.
- `DOM-003` Identity, Party, contact, consent, and preference — principals, staff/customer identity, verified channels, purpose, consent, merchant-local relationships, and deletion/export.
- `DOM-004` Authentication, sessions, OTP, and customer auto-account — linking, recovery, session/cookie/host safety, abuse, and fail-closed behavior.
- `DOM-005` Roles, authorization, execution context, and sensitive activation — actor + organization + location + plan + policy + evidence resolution, grants, denials, and audit.

Current facts cite the evidence SHA. Target rules identify P01/P18 ownership and acceptance. Do not imply P01 is authorized or implemented.

- [ ] **Step 3: Write DOM-006 through DOM-010**

Complete:

- `DOM-006` Commercial catalogue, plans, entitlements, limits, and add-ons — canonicalize Task 9 commercial rules rather than restating conflicting values.
- `DOM-007` Trial, subscription, Dorzak billing, dunning, and refunds — state machines, immutable PlanVersion reference, provider facts, money precision, account effects, and recovery.
- `DOM-008` ERPNext fleet and merchant-site lifecycle — provision, migrate, health, backup, restore, upgrade, failure, quarantine, retire, and capacity.
- `DOM-009` dorzak_core, tenant routing, commands, projections, and reconciliation — mapping/version/drift/freshness/error contracts.
- `DOM-010` Idempotency, outbox, webhook, event, and saga coordination — exactly-once intent, duplicate handling, receipt, retry, dead letter, compensation, and reconciliation.

- [ ] **Step 4: Begin domain/authority matrices**

Add the relevant rows of `MAT-005` field-family system-of-record/allowed-write-direction, `MAT-009` domain/module/program/milestone, `MAT-010` integration/owner/credential/data/failure, and `MAT-011` event/webhook/idempotency/reconciliation. Each row has its own stable row ID and exact owner/direction/failure rule.

- [ ] **Step 5: Validate, review, and commit the domain group**

Dispatch a read-only architecture/domain checker for the ten domains. It verifies all 16 fields, legal status/state, authority, workflow exceptions, data ownership, security, operations, and traceability.

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.domainRange("DOM-001", "DOM-010").errors.length) process.exit(1);
console.log("DOMAIN_GROUP PASS first=DOM-001 last=DOM-010 domains=10 fields_each=16");
'
```

Expected: exact PASS line.

Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: specify master authority and platform domains"
```

Expected: exactly two paths; clean worktree.

---

### Task 12: Phase 4E — Specify DOM-011 through DOM-018 Commerce

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: DOM-011–DOM-018 ledger mappings and canonical commercial/data/money rules.
- Produces: eight complete commerce, inventory, payment, offline, and cutover domains.

- [ ] **Step 1: Write every domain with all 16 ordered fields**

Complete:

- `DOM-011` Product and service catalogue — item/service, variant, UOM, operational price/tax, presentation projection, ownership, and publication.
- `DOM-012` Supplier, procurement, receiving, and landed cost — supplier identity, purchase/approval/receipt/return/credit, landed cost, balances, and reconciliation.
- `DOM-013` Inventory, warehouse, transfer, count, and valuation — canonical stock, batches/serials, units, reservations, counts, COGS, conflict, and repair.
- `DOM-014` Cart, quote, order, POS, invoice, receipt, return, and refund — draft versus canonical submission, fiscal effects, correction, cancellation, and finality.
- `DOM-015` Payment, settlement, wallet, gift card, and loyalty value — provider/ERP facts, authorization/capture/refund/chargeback, stored value, redemption, fees, precision, and audit.
- `DOM-016` Commercial customer and supplier account — Party relationship, legal/tax/accounting projections, credit terms, balances, privacy, and isolation.
- `DOM-017` Offline POS — device identity, signed journal, provisional receipt, replay/idempotency, posting conflict, canonical receipt, and recovery.
- `DOM-018` Commerce migration, cutover, and legacy history — expand, backfill, parity, route switch, no dual write, rollback deadline, forward recovery, history access, and reconciliation.

Each workflow includes failure, timeout, duplicate, partial provider/ERP consequence, compensation, and operator recovery. Money and inventory invariants fail closed.

- [ ] **Step 2: Extend matrices and transitions**

Add all commerce rows to `MAT-005`, `MAT-009`, `MAT-010`, `MAT-011`, and `MAT-012` data classification/purpose/retention/residency/export. Ensure one writer for each field/fact and no long-lived dual write.

- [ ] **Step 3: Validate, review, and commit**

Run:

```bash
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-011 DOM-018
```

Expected: `DOMAIN_GROUP PASS first=DOM-011 last=DOM-018 domains=8 fields_each=16`.

Dispatch a read-only commerce/data reviewer; correct every ownership, money, stock, offline, migration, and recovery finding. Commit exactly master and coverage as:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: specify master commerce domains"
```

Expected: exactly two paths; clean worktree.

---

### Task 13: Phase 4F — Specify DOM-019 through DOM-029 Acquisition and Growth

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: DOM-019–DOM-029 mappings and the completed Part II surface/journey policy.
- Produces: eleven complete acquisition, publication, experience, analytics, CRM, and communications domains.

- [ ] **Step 1: Write DOM-019 through DOM-024 with all 16 fields**

Complete corporate website/acquisition (`DOM-019`), Free Tools (`DOM-020`), pricing/signup/onboarding/activation (`DOM-021`), Our Clients proof (`DOM-022`), website/storefront builder (`DOM-023`), and publication/domains/SEO/translations/media (`DOM-024`). Cover consent/evidence/expiry for claims; manifest-to-checkout accuracy; provisioning progress; immutable release; preview; DNS/TLS; redirect/rollback; and safe assets.

- [ ] **Step 2: Write DOM-025 through DOM-029 with all 16 fields**

Complete storefront/customer mobile shell/checkout (`DOM-025`), design system/content/localization/accessibility/motion (`DOM-026`), analytics/value ledger/reporting/instrumentation (`DOM-027`), CRM/campaign/segmentation/referral/review/attribution (`DOM-028`), and communications/channel governance (`DOM-029`). Cover metric definitions/provenance/freshness/privacy, eligibility/consent/attribution, email/SMS/WhatsApp template and delivery/cost governance, opt-out, provider failure, and abuse.

- [ ] **Step 3: Extend all relevant matrices**

Add rows to `MAT-005`, `MAT-009`, `MAT-010`, `MAT-011`, `MAT-012`, and finish these domains’ `MAT-013` language/accessibility/performance budgets. Public claims must map to approved evidence and expiry.

- [ ] **Step 4: Validate, review, and commit**

Run:

```bash
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-019 DOM-029
```

Expected: `DOMAIN_GROUP PASS first=DOM-019 last=DOM-029 domains=11 fields_each=16`.

Dispatch a read-only growth/UX/privacy checker and apply all corrections. Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: specify master acquisition and growth domains"
```

Expected: exactly two paths; clean worktree.

---

### Task 14: Phase 4G — Specify DOM-030 through DOM-040 All Verticals

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: every vertical ledger row, commercial plan rule, role, sensitive-capability, country, and workflow mapping.
- Produces: eleven fully operational vertical domains, including the shared scheduling kernel and General Business.

- [ ] **Step 1: Write shared commerce and food verticals**

Complete `DOM-030` retail/shop, `DOM-031` supplier/wholesale/B2B, and `DOM-032` restaurant/café/food-and-beverage. Include their primary journeys, plan progression, roles, operational records, POS/order/kitchen/table/dispatch/warehouse/revenue-center boundaries, failure/compensation, localization, operations, and evidence.

- [ ] **Step 2: Write scheduling and service verticals**

Complete `DOM-033` shared scheduling/resource kernel, `DOM-034` appointments/professional services, and `DOM-035` salon/coiffeur/beauty/spa. Define availability, holds, recurrence, resources, conflicts, waitlist, cancellation/no-show, package/membership, staff/resource assignment, payment consequence, and race-safe recovery.

- [ ] **Step 3: Write regulated and sensitive verticals**

Complete `DOM-036` healthcare, `DOM-037` education/school, `DOM-038` gym/fitness, and `DOM-039` nonprofit. Define purpose limitation, credential/evidence activation, clinical/nonclinical boundary, patient/guardian/learner/minor/member/donor/volunteer/beneficiary safety, country gates, sensitive access, retention, audit, prohibited claims, and qualified-review requirements. Do not infer medical, legal, educational, or charity regulatory conclusions absent an approved source; unresolved launch-affecting questions block approval.

- [ ] **Step 4: Write `DOM-040` General Business**

Cover inquiry, quote, engagement, work, invoice, payment, customer relationship, project/timesheet handoff, broad operations, plan progression, and explicit exclusions that prevent General Business from silently absorbing specialist sensitive behavior.

- [ ] **Step 5: Prove plan/vertical commercial completeness**

Cross-check every vertical against `MAT-006`, `MAT-007`, and `MAT-008`: all approved features, quantities, limits, prices/charges, support levels, country variations, and sensitive activation rules must be explicit. A missing approved numeric value creates a complete approval-blocking Open decision with owner, options/trade-offs, fail-closed no-sale/no-claim rule, decision gate, blocked work, required evidence, and affected sections; return it to Control and do not commit a commercially complete vertical candidate. Never infer a value from another vertical or competitor.

- [ ] **Step 6: Validate, specialist-review, and commit**

Run:

```bash
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-030 DOM-040
```

Expected: `DOMAIN_GROUP PASS first=DOM-030 last=DOM-040 domains=11 fields_each=16` plus `COMMERCIAL_VERTICAL_COVERAGE PASS verticals=10 gaps=0`.

Dispatch read-only domain specialists, including named qualified healthcare, education/minor, and nonprofit/sensitive reviewers when required by the source. Apply all corrections. Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: specify master vertical domains"
```

Expected: exactly two paths; clean worktree.

---

### Task 15: Phase 4H — Specify DOM-041 through DOM-045 Work, Fulfillment, and Superadmin

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: P15/P16/P17 and later Superadmin decision rows.
- Produces: five complete managed-work, delivery, control-plane, and intervention domains.

- [ ] **Step 1: Write Work and Dorzak-managed delivery**

Complete `DOM-041` Work/Gantt/projects/tasks/timesheets/portfolio and `DOM-042` Dorzak-managed delivery. Distinguish ERP core, Dorzak extensions, plan progression, collaboration, request/scope/estimate/approval/workspace/change/acceptance/support, customer versus Dorzak responsibilities, billing, evidence, and dispute/recovery.

- [ ] **Step 2: Write shipping and fulfillment**

Complete `DOM-043` shipping/parcel/delivery/fulfillment: quote/rate, label, pickup, dispatch, tracking, proof, exception, cancellation, return, reconciliation, provider-neutral contracts, carrier facts, customer communication, money consequence, and operational recovery.

- [ ] **Step 3: Write the internal Frappe-native Superadmin domains**

Complete `DOM-044` internal Superadmin control plane and `DOM-045` delegated intervention/support/incident control. Preserve the separate internal site, strict merchant-site isolation, organization/commercial/fleet/content/release/health visibility, no ambient merchant access, reason/purpose, scoped grant, approval, time bound, redaction, audit, revocation, emergency break-glass governance, and post-action review. State explicitly that P17 is Target/Planned and is not authorized or Current.

- [ ] **Step 4: Extend matrices and validate**

Add domain/module/program, integration, data, role/permission, audit, and risk rows. Run:

```bash
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-041 DOM-045
```

Expected: `DOMAIN_GROUP PASS first=DOM-041 last=DOM-045 domains=5 fields_each=16`.

Dispatch a read-only operations/Superadmin isolation checker and correct all findings. Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: specify master operations and control domains"
```

Expected: exactly two paths; clean worktree.

---

### Task 16: Phase 4I — Complete Part III with DOM-046 through DOM-053

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: all remaining cross-cutting ledger and coverage rows.
- Produces: complete data, security, country, reliability, operations, adapter, supply-chain, quality, and release domains; all 53 domains now exist.

- [ ] **Step 1: Write DOM-046 through DOM-049**

Complete data architecture/governance (`DOM-046`), security/privacy/trust (`DOM-047`), country/language/regulatory/legal packs (`DOM-048`), and reliability/performance/capacity/backup/recovery (`DOM-049`). Define owners, classification/purpose/lineage/quality/retention/residency/export/deletion; threats/auth/authz/isolation/encryption/secrets/abuse; Qatar/Tunisia and EN/FR/AR/RTL plus sector activation; SLO/RPO/RTO/capacity/degradation/queue/restore ownership and evidence.

- [ ] **Step 2: Write DOM-050 through DOM-053**

Complete audit/observability/operations/support/runbooks (`DOM-050`), external adapters/integration governance (`DOM-051`), source reuse/licenses/supply chain/upgrades (`DOM-052`), and quality/migration rehearsal/evidence/release (`DOM-053`). Cover provider-neutral boundaries, credentials without values, timeouts/retries/circuit/dead-letter/reconciliation, licenses/notices/version isolation/security/upgrade/rollback, test matrices/defect thresholds/rehearsal/signoff/one release/rollback.

- [ ] **Step 3: Finish Part III matrices**

Complete every row in `MAT-005`, `MAT-009`, `MAT-010`, `MAT-011`, and `MAT-012`. Prove every fact/field has one owner, every write direction is explicit, every provider fact is separated from Dorzak/ERP authority, every event has idempotency/reconciliation, and every data class has purpose/retention/residency/export/deletion.

- [ ] **Step 4: Validate all 53 uniform domain specifications**

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.domains.count !== 53 || result.domains.errors.length) process.exit(1);
console.log("MASTER_PART_III PASS domains=53 fields=848 missing=0 duplicates=0");
'
```

Expected: exact PASS line (`53 × 16 = 848` fields).

- [ ] **Step 5: Run cross-domain read-only contradiction review and commit**

Dispatch architecture, data, security, operations, and authority checkers in parallel against the same commit. They check one commercial hierarchy, organization/site/location model, field owner, surface target, release policy, evidence stamp, target rule, no long-lived dual write, and no prose/matrix/domain conflict. The writer serially applies corrections and reruns:

```bash
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-001 DOM-053
```

Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: complete master domain solution"
```

Expected: clean worktree and all checker Critical/Important counts zero for this focused phase.

---

### Task 17: Phase 4J — Assemble Part IV Technical, Security, Reliability, and Operations

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: all `TECH-*` mappings plus the canonical domain authority, data, interface, security, and operations rules.
- Produces: a self-contained technical and operating solution that never contradicts Part III.

- [ ] **Step 1: Write deployment units and repository/interface boundaries**

Append `## Part IV — Technical, security, reliability, and operating solution`. Define Laravel modular monolith, merchant React application, separate public/customer surface, isolated ERPNext site/database per paid Organization, separate Frappe-native Superadmin site, `dorzak_core`, queues/workers, storage/scanning, and provider-neutral external adapters. Name owner, trust boundary, deployment boundary, allowed data, interfaces, failure behavior, and prohibited coupling for each. Explain repository evolution without treating folders as business domains.

- [ ] **Step 2: Specify all 13 mandatory deep modules**

For each module, state owner, stable interface, upstream/downstream dependencies, authoritative and projected data, success/failure/timeout/retry behavior, security boundary, observability, versioning, and acceptance evidence:

```text
Tenant and Site Router
Identity, Party, Role, Consent, and Preference
Capability and Commercial Policy
Provider-neutral Payment Kernel
Scheduling and Resource Kernel
Workflow, Approval, and Version Kernel
ERP Command, Mapping, Projection, and Reconciliation Gateway
Media and Document Safety
Communications and Channel Governance
Audit, Observability, and Evidence
Website Publication and Domain Routing
Data Migration and Cutover
Superadmin Grant and Intervention
```

- [ ] **Step 3: Write tenancy, security, privacy, and financial controls**

Specify authenticated execution-context resolution; fail-closed organization/location/site routing; host/cookie/cache/credential/signing-key/network separation; authentication/recovery/session; authorization/grants/sensitive purpose; encryption/secrets without values; threat/abuse controls; audit; privacy/classification/retention/residency/export/deletion; money precision/idempotency/concurrency; inventory; scheduling; provisioning; publication; webhook; outbox; and distributed consistency/reconciliation.

- [ ] **Step 4: Write reliability and operating model**

Specify SLOs, RPO/RTO, capacity, performance budgets, accessibility operations, queue age/dead letter, observability signals, alert thresholds, ownership, incident classification/response, support escalation, backup/restore tests, upgrade/rollback, fleet operations, degradation, runbook ownership, and release topology. A missing numeric target follows the same governed Open policy; release-affecting reliability unknowns block approval.

- [ ] **Step 5: Write supply-chain and environment rules**

Cover runtime/version pins, dependency integrity, source reuse audit, licenses/notices, vulnerability response, artifact provenance, build/release separation, upgrade/rollback, and environment topology. Do not include a credential, endpoint secret, personal host path, or unsafe command.

- [ ] **Step 6: Complete technical matrices and validate**

Finish the Part IV views of `MAT-009`, `MAT-010`, `MAT-012`, `MAT-013`, and `MAT-016` risk/control/owner/detection/recovery. Map every `TECH-*` rule both directions.

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.partial.partIV !== "PASS" || result.modules.count !== 13 || result.modules.errors.length) process.exit(1);
console.log("MASTER_PART_IV PASS modules=13 technical_gaps=0");
'
```

Expected: exact PASS line.

- [ ] **Step 7: Run specialist review and commit**

Dispatch read-only architecture/integration, security/privacy, and reliability/operations checkers against the same exact bytes. Correct every finding and rerun Part III plus Part IV checks.

Commit exactly master/coverage:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: assemble master technical and operating solution"
```

Expected: exactly two paths; clean worktree.

---

### Task 18: Phase 4K — Assemble Part V Delivery, Evidence, Governance, and Evolution

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`

**Interfaces:**
- Consumes: all `DEL-*`, PRD, program, work-package, milestone, migration, test, evidence, risk, decision, revision, and signoff mappings.
- Produces: a complete Part V and self-contained definition of complete launch.

- [ ] **Step 1: Write every program and work-package contribution**

Append `## Part V — Delivery, evidence, governance, and evolution`. For each P00–P19, write outcome, accepted present state, target contribution, planning dependency, execution dependency, milestone, serialized owner, permitted same-milestone parallelism, inputs/outputs, acceptance evidence, and explicit statement that the master describes sequencing rather than current authorization.

Include exact entries for `WP-M2`, `WP-P04T`, `WP-P09A`, `WP-P09B`, and the M7 General Business slice. `P09` explains its two work packages. P00 records Tasks 1–15 as accepted only to the Current-evidence boundary and retains the owner pause before Task 16.

- [ ] **Step 2: Write M0–M9 and current-to-target sequence**

Define mandatory `M0 → M1 → M2 → M3 → M4 → M5 → M6 → M7 → M8 → M9`, entry/exit evidence, allowed parallel streams, serial integration, expand/backfill/parity/cutover/forward-recovery stages, no dual write, migration rehearsal, rollback boundaries, operational rehearsal, one-complete-launch gate, and release switch condition.

- [ ] **Step 3: Write verification, defect, evidence, and release governance**

Define automated/unit/integration/contract/database/browser/security/accessibility/performance/migration/restore/release tests; human scenario and regulated review; matrix coverage; exact-SHA evidence; defect severity; zero Critical/Important acceptance; retry/flake/skip rules; rehearsal; rollback; release approval; and public-claim evidence. Explain code SHA, evidence commit, and final integration SHA separately.

- [ ] **Step 4: Write decision, risk, source, revision, and change control**

Include complete Open, Deferred, and Superseded registers; risks/assumptions/controls; deterministic source register summary and inventory/ledger/coverage hashes; change reason/scope; affected-domain analysis; one writer; full coverage/global invariant rerun; independent re-review; same-byte signoff; Control activation; replacement history; and emergency execution-pause separation.

- [ ] **Step 5: Write the self-contained definition of done**

The complete-launch definition of done names all product, plan, vertical, surface, data, authority, integration, security, privacy, accessibility, reliability, operational, migration, support, evidence, signoff, and release outcomes. It requires every sub-plan feature and resolved numeric limit/charge, all 53 domains, 36 PRD areas, 18 matrices, 12 flows, ten review lenses, required signoffs, zero approval-blocking Open decisions, and one Control-recorded activation. It reiterates that activation grants no execution authority.

- [ ] **Step 6: Populate delivery traceability matrices**

Complete `MAT-014` P00–P19/work-package dependency-entry-exit, `MAT-015` requirement-domain-program-test-evidence, `MAT-016` risk/control/owner/detection/recovery, `MAT-017` Open/Deferred/Superseded registers, and `MAT-018` release gate/human signoff.

- [ ] **Step 7: Embed nominated signoff identities without claiming approval**

Use only the exact `signoff_nominations` from the Control entry. Name Dorzak owner/product authority; business/commercial lead; product/domain lead; engineering/architecture lead; security/privacy lead; reliability/operations lead; quality/release lead; and each required regulated-vertical qualified reviewer. State that actual approval is valid only in `signoff.json` when bound to these exact master bytes and recorded by Control. A missing or changed nominee stops before candidate review.

- [ ] **Step 8: Validate and commit Part V**

Run:

```bash
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.partial.partV !== "PASS" || result.programs.count !== 20 || result.workPackages.count !== 4 || result.milestones.count !== 10) process.exit(1);
console.log("MASTER_PART_V PASS programs=20 work_packages=4 milestones=10");
'
```

Expected: exact PASS line.

Dispatch a read-only delivery/quality/authority checker and correct every finding. Commit:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json
git commit -m "docs: assemble master delivery and governance solution"
```

Expected: exactly two paths; clean worktree.

---

### Task 19: Phase 4L — Complete All Embedded Matrices, Flows, Registers, and Link-Free Meaning

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json`
- Modify: `scripts/quality/dorzak-master-solution.test.mjs`

**Interfaces:**
- Consumes: completed Parts I–V and all planned matrix/flow/decision mappings.
- Produces: the first structurally complete source-frozen candidate bytes.

- [ ] **Step 1: Audit all 18 matrices for complete cells and canonical rules**

Verify and finish exactly:

```text
MAT-001 authority by question
MAT-002 current versus target gap and transition
MAT-003 actor/role by surface by permission
MAT-004 Organization/Location/site/Company mapping
MAT-005 field-family system of record and write direction
MAT-006 plan/capability/quantity/limit/value/upgrade
MAT-007 category by plan by primary journey
MAT-008 sensitive capability by country by activation evidence
MAT-009 domain by module by program by milestone
MAT-010 integration by owner by credential class by data by failure
MAT-011 event/webhook by idempotency by reconciliation
MAT-012 data classification by purpose by retention by residency by export
MAT-013 surface by language by accessibility by performance budget
MAT-014 P00–P19/work-package dependency, planning entry, execution entry, and exit
MAT-015 requirement by domain by program by test by evidence
MAT-016 risk by control by owner by detection by recovery
MAT-017 Open, Deferred, and Superseded decision registers
MAT-018 release gate and human signoff
```

Every material variation gets its own row/cell; no generic qualifier hides plan, vertical, country, role, integration, or state differences. `MAT-006` must contain every sub-plan feature and resolved quantity, limit, price, currency, tax, discount, trial, add-on, overage, payment, onboarding, managed-service, support, and lifecycle rule, with zero missing cells, zero invented values, and zero approval-blocking commercial Open decisions. An inapplicable cell contains a domain-specific reason.

- [ ] **Step 2: Embed all 12 flows with complete adjacent prose**

Add renderable text diagrams or Mermaid-compatible fenced diagrams with these exact IDs:

```text
FLOW-001 five-part solution and deployment-unit boundaries
FLOW-002 authenticated execution context and fail-closed tenant routing
FLOW-003 visitor → plan → payment → provisioned paid Organization
FLOW-004 merchant/customer command → Dorzak → dorzak_core → ERPNext → projection
FLOW-005 Dorzak-native workflow with ERP financial or stock consequence
FLOW-006 offline POS provisional → canonical posting → conflict recovery
FLOW-007 ERP fleet provision/fail/recover/backup/upgrade/retire
FLOW-008 website draft → approval → immutable publication → rollback
FLOW-009 existing commerce prepare → cutover → forward recovery
FLOW-010 Superadmin visibility → time-limited intervention → audit → revocation
FLOW-011 event/webhook ingest → idempotency → retry → dead letter → reconciliation
FLOW-012 M0 → M9 → one complete launch
```

Adjacent prose defines every node, edge, state, owner, timeout, failure, compensation, reconciliation, and recovery. A diagram is never the only statement of a rule.

- [ ] **Step 3: Reconcile glossary, acronym, decision, and source summaries**

Ensure every term and acronym used anywhere is defined once canonically. Ensure every Open record is complete, every Deferred item has reason/earliest gate/prohibition, every Superseded item names replacement, every later decision survives with source/scope/effective date/affected IDs/conflict disposition, and all source references are audit-only rather than required meaning.

- [ ] **Step 4: Run the link-free comprehension exercise**

Dispatch a read-only worker with a rendered plaintext copy in which Markdown link destinations are removed. It must answer, solely from visible master text: product/commercial promise; Current/Target systems; permissions; fact owners; all major journey success/failure/recovery; data isolation/protection; operations; current-to-target delivery; and Open/Deferred/Superseded decisions. Any answer requiring another document is an Important finding.

- [ ] **Step 5: Reconcile actual master IDs to bidirectional coverage**

Update `coverage.json` so the set of every visible normative ID in the master equals the `master_to_source` subject set exactly. Validate 36 PRD areas, 53 domains, every catalogue set, 20 programs, four work packages, ten milestones, seven ADRs, 18 matrices, and 12 flows. Source-to-master and master-to-source gaps both equal zero.

- [ ] **Step 6: Run incomplete-content and five-part tests**

Run:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node --input-type=module -e '
import { readFileSync } from "node:fs";
import { validateMaster } from "./scripts/quality/dorzak-master-solution.mjs";
const result = validateMaster(readFileSync("docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md", "utf8"), "4cdaca78f2c195c653d6d72c7815f8ae82e3f4bf");
if (result.parts !== 5 || result.matrices !== 18 || result.flows !== 12 || result.unfinished || result.emptyCells || result.blockingOpenDecisions) process.exit(1);
console.log("MASTER_ASSEMBLY PASS parts=5 matrices=18 flows=12 unfinished=0 empty_cells=0 blocking_open=0");
'
```

Expected: all tests pass and exact PASS line.

- [ ] **Step 7: Commit the complete assembly**

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/coverage.json scripts/quality/dorzak-master-solution.test.mjs
git commit -m "docs: assemble Dorzak master solution candidate"
```

Expected: exactly three paths; clean worktree. This commit is structurally complete but does not become authority.

---

### Task 20: Phase 5 — Run Deterministic Validation and Seal the Candidate

**Files:**
- Modify: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md` only for validation corrections
- Modify: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl` only for discovered traceability corrections
- Modify: `docs/dorzak-launch/master-solution-evidence/coverage.json` only for discovered traceability corrections
- Modify: `scripts/quality/dorzak-master-solution.mjs` and `scripts/quality/dorzak-master-solution.test.mjs` only for a demonstrated validator defect
- Create: `docs/dorzak-launch/master-solution-evidence/validation.json`

**Interfaces:**
- Consumes: complete assembly, frozen inventory/ledger, and actual coverage.
- Produces: one clean immutable candidate commit plus a separate validation-evidence commit bound to it.

- [ ] **Step 1: Run the full deterministic validation suite**

Run under Node `24.18.0`:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node --check scripts/quality/dorzak-master-solution.mjs
node scripts/quality/dorzak-master-solution.mjs inventory
MASTER_CANDIDATE_COMMIT="$(git rev-parse HEAD)" \
MASTER_VALIDATION_OUTPUT=docs/dorzak-launch/master-solution-evidence/validation.json \
node scripts/quality/dorzak-master-solution.mjs candidate
```

The candidate mode verifies exact path/mode, exactly five parts, unique stable IDs, 53 domains/848 ordered fields, 36 PRD areas, 20 programs, four work packages, ten milestones, seven ADRs, 18 matrices, 12 flows, complete extraction/coverage, status/state legality, terminology/acronyms, links and link-free meaning, commercial completeness, duplicate/contradictory rule indicators, system-of-record/tenant/money/sensitive-data/release invariants, Current evidence binding, complete nonblocking Open records, zero approval-blocking Open records, secret/personal-data safety, and writer allowlist.

Expected: all tests pass and `MASTER_SOLUTION_VALIDATION PASS domains=53 prd=36 programs=20 work_packages=4 matrices=18 flows=12 uncovered_source=0 uncovered_master=0 critical=0 important=0`. `validation.json` is canonical and contains all check IDs/counts/input hashes with zero Critical/Important.

- [ ] **Step 2: Correct every failed check serially**

If validation fails, the sole writer uses `apply_patch` only on the exact permitted cause. A source/decision gap returns to the relevant earlier phase and refreshes all downstream hashes. A validator defect requires a failing test before code correction. Never suppress a check, relax a count, mark a material row inapplicable, or invent content to obtain PASS.

After any correction, delete no evidence; replace the uncommitted `validation.json`, rerun the complete suite, and commit the corrected candidate content before regenerating validation evidence.

- [ ] **Step 3: Seal the exact candidate commit**

If Task 20 changed candidate inputs, commit only those exact paths:

```bash
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl docs/dorzak-launch/master-solution-evidence/coverage.json scripts/quality/dorzak-master-solution.mjs scripts/quality/dorzak-master-solution.test.mjs
git diff --cached --quiet || git commit -m "docs: validate Dorzak master solution candidate"
```

Do not stage `validation.json` in this commit. Set:

```bash
CANDIDATE_COMMIT="$(git rev-parse HEAD)"
CANDIDATE_PARENT="$(git rev-parse HEAD^)"
MASTER_BLOB_SHA1="$(git rev-parse "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md")"
MASTER_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md" | shasum -a 256 | awk '{print $1}')"
SOURCE_INVENTORY_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/master-solution-evidence/source-inventory.json" | shasum -a 256 | awk '{print $1}')"
EXTRACTION_LEDGER_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl" | shasum -a 256 | awk '{print $1}')"
COVERAGE_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/master-solution-evidence/coverage.json" | shasum -a 256 | awk '{print $1}')"
```

Expected: clean candidate inputs at one exact commit; candidate, parent, and blob variables are 40-hex; every SHA-256 is 64-hex. Preserve all identities in orchestrator memory.

- [ ] **Step 4: Regenerate validation against the sealed commit**

Run:

```bash
MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT" \
MASTER_VALIDATION_OUTPUT=docs/dorzak-launch/master-solution-evidence/validation.json \
node scripts/quality/dorzak-master-solution.mjs candidate
test "$(git status --short)" = "?? docs/dorzak-launch/master-solution-evidence/validation.json"
```

Expected: global PASS line; only validation evidence differs.

- [ ] **Step 5: Commit validation evidence separately**

```bash
git add docs/dorzak-launch/master-solution-evidence/validation.json
git commit -m "docs: record master solution validation"
REVIEW_PACKET_COMMIT="$(git rev-parse HEAD)"
test "$(git rev-parse "$REVIEW_PACKET_COMMIT^")" = "$CANDIDATE_COMMIT"
test "$(git diff-tree --no-commit-id --name-status -r "$REVIEW_PACKET_COMMIT")" = $'A\tdocs/dorzak-launch/master-solution-evidence/validation.json'

for artifact_path in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence/source-inventory.json \
  docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl \
  docs/dorzak-launch/master-solution-evidence/coverage.json
do
  test "$(git rev-parse "$REVIEW_PACKET_COMMIT:$artifact_path")" = "$(git rev-parse "$CANDIDATE_COMMIT:$artifact_path")"
  test "$(git show "$REVIEW_PACKET_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')" = "$(git show "$CANDIDATE_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')"
done
git cat-file -e "$REVIEW_PACKET_COMMIT:docs/dorzak-launch/master-solution-evidence/validation.json"
```

Expected: one `100644` evidence path; clean worktree. `REVIEW_PACKET_COMMIT` is the immutable validation-evidence commit whose parent is the candidate; its sole changed path is `validation.json`; the master, source inventory, extraction ledger, and coverage bytes at that commit are object- and SHA-identical to the candidate. Record candidate and review-packet commits separately in orchestrator memory.

- [ ] **Step 6: Prove writer and protected boundaries**

Run:

```bash
CONTROL_REF=codex/p00-entry-setup
ENTRY_PATH=docs/superpowers/control/execution/dorzak-master-solution-authority-entry.json
test "$(git rev-parse "$CONTROL_REF")" = "$CONTROL_HEAD"
test "$(git log -1 "$CONTROL_REF" --format=%H -- "$ENTRY_PATH")" = "$CONTROL_HEAD"
PRODUCTION_ENTRY_BASE="$(git show "$CONTROL_HEAD:$ENTRY_PATH" | node -e 'let s="";process.stdin.on("data",d=>s+=d).on("end",()=>process.stdout.write(JSON.parse(s).production_base_commit))')"
test "$(printf '%s' "$PRODUCTION_ENTRY_BASE" | wc -c | tr -d ' ')" = "40"
git diff --name-status "$PRODUCTION_ENTRY_BASE"..HEAD | LC_ALL=C sort
PROTECTED='/Users/barsha/Documents/recover Kyte'
test "$(git -C "$PROTECTED" branch --show-current)" = "feat/premium-features"
test "$(git -C "$PROTECTED" rev-parse HEAD)" = "cf2f65b2e308bdf4750c3e02dc1aafa7a7a39a4d"
test "$(git -C "$PROTECTED" status --short | LC_ALL=C sort | wc -l | tr -d ' ')" = "16"
test "$(git -C "$PROTECTED" status --short | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = "a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa"
```

Compare the printed diff to the exact 14-path allowlist and verify all file modes with `git ls-tree -r HEAD`.

Expected: frozen Control tip and entry-last-change checks pass; allowlist PASS, protected PASS, no external/provider action. Any Control movement or entry rewrite/revocation stops rather than adopting a newer entry.

---

### Task 21: Phase 6A — Run Four Independent Read-Only Reviews on One Candidate

**Files:**
- Create by the sole writer after reports return: `docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json`
- Create by the sole writer after reports return: `docs/dorzak-launch/master-solution-evidence/reviews/02-architecture-data.json`
- Create by the sole writer after reports return: `docs/dorzak-launch/master-solution-evidence/reviews/03-security-operations.json`
- Create by the sole writer after reports return: `docs/dorzak-launch/master-solution-evidence/reviews/04-delivery-authority.json`
- Reviewers: no file writes

**Interfaces:**
- Consumes: exact candidate commit/parent/master blob/master SHA-256, source-inventory SHA-256, ledger/coverage hashes, immutable review-packet commit and validation bytes, and clean repository state.
- Produces: four independent structured reports covering ten separate lenses on the same review-packet bytes.

- [ ] **Step 1: Freeze the review packet**

The orchestrator sends every reviewer the same complete `candidateIdentity` and the already frozen `REVIEW_PACKET_COMMIT`. Before analysis, every reviewer independently runs the equivalent of:

```bash
test "$(git rev-parse "$REVIEW_PACKET_COMMIT^")" = "$CANDIDATE_COMMIT"
PACKET_DIFF="$(git diff-tree --no-commit-id --name-status -r "$REVIEW_PACKET_COMMIT")"
test "$PACKET_DIFF" = $'A\tdocs/dorzak-launch/master-solution-evidence/validation.json' || \
  test "$PACKET_DIFF" = $'M\tdocs/dorzak-launch/master-solution-evidence/validation.json'

for REVIEW_PATH in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence/source-inventory.json \
  docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl \
  docs/dorzak-launch/master-solution-evidence/coverage.json \
  docs/dorzak-launch/master-solution-evidence/validation.json
do
  git show "$REVIEW_PACKET_COMMIT:$REVIEW_PATH" >/dev/null
done

for REVIEW_PATH in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence/source-inventory.json \
  docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl \
  docs/dorzak-launch/master-solution-evidence/coverage.json
do
  test "$(git rev-parse "$REVIEW_PACKET_COMMIT:$REVIEW_PATH")" = "$(git rev-parse "$CANDIDATE_COMMIT:$REVIEW_PATH")"
  test "$(git show "$REVIEW_PACKET_COMMIT:$REVIEW_PATH" | shasum -a 256 | awk '{print $1}')" = "$(git show "$CANDIDATE_COMMIT:$REVIEW_PATH" | shasum -a 256 | awk '{print $1}')"
done
```

Each reviewer verifies the supplied candidate parent, master blob/SHA-256, source-inventory SHA-256, ledger/coverage hashes, and the validation report's complete input identity from those `git show` bytes. All five review paths are read only at `REVIEW_PACKET_COMMIT`; working-tree bytes and later commits are forbidden. If the packet parent/path shape, candidate identity, validation binding, object identity, or hash differs, all reviews stop and no report is accepted.

- [ ] **Step 2: Dispatch reviewer 01 — product and business**

This independent reviewer returns separate verdicts for:

1. Product and commercial — promise, every sub-plan feature, plan/category/vertical values, numeric limits/charges, lifecycle, journeys, claims, and scope.
2. Domain and business operations — all verticals, actors, workflows, exceptions, ownership, and recovery.
3. UX/content/localization/accessibility — surfaces, EN/FR/AR, RTL, semantics, responsive behavior, accessibility, motion, and performance.
4. Link-free comprehension — all nine design questions answerable without links.

It explicitly verifies zero invented values, zero missing approved commercial value, zero approval-blocking commercial Open decision, and benchmark non-authority.

- [ ] **Step 3: Dispatch reviewer 02 — architecture and data**

Return separate verdicts for:

1. Architecture and integration — modules, deployment units, authority, APIs/events/webhooks, providers, ERP/dorzak_core, cutover, and surfaces.
2. Data and migration — entities/fields, writer, projection, lineage, classification, retention/residency/export/deletion, reconciliation, migration, rollback, and forward recovery.

- [ ] **Step 4: Dispatch reviewer 03 — security and operations**

Return separate verdicts for:

1. Security, privacy, and regulation — tenancy, auth, grants, sensitive purpose, trust, country/vertical obligations, abuse, audit, and secret/personal-data safety.
2. Reliability and operations — SLO/RPO/RTO, fleet, queues, observability, backups/restores, incidents, capacity, support, performance, accessibility operations, and runbooks.

- [ ] **Step 5: Dispatch reviewer 04 — delivery and authority**

Return separate verdicts for:

1. Delivery and quality — P00–P19/work packages/milestones, tests, matrices, evidence, migration rehearsal, release/rollback, and definition of done.
2. Authority and traceability — hierarchy, later decisions, statuses/states, conflicts, inventory/ledger/coverage, stable IDs, source/master bidirectionality, and Control split.

- [ ] **Step 6: Enforce one findings schema**

Every reviewer returns identity, independence statement, exact complete candidate identity including `review_packet_commit`, per-lens verdict, and findings sorted `Critical`, `Important`, `Minor`, then finding ID. Every finding names section/master ID, source evidence, consequence, and precise acceptance condition. It never supplies a patch or chooses an unresolved option.

- [ ] **Step 7: Have the writer record exact returned reports**

The designated writer alone uses `apply_patch` to create the four canonical JSON files. A report with Critical/Important findings uses final verdict `Corrections required`; zero-filled correction fields use the explicit states `Pending writer disposition` and `Not re-reviewed`, not missing content. A clean report uses `Approved for signoff review`.

Run:

```bash
MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT" \
MASTER_CANDIDATE_PARENT="$CANDIDATE_PARENT" \
MASTER_BLOB_SHA1="$MASTER_BLOB_SHA1" \
MASTER_SHA256="$MASTER_SHA256" \
MASTER_SOURCE_INVENTORY_SHA256="$SOURCE_INVENTORY_SHA256" \
MASTER_REVIEW_PACKET_COMMIT="$REVIEW_PACKET_COMMIT" \
node scripts/quality/dorzak-master-solution.mjs reviews
```

Expected: either `MASTER_REVIEWS PASS reports=4 lenses=10 critical=0 important=0` or `MASTER_REVIEWS CORRECTIONS_REQUIRED reports=4 lenses=10 critical=N important=M`, where `N` and `M` are nonnegative integers and `N + M` is greater than zero. Identity/independence/lens gaps always fail.

- [ ] **Step 8: Commit the immutable review round**

```bash
git add docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json docs/dorzak-launch/master-solution-evidence/reviews/02-architecture-data.json docs/dorzak-launch/master-solution-evidence/reviews/03-security-operations.json docs/dorzak-launch/master-solution-evidence/reviews/04-delivery-authority.json
git commit -m "docs: record master solution reviews"
```

Expected: exactly four `100644` evidence paths; every report records the same exact `REVIEW_PACKET_COMMIT`; clean worktree. Record the packet commit with the report-round commit in orchestrator memory and later handoff. If Critical/Important is nonzero, proceed only to Task 22. If both are zero, Task 22 still processes Minor dispositions before Task 23.

---

### Task 22: Phase 6B — Apply Corrections and Obtain Fresh Zero-Blocker Re-review

**Files:**
- Modify as justified: `docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md`
- Modify as justified: `docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl`
- Modify as justified: `docs/dorzak-launch/master-solution-evidence/coverage.json`
- Modify for demonstrated validator defect only: `scripts/quality/dorzak-master-solution.mjs`
- Modify for validator correction tests: `scripts/quality/dorzak-master-solution.test.mjs`
- Modify after rerun: `docs/dorzak-launch/master-solution-evidence/validation.json`
- Modify after returned re-reviews: all four exact review JSON paths

**Interfaces:**
- Consumes: one exact review round and every acceptance condition.
- Produces: a corrected exact candidate and immutable review packet with zero Critical/Important findings across all ten lenses; every Minor is corrected or explicitly accepted without weakening a rule.

- [ ] **Step 1: Classify each finding without changing scope**

The writer maps each finding to master, ledger, coverage, validator, or evidence. If acceptance requires a new product/commercial/tenancy/data/security/regulatory/migration/release decision, stop and return the precise blocker to Control. Do not choose the option. If the source inventory changes, return to Task 3 and invalidate every downstream hash/review.

- [ ] **Step 2: Apply accepted corrections serially**

Use `apply_patch`; preserve one canonical statement per rule and update every affected prose section, matrix, flow, glossary entry, decision register, coverage row, and ledger disposition together. A validator correction starts with a failing test. Never edit a review report to hide a finding.

- [ ] **Step 3: Rerun the entire validation suite**

Run, not merely the focused checks:

```bash
node --test scripts/quality/dorzak-master-solution.test.mjs
node --check scripts/quality/dorzak-master-solution.mjs
node scripts/quality/dorzak-master-solution.mjs inventory
node scripts/quality/dorzak-master-solution.mjs ledger
node scripts/quality/dorzak-master-solution.mjs coverage
node scripts/quality/dorzak-master-solution.mjs domain-range DOM-001 DOM-053
```

Expected: all pre-candidate tests/checks pass with 53 domains, zero source/master gaps, and zero commercial, structure, or safety failures.

- [ ] **Step 4: Commit a new candidate before evidence**

Stage only justified candidate-input paths and commit when at least one correction exists:

```bash
PRIOR_CANDIDATE_COMMIT="$(node -e 'const r=require("./docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json");process.stdout.write(r.candidate_commit)')"
PRIOR_REVIEW_PACKET_COMMIT="$(node -e 'const r=require("./docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json");process.stdout.write(r.review_packet_commit)')"
test "$(printf '%s' "$PRIOR_CANDIDATE_COMMIT" | wc -c | tr -d ' ')" = "40"
test "$(printf '%s' "$PRIOR_REVIEW_PACKET_COMMIT" | wc -c | tr -d ' ')" = "40"
for report in docs/dorzak-launch/master-solution-evidence/reviews/*.json; do
  PRIOR_CANDIDATE_COMMIT="$PRIOR_CANDIDATE_COMMIT" PRIOR_REVIEW_PACKET_COMMIT="$PRIOR_REVIEW_PACKET_COMMIT" REPORT="$report" node -e '
    const r = require("./" + process.env.REPORT);
    if (r.candidate_commit !== process.env.PRIOR_CANDIDATE_COMMIT) process.exit(1);
    if (r.review_packet_commit !== process.env.PRIOR_REVIEW_PACKET_COMMIT) process.exit(1);
  '
done
git add docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl docs/dorzak-launch/master-solution-evidence/coverage.json scripts/quality/dorzak-master-solution.mjs scripts/quality/dorzak-master-solution.test.mjs
if git diff --cached --quiet; then
  CANDIDATE_COMMIT="$PRIOR_CANDIDATE_COMMIT"
else
  git commit -m "docs: correct Dorzak master solution review findings"
  CANDIDATE_COMMIT="$(git rev-parse HEAD)"
fi
CANDIDATE_PARENT="$(git rev-parse "$CANDIDATE_COMMIT^")"
MASTER_BLOB_SHA1="$(git rev-parse "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md")"
MASTER_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md" | shasum -a 256 | awk '{print $1}')"
SOURCE_INVENTORY_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/master-solution-evidence/source-inventory.json" | shasum -a 256 | awk '{print $1}')"
```

Record new candidate commit/parent/master blob/master SHA-256. The prior candidate and its review evidence remain immutable history.

- [ ] **Step 5: Regenerate and commit validation evidence**

Regenerate canonical validation against the new candidate, then commit it:

```bash
MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT" \
MASTER_VALIDATION_OUTPUT=docs/dorzak-launch/master-solution-evidence/validation.json \
node scripts/quality/dorzak-master-solution.mjs candidate
git add docs/dorzak-launch/master-solution-evidence/validation.json
if test "$CANDIDATE_COMMIT" = "$PRIOR_CANDIDATE_COMMIT"; then
  git diff --cached --quiet
  REVIEW_PACKET_COMMIT="$PRIOR_REVIEW_PACKET_COMMIT"
else
  ! git diff --cached --quiet
  git commit -m "docs: refresh master solution validation"
  REVIEW_PACKET_COMMIT="$(git rev-parse HEAD)"
fi

test "$(git rev-parse "$REVIEW_PACKET_COMMIT^")" = "$CANDIDATE_COMMIT"
PACKET_DIFF="$(git diff-tree --no-commit-id --name-status -r "$REVIEW_PACKET_COMMIT")"
test "$PACKET_DIFF" = $'A\tdocs/dorzak-launch/master-solution-evidence/validation.json' || \
  test "$PACKET_DIFF" = $'M\tdocs/dorzak-launch/master-solution-evidence/validation.json'
for artifact_path in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence/source-inventory.json \
  docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl \
  docs/dorzak-launch/master-solution-evidence/coverage.json
do
  test "$(git rev-parse "$REVIEW_PACKET_COMMIT:$artifact_path")" = "$(git rev-parse "$CANDIDATE_COMMIT:$artifact_path")"
  test "$(git show "$REVIEW_PACKET_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')" = "$(git show "$CANDIDATE_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')"
done
git cat-file -e "$REVIEW_PACKET_COMMIT:docs/dorzak-launch/master-solution-evidence/validation.json"
```

Expected: a global validation PASS bound to the selected candidate. An unchanged candidate must reproduce byte-identical validation and retain its prior immutable packet; a changed candidate must create a new one-path validation commit whose parent is that candidate. Any other history shape or byte identity stops.

- [ ] **Step 6: Dispatch four fresh read-only re-reviewers**

Use new reviewer tasks independent from the writer. Each repeats the complete Task 21 Step 1 packet protocol, reads all five paths only at the selected `REVIEW_PACKET_COMMIT`, repeats the same assigned lenses, and verifies every prior acceptance condition. A reviewer may not rely on an earlier verdict. The writer appends a new round to each exact report and records candidate identity, review-packet commit, correction commit, and re-review verdict.

- [ ] **Step 7: Repeat only while correction is possible within authority**

Any Critical or Important finding requires another complete correction/validation/four-review round. No maximum-round shortcut exists. A repeated authority/decision blocker stops for Control. Minor findings are corrected or recorded in the same-byte signoff packet with reason, consequence, owner, and explicit confirmation that no requirement is weakened.

- [ ] **Step 8: Seal final review evidence**

Run:

```bash
MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT" \
MASTER_CANDIDATE_PARENT="$CANDIDATE_PARENT" \
MASTER_BLOB_SHA1="$MASTER_BLOB_SHA1" \
MASTER_SHA256="$MASTER_SHA256" \
MASTER_SOURCE_INVENTORY_SHA256="$SOURCE_INVENTORY_SHA256" \
MASTER_REVIEW_PACKET_COMMIT="$REVIEW_PACKET_COMMIT" \
node scripts/quality/dorzak-master-solution.mjs reviews
```

Expected: `MASTER_REVIEWS PASS reports=4 lenses=10 critical=0 important=0`; each prior finding has a disposition and fresh verdict; every Minor is corrected or marked `Accepted for signoff decision`.

Commit:

```bash
git add docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json docs/dorzak-launch/master-solution-evidence/reviews/02-architecture-data.json docs/dorzak-launch/master-solution-evidence/reviews/03-security-operations.json docs/dorzak-launch/master-solution-evidence/reviews/04-delivery-authority.json
git commit -m "docs: record master solution re-review"
```

Expected: the four final reports all record the exact same `REVIEW_PACKET_COMMIT`; record that packet with the final review-evidence commit for signoff and handoff.

---

### Task 23: Phase 7 — Same-Byte Team Signoff, Control Handoff, and Activation Stop

**Files:**
- Create: `docs/dorzak-launch/master-solution-evidence/signoff.json`
- Create: `docs/dorzak-launch/master-solution-evidence/handoff.json`
- Read: all other production artifacts at the final candidate/evidence commits
- Control activation: written only by Control in a separate transition, never by this writer

**Interfaces:**
- Consumes: final complete candidate identity, immutable review-packet commit, validation PASS, four zero-blocker reports/ten lens verdicts, nominated signers, and resolved Minor dispositions.
- Produces: same-byte approvals and a deterministic activation handoff that explicitly grants no execution authority.

- [ ] **Step 1: Freeze final candidate and evidence identities**

Record candidate commit and parent; master path/mode/line count/blob/SHA-256; source-inventory, extraction-ledger, and coverage SHA-256 at the candidate; review-packet commit and validation SHA-256 at that packet; complete counts; four report hashes; ten lens verdicts; Open/Deferred/Superseded IDs; and exact writer diff. Verify all four final reports name the same candidate and review packet. Re-run protected and allowlist checks. No candidate or review-packet input may change after signoff begins.

- [ ] **Step 2: Obtain every required role decision on the same bytes**

Provide the complete candidate identity—candidate commit/parent, master blob/SHA-256, source-inventory SHA-256, and review-packet commit—plus validation/review result, Minor dispositions, and explicit non-execution statement to:

1. Dorzak owner/product authority;
2. business/commercial lead;
3. product/domain lead;
4. engineering/architecture lead;
5. security/privacy lead;
6. reliability/operations lead;
7. quality/release lead; and
8. every named qualified regulated-vertical reviewer required for country/sensitive sections.

Each returns `Approved` on that exact candidate/hash, identity, role, scope, decision time in `+03:00`, and limitations. A rejection, abstention, changed hash, missing role, unresolved Minor, or commercial/regulatory Open decision blocks activation.

- [ ] **Step 3: Create and validate `signoff.json`**

The sole writer uses `apply_patch` to record the exact returned decisions in canonical role order. Run:

```bash
CANDIDATE_PARENT="$(git rev-parse "$CANDIDATE_COMMIT^")"
MASTER_BLOB_SHA1="$(git rev-parse "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md")"
MASTER_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md" | shasum -a 256 | awk '{print $1}')"
SOURCE_INVENTORY_SHA256="$(git show "$CANDIDATE_COMMIT:docs/dorzak-launch/master-solution-evidence/source-inventory.json" | shasum -a 256 | awk '{print $1}')"
REVIEW_PACKET_COMMIT="$(node -e 'const r=require("./docs/dorzak-launch/master-solution-evidence/reviews/01-product-business.json");process.stdout.write(r.review_packet_commit)')"

test "$(git rev-parse "$REVIEW_PACKET_COMMIT^")" = "$CANDIDATE_COMMIT"
PACKET_DIFF="$(git diff-tree --no-commit-id --name-status -r "$REVIEW_PACKET_COMMIT")"
test "$PACKET_DIFF" = $'A\tdocs/dorzak-launch/master-solution-evidence/validation.json' || \
  test "$PACKET_DIFF" = $'M\tdocs/dorzak-launch/master-solution-evidence/validation.json'
for artifact_path in \
  docs/dorzak-launch/DORZAK_MASTER_SOLUTION.md \
  docs/dorzak-launch/master-solution-evidence/source-inventory.json \
  docs/dorzak-launch/master-solution-evidence/extraction-ledger.jsonl \
  docs/dorzak-launch/master-solution-evidence/coverage.json
do
  test "$(git rev-parse "$REVIEW_PACKET_COMMIT:$artifact_path")" = "$(git rev-parse "$CANDIDATE_COMMIT:$artifact_path")"
  test "$(git show "$REVIEW_PACKET_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')" = "$(git show "$CANDIDATE_COMMIT:$artifact_path" | shasum -a 256 | awk '{print $1}')"
done
git cat-file -e "$REVIEW_PACKET_COMMIT:docs/dorzak-launch/master-solution-evidence/validation.json"

export MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT"
export MASTER_CANDIDATE_PARENT="$CANDIDATE_PARENT"
export MASTER_BLOB_SHA1="$MASTER_BLOB_SHA1"
export MASTER_SHA256="$MASTER_SHA256"
export MASTER_SOURCE_INVENTORY_SHA256="$SOURCE_INVENTORY_SHA256"
export MASTER_REVIEW_PACKET_COMMIT="$REVIEW_PACKET_COMMIT"
node --input-type=module <<'NODE'
import { readFileSync } from "node:fs";
import { validateSignoff } from "./scripts/quality/dorzak-master-solution.mjs";
const signoff = JSON.parse(readFileSync("docs/dorzak-launch/master-solution-evidence/signoff.json", "utf8"));
const candidateIdentity = {
  candidateCommit: process.env.MASTER_CANDIDATE_COMMIT,
  candidateParent: process.env.MASTER_CANDIDATE_PARENT,
  masterBlobSha1: process.env.MASTER_BLOB_SHA1,
  masterSha256: process.env.MASTER_SHA256,
  sourceInventorySha256: process.env.MASTER_SOURCE_INVENTORY_SHA256,
  reviewPacketCommit: process.env.MASTER_REVIEW_PACKET_COMMIT
};
const exactFields = {
  candidate_commit: candidateIdentity.candidateCommit,
  candidate_parent: candidateIdentity.candidateParent,
  master_blob_sha1: candidateIdentity.masterBlobSha1,
  master_sha256: candidateIdentity.masterSha256,
  source_inventory_sha256: candidateIdentity.sourceInventorySha256,
  review_packet_commit: candidateIdentity.reviewPacketCommit
};
for (const [field, expected] of Object.entries(exactFields)) {
  if (signoff[field] !== expected) throw new Error(`SIGNOFF_IDENTITY_MISMATCH:${field}`);
}
const result = validateSignoff(signoff, candidateIdentity);
if (result.errors.length) process.exit(1);
console.log(`MASTER_SIGNOFF PASS required=${result.required} approved=${result.approved} missing=0 hash_mismatch=0`);
NODE
```

Expected: every one of the six identity fields is explicitly equal before validator execution; `validateSignoff` receives the complete identity object; PASS has `required` equal `approved`, with at least seven core roles plus every required qualified role.

- [ ] **Step 4: Create the deterministic Control handoff**

Use `apply_patch` to create `handoff.json`. Include all complete candidate-identity fields, exact `review_packet_commit`, packet validation hash, all other identities/counts/hashes, exact validator command/results, source and master zero-gap counts, reviewer identities/lenses/rounds/findings, complete decision registers, signoff rows, exact Control activation request, `master_approval_grants_execution: false`, `execution_authority_granted: false`, and explicit prohibition on P00 Task 16, P01–P19/P17 execution, provider action, deployment, provisioning, migration, and release absent separate Control authorization.

- [ ] **Step 5: Validate the full handoff**

Run:

```bash
MASTER_CANDIDATE_COMMIT="$CANDIDATE_COMMIT" \
MASTER_CANDIDATE_PARENT="$CANDIDATE_PARENT" \
MASTER_BLOB_SHA1="$MASTER_BLOB_SHA1" \
MASTER_SHA256="$MASTER_SHA256" \
MASTER_SOURCE_INVENTORY_SHA256="$SOURCE_INVENTORY_SHA256" \
MASTER_REVIEW_PACKET_COMMIT="$REVIEW_PACKET_COMMIT" \
node scripts/quality/dorzak-master-solution.mjs handoff
git diff --check
git status --short
```

Expected:

```text
MASTER_HANDOFF PASS source_gaps=0 master_gaps=0 critical=0 important=0 blocking_open=0 signoff_missing=0 execution_authority=false
```

Status lists only `signoff.json` and `handoff.json`; diff check exits `0`.

- [ ] **Step 6: Commit the handoff evidence**

```bash
git add docs/dorzak-launch/master-solution-evidence/signoff.json docs/dorzak-launch/master-solution-evidence/handoff.json
git commit -m "docs: hand off Dorzak master solution authority"
```

Expected: exactly two `100644` paths; clean worktree.

- [ ] **Step 7: Return evidence to Control and stop**

Return final handoff commit, candidate commit/parent, master path/mode/line count/blob/SHA-256, source-inventory SHA-256, review-packet commit, ledger/coverage/packet-validation/report/signoff/handoff hashes, zero-gap/count results, four reviewers/ten lens verdicts, signoff identities, protected/allowlist PASS, and the explicit no-execution statement.

Control independently verifies these bytes and, only if every activation prerequisite passes, records `TEAM-APPROVED ACTIVE MASTER` in the Control Register with exact commit/file/source-inventory hashes. The designated writer then stops. It does not edit Control, start Task 16/P01/P17, invoke a provider, deploy, provision, migrate, release, or ask the owner for another execution-mode choice.

---

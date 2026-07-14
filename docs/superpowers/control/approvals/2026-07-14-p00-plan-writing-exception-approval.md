# P00 Implementation-Plan Writing Exception Approval Record

**Approver:** Dorzak product owner, in the Dorzak Launch — Control Room task

**Owner decision:** `Approved at baseline planning`

**Recorded at:** 2026-07-14 07:04:31 +03 (Asia/Qatar)

**Decision stage:** P00 baseline implementation-plan writing only

**Planning task:** Dorzak Launch — P00 Baseline Planning, task `019f5e64-9472-7f32-b0a9-77b4b3741864`

**Exact plan target:** `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md`

**Approved P00 design:** `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md` at commit `ea7b8258083231c6a9b7aa7c00d89009e29e696e`, SHA-256 `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`

## Decision interpretation

The owner issued this decision immediately after the Control Room explained that implementation-plan writing remained blocked and that the safe narrow option was a P00 plan-writing-only exception. The phrase `Approved at baseline planning` is therefore recorded conservatively as approval to write and review the P00 baseline implementation plan only.

It is not interpreted as formal approval of the program-wide source artifacts, preservation work, implementation or execution.

## Permitted planning inputs

The planning task may use these exact artifacts as read-only planning inputs:

- the Approved corrected P00 design identified above;
- the complete-launch product baseline at `docs/superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md`, commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`, SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2`;
- the current technical execution roadmap at `docs/superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md`, commit `069f4833190c75866494e7ba51bff3021070c0bf`, SHA-256 `e9aa2c7970f9edf08f03177458cb496f979a30dbf3cf7fd96480c0c3b9a5cc60`;
- the Approved session orchestration design and latest committed Control Register; and
- read-only repository evidence required to name exact existing paths, commands, dependencies and test surfaces.

The product baseline and technical roadmap remain **Awaiting owner** as program-wide artifacts. Permission to use their exact current versions as P00 planning inputs is not formal approval of either artifact.

## Resolved planning inputs

For the exact plan target, these inputs are resolved:

- the governing P00 design identity and its Approved status;
- the exact read-only product-baseline and roadmap versions permitted as P00 planning inputs;
- the mandatory P00 boundary order and ownership rules;
- the local-media and HTTP(S) pass-through contracts;
- Qatar/QAR as the canonical demo and E2E contract;
- PostgreSQL 16 as the qualification lane;
- the provider-neutral quality-command and CI-job contracts;
- the exact planning task, plan target and single-writer rule; and
- the requirement to use TDD-oriented task boundaries, explicit verification evidence and frequent focused commits.

## Inputs deferred beyond plan entry

These inputs remain unresolved and are not supplied by this exception:

- canonical Git remote;
- CI provider;
- exact production PHP and Node runtime pins;
- MediaUrl preservation method, preserved artifact identity and verification evidence;
- approved integration `BASE_SHA`;
- named clean execution worktree; and
- any infrastructure choice whose exact plan steps vary according to one of those decisions.

The plan may define the decision record, prerequisites, invariant checks and alternative bounded command branches for these inputs. If an unresolved input affects an exact planning choice that cannot be represented without assumption, the task must stop the affected plan work and return that decision to the Control Room. It may not choose a value, silently defer a required task, or use placeholder language.

## Authorization granted

The existing P00 planning task is **Approved to plan** at the exact target path. It may:

- refresh the latest committed Control Register before writing;
- inspect the repository and permitted inputs read-only;
- use the `writing-plans` workflow;
- write a complete TDD-oriented, task-by-task P00 implementation plan at the exact target;
- self-review the plan for specification coverage, placeholder language, type/interface consistency, exact commands and executable task boundaries;
- stage and commit only the exact plan target; and
- report the full plan commit SHA and stop at owner plan review.

The plan must represent unresolved remote, CI-provider, runtime, MediaUrl-preservation and integration-base decisions as explicit pre-execution gates. It may not select them by assumption.

## Explicit exclusions

This exception does **not** authorize:

- formal program-wide approval of the complete-launch product baseline or technical roadmap;
- edits to the Approved P00 design, approval/review/control records or any other documentation path;
- application, test, configuration, CI, dependency or lockfile changes;
- MediaUrl preservation, staging or commit activity;
- choosing unresolved infrastructure or integration inputs;
- creating or reusing a branch or worktree;
- executing any implementation-plan step;
- creating the P00 execution task;
- P00 execution or P01–P19 work; or
- public release.

## Next gate

The planning task writes, self-reviews and commits only the exact plan target, then stops. The Control Room independently verifies the plan and returns its exact path, commit and content hash to the owner for implementation-plan approval.

No planning approval implies MediaUrl-preservation, worktree, implementation or execution approval.

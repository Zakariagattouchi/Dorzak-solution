# P00 Corrected Written Specification Approval Record

**Approver:** Dorzak product owner, in the Dorzak Launch — Control Room task

**Owner decision:** `Approved`

**Recorded at:** 2026-07-14 07:01:02 +03 (Asia/Qatar)

**Decision stage:** Corrected P00 written-specification approval only

**Source planning task:** Dorzak Launch — P00 Baseline Planning, task `019f5e64-9472-7f32-b0a9-77b4b3741864`

**Approved artifact:** [2026-07-14-dorzak-p00-baseline-stabilization-design.md](../../specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

**Approved artifact commit:** `ea7b8258083231c6a9b7aa7c00d89009e29e696e`

**Approved artifact SHA-256:** `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`

**Approval-gate control revision:** `865611e78c13d305449573480cae62c48f62fedb`

**Review evidence:** [P00 written-specification control review](../reviews/2026-07-14-p00-written-spec-review.md), including the corrected-artifact and final control-transition reviews

## Decision context

The Control Room returned the exact corrected artifact, commit and SHA-256 to the owner and explicitly requested the response `Corrected P00 approved.` The owner responded `Approved` in that immediate context. This record binds that decision only to the exact artifact identity above.

The earlier owner signal for commit `96844070bb0d1d8887afc10b0f21c86f04399f5a` remains historical evidence. It is not used as approval of the corrected artifact.

## Approved scope

The owner approves the corrected P00 baseline-stabilization written specification as the governing P00 design. The approval includes its:

- mandatory serialized implementation order;
- eight implementation boundaries and their ownership limits;
- local-media and HTTP(S) pass-through contracts;
- Qatar/QAR canonical demo and E2E contract;
- PostgreSQL 16 qualification lane;
- dirty-state, MediaUrl-preservation, recovery and clean-worktree gates;
- canonical quality-command and CI-job contracts;
- measurable P00 exit criteria;
- corrected implementation-planning prerequisite; and
- corrected requirement for completed, verified and evidenced MediaUrl preservation before execution.

## Authorization consequence

- The exact P00 written specification is **Approved**.
- The Control Room may record this approval and evaluate the separate implementation-plan entry prerequisites.
- The P00 planning task remains **Not authorized** to write an implementation plan until those prerequisites are separately satisfied and durably recorded.

## Explicit exclusions

This approval does **not** authorize:

- formal approval of the complete-launch product baseline;
- formal approval of the technical execution roadmap;
- a P00 plan-writing exception;
- writing or committing a P00 implementation plan;
- choosing unresolved remote, CI, runtime, integration-base or infrastructure inputs by assumption;
- preserving, staging or committing the existing MediaUrl change;
- creating an execution branch or worktree;
- P00 implementation or execution;
- any P01–P19 work; or
- public release.

## Next gate

Before implementation-plan writing may begin, the owner must either:

1. formally approve the exact current complete-launch product baseline at commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`, SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2`, and technical execution roadmap at current artifact commit `069f4833190c75866494e7ba51bff3021070c0bf`, SHA-256 `e9aa2c7970f9edf08f03177458cb496f979a30dbf3cf7fd96480c0c3b9a5cc60`; or
2. grant a separate, exact P00 plan-writing-only exception that names its scope and exclusions and cannot become implementation or execution authority.

After that decision is durably recorded, the Control Room may grant **Approved to plan** for the exact P00 implementation-plan target. No code or execution authority follows from that transition.

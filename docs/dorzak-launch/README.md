# Dorzak Launch Hub

This folder is the single navigation entry point for the Dorzak complete-launch program.

Approved historical artifacts are not moved because their paths, commits and hashes are part of the approval record. This hub groups them by link so there is one place to find the current truth without duplicating content.

## Start here

1. [Control Register](../superpowers/control/README.md) — current status, authority, blocker and next action.
2. [Complete-launch product baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md) — what the final product must contain.
3. [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md) — P00–P19 sequence and milestone gates.
4. [Session orchestration design](../superpowers/specs/2026-07-14-dorzak-session-orchestration-design.md) — task, worktree and agent ownership.
5. [Lean working method](./WORKING_METHOD.md) — short-session and economic review rules.

## Active package: P00

- [Approved P00 design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)
- [Proposed E2E safety erratum](../superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md)
- [P00 implementation plan](../superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md)
- [P00 implementation-plan review](../superpowers/control/reviews/2026-07-14-p00-implementation-plan-review.md)

The Control Register, not this index, owns lifecycle and authorization.

## Approved program decisions

- [P17 Frappe-native Superadmin owner decision](../superpowers/control/approvals/2026-07-14-p17-frappe-native-superadmin-owner-decision.md) — Frappe Desk for Dorzak's internal control environment, strict merchant-site isolation, governed intervention and a defined Dorzak/Frappe source-of-truth boundary.

## Program organization

~~~text
docs/dorzak-launch/                 one human-friendly entry point
docs/superpowers/control/           status, approvals and reviews
docs/superpowers/specs/             approved designs and proposed errata
docs/superpowers/plans/             executable work-package plans
docs/superpowers/evidence/          measured completion evidence
~~~

Owner drafts and supplied source repositories remain in their current locations until a separately approved ingestion task classifies them. They are never moved, overwritten or silently promoted to authority.

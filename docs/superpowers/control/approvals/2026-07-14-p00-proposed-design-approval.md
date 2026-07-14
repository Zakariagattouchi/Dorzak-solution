# P00 Proposed Design Approval Record

**Approver:** Dorzak product owner, in the Dorzak Launch — Control Room task

**Owner decision:** `P00 approved`

**Recorded at:** 2026-07-14 05:39:11 +03 (Asia/Qatar)

**Decision stage:** Proposed-design approval only

**Source planning task:** Dorzak Launch — P00 Baseline Planning, task `019f5e64-9472-7f32-b0a9-77b4b3741864`

**Approved proposal copy:** [2026-07-14-p00-baseline-stabilization-proposal.md](../proposals/2026-07-14-p00-baseline-stabilization-proposal.md)

**Approved proposal SHA-256:** `551054d063a89b8b361b4dbd45fefa03ec9e91915148b2490e0b454f57704320`

**Proposal authority revision:** Control Register commit `267704e1c58dd9cff9aa90b4f69375fc8b0cf292`

**Exact target artifact:** `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md`

## Approved scope

The owner approves the preserved P00 proposal as the source for writing the formal P00 baseline-stabilization design. This includes:

- the serialized, layered, contract-first approach;
- the origin-relative local-media contract and HTTP(S) pass-through contract;
- Qatar/QAR as the canonical demo and E2E tenant contract;
- keeping subscription-currency redesign in P03;
- the eight proposed ordering/task boundaries;
- the dirty-state, safety, recovery, evidence and measurable P00 exit rules;
- PostgreSQL 16 as the proposed database qualification lane;
- provider-neutral canonical quality commands and the listed required CI jobs;
- preserving the MediaUrl patch independently, with the recommended separate owner-approved commit represented in the design;
- writing and committing the exact formal design artifact named above, followed by self-review and return to the owner gate.

Because the complete-launch baseline and technical roadmap remain review candidates, this record also grants the narrow exception required by the Control Register: the P00 planning task may use those candidates as design inputs solely to write the formal P00 design. This exception is not formal approval of either program-wide artifact and is not execution authority.

## Decisions deliberately left open

The formal design must expose, and must not silently choose, these unresolved implementation inputs:

- the canonical Git remote;
- the CI provider; GitHub Actions remains conditional on establishing a GitHub remote;
- exact production PHP and Node runtime pins;
- the approved integration base and future clean P00 worktree;
- the exact MediaUrl preservation commit or reviewed-patch action.

These items may block implementation planning or execution, but they do not block writing the formal design when represented as explicit open decisions and gates.

## Authorization granted

The existing P00 planning task is **Approved to write design** at the exact target path. It may inspect authority and evidence, write that design, self-review it, and commit only the design plus any strictly necessary correction to its own approved proposal/approval references if the Control Room first records such a correction.

After the design commit, the task must stop and return the artifact path and commit SHA to the Control Room for owner written-spec approval.

## Explicit exclusions

This approval does **not** authorize:

- formal program-wide approval of the complete-launch product baseline or technical roadmap;
- an implementation plan;
- application, test, configuration, CI, dependency or documentation implementation outside the one formal design artifact;
- staging or committing the existing MediaUrl patch;
- selecting a remote, CI provider, runtime versions or integration base by assumption;
- creating or reusing a branch or worktree;
- starting P00 execution or any P01–P19 work;
- public release.

## Next gate

The planning task writes and commits the formal P00 design, then the Control Room verifies the commit and asks the owner for **written P00 specification approval**. No implementation-plan work begins before that separate approval is durably recorded.

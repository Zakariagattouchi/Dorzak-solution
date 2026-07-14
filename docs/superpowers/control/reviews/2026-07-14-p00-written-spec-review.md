# P00 Written Specification Control Review

**Reviewed artifact:** `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md`

**Reviewed commit:** `96844070bb0d1d8887afc10b0f21c86f04399f5a`

**Artifact SHA-256:** `24914e833fea8f20195f16bc619a5f9068cf5ab2ae281ec2ad4a7068e34af3da`

**Review completed:** 2026-07-14 06:27:26 +03 (Asia/Qatar)

**Review mode:** Independent, read-only control/specification conformance review

**Owner decision signal received before review:** `Reviewed and approved.`

**Verdict:** No Critical findings; two Important findings require correction before the owner decision can become durable written-spec approval or authorize implementation-plan writing.

## Verified commit integrity

- Commit `96844070…` has sole parent `4658111c…`.
- Its only delta is the authorized 445-line P00 design file.
- The proposal, prior approval and Control Register blobs were unchanged by the design commit.
- The approved proposal SHA-256 remains `551054d063a89b8b361b4dbd45fefa03ec9e91915148b2490e0b454f57704320`.
- `git diff --check` passes.
- A fresh 20-anchor semantic verification passed.
- The non-control dirty-state manifest remains `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.

## Important finding 1 — Planning authority prerequisite is under-specified

The design's implementation-planning gate at lines 384–386 requires written-spec approval and a separate authorization that states the product-baseline and roadmap status. The parent Control Register requires more: before any implementation plan is written, those two artifacts must either be formally approved or an exact owner-approved planning exception must be durably recorded.

The existing exception is expressly design-only, while the design leaves program-wide approvals unresolved. Merely recording `Awaiting owner` would therefore be insufficient.

**Required correction:** Section 11.1 must require formal approval of both program-wide artifacts **or** a separate, exact, owner-approved P00 plan-writing exception. The exception must name its scope and exclusions and cannot become execution authority.

## Important finding 2 — MediaUrl preservation must be completed, not merely selected

The parent Control Register requires the existing user-owned MediaUrl diff to be preserved and the future execution worktree to be clean before execution. The design's execution gate requires only recording the chosen preservation action; its preservation section does not clearly require that action to be completed, verified and evidenced before execution authorization.

**Required correction:** Sections 7.1, 9 and 11.2 must make successful, evidenced preservation an execution-entry condition. The record must identify the approved preservation method, exact patch/commit hash, verification result and resulting integration base. Selection of a method alone is not sufficient.

## Control consequence

- The owner's approval message remains a non-durable decision signal tied to commit `96844070…`.
- The written specification is not yet durably Approved.
- Implementation-plan writing remains Not authorized.
- The existing P00 planning task may receive a narrowly scoped design-correction lease for the exact design file only.
- After a corrected commit and clean independent review, the Control Room must ask the owner to approve the new exact artifact/hash.
- No implementation plan, MediaUrl preservation action, worktree, execution task or code change may begin.

## Resolution

The two Important findings were corrected in commit `ea7b8258083231c6a9b7aa7c00d89009e29e696e`. The corrected artifact SHA-256 is `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`.

- Section 11.1 now requires either formal approval of the product baseline and technical roadmap or a separate exact, owner-approved, durably recorded P00 plan-writing-only exception. It states that neither artifact status nor the earlier design-writing exception is sufficient.
- Sections 7.1, 9 and 11.2 now require the approved MediaUrl preservation action to be completed, verified and evidenced before execution. The evidence must name the preservation method, exact patch/commit hash, verification result, resulting `BASE_SHA` and clean named worktree state.
- The corrected commit changes only the formal P00 design relative to its review/control parent `70d5701b943bfc0b46314ec6325232dadcc2e392`.
- The Control Register's future lifecycle diagram, P00 program gate, WP-P00 gate, execution-task gate and protected-state rule were also expanded in the current control revision after follow-up review identified weaker shorthand there.
- A final independent read-only review of `ea7b825…` found no Critical or Important findings. It confirmed both corrections, the exact parent and sole-file delta, the artifact hashes, unchanged mandatory order/contracts/boundaries/exit gate, and the absence of planning or execution authority.
- A separate final read-only review of the Control Register transition found no Critical or Important findings after its repeated execution-gate summaries and historical commit timestamps were reconciled.

The earlier owner decision remains tied to commit `96844070bb0d1d8887afc10b0f21c86f04399f5a`. Because the corrected artifact has a new commit and content hash, its lifecycle is **Awaiting owner** and all P00 plan-writing and execution authorization remains withheld pending a new exact-artifact decision.

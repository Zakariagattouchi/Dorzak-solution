# P00 Amended Roadmap, Plan and Execution Green-Light Record

## Decision metadata

- Approver: Dorzak owner, in the `Control Room` task `019f6010-88bb-7653-ab98-15ef5d75c7e0`
- Recorded at: 2026-07-14 14:55:34 +03 (Asia/Qatar)
- Control/review base: `10730b447cc551efc2ebcd1e3e7c42968dc64389`
- Decision stage: exact amended-roadmap/plan approval and conditional local P00 execution green light
- Protected-state boundary: 16 path-status entries, SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`

## Preserved owner decisions

The owner supplied the canonical repository as:

> `https://github.com/Zakariagattouchi/dorzak.git`

The owner approved the standard GitHub-hosted `ubuntu-24.04` x64 runner selection and then directed the Control Room to make routine recommended choices without repeated approval stops. After the Control Room named amendment commit `10730b447cc551efc2ebcd1e3e7c42968dc64389`, the documented GitHub limitation, `reviewed_patch` preservation, clean P00 workspace creation, and P00 execution after the registered gate, the owner replied:

> Okay, green light. You don't need my approval for the next couple of steps. It's all approved. Carry on, and then start implementing right after that you can start running the next step.

The owner further requested that implementation tasks run in separate short sessions and report their commits and evidence back to this light Control Room.

## Exactly approved amended artifacts

The owner approves the following exact bytes after fresh independent review reported zero Critical and zero Important findings:

1. `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md` at commit `10730b447cc551efc2ebcd1e3e7c42968dc64389`, SHA-256 `912bbdee7ee446e4184713e55215c8ffad30a7a3bb62025e19ee68b1bb337c91`.
2. `docs/superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md` at commit `10730b447cc551efc2ebcd1e3e7c42968dc64389`, SHA-256 `5e73f8bc9e6a9c6b1e0a36fbbbe3d014c785acd8c5ca73b57e6f1a1bac600cb0`.

The focused commit changes exactly those two paths. Independent pre-commit and post-commit review each returned 0 Critical / 0 Important. The embedded Task 13 tests passed 9/9, the Task 14 provider normalizer passed 5/5, and the generated workflow passed YAML, action, and shell validation.

## GitHub limitation accepted for local execution

The private repository remains private. The owner accepts that:

- classic required-check context `required-gates` does not cryptographically bind a workflow path, so exact workflow identity is enforced through Control Room exact-byte review;
- the private branch-protection readback API currently returns `403` until GitHub Pro is separately authorized and enabled; and
- the current GitHub credential lacks the `workflow` scope.

These limitations do not block local P00 Tasks 0–16. Push/provider mutation and all of Task 17 remain outside this approval.

## Preservation and execution authorization

The owner selects `reviewed_patch` for the protected `backend/app/Support/MediaUrl.php` change. After this record and the matching Control Register transition are committed, the Control Room may:

1. inspect only that path's full-index diff, export it as a durable patch, hash it, and prove exact application in a disposable clean checkout;
2. classify and hash the 16 owner-owned paths only when each is safe to classify as owner-approved non-secret;
3. add the approved `origin` remote locally without pushing;
4. establish the clean plan-conforming base and named branch `codex/launch-baseline-v1` in a new isolated worktree;
5. provision the approved immutable PostgreSQL 16 test-only service and sanitized attestations;
6. commit and verify the closed schema-2 P00 execution-entry record; and
7. run the non-writing Task 0 gate and, only if it passes exactly, execute local P00 Tasks 1–16 in short separate sessions under their per-task file and commit boundaries.

This is the owner's explicit execution green light for the exact amended plan, delegated to the Control Room to bind the remaining plan-conforming concrete paths, hashes, identities, and local service observations in the schema-2 record. A failed or incomplete Task 0 still stops implementation.

## Explicit exclusions

This approval does not authorize:

- pushing any branch, changing GitHub state, refreshing credential scopes, purchasing/enabling GitHub Pro, or claiming branch protection is effective;
- all of Task 17, including review, fresh verification, provider work and final evidence publication;
- P17 planning or implementation, P01–P19 work, or public release;
- changing the protected user checkout's `MediaUrl.php` bytes; or
- staging, resetting, deleting, formatting, moving, or absorbing any other protected user work.

## Next gate

Commit this record and matching Control Register transition. Then complete and verify preservation, the protected-content manifest, clean base/worktree, immutable PostgreSQL attestations and schema-2 execution entry. Task 1 may start only after Task 0 emits its exact pass line.

# P00 Implementation Plan Control Review

**Reviewed artifact:** `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md`

**Reviewed commit:** `a44780dd259a02fbf6db81f8d0e8a113e310c78a`

**Artifact SHA-256:** `a8c94d01af34c386e4291f97534c8251c5efa3ea20f11856c5b834009a27608d`

**Review completed:** 2026-07-14 08:07:57 +03 (Asia/Qatar)

**Review mode:** Independent read-only specification, repository, safety and executability review

**Owner decision in force:** `Approved at baseline planning` authorizes exact P00 plan writing/correction only. It is not approval of this plan revision and grants no implementation or execution authority.

**Verdict:** One Critical and nine Important findings require correction. The plan remains in Planning and may be modified only at its exact authorized path. P00 execution remains Not authorized.

## Verified commit integrity

- Commit `a44780dd…` has sole parent `b2559301…`.
- Its only delta is the authorized P00 implementation-plan file.
- The plan contains 2,790 lines, 18 ordered tasks (Tasks 0–17), 112 checkboxes and balanced fenced code blocks.
- `git show --check a44780dd…` passes.
- The protected checkout still has the registered 16-entry path/status manifest with SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.
- Review was read-only; neither product code nor user-owned dirty state was changed.

## Critical finding 1 — The destructive E2E reset accepts filesystem aliases

Plan lines 488–507 validate only environment, connection, parent directory and basename before invoking `migrate:fresh`. A symlink named `database/dorzak-e2e.sqlite` satisfies those checks while resolving to an unrelated SQLite database. A hard link or other pre-existing alias is also not excluded. Because this fixture is ignored, the clean-worktree guard cannot detect that substitution.

This violates the approved design's requirement that destructive reset be physically constrained to a newly controlled dedicated E2E database.

**Required correction:** Replace the guard and its tests with complete code that:

- derives one exact lexical target from `database_path('dorzak-e2e.sqlite')` rather than trusting an arbitrary configured path;
- rejects symlinks and every pre-existing non-regular or multiply linked target using no-follow filesystem inspection;
- creates a missing file exclusively, then revalidates its canonical parent, regular-file identity and single-link state before destructive work;
- fails closed on every inspection/create race or identity change;
- proves safe creation and refusal of symlink, hard-link, wrong path, wrong environment, wrong connection, directory and other unsafe cases without running destructive work against the unsafe target.

## Important finding 1 — Repository-contract mistakes make named tests fail

Several exact instructions disagree with the current repository:

- Plan line 451 reads `$store->subscription->plan->value`, but `App\Models\Plan` is an Eloquent model whose plan identifier is `code`; it has no `value` property.
- The Vitest configuration at lines 1319–1329 does not scope unit discovery, so `vitest run` can discover the Playwright `tests/e2e/*.spec.ts` files.
- Lines 1221–1227 expect seven passes from the Chromium project even though its configured setup dependency also runs unless the command explicitly disables dependencies.

**Required correction:** Use the real `Plan::code` contract, explicitly include only frontend unit/component test globs in Vitest (or exclude E2E specs), and make the Playwright command and expected count agree deterministically—for example, run setup separately and use `--no-deps` for the seven-journey count.

## Important finding 2 — Several steps are descriptions, not executable implementation instructions

The required `writing-plans` standard says every changed-code step must contain complete code and exact verification. The current plan leaves material implementation to inference:

- Task 7 supplies isolated assertions rather than complete test files with imports, wrappers, mocks and reset/setup behavior.
- Task 8 describes component tests without complete test code.
- Task 11 describes the PostgreSQL XML transformation instead of giving a complete deterministic file/change.
- Task 12 omits the complete concurrency test despite making it an exit gate.
- Task 13 describes several test suites, result writers, aggregators and validators without complete implementations.
- Tasks 15 and 16 outline ADR/runbook content rather than supplying complete deterministic content or generation steps.
- Task 17 lists evidence schema and payload fields without complete schemas/generators.

**Required correction:** Make every created or modified file deterministic from the plan. Supply complete compilable/runnable code and tests, complete document content, or a complete deterministic patch/generator for each file. Measured evidence values may remain runtime outputs, but their schemas, generators, sanitization and validators must be fully specified and tested. Placeholder prose such as “write tests,” “implement,” “must contain” or “use X” cannot stand in for required code.

## Important finding 3 — The mandatory serialized CI boundary is bypassed

Plan lines 2394–2406 stop Task 14 for an owner-selected provider amendment, then explicitly allow Task 15 to proceed. The approved design requires every boundary to be accepted before the next serialized task begins.

**Required correction:** Make Task 14 a hard stop for Tasks 15–17. The provider decision, complete plan amendment, renewed exact-plan approval, adapter implementation, required-status configuration and verification must all finish before Task 15 starts. Task 0 must reject execution against a superseded pre-amendment plan.

## Important finding 4 — Fresh-checkout verification requests a SHA that the remote does not yet have

Plan lines 2648–2654 clone the canonical remote and check out `CODE_SHA`, but the first push of that SHA is later at lines 2677–2682. A normal remote clone therefore cannot obtain the local implementation commit.

**Required correction:** Create the independent fresh checkout from the clean local execution repository with local object sharing disabled, such as a `--no-local --no-checkout` clone, and verify detached `CODE_SHA`; alternatively require an explicitly approved pre-verification remote ref and reorder the push. Keep the later canonical push/remote verification separate from local fresh-checkout proof.

## Important finding 5 — Protected dirty-state checks prove only path/status labels

Plan lines 19–32 verify the 16 `git status --short` records, but the same `M`/`??` records can conceal changed contents. The plan does not reprove the protected checkout's branch, HEAD, worktree identity or the exact reviewed MediaUrl diff/artifact relationship at each writer boundary.

**Required correction:** Add non-secret execution inputs and fail-closed checks for the protected checkout's branch, HEAD, worktree-list identity, reviewed MediaUrl full-index diff hash, preservation artifact and a safe content-integrity manifest for all protected dirty paths. Apply them in Task 0, every writer boundary and final verification. Do not hash secret file contents into committed evidence; define an approved safe manifest method and stop on paths that cannot be safely attested.

## Important finding 6 — Task 13 evidence is attributed to the wrong commit

The result writer records `git rev-parse HEAD` at line 2296, while the six canonical jobs run at lines 2360–2367 before the Task 13 files are committed at lines 2371–2382. The results therefore name the Task 12 SHA while exercising uncommitted Task 13 code.

**Required correction:** Run red/focused tests while developing, then commit the focused Task 13 files, require a clean worktree and run all six canonical jobs plus aggregation again on the Task 13 commit. Only the post-commit results may become canonical evidence.

## Important finding 7 — PostgreSQL identity is not necessarily immutable

The execution interface at line 57 accepts `external-postgresql-16`, and Task 0 checks only that an image value exists. This does not prove the approved design's exact immutable PostgreSQL 16 image identity and cannot be compared across local/fresh/CI evidence.

**Required correction:** Require an immutable OCI reference with an explicit `@sha256:<64-hex>` digest, or a separately approved equally immutable external-service identity contract. Validate PostgreSQL's server version and the exact image/service identity in Task 0, the PostgreSQL runner, both CI runs and final evidence without exposing credentials.

## Important finding 8 — Required large-chunk debt disappears from the plan

The approved design requires the existing Vite large-chunk warning to remain explicit measured debt. The plan records bundle byte totals and a limit but neither captures nor carries that warning into maintained documentation/evidence.

**Required correction:** Capture the exact build warning deterministically, hash or structure its sanitized evidence, test its expected-debt status, and record it in maintained technical debt/runbook material and final evidence. Do not suppress it or treat a passing gzip budget as resolution.

## Important finding 9 — Final evidence construction is not reproducible or self-validating

Task 17 names eight evidence files and required fields but does not give a complete JSON Schema, complete measured-artifact builder, exact CI normalization logic or full validator tests. It also relies on fixed pass totals in several tasks without defining how an intentional test addition updates the accepted counts and provenance.

**Required correction:** Supply complete schema, evidence builder and validator implementations with exact input paths, stable serialization, hash rules, identity comparison, secret rejection and failure tests. Derive expected counts from the committed canonical results or an explicitly versioned manifest so adding the plan's own tests cannot leave stale magic totals.

## Control consequence

- Commit `a44780dd…` is a reviewable plan candidate, not an approved execution plan.
- The P00 planning task may correct only `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md` against these findings.
- It must retain the approved P00 scope, ordered boundaries and hard stops; correction cannot choose unresolved owner/provider/runtime/base decisions.
- The corrected file must be the sole file in a new focused commit and must receive another independent read-only review.
- If that review reports zero Critical and zero Important findings, the Control Room must present the corrected commit and content hash for a new exact owner approval.
- No MediaUrl preservation action, branch/worktree creation, dependency install, test/config/code/CI change, implementation task, P01 work or release is authorized.

## Resolution

Open. Awaiting a corrected plan commit and independent re-review.

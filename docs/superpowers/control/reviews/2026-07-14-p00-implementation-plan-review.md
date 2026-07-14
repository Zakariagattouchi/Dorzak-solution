# P00 Implementation Plan Control Review

**Reviewed artifact:** `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md`

**Reviewed commit:** `a44780dd259a02fbf6db81f8d0e8a113e310c78a`

**Artifact SHA-256:** `a8c94d01af34c386e4291f97534c8251c5efa3ea20f11856c5b834009a27608d`

**Latest corrected artifact commit:** `5c88dc36fa8e5948160b257d3cd1ba0dd9ed675e`

**Latest corrected artifact SHA-256:** `46da62efdaf73820cece759bc339f955196431b6b96a3331aacac6c3bc6043b5`

**Review completed:** 2026-07-14 08:07:57 +03 (Asia/Qatar)

**Review mode:** Independent read-only specification, repository, safety and executability review

**Owner decision in force:** `Approved at baseline planning` authorizes exact P00 plan writing/correction only. It is not approval of this plan revision and grants no implementation or execution authority.

**Initial verdict:** One Critical and nine Important findings required correction.

**Latest re-review verdict:** Commit `5c88dc36…` resolves the prior registered findings but its final exact-commit assessment found two Critical and thirteen Important defects. The plan remains in Planning and may be modified only at its exact authorized path. P00 execution remains Not authorized.

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

## Corrected-plan re-review — `822a8ce5ca6dac3aa236ec0c14122b0cddcf5baa`

The corrected plan is a one-path commit with parent `5c4eca4c516de9771eaf9242f462c04dcbc2feaa`, 5,221 lines, 18 ordered tasks, 109 checkboxes and 276 balanced fence delimiters. `git show --check` passes, the index is empty, and the protected 16-entry path/status manifest remains SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.

Three independent read-only reviews plus Control Room verification found the following remaining blockers.

### Critical — The destructive SQLite reset still has a pathname race

The lease locks and verifies one file descriptor, but Laravel/PDO later reopens the configured pathname for `migrate:fresh`. Rename, unlink, parent replacement and link substitution remain possible between the final check and that destructive open; advisory `flock` does not prevent them. The existing race tests substitute the target only before an explicit recheck and therefore do not prove that a substituted inode cannot receive migration or seeding. A failed migration also leaves an unmarked command-created database that the next invocation refuses.

**Required correction:** Redesign the complete E2E database construction/publication flow so destructive work can touch only a newly command-owned candidate, no substituted/aliased inode or unrelated directory entry can be modified or removed at any race point, and failure preserves the previous usable fixture while safely discarding the candidate. Bind all migration and seeding work to the proven database identity instead of reopening an untrusted pathname. Add deterministic target- and parent-replacement tests inside the destructive-open interval plus migration/seed failure-recovery tests.

### Important findings

1. **E2E API and PSR-4 layout disagree.** The interface promises `E2eDatabaseLease::acquire()`, but acquisition is implemented as `ResetE2eDatabase::acquireDatabase()`, and the lease class is declared in `ResetE2eDatabase.php` rather than its own PSR-4 file. Make the published API, files, tests and staging counts exact and independently autoloadable.
2. **Tasks 2–3 have false intermediate counts and leave a real stale assertion.** Task 2 cannot report 444 passing tests while the Demo failure remains. Task 3 changes USD/QAR but does not replace the repository's `plan->value` assertion with `plan->code`. Correct the exact red/green expectations and explicit patch.
3. **One browser locator is ambiguous.** The unscoped `Add Product` role/name selector matches both POS and Topbar buttons. Scope it to the intended landmark/region or give the actions distinct accessible names, then test strict-mode uniqueness.
4. **Task 11 names a nonexistent test path.** Replace every `tests/Feature/Product/ProductApiTest.php` occurrence with the actual `tests/Feature/Catalog/ProductApiTest.php` and retain exact red/green commands.
5. **The Task 14 stop-marker checks match their own command literals.** Removing the intended marker cannot make Task 0 or Task 17 pass. Use a fail-closed structural gate whose sentinel is not reproduced by its verifier, and test pending and amended states.
6. **Protected status verification strips a significant status byte.** `trim()` removes the leading space from the first current ` M` porcelain record, corrupting the registered hash and manifest match. Remove only terminal CR/LF bytes and prove exact two-column porcelain preservation.
7. **PostgreSQL evidence is declared, not bound to the connection.** The runner observes only version/database and copies identity environment variables into evidence. Bind the actual `DB_URL` connection to independently verified OCI/external attestation in local, fresh and CI runs, without exposing credentials, and test a mismatched PostgreSQL 16 endpoint.
8. **A required Task 13 Node test cannot pass.** The alleged no-large-chunk-debt case leaves the oversized unused `entry.js` in `dist`; `measureBundle()` scans all JavaScript and therefore succeeds instead of throwing. Make the fixture truly debt-free before asserting rejection and run the exact committed negative tests.
9. **The local/CI fingerprint contract is not provider-neutral.** It requires a macOS/ARM local Chromium executable hash to equal a likely Linux/x64 CI hash while OS/architecture are neither pinned nor modeled. Composer and npm are observed but not approved/pinned. Separate portable approved inputs from platform observations, require two CI runs to match each other exactly, and either approve one immutable execution platform or explicitly model allowed local/CI platform differences without falsifying provenance.
10. **CI job provenance is not bound to its wrapper or raw artifacts.** Each job can carry a different SHA/input fingerprint from the CI run/local aggregate, and aggregation accepts artifact-shaped hashes without recomputing available raw artifacts. Cross-check every job SHA, inputs and fingerprint against its run; recompute downloaded/local artifact hashes; add mismatch tests.
11. **The emitted JSON Schema is not enforced.** `validateEvidence()` never applies the closed schema or a complete equivalent. Extra properties and inconsistent `local`, `bundle`, `review` and `ciRuns` path/hash summaries can pass. Enforce exact keys/types at every boundary and cross-bind every summary to its sibling payload, including immutable PostgreSQL syntax.
12. **Secret rejection is too narrow.** Obvious keys/values such as `apiKey`, `clientSecret`, `accessToken`, `dbUrl`, cookies and query-string credentials can pass. Use schema allowlists plus broader recursive key/value/URI rejection and mutation tests; never normalize raw secrets into committed evidence.
13. **The evidence implementation's own tests are not in post-commit provenance.** `test-run-p00.sh` and `p00.test.mjs` run only before the Task 13 commit and are absent from the clean Task 13, fresh-checkout and CI matrices. Include their exact committed execution in a canonical job and count/prove it deterministically.
14. **Generated runbooks are not runnable from fresh setup.** They omit the Chromium installation and the required PostgreSQL identity/attestation variables even though every job collects them. Add the exact dependency and non-secret input preparation/validation commands.
15. **The SHA contract contradicts itself.** Prose permits `INTEGRATED_SHA` to differ after an integration merge, but the builder requires equality with `CODE_SHA`. Choose one exact provider-neutral rule. If equality is retained, require Task 14 to execute the pushed head SHA and remove merge-SHA claims; otherwise add complete integrated-diff review and evidence support.

## Control consequence

- Commit `822a8ce5…` is not an owner-approval candidate and is not execution authority.
- The existing correction-only authorization remains limited to the exact plan path.
- The next corrected revision must resolve every finding above, preserve the 18-task order and approved P00 scope, and receive another independent re-review.
- No MediaUrl preservation, worktree, implementation, dependency, CI, application, P01 or release action is authorized.

## Final assessment of correction candidate — `5c88dc36fa8e5948160b257d3cd1ba0dd9ed675e`

The candidate is a one-path commit with parent `fe95edc68696531ae9b001e0e8f57e815d25d0a3`, 6,200 lines, 18 ordered tasks, 110 checkboxes and 288 balanced fence lines. Its artifact SHA-256 is `46da62efdaf73820cece759bc339f955196431b6b96a3331aacac6c3bc6043b5`. `git show --check` passes, the index is empty, and the protected 16-entry state remains SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.

Three independent read-only reviews plus Control Room verification found two Critical and thirteen Important blockers.

### Critical findings

1. **Browser provisioning children do not verify the PDO they mutate.** The parent validates the candidate, but the separate migration and seeding Laravel processes explicitly bypass the live guard during `provisioning`. Endpoint substitution after acquisition or between those children can reach mutation first. Guard the exact `e2e` PDO at every provisioning application boot and add both destructive-interval substitution/canary tests.
2. **The PostgreSQL qualification suite verifies a different, earlier connection.** Bootstrap validates one PDO, then Laravel opens its default connection and Feature/`RefreshDatabase` work may mutate before the late environment test. Add an explicit qualification phase and guard the actual default PDO during every Laravel application boot before mutation, with an endpoint-substitution test between bootstrap and the first Feature test.

### Important findings

1. Replace Task 0's loose authority text search with a closed machine-readable control record bound by exact commit/content hash; validate every approval and execution value independently.
2. Correct Task 4's double-escaped forbidden-operation regex so the expected no-match exit is `1`, not a regex-error exit `2`.
3. Make PostgreSQL bootstrap use a closed allowlist of the exact URL transport options used by Laravel, including supported `sslmode`, and reject unknown options.
4. Add the required valid-PostgreSQL-16/`_test` wrong-live-nonce negative case before PHPUnit can migrate.
5. Parse the unique top-level Node TAP plan line instead of assuming `1..N` is the final line after reporter diagnostics.
6. Add `P00_RUNNER_CLASS` to the approved input interface and set exact local/CI values in every canonical command.
7. Validate the generated root `bundle.json`; the current evidence validator incorrectly reads `frontend/bundle.json`.
8. Read Task 17's PostgreSQL observation from `postgresql-16/postgresql-identity.json`, matching the writer.
9. Cross-bind every CI job's SHA, portable input fingerprint and complete portable inputs to its run/aggregate, not only its platform observation.
10. Apply closed top-level schemas/exact-key checks and whole-value secret scanning to every loaded local/CI sibling payload, not only the manifest.
11. Build and validate evidence in a sibling temporary directory, then atomically rename it; failure must not leave a partial canonical directory.
12. After the evidence commit, prove `EVIDENCE_SHA^ == CODE_SHA` and verify exactly the eight evidence paths changed.
13. Compare only portable PostgreSQL identity/policy across distinct CI runs. Allow separately bound per-run attestation, nonce, endpoint and database observations for provider-neutral ephemeral services, and add a different-instance passing test.

### Required correction boundary

Use one fresh short writer session. It may change only `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md`, may correct only the findings above and the matching proposed safety-erratum references, and must not expand P00 scope. It must commit only that plan and stop. One final parallel exact-commit review follows.

## Resolution

Open. Awaiting one narrow final corrected plan commit and independent re-review. No implementation, MediaUrl preservation, worktree, dependency, CI, P01 or release action is authorized.

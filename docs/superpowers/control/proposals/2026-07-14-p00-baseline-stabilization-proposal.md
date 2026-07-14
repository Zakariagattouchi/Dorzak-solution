The proposed P00 design is ready. I recommend a serialized, contract-first baseline program: restore trustworthy existing behavior first, then add hermetic test/tooling/CI gates, and finish with documentation and clean-checkout evidence.

The Control Register was refreshed at `267704e1c58dd9cff9aa90b4f69375fc8b0cf292`. Authorization remains read-only.

## Observed baseline

- Current branch/HEAD: `feat/premium-features` at `267704e`.
- The registered 16-entry dirty-state manifest still matches exactly: `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`.
- Nothing is staged.
- [MediaUrl.php](</Users/barsha/Documents/recover Kyte/backend/app/Support/MediaUrl.php:23>) has only a formatting-only user change—no behavioral difference.
- A second linked worktree is dirty, 50 commits behind, and modifies `DemoSeeder.php` plus the two failing tests. It must be preserved and never repurposed for P00.
- Fresh Laravel run: 443 tests, 441 pass, two fail:
  - [CommerceImprovementsTest.php:76](</Users/barsha/Documents/recover Kyte/backend/tests/Feature/Commerce/CommerceImprovementsTest.php:76>) expects an absolute media URL; runtime returns `/storage/...`.
  - [DemoSeederParityTest.php:34](</Users/barsha/Documents/recover Kyte/backend/tests/Feature/DemoSeederParityTest.php:34>) expects USD; [DemoSeeder.php:42](</Users/barsha/Documents/recover Kyte/backend/database/seeders/DemoSeeder.php:42>) explicitly creates a Qatar/QAR store.
- The last recorded Playwright run is 0/7. Source and artifacts prove all seven initially reach the login page because [playwright.config.ts](</Users/barsha/Documents/recover Kyte/playwright.config.ts:20>) starts only Vite and supplies no backend, seeded database, or authenticated state.
- Several Playwright assertions are also stale behind that first failure: old navigation coverage, ambiguous Add Product selector, USD pricing, retired settings-localStorage behavior, and shared mutable localization state.
- Fresh TypeScript/production build passes: Vite reports 778.88 kB minified and 216.70 kB gzip, with one large-chunk warning.
- Fresh Pint check reports 16 affected files.
- Composer strict validation passes.
- There is no frontend unit/component runner, lint or formatting command, PHP static-analysis configuration, PostgreSQL test lane, CI definition, runtime pin, or canonical git remote.
- PHPUnit uses in-memory SQLite. PostgreSQL concurrency is unproved.
- `CONTEXT.md`, ADRs, P00 evidence, and named runbooks are absent. [backend/README.md](</Users/barsha/Documents/recover Kyte/backend/README.md:24>) still claims 179 tests and Pint clean.

## Approach comparison

| Approach | Benefit | Problem |
|---|---|---|
| Patch the two assertions and add one CI job | Fastest apparent green | Leaves browser fixtures, PostgreSQL behavior, lint/static debt, reproducibility, and stale documentation untrusted |
| **Layered contract-first baseline** | **Each failure has one owner; gates become diagnostic and reproducible** | Requires several serialized slices before feature work resumes |
| Containerize everything first | Maximum environment uniformity | Introduces unnecessary infrastructure scope before current contracts and tests are trustworthy |

Recommendation: layered contract-first baseline. Use container/service isolation where it materially matters—especially PostgreSQL and CI—without making a full local container platform a P00 prerequisite.

## Proposed contracts

### Media URL

For public media DTOs:

- `null` or empty input → `null`.
- Existing `http://` or `https://` URL → unchanged.
- A local storage-disk key → origin-relative `/storage/<key>`.
- Callers must supply a disk-relative key, not an already-public `/storage/...` value.
- Every supported web origin must explicitly proxy or serve `/storage`; this becomes a deployment/runbook acceptance check.

This matches the committed implementation, Vite proxy, and cache portability. It avoids embedding an environment-specific `APP_URL` in cached API payloads.

### Demo currency

- The canonical baseline demo tenant is Qatar/QAR.
- Orders created for that tenant snapshot QAR.
- Legacy USD values in `mockData.ts` are stale fixture data, not authority.
- P00 does not redesign subscription billing currency. The product baseline already requires subscription currency to be independent of merchant selling currency; that model change remains P03 scope.
- Playwright receives a dedicated, coherent Qatar/QAR E2E fixture rather than depending on the hybrid, non-resetting `DemoSeeder`.

## Ordering and task boundaries

One writer stream, serialized:

1. **Preservation preflight**
   - Record both worktrees, HEADs, statuses, manifests, and the exact MediaUrl patch.
   - Establish the approved integration base.
   - Create a new clean P00 worktree only after authorization.

2. **Baseline contracts**
   - Add characterization tests for the approved MediaUrl contract.
   - Pin the Qatar/QAR fixture contract.
   - Reach 443/443 without unrelated application changes.

3. **Deterministic full-stack browser lane**
   - Add an explicitly guarded E2E database/environment.
   - Reset, migrate, and seed deterministic data before each suite.
   - Start Laravel and Vite.
   - Authenticate once through a Playwright setup project and save `storageState`; retain a separate real login smoke test.
   - Run with one worker and zero retries initially. Parallelism requires per-worker tenant isolation later.
   - Repair all seven journeys to current behavior and semantic selectors.

4. **Frontend quality lane**
   - Add Vitest, React Testing Library, accessible component helpers, ESLint, and Prettier.
   - Cover at minimum auth/bootstrap behavior, DTO adapters, money/settings behavior, protected shell states, and one interactive accessible form.
   - Keep tests/configurations inside TypeScript checking.
   - Give `package.json` and `package-lock.json` one serialized owner.

5. **Backend quality and PostgreSQL lane**
   - Apply the 16-file Pint cleanup as a separate mechanical change.
   - Add Larastan/PHPStan at a reviewed achievable level with a versioned, non-increasing legacy baseline.
   - Run the complete Laravel suite on PostgreSQL 16.
   - Add process-level concurrency tests for order numbering/stock and representative wallet, loyalty, gift-card, or webhook locking. Synchronize with barriers, never sleeps.

6. **CI and performance baseline**
   - Keep canonical quality commands provider-neutral; CI is a thin wrapper.
   - Required jobs:
     - Composer validation
     - PHP style/static analysis
     - SQLite fast suite
     - PostgreSQL full/concurrency suite
     - Frontend format/lint/type/unit/build/bundle
     - Full-stack Playwright
     - Required-gates aggregator
   - Pin runtimes, lockfile install modes, PostgreSQL image, and Playwright browser.
   - Record the current merchant-shell baseline. Under a pinned Node/zlib measurement, initial JavaScript gzip must not exceed 216,700 bytes.
   - Preserve the existing large-chunk warning as explicit debt; broad route splitting is not required for P00 unless the no-growth gate fails.

7. **Context, ADRs, runbooks, and evidence**
   - Create `CONTEXT.md`.
   - Initial ADR set:
     - `0001-system-of-record-authority.md`
     - `0002-organization-location-and-isolated-erpnext-tenancy.md`
     - `0003-modular-monolith-and-external-adapters.md`
     - `0004-one-complete-public-launch.md`
     - `0005-immutable-plan-publication.md`
     - `0006-commerce-cutover-and-no-dual-write.md`
     - `0007-frontend-surface-boundaries.md`
   - The frontend ADR records Next.js as deferred pending the P05 measured spike, not as a completed P00 selection.
   - Update `RUN.md`, repository setup guidance, and `backend/README.md` only after commands are verified.
   - Record sanitized evidence under `docs/superpowers/evidence/p00/`.

8. **Final verification**
   - Independent review against a named clean `CODE_SHA`.
   - Run the entire matrix from a fresh checkout.
   - Repeat required CI on the integrated SHA and record code, evidence, and integrated SHAs separately.

## Dirty-state and recovery rules

- Preserve the MediaUrl diff independently; do not let Pint silently absorb it.
- Recommended preservation: a dedicated owner-approved commit before selecting the clean execution base. Alternative: export and apply the exact reviewed patch.
- Never reuse the stale Claude worktree.
- Never use `git add -A`; stage only the approved allowlist.
- Any unexpected dirty-state divergence stops execution without reset, stash, or deletion.
- A destructive E2E reset must abort unless `APP_ENV=e2e` and the database identity passes an explicit safety guard.
- CI retries cannot convert a flaky first failure into green evidence.
- A new product/authority contradiction returns to the Control Room.
- A PostgreSQL-discovered bug may be fixed within P00 only when it preserves an approved current contract; otherwise it requires a new decision.
- Bundle/static-analysis baselines may decrease automatically but may increase only through an explicit reviewed baseline revision.

## Measurable P00 exit gate

P00 closes only when:

- The approved integration worktree is clean.
- All 443 existing Laravel tests and all added tests pass on SQLite.
- The complete backend suite plus concurrency tests pass on PostgreSQL 16.
- All seven existing Playwright journeys and all added browser smoke tests pass with Laravel/data/auth fixtures started by the test harness.
- Playwright has zero retries, quarantines, or unexplained skips.
- Composer validation, Pint, Larastan/PHPStan, frontend formatting, lint, TypeScript, unit/component tests, and production build all exit zero.
- Static-analysis debt is versioned and cannot increase.
- Initial JavaScript remains at or below the canonical 216,700-byte gzip baseline.
- Two clean CI executions of the same integrated SHA pass, and the remote marks the aggregate gate required.
- `CONTEXT.md`, all seven ADRs, accurate runbooks, and a sanitized evidence manifest exist.
- Evidence records commands, counts, versions, durations, lockfile hashes, PostgreSQL image, CI run IDs, bundle sizes, review result, and exact `BASE_SHA`, `CODE_SHA`, evidence hash, and `INTEGRATED_SHA`.

## Owner decisions required

1. Do you formally approve the complete-launch baseline at `cc4085c` and the technical roadmap introduced at `d518f92` with the `069f483` correction?

2. Do you approve the proposed contracts:
   - local media URLs are origin-relative `/storage/...`, while HTTP(S) URLs pass through;
   - the canonical demo/E2E tenant is Qatar/QAR;
   - subscription-currency redesign remains P03?

3. How should the MediaUrl whitespace-only user patch be preserved?
   - Recommended: separate approved commit.
   - Alternative: exact reviewed patch imported into the future clean worktree.

4. What canonical remote and CI provider should P00 target? GitHub Actions is the recommendation if a GitHub remote will be established. Production PHP and Node runtime pins also need confirmation; PostgreSQL 16 is already proposed.

5. Do you approve this P00 proposal for the Control Room to record before authorizing design writing?

Proposed future design artifact:

[2026-07-14-dorzak-p00-baseline-stabilization-design.md](</Users/barsha/Documents/recover Kyte/docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md>)

No implementation-plan path is proposed, and `writing-plans` was not used.

No repository-controlled file was changed, staged, committed, branched, or worktreed by this task. Verification build output was directed to `/tmp`; test scratch output was cleaned. The final checkout manifest remains exactly `a797825e…`. The next required gate is formal product-baseline and roadmap approval, followed by approval of this P00 proposal.

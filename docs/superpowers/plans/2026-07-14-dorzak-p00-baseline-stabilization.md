# Dorzak P00 Baseline Stabilization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a reproducible, reviewable P00 baseline in which the current Dorzak merchant application has explicit media and Qatar/QAR contracts, deterministic full-stack browser coverage, frontend and backend quality lanes, PostgreSQL 16 qualification, provider-neutral CI commands, architecture/runbook evidence, and a fail-closed final gate.

**Architecture:** Preserve the current React/Vite merchant surface and Laravel 13 modular monolith. Keep in-memory SQLite as the fast PHPUnit lane, add PostgreSQL 16 as the qualification database, and run Playwright against a create-only, per-run PostgreSQL database/role capability on an attested P00-only service. No browser-fixture step resets, drops, renames, unlinks, or reuses a database. Expose one provider-neutral quality dispatcher that a later owner-selected CI adapter wraps. P00 documents the future Organization/ERPNext authority boundaries but does not implement P01 or later roadmap work.

**Tech Stack:** PHP at the exact owner-approved production pin; Laravel 13.18.1; Composer 2; PHPUnit 12; Pint 1.29.3; Larastan 3.10; PHPStan 2.2; PostgreSQL 16; Node at the exact owner-approved production pin; npm lockfile v3; React 18; TypeScript 5; Vite 5; Vitest 2; Testing Library; axe-core; ESLint 8; Prettier 3; Playwright Chromium.

## Global Constraints

- This document is an implementation plan, not implementation or execution authority. Do not run Task 1 or any later task until Task 0 passes in full under a separate Control Room execution lease.
- Approved design authority is `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md` at commit `ea7b8258083231c6a9b7aa7c00d89009e29e696e`, SHA-256 `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`.
- Safety erratum gate: that approved design describes a destructive SQLite browser-fixture reset, while this corrected plan uses a create-only PostgreSQL capability because stock Laravel/SQLite cannot satisfy the registered pathname-race requirement. This correction lease does not authorize changing the design or treating the artifacts as identical. Before any P00 execution, the Control Room must durably record an exact design erratum covering the Task 4–5 create-only PostgreSQL contract, obtain exact owner approval of that erratum and this matching plan revision, and update the execution-entry record. Until then Task 0 must fail.
- The owner-approved product baseline is commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`, SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2`. The technical roadmap used for execution must be the later independently reviewed and exactly owner-approved amended artifact bound by `P00_APPROVED_ROADMAP_COMMIT` and `P00_APPROVED_ROADMAP_SHA256`; no self-referential future commit is hard-coded into this plan. The documentation-amendment lease is not execution authority.
- The mandatory serialized order is: preservation/entry preflight; runtime pins; baseline contracts; deterministic browser; frontend quality; backend/PHP quality; PostgreSQL; CI/performance; context/ADRs/runbooks/evidence; independent review and final verification.
- Never reuse the stale linked worktree. Never run `git add -A`, `git add .`, a broad checkout/reset/clean, or an unscoped formatter. Every task below has one staging allowlist and one focused commit.
- `backend/app/Support/MediaUrl.php` is protected user-owned state until a separate preservation lease has completed, verified, and evidenced its disposition. Task 2 normally adds tests and corrects the stale consumer assertion without staging that file. Its guarded application-code branch is permitted only if the approved clean `BASE_SHA` demonstrably lacks the approved method body.
- The original user checkout must retain its approved absolute path, branch, HEAD, worktree-list identity, 16-entry path/status manifest, reviewed MediaUrl full-index diff, preservation-artifact relationship, and owner-approved non-secret content manifest. Task 0 contains the complete verifier. Run Task 0's preservation and protected-state command blocks before Task 1 and after every focused commit; any mismatch stops without staging, reset, stash, clean, deletion, or speculative repair.
- After every focused commit, also require the approved execution branch, `P00_BASE_SHA` ancestry, an empty index, and a clean execution worktree:

  ```bash
  test "$(git branch --show-current)" = "$P00_EXECUTION_BRANCH"
  git merge-base --is-ancestor "$P00_BASE_SHA" HEAD
  test -z "$(git diff --cached --name-only)"
  test -z "$(git status --short --untracked-files=normal)"
  ```

- Formatting and dependency generation are mechanical writer boundaries. Behavior, formatting, dependency, CI-adapter, documentation, and final-evidence changes remain separate commits.
- PostgreSQL failures may be corrected only when an existing test proves the already-approved current contract. The case-insensitive search correction in Task 11 is the only statically established correction. Any other PostgreSQL behavior choice returns to the Control Room with the exact failing test and SQL/error evidence.
- P00 does not choose or implement subscription currency redesign, Next.js, ERPNext migration, Organization tenancy, a MediaUrl preservation method, an integration base, or an execution worktree. The owner has selected `origin` at `https://github.com/Zakariagattouchi/dorzak.git`, integration branch `main`, GitHub Actions, PHP `8.5.6`, Node `24.18.0`, npm `11.16.0`, and Composer `2.9.8`; this plan records their future use but does not add a remote, create a branch, configure GitHub, or execute CI. GitHub Pro has not been authorized: local execution may proceed after Task 0, but Task 17 provider mutation remains blocked until the owner separately authorizes and enables it while keeping the repository private.

## Execution Input Interface and Stop Rules

The future execution lease must bind the following shell variables to literal values copied from one durable Control Room execution-entry record. Empty values, ranges for runtime versions, mutable container tags, or inferred values fail Task 0.

| Variable | Exact meaning |
|---|---|
| `P00_CONTROL_RECORD` / `P00_CONTROL_RECORD_COMMIT` / `P00_CONTROL_RECORD_SHA256` | Repository-relative JSON execution-entry record, full commit containing those exact bytes, and their 64-hex SHA-256 |
| `P00_SAFETY_ERRATUM_COMMIT` / `P00_SAFETY_ERRATUM_SHA256` | Full commit containing the exactly approved safety erratum and the erratum file's 64-hex SHA-256 |
| `P00_APPROVED_PLAN_COMMIT` | Full commit containing the independently reviewed, exactly owner-approved, provider-complete plan revision |
| `P00_APPROVED_PLAN_SHA256` | SHA-256 of this separately reviewed, owner-approved plan |
| `P00_APPROVED_ROADMAP_COMMIT` / `P00_APPROVED_ROADMAP_SHA256` | Full commit containing the independently reviewed, exactly owner-approved amended technical roadmap and its 64-hex SHA-256 |
| `P00_REMOTE_NAME` / `P00_REMOTE_URL` | Owner-approved canonical Git remote and its exact URL |
| `P00_INTEGRATION_BRANCH` | Owner-approved canonical integration branch; exactly `main` |
| `P00_CI_PROVIDER` | Owner-approved provider identifier |
| `P00_PHP_VERSION` / `P00_NODE_VERSION` | Exact production versions, each with three numeric components |
| `P00_COMPOSER_VERSION` / `P00_NPM_VERSION` | Exact approved Composer and npm versions, each with three numeric components |
| `P00_MEDIA_METHOD` | Exactly `dedicated_commit` or `reviewed_patch` |
| `P00_MEDIA_REVIEWED_DIFF_SHA256` | Hash of the reviewed full-index MediaUrl diff |
| `P00_MEDIA_ARTIFACT_ID` | Preserved commit SHA or durable patch SHA-256 |
| `P00_MEDIA_ARTIFACT_PATH` | Empty for a commit; approved durable path for a patch |
| `P00_MEDIA_VERIFICATION_RESULT` | Exactly `verified` after equality to the reviewed diff was proved |
| `P00_BASE_SHA` | Owner-approved 40-character clean integration base |
| `P00_EXECUTION_WORKTREE` / `P00_EXECUTION_BRANCH` | Owner-approved new worktree absolute path and exact isolated branch `codex/launch-baseline-v1` |
| `P00_USER_WORKTREE` | Original user checkout whose 16-entry manifest is protected |
| `P00_USER_BRANCH` / `P00_USER_HEAD` | Exact protected checkout branch and 40-character HEAD captured before preservation/execution |
| `P00_WORKTREE_IDENTITY_SHA256` | SHA-256 of sorted `worktree` and `branch` records from `git worktree list --porcelain` after the approved execution worktree is created |
| `P00_PROTECTED_STATUS_SHA256` | Registered sorted status hash; it must remain `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa` |
| `P00_PROTECTED_CONTENT_MANIFEST` / `P00_PROTECTED_CONTENT_MANIFEST_SHA256` | Durable owner-approved non-secret content manifest and its 64-hex file hash; unsafe-to-attest paths stop execution |
| `P00_PG_IDENTITY_KIND` | Exactly `oci` or `external-attestation` |
| `P00_PG_IDENTITY` | Immutable OCI reference with `@sha256:<64-hex>`, or approved external resource/revision/fingerprint identity |
| `P00_PG_ATTESTATION_PATH` / `P00_PG_ATTESTATION_SHA256` | Canonical absolute nonsymlink path to the sanitized immutable-service attestation and its 64-hex hash; never a mutable sentinel |
| `P00_PG_INSTANCE_NONCE_SHA256` | Approved 64-hex hash of the provisioner-created live service nonce exposed by `current_setting('dorzak.instance_nonce_sha256')` |
| `P00_E2E_SUPERVISOR_DB_URL` | Secret supervisor URL for the dedicated, no-real-data P00 E2E service; never committed, printed, or embedded in evidence |
| `P00_E2E_SERVICE_LIFECYCLE_ID` | Exact immutable provisioner/container lifecycle identifier from the attestation; cleanup may address only this value |
| `P00_E2E_SERVICE_ATTESTATION_PATH` / `P00_E2E_SERVICE_ATTESTATION_SHA256` | Canonical absolute nonsymlink path and hash for the sanitized E2E-service attestation proving PG16, immutable image/resource identity, no real data, isolated credential issuance, noncandidate access denial, and the lifecycle ID |
| `P00_FRESH_CHECKOUT` | New absolute path used only by final fresh-checkout verification |
| `P00_RUNNER_ROLE` | Exactly `local` for fresh local commands or `ci` for provider jobs |
| `P00_RUNNER_CLASS` | Exact current runner-class literal bound by the control record: its `runnerClasses.local` value for fresh local commands or `runnerClasses.ci` value for every provider job |

Guarded decisions are not substitute values. Task 14 below is the complete GitHub Actions adapter plan selected by the owner; it does not create the named files or change provider state. Its exact amended bytes still require independent zero-Critical/Important review, exact owner approval, and a later Control Room execution lease before any task runs.

## File Responsibility Map

| Serialized owner | Files and responsibility | May start after |
|---|---|---|
| Control Room / preservation lease | Execution record; MediaUrl preserved commit or patch evidence; canonical remote/provider/pins/base/worktree decisions | Plan approval |
| Runtime owner | `.php-version`, `.node-version` | Task 0 |
| Contract owner | `backend/tests/Unit/Support/MediaUrlTest.php`, `backend/tests/Feature/Commerce/CommerceImprovementsTest.php`, `backend/tests/Feature/DemoSeederParityTest.php`; conditional `MediaUrl.php` branch only under Task 2's guard | Task 1 |
| Browser fixture owner | `E2eDatabaseLease.php`, `ServeE2e.php`, `E2ESeeder.php`, the `e2e` database connection and boot guard, and two E2E PHP tests | Task 3 |
| Browser harness/journey owner | `playwright.config.ts`, `vite.config.ts`, `tests/e2e/**`, `TextInput.tsx`, `SelectInput.tsx`, `POSPage.tsx` | Task 4 |
| Frontend manifest owner | Task 6 owns `package.json`, `package-lock.json`, `tsconfig.json`, Vitest/ESLint/Prettier config, and test setup; Task 8 is the sole later mechanical formatter and must leave both npm manifests byte-identical | Task 5 |
| Frontend behavior owner | Focused unit/component tests and production seams explicitly named in Tasks 7–8 | Task 6 |
| PHP style owner | Exactly the 16 paths enumerated in Task 9; never MediaUrl | Task 8 |
| Composer/static owner | `backend/composer.json`, `backend/composer.lock`, `backend/phpstan.neon.dist`, `backend/phpstan-baseline.neon` | Task 9 |
| PostgreSQL owner | `backend/phpunit.pgsql.xml`, qualification parser/guard and its `AppServiceProvider` boot hook, PostgreSQL bootstrap, portable search models, process barrier/worker/tests | Task 10 |
| Quality-interface owner | `.gitignore`, `scripts/quality/**`; package/composer scripts were reserved earlier and are not reopened | Task 12 |
| CI adapter owner | One provider-native adapter and required-status configuration, only after the Task 14 amendment | Task 13 |
| Architecture-doc owner | `CONTEXT.md`, exactly seven `docs/adr/*.md` files | Task 13 |
| Runbook owner | `README.md`, `RUN.md`, `backend/README.md`, PostgreSQL wording in `backend/.env.example` | Task 15 |
| Evidence owner | `docs/superpowers/evidence/p00/**`; no application or config files | Tasks 14 and 16 |

Shared manifests, lockfiles, PHPStan baseline, Playwright configuration, provider adapter, and evidence manifest each have one semantic owner and are never edited by parallel tasks. Task 8 is the sole serialized mechanical formatter for its explicit frontend allowlist. Tasks are sequential even when independent review is delegated.

---

### Task 0: Pass the non-writing execution-entry and preservation gate

**Files:**
- Read: `$P00_CONTROL_RECORD`
- Read: `$P00_PROTECTED_CONTENT_MANIFEST`
- Read: `$P00_PG_ATTESTATION_PATH`
- Read: `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md:401`
- Read: the approved plan at this path
- Modify: none

**Interface:** This gate consumes the variables defined above and emits only `P00_EXECUTION_GATE PASS base=$P00_BASE_SHA worktree=$P00_EXECUTION_WORKTREE`. It never creates a branch, worktree, patch, commit, or repository file. The provider-complete decision in Task 14 is necessary but not sufficient: these exact amended bytes must be independently re-reviewed, exactly owner-approved, and bound by the later execution record before this gate can pass.

- [ ] **Verify formal authorities, not the planning exception.**

  The durable record is closed JSON. Its five approval objects separately approve the exact safety erratum, matching plan, product baseline, roadmap, and P00 execution; its closed `execution` object binds every non-secret execution input and the exact local/CI runner-class literals. The two credential-bearing database URLs are never stored in the record and are instead validated on their live PDOs in Tasks 4 and 11. Bind the record itself to an exact commit and content hash, then validate every approval and execution value independently:

  ```bash
  plan=docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md
  roadmap=docs/superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md
  erratum=docs/superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md
  test -f "$P00_CONTROL_RECORD"
  printf '%s\n' "$P00_CONTROL_RECORD_COMMIT" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_CONTROL_RECORD_SHA256" | rg -x '[0-9a-f]{64}'
  test "$(git show "$P00_CONTROL_RECORD_COMMIT:$P00_CONTROL_RECORD" | shasum -a 256 | awk '{print $1}')" = "$P00_CONTROL_RECORD_SHA256"
  test "$(shasum -a 256 "$P00_CONTROL_RECORD" | awk '{print $1}')" = "$P00_CONTROL_RECORD_SHA256"
  printf '%s\n' "$P00_APPROVED_PLAN_COMMIT" | rg -x '[0-9a-f]{40}'
  test "$(git log -1 --format=%H -- "$plan")" = "$P00_APPROVED_PLAN_COMMIT"
  test "$(shasum -a 256 "$plan" | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  test "$(git show "$P00_APPROVED_PLAN_COMMIT:$plan" | shasum -a 256 | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  printf '%s\n' "$P00_APPROVED_ROADMAP_COMMIT" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_APPROVED_ROADMAP_SHA256" | rg -x '[0-9a-f]{64}'
  test "$(git log -1 --format=%H -- "$roadmap")" = "$P00_APPROVED_ROADMAP_COMMIT"
  test "$(shasum -a 256 "$roadmap" | awk '{print $1}')" = "$P00_APPROVED_ROADMAP_SHA256"
  test "$(git show "$P00_APPROVED_ROADMAP_COMMIT:$roadmap" | shasum -a 256 | awk '{print $1}')" = "$P00_APPROVED_ROADMAP_SHA256"
  test "$(git show "$P00_SAFETY_ERRATUM_COMMIT:$erratum" | shasum -a 256 | awk '{print $1}')" = "$P00_SAFETY_ERRATUM_SHA256"
  test "$(shasum -a 256 "$erratum" | awk '{print $1}')" = "$P00_SAFETY_ERRATUM_SHA256"
  node --input-type=module - "$P00_CONTROL_RECORD" <<'NODE'
  import { readFileSync } from 'node:fs';
  import assert from 'node:assert/strict';
  const record = JSON.parse(readFileSync(process.argv[2], 'utf8'));
  const exactKeys = (value, keys) => assert.deepEqual(Object.keys(value).sort(), [...keys].sort());
  exactKeys(record, ['schemaVersion', 'approvals', 'execution']);
  assert.equal(record.schemaVersion, 2);
  exactKeys(record.approvals, ['safetyErratum', 'plan', 'productBaseline', 'roadmap', 'p00Execution']);
  const artifactApproval = (name, commit, sha256) => {
    const value = record.approvals[name];
    exactKeys(value, ['approved', 'commit', 'sha256']);
    assert.equal(value.approved, true);
    assert.equal(value.commit, commit);
    assert.equal(value.sha256, sha256);
  };
  artifactApproval('safetyErratum', process.env.P00_SAFETY_ERRATUM_COMMIT, process.env.P00_SAFETY_ERRATUM_SHA256);
  artifactApproval('plan', process.env.P00_APPROVED_PLAN_COMMIT, process.env.P00_APPROVED_PLAN_SHA256);
  artifactApproval('productBaseline', 'cc4085cbca11e89257ae8535438db6cfe3dd75cc', '7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2');
  artifactApproval('roadmap', process.env.P00_APPROVED_ROADMAP_COMMIT, process.env.P00_APPROVED_ROADMAP_SHA256);
  exactKeys(record.approvals.p00Execution, ['approved', 'planCommit', 'planSha256']);
  assert.equal(record.approvals.p00Execution.approved, true);
  assert.equal(record.approvals.p00Execution.planCommit, process.env.P00_APPROVED_PLAN_COMMIT);
  assert.equal(record.approvals.p00Execution.planSha256, process.env.P00_APPROVED_PLAN_SHA256);
  const bindings = {
    approvedPlanCommit: 'P00_APPROVED_PLAN_COMMIT', approvedPlanSha256: 'P00_APPROVED_PLAN_SHA256',
    remoteName: 'P00_REMOTE_NAME', remoteUrl: 'P00_REMOTE_URL', integrationBranch: 'P00_INTEGRATION_BRANCH', ciProvider: 'P00_CI_PROVIDER',
    phpVersion: 'P00_PHP_VERSION', nodeVersion: 'P00_NODE_VERSION', composerVersion: 'P00_COMPOSER_VERSION', npmVersion: 'P00_NPM_VERSION',
    mediaMethod: 'P00_MEDIA_METHOD', mediaReviewedDiffSha256: 'P00_MEDIA_REVIEWED_DIFF_SHA256', mediaArtifactId: 'P00_MEDIA_ARTIFACT_ID',
    mediaArtifactPath: 'P00_MEDIA_ARTIFACT_PATH', mediaVerificationResult: 'P00_MEDIA_VERIFICATION_RESULT', baseSha: 'P00_BASE_SHA',
    executionWorktree: 'P00_EXECUTION_WORKTREE', executionBranch: 'P00_EXECUTION_BRANCH', userWorktree: 'P00_USER_WORKTREE',
    userBranch: 'P00_USER_BRANCH', userHead: 'P00_USER_HEAD', worktreeIdentitySha256: 'P00_WORKTREE_IDENTITY_SHA256',
    protectedStatusSha256: 'P00_PROTECTED_STATUS_SHA256', protectedContentManifest: 'P00_PROTECTED_CONTENT_MANIFEST',
    protectedContentManifestSha256: 'P00_PROTECTED_CONTENT_MANIFEST_SHA256', pgIdentityKind: 'P00_PG_IDENTITY_KIND',
    pgIdentity: 'P00_PG_IDENTITY', pgAttestationPath: 'P00_PG_ATTESTATION_PATH', pgAttestationSha256: 'P00_PG_ATTESTATION_SHA256',
    pgInstanceNonceSha256: 'P00_PG_INSTANCE_NONCE_SHA256', e2eServiceLifecycleId: 'P00_E2E_SERVICE_LIFECYCLE_ID',
    e2eServiceAttestationPath: 'P00_E2E_SERVICE_ATTESTATION_PATH', e2eServiceAttestationSha256: 'P00_E2E_SERVICE_ATTESTATION_SHA256',
    freshCheckout: 'P00_FRESH_CHECKOUT',
  };
  exactKeys(record.execution, [...Object.keys(bindings), 'runnerClasses', 'mediaReviewedPatchVerification']);
  for (const [key, environment] of Object.entries(bindings)) {
    assert.equal(typeof process.env[environment], 'string', environment + ' is absent');
    assert.equal(record.execution[key], process.env[environment], key + ' does not match its independent binding');
  }
  exactKeys(record.execution.runnerClasses, ['local', 'ci']);
  for (const value of Object.values(record.execution.runnerClasses)) assert.match(value, /^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/);
  assert.notEqual(record.execution.runnerClasses.local, record.execution.runnerClasses.ci);
  assert.match(process.env.P00_RUNNER_ROLE || '', /^(local|ci)$/);
  assert.equal(
    process.env.P00_RUNNER_CLASS,
    record.execution.runnerClasses[process.env.P00_RUNNER_ROLE],
  );
  const mediaVerification = record.execution.mediaReviewedPatchVerification;
  if (record.execution.mediaMethod === 'reviewed_patch') {
    assert.ok(mediaVerification !== null && typeof mediaVerification === 'object' && !Array.isArray(mediaVerification));
    exactKeys(mediaVerification, [
      'checkoutKind', 'checkoutHead', 'checkoutCleanBeforeApply',
      'artifactSha256', 'applyExitCode', 'resultingDiffSha256',
    ]);
    assert.equal(mediaVerification.checkoutKind, 'disposable-verification-checkout');
    assert.equal(mediaVerification.checkoutHead, record.execution.userHead);
    assert.match(mediaVerification.checkoutHead, /^[0-9a-f]{40}$/);
    assert.equal(mediaVerification.checkoutCleanBeforeApply, true);
    assert.equal(mediaVerification.artifactSha256, record.execution.mediaArtifactId);
    assert.equal(mediaVerification.artifactSha256, record.execution.mediaReviewedDiffSha256);
    assert.match(mediaVerification.artifactSha256, /^[0-9a-f]{64}$/);
    assert.equal(mediaVerification.applyExitCode, 0);
    assert.equal(mediaVerification.resultingDiffSha256, record.execution.mediaReviewedDiffSha256);
    assert.equal(record.execution.mediaVerificationResult, 'verified');
  } else {
    assert.equal(record.execution.mediaMethod, 'dedicated_commit');
    assert.equal(mediaVerification, null);
  }
  NODE
  set +e
  node --input-type=module - "$plan" "$P00_CONTROL_RECORD" <<'NODE'
  import assert from 'node:assert/strict';
  import { createHash } from 'node:crypto';
  import { readFileSync } from 'node:fs';
  const text = readFileSync(process.argv[2], 'utf8');
  const control = JSON.parse(readFileSync(process.argv[3], 'utf8'));
  const section = text.match(/^### Task 14:[\s\S]*?(?=^### Task 15:)/m)?.[0];
  assert.ok(section);
  const records = [...section.matchAll(/```json\n([\s\S]*?)\n```/g)]
    .map((match) => JSON.parse(match[1]))
    .filter((value) => value?.schemaVersion === 1 && 'adapterPaths' in value && 'state' in value);
  assert.equal(records.length, 1);
  const value = records[0];
  const keys = ['adapterPaths','ciRunnerClass','credentialCapability','immutableDependencyIdentities','localRunnerClass','normalizerSha256','normalizerTestSha256','provider','providerCapability','pushAndRunCommandsSha256','repositoryVisibility','requiredStatus','requiredStatusCommandsSha256','schemaVersion','state','twoRunCommandsSha256','workflowIdentityEnforcement','workflowIdentityLimitation'];
  const adapterPaths = [
    '.github/workflows/p00-quality.yml',
    'scripts/quality/github-actions-postgres-service',
    'scripts/quality/test-github-actions-postgres-service.sh',
    'scripts/quality/github-actions-normalize.mjs',
    'scripts/quality/github-actions-normalize.test.mjs',
  ];
  const immutableDependencyIdentities = [
    'actions/checkout@9c091bb21b7c1c1d1991bb908d89e4e9dddfe3e0',
    'actions/setup-node@820762786026740c76f36085b0efc47a31fe5020',
    'docker.io/kirschbaumdevelopment/laravel-test-runner@sha256:571f0ea5098c32a534eeca499708d4edeb2f8874a76c32ef546a523a311660c6',
    'https://getcomposer.org/download/2.9.8/composer.phar#sha256=59b2c50e10cafa0d8efc19ede9a326d782f096c674a26baf98cf042ce23de890',
    'actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a',
    'actions/download-artifact@3e5f45b2cfb9172054b4087a40e8e0b5a5461e7c',
    'docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28',
  ];
  const blockSha256 = (marker, language) => {
    const pattern = new RegExp(
      '<!-- ' + marker + '_BEGIN -->\\n  ~~~' + language + '\\n([\\s\\S]*?)\\n  ~~~\\n  <!-- ' + marker + '_END -->',
      'g',
    );
    const matches = [...text.matchAll(pattern)];
    assert.equal(matches.length, 1, marker + ' must occur exactly once');
    const canonical = matches[0][1].split('\n').map((line) => {
      if (line === '') return '';
      assert.ok(line.startsWith('  '), marker + ' has an unindented non-empty line');
      return line.slice(2);
    }).join('\n') + '\n';
    return createHash('sha256').update(canonical).digest('hex');
  };
  const blockHashes = {
    normalizerSha256: blockSha256('TASK14_NORMALIZER', 'js'),
    normalizerTestSha256: blockSha256('TASK14_NORMALIZER_TEST', 'js'),
    pushAndRunCommandsSha256: blockSha256('TASK14_PUSH_AND_RUN_COMMANDS', 'bash'),
    requiredStatusCommandsSha256: blockSha256('TASK14_REQUIRED_STATUS_COMMANDS', 'bash'),
    twoRunCommandsSha256: blockSha256('TASK14_TWO_RUN_COMMANDS', 'bash'),
  };
  const validate = (candidate) => {
    assert.deepEqual(Object.keys(candidate).sort(), [...keys].sort());
    assert.equal(candidate.schemaVersion, 1);
    assert.equal(candidate.state, 'approved');
    assert.equal(candidate.provider, 'github-actions');
    assert.equal(candidate.credentialCapability, 'github-classic-oauth-workflow-scope-required');
    assert.equal(candidate.providerCapability, 'github-pro-private-branch-protection-required');
    assert.equal(candidate.repositoryVisibility, 'private');
    assert.equal(candidate.requiredStatus, 'required-gates');
    assert.equal(candidate.workflowIdentityEnforcement, 'control-room-exact-byte-review');
    assert.equal(candidate.workflowIdentityLimitation, 'classic-check-context-does-not-bind-workflow-path');
    assert.equal(candidate.localRunnerClass, 'local-macos-arm64');
    assert.equal(candidate.ciRunnerClass, 'github-hosted-ubuntu-24.04-x64');
    assert.equal(candidate.localRunnerClass, control.execution?.runnerClasses?.local);
    assert.equal(candidate.ciRunnerClass, control.execution?.runnerClasses?.ci);
    assert.deepEqual(candidate.adapterPaths, adapterPaths);
    assert.deepEqual(candidate.immutableDependencyIdentities, immutableDependencyIdentities);
    for (const [key, expected] of Object.entries(blockHashes)) {
      assert.match(candidate[key], /^[0-9a-f]{64}$/);
      assert.equal(candidate[key], expected, key + ' does not bind its canonical marked block');
    }
  };
  validate(value);
  const mutatedFixtures = [
    (candidate) => { candidate.state = 'pending'; },
    (candidate) => { candidate.provider = 'circleci'; },
    (candidate) => { candidate.adapterPaths.reverse(); },
    (candidate) => { candidate.immutableDependencyIdentities.pop(); },
    (candidate) => { candidate.normalizerSha256 = '0'.repeat(64); },
  ];
  for (const mutate of mutatedFixtures) {
    const candidate = JSON.parse(JSON.stringify(value));
    mutate(candidate);
    assert.throws(() => validate(candidate));
  }
  NODE
  ci_decision_status="$?"
  set -e
  test "$ci_decision_status" = 0
  ```

  Expected: every binding and closed-key check exits `0`; the record bytes match both their exact commit and SHA-256; all five approvals are independently true for their exact commit/hash; and every non-secret execution value equals its own record field. Text presence is never authority.

- [ ] **Validate decision-bound values.**

  ```bash
  case "$P00_MEDIA_METHOD" in dedicated_commit|reviewed_patch) ;; *) exit 1 ;; esac
  test "$P00_MEDIA_VERIFICATION_RESULT" = verified
  printf '%s\n' "$P00_BASE_SHA" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_MEDIA_REVIEWED_DIFF_SHA256" | rg -x '[0-9a-f]{64}'
  test "$P00_PHP_VERSION" = 8.5.6
  test "$P00_NODE_VERSION" = 24.18.0
  test "$P00_COMPOSER_VERSION" = 2.9.8
  test "$P00_NPM_VERSION" = 11.16.0
  test "$P00_REMOTE_NAME" = origin
  test "$P00_REMOTE_URL" = https://github.com/Zakariagattouchi/dorzak.git
  test "$P00_INTEGRATION_BRANCH" = main
  test "$P00_CI_PROVIDER" = github-actions
  test "$P00_EXECUTION_BRANCH" = codex/launch-baseline-v1
  test "$P00_RUNNER_ROLE" = local
  test "$P00_RUNNER_CLASS" = local-macos-arm64
  test "$P00_PROTECTED_STATUS_SHA256" = a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa
  printf '%s\n' "$P00_USER_HEAD" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_WORKTREE_IDENTITY_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PROTECTED_CONTENT_MANIFEST_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PG_ATTESTATION_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PG_INSTANCE_NONCE_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_E2E_SERVICE_ATTESTATION_SHA256" | rg -x '[0-9a-f]{64}'
  test -n "$P00_E2E_SERVICE_LIFECYCLE_ID"
  case "$P00_PG_IDENTITY_KIND" in
    oci) printf '%s\n' "$P00_PG_IDENTITY" | rg -x '[^[:space:]@]+@sha256:[0-9a-f]{64}' ;;
    external-attestation) printf '%s\n' "$P00_PG_IDENTITY" | rg -x 'external:[A-Za-z0-9._/-]+@[A-Za-z0-9._:-]+#sha256:[0-9a-f]{64}' ;;
    *) exit 1 ;;
  esac
  test -f "$P00_PG_ATTESTATION_PATH"
  test ! -L "$P00_PG_ATTESTATION_PATH"
  test "$(realpath "$P00_PG_ATTESTATION_PATH")" = "$P00_PG_ATTESTATION_PATH"
  test "$(shasum -a 256 "$P00_PG_ATTESTATION_PATH" | awk '{print $1}')" = "$P00_PG_ATTESTATION_SHA256"
  php -r '$a=json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR); if (($a["schemaVersion"] ?? null) !== 2 || ($a["kind"] ?? null) !== $argv[2] || ($a["identity"] ?? null) !== $argv[3] || ($a["serverMajor"] ?? null) !== 16 || ($a["immutable"] ?? null) !== true || ($a["instanceNonceSha256"] ?? null) !== $argv[4] || array_keys($a) !== ["schemaVersion","kind","identity","serverMajor","immutable","instanceNonceSha256"]) { exit(1); }' "$P00_PG_ATTESTATION_PATH" "$P00_PG_IDENTITY_KIND" "$P00_PG_IDENTITY" "$P00_PG_INSTANCE_NONCE_SHA256"
  test -f "$P00_E2E_SERVICE_ATTESTATION_PATH"
  test ! -L "$P00_E2E_SERVICE_ATTESTATION_PATH"
  test "$(realpath "$P00_E2E_SERVICE_ATTESTATION_PATH")" = "$P00_E2E_SERVICE_ATTESTATION_PATH"
  test "$(shasum -a 256 "$P00_E2E_SERVICE_ATTESTATION_PATH" | awk '{print $1}')" = "$P00_E2E_SERVICE_ATTESTATION_SHA256"
  php -r '$a=json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR); if (array_keys($a) !== ["schemaVersion","identity","serverMajor","immutable","instanceNonceSha256","lifecycleId","supervisorDatabase","supervisorRole","containsRealData","canIssueIsolatedCredentials","noncandidateAccessDenied"] || $a["schemaVersion"] !== 1 || $a["identity"] !== $argv[2] || $a["serverMajor"] !== 16 || $a["immutable"] !== true || $a["instanceNonceSha256"] !== $argv[3] || $a["lifecycleId"] !== $argv[4] || ! is_string($a["supervisorDatabase"]) || $a["supervisorDatabase"] === "" || ! is_string($a["supervisorRole"]) || $a["supervisorRole"] === "" || $a["containsRealData"] !== false || $a["canIssueIsolatedCredentials"] !== true || $a["noncandidateAccessDenied"] !== true) { exit(1); }' "$P00_E2E_SERVICE_ATTESTATION_PATH" "$P00_PG_IDENTITY" "$P00_PG_INSTANCE_NONCE_SHA256" "$P00_E2E_SERVICE_LIFECYCLE_ID"
  ```

  Expected: every command exits `0`. No semantic version range is accepted.

- [ ] **Verify the completed preservation artifact against the reviewed diff.**

  First prove that the protected checkout diff and the approved base both reproduce the reviewed full-index diff exactly:

  ```bash
  media=backend/app/Support/MediaUrl.php
  test "$(git -C "$P00_USER_WORKTREE" diff --binary --full-index "$P00_USER_HEAD" -- "$media" | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  test "$(git -C "$P00_EXECUTION_WORKTREE" diff --binary --full-index "$P00_USER_HEAD" "$P00_BASE_SHA" -- "$media" | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  ```

  For `dedicated_commit`:

  ```bash
  test -z "$P00_MEDIA_ARTIFACT_PATH"
  git -C "$P00_EXECUTION_WORKTREE" cat-file -e "$P00_MEDIA_ARTIFACT_ID^{commit}"
  test "$(git -C "$P00_EXECUTION_WORKTREE" diff --binary --full-index "$P00_MEDIA_ARTIFACT_ID^" "$P00_MEDIA_ARTIFACT_ID" -- "$media" | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  test "$(git -C "$P00_EXECUTION_WORKTREE" diff-tree --no-commit-id --name-only -r "$P00_MEDIA_ARTIFACT_ID")" = "$media"
  git -C "$P00_EXECUTION_WORKTREE" merge-base --is-ancestor "$P00_MEDIA_ARTIFACT_ID" "$P00_BASE_SHA"
  ```

  For `reviewed_patch`:

  ```bash
  test -f "$P00_MEDIA_ARTIFACT_PATH"
  test "$(shasum -a 256 "$P00_MEDIA_ARTIFACT_PATH" | awk '{print $1}')" = "$P00_MEDIA_ARTIFACT_ID"
  test "$P00_MEDIA_ARTIFACT_ID" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  ```

  Expected: the selected branch exits `0`. For `reviewed_patch`, the earlier schema-2 control-record validator proves the exact structured disposable-checkout application result and cross-binds its clean starting HEAD, artifact hash, zero exit status, and resulting diff hash; text presence is never authority. The patch is never applied to the named clean execution worktree. Control Room must commit, hash, and exactly reapprove the schema-2 record before execution.

- [ ] **Verify the protected checkout identity and every approved non-secret content digest.**

  ```bash
  test "$(realpath "$P00_USER_WORKTREE")" = "$P00_USER_WORKTREE"
  test "$(git -C "$P00_USER_WORKTREE" rev-parse --show-toplevel)" = "$P00_USER_WORKTREE"
  test "$(git -C "$P00_USER_WORKTREE" branch --show-current)" = "$P00_USER_BRANCH"
  test "$(git -C "$P00_USER_WORKTREE" rev-parse HEAD)" = "$P00_USER_HEAD"
  worktree_identity="$(git -C "$P00_USER_WORKTREE" worktree list --porcelain | awk '/^worktree / || /^branch /' | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')"
  test "$worktree_identity" = "$P00_WORKTREE_IDENTITY_SHA256"
  protected_status="$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort)"
  test "$(printf '%s\n' "$protected_status" | wc -l | tr -d ' ')" = 16
  test "$(printf '%s\n' "$protected_status" | shasum -a 256 | awk '{print $1}')" = "$P00_PROTECTED_STATUS_SHA256"
  test -f "$P00_PROTECTED_CONTENT_MANIFEST"
  test "$(shasum -a 256 "$P00_PROTECTED_CONTENT_MANIFEST" | awk '{print $1}')" = "$P00_PROTECTED_CONTENT_MANIFEST_SHA256"
  php -- "$P00_USER_WORKTREE" "$P00_PROTECTED_CONTENT_MANIFEST" <<'PHP'
  <?php

  declare(strict_types=1);

  [$script, $root, $manifestPath] = $argv;
  $fail = static function (string $message): never {
      fwrite(STDERR, "P00_PROTECTED_CONTENT FAIL {$message}\n");
      exit(2);
  };
  $git = static function (array $arguments) use ($root, $fail): string {
      $process = proc_open(
          array_merge(['git', '-C', $root], $arguments),
          [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
          $pipes,
      );
      if (! is_resource($process)) {
          $fail('git could not start');
      }
      $stdout = stream_get_contents($pipes[1]);
      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      if (proc_close($process) !== 0) {
          $fail('git failed: '.trim($stderr));
      }

      return $stdout;
  };
  $updateNode = static function ($context, string $absolute, string $relative) use (&$updateNode, $fail): void {
      $stat = @lstat($absolute);
      if (! is_array($stat)) {
          $fail("cannot inspect {$relative}");
      }
      $mode = $stat['mode'] & 0170000;
      hash_update($context, pack('N', strlen($relative)).$relative.pack('N', $stat['mode'] & 0777));
      if ($mode === 0100000) {
          hash_update($context, "F".pack('J', $stat['size']));
          $handle = @fopen($absolute, 'rb');
          if (! is_resource($handle)) {
              $fail("cannot read {$relative}");
          }
          while (! feof($handle)) {
              $chunk = fread($handle, 1048576);
              if ($chunk === false) {
                  $fail("read failed {$relative}");
              }
              hash_update($context, $chunk);
          }
          fclose($handle);
          return;
      }
      if ($mode === 0040000) {
          hash_update($context, 'D');
          $children = array_values(array_diff(scandir($absolute) ?: [], ['.', '..']));
          sort($children, SORT_STRING);
          foreach ($children as $child) {
              $updateNode($context, $absolute.'/'.$child, $relative.'/'.$child);
          }
          return;
      }
      if ($mode === 0120000) {
          $target = readlink($absolute);
          if ($target === false) {
              $fail("cannot read link {$relative}");
          }
          hash_update($context, 'L'.pack('N', strlen($target)).$target);
          return;
      }
      $fail("special file is not attestable: {$relative}");
  };

  $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
  if (($manifest['schemaVersion'] ?? null) !== 1 || ($manifest['userWorktree'] ?? null) !== $root) {
      $fail('manifest header mismatch');
  }
  $statusOutput = $git(['status', '--porcelain=v1', '-z', '--untracked-files=normal']);
  if ($statusOutput !== '' && ! str_ends_with($statusOutput, "\0")) {
      $fail('porcelain output is not NUL terminated');
  }
  $statusLines = $statusOutput === '' ? [] : explode("\0", substr($statusOutput, 0, -1));
  foreach ($statusLines as $statusLine) {
      if (strlen($statusLine) < 4 || $statusLine[2] !== ' '
          || in_array($statusLine[0], ['R', 'C'], true)
          || in_array($statusLine[1], ['R', 'C'], true)) {
          $fail('unsupported porcelain status record');
      }
  }
  sort($statusLines, SORT_STRING);
  if (count($statusLines) !== 16 || hash('sha256', implode("\n", $statusLines)."\n") !== ($manifest['statusSha256'] ?? null)) {
      $fail('status identity mismatch');
  }
  $entries = $manifest['entries'] ?? null;
  if (! is_array($entries) || count($entries) !== 16) {
      $fail('manifest must contain 16 entries');
  }
  $seen = [];
  foreach ($entries as $entry) {
      $status = $entry['status'] ?? '';
      $path = $entry['path'] ?? '';
      if (($entry['classification'] ?? null) !== 'owner-approved-nonsecret'
          || ! in_array($entry['kind'] ?? null, ['tracked-diff', 'untracked-tree'], true)
          || ! preg_match('/^[0-9a-f]{64}$/', $entry['sha256'] ?? '')
          || $path === '' || str_starts_with($path, '/') || preg_match('~(^|/)\.\.(/|$)~', $path)) {
          $fail('invalid or unsafe manifest entry');
      }
      $statusLine = $status.' '.$path;
      if (! in_array($statusLine, $statusLines, true) || isset($seen[$statusLine])) {
          $fail("status/path mismatch {$path}");
      }
      $seen[$statusLine] = true;
      if ($entry['kind'] === 'tracked-diff') {
          if ($status === '??') {
              $fail("tracked kind mismatch {$path}");
          }
          $actual = hash('sha256', $git(['diff', '--binary', '--full-index', 'HEAD', '--', $path]));
      } else {
          if ($status !== '??') {
              $fail("untracked kind mismatch {$path}");
          }
          $context = hash_init('sha256');
          $updateNode($context, $root.'/'.$path, $path);
          $actual = hash_final($context);
      }
      if (! hash_equals($entry['sha256'], $actual)) {
          $fail("content mismatch {$path}");
      }
  }
  if (count($seen) !== count($statusLines)) {
      $fail('manifest coverage mismatch');
  }
  fwrite(STDOUT, "P00_PROTECTED_CONTENT PASS entries=16\n");
  PHP

  php -r '$raw=" M tracked.php\0?? untracked\0"; $rows=explode("\0", substr($raw,0,-1)); sort($rows,SORT_STRING); if ($rows !== [" M tracked.php","?? untracked"]) exit(1);'
  ```

  The Control Room creates the JSON manifest before execution with exact `schemaVersion`, `userWorktree`, registered `statusSha256`, and 16 sorted entries. Each entry contains exact `status`, repository-relative `path`, `kind`, `classification=owner-approved-nonsecret`, and `sha256`. A path that cannot safely receive that classification stops execution; secret content is never hashed into committed evidence.

- [ ] **Verify remote, approved base, and the clean named execution worktree.**

  ```bash
  test "$(git -C "$P00_EXECUTION_WORKTREE" remote get-url "$P00_REMOTE_NAME")" = "$P00_REMOTE_URL"
  test "$(realpath "$P00_EXECUTION_WORKTREE")" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse HEAD)" = "$P00_BASE_SHA"
  test "$(git -C "$P00_EXECUTION_WORKTREE" branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" diff --cached --name-only)"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" status --short --untracked-files=normal)"
  git -C "$P00_EXECUTION_WORKTREE" worktree list --porcelain | rg -Fx "worktree $P00_EXECUTION_WORKTREE"
  ```

  Expected: every command exits `0`. If a stale worktree is named, a protected identity differs, a content entry is unsafe, or the approved Task 14 record or any canonical marked-block hash differs, stop.

- [ ] **Emit the sole success line and change nothing.**

  ```bash
  printf 'P00_EXECUTION_GATE PASS base=%s worktree=%s\n' "$P00_BASE_SHA" "$P00_EXECUTION_WORKTREE"
  ```

  Expected: one line with the approved literal base and path. There is no staging or commit in Task 0.

### Task 1: Record the exact approved runtime pins

**Files:**
- Create: `.php-version`
- Create: `.node-version`

**Interfaces:** Each file contains one exact `major.minor.patch` line. `php --version` and `node --version` must match before dependency resolution, bundle measurement, or quality evidence.

- [ ] **Prove the pin files are absent.**

  ```bash
  test -f .php-version && test -f .node-version
  ```

  Expected failure: exit `1` because neither file exists at the approved baseline.

- [ ] **Create the files with an exact patch.**

  ```diff
  *** Begin Patch
  *** Add File: .php-version
  +$P00_PHP_VERSION
  *** Add File: .node-version
  +$P00_NODE_VERSION
  *** End Patch
  ```

  In the patch, replace the two shell-variable expressions with the literal approved values from the Control Room record. Do not commit dollar-prefixed expressions.

- [ ] **Verify runtime identity before any lockfile change.**

  ```bash
  test "$(cat .php-version)" = "$P00_PHP_VERSION"
  test "$(cat .node-version)" = "$P00_NODE_VERSION"
  test "$(php -r 'echo PHP_VERSION;')" = "$P00_PHP_VERSION"
  test "$(node -p 'process.versions.node')" = "$P00_NODE_VERSION"
  ```

  Expected: four zero exits. A machine without the approved runtime stops here.

- [ ] **Stage only the two pins and commit.**

  ```bash
  git add -- .php-version .node-version
  test "$(git diff --cached --name-only | LC_ALL=C sort)" = "$(printf '%s\n' .node-version .php-version | LC_ALL=C sort)"
  git commit -m "build: pin approved P00 runtimes"
  ```

  Run the global writer-boundary checks.

### Task 2: Lock the public media URL contract without absorbing user work

**Files:**
- Create: `backend/tests/Unit/Support/MediaUrlTest.php`
- Modify: `backend/tests/Feature/Commerce/CommerceImprovementsTest.php:60-76`
- Conditional modify: `backend/app/Support/MediaUrl.php:10-23` only if the clean approved base fails the direct unit contract

**Interfaces:** `App\Support\MediaUrl::public(?string $path): ?string` returns `null` for null/empty, passes through HTTP(S), and maps a storage-disk-relative key to `/storage/<key>`. Callers must not supply an already-public URI. Every served frontend origin must route `/storage` to Laravel; `vite.config.ts:21-24` already does so.

- [ ] **Run the existing red seam before editing.**

  ```bash
  cd backend
  php artisan test tests/Feature/Commerce/CommerceImprovementsTest.php --filter=test_category_photo_upload_is_exposed_to_admin_and_public_catalog
  ```

  Expected failure: the test expects `http://localhost/storage/...`; the resource returns `/storage/...`.

- [ ] **Add the complete direct contract test.**

  ```php
  <?php

  namespace Tests\Unit\Support;

  use App\Support\MediaUrl;
  use PHPUnit\Framework\TestCase;

  final class MediaUrlTest extends TestCase
  {
      public function test_public_media_url_contract(): void
      {
          self::assertNull(MediaUrl::public(null));
          self::assertNull(MediaUrl::public(''));
          self::assertSame('http://cdn.example.test/a.jpg', MediaUrl::public('http://cdn.example.test/a.jpg'));
          self::assertSame('https://cdn.example.test/a.jpg', MediaUrl::public('https://cdn.example.test/a.jpg'));
          self::assertSame('/storage/categories/a.jpg', MediaUrl::public('categories/a.jpg'));
      }
  }
  ```

- [ ] **Correct only the stale feature assertion.**

  Replace `CommerceImprovementsTest.php:73-76` with:

  ```php
  $this->getJson('/api/public/stores/photo-shop/catalog')
      ->assertOk()
      ->assertJsonPath('data.categories.0.name', 'Desserts')
      ->assertJsonPath(
          'data.categories.0.image_url',
          Storage::disk('public')->url($path),
      );
  ```

- [ ] **Use the guarded application-code branch only when proved necessary.**

  Run the new unit test. If it passes, do not edit or stage `MediaUrl.php`. If it fails, verify the Control Room record preserved the reviewed user diff and then replace only the method body with this approved contract:

  ```php
  public static function public(?string $path): ?string
  {
      if (! $path) {
          return null;
      }

      if (Str::startsWith($path, ['http://', 'https://'])) {
          return $path;
      }

      return '/storage/'.ltrim($path, '/');
  }
  ```

  This branch is not permission to alter the contract or preservation evidence.

- [ ] **Verify both focused tests and the full SQLite lane.**

  ```bash
  cd backend
  php artisan test tests/Unit/Support/MediaUrlTest.php tests/Feature/Commerce/CommerceImprovementsTest.php
  php artisan test
  ```

  Expected: both focused files pass. The full suite contains 444 tests and intentionally exits nonzero with exactly `443 passed, 1 failed`; the sole failure is `Tests\\Feature\\DemoSeederParityTest::test_store_and_subscription`. Any other failure stops. Task 3 owns that already-characterized stale assertion, so Task 2 must not absorb it.

- [ ] **Stage the exact proven allowlist and commit.**

  Normal branch:

  ```bash
  git add -- backend/tests/Unit/Support/MediaUrlTest.php backend/tests/Feature/Commerce/CommerceImprovementsTest.php
  test -z "$(git diff --cached --name-only | rg -v '^(backend/tests/Unit/Support/MediaUrlTest\.php|backend/tests/Feature/Commerce/CommerceImprovementsTest\.php)$')"
  git commit -m "test: lock public media URL contract"
  ```

  Guarded branch adds `backend/app/Support/MediaUrl.php` to both the explicit `git add --` list and the regex. Run the global writer-boundary checks.

### Task 3: Lock the canonical Qatar/QAR demo contract

**Files:**
- Modify: `backend/tests/Feature/DemoSeederParityTest.php:31-60`
- Read: `backend/database/seeders/DemoSeeder.php:32-45`
- Read: `backend/app/Services/OrderService.php:104-113`

**Interfaces:** The demo store is country `Qatar`, currency `QAR`, symbol placement `BEFORE`; every seeded order snapshots `currency_code=QAR`. Subscription-currency redesign remains P03.

- [ ] **Run the exact failing test.**

  ```bash
  cd backend
  php artisan test tests/Feature/DemoSeederParityTest.php --filter=test_store_and_subscription
  ```

  Expected failure: expected `USD`, actual `QAR`.

- [ ] **Replace the complete stale store/subscription test and extend order parity.**

  Replace `test_store_and_subscription()` completely so the real `Plan.code` contract is exercised:

  ```php
  public function test_store_and_subscription(): void
  {
      $store = Store::firstWhere('name', 'Dorzak Merchant');
      $this->assertSame('QAR', $store->currency);
      $this->assertSame('8.50', (string) $store->tax_rate);
      $this->assertSame('PRO', $store->subscription->plan->code);
      $this->assertSame('dorzak-merchant', $store->storefrontSetting->slug);
  }
  ```

  Replace `test_three_orders_with_consistent_money()` with:

  ```php
  public function test_three_orders_with_consistent_money(): void
  {
      $this->assertSame(3, Order::count());

      foreach (Order::with('items')->get() as $order) {
          $this->assertSame(
              'QAR',
              $order->currency_code,
              "Order {$order->order_number} currency mismatch",
          );
          $subtotal = (float) $order->items->sum('line_total');
          $expectedTotal = round($subtotal - (float) $order->discount + (float) $order->tax_amount + (float) $order->delivery_fee, 2);
          $this->assertSame(number_format($expectedTotal, 2, '.', ''), (string) $order->total, "Order {$order->order_number} total mismatch");
      }
  }
  ```

- [ ] **Verify the focused file and full SQLite count.**

  ```bash
  cd backend
  php artisan test tests/Feature/DemoSeederParityTest.php
  php artisan test
  ```

  Expected: the parity file reports `4 passed`; full suite remains `444 passed`.

- [ ] **Stage only the parity test and commit.**

  ```bash
  git add -- backend/tests/Feature/DemoSeederParityTest.php
  test "$(git diff --cached --name-only)" = backend/tests/Feature/DemoSeederParityTest.php
  git commit -m "test: lock Qatar demo currency"
  ```

  Run the global writer-boundary checks.

### Task 4: Add a create-only, capability-isolated PostgreSQL E2E fixture

**Files:**
- Create: `backend/app/Support/E2eSupervisor.php`
- Create: `backend/app/Support/PdoE2eSupervisor.php`
- Create: `backend/app/Support/E2eDatabaseLease.php`
- Create: `backend/app/Support/E2eProvisioningException.php`
- Create: `backend/app/Support/PostgresConnectionProfile.php`
- Create: `backend/app/Console/Commands/ServeE2e.php`
- Create: `backend/database/seeders/E2ESeeder.php`
- Modify: `backend/config/database.php`
- Modify: `backend/app/Providers/AppServiceProvider.php`
- Create: `backend/tests/Feature/E2E/E2EProvisioningGuardTest.php`
- Create: `backend/tests/LivePostgres/E2ELiveMutationGuardTest.php`
- Create: `backend/tests/Feature/E2E/E2ESeederTest.php`
- Create: `backend/tests/Support/PostgresSubstitutionHarness.php`

**Interfaces:** Playwright uses `php artisan e2e:serve`, never `migrate:fresh`, `db:wipe`, a file SQLite database, or a stable database pointer. `App\Support\E2eDatabaseLease::acquire()` opens one already-attested supervisor connection to a dedicated no-real-data PostgreSQL 16 service, re-verifies its immutable identity through the live provisioner nonce, and creates a cryptographically unique database plus login role. It never reuses, resets, drops, renames, or unlinks anything. The role is `NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS`, can connect only to its candidate database, and owns no other database. `PostgresConnectionProfile` is the sole closed URL/configuration parser: it rejects malformed encoding, empty authority fields, fragments, duplicate/unknown options, control/whitespace/DSN-delimiter injection, read/write splitting and every query key except the six-value `sslmode`; Laravel's resolved fields are compared because Laravel removes `url`. The command runs only `artisan migrate --database=e2e --force`, seeds, then inserts the activation nonce and fixture-contract hash as the final mutation. Migration, seed, activation and serving each validate the exact PDO used in that phase; activation validates and mutates in one transaction on one PDO. Every guarded Laravel connection is sealed against PDO replacement and automatic reconnection before queries can run. Parent or earlier-connection checks never authorize a later PDO. A typed post-create failure always carries the nonsecret database, role and lifecycle orphan identity. Real PostgreSQL child/substitution/reconnect canaries, not mocks or prose, prove every mutation interval. Failure leaves every prior fixture/server and every noncandidate database unchanged. Cleanup addresses only the attested immutable service/container lifecycle ID; cleanup failure records this candidate as an orphan and never broadens deletion. If the approved service attestation cannot prove no real data and isolated credentials, Task 0 requires a new ephemeral digest-pinned PostgreSQL 16 service for that run.

- [ ] **Write the provisioning and fixture tests first.**

  `E2EProvisioningGuardTest.php`:

  ```php
  <?php

  namespace Tests\Feature\E2E;

  use App\Console\Commands\ServeE2e;
  use App\Support\E2eDatabaseLease;
  use App\Support\E2eSupervisor;
  use Closure;
  use RuntimeException;
  use Tests\TestCase;

  final class E2EProvisioningGuardTest extends TestCase
  {
      public function test_create_only_capability_refuses_substitution_and_never_resets_existing_state(): void
      {
          $attestation = FakeE2eSupervisor::attestation();
          $expected = [
              'environment' => 'e2e',
              'identity' => $attestation['identity'],
              'nonce' => $attestation['instanceNonceSha256'],
              'lifecycle' => $attestation['lifecycleId'],
              'contract' => str_repeat('c', 64),
          ];

          foreach ([
              ['environment', 'testing'],
              ['driver', 'sqlite'],
              ['identity', 'wrong-service'],
              ['serverMajor', 15],
              ['instanceNonceSha256', str_repeat('0', 64)],
              ['database', 'wrong-control'],
              ['role', 'wrong-supervisor'],
          ] as [$field, $value]) {
              $supervisor = new FakeE2eSupervisor();
              $input = $expected;
              if ($field === 'environment') {
                  $input['environment'] = $value;
              } else {
                  $supervisor->facts[$field] = $value;
              }
              $this->assertRefused(fn () => $this->acquire($supervisor, $attestation, $input));
              self::assertSame(0, $supervisor->createCalls, "wrong {$field} mutated the service");
          }

          $collision = new FakeE2eSupervisor();
          $collision->collision = true;
          $this->assertRefused(fn () => $this->acquire($collision, $attestation, $expected));
          self::assertSame(0, $collision->createCalls);

          $substitution = new FakeE2eSupervisor();
          $substitution->substituteServiceAfterCreate = true;
          $this->assertRefused(fn () => $this->acquire($substitution, $attestation, $expected));
          self::assertSame(['control' => 'unchanged', 'priorServer' => 'running'], $substitution->protectedState);
          self::assertFalse($substitution->activated);

          $first = new FakeE2eSupervisor();
          $second = new FakeE2eSupervisor();
          $leaseA = $this->acquire($first, $attestation, $expected, '11');
          $leaseB = $this->acquire($second, $attestation, $expected, '22');
          self::assertNotSame($leaseA->database(), $leaseB->database());
          self::assertSame([], $first->noncandidateDatabasesWithAccess($first->createdRole, $leaseA->database()));
          self::assertSame([], $second->noncandidateDatabasesWithAccess($second->createdRole, $leaseB->database()));

          foreach ([['migrate', '44'], ['db:seed', '55']] as [$failingCommand, $fill]) {
              $failureSupervisor = new FakeE2eSupervisor();
              $failureLease = $this->acquire($failureSupervisor, $attestation, $expected, $fill);
              $this->assertRefused(fn () => ServeE2e::prepare(
                  $failureLease,
                  function (array $command, array $environment) use ($failingCommand): void {
                      self::assertContains($environment['P00_E2E_PHASE'], ['provisioning-migrate', 'provisioning-seed']);
                      if (in_array($failingCommand, $command, true)) {
                          throw new RuntimeException($failingCommand.' failed');
                      }
                  },
              ));
              self::assertFalse($failureSupervisor->activated);
              self::assertSame(['control' => 'unchanged', 'priorServer' => 'running'], $failureSupervisor->protectedState);
              self::assertSame('unchanged', $failureSupervisor->noncandidateFingerprint());
          }

          $leaseC = $this->acquire(new FakeE2eSupervisor(), $attestation, $expected, '33');
          $successful = [];
          ServeE2e::prepare($leaseC, function (array $command, array $environment) use (&$successful): void {
              $successful[] = [$environment['P00_E2E_PHASE'], $command];
          });
          $leaseC->assertActive();
          self::assertSame([
              ['provisioning-migrate', [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction']],
              ['provisioning-seed', [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\\Seeders\\E2ESeeder', '--force', '--no-interaction']],
          ], $successful);
          self::assertStringNotContainsString(
              'migrate:fresh db:wipe drop unlink rename reset',
              implode(' ', array_merge(...array_column($successful, 1))),
          );

          $leaseC->supervisorForTest()->candidateSubstitution = true;
          $this->assertRefused(fn () => $leaseC->assertActive());
      }

      private function acquire(
          FakeE2eSupervisor $supervisor,
          array $attestation,
          array $expected,
          string $fill = 'aa',
      ): E2eDatabaseLease {
          $bytes = [
              hex2bin(str_repeat($fill, 16)),
              str_repeat('p', 32),
              str_repeat('n', 32),
          ];

          return E2eDatabaseLease::acquire(
              $expected['environment'],
              $supervisor,
              $attestation,
              $expected['identity'],
              $expected['nonce'],
              $expected['lifecycle'],
              $expected['contract'],
              static function (int $length) use (&$bytes): string {
                  $value = array_shift($bytes);
                  if (! is_string($value) || strlen($value) !== $length) {
                      throw new RuntimeException('test entropy contract mismatch');
                  }

                  return $value;
              },
          );
      }

      private function assertRefused(Closure $attempt): void
      {
          try {
              $attempt();
              self::fail('Unsafe E2E operation was accepted.');
          } catch (RuntimeException) {
              self::assertTrue(true);
          }
      }
  }

  final class FakeE2eSupervisor implements E2eSupervisor
  {
      public array $facts;
      public bool $collision = false;
      public bool $substituteServiceAfterCreate = false;
      public bool $candidateSubstitution = false;
      public bool $activated = false;
      public ?string $activationNonceSha256 = null;
      public ?string $fixtureContractSha256 = null;
      public int $createCalls = 0;
      public string $createdRole = '';
      public array $protectedState = ['control' => 'unchanged', 'priorServer' => 'running'];

      public function __construct()
      {
          $attestation = self::attestation();
          $this->facts = [
              'driver' => 'pgsql',
              'identity' => $attestation['identity'],
              'serverMajor' => 16,
              'instanceNonceSha256' => $attestation['instanceNonceSha256'],
              'database' => $attestation['supervisorDatabase'],
              'role' => $attestation['supervisorRole'],
          ];
      }

      public static function attestation(): array
      {
          return [
              'schemaVersion' => 1,
              'identity' => 'registry.example.test/postgres@sha256:'.str_repeat('a', 64),
              'serverMajor' => 16,
              'immutable' => true,
              'instanceNonceSha256' => str_repeat('b', 64),
              'lifecycleId' => 'container:p00-e2e-fixture-1',
              'supervisorDatabase' => 'postgres',
              'supervisorRole' => 'p00_supervisor',
              'containsRealData' => false,
              'canIssueIsolatedCredentials' => true,
              'noncandidateAccessDenied' => true,
          ];
      }

      public function facts(): array
      {
          if ($this->substituteServiceAfterCreate && $this->createCalls > 0) {
              return [...$this->facts, 'instanceNonceSha256' => str_repeat('f', 64)];
          }

          return $this->facts;
      }

      public function candidateExists(string $database, string $role): bool
      {
          return $this->collision;
      }

      public function noncandidateFingerprint(): string
      {
          return 'unchanged';
      }

      public function createCandidate(string $database, string $role, string $password): void
      {
          $this->createCalls++;
          $this->createdRole = $role;
      }

      public function candidateUrl(string $database, string $role, string $password): string
      {
          return "postgresql://{$role}:secret@fixture.test/{$database}";
      }

      public function noncandidateDatabasesWithAccess(string $role, string $candidate): array
      {
          return [];
      }

      public function candidateFacts(string $url): array
      {
          parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
          $database = basename((string) parse_url($url, PHP_URL_PATH));
          $role = (string) parse_url($url, PHP_URL_USER);

          return [
              'driver' => 'pgsql',
              'database' => $this->candidateSubstitution ? 'substituted' : $database,
              'role' => $role,
              'serverMajor' => 16,
              'instanceNonceSha256' => str_repeat('b', 64),
              'superuser' => false,
              'createdb' => false,
              'createrole' => false,
              'bypassrls' => false,
              'replication' => false,
              'activationNonceSha256' => $this->activated ? $this->activationNonceSha256 : null,
              'fixtureContractSha256' => $this->activated ? $this->fixtureContractSha256 : null,
          ];
      }

      public function activate(
          string $url,
          string $database,
          string $role,
          string $activationNonceSha256,
          string $fixtureContractSha256,
          string $serviceNonceSha256,
      ): void {
          $this->activated = true;
          $this->activationNonceSha256 = $activationNonceSha256;
          $this->fixtureContractSha256 = $fixtureContractSha256;
      }
  }
  ```

  `E2ESeederTest.php` remains one test and uses the real model contract:

  ```php
  <?php

  namespace Tests\Feature\E2E;

  use App\Models\Product;
  use App\Models\Store;
  use App\Models\User;
  use Database\Seeders\E2ESeeder;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Tests\TestCase;

  final class E2ESeederTest extends TestCase
  {
      use RefreshDatabase;

      public function test_fixture_is_one_repeatable_qatar_merchant(): void
      {
          $this->seed(E2ESeeder::class);

          $store = Store::sole();
          self::assertSame('Dorzak E2E Merchant', $store->name);
          self::assertSame('Qatar', $store->country);
          self::assertSame('QAR', $store->currency);
          self::assertSame('BEFORE', $store->symbol_placement);
          self::assertSame('PRO', $store->subscription->plan->code);
          self::assertSame('owner@e2e.dorzak.test', User::sole()->email);

          $product = Product::with('variants')->sole();
          self::assertSame('Dorzak Signature Cotton Hoodie', $product->name);
          self::assertSame('49.99', (string) $product->price);
          self::assertCount(1, $product->variants);
          self::assertSame(['size' => 'small', 'color' => 'black'], $product->variants->sole()->option_values);
      }
  }
  ```

- [ ] **Run the red tests.**

  ```bash
  cd backend
  php artisan test tests/Feature/E2E/E2EProvisioningGuardTest.php tests/Feature/E2E/E2ESeederTest.php
  ```

  Expected failure: `App\Support\E2eDatabaseLease`, `App\Support\E2eSupervisor`, and `Database\Seeders\E2ESeeder` do not exist.

- [ ] **Create the exact PSR-4 lease API and PDO supervisor.**

  Create `backend/app/Support/PostgresConnectionProfile.php` first and use it for every Task 4 and Task 11 PostgreSQL URL, PDO DSN and Laravel resolved-config comparison. No other URL parser is permitted:

  ```php
  <?php

  namespace App\Support;

  use Illuminate\Database\Connection;
  use PDO;
  use RuntimeException;
  use ValueError;

  final class PostgresConnectionProfile
  {
      private const SSLMODES = ['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'];

      private function __construct(
          public readonly string $host,
          public readonly int $port,
          public readonly string $database,
          public readonly string $username,
          private readonly string $password,
          public readonly string $sslmode,
      ) {}

      public static function fromUrl(string $url, bool $requireTestSuffix = true): self
      {
          if ($url === '' || preg_match('/[\x00-\x20\x7f;]/', $url)) {
              throw new RuntimeException('PostgreSQL URL contains an unsafe byte.');
          }
          try {
              $parts = parse_url($url);
          } catch (ValueError) {
              throw new RuntimeException('PostgreSQL URL cannot be parsed.');
          }
          if (! is_array($parts)
              || ! in_array($parts['scheme'] ?? null, ['postgres', 'postgresql'], true)
              || ! isset($parts['host'], $parts['user'], $parts['pass'], $parts['path'])
              || isset($parts['fragment'])
              || ! is_int($parts['port'] ?? 5432)) {
              throw new RuntimeException('PostgreSQL URL authority is incomplete.');
          }
          $decode = static function (string $value): string {
              if (preg_match('/%(?![0-9A-Fa-f]{2})/', $value)) {
                  throw new RuntimeException('PostgreSQL URL encoding is invalid.');
              }
              $decoded = rawurldecode($value);
              if ($decoded === '' || preg_match('/[\x00-\x20\x7f;=]/', $decoded)) {
                  throw new RuntimeException('PostgreSQL URL component is unsafe.');
              }
              return $decoded;
          };
          $host = trim((string) $parts['host'], '[]');
          $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
          $isDns = preg_match('/^(?=.{1,253}$)(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)(?:\.(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?))*$/', $host) === 1;
          if (! $isIp && ! $isDns) {
              throw new RuntimeException('PostgreSQL host is unsafe.');
          }
          $port = $parts['port'] ?? 5432;
          if ($port < 1 || $port > 65535 || ! str_starts_with($parts['path'], '/')) {
              throw new RuntimeException('PostgreSQL port or path is invalid.');
          }
          $database = $decode(substr($parts['path'], 1));
          $username = $decode($parts['user']);
          $password = $decode($parts['pass']);
          if (! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $database)
              || ! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $username)
              || ($requireTestSuffix && ! str_ends_with($database, '_test'))) {
              throw new RuntimeException('PostgreSQL database or role is outside the P00 grammar.');
          }
          $sslmode = 'prefer';
          if (($parts['query'] ?? '') !== '') {
              $items = explode('&', $parts['query']);
              if (count($items) !== 1) {
                  throw new RuntimeException('PostgreSQL URL options are not closed.');
              }
              $pair = explode('=', $items[0], 2);
              if (count($pair) !== 2 || $decode($pair[0]) !== 'sslmode') {
                  throw new RuntimeException('PostgreSQL URL option is not allowed.');
              }
              $sslmode = $decode($pair[1]);
          }
          if (! in_array($sslmode, self::SSLMODES, true)) {
              throw new RuntimeException('PostgreSQL sslmode is unsupported.');
          }
          return new self($host, $port, $database, $username, $password, $sslmode);
      }

      public function pdo(): PDO
      {
          $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database};sslmode={$this->sslmode}";
          return new PDO($dsn, $this->username, $this->password, [
              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
              PDO::ATTR_STRINGIFY_FETCHES => false,
          ]);
      }

      public function laravelConfiguration(): array
      {
          return [
              'driver' => 'pgsql', 'host' => $this->host, 'port' => $this->port,
              'database' => $this->database, 'username' => $this->username,
              'password' => $this->password, 'charset' => 'utf8', 'prefix' => '',
              'prefix_indexes' => true, 'search_path' => 'public', 'sslmode' => $this->sslmode,
          ];
      }

      public function assertSameAuthority(self $other): void
      {
          if ($this->host !== $other->host || $this->port !== $other->port
              || $this->database !== $other->database || $this->username !== $other->username
              || $this->sslmode !== $other->sslmode || ! hash_equals($this->password, $other->password)) {
              throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
          }
      }

      public function assertLaravelConfiguration(Connection $connection, string $expectedName): void
      {
          $actual = $connection->getConfig();
          foreach (['read', 'write', 'sticky', 'unix_socket', 'options'] as $forbidden) {
              if (array_key_exists($forbidden, $actual)) {
                  throw new RuntimeException('PostgreSQL read/write or option override is prohibited.');
              }
          }
          $actualHost = trim((string) ($actual['host'] ?? ''), '[]');
          $actualPort = filter_var($actual['port'] ?? null, FILTER_VALIDATE_INT);
          if ($actualHost !== $this->host || $actualPort !== $this->port) {
              throw new RuntimeException('PostgreSQL Laravel transport mismatch.');
          }
          $expected = [
              'driver' => 'pgsql', 'database' => $this->database, 'username' => $this->username,
              'sslmode' => $this->sslmode, 'charset' => 'utf8', 'prefix' => '',
              'prefix_indexes' => true, 'search_path' => 'public',
          ];
          foreach ($expected as $key => $value) {
              if (($actual[$key] ?? null) !== $value) {
                  throw new RuntimeException('PostgreSQL Laravel configuration mismatch.');
              }
          }
          if ($connection->getName() !== $expectedName
              || ! isset($actual['password'])
              || ! hash_equals($this->password, (string) $actual['password'])) {
              throw new RuntimeException('PostgreSQL Laravel authority mismatch.');
          }
      }

      public static function sealVerifiedPdo(Connection $connection, PDO $verifiedPdo): void
      {
          $assertSame = static function (Connection $current) use ($verifiedPdo): void {
              if ($current->getRawPdo() !== $verifiedPdo) {
                  throw new RuntimeException('P00_PDO_SUBSTITUTION_REFUSED');
              }
          };
          $assertSame($connection);
          $connection->beforeExecuting(
              static function (string $query, array $bindings, Connection $current) use ($assertSame): void {
                  $assertSame($current);
              },
          );
          $connection->beforeStartingTransaction($assertSame);
          $connection->setReconnector(
              static fn (Connection $current): never => throw new RuntimeException('P00_RECONNECT_REFUSED'),
          );
      }
  }
  ```

  The profile's credential fields remain private, are never logged/serialized, and are compared only in memory. Tests include empty fields, unsafe host/database/user/password bytes, semicolons, whitespace, malformed encoding, duplicate/unknown options, fragments, out-of-range ports, read/write configuration and all six accepted `sslmode` values.

  `backend/app/Support/E2eSupervisor.php`:

  ```php
  <?php

  namespace App\Support;

  interface E2eSupervisor
  {
      public function facts(): array;

      public function candidateExists(string $database, string $role): bool;

      public function noncandidateFingerprint(): string;

      public function createCandidate(string $database, string $role, string $password): void;

      public function candidateUrl(string $database, string $role, string $password): string;

      public function noncandidateDatabasesWithAccess(string $role, string $candidate): array;

      public function candidateFacts(string $url): array;

      public function activate(
          string $url,
          string $database,
          string $role,
          string $activationNonceSha256,
          string $fixtureContractSha256,
          string $serviceNonceSha256,
      ): void;
  }
  ```

  `backend/app/Support/E2eProvisioningException.php` preserves a created candidate's nonsecret orphan identity even when acquisition fails before a lease can be returned:

  ```php
  <?php

  namespace App\Support;

  use RuntimeException;
  use Throwable;

  final class E2eProvisioningException extends RuntimeException
  {
      public function __construct(
          public readonly string $database,
          public readonly string $role,
          public readonly string $lifecycleId,
          Throwable $previous,
      ) {
          parent::__construct('E2E candidate requires lifecycle cleanup.', 0, $previous);
      }
  }
  ```

  `backend/app/Support/E2eDatabaseLease.php`:

  ```php
  <?php

  namespace App\Support;

  use Closure;
  use PDO;
  use RuntimeException;
  use Throwable;

  final class E2eDatabaseLease
  {
      private const ATTESTATION_KEYS = [
          'schemaVersion',
          'identity',
          'serverMajor',
          'immutable',
          'instanceNonceSha256',
          'lifecycleId',
          'supervisorDatabase',
          'supervisorRole',
          'containsRealData',
          'canIssueIsolatedCredentials',
          'noncandidateAccessDenied',
      ];

      private function __construct(
          private readonly E2eSupervisor $supervisor,
          private readonly string $database,
          private readonly string $role,
          private readonly string $url,
          private readonly string $identity,
          private readonly string $serviceNonceSha256,
          private readonly string $lifecycleId,
          private readonly string $activationNonceSha256,
          private readonly string $fixtureContractSha256,
      ) {}

      public static function acquire(
          string $environment,
          E2eSupervisor $supervisor,
          array $attestation,
          string $approvedIdentity,
          string $approvedServiceNonceSha256,
          string $approvedLifecycleId,
          string $fixtureContractSha256,
          ?Closure $randomBytes = null,
      ): self {
          if ($environment !== 'e2e'
              || array_keys($attestation) !== self::ATTESTATION_KEYS
              || $attestation['schemaVersion'] !== 1
              || $attestation['identity'] !== $approvedIdentity
              || $attestation['serverMajor'] !== 16
              || $attestation['immutable'] !== true
              || $attestation['instanceNonceSha256'] !== $approvedServiceNonceSha256
              || $attestation['lifecycleId'] !== $approvedLifecycleId
              || $attestation['containsRealData'] !== false
              || $attestation['canIssueIsolatedCredentials'] !== true
              || $attestation['noncandidateAccessDenied'] !== true
              || ! preg_match('/^[0-9a-f]{64}$/', $fixtureContractSha256)) {
              throw new RuntimeException('E2E service authority is incomplete or unsafe.');
          }

          self::assertSupervisorFacts($supervisor->facts(), $attestation);
          $before = $supervisor->noncandidateFingerprint();
          $entropy = $randomBytes ?? random_bytes(...);
          $suffix = bin2hex($entropy(16));
          $database = 'p00_e2e_'.$suffix.'_test';
          $role = 'p00_e2e_r_'.$suffix;
          $password = rtrim(strtr(base64_encode($entropy(32)), '+/', '-_'), '=');
          $activationNonceSha256 = hash('sha256', $entropy(32));

          if ($supervisor->candidateExists($database, $role)) {
              throw new RuntimeException('E2E candidate collision refused; no name is reused.');
          }
          try {
              $supervisor->createCandidate($database, $role, $password);
              self::assertSupervisorFacts($supervisor->facts(), $attestation);
              if (! hash_equals($before, $supervisor->noncandidateFingerprint())) {
                  throw new RuntimeException('Noncandidate database metadata changed.');
              }
              if ($supervisor->noncandidateDatabasesWithAccess($role, $database) !== []) {
                  throw new RuntimeException('Candidate role can access a noncandidate database.');
              }

              $url = $supervisor->candidateUrl($database, $role, $password);
              $lease = new self(
                  $supervisor,
                  $database,
                  $role,
                  $url,
                  $approvedIdentity,
                  $approvedServiceNonceSha256,
                  $approvedLifecycleId,
                  $activationNonceSha256,
                  $fixtureContractSha256,
              );
              $lease->assertCandidate(false);

              return $lease;
          } catch (Throwable $error) {
              throw new E2eProvisioningException($database, $role, $approvedLifecycleId, $error);
          }
      }

      public function database(): string
      {
          return $this->database;
      }

      public function role(): string
      {
          return $this->role;
      }

      public function lifecycleId(): string
      {
          return $this->lifecycleId;
      }

      public function environment(string $phase): array
      {
          return [
              'APP_ENV' => 'e2e',
              'DB_CONNECTION' => 'e2e',
              'P00_E2E_PHASE' => $phase,
              'P00_E2E_DB_URL' => $this->url,
              'P00_E2E_DATABASE' => $this->database,
              'P00_E2E_ROLE' => $this->role,
              'P00_PG_IDENTITY' => $this->identity,
              'P00_PG_INSTANCE_NONCE_SHA256' => $this->serviceNonceSha256,
              'P00_E2E_ACTIVATION_NONCE_SHA256' => $this->activationNonceSha256,
              'P00_E2E_FIXTURE_CONTRACT_SHA256' => $this->fixtureContractSha256,
              'P00_E2E_SERVICE_LIFECYCLE_ID' => $this->lifecycleId,
          ];
      }

      public function activate(): void
      {
          $this->assertCandidate(false);
          $this->supervisor->activate(
              $this->url,
              $this->database,
              $this->role,
              $this->activationNonceSha256,
              $this->fixtureContractSha256,
              $this->serviceNonceSha256,
          );
          $this->assertActive();
      }

      public function assertActive(): void
      {
          $this->assertCandidate(true);
      }

      public function supervisorForTest(): E2eSupervisor
      {
          if (! app()->environment('testing')) {
              throw new RuntimeException('Test seam is unavailable outside tests.');
          }

          return $this->supervisor;
      }

      public static function assertBootConnection(
          PDO $pdo,
          string $database,
          string $role,
          string $serviceNonceSha256,
          string $activationNonceSha256,
          string $fixtureContractSha256,
          string $phase,
      ): void {
          $row = $pdo->query(
              "SELECT current_database() AS database, current_user AS role,
              current_setting('server_version_num')::int / 10000 AS server_major,
              current_setting('dorzak.instance_nonce_sha256') AS service_nonce_sha256,
              rolsuper, rolcreatedb, rolcreaterole, rolbypassrls, rolreplication,
              to_regclass('public.p00_e2e_activation') AS activation_table
              FROM pg_roles WHERE rolname = current_user",
          )->fetch(PDO::FETCH_ASSOC);
          if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql'
              || ! in_array($phase, ['provisioning-migrate', 'provisioning-seed', 'provisioning-activate', 'active'], true)
              || ! is_array($row)
              || $row['database'] !== $database
              || $row['role'] !== $role
              || (int) $row['server_major'] !== 16
              || $row['service_nonce_sha256'] !== $serviceNonceSha256
              || $row['rolsuper'] !== false
              || $row['rolcreatedb'] !== false
              || $row['rolcreaterole'] !== false
              || $row['rolbypassrls'] !== false
              || $row['rolreplication'] !== false) {
              throw new RuntimeException('P00_E2E_LIVE_PDO_REFUSED');
          }
          if ($phase !== 'active') {
              if ($row['activation_table'] !== null) {
                  throw new RuntimeException('Provisioning child requires an unactivated candidate.');
              }
              return;
          }
          $activation = $pdo->query(
              'SELECT activation_nonce_sha256, fixture_contract_sha256, service_nonce_sha256
               FROM p00_e2e_activation WHERE singleton = true',
          )->fetch(PDO::FETCH_ASSOC);
          if (! is_array($activation)
              || $activation['activation_nonce_sha256'] !== $activationNonceSha256
              || $activation['fixture_contract_sha256'] !== $fixtureContractSha256
              || $activation['service_nonce_sha256'] !== $serviceNonceSha256) {
              throw new RuntimeException('Active E2E connection identity mismatch.');
          }
      }

      private function assertCandidate(bool $active): void
      {
          $facts = $this->supervisor->candidateFacts($this->url);
          $expectedKeys = [
              'driver', 'database', 'role', 'serverMajor', 'instanceNonceSha256',
              'superuser', 'createdb', 'createrole', 'bypassrls', 'replication',
              'activationNonceSha256', 'fixtureContractSha256',
          ];
          if (array_keys($facts) !== $expectedKeys
              || $facts['driver'] !== 'pgsql'
              || $facts['database'] !== $this->database
              || $facts['role'] !== $this->role
              || $facts['serverMajor'] !== 16
              || $facts['instanceNonceSha256'] !== $this->serviceNonceSha256
              || $facts['superuser'] !== false
              || $facts['createdb'] !== false
              || $facts['createrole'] !== false
              || $facts['bypassrls'] !== false
              || $facts['replication'] !== false
              || $facts['activationNonceSha256'] !== ($active ? $this->activationNonceSha256 : null)
              || $facts['fixtureContractSha256'] !== ($active ? $this->fixtureContractSha256 : null)) {
              throw new RuntimeException('E2E candidate capability identity mismatch.');
          }
      }

      private static function assertSupervisorFacts(array $facts, array $attestation): void
      {
          if (array_keys($facts) !== ['driver', 'identity', 'serverMajor', 'instanceNonceSha256', 'database', 'role']
              || $facts['driver'] !== 'pgsql'
              || $facts['identity'] !== $attestation['identity']
              || $facts['serverMajor'] !== 16
              || $facts['instanceNonceSha256'] !== $attestation['instanceNonceSha256']
              || $facts['database'] !== $attestation['supervisorDatabase']
              || $facts['role'] !== $attestation['supervisorRole']) {
              throw new RuntimeException('Live E2E supervisor does not match its attestation.');
          }
      }
  }
  ```

  `backend/app/Support/PdoE2eSupervisor.php`:

  ```php
  <?php

  namespace App\Support;

  use PDO;
  use RuntimeException;
  use Throwable;

  final class PdoE2eSupervisor implements E2eSupervisor
  {
      private PDO $pdo;

      public function __construct(
          private readonly string $supervisorUrl,
          private readonly string $identity,
      ) {
          $this->pdo = self::connect($supervisorUrl);
      }

      public function facts(): array
      {
          $row = $this->pdo->query(
              "SELECT current_database() AS database, current_user AS role,
              current_setting('server_version_num')::int / 10000 AS server_major,
              current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256",
          )->fetch(PDO::FETCH_ASSOC);

          return [
              'driver' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
              'identity' => $this->identity,
              'serverMajor' => (int) $row['server_major'],
              'instanceNonceSha256' => $row['instance_nonce_sha256'],
              'database' => $row['database'],
              'role' => $row['role'],
          ];
      }

      public function candidateExists(string $database, string $role): bool
      {
          $statement = $this->pdo->prepare(
              'SELECT EXISTS(SELECT 1 FROM pg_database WHERE datname = :database)
               OR EXISTS(SELECT 1 FROM pg_roles WHERE rolname = :role)',
          );
          $statement->execute(['database' => $database, 'role' => $role]);

          return (bool) $statement->fetchColumn();
      }

      public function noncandidateFingerprint(): string
      {
          $rows = $this->pdo->query(
              "SELECT 'database' AS kind, datname AS name, datallowconn::text AS value
               FROM pg_database WHERE datname NOT LIKE 'p00_e2e_%'
               UNION ALL
               SELECT 'role', rolname, concat_ws(':', rolsuper, rolcreatedb, rolcreaterole, rolreplication, rolbypassrls)
               FROM pg_roles WHERE rolname NOT LIKE 'p00_e2e_r_%'
               ORDER BY kind, name",
          )->fetchAll(PDO::FETCH_ASSOC);

          return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
      }

      public function createCandidate(string $database, string $role, string $password): void
      {
          $databaseIdentifier = self::identifier($database);
          $roleIdentifier = self::identifier($role);
          $passwordLiteral = $this->pdo->quote($password);
          $this->pdo->exec(
              "CREATE ROLE {$roleIdentifier} LOGIN PASSWORD {$passwordLiteral}
               NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOREPLICATION NOBYPASSRLS",
          );
          $this->pdo->exec(
              "CREATE DATABASE {$databaseIdentifier} OWNER {$roleIdentifier} TEMPLATE template0 ENCODING 'UTF8'",
          );
          $this->pdo->exec("REVOKE ALL ON DATABASE {$databaseIdentifier} FROM PUBLIC");
          $this->pdo->exec("GRANT CONNECT, TEMPORARY ON DATABASE {$databaseIdentifier} TO {$roleIdentifier}");
      }

      public function candidateUrl(string $database, string $role, string $password): string
      {
          $profile = PostgresConnectionProfile::fromUrl($this->supervisorUrl, false);
          $host = str_contains($profile->host, ':') ? '['.$profile->host.']' : $profile->host;
          $query = '?sslmode='.rawurlencode($profile->sslmode);

          return sprintf(
              'postgresql://%s:%s@%s:%d/%s%s',
              rawurlencode($role),
              rawurlencode($password),
              $host,
              $profile->port,
              rawurlencode($database),
              $query,
          );
      }

      public function noncandidateDatabasesWithAccess(string $role, string $candidate): array
      {
          $statement = $this->pdo->prepare(
              "SELECT datname FROM pg_database
               WHERE datname <> :candidate AND datallowconn
                 AND has_database_privilege(:role, datname, 'CONNECT')
               ORDER BY datname",
          );
          $statement->execute(['candidate' => $candidate, 'role' => $role]);

          return $statement->fetchAll(PDO::FETCH_COLUMN);
      }

      public function candidateFacts(string $url): array
      {
          $pdo = self::connect($url);
          $row = $pdo->query(
              "SELECT current_database() AS database, current_user AS role,
              current_setting('server_version_num')::int / 10000 AS server_major,
              current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256,
              CASE WHEN rolsuper THEN 1 ELSE 0 END AS rolsuper,
              CASE WHEN rolcreatedb THEN 1 ELSE 0 END AS rolcreatedb,
              CASE WHEN rolcreaterole THEN 1 ELSE 0 END AS rolcreaterole,
              CASE WHEN rolbypassrls THEN 1 ELSE 0 END AS rolbypassrls,
              CASE WHEN rolreplication THEN 1 ELSE 0 END AS rolreplication
              FROM pg_roles WHERE rolname = current_user",
          )->fetch(PDO::FETCH_ASSOC);
          $activation = ['activation_nonce_sha256' => null, 'fixture_contract_sha256' => null];
          if ($pdo->query("SELECT to_regclass('public.p00_e2e_activation')")->fetchColumn() !== null) {
              $activation = $pdo->query(
                  'SELECT activation_nonce_sha256, fixture_contract_sha256
                   FROM p00_e2e_activation WHERE singleton = true',
              )->fetch(PDO::FETCH_ASSOC);
          }

          return [
              'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
              'database' => $row['database'],
              'role' => $row['role'],
              'serverMajor' => (int) $row['server_major'],
              'instanceNonceSha256' => $row['instance_nonce_sha256'],
              'superuser' => (int) $row['rolsuper'] === 1,
              'createdb' => (int) $row['rolcreatedb'] === 1,
              'createrole' => (int) $row['rolcreaterole'] === 1,
              'bypassrls' => (int) $row['rolbypassrls'] === 1,
              'replication' => (int) $row['rolreplication'] === 1,
              'activationNonceSha256' => $activation['activation_nonce_sha256'],
              'fixtureContractSha256' => $activation['fixture_contract_sha256'],
          ];
      }

      public function activate(
          string $url,
          string $database,
          string $role,
          string $activationNonceSha256,
          string $fixtureContractSha256,
          string $serviceNonceSha256,
      ): void {
          $pdo = PostgresConnectionProfile::fromUrl($url)->pdo();
          self::activateVerifiedPdo(
              $pdo, $database, $role, $serviceNonceSha256,
              $activationNonceSha256, $fixtureContractSha256,
          );
      }

      public static function activateVerifiedPdo(
          PDO $pdo,
          string $database,
          string $role,
          string $serviceNonceSha256,
          string $activationNonceSha256,
          string $fixtureContractSha256,
      ): void {
          try {
              E2eDatabaseLease::assertBootConnection(
                  $pdo, $database, $role, $serviceNonceSha256,
                  $activationNonceSha256, $fixtureContractSha256, 'provisioning-activate',
              );
              $pdo->beginTransaction();
              $pdo->exec(
                  'CREATE TABLE p00_e2e_activation (
                      singleton boolean PRIMARY KEY CHECK (singleton),
                      activation_nonce_sha256 char(64) NOT NULL,
                      fixture_contract_sha256 char(64) NOT NULL,
                      service_nonce_sha256 char(64) NOT NULL
                  )',
              );
              $statement = $pdo->prepare(
                  'INSERT INTO p00_e2e_activation
                   (singleton, activation_nonce_sha256, fixture_contract_sha256, service_nonce_sha256)
                   VALUES (true, :activation, :contract, :service)',
              );
              $statement->execute([
                  'activation' => $activationNonceSha256,
                  'contract' => $fixtureContractSha256,
                  'service' => $serviceNonceSha256,
              ]);
              E2eDatabaseLease::assertBootConnection(
                  $pdo, $database, $role, $serviceNonceSha256,
                  $activationNonceSha256, $fixtureContractSha256, 'active',
              );
              $pdo->commit();
          } catch (Throwable $error) {
              if ($pdo->inTransaction()) {
                  $pdo->rollBack();
              }
              throw $error;
          }
      }

      public function terminateBackend(string $database, string $role, int $pid): void
      {
          if ($pid < 1) {
              throw new RuntimeException('P00_BACKEND_TERMINATION_REFUSED');
          }
          $statement = $this->pdo->prepare(
              'SELECT pg_terminate_backend(pid) AS terminated
               FROM pg_stat_activity
               WHERE pid = :pid AND datname = :database AND usename = :role
                 AND pid <> pg_backend_pid()',
          );
          $statement->execute(['pid' => $pid, 'database' => $database, 'role' => $role]);
          $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
          if (count($rows) !== 1 || $rows[0]['terminated'] !== true) {
              throw new RuntimeException('P00_BACKEND_TERMINATION_REFUSED');
          }
      }

      private static function connect(string $url): PDO
      {
          return PostgresConnectionProfile::fromUrl($url, false)->pdo();
      }

      private static function identifier(string $value): string
      {
          if (! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $value)) {
              throw new RuntimeException('Unsafe PostgreSQL identifier.');
          }

          return '"'.$value.'"';
      }
  }
  ```

- [ ] **Create the supervisor command, dedicated connection, and boot guard.**

  `backend/app/Console/Commands/ServeE2e.php`:

  ```php
  <?php

  namespace App\Console\Commands;

  use App\Support\E2eDatabaseLease;
  use App\Support\E2eProvisioningException;
  use App\Support\PdoE2eSupervisor;
  use Database\Seeders\E2ESeeder;
  use Illuminate\Console\Command;
  use RuntimeException;
  use Symfony\Component\Process\Process;
  use Throwable;

  final class ServeE2e extends Command
  {
      protected $signature = 'e2e:serve {--host=127.0.0.1} {--port=8000}';

      protected $description = 'Create and serve one attested PostgreSQL E2E capability';

      public function handle(): int
      {
          $lease = null;
          try {
              $host = (string) $this->option('host');
              $port = filter_var($this->option('port'), FILTER_VALIDATE_INT);
              if ($host !== '127.0.0.1' || $port === false || $port < 1024 || $port > 65535) {
                  throw new RuntimeException('E2E listener must be a non-privileged loopback port.');
              }

              $attestationPath = (string) env('P00_E2E_SERVICE_ATTESTATION_PATH');
              $attestationSha256 = (string) env('P00_E2E_SERVICE_ATTESTATION_SHA256');
              if (! is_file($attestationPath)
                  || ! hash_equals($attestationSha256, hash_file('sha256', $attestationPath))) {
                  throw new RuntimeException('E2E service attestation is absent or changed.');
              }
              $attestation = json_decode(
                  (string) file_get_contents($attestationPath),
                  true,
                  512,
                  JSON_THROW_ON_ERROR,
              );
              $supervisor = new PdoE2eSupervisor(
                  (string) env('P00_E2E_SUPERVISOR_DB_URL'),
                  (string) env('P00_PG_IDENTITY'),
              );
              $lease = E2eDatabaseLease::acquire(
                  $this->laravel->environment(),
                  $supervisor,
                  $attestation,
                  (string) env('P00_PG_IDENTITY'),
                  (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
                  (string) env('P00_E2E_SERVICE_LIFECYCLE_ID'),
                  hash('sha256', E2ESeeder::CONTRACT_JSON),
              );

              self::prepare($lease, static function (array $command, array $environment): void {
                  (new Process(
                      $command,
                      base_path(),
                      array_merge($_SERVER, $_ENV, $environment),
                  ))->setTimeout(300)->mustRun();
              });

              $environment = array_merge($_SERVER, $_ENV, $lease->environment('active'));
              $server = new Process(
                  [PHP_BINARY, 'artisan', 'serve', '--host=127.0.0.1', "--port={$port}", '--no-interaction'],
                  base_path(),
                  $environment,
              );
              $server->setTimeout(null);
              $this->info("E2E_SERVE PASS database={$lease->database()} lifecycle={$lease->lifecycleId()}");
              return $server->run(static function (string $type, string $buffer): void {
                  fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
              });
          } catch (E2eProvisioningException $error) {
              $this->error("E2E_SERVE REFUSED orphan_database={$error->database} orphan_role={$error->role} lifecycle={$error->lifecycleId}");
              return self::FAILURE;
          } catch (Throwable) {
              $database = $lease?->database() ?? 'none';
              $role = $lease?->role() ?? 'none';
              $lifecycle = $lease?->lifecycleId() ?? (string) env('P00_E2E_SERVICE_LIFECYCLE_ID', 'unknown');
              $this->error("E2E_SERVE REFUSED orphan_database={$database} orphan_role={$role} lifecycle={$lifecycle}");
              return self::FAILURE;
          }
      }

      public static function prepare(E2eDatabaseLease $lease, callable $runner): void
      {
          $runner(
              [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction'],
              $lease->environment('provisioning-migrate'),
          );
          $runner(
              [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\\Seeders\\E2ESeeder', '--force', '--no-interaction'],
              $lease->environment('provisioning-seed'),
          );
          $lease->activate();
      }
  }
  ```

  Add this exact connection immediately after the existing `pgsql` connection in `backend/config/database.php`:

  ```php
  'e2e' => [
      'driver' => 'pgsql',
      'url' => env('P00_E2E_DB_URL'),
      'host' => '127.0.0.1',
      'port' => '5432',
      'database' => 'refused_without_P00_E2E_DB_URL',
      'username' => 'refused_without_P00_E2E_DB_URL',
      'password' => '',
      'charset' => 'utf8',
      'prefix' => '',
      'prefix_indexes' => true,
      'search_path' => 'public',
      'sslmode' => 'prefer',
  ],
  ```

  Apply this deterministic patch to `backend/app/Providers/AppServiceProvider.php`:

  ```diff
   use App\Services\PlanGate;
  +use App\Support\E2eDatabaseLease;
  +use App\Support\PostgresConnectionProfile;
  +use Illuminate\Database\Connection;
  +use Illuminate\Support\Facades\DB;
   use Illuminate\Support\ServiceProvider;
  +use RuntimeException;
  @@
       public function boot(): void
       {
  +        if ($this->app->environment('e2e')) {
  +            $phase = (string) env('P00_E2E_PHASE');
  +            if (in_array($phase, ['provisioning-migrate', 'provisioning-seed', 'active'], true)) {
  +                $profile = PostgresConnectionProfile::fromUrl((string) env('P00_E2E_DB_URL'));
  +                DB::setReconnector(
  +                    static fn (Connection $connection): never => throw new RuntimeException('P00_RECONNECT_REFUSED'),
  +                );
  +                $connection = DB::connection('e2e');
  +                $profile->assertLaravelConfiguration($connection, 'e2e');
  +                $pdo = $connection->getPdo();
  +                E2eDatabaseLease::assertBootConnection(
  +                    $pdo,
  +                    (string) env('P00_E2E_DATABASE'),
  +                    (string) env('P00_E2E_ROLE'),
  +                    (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
  +                    (string) env('P00_E2E_ACTIVATION_NONCE_SHA256'),
  +                    (string) env('P00_E2E_FIXTURE_CONTRACT_SHA256'),
  +                    $phase,
  +                );
  +                PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
  +            } elseif ($phase !== 'supervisor') {
  +                throw new RuntimeException('Unrecognized E2E boot phase.');
  +            }
  +        }
  +
           // Invalidate the public storefront cache when catalog data changes.
  ```

- [ ] **Create and execute the real PostgreSQL mutation-window harness.**

  `backend/tests/Support/PostgresSubstitutionHarness.php` is executable test support, not an external registered dependency. It provisions two independent create-only candidates on the approved P00-only service, never changes DNS/proxies/hosts, never prints a URL, and runs the real Laravel child with the substitute URL merged last:

  ```php
  <?php

  namespace Tests\Support;

  use App\Support\E2eDatabaseLease;
  use App\Support\PdoE2eSupervisor;
  use App\Support\PostgresConnectionProfile;
  use App\Support\PostgresQualificationGuard;
  use Database\Seeders\E2ESeeder;
  use PDO;
  use RuntimeException;
  use Symfony\Component\Process\Process;

  final class PostgresSubstitutionHarness
  {
      private function __construct(
          private readonly PdoE2eSupervisor $supervisor,
          private readonly array $attestation,
      ) {}

      public static function fromEnvironment(): self
      {
          $path = (string) env('P00_E2E_SERVICE_ATTESTATION_PATH');
          $sha = (string) env('P00_E2E_SERVICE_ATTESTATION_SHA256');
          if (! is_file($path) || is_link($path) || realpath($path) !== $path
              || ! hash_equals($sha, hash_file('sha256', $path))) {
              throw new RuntimeException('P00_LIVE_HARNESS_ATTESTATION_REFUSED');
          }
          $attestation = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
          return new self(
              new PdoE2eSupervisor(
                  (string) env('P00_E2E_SUPERVISOR_DB_URL'),
                  (string) env('P00_PG_IDENTITY'),
              ),
              $attestation,
          );
      }

      public function candidate(): E2eDatabaseLease
      {
          return E2eDatabaseLease::acquire(
              'e2e', $this->supervisor, $this->attestation,
              (string) env('P00_PG_IDENTITY'),
              (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
              (string) env('P00_E2E_SERVICE_LIFECYCLE_ID'),
              hash('sha256', E2ESeeder::CONTRACT_JSON),
          );
      }

      public function profile(E2eDatabaseLease $lease): PostgresConnectionProfile
      {
          return PostgresConnectionProfile::fromUrl(
              $lease->environment('provisioning-migrate')['P00_E2E_DB_URL'],
          );
      }

      public function pdo(E2eDatabaseLease $lease): PDO
      {
          $pdo = $this->profile($lease)->pdo();
          $facts = $lease->environment('provisioning-migrate');
          E2eDatabaseLease::assertBootConnection(
              $pdo,
              $facts['P00_E2E_DATABASE'],
              $facts['P00_E2E_ROLE'],
              $facts['P00_PG_INSTANCE_NONCE_SHA256'],
              $facts['P00_E2E_ACTIVATION_NONCE_SHA256'],
              $facts['P00_E2E_FIXTURE_CONTRACT_SHA256'],
              'provisioning-migrate',
          );
          return $pdo;
      }

      public function terminateBackend(E2eDatabaseLease $lease, int $pid): void
      {
          $this->supervisor->terminateBackend($lease->database(), $lease->role(), $pid);
      }

      public function activateQualification(E2eDatabaseLease $lease, string $nonceSha256): void
      {
          $pdo = $this->pdo($lease);
          PostgresQualificationGuard::activateCandidate($pdo, $nonceSha256);
      }

      public function installCanary(E2eDatabaseLease $lease): string
      {
          $pdo = $this->pdo($lease);
          $pdo->exec('CREATE TABLE p00_guard_canary (singleton boolean PRIMARY KEY, value char(64) NOT NULL)');
          $pdo->exec("INSERT INTO p00_guard_canary VALUES (true, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')");
          return $this->canaryFingerprint($lease);
      }

      public function canaryFingerprint(E2eDatabaseLease $lease): string
      {
          $rows = $this->pdo($lease)->query(
              'SELECT singleton, value FROM p00_guard_canary ORDER BY singleton',
          )->fetchAll(PDO::FETCH_ASSOC);
          return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
      }

      public function activationExists(E2eDatabaseLease $lease): bool
      {
          return $this->pdo($lease)
              ->query("SELECT to_regclass('public.p00_e2e_activation') IS NOT NULL")
              ->fetchColumn() === true;
      }

      public function runChild(
          E2eDatabaseLease $expected,
          array $command,
          string $phase,
          ?E2eDatabaseLease $substitute = null,
          array $overrides = [],
      ): Process {
          $environment = $expected->environment($phase);
          if ($substitute !== null) {
              $environment['P00_E2E_DB_URL'] = $substitute->environment($phase)['P00_E2E_DB_URL'];
          }
          $process = new Process(
              $command,
              base_path(),
              array_merge($_SERVER, $_ENV, $environment, $overrides),
          );
          $process->setTimeout(300)->run();
          return $process;
      }
  }
  ```

  `backend/tests/LivePostgres/E2ELiveMutationGuardTest.php` is outside every default PHPUnit suite and is run explicitly only with the approved live-service secrets:

  ```php
  <?php

  namespace Tests\LivePostgres;

  use App\Support\E2eDatabaseLease;
  use App\Support\PdoE2eSupervisor;
  use App\Support\PostgresConnectionProfile;
  use Illuminate\Database\Connectors\ConnectionFactory;
  use RuntimeException;
  use Tests\Support\PostgresSubstitutionHarness;
  use Tests\TestCase;

  final class E2ELiveMutationGuardTest extends TestCase
  {
      public function test_every_mutation_window_and_reconnector_refuses_a_substitute(): void
      {
          $harness = PostgresSubstitutionHarness::fromEnvironment();
          $migrate = [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction'];
          $seed = [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\Seeders\E2ESeeder', '--force', '--no-interaction'];

          $beforeMigrate = $harness->candidate();
          $migrateCanary = $harness->candidate();
          $migrateFingerprint = $harness->installCanary($migrateCanary);
          $this->assertRefused($harness->runChild($beforeMigrate, $migrate, 'provisioning-migrate', $migrateCanary));
          self::assertSame($migrateFingerprint, $harness->canaryFingerprint($migrateCanary));
          self::assertFalse($harness->activationExists($migrateCanary));

          $beforeSeed = $harness->candidate();
          $seedCanary = $harness->candidate();
          $seedFingerprint = $harness->installCanary($seedCanary);
          self::assertTrue($harness->runChild($beforeSeed, $migrate, 'provisioning-migrate')->isSuccessful());
          $this->assertRefused($harness->runChild($beforeSeed, $seed, 'provisioning-seed', $seedCanary));
          self::assertSame($seedFingerprint, $harness->canaryFingerprint($seedCanary));
          self::assertFalse($harness->activationExists($seedCanary));

          $wrongNonce = $harness->candidate();
          foreach ([['provisioning-migrate', $migrate], ['provisioning-seed', $seed]] as [$phase, $command]) {
              $this->assertRefused($harness->runChild(
                  $wrongNonce, $command, $phase, null,
                  ['P00_PG_INSTANCE_NONCE_SHA256' => str_repeat('0', 64)],
              ));
          }

          $activationExpected = $harness->candidate();
          $activationCanary = $harness->candidate();
          $activationFingerprint = $harness->installCanary($activationCanary);
          $expected = $activationExpected->environment('provisioning-migrate');
          try {
              PdoE2eSupervisor::activateVerifiedPdo(
                  $harness->pdo($activationCanary),
                  $expected['P00_E2E_DATABASE'], $expected['P00_E2E_ROLE'],
                  $expected['P00_PG_INSTANCE_NONCE_SHA256'],
                  $expected['P00_E2E_ACTIVATION_NONCE_SHA256'],
                  $expected['P00_E2E_FIXTURE_CONTRACT_SHA256'],
              );
              self::fail('Activation substitution was accepted.');
          } catch (RuntimeException $error) {
              self::assertSame('P00_E2E_LIVE_PDO_REFUSED', $error->getMessage());
          }
          self::assertSame($activationFingerprint, $harness->canaryFingerprint($activationCanary));
          self::assertFalse($harness->activationExists($activationCanary));

          $sealed = $harness->candidate();
          $sealedFingerprint = $harness->installCanary($sealed);
          $profile = $harness->profile($sealed);
          $connection = app(ConnectionFactory::class)->make($profile->laravelConfiguration(), 'e2e-live-guard');
          $profile->assertLaravelConfiguration($connection, 'e2e-live-guard');
          $pdo = $connection->getPdo();
          $sealedFacts = $sealed->environment('provisioning-migrate');
          E2eDatabaseLease::assertBootConnection(
              $pdo, $sealedFacts['P00_E2E_DATABASE'], $sealedFacts['P00_E2E_ROLE'],
              $sealedFacts['P00_PG_INSTANCE_NONCE_SHA256'],
              $sealedFacts['P00_E2E_ACTIVATION_NONCE_SHA256'],
              $sealedFacts['P00_E2E_FIXTURE_CONTRACT_SHA256'], 'provisioning-migrate',
          );
          PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
          $backendPid = (int) $connection->scalar('SELECT pg_backend_pid()');
          $harness->terminateBackend($sealed, $backendPid);
          $this->expectMarker(
              'P00_RECONNECT_REFUSED',
              static fn () => $connection->statement('CREATE TABLE forbidden_lost_write (id integer)'),
          );
          self::assertSame($sealedFingerprint, $harness->canaryFingerprint($sealed));

          $replacement = $harness->candidate();
          $replacementFingerprint = $harness->installCanary($replacement);
          $connection->setPdo($harness->pdo($replacement));
          $this->expectMarker('P00_PDO_SUBSTITUTION_REFUSED', static fn () => $connection->statement('CREATE TABLE forbidden_write (id integer)'));
          self::assertSame($replacementFingerprint, $harness->canaryFingerprint($replacement));
      }

      private function assertRefused(\Symfony\Component\Process\Process $process): void
      {
          self::assertFalse($process->isSuccessful());
          self::assertStringContainsString('P00_E2E_LIVE_PDO_REFUSED', $process->getOutput().$process->getErrorOutput());
      }

      private function expectMarker(string $marker, callable $callback): void
      {
          try {
              $callback();
              self::fail($marker.' was not raised.');
          } catch (RuntimeException $error) {
              self::assertSame($marker, $error->getMessage());
          }
      }
  }
  ```

  Change the generic identity failure in `E2eDatabaseLease::assertBootConnection()` to the stable marker `P00_E2E_LIVE_PDO_REFUSED`, then run the out-of-suite file explicitly:

  ```bash
  cd backend
  php artisan test tests/LivePostgres/E2ELiveMutationGuardTest.php
  ```

  Expected: one passing live test; real migrate/seed children, same-PDO activation, wrong live nonce, fail-closed reconnect and PDO-replacement cases all refuse before the target mutation, and every substitute canary remains byte-identical. No mock-only assertion satisfies this gate.

- [ ] **Create the deterministic Qatar/QAR fixture.**

  `backend/database/seeders/E2ESeeder.php`:

  ```php
  <?php

  namespace Database\Seeders;

  use App\Enums\StaffRole;
  use App\Models\Category;
  use App\Models\Plan;
  use App\Models\Store;
  use App\Models\StoreUser;
  use App\Models\User;
  use App\Services\ProductService;
  use App\Support\StoreContext;
  use Illuminate\Database\Seeder;
  use Illuminate\Support\Facades\Hash;

  final class E2ESeeder extends Seeder
  {
      public const CONTRACT_JSON = '{"country":"Qatar","currency":"QAR","email":"owner@e2e.dorzak.test","plan":"PRO","productSku":"E2E-HOODIE","schemaVersion":1}';

      public function run(): void
      {
          $this->call(PlanSeeder::class);

          $owner = User::create([
              'name' => 'Dorzak E2E Owner',
              'email' => 'owner@e2e.dorzak.test',
              'password' => Hash::make('e2e-password'),
          ]);
          $store = Store::create([
              'name' => 'Dorzak E2E Merchant',
              'tagline' => 'Deterministic browser fixture',
              'owner_name' => $owner->name,
              'email' => $owner->email,
              'country' => 'Qatar',
              'timezone' => 'Asia/Qatar',
              'language' => 'en',
              'currency' => 'QAR',
              'symbol_placement' => 'BEFORE',
              'charge_sales_tax' => false,
              'tax_rate' => 0,
          ]);
          $store->initializeSettings();
          $store->subscription->update([
              'plan_id' => Plan::where('code', 'PRO')->value('id'),
              'status' => 'ACTIVE',
          ]);
          StoreUser::create([
              'store_id' => $store->id,
              'user_id' => $owner->id,
              'role' => StaffRole::OWNER,
              'is_active' => true,
              'joined_at' => now(),
          ]);
          app(StoreContext::class)->setStore($store);
          $category = Category::create(['name' => 'Apparel', 'color' => '#17201e']);

          app(ProductService::class)->create([
              'name' => 'Dorzak Signature Cotton Hoodie',
              'price' => 49.99,
              'cost' => 18,
              'category_id' => $category->id,
              'sku' => 'E2E-HOODIE',
              'taxable' => false,
              'track_stock' => true,
              'variant_groups' => [
                  ['id' => 'size', 'name' => 'Size', 'required' => true, 'options' => [['id' => 'small', 'name' => 'Small']]],
                  ['id' => 'color', 'name' => 'Color', 'required' => true, 'options' => [['id' => 'black', 'name' => 'Black']]],
              ],
              'variants' => [[
                  'name' => 'Small / Black',
                  'option_values' => ['size' => 'small', 'color' => 'black'],
                  'price' => 49.99,
                  'stock' => 10,
                  'sku' => 'E2E-HOODIE-S-BLK',
                  'is_active' => true,
              ]],
          ], $owner);
      }
  }
  ```

- [ ] **Verify create-only safety, fixture behavior, and the fast SQLite aggregate.**

  ```bash
  cd backend
  php artisan test tests/Feature/E2E/E2EProvisioningGuardTest.php tests/Feature/E2E/E2ESeederTest.php
  APP_ENV=testing P00_E2E_PHASE=supervisor php artisan e2e:serve --no-interaction; test "$?" = 1
  set +e
  rg -n 'migrate:fresh|db:wipe|\bDROP\b|unlink\(|rename\(' \
    app/Support/E2eDatabaseLease.php app/Support/PdoE2eSupervisor.php app/Console/Commands/ServeE2e.php
  forbidden_status="$?"
  set -e
  test "$forbidden_status" = 1
  php artisan test
  ```

  Expected: `2 passed`; the refusal command exits `1` before mutation. The comprehensive guard test covers wrong environment/driver/service/major/control-database/supervisor-role/nonce, create collision, live-service substitution between attestation and creation, seed failure, prior-server preservation, candidate DSN substitution at activation, concurrent candidate isolation, least privilege, unchanged noncandidate metadata, and an exact spy proving only `migrate` and `db:seed` run. It also runs real Laravel children through `PostgresSubstitutionHarness` twice: substitute the candidate endpoint after acquisition/before `provisioning-migrate`, then again after migration/before `provisioning-seed`; in both cases the child's exact live `e2e` PDO boot guard exits before its mutation and a pre-written noncandidate canary fingerprint remains byte-identical. A valid PostgreSQL 16 candidate ending `_test` but exposing the wrong live nonce is refused by both child phases. The forbidden-operation scan returns exactly no-match status `1`, never regex-error status `2`. Full SQLite reports `446 passed`.

  For the live browser run, Task 5 additionally proves the supervisor and candidate through the real PDO connection. The approved provisioner performs cleanup by exact `P00_E2E_SERVICE_LIFECYCLE_ID`; a failed cleanup records `orphan_database` and lifecycle ID without issuing SQL deletion. No P00 command contains a database/file destroy primitive.

- [ ] **Stage only the thirteen Task 4 files and commit.**

  ```bash
  git add -- \
    backend/app/Support/E2eSupervisor.php \
    backend/app/Support/PdoE2eSupervisor.php \
    backend/app/Support/E2eDatabaseLease.php \
    backend/app/Support/E2eProvisioningException.php \
    backend/app/Support/PostgresConnectionProfile.php \
    backend/app/Console/Commands/ServeE2e.php \
    backend/database/seeders/E2ESeeder.php \
    backend/config/database.php \
    backend/app/Providers/AppServiceProvider.php \
    backend/tests/Feature/E2E/E2EProvisioningGuardTest.php \
    backend/tests/Feature/E2E/E2ESeederTest.php \
    backend/tests/LivePostgres/E2ELiveMutationGuardTest.php \
    backend/tests/Support/PostgresSubstitutionHarness.php
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 13
  git commit -m "test(e2e): add create-only PostgreSQL fixture"
  ```

  Run the global writer-boundary checks.

### Task 5: Start the full stack and repair all seven browser journeys

**Files:**
- Create: `tests/e2e/support/e2e.ts`
- Create: `tests/e2e/fixtures/merchant.ts`
- Create: `tests/e2e/auth.setup.ts`
- Create: `tests/e2e/auth.smoke.spec.ts`
- Modify: `playwright.config.ts:3-24`
- Modify: `vite.config.ts:12-25`
- Modify: `src/components/forms/TextInput.tsx:10-33`
- Modify: `src/components/forms/SelectInput.tsx:15-34`
- Modify: `src/pages/pos/POSPage.tsx:103-135`
- Replace: `tests/e2e/navigation.spec.ts`
- Replace: `tests/e2e/interactions.spec.ts`
- Replace: `tests/e2e/localization.spec.ts`

**Interfaces:** Playwright starts the create-only attested Laravel/PostgreSQL E2E supervisor and strict-port Vite server, uses one worker and zero retries, separates API authentication setup from a real UI login smoke, retains failure artifacts, and restores English/QAR after every journey. The existing seven journeys remain seven tests; setup and login smoke make the full run nine passing tests. The supervisor variables are required inputs and never receive defaults in repository code.

- [ ] **Run the new smoke path red against the current Vite-only harness.**

  ```bash
  npm run test:e2e -- tests/e2e/auth.smoke.spec.ts
  ```

  Expected failure: the file is absent; after it is added but before the harness is replaced, login cannot reach a reset/seeded Laravel backend.

- [ ] **Create the shared constants.**

  ```ts
  import { resolve } from 'node:path';

  export const frontendUrl = 'http://127.0.0.1:3000';
  export const backendUrl = 'http://127.0.0.1:8000';
  export const merchantEmail = 'owner@e2e.dorzak.test';
  export const merchantPassword = 'e2e-password';
  export const tokenKey = 'dorzak-token';
  export const storageStatePath = resolve('test-results/auth/merchant.json');
  ```

- [ ] **Replace `playwright.config.ts` completely.**

  ```ts
  import { defineConfig, devices } from '@playwright/test';
  import {
    backendUrl,
    frontendUrl,
    storageStatePath,
  } from './tests/e2e/support/e2e';

  const required = (name: string): string => {
    const value = process.env[name];
    if (!value) throw new Error(`Missing required E2E input: ${name}`);
    return value;
  };

  const e2eSupervisorEnv = Object.fromEntries([
    'P00_E2E_SUPERVISOR_DB_URL',
    'P00_E2E_SERVICE_LIFECYCLE_ID',
    'P00_E2E_SERVICE_ATTESTATION_PATH',
    'P00_E2E_SERVICE_ATTESTATION_SHA256',
    'P00_PG_IDENTITY',
    'P00_PG_INSTANCE_NONCE_SHA256',
  ].map((name) => [name, required(name)]));

  export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    forbidOnly: true,
    retries: 0,
    workers: 1,
    reporter: [['line'], ['json', { outputFile: 'test-results/results.json' }]],
    use: {
      baseURL: frontendUrl,
      trace: 'retain-on-failure',
      screenshot: 'only-on-failure',
      video: 'retain-on-failure',
    },
    projects: [
      { name: 'setup', testMatch: /auth\.setup\.ts/ },
      {
        name: 'chromium',
        dependencies: ['setup'],
        testIgnore: [/auth\.setup\.ts/, /auth\.smoke\.spec\.ts/],
        use: { ...devices['Desktop Chrome'], storageState: storageStatePath },
      },
      {
        name: 'login-smoke',
        testMatch: /auth\.smoke\.spec\.ts/,
        use: {
          ...devices['Desktop Chrome'],
          storageState: { cookies: [], origins: [] },
        },
      },
    ],
    webServer: [
      {
        command: 'php artisan e2e:serve --host=127.0.0.1 --port=8000 --no-interaction',
        cwd: './backend',
        url: `${backendUrl}/up`,
        reuseExistingServer: false,
        env: {
          ...e2eSupervisorEnv,
          APP_ENV: 'e2e',
          P00_E2E_PHASE: 'supervisor',
          APP_KEY: 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
          APP_URL: backendUrl,
          FRONTEND_URL: frontendUrl,
          CACHE_STORE: 'array',
          SESSION_DRIVER: 'array',
          QUEUE_CONNECTION: 'sync',
          MAIL_MAILER: 'array',
        },
      },
      {
        command: 'npm run dev -- --host 127.0.0.1 --strictPort',
        url: frontendUrl,
        reuseExistingServer: false,
      },
    ],
  });
  ```

  Replace `vite.config.ts` completely so the strict port and both existing proxies are explicit:

  ```ts
  import react from '@vitejs/plugin-react';
  import path from 'node:path';
  import { defineConfig } from 'vite';

  export default defineConfig({
    plugins: [react()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './src'),
      },
    },
    server: {
      host: '127.0.0.1',
      port: 3000,
      strictPort: true,
      open: false,
      allowedHosts: true,
      proxy: {
        '/api': {
          target: 'http://127.0.0.1:8000',
          changeOrigin: true,
        },
        '/storage': {
          target: 'http://127.0.0.1:8000',
          changeOrigin: true,
        },
      },
    },
  });
  ```

- [ ] **Create separate API setup and real-login smoke.**

  `auth.setup.ts`:

  ```ts
  import { expect, test as setup } from '@playwright/test';
  import {
    backendUrl,
    frontendUrl,
    merchantEmail,
    merchantPassword,
    storageStatePath,
    tokenKey,
  } from './support/e2e';

  setup('authenticate the deterministic merchant', async ({ page, request }) => {
    const response = await request.post(`${backendUrl}/api/v1/auth/login`, {
      data: { email: merchantEmail, password: merchantPassword, device_name: 'playwright-setup' },
    });
    expect(response.ok()).toBeTruthy();
    const payload = (await response.json()) as { data: { token: string } };
    expect(payload.data.token).toMatch(/\|/);

    await page.goto(frontendUrl);
    await page.evaluate(
      ({ key, token }) => localStorage.setItem(key, token),
      { key: tokenKey, token: payload.data.token },
    );
    await page.goto('/checkout');
    await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toBeVisible();
    await page.context().storageState({ path: storageStatePath });
  });
  ```

  `auth.smoke.spec.ts`:

  ```ts
  import { expect, test } from '@playwright/test';
  import { merchantEmail, merchantPassword } from './support/e2e';

  test('a merchant signs in through the real UI', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login$/);
    await page.getByLabel('Email').fill(merchantEmail);
    await page.getByLabel('Password').fill(merchantPassword);
    await page.getByRole('button', { name: 'Sign In' }).click();
    await expect(page).toHaveURL(/\/checkout$/);
    await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toBeVisible();
    await expect(page.locator('.business-context')).toContainText('QAR');
  });
  ```

- [ ] **Create the auto-restoring merchant fixture.**

  ```ts
  import { expect, test as base } from '@playwright/test';
  import { backendUrl, merchantEmail, merchantPassword } from '../support/e2e';

  type MerchantFixtures = { restoreCanonicalSettings: void };

  export const test = base.extend<MerchantFixtures>({
    restoreCanonicalSettings: [
      async ({ request }, use) => {
        const login = await request.post(`${backendUrl}/api/v1/auth/login`, {
          data: { email: merchantEmail, password: merchantPassword, device_name: 'playwright-reset' },
        });
        expect(login.ok()).toBeTruthy();
        const token = ((await login.json()) as { data: { token: string } }).data.token;
        const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' };

        try {
          await use();
        } finally {
          const general = await request.put(`${backendUrl}/api/v1/settings/general`, {
            headers,
            data: {
              business_name: 'Dorzak E2E Merchant',
              tagline: 'Deterministic browser fixture',
              phone: null,
              whatsapp: null,
              language: 'en',
            },
          });
          const currency = await request.put(`${backendUrl}/api/v1/settings/currency`, {
            headers,
            data: { currency: 'QAR', symbol_placement: 'BEFORE' },
          });
          expect(general.ok()).toBeTruthy();
          expect(currency.ok()).toBeTruthy();
        }
      },
      { auto: true },
    ],
  });

  export { expect } from '@playwright/test';
  ```

- [ ] **Make the shared labels and POS product action semantic.**

  Replace `TextInput.tsx` completely:

  ```tsx
  import React from 'react';
  import { AppIcon, IconName } from '../icons/AppIcon';

  interface TextInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    icon?: IconName;
  }

  export const TextInput: React.FC<TextInputProps> = ({
    label,
    error,
    icon,
    id: providedId,
    className = '',
    ...props
  }) => {
    const generatedId = React.useId();
    const inputId = providedId ?? generatedId;
    const errorId = error ? `${inputId}-error` : undefined;

    return (
      <div className="form-group">
        {label && (
          <label className="form-label" htmlFor={inputId}>
            {label}
          </label>
        )}
        <div style={{ position: 'relative', display: 'flex', alignItems: 'center' }}>
          {icon && (
            <div style={{ position: 'absolute', left: '12px', color: 'var(--text-muted)' }}>
              <AppIcon name={icon} size={16} />
            </div>
          )}
          <input
            id={inputId}
            className={`form-input ${className}`}
            style={{ paddingLeft: icon ? '36px' : '12px' }}
            aria-invalid={error ? true : undefined}
            aria-describedby={errorId}
            {...props}
          />
        </div>
        {error && (
          <span
            id={errorId}
            role="alert"
            style={{ fontSize: '0.75rem', color: 'var(--dorzak-danger)' }}
          >
            {error}
          </span>
        )}
      </div>
    );
  };
  ```

  Replace `SelectInput.tsx` completely:

  ```tsx
  import React from 'react';

  interface Option {
    value: string;
    label: string;
    disabled?: boolean;
  }

  interface SelectInputProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
    label?: string;
    options: Option[];
    error?: string;
  }

  export const SelectInput: React.FC<SelectInputProps> = ({
    label,
    options,
    error,
    id: providedId,
    className = '',
    ...props
  }) => {
    const generatedId = React.useId();
    const inputId = providedId ?? generatedId;
    const errorId = error ? `${inputId}-error` : undefined;

    return (
      <div className="form-group">
        {label && (
          <label className="form-label" htmlFor={inputId}>
            {label}
          </label>
        )}
        <select
          id={inputId}
          className={`form-select ${className}`}
          aria-invalid={error ? true : undefined}
          aria-describedby={errorId}
          {...props}
        >
          {options.map((option) => (
            <option key={option.value} value={option.value} disabled={option.disabled}>
              {option.label}
            </option>
          ))}
        </select>
        {error && (
          <span
            id={errorId}
            role="alert"
            style={{ fontSize: '0.75rem', color: 'var(--dorzak-danger)' }}
          >
            {error}
          </span>
        )}
      </div>
    );
  };
  ```

  Replace the complete `filteredProducts.map` block at `POSPage.tsx:102-136` with:

  ```tsx
  {filteredProducts.map((product) => (
    <button
      type="button"
      key={product.id}
      aria-label={`Choose ${product.name}`}
      onClick={() => {
        if (product.variants.length) {
          openModal('VARIANT_SELECT', { product });
        } else {
          addItem(product);
          addToast(`Added "${product.name}" to cart`, 'info');
        }
      }}
      className="card"
      style={{
        padding: '12px',
        cursor: 'pointer',
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'space-between',
        transition: 'transform 0.15s ease, box-shadow 0.15s ease',
        textAlign: 'left',
        width: '100%',
      }}
    >
      <div
        style={{
          height: '110px',
          borderRadius: '6px',
          overflow: 'hidden',
          backgroundColor: 'var(--color-bg)',
          marginBottom: '8px',
        }}
      >
        <img
          src={
            product.imageUrl ||
            'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=300'
          }
          alt={product.name}
          style={{ width: '100%', height: '100%', objectFit: 'cover' }}
        />
      </div>
      <div>
        <h5 style={{ margin: '0 0 4px 0', fontSize: '0.9rem', fontWeight: 600 }}>
          {product.name}
        </h5>
        <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
          SKU: {product.code}
        </span>
      </div>
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          marginTop: '8px',
        }}
      >
        <strong style={{ color: 'var(--dorzak-primary)', fontSize: '1rem' }}>
          {money(product.price)}
        </strong>
        <span
          style={{
            fontSize: '0.75rem',
            padding: '2px 6px',
            borderRadius: '4px',
            backgroundColor: 'var(--color-bg)',
            color: 'var(--text-muted)',
          }}
        >
          Stock: {product.stock}
        </span>
      </div>
    </button>
  ))}
  ```

- [ ] **Replace the navigation and interaction tests with current behavior.**

  `navigation.spec.ts` contains exactly two tests:

  ```ts
  import { expect, test } from './fixtures/merchant';

  test('redirects to checkout and renders the protected shell', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/checkout$/);
    await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toContainText('Dorzak Merchant');
  });

  test('navigates through every merchant route semantically', async ({ page }) => {
    await page.goto('/checkout');
    const routes = [
      ['Products', '/products', 'Products Catalog'],
      ['Orders', '/orders', 'Orders'],
      ['Online Catalog', '/catalog', 'Online Storefront Customizer'],
      ['Customers', '/customers', 'Customers'],
      ['Transactions', '/sales', 'Sales Transactions Log'],
      ['Finances', '/finances', 'Finances & Cash Flow'],
      ['Analytics', '/analytics', 'Analytics & Business Reports'],
      ['Users', '/users', 'Users & Staff Management'],
      ['Settings', '/config', 'General Store Settings'],
    ] as const;

    for (const [name, path, heading] of routes) {
      const link = page.getByRole('link', { name, exact: true });
      await link.click();
      await expect(page).toHaveURL(new RegExp(`${path}$`));
      await expect(link).toHaveAttribute('aria-current', 'page');
      await expect(page.getByRole('heading', { name: heading, exact: true })).toBeVisible();
    }
  });
  ```

  `interactions.spec.ts` contains exactly two tests:

  ```ts
  import { expect, test } from './fixtures/merchant';

  test.beforeEach(async ({ page }) => page.goto('/checkout'));

  test('selects a hoodie variant and charges in QAR', async ({ page }) => {
    await page.getByRole('button', { name: 'Choose Dorzak Signature Cotton Hoodie' }).click();
    const dialog = page.getByRole('dialog', { name: 'Choose Dorzak Signature Cotton Hoodie options' });
    await dialog.getByRole('button', { name: 'Small', exact: true }).click();
    await dialog.getByRole('button', { name: 'Black', exact: true }).click();
    await dialog.getByRole('button', { name: 'Add to Cart • QAR 49.99' }).click();
    await expect(page.getByText('Dorzak Signature Cotton Hoodie').last()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Charge QAR 49.99' })).toBeEnabled();
  });

  test('opens the current product creation dialog', async ({ page }) => {
    const posAddProduct = page
      .getByRole('main')
      .getByRole('button', { name: 'Add Product', exact: true });
    await expect(posAddProduct).toHaveCount(1);
    await posAddProduct.click();
    await expect(page.getByRole('dialog', { name: 'Create Production Product' })).toBeVisible();
    await expect(page).toHaveURL(/\/checkout$/);
  });
  ```

- [ ] **Repair the three localization journeys without local-state leakage.**

  Replace `localization.spec.ts` completely:

  ```ts
  import { expect, test } from './fixtures/merchant';

  async function enableArabic(page: import('@playwright/test').Page) {
    await page.goto('/config');
    await page.getByLabel('Interface Language').selectOption('ar');
    await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    await page.getByRole('button', { name: 'حفظ التغييرات' }).click();
    await expect(page.getByText('تم حفظ الإعدادات بنجاح!')).toBeVisible();
  }

  test('switches the persisted interface to Arabic RTL and Qatari riyals', async ({ page }) => {
    await enableArabic(page);
    await page.getByRole('button', { name: 'العملة', exact: true }).click();
    await page.getByLabel('عملة المتجر').selectOption('QAR');
    await page.getByRole('button', { name: 'حفظ التغييرات' }).click();
    await expect(page.getByText('تم حفظ الإعدادات بنجاح!')).toBeVisible();
    await expect(page.locator('.business-context')).toContainText('QAR');
    await page.goto('/checkout');
    await expect(page.getByText('QAR 49.99').first()).toBeVisible();
    await page.goto('/config');
    await page.getByLabel('لغة الواجهة').selectOption('en');
    await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
  });

  test('translates every primary application section into Arabic', async ({ page }) => {
    await enableArabic(page);
    const routes = [
      ['/checkout', 'السلة فارغة'],
      ['/products', 'كتالوج المنتجات'],
      ['/products/create', 'بيانات المنتج الأساسية'],
      ['/categories', 'فئات المنتجات'],
      ['/orders', 'سجل الطلبات والمبيعات'],
      ['/customers', 'إجمالي العملاء'],
      ['/sales', 'سجل معاملات المبيعات'],
      ['/finances', 'المالية والتدفق النقدي'],
      ['/analytics', 'التحليلات وتقارير الأعمال'],
      ['/catalog', 'تخصيص المتجر الإلكتروني'],
      ['/users', 'إدارة المستخدمين والموظفين'],
      ['/billing', 'الخطط والاشتراك'],
      ['/catalog/preview', 'كل المنتجات'],
    ] as const;

    for (const [route, arabicText] of routes) {
      await page.goto(route);
      await expect(page.locator('main')).toContainText(arabicText);
      await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    }
  });

  test('translates every Settings subsection into Arabic', async ({ page }) => {
    await enableArabic(page);
    const tabs = [
      ['بيانات النشاط', 'بيانات النشاط التجاري'],
      ['العملة', 'العملة والتنسيق'],
      ['الضرائب', 'إعداد ضريبة المبيعات'],
      ['الإيصالات', 'تخصيص الإيصال'],
      ['المدفوعات', 'طرق الدفع'],
      ['التكاملات', 'تكاملات الجهات الخارجية'],
      ['المستخدمون والموظفون', 'إدارة المستخدمين والموظفين'],
      ['الاشتراك', 'الاشتراك والخطة'],
    ] as const;

    for (const [tab, arabicHeading] of tabs) {
      await page.getByRole('button', { name: tab, exact: true }).click();
      await expect(page.locator('main')).toContainText(arabicHeading);
    }
  });
  ```

- [ ] **Verify setup, smoke, the seven journeys, and full determinism.**

  ```bash
  npm run test:e2e -- --project=login-smoke
  npm run test:e2e -- --project=chromium
  node --input-type=module <<'NODE'
  import { readFileSync } from 'node:fs';
  const report = JSON.parse(readFileSync('test-results/results.json', 'utf8'));
  const tests = [];
  const visit = (suite) => {
    for (const spec of suite.specs ?? []) tests.push(...(spec.tests ?? []));
    for (const child of suite.suites ?? []) visit(child);
  };
  for (const suite of report.suites ?? []) visit(suite);
  const count = (project) => tests.filter((test) => test.projectName === project).length;
  if (count('setup') !== 1 || count('chromium') !== 7 || tests.length !== 8) process.exit(1);
  if (tests.some((test) => test.expectedStatus !== 'passed'
      || test.results.length !== 1
      || test.results[0].status !== 'passed'
      || test.results[0].retry !== 0)) process.exit(1);
  console.log('P00_PLAYWRIGHT_PROJECTS PASS setup=1 chromium=7 total=8 retries=0');
  NODE
  npm run test:e2e
  node --input-type=module <<'NODE'
  import { readFileSync } from 'node:fs';
  const report = JSON.parse(readFileSync('test-results/results.json', 'utf8'));
  const tests = [];
  const visit = (suite) => {
    for (const spec of suite.specs ?? []) tests.push(...(spec.tests ?? []));
    for (const child of suite.suites ?? []) visit(child);
  };
  for (const suite of report.suites ?? []) visit(suite);
  const counts = Object.fromEntries(['setup', 'chromium', 'login-smoke'].map((project) => [
    project,
    tests.filter((test) => test.projectName === project).length,
  ]));
  if (counts.setup !== 1 || counts.chromium !== 7 || counts['login-smoke'] !== 1 || tests.length !== 9) process.exit(1);
  if (report.stats.unexpected !== 0 || report.stats.flaky !== 0 || report.stats.skipped !== 0) process.exit(1);
  if (tests.some((test) => test.expectedStatus !== 'passed'
      || test.results.length !== 1
      || test.results[0].status !== 'passed'
      || test.results[0].retry !== 0)) process.exit(1);
  console.log('P00_PLAYWRIGHT_FULL PASS setup=1 chromium=7 login-smoke=1 total=9 retries=0');
  NODE
  ```

  Expected: login smoke reports one pass. The Chromium selection reports eight passes because Playwright runs its one setup dependency plus the seven existing journeys; the first checker proves the project split. The full run reports nine passes and the second checker proves one setup, seven journeys, one login smoke, one result per test, zero retries, and zero skipped/flaky/unexpected tests. Laravel and Vite health failures remain distinct from auth and journey failures.

- [ ] **Stage only the exact browser allowlist and commit.**

  ```bash
  git add -- \
    playwright.config.ts vite.config.ts \
    src/components/forms/TextInput.tsx src/components/forms/SelectInput.tsx src/pages/pos/POSPage.tsx \
    tests/e2e/support/e2e.ts tests/e2e/fixtures/merchant.ts \
    tests/e2e/auth.setup.ts tests/e2e/auth.smoke.spec.ts \
    tests/e2e/navigation.spec.ts tests/e2e/interactions.spec.ts tests/e2e/localization.spec.ts
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 12
  git commit -m "test(e2e): run and repair full merchant journeys"
  ```

  Run the global writer-boundary checks.

### Task 6: Establish the single-owner frontend quality toolchain

**Files:**
- Modify: `package.json:6-31`
- Regenerate: `package-lock.json`
- Modify: `tsconfig.json:28`
- Create: `vitest.config.ts`
- Create: `.eslintrc.cjs`
- Create: `.prettierrc.json`
- Create: `.prettierignore`
- Create: `src/test/setup.ts`
- Create: `src/test/setup.test.ts`
- Create: `src/test/axe.ts`

**Interfaces:** `npm ci` is the only installation mode after this commit. Commands are `format`, `format:check`, `lint`, `typecheck`, `test:unit`, `test:unit:watch`, `build`, `bundle:check`, `quality:frontend`, and `test:e2e`. The approved Node pin must already match Task 1.

- [ ] **Prove the unit/static interface is absent.**

  ```bash
  npm run test:unit
  npm run lint
  npm run format:check
  ```

  Expected: each exits nonzero with a missing-script error.

- [ ] **Install one exact compatible tool tuple under the approved Node runtime.**

  ```bash
  test "$(node -p 'process.versions.node')" = "$(cat .node-version)"
  npm install --save-dev --save-exact \
    vitest@2.1.9 jsdom@25.0.1 \
    @testing-library/dom@10.4.0 @testing-library/react@16.1.0 \
    @testing-library/jest-dom@6.6.3 @testing-library/user-event@14.5.2 \
    axe-core@4.10.2 eslint@8.57.1 \
    @typescript-eslint/parser@6.21.0 @typescript-eslint/eslint-plugin@6.21.0 \
    eslint-plugin-react-hooks@4.6.2 prettier@3.4.2
  ```

  Expected: install exits `0`, changes only `package.json` and `package-lock.json`, and `npm ci` succeeds from the resulting lockfile.

- [ ] **Replace the scripts object with the complete command interface.**

  ```json
  "scripts": {
    "dev": "vite",
    "build": "tsc --noEmit && vite build",
    "preview": "vite preview",
    "format": "prettier --no-error-on-unmatched-pattern --write \"src/**/*.{ts,tsx,css}\" \"tests/**/*.{ts,tsx}\" playwright.config.ts vite.config.ts vitest.config.ts tsconfig.json package.json \"scripts/**/*.mjs\"",
    "format:check": "prettier --no-error-on-unmatched-pattern --check \"src/**/*.{ts,tsx,css}\" \"tests/**/*.{ts,tsx}\" playwright.config.ts vite.config.ts vitest.config.ts tsconfig.json package.json \"scripts/**/*.mjs\"",
    "lint": "eslint src tests playwright.config.ts vite.config.ts vitest.config.ts --ext .ts,.tsx --max-warnings=0",
    "typecheck": "tsc --noEmit",
    "test:unit": "vitest run",
    "test:unit:watch": "vitest",
    "bundle:check": "node scripts/quality/p00.mjs bundle dist 216700",
    "quality:frontend": "npm run format:check && npm run lint && npm run typecheck && npm run test:unit && npm run build && npm run bundle:check",
    "test:e2e": "playwright test"
  }
  ```

  Change `tsconfig.json` to:

  ```json
  "include": ["src", "tests", "playwright.config.ts", "vite.config.ts", "vitest.config.ts"]
  ```

- [ ] **Create exact Vitest and static-tool configuration.**

  `vitest.config.ts`:

  ```ts
  import react from '@vitejs/plugin-react';
  import path from 'node:path';
  import { defineConfig } from 'vitest/config';

  export default defineConfig({
    plugins: [react()],
    resolve: { alias: { '@': path.resolve(__dirname, './src') } },
    test: {
      environment: 'jsdom',
      globals: true,
      setupFiles: ['./src/test/setup.ts'],
      include: ['src/**/*.test.{ts,tsx}'],
      exclude: ['tests/e2e/**', 'node_modules/**', 'dist/**', 'playwright-report/**', 'test-results/**'],
      clearMocks: true,
      restoreMocks: true,
    },
  });
  ```

  `.eslintrc.cjs`:

  ```js
  module.exports = {
    root: true,
    env: { browser: true, es2022: true, node: true },
    parser: '@typescript-eslint/parser',
    parserOptions: { ecmaVersion: 'latest', sourceType: 'module' },
    plugins: ['@typescript-eslint', 'react-hooks'],
    rules: {
      'no-debugger': 'error',
      'no-dupe-keys': 'error',
      'no-duplicate-case': 'error',
      'no-func-assign': 'error',
      'no-import-assign': 'error',
      'no-unreachable': 'error',
      'no-unsafe-finally': 'error',
      'use-isnan': 'error',
      'valid-typeof': 'error',
      'react-hooks/rules-of-hooks': 'error'
    }
  };
  ```

  `.prettierrc.json`:

  ```json
  { "singleQuote": true, "trailingComma": "all", "printWidth": 100 }
  ```

  `.prettierignore`:

  ```text
  node_modules
  dist
  playwright-report
  test-results
  .artifacts
  backend
  docs
  outputs
  ```

  `src/test/setup.ts` and smoke test:

  ```ts
  import '@testing-library/jest-dom/vitest';
  ```

  ```ts
  import { expect, test } from 'vitest';

  test('unit tests run in a DOM', () => {
    document.body.innerHTML = '<main>Dorzak</main>';
    expect(document.querySelector('main')).toHaveTextContent('Dorzak');
  });
  ```

  `src/test/axe.ts`:

  ```ts
  import axe from 'axe-core';
  import { expect } from 'vitest';

  export async function expectNoA11yViolations(container: Element): Promise<void> {
    const result = await axe.run(container);
    expect(result.violations.map(({ id, impact, nodes }) => ({ id, impact, nodes: nodes.length }))).toEqual([]);
  }
  ```

- [ ] **Verify lock-only install and the initial unit seam.**

  ```bash
  npm ci
  npx prettier --write package.json
  npm run test:unit -- src/test/setup.test.ts
  npm run typecheck
  npm run lint
  ```

  Expected: one unit test passes; typecheck and lint exit `0`. Formatting is deliberately completed as a separate boundary in Task 8.

- [ ] **Stage only the manifest/toolchain allowlist and commit.**

  ```bash
  git add -- package.json package-lock.json tsconfig.json vitest.config.ts \
    .eslintrc.cjs .prettierrc.json .prettierignore \
    src/test/setup.ts src/test/setup.test.ts src/test/axe.ts
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 10
  git commit -m "test(frontend): establish unit and static quality lane"
  ```

  The frontend manifest owner is now closed. No later task edits either npm manifest. Run the global writer-boundary checks.

### Task 7: Characterize DTO, API-error, auth, settings, and money behavior

**Files:**
- Create: `src/api/adapters.test.ts`
- Create: `src/api/apiClient.test.ts`
- Create: `src/stores/authStore.test.ts`
- Create: `src/stores/settingsStore.test.ts`
- Create: `src/hooks/useMoney.test.ts`
- Modify: `src/hooks/useMoney.ts:1-16`

**Interfaces:** Tests pin numeric DTO normalization, `/storage` media preservation, QAR order snapshots, settings group payloads, normalized API errors/token clearing, auth bootstrap/login state, server-backed language/direction, and pure money formatting.

- [ ] **Create all five focused test files completely before the pure money export.**

  `src/api/adapters.test.ts`:

  ```ts
  import { describe, expect, test } from 'vitest';
  import { initialAccountInfo } from '../data/mockData';
  import { settingsGroupPayloads, toOrder, toProduct } from './adapters';

  describe('API adapters', () => {
    test('normalizes DTO numbers, media, QAR orders, and settings payloads', () => {
      expect(toProduct({ id: 7, name: 'Hoodie', price: '49.99', cost: null, stock: '10', min_stock: 2, track_stock: true, image_url: '/storage/a.jpg' })).toMatchObject({
        id: '7', price: 49.99, cost: 0, stock: 10, imageUrl: '/storage/a.jpg',
      });
      expect(toOrder({ id: 2, order_number: 'ORD-1000', customer_name: 'Walk-in', subtotal: '49.99', discount: 0, tax_amount: 0, total: '49.99', status: 'COMPLETE', payment_method: 'CASH', currency_code: 'QAR', items: [] })).toMatchObject({
        id: 'ORD-1000', total: 49.99, currencyCode: 'QAR',
      });
      expect(settingsGroupPayloads(
        { ...initialAccountInfo, currency: 'QAR', symbolPlacement: 'BEFORE' },
        { currency: 'QAR' },
      )).toEqual([['currency', { currency: 'QAR', symbol_placement: 'BEFORE' }]]);
    });
  });
  ```

  `src/api/apiClient.test.ts`:

  ```ts
  import { afterEach, expect, test, vi } from 'vitest';
  import { getToken, request, setToken } from './apiClient';

  afterEach(() => {
    setToken(null);
    vi.unstubAllGlobals();
    window.history.replaceState({}, '', '/');
  });

  test('normalizes API errors and clears an expired token', async () => {
    window.history.replaceState({}, '', '/login');
    const fetchMock = vi.fn()
      .mockResolvedValueOnce(new Response(JSON.stringify({
        message: 'Invalid order.',
        code: 'INVALID_ORDER',
        errors: { items: ['Required'] },
      }), { status: 422, headers: { 'Content-Type': 'application/json' } }))
      .mockResolvedValueOnce(new Response(JSON.stringify({
        message: 'Unauthenticated.',
        code: 'AUTH_REQUIRED',
      }), { status: 401, headers: { 'Content-Type': 'application/json' } }));
    vi.stubGlobal('fetch', fetchMock);

    await expect(request('/orders')).rejects.toMatchObject({
      status: 422,
      message: 'Invalid order.',
      code: 'INVALID_ORDER',
      errors: { items: ['Required'] },
    });
    setToken('expired');
    await expect(request('/auth/me')).rejects.toMatchObject({
      status: 401,
      message: 'Unauthenticated.',
    });
    expect(getToken()).toBeNull();
  });
  ```

  `src/stores/authStore.test.ts`:

  ```ts
  import { beforeEach, expect, test, vi } from 'vitest';
  import { setToken } from '../api/apiClient';
  import { authApi } from '../api/endpoints';
  import { useAuthStore } from './authStore';

  vi.mock('../api/endpoints', () => ({
    authApi: { login: vi.fn(), logout: vi.fn(), me: vi.fn(), register: vi.fn() },
    platformApi: { stores: { impersonate: vi.fn() } },
  }));

  beforeEach(() => {
    localStorage.clear();
    vi.clearAllMocks();
    setToken(null);
    useAuthStore.setState({
      user: null,
      store: null,
      role: null,
      abilities: [],
      status: 'idle',
      error: null,
      impersonating: null,
    });
  });

  test('bootstraps guest/authenticated sessions and exposes login validation', async () => {
    await useAuthStore.getState().bootstrap();
    expect(useAuthStore.getState().status).toBe('guest');

    const user = { id: 1, name: 'Owner', email: 'owner@e2e.dorzak.test', is_platform_admin: false };
    const store = { id: 1, name: 'Dorzak', currency: 'QAR', symbol_placement: 'BEFORE', language: 'en', country: 'Qatar' };
    setToken('valid');
    vi.mocked(authApi.me).mockResolvedValue({
      data: { user, store, role: 'OWNER', abilities: ['orders.create'] },
    });
    await useAuthStore.getState().bootstrap();
    expect(useAuthStore.getState()).toMatchObject({
      user,
      store,
      status: 'authenticated',
      role: 'OWNER',
      abilities: ['orders.create'],
    });

    vi.mocked(authApi.login).mockRejectedValue({
      status: 422,
      message: 'Invalid',
      errors: { email: ['Bad credentials'] },
    });
    await expect(useAuthStore.getState().login('bad@example.test', 'bad')).rejects.toBeTruthy();
    expect(useAuthStore.getState().error).toBe('Bad credentials');
  });
  ```

  `src/stores/settingsStore.test.ts`:

  ```ts
  import { beforeEach, expect, test, vi } from 'vitest';
  import { settingsApi } from '../api/endpoints';
  import { initialAccountInfo } from '../data/mockData';
  import { useSettingsStore } from './settingsStore';

  vi.mock('../api/endpoints', () => ({
    settingsApi: { get: vi.fn(), update: vi.fn() },
  }));

  const envelope = (language: 'en' | 'ar', placement: 'BEFORE' | 'AFTER') => ({
    data: {
      general: { business_name: 'Dorzak', language },
      currency: { currency: 'QAR', currency_symbol: 'QAR', symbol_placement: placement },
    },
  });

  beforeEach(() => {
    localStorage.clear();
    vi.clearAllMocks();
    document.documentElement.lang = 'en';
    document.documentElement.dir = 'ltr';
    useSettingsStore.setState({ accountInfo: { ...initialAccountInfo }, loading: false });
  });

  test('hydrates server settings, direction, and grouped currency updates', async () => {
    vi.mocked(settingsApi.get).mockResolvedValue(envelope('ar', 'BEFORE'));
    await useSettingsStore.getState().fetchSettings();
    expect(useSettingsStore.getState().accountInfo.currency).toBe('QAR');
    expect(document.documentElement).toHaveAttribute('lang', 'ar');
    expect(document.documentElement).toHaveAttribute('dir', 'rtl');

    vi.mocked(settingsApi.update).mockResolvedValue(envelope('en', 'AFTER'));
    await useSettingsStore.getState().updateSettings({
      currency: 'QAR',
      symbolPlacement: 'AFTER',
    });
    expect(settingsApi.update).toHaveBeenCalledTimes(1);
    expect(settingsApi.update).toHaveBeenCalledWith('currency', {
      currency: 'QAR',
      symbol_placement: 'AFTER',
    });
    expect(document.documentElement).toHaveAttribute('lang', 'en');
    expect(document.documentElement).toHaveAttribute('dir', 'ltr');
  });
  ```

  `src/hooks/useMoney.test.ts`:

  ```ts
  import { expect, test } from 'vitest';
  import { formatMoney } from './useMoney';

  test('formats placement, overrides, and fraction digits deterministically', () => {
    const before = {
      currency: 'QAR',
      currencySymbol: 'QAR',
      symbolPlacement: 'BEFORE',
    } as const;
    expect(formatMoney(49.99, before)).toBe('QAR 49.99');
    expect(formatMoney(49.99, { ...before, symbolPlacement: 'AFTER' })).toBe('49.99 QAR');
    expect(formatMoney(49.99, before, 2, 'USD')).toBe('$49.99');
    expect(formatMoney(49.99, before, 0)).toBe('QAR 50');
  });
  ```

- [ ] **Run red.**

  ```bash
  npm run test:unit -- src/api/adapters.test.ts src/api/apiClient.test.ts src/stores/authStore.test.ts src/stores/settingsStore.test.ts src/hooks/useMoney.test.ts
  ```

  Expected failure: `formatMoney` is not exported. Any additional failure is a plan defect or a discovered contract mismatch; stop and return its exact assertion rather than weakening it.

- [ ] **Extract the pure formatter and keep the hook as a delegate.**

  Replace `useMoney.ts` with:

  ```ts
  import { useSettingsStore } from '../stores/settingsStore';

  export interface MoneyFormat {
    currency: string;
    currencySymbol: string;
    symbolPlacement: 'BEFORE' | 'AFTER';
  }

  export function formatMoney(
    value: number,
    format: MoneyFormat,
    fractionDigits = 2,
    currencyOverride?: string,
  ): string {
    const amount = value.toLocaleString('en-US', {
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    });
    const code = currencyOverride ?? format.currency;
    const known: Record<string, string> = { QAR: 'QAR', USD: '$', EUR: '€', GBP: '£', CAD: 'CA$', BRL: 'R$', AUD: 'A$' };
    const symbol = known[code] ?? (currencyOverride || format.currencySymbol);
    return format.symbolPlacement === 'AFTER'
      ? `${amount} ${symbol}`
      : `${symbol}${symbol.length > 1 ? ' ' : ''}${amount}`;
  }

  export function useMoney() {
    const format = useSettingsStore((state) => state.accountInfo);
    return (value: number, fractionDigits = 2, currencyOverride?: string) =>
      formatMoney(value, format, fractionDigits, currencyOverride);
  }
  ```

- [ ] **Verify focused and aggregate frontend behavior.**

  ```bash
  npm run test:unit -- src/api/adapters.test.ts src/api/apiClient.test.ts src/stores/authStore.test.ts src/stores/settingsStore.test.ts src/hooks/useMoney.test.ts
  npm run typecheck
  npm run test:unit
  ```

  Expected: five focused tests plus the setup smoke pass; typecheck exits `0`.

- [ ] **Stage exactly six paths and commit.**

  ```bash
  git add -- src/api/adapters.test.ts src/api/apiClient.test.ts \
    src/stores/authStore.test.ts src/stores/settingsStore.test.ts \
    src/hooks/useMoney.test.ts src/hooks/useMoney.ts
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 6
  git commit -m "test(frontend): cover API auth settings and money"
  ```

  Run the global writer-boundary checks.

### Task 8: Prove protected-shell and accessible-form behavior, then format mechanically

**Files:**
- Create: `src/layouts/AppShell.test.tsx`
- Create: `src/components/forms/AccessibleForm.test.tsx`
- Modify: `src/layouts/AppShell.tsx:45-50`
- Modify: `src/components/forms/ToggleSwitch.tsx:17-32`
- Modify: `src/components/forms/CheckboxInput.tsx:18-26`
- Mechanical format scope: `src/**`, `tests/**`, root TypeScript configs, `tsconfig.json`, `scripts/**/*.mjs`; both npm manifests must remain byte-identical

**Interfaces:** Loading is a polite status; guest and platform-only sessions redirect; authenticated sessions hydrate settings before domain fetches; form labels/errors are associated; toggle uses a native button with `role=switch`; checkbox contains a native input; one keyboard-operable form has zero axe violations.

- [ ] **Write the two failing component tests.**

  `src/layouts/AppShell.test.tsx`:

  ```tsx
  import { render, screen, waitFor } from '@testing-library/react';
  import { MemoryRouter, Route, Routes } from 'react-router-dom';
  import { beforeEach, expect, test, vi } from 'vitest';
  import { AppShell } from './AppShell';

  const doubles = vi.hoisted(() => ({
    auth: {
      status: 'idle' as 'idle' | 'loading' | 'guest' | 'authenticated',
      store: null as null | { id: number; name: string },
      user: null as null | { is_platform_admin: boolean },
    },
    bootstrap: vi.fn(),
    fetchSettings: vi.fn(),
    fetchProducts: vi.fn(),
    fetchCategories: vi.fn(),
    fetchCustomers: vi.fn(),
    fetchOrders: vi.fn(),
  }));

  vi.mock('../stores/authStore', () => ({
    useAuthStore: () => ({ ...doubles.auth, bootstrap: doubles.bootstrap }),
  }));
  vi.mock('../stores/productStore', () => ({
    useProductStore: () => ({
      fetchProducts: doubles.fetchProducts,
      fetchCategories: doubles.fetchCategories,
    }),
  }));
  vi.mock('../stores/customerStore', () => ({
    useCustomerStore: () => ({ fetchCustomers: doubles.fetchCustomers }),
  }));
  vi.mock('../stores/orderStore', () => ({
    useOrderStore: () => ({ fetchOrders: doubles.fetchOrders }),
  }));
  vi.mock('../stores/settingsStore', () => ({
    useSettingsStore: () => ({ fetchSettings: doubles.fetchSettings }),
  }));
  vi.mock('../hooks/useOrderPolling', () => ({ useOrderPolling: vi.fn() }));
  vi.mock('../components/navigation/Sidebar', () => ({ Sidebar: () => null }));
  vi.mock('../components/navigation/Topbar', () => ({ Topbar: () => null }));
  vi.mock('../components/navigation/ImpersonationBanner', () => ({ ImpersonationBanner: () => null }));
  vi.mock('../components/modals/ModalHost', () => ({ ModalHost: () => null }));
  vi.mock('../components/feedback/ToastHost', () => ({ ToastHost: () => null }));

  function renderShell() {
    return render(
      <MemoryRouter initialEntries={['/']}>
        <Routes>
          <Route path="/" element={<AppShell />}>
            <Route index element={<h1>Merchant route</h1>} />
          </Route>
          <Route path="/login" element={<h1>Login route</h1>} />
          <Route path="/platform" element={<h1>Platform route</h1>} />
        </Routes>
      </MemoryRouter>,
    );
  }

  beforeEach(() => {
    vi.clearAllMocks();
    doubles.auth.status = 'idle';
    doubles.auth.store = null;
    doubles.auth.user = null;
  });

  test('guards shell states and hydrates merchant data with settings first', async () => {
    let view = renderShell();
    expect(screen.getByRole('status', { name: 'Loading your store…' })).toBeInTheDocument();
    await waitFor(() => expect(doubles.bootstrap).toHaveBeenCalledTimes(1));
    view.unmount();

    doubles.auth.status = 'guest';
    view = renderShell();
    expect(await screen.findByRole('heading', { name: 'Login route' })).toBeInTheDocument();
    view.unmount();

    doubles.auth.status = 'authenticated';
    doubles.auth.user = { is_platform_admin: true };
    doubles.auth.store = null;
    view = renderShell();
    expect(await screen.findByRole('heading', { name: 'Platform route' })).toBeInTheDocument();
    view.unmount();

    doubles.auth.user = { is_platform_admin: false };
    doubles.auth.store = { id: 1, name: 'Dorzak' };
    renderShell();
    expect(await screen.findByRole('heading', { name: 'Merchant route' })).toBeInTheDocument();
    await waitFor(() => {
      expect(doubles.fetchSettings).toHaveBeenCalledTimes(1);
      expect(doubles.fetchProducts).toHaveBeenCalledTimes(1);
      expect(doubles.fetchCategories).toHaveBeenCalledTimes(1);
      expect(doubles.fetchCustomers).toHaveBeenCalledTimes(1);
      expect(doubles.fetchOrders).toHaveBeenCalledTimes(1);
    });
    const settingsCall = doubles.fetchSettings.mock.invocationCallOrder[0];
    for (const fetchDomain of [
      doubles.fetchProducts,
      doubles.fetchCategories,
      doubles.fetchCustomers,
      doubles.fetchOrders,
    ]) {
      expect(settingsCall).toBeLessThan(fetchDomain.mock.invocationCallOrder[0]);
    }
  });
  ```

  `src/components/forms/AccessibleForm.test.tsx`:

  ```tsx
  import { render, screen } from '@testing-library/react';
  import userEvent from '@testing-library/user-event';
  import { useState } from 'react';
  import { expect, test } from 'vitest';
  import { expectNoA11yViolations } from '../../test/axe';
  import { CheckboxInput } from './CheckboxInput';
  import { SelectInput } from './SelectInput';
  import { TextInput } from './TextInput';
  import { ToggleSwitch } from './ToggleSwitch';

  function AccessibleForm() {
    const [online, setOnline] = useState(false);
    const [receipts, setReceipts] = useState(false);

    return (
      <form aria-label="Store preferences">
        <TextInput label="Name" error="Name is required" />
        <SelectInput
          label="Currency"
          defaultValue="QAR"
          options={[
            { value: 'QAR', label: 'QAR' },
            { value: 'USD', label: 'USD' },
          ]}
        />
        <ToggleSwitch checked={online} onChange={setOnline} label="Online store" />
        <CheckboxInput checked={receipts} onChange={setReceipts} label="Email receipts" />
      </form>
    );
  }

  test('associates labels and errors and supports keyboard boolean controls', async () => {
    const user = userEvent.setup();
    const { container } = render(<AccessibleForm />);
    const name = screen.getByLabelText('Name');
    expect(screen.getByLabelText('Currency')).toBeInTheDocument();
    const toggle = screen.getByLabelText('Online store');
    const checkbox = screen.getByLabelText('Email receipts');
    const errorId = name.getAttribute('aria-describedby');
    expect(errorId).toBeTruthy();
    expect(document.getElementById(errorId as string)).toHaveTextContent('Name is required');
    toggle.focus();
    await user.keyboard('[Space]');
    expect(toggle).toHaveAttribute('aria-checked', 'true');
    checkbox.focus();
    await user.keyboard('[Space]');
    expect(checkbox).toBeChecked();
    await expectNoA11yViolations(container);
  });
  ```

- [ ] **Run the exact red command.**

  ```bash
  npm run test:unit -- src/layouts/AppShell.test.tsx src/components/forms/AccessibleForm.test.tsx
  ```

  Expected failure: AppShell has no status role; current toggle/checkbox markup is not keyboard-native.

- [ ] **Apply the minimal accessible implementations.**

  AppShell loading block:

  ```tsx
  <div
    role="status"
    aria-label="Loading your store…"
    aria-live="polite"
    style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)' }}
  >
    Loading your store…
  </div>
  ```

  Replace `src/components/forms/ToggleSwitch.tsx` completely:

  ```tsx
  import React from 'react';

  interface ToggleSwitchProps {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label?: string;
    description?: string;
  }

  export const ToggleSwitch: React.FC<ToggleSwitchProps> = ({
    checked,
    onChange,
    label,
    description,
  }) => {
    const generatedId = React.useId();
    const descriptionId = description ? `${generatedId}-description` : undefined;

    return (
      <div className="toggle-wrapper">
        {(label || description) && (
          <div className="flex flex-col">
            {label && <span className="form-label">{label}</span>}
            {description && (
              <span
                id={descriptionId}
                style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}
              >
                {description}
              </span>
            )}
          </div>
        )}
        <button
          type="button"
          role="switch"
          aria-checked={checked}
          aria-label={label}
          aria-describedby={descriptionId}
          onClick={() => onChange(!checked)}
          className={`toggle-switch ${checked ? 'active' : ''}`}
        >
          <span aria-hidden="true" className="toggle-thumb" />
        </button>
      </div>
    );
  };
  ```

  Replace `src/components/forms/CheckboxInput.tsx` completely:

  ```tsx
  import React from 'react';
  import { AppIcon } from '../icons/AppIcon';

  interface CheckboxInputProps {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label?: string;
    className?: string;
  }

  export const CheckboxInput: React.FC<CheckboxInputProps> = ({
    checked,
    onChange,
    label,
    className = '',
  }) => (
  <label
    className={`checkbox-wrapper ${className}`}
    onClick={(event) => event.stopPropagation()}
  >
    <input
      type="checkbox"
      checked={checked}
      onChange={(event) => onChange(event.target.checked)}
      style={{
        position: 'absolute',
        width: 1,
        height: 1,
        overflow: 'hidden',
        clip: 'rect(0 0 0 0)',
        clipPath: 'inset(50%)',
        whiteSpace: 'nowrap',
      }}
    />
    <span aria-hidden="true" className={`checkbox-custom ${checked ? 'checked' : ''}`}>
      {checked && <AppIcon name="check" size={12} color="#ffffff" />}
    </span>
    {label && <span className="form-label" style={{ margin: 0 }}>{label}</span>}
  </label>
  );
  ```

- [ ] **Verify accessible behavior and commit it before formatting.**

  ```bash
  npm run test:unit -- src/layouts/AppShell.test.tsx src/components/forms/AccessibleForm.test.tsx
  npm run test:unit
  npm run typecheck
  git add -- src/layouts/AppShell.test.tsx src/components/forms/AccessibleForm.test.tsx \
    src/layouts/AppShell.tsx src/components/forms/ToggleSwitch.tsx src/components/forms/CheckboxInput.tsx
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 5
  git commit -m "fix(frontend): make shell and shared form accessible"
  ```

  Expected: focused tests pass, all unit tests pass, typecheck exits `0`.

- [ ] **Run the mechanical formatter as its own exact boundary.**

  ```bash
  npx prettier --check package.json
  npm run format
  test -z "$(git diff --name-only -- package.json package-lock.json)"
  test -z "$(git diff --name-only | rg -v '^(src/|tests/|playwright\.config\.ts$|vite\.config\.ts$|vitest\.config\.ts$|tsconfig\.json$|scripts/)')"
  test -z "$(git diff --name-only | rg 'backend/app/Support/MediaUrl\.php|docs/superpowers/plans/2026-07-12-marketing-00-overview\.md')"
  git diff --name-only -z -- src tests playwright.config.ts vite.config.ts vitest.config.ts tsconfig.json scripts | xargs -0 git add --
  git diff --cached --check
  git commit -m "style(frontend): apply baseline formatting"
  ```

  The exact staged path list is the formatter-produced list constrained by the regex; store that list and its SHA-256 in Task 17 evidence. An empty diff is acceptable and skips only the style commit.

- [ ] **Run the complete pre-bundle frontend lane.**

  ```bash
  npm run format:check
  npm run lint
  npm run typecheck
  npm run test:unit
  npm run build
  npm run test:e2e
  ```

  Expected: every command exits `0`; nine browser tests pass with no retry or skip. Run the global writer-boundary checks.

### Task 9: Apply the known 16-file Pint cleanup as an isolated commit

**Files:**
- Modify exactly:
  - `backend/app/Http/Resources/OrderResource.php`
  - `backend/app/Http/Controllers/Api/ReferralController.php`
  - `backend/app/Http/Controllers/Api/CouponController.php`
  - `backend/app/Http/Controllers/Api/LoyaltyController.php`
  - `backend/app/Services/CampaignService.php`
  - `backend/app/Services/MessagingService.php`
  - `backend/app/Services/CouponService.php`
  - `backend/app/Services/SegmentService.php`
  - `backend/tests/Feature/Order/OrderResourceCourierStateTest.php`
  - `backend/tests/Feature/Loyalty/LoyaltyRedemptionTest.php`
  - `backend/tests/Feature/Campaign/CampaignServiceTest.php`
  - `backend/tests/Feature/Marketing/MarketingOverviewTest.php`
  - `backend/tests/Feature/Marketing/MarketingControlsTest.php`
  - `backend/tests/Feature/Marketing/MessagingChannelsTest.php`
  - `backend/tests/Feature/GiftCard/GiftCardWalletTest.php`
  - `backend/routes/api.php`

**Interface:** Pint 1.29.3 reports exactly this set at the planning baseline. The execution base must reproduce the same set before the formatter writes. `backend/app/Support/MediaUrl.php` is explicitly excluded.

- [ ] **Capture and compare the dry-run path set before writing.**

  ```bash
  cd backend
  report="$(mktemp)"
  cache="$(mktemp)"
  vendor/bin/pint --test --cache-file="$cache" --format=json >"$report"; test "$?" = 1
  php -r '$j=json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR); foreach ($j["files"] as $f) echo "backend/",$f["path"],PHP_EOL;' "$report" | LC_ALL=C sort >"$report.paths"
  diff -u <(printf '%s\n' \
    backend/app/Http/Resources/OrderResource.php \
    backend/app/Http/Controllers/Api/ReferralController.php \
    backend/app/Http/Controllers/Api/CouponController.php \
    backend/app/Http/Controllers/Api/LoyaltyController.php \
    backend/app/Services/CampaignService.php \
    backend/app/Services/MessagingService.php \
    backend/app/Services/CouponService.php \
    backend/app/Services/SegmentService.php \
    backend/tests/Feature/Order/OrderResourceCourierStateTest.php \
    backend/tests/Feature/Loyalty/LoyaltyRedemptionTest.php \
    backend/tests/Feature/Campaign/CampaignServiceTest.php \
    backend/tests/Feature/Marketing/MarketingOverviewTest.php \
    backend/tests/Feature/Marketing/MarketingControlsTest.php \
    backend/tests/Feature/Marketing/MessagingChannelsTest.php \
    backend/tests/Feature/GiftCard/GiftCardWalletTest.php \
    backend/routes/api.php | LC_ALL=C sort) "$report.paths"
  ```

  Expected: Pint exits `1`; `diff` exits `0`; path count is 16. Any difference stops this task and returns the new report to the Control Room.

- [ ] **Format only the enumerated relative paths.**

  ```bash
  cd backend
  vendor/bin/pint \
    app/Http/Resources/OrderResource.php \
    app/Http/Controllers/Api/ReferralController.php \
    app/Http/Controllers/Api/CouponController.php \
    app/Http/Controllers/Api/LoyaltyController.php \
    app/Services/CampaignService.php app/Services/MessagingService.php \
    app/Services/CouponService.php app/Services/SegmentService.php \
    tests/Feature/Order/OrderResourceCourierStateTest.php \
    tests/Feature/Loyalty/LoyaltyRedemptionTest.php \
    tests/Feature/Campaign/CampaignServiceTest.php \
    tests/Feature/Marketing/MarketingOverviewTest.php \
    tests/Feature/Marketing/MarketingControlsTest.php \
    tests/Feature/Marketing/MessagingChannelsTest.php \
    tests/Feature/GiftCard/GiftCardWalletTest.php routes/api.php
  ```

  Expected: 16 files fixed; no other path changes.

- [ ] **Verify style and behavior.**

  ```bash
  cd backend
  vendor/bin/pint --test
  php artisan test
  ```

  Expected: Pint exits `0`; SQLite remains `446 passed`.

- [ ] **Stage the same exact 16 paths and commit.**

  ```bash
  git add -- \
    backend/app/Http/Resources/OrderResource.php \
    backend/app/Http/Controllers/Api/ReferralController.php \
    backend/app/Http/Controllers/Api/CouponController.php \
    backend/app/Http/Controllers/Api/LoyaltyController.php \
    backend/app/Services/CampaignService.php backend/app/Services/MessagingService.php \
    backend/app/Services/CouponService.php backend/app/Services/SegmentService.php \
    backend/tests/Feature/Order/OrderResourceCourierStateTest.php \
    backend/tests/Feature/Loyalty/LoyaltyRedemptionTest.php \
    backend/tests/Feature/Campaign/CampaignServiceTest.php \
    backend/tests/Feature/Marketing/MarketingOverviewTest.php \
    backend/tests/Feature/Marketing/MarketingControlsTest.php \
    backend/tests/Feature/Marketing/MessagingChannelsTest.php \
    backend/tests/Feature/GiftCard/GiftCardWalletTest.php backend/routes/api.php
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 16
  test -z "$(git diff --cached --name-only | rg 'MediaUrl\.php')"
  git commit -m "style(backend): apply isolated Pint baseline"
  ```

  Delete only the two temporary files and run the global writer-boundary checks.

### Task 10: Add Larastan/PHPStan with a non-increasing reviewed baseline

**Files:**
- Modify: `backend/composer.json:8-22,35-51`
- Regenerate: `backend/composer.lock`
- Create: `backend/phpstan.neon.dist`
- Generate: `backend/phpstan-baseline.neon`

**Interfaces:** `composer analyse` runs Larastan/PHPStan level 5 over `app`. Unmatched baseline ignores fail; newly generated baseline growth is never automatic. The initial numeric debt is execution-derived under the approved PHP pin and recorded in evidence.

- [ ] **Prove the analyzer is absent.**

  ```bash
  cd backend
  test -x vendor/bin/phpstan
  ```

  Expected failure: exit `1`.

- [ ] **Add the exact dependency constraints and Composer scripts under the approved PHP pin.**

  ```bash
  test "$(php -r 'echo PHP_VERSION;')" = "$(cat ../.php-version)"
  composer require --dev "larastan/larastan:^3.10" "phpstan/phpstan:^2.2" --with-all-dependencies --no-interaction
  ```

  Add these scripts without removing the existing ones:

  ```json
  "pint:check": "pint --test",
  "analyse": "phpstan analyse --configuration=phpstan.neon.dist --no-progress --memory-limit=2G",
  "test:sqlite": "@php artisan test",
  "test:postgres": "../scripts/quality/run-qualified-postgres full"
  ```

  If Composer resolves a Larastan release outside `3.10.*` or PHPStan outside `2.2.*`, stop before staging.

- [ ] **Create the pre-baseline config and run the exact red analysis.**

  ```neon
  includes:
      - vendor/larastan/larastan/extension.neon
      - vendor/nesbot/carbon/extension.neon

  parameters:
      level: 5
      paths:
          - app
      reportUnmatchedIgnoredErrors: true
  ```

  ```bash
  cd backend
  vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --no-progress --memory-limit=2G
  ```

  Expected: exit `1` with legacy diagnostics. Store the exact diagnostic count and output hash. Exit `0`, a crash, or an internal error stops for review because the baseline-generation branch would differ.

- [ ] **Generate, include, and review the deterministic baseline.**

  ```bash
  cd backend
  vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --generate-baseline=phpstan-baseline.neon --no-progress --memory-limit=2G
  ```

  Add `- phpstan-baseline.neon` as the third `includes` entry. Review every generated path/count; reject diagnostics outside `app`. Record the initial budget with:

  ```bash
  rg -n '^\s+count:' phpstan-baseline.neon | wc -l
  ```

  The resulting literal count is the maximum accepted budget. Resolved entries fail as unmatched and must be removed; any added/increased entry requires a separately reviewed baseline revision.

- [ ] **Verify manifest, style, analysis, and SQLite.**

  ```bash
  cd backend
  composer validate --strict --no-check-publish
  composer install --no-interaction --prefer-dist --no-progress
  vendor/bin/pint --test
  composer analyse
  composer test:sqlite
  ```

  Expected: all commands exit `0`; SQLite reports `446 passed`.

- [ ] **Stage only the four static-quality files and commit.**

  ```bash
  git add -- backend/composer.json backend/composer.lock backend/phpstan.neon.dist backend/phpstan-baseline.neon
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 4
  git commit -m "build(backend): add bounded static analysis"
  ```

  The Composer/lock owner is now closed. Run the global writer-boundary checks.

### Task 11: Add the guarded PostgreSQL 16 lane and preserve case-insensitive search

**Files:**
- Create: `backend/phpunit.pgsql.xml`
- Create: `backend/app/Support/PostgresQualificationGuard.php`
- Create: `backend/tests/Support/postgres-bootstrap.php`
- Create: `backend/tests/Postgres/PostgresEnvironmentTest.php`
- Create: `backend/tests/LivePostgres/PostgresQualificationLiveGuardTest.php`
- Create: `backend/tests/LivePostgres/QualificationBootstrapProbeTest.php`
- Create: `scripts/quality/run-qualified-postgres`
- Modify: `backend/app/Support/PostgresConnectionProfile.php`
- Modify: `backend/tests/Support/PostgresSubstitutionHarness.php`
- Modify: `backend/app/Providers/AppServiceProvider.php`
- Modify: `backend/app/Models/Product.php:96-107`
- Modify: `backend/app/Models/Customer.php:47-58`

**Interfaces:** Every successful qualification process is launched only through `scripts/quality/run-qualified-postgres`. The runner has fixed modes and no arbitrary-command escape; it refuses caller-supplied guarded state, obtains a cryptographically unique create-only `_test` database and least-privilege role from the already attested no-real-data supervisor, proves the complete user schema is empty, migrates it once without `migrate:fresh`, and creates a random candidate-bound activation nonce as the final setup mutation. The credential URL exists only in the closed child environment; the nonce exists only there and in the disposable candidate's activation row. A candidate is never reset, wiped, dropped, renamed, reused or printed; local execution and every CI attempt receive different candidates. Task 4's `PostgresConnectionProfile` remains the sole URL, DSN and resolved-Laravel-configuration parser; Task 11 adds no second parser. PHPUnit bootstrap verifies its preliminary PDO and exact activation row, then binds the closed parsed profile, activation nonce and nonsecret live fingerprint in process memory exactly once. Every qualification Laravel application boot asserts that immutable bootstrap authority *before resolving a connection*, installs manager- and connection-level fail-closed reconnectors, validates Laravel's resolved fields (Laravel removes `url`), verifies the activation row on the exact default PDO, and seals that PDO before Feature setup, `RefreshDatabase`, `DatabaseTruncation`, or any other mutation. No setup or mutation path may call `migrate:fresh`, `db:wipe`, disconnect or reconnect. Laravel's normal `RefreshDatabase` teardown may disconnect only after rollback while destroying that test application; the next application must independently verify and seal a new exact PDO before use. Neither the credential-bearing URL nor the activation nonce may enter logs, committed artifacts or evidence. Real PostgreSQL substitution, replacement and automatic lost-connection canaries prove the refusal paths. The lane runs Unit, Feature and PostgreSQL suites; search remains case-insensitive on SQLite and PostgreSQL.

- [ ] **Create the guard test/config seam and prove it is absent.**

  ```bash
  cd backend
  DB_URL=postgresql://dorzak_p00:dorzak_p00@127.0.0.1:55432/dorzak_p00_test vendor/bin/phpunit --configuration=phpunit.pgsql.xml --testsuite PostgreSQL
  ```

  Expected failure: `phpunit.pgsql.xml` is absent.

- [ ] **Create the one-time in-memory qualification authority and bootstrap.**

  `backend/app/Support/PostgresQualificationGuard.php` delegates all URL/configuration work to the shared profile and owns only live-PDO facts plus one-time in-memory bootstrap binding:

  ```php
  <?php

  namespace App\Support;

  use PDO;
  use RuntimeException;
  use Throwable;

  final class PostgresQualificationGuard
  {
      private static ?PostgresConnectionProfile $bootstrapProfile = null;

      private static ?array $bootstrapAuthority = null;

      public static function assertPdo(
          PDO $pdo,
          PostgresConnectionProfile $profile,
          string $nonceSha256,
      ): array
      {
          $live = $pdo->query(
              "SELECT current_database() AS database, current_user AS role,
              current_setting('server_version_num')::int AS server_version_num,
              current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256,
              inet_server_addr()::text AS server_address,
              inet_server_port() AS server_port,
              COALESCE((SELECT ssl FROM pg_stat_ssl WHERE pid = pg_backend_pid()), false) AS tls",
          )->fetch(PDO::FETCH_ASSOC);
          if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql' || ! is_array($live)
              || $live['database'] !== $profile->database || $live['role'] !== $profile->username
              || (int) $live['server_version_num'] < 160000 || (int) $live['server_version_num'] >= 170000
              || ! preg_match('/^[0-9a-f]{64}$/', $nonceSha256)
              || ! hash_equals($nonceSha256, (string) $live['instance_nonce_sha256'])) {
              throw new RuntimeException('P00_PG_LIVE_PDO_REFUSED');
          }
          return $live;
      }

      public static function fingerprint(array $live): string
      {
          $facts = [
              'database' => (string) ($live['database'] ?? ''),
              'role' => (string) ($live['role'] ?? ''),
              'serverVersionNum' => (int) ($live['server_version_num'] ?? 0),
              'instanceNonceSha256' => (string) ($live['instance_nonce_sha256'] ?? ''),
              'serverAddress' => (string) ($live['server_address'] ?? ''),
              'serverPort' => (int) ($live['server_port'] ?? 0),
              'tls' => (bool) ($live['tls'] ?? false),
          ];
          if ($facts['database'] === '' || $facts['role'] === ''
              || ! preg_match('/^[0-9a-f]{64}$/', $facts['instanceNonceSha256'])
              || $facts['serverAddress'] === '' || $facts['serverPort'] < 1) {
              throw new RuntimeException('P00_PG_LIVE_FINGERPRINT_REFUSED');
          }
          return hash('sha256', json_encode($facts, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
      }

      public static function bindBootstrapAuthority(
          PostgresConnectionProfile $profile,
          string $identity,
          string $attestationSha256,
          string $nonceSha256,
          string $qualificationNonceSha256,
          array $live,
      ): void {
          if (self::$bootstrapProfile !== null
              || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9._:@#\/-]{0,255}$/', $identity)
              || ! preg_match('/^[0-9a-f]{64}$/', $attestationSha256)
              || ! preg_match('/^[0-9a-f]{64}$/', $nonceSha256)
              || ! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)) {
              throw new RuntimeException('P00_PG_BOOTSTRAP_BIND_REFUSED');
          }
          self::$bootstrapProfile = $profile;
          $liveFingerprintSha256 = self::fingerprint($live);
          self::$bootstrapAuthority = compact(
              'identity', 'attestationSha256', 'nonceSha256',
              'qualificationNonceSha256', 'liveFingerprintSha256',
          );
      }

      public static function assertBootstrapAuthority(
          string $url,
          string $identity,
          string $attestationSha256,
          string $nonceSha256,
          string $qualificationNonceSha256,
      ): PostgresConnectionProfile {
          if (self::$bootstrapProfile === null || self::$bootstrapAuthority === null) {
              throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
          }
          self::$bootstrapProfile->assertSameAuthority(PostgresConnectionProfile::fromUrl($url));
          $expected = self::$bootstrapAuthority;
          if (! hash_equals($expected['identity'], $identity)
              || ! hash_equals($expected['attestationSha256'], $attestationSha256)
              || ! hash_equals($expected['nonceSha256'], $nonceSha256)
              || ! hash_equals($expected['qualificationNonceSha256'], $qualificationNonceSha256)) {
              throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
          }
          return self::$bootstrapProfile;
      }

      public static function assertBootstrapLive(array $live): void
      {
          $expected = self::$bootstrapAuthority['liveFingerprintSha256'] ?? null;
          $actual = self::fingerprint($live);
          if (! is_string($expected) || ! hash_equals($expected, $actual)) {
              throw new RuntimeException('P00_PG_BOOTSTRAP_LIVE_REFUSED');
          }
      }

      public static function assertEmptySchema(PDO $pdo): void
      {
          $count = (int) $pdo->query(
              "WITH user_objects AS (
                  SELECT c.oid FROM pg_class c
                  JOIN pg_namespace n ON n.oid = c.relnamespace
                  WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                    AND n.nspname !~ '^pg_toast'
                    AND c.relkind IN ('r', 'p', 'v', 'm', 'S', 'f')
                  UNION ALL
                  SELECT t.oid FROM pg_type t
                  JOIN pg_namespace n ON n.oid = t.typnamespace
                  WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                    AND n.nspname !~ '^pg_toast'
                    AND t.typtype IN ('c', 'd', 'e', 'r', 'm')
                  UNION ALL
                  SELECT p.oid FROM pg_proc p
                  JOIN pg_namespace n ON n.oid = p.pronamespace
                  WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                    AND n.nspname !~ '^pg_toast'
                  UNION ALL
                  SELECT n.oid FROM pg_namespace n
                  WHERE n.nspname NOT IN ('public', 'pg_catalog', 'information_schema')
                    AND n.nspname !~ '^pg_toast'
              ) SELECT count(*) FROM user_objects",
          )->fetchColumn();
          if ($count !== 0) {
              throw new RuntimeException('P00_PG_NONEMPTY_CANDIDATE_REFUSED');
          }
      }

      public static function activateCandidate(PDO $pdo, string $qualificationNonceSha256): void
      {
          if (! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)) {
              throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED');
          }
          try {
              $pdo->beginTransaction();
              $pdo->exec(
                  'CREATE TABLE p00_qualification_activation (
                      singleton boolean PRIMARY KEY CHECK (singleton),
                      qualification_nonce_sha256 char(64) NOT NULL
                  )',
              );
              $statement = $pdo->prepare(
                  'INSERT INTO p00_qualification_activation
                   (singleton, qualification_nonce_sha256) VALUES (true, :nonce)',
              );
              $statement->execute(['nonce' => $qualificationNonceSha256]);
              $pdo->commit();
          } catch (Throwable $error) {
              if ($pdo->inTransaction()) {
                  $pdo->rollBack();
              }
              throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED', 0, $error);
          }
      }

      public static function assertActivated(PDO $pdo, string $qualificationNonceSha256): void
      {
          try {
              $rows = $pdo->query(
                  'SELECT qualification_nonce_sha256
                   FROM p00_qualification_activation WHERE singleton = true',
              )->fetchAll(PDO::FETCH_COLUMN);
          } catch (Throwable $error) {
              throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED', 0, $error);
          }
          if (count($rows) !== 1 || ! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)
              || ! hash_equals($qualificationNonceSha256, (string) $rows[0])) {
              throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED');
          }
      }
  }
  ```

  `backend/tests/Support/postgres-bootstrap.php` verifies the exact attestation bytes, then connects and binds only after the live PDO passes:

  ```php
  <?php

  declare(strict_types=1);

  use App\Support\PostgresQualificationGuard;

  require dirname(__DIR__, 2).'/vendor/autoload.php';

  $fail = static function (string $message): never {
      fwrite(STDERR, "P00_POSTGRES_GUARD FAIL {$message}\n");
      exit(2);
  };

  $url = getenv('DB_URL') ?: '';
  if (! extension_loaded('pdo_pgsql')) {
      $fail('pdo_pgsql is unavailable');
  }

  $attestationPath = getenv('P00_PG_ATTESTATION_PATH') ?: '';
  $attestationSha256 = getenv('P00_PG_ATTESTATION_SHA256') ?: '';
  $identity = getenv('P00_PG_IDENTITY') ?: '';
  $nonceSha256 = getenv('P00_PG_INSTANCE_NONCE_SHA256') ?: '';
  $qualificationNonceSha256 = getenv('P00_PG_QUALIFICATION_NONCE_SHA256') ?: '';
  if (getenv('P00_PG_QUALIFIED_CANDIDATE') !== '1') {
      $fail('qualification runner marker is absent');
  }
  if (! is_file($attestationPath) || is_link($attestationPath) || realpath($attestationPath) !== $attestationPath
      || ! preg_match('/^[0-9a-f]{64}$/', $attestationSha256)
      || ! hash_equals($attestationSha256, hash_file('sha256', $attestationPath))) {
      $fail('approved PostgreSQL attestation is absent or changed');
  }
  try {
      $attestation = json_decode((string) file_get_contents($attestationPath), true, 512, JSON_THROW_ON_ERROR);
      if (array_keys($attestation) !== ['schemaVersion', 'kind', 'identity', 'serverMajor', 'immutable', 'instanceNonceSha256']
          || $attestation['schemaVersion'] !== 2
          || $attestation['identity'] !== $identity
          || $attestation['serverMajor'] !== 16
          || $attestation['immutable'] !== true
          || $attestation['instanceNonceSha256'] !== $nonceSha256) {
          throw new RuntimeException('approved PostgreSQL identity is invalid');
      }
      $profile = App\Support\PostgresConnectionProfile::fromUrl($url);
      $pdo = $profile->pdo();
      $live = PostgresQualificationGuard::assertPdo($pdo, $profile, $nonceSha256);
      PostgresQualificationGuard::assertActivated($pdo, $qualificationNonceSha256);
      PostgresQualificationGuard::bindBootstrapAuthority(
          $profile, $identity, $attestationSha256, $nonceSha256,
          $qualificationNonceSha256, $live,
      );
      $substitutePath = getenv('P00_PG_TEST_SUBSTITUTE_URL_PATH') ?: '';
      if ($substitutePath !== '') {
          $handle = null;
          $opened = null;
          try {
              if ((getenv('APP_ENV') ?: '') !== 'testing' || ! function_exists('posix_geteuid')) {
                  throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
              }
              $handle = @fopen($substitutePath, 'rb');
              $opened = is_resource($handle) ? fstat($handle) : false;
              $named = @lstat($substitutePath);
              if (! is_resource($handle) || ! is_array($opened) || ! is_array($named)
                  || ($opened['mode'] & 0170000) !== 0100000
                  || ($opened['mode'] & 0777) !== 0600
                  || $opened['uid'] !== posix_geteuid()
                  || $opened['dev'] !== $named['dev'] || $opened['ino'] !== $named['ino']
                  || ! flock($handle, LOCK_EX)) {
                  throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
              }
              fwrite(STDOUT, "P00_PG_BOOTSTRAP_BARRIER READY\n");
              fflush(STDOUT);
              if (trim((string) fgets(STDIN)) !== 'GO') {
                  throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
              }
              $namedAfter = @lstat($substitutePath);
              $openedAfter = fstat($handle);
              if (! is_array($namedAfter) || ! is_array($openedAfter)
                  || $openedAfter['dev'] !== $opened['dev'] || $openedAfter['ino'] !== $opened['ino']
                  || $namedAfter['dev'] !== $opened['dev'] || $namedAfter['ino'] !== $opened['ino']
                  || ($namedAfter['mode'] & 0777) !== 0600
                  || rewind($handle) !== true) {
                  throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
              }
              $contents = stream_get_contents($handle);
              if (! is_string($contents) || ! @unlink($substitutePath)) {
                  throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
              }
              $substituteUrl = trim($contents);
          } finally {
              if (is_resource($handle)) {
                  flock($handle, LOCK_UN);
                  fclose($handle);
              }
              $remaining = @lstat($substitutePath);
              if (is_array($remaining) && is_array($opened)
                  && $remaining['dev'] === $opened['dev'] && $remaining['ino'] === $opened['ino']) {
                  @unlink($substitutePath);
              }
          }
          App\Support\PostgresConnectionProfile::fromUrl($substituteUrl);
          if (! putenv('DB_URL='.$substituteUrl)) {
              throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
          }
          $_ENV['DB_URL'] = $substituteUrl;
          $_SERVER['DB_URL'] = $substituteUrl;
      }
      if (getenv('P00_PG_SCHEMA_READY') !== '1') {
          throw new RuntimeException('P00_PG_SCHEMA_READY_REFUSED');
      }
      Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = true;
  } catch (Throwable $error) {
      $fail($error->getMessage());
  }
  $version = (int) $live['server_version_num'];
  fwrite(STDOUT, "P00_POSTGRES_GUARD PASS database={$profile->database} server_version_num={$version}\n");
  ```

- [ ] **Create the full PostgreSQL PHPUnit config and environment test.**

  `backend/phpunit.pgsql.xml`:

  ```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
           bootstrap="tests/Support/postgres-bootstrap.php"
           colors="true"
  >
      <testsuites>
          <testsuite name="Unit">
              <directory>tests/Unit</directory>
          </testsuite>
          <testsuite name="Feature">
              <directory>tests/Feature</directory>
          </testsuite>
          <testsuite name="PostgreSQL">
              <directory>tests/Postgres</directory>
          </testsuite>
      </testsuites>
      <source>
          <include>
              <directory>app</directory>
          </include>
      </source>
      <php>
          <env name="APP_ENV" value="testing"/>
          <env name="APP_URL" value="http://localhost"/>
          <env name="APP_MAINTENANCE_DRIVER" value="file"/>
          <env name="BCRYPT_ROUNDS" value="4"/>
          <env name="BROADCAST_CONNECTION" value="null"/>
          <env name="CACHE_STORE" value="array"/>
          <env name="DB_CONNECTION" value="pgsql" force="true"/>
          <env name="P00_PG_QUALIFICATION_PHASE" value="qualification" force="true"/>
          <env name="MAIL_MAILER" value="array"/>
          <env name="QUEUE_CONNECTION" value="sync"/>
          <env name="SESSION_DRIVER" value="array"/>
          <env name="PULSE_ENABLED" value="false"/>
          <env name="TELESCOPE_ENABLED" value="false"/>
          <env name="NIGHTWATCH_ENABLED" value="false"/>
      </php>
  </phpunit>
  ```

  `PostgresEnvironmentTest.php`:

  ```php
  <?php

  namespace Tests\Postgres;

  use App\Support\PostgresQualificationGuard;
  use Illuminate\Support\Facades\DB;
  use Tests\TestCase;

  final class PostgresEnvironmentTest extends TestCase
  {
      public function test_lane_is_postgresql_16(): void
      {
          self::assertSame('pgsql', DB::connection()->getDriverName());
          $profile = PostgresQualificationGuard::assertBootstrapAuthority(
              (string) getenv('DB_URL'),
              (string) getenv('P00_PG_IDENTITY'),
              (string) getenv('P00_PG_ATTESTATION_SHA256'),
              (string) getenv('P00_PG_INSTANCE_NONCE_SHA256'),
              (string) getenv('P00_PG_QUALIFICATION_NONCE_SHA256'),
          );
          $identity = PostgresQualificationGuard::assertPdo(
              DB::connection()->getPdo(),
              $profile,
              (string) getenv('P00_PG_INSTANCE_NONCE_SHA256'),
          );
          PostgresQualificationGuard::assertActivated(
              DB::connection()->getPdo(),
              (string) getenv('P00_PG_QUALIFICATION_NONCE_SHA256'),
          );
          PostgresQualificationGuard::assertBootstrapLive($identity);
          $version = (int) $identity['server_version_num'];
          self::assertGreaterThanOrEqual(160000, $version);
          self::assertLessThan(170000, $version);
      }
  }
  ```

  In Task 4's `AppServiceProvider::boot()` patch, add this independent qualification branch after the E2E branch. Bootstrap authority is asserted before `DB::connection()`; both reconnectors are fail-closed before the PDO is resolved; the resolved configuration and exact PDO are then verified and sealed:

  ```diff
  +use App\Support\PostgresQualificationGuard;
  @@
  +        $qualificationPhase = (string) env('P00_PG_QUALIFICATION_PHASE');
  +        if (in_array($qualificationPhase, ['qualification-provisioning', 'qualification'], true)) {
  +            $profile = $qualificationPhase === 'qualification'
  +                ? PostgresQualificationGuard::assertBootstrapAuthority(
  +                    (string) env('DB_URL'),
  +                    (string) env('P00_PG_IDENTITY'),
  +                    (string) env('P00_PG_ATTESTATION_SHA256'),
  +                    (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
  +                    (string) env('P00_PG_QUALIFICATION_NONCE_SHA256'),
  +                )
  +                : PostgresConnectionProfile::fromUrl((string) env('DB_URL'));
  +            DB::setReconnector(
  +                static fn (Connection $connection): never => throw new RuntimeException('P00_RECONNECT_REFUSED'),
  +            );
  +            $connection = DB::connection();
  +            $profile->assertLaravelConfiguration($connection, 'pgsql');
  +            $pdo = $connection->getPdo();
  +            $live = PostgresQualificationGuard::assertPdo(
  +                $pdo,
  +                $profile,
  +                (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
  +            );
  +            if ($qualificationPhase === 'qualification') {
  +                PostgresQualificationGuard::assertActivated(
  +                    $pdo,
  +                    (string) env('P00_PG_QUALIFICATION_NONCE_SHA256'),
  +                );
  +                PostgresQualificationGuard::assertBootstrapLive($live);
  +            } else {
  +                PostgresQualificationGuard::assertEmptySchema($pdo);
  +            }
  +            PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
  +        } elseif ($qualificationPhase !== '') {
  +            throw new RuntimeException('Unrecognized PostgreSQL qualification phase.');
  +        }
  ```

  Before using the service, rerun Task 0's immutable-attestation checks. Provision the service outside the repository from the exact `P00_PG_IDENTITY`. A mutable tag, service alias, or `external-postgresql-16` sentinel is rejected. Qualification receives no stable database URL: only the attested supervisor may issue a fresh candidate.

- [ ] **Create the sole qualification process runner.**

  From the repository root, create `scripts/quality/` if absent. `scripts/quality/run-qualified-postgres` accepts only the closed modes `environment`, `search`, `concurrency`, `full`, `quality-job`, and the test-only `wrong-live-nonce <nonce> <absolute-attestation-path> <hash>`. It provisions one unique candidate, retains its credential URL only in the shell environment, migrates and activates it through guarded phases, then executes the mode's fixed command through qualification:

  ```bash
  #!/usr/bin/env bash
  set -euo pipefail
  set +x
  if [[ -n "${DB_URL:-}" || -n "${P00_PG_SCHEMA_READY:-}" \
    || -n "${P00_PG_QUALIFIED_CANDIDATE:-}" || -n "${P00_PG_QUALIFICATION_NONCE_SHA256:-}" \
    || -n "${P00_PG_TEST_SUBSTITUTE_URL_PATH:-}" ]]; then
    printf '%s\n' 'P00_QUALIFICATION_REFUSED caller supplied guarded state' >&2
    exit 65
  fi
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  mode="${1:-}"
  if [[ "$#" -gt 0 ]]; then shift; fi
  case "$mode" in
    environment)
      [[ "$#" -eq 0 ]] || exit 64
      command=(vendor/bin/phpunit --configuration=phpunit.pgsql.xml --testsuite PostgreSQL)
      ;;
    search)
      [[ "$#" -eq 0 ]] || exit 64
      command=(vendor/bin/phpunit --configuration=phpunit.pgsql.xml
        tests/Feature/Catalog/ProductApiTest.php tests/Feature/Customer/CustomerApiTest.php
        --filter=/test_\(search_matches_name_and_sku\|search_matches_name_email_phone\)/)
      ;;
    concurrency)
      [[ "$#" -eq 0 ]] || exit 64
      command=(vendor/bin/phpunit --configuration=phpunit.pgsql.xml
        tests/Postgres/OrderAndStockConcurrencyTest.php)
      ;;
    full)
      [[ "$#" -eq 0 ]] || exit 64
      command=(vendor/bin/phpunit --configuration=phpunit.pgsql.xml)
      ;;
    quality-job)
      [[ "$#" -eq 0 ]] || exit 64
      command=("$ROOT/scripts/quality/run-postgres-16")
      ;;
    wrong-live-nonce)
      [[ "$#" -eq 3 ]] || exit 64
      wrong_nonce="$1"
      wrong_attestation="$2"
      wrong_attestation_sha256="$3"
      [[ "$wrong_nonce" =~ ^[0-9a-f]{64}$ ]]
      [[ "$wrong_attestation_sha256" =~ ^[0-9a-f]{64}$ ]]
      [[ -f "$wrong_attestation" && ! -L "$wrong_attestation" ]]
      [[ "$(realpath "$wrong_attestation")" == "$wrong_attestation" ]]
      [[ "$(shasum -a 256 "$wrong_attestation" | awk '{print $1}')" == "$wrong_attestation_sha256" ]]
      command=(vendor/bin/phpunit --configuration=phpunit.pgsql.xml --testsuite Feature)
      ;;
    *)
      printf 'Unknown P00 PostgreSQL qualification mode: %s\n' "$mode" >&2
      exit 64
      ;;
  esac
  candidate_database=none
  candidate_role=none
  candidate_lifecycle=unknown
  on_exit() {
    status="$?"
    trap - EXIT
    if [[ "$status" -ne 0 && "$candidate_database" != none ]]; then
      printf 'P00_QUALIFICATION_RETAINED database=%s role=%s lifecycle=%s\n' \
        "$candidate_database" "$candidate_role" "$candidate_lifecycle" >&2
    fi
    exit "$status"
  }
  trap on_exit EXIT
  provisioning_status=ERROR
  set +e
  IFS='|' read -r provisioning_status DB_URL candidate_database candidate_role candidate_lifecycle < <(
    P00_ROOT="$ROOT" php -r '
      require getenv("P00_ROOT")."/backend/vendor/autoload.php";
      try {
        $lease = Tests\Support\PostgresSubstitutionHarness::fromEnvironment()->candidate();
        $url = $lease->environment("provisioning-migrate")["P00_E2E_DB_URL"];
        echo "OK|", $url, "|", $lease->database(), "|", $lease->role(), "|", $lease->lifecycleId(), "\n";
      } catch (App\Support\E2eProvisioningException $error) {
        echo "ORPHAN||", $error->database, "|", $error->role, "|", $error->lifecycleId, "\n";
      }
    '
  )
  read_status="$?"
  set -e
  if [[ "$provisioning_status" == ORPHAN ]]; then
    exit 66
  fi
  if [[ "$read_status" -ne 0 || "$provisioning_status" != OK || -z "$DB_URL" \
    || "$candidate_database" == none || "$candidate_role" == none ]]; then
    printf '%s\n' 'P00_QUALIFICATION_REFUSED candidate provisioning failed' >&2
    exit 66
  fi
  export DB_URL
  P00_PG_QUALIFICATION_NONCE_SHA256="$(php -r 'echo hash("sha256", random_bytes(32));')"
  [[ "$P00_PG_QUALIFICATION_NONCE_SHA256" =~ ^[0-9a-f]{64}$ ]]
  export P00_PG_QUALIFICATION_NONCE_SHA256
  (
    cd "$ROOT/backend"
    DB_CONNECTION=pgsql P00_PG_QUALIFICATION_PHASE=qualification-provisioning \
      php artisan migrate --force --no-interaction
  )
  P00_ROOT="$ROOT" php -r '
    require getenv("P00_ROOT")."/backend/vendor/autoload.php";
    $profile = App\Support\PostgresConnectionProfile::fromUrl(getenv("DB_URL") ?: "");
    $pdo = $profile->pdo();
    App\Support\PostgresQualificationGuard::assertPdo(
      $pdo, $profile, getenv("P00_PG_INSTANCE_NONCE_SHA256") ?: "",
    );
    App\Support\PostgresQualificationGuard::activateCandidate(
      $pdo, getenv("P00_PG_QUALIFICATION_NONCE_SHA256") ?: "",
    );
  '
  export P00_PG_SCHEMA_READY=1
  export P00_PG_QUALIFIED_CANDIDATE=1
  export DB_CONNECTION=pgsql
  export P00_PG_QUALIFICATION_PHASE=qualification
  if [[ "$mode" == wrong-live-nonce ]]; then
    export P00_PG_INSTANCE_NONCE_SHA256="$wrong_nonce"
    export P00_PG_ATTESTATION_PATH="$wrong_attestation"
    export P00_PG_ATTESTATION_SHA256="$wrong_attestation_sha256"
  fi
  cd "$ROOT/backend"
  "${command[@]}"
  ```

  Make it executable. There is no arbitrary-command mode, so a caller cannot use the runner to print `DB_URL` or boot outside qualification. The structured provisioning channel is captured directly into shell variables and never echoed or persisted. A typed post-create failure returns only `ORPHAN`, database, role and lifecycle; the EXIT trap records that nonsecret identity. The runner refuses inherited candidate state, so nesting or reuse fails closed. `qualification-provisioning` validates the exact PDO and complete empty user schema, seals it, and runs ordinary forward migrations on that same connection. A separately verified exact PDO then creates the single candidate-bound activation row as the final setup mutation. Bootstrap requires the runner marker, 64-hex activation nonce and exact row before binding authority. No path calls `migrate:fresh` or `db:wipe`; cleanup may address only the attested service lifecycle.

- [ ] **Create executable qualification substitution, PDO-replacement and reconnect proofs.**

  The test-only bootstrap hook above runs only when `P00_PG_TEST_SUBSTITUTE_URL_PATH` is explicitly present. It binds and verifies the preliminary PDO first, emits a nonsecret READY barrier, waits on stdin, consumes and deletes the mode-0600 substitute URL file, then changes `DB_URL` before PHPUnit constructs its first application. The production branch never sets this variable.

  `backend/tests/LivePostgres/QualificationBootstrapProbeTest.php` is the mutation probe. Reaching its body is failure:

  ```php
  <?php

  namespace Tests\LivePostgres;

  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Tests\TestCase;

  final class QualificationBootstrapProbeTest extends TestCase
  {
      use RefreshDatabase;

      public function test_first_feature_application_must_never_reach_mutation(): void
      {
          self::fail('Qualification application boot accepted the substituted endpoint.');
      }
  }
  ```

  `backend/tests/LivePostgres/PostgresQualificationLiveGuardTest.php` runs under the default non-PostgreSQL PHPUnit bootstrap and orchestrates the real guarded qualification child, so substitution occurs after that child's preliminary bootstrap and before its first Feature application:

  ```php
  <?php

  namespace Tests\LivePostgres;

  use App\Support\PostgresConnectionProfile;
  use App\Support\PostgresQualificationGuard;
  use Illuminate\Database\Connectors\ConnectionFactory;
  use RuntimeException;
  use Symfony\Component\Process\InputStream;
  use Symfony\Component\Process\Process;
  use Tests\Support\PostgresSubstitutionHarness;
  use Tests\TestCase;

  final class PostgresQualificationLiveGuardTest extends TestCase
  {
      public function test_bootstrap_authority_reconnect_and_pdo_replacement_fail_closed(): void
      {
          $harness = PostgresSubstitutionHarness::fromEnvironment();
          $expected = $harness->candidate();
          $qualificationNonceSha256 = hash('sha256', random_bytes(32));
          $harness->activateQualification($expected, $qualificationNonceSha256);
          $decoy = $harness->candidate();
          $canary = $harness->installCanary($decoy);
          $decoyUrl = $decoy->environment('provisioning-migrate')['P00_E2E_DB_URL'];
          $substitutePath = tempnam(sys_get_temp_dir(), 'p00-pg-substitute-');
          self::assertIsString($substitutePath);
          try {
              self::assertTrue(chmod($substitutePath, 0600));
              self::assertSame(strlen($decoyUrl), file_put_contents($substitutePath, $decoyUrl, LOCK_EX));
              $input = new InputStream;
              $barrierOutput = '';
              $expectedUrl = $expected->environment('provisioning-migrate')['P00_E2E_DB_URL'];
              $process = new Process(
                  [
                      PHP_BINARY, 'vendor/bin/phpunit', '-c', 'phpunit.pgsql.xml',
                      'tests/LivePostgres/QualificationBootstrapProbeTest.php',
                  ],
                  base_path(),
                  array_merge($_SERVER, $_ENV, [
                      'DB_URL' => $expectedUrl,
                      'P00_PG_SCHEMA_READY' => '1',
                      'P00_PG_QUALIFIED_CANDIDATE' => '1',
                      'P00_PG_QUALIFICATION_NONCE_SHA256' => $qualificationNonceSha256,
                      'P00_PG_TEST_SUBSTITUTE_URL_PATH' => $substitutePath,
                  ]),
              );
              $process->setInput($input);
              $process->setTimeout(60);
              $process->start();
              self::assertTrue($process->waitUntil(
                  static function (string $type, string $buffer) use (&$barrierOutput): bool {
                      if ($type === Process::OUT) {
                          $barrierOutput .= $buffer;
                      }
                      return str_contains($barrierOutput, "P00_PG_BOOTSTRAP_BARRIER READY\n");
                  },
              ));
              $input->write("GO\n");
              $input->close();
              $process->wait();
              self::assertFalse($process->isSuccessful());
              self::assertStringContainsString(
                  'P00_PG_BOOTSTRAP_AUTHORITY_REFUSED',
                  $process->getOutput().$process->getErrorOutput(),
              );
          } finally {
              if (file_exists($substitutePath) || is_link($substitutePath)) {
                  self::assertTrue(unlink($substitutePath));
              }
          }
          self::assertSame($canary, $harness->canaryFingerprint($decoy));
          self::assertFalse($harness->activationExists($expected));

          $lost = $harness->candidate();
          $lostFingerprint = $harness->installCanary($lost);
          $profile = $harness->profile($lost);
          $connection = app(ConnectionFactory::class)->make(
              $profile->laravelConfiguration(), 'qualification-live-guard',
          );
          $profile->assertLaravelConfiguration($connection, 'qualification-live-guard');
          $pdo = $connection->getPdo();
          PostgresQualificationGuard::assertPdo(
              $pdo, $profile, (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
          );
          PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
          $backendPid = (int) $connection->scalar('SELECT pg_backend_pid()');
          $harness->terminateBackend($lost, $backendPid);
          $this->expectMarker(
              'P00_RECONNECT_REFUSED',
              static fn () => $connection->statement('CREATE TABLE forbidden_qualification_lost_write (id integer)'),
          );
          self::assertSame($lostFingerprint, $harness->canaryFingerprint($lost));

          $connection->setPdo($harness->pdo($decoy));
          $this->expectMarker(
              'P00_PDO_SUBSTITUTION_REFUSED',
              static fn () => $connection->statement('CREATE TABLE forbidden_qualification_replacement (id integer)'),
          );
          self::assertSame($canary, $harness->canaryFingerprint($decoy));
      }

      private function expectMarker(string $marker, callable $operation): void
      {
          try {
              $operation();
              self::fail($marker.' was not raised.');
          } catch (RuntimeException $error) {
              self::assertSame($marker, $error->getMessage());
          }
      }
  }
  ```

  The executable test uses independent create-only candidates and the bootstrap stdin barrier—never DNS, proxy, shared-host mutation or mocks. It proves the exact post-bootstrap/pre-first-Feature interval, kills a real guarded backend before a mutating query to exercise Laravel's automatic lost-connection path, and replaces a sealed raw PDO. Every canary must be byte-identical after refusal. The valid-PG16 wrong-live-nonce invocation remains mandatory, so success cannot be inferred from URL binding alone.

- [ ] **Verify the safety failures and commit the lane separately.**

  ```bash
  cd backend
  chmod +x ../scripts/quality/run-qualified-postgres
  set +e
  arbitrary_output="$(../scripts/quality/run-qualified-postgres printenv 2>&1)"
  arbitrary_status="$?"
  DB_URL=sqlite://unsafe vendor/bin/phpunit -c phpunit.pgsql.xml --testsuite PostgreSQL
  unsafe_driver_status="$?"
  DB_URL=postgresql://dorzak_p00:dorzak_p00@127.0.0.1:55432/dorzak_p00 vendor/bin/phpunit -c phpunit.pgsql.xml --testsuite PostgreSQL
  unsafe_database_status="$?"
  set -e
  test "$arbitrary_status" = 64
  test "$arbitrary_output" = 'Unknown P00 PostgreSQL qualification mode: printenv'
  test "$unsafe_driver_status" = 2
  test "$unsafe_database_status" = 2
  php -r 'require "vendor/autoload.php"; foreach ([
    "sqlite://unsafe",
    "postgresql://user:pass@127.0.0.1/db_live",
    "postgresql://user:pass@127.0.0.1/db_test?unknown=x",
    "postgresql://user:pass@127.0.0.1/db_test?sslmode=require&sslmode=prefer",
    "postgresql://user:pass@127.0.0.1/db_test?sslmode=unsupported",
    "postgresql://user:pass@127.0.0.1/db_test#fragment",
    "postgresql://user:pass@127.0.0.1/db%ZZ_test",
    "postgresql://user:pass%3Bsslmode=require@127.0.0.1/db_test",
    "postgresql://user:pass@bad host/db_test",
  ] as $url) { try { App\Support\PostgresConnectionProfile::fromUrl($url); exit(1); } catch (RuntimeException) {} }'
  php <<'PHP'
  <?php
  require 'vendor/autoload.php';
  use App\Support\PostgresConnectionProfile;
  use Illuminate\Database\ConfigurationUrlParser;
  use Illuminate\Database\PostgresConnection;
  $cases = [
      ['postgresql://user:pass@localhost/db_test?sslmode=disable', 'disable'],
      ['postgresql://user:pass@127.0.0.1:55432/db_test?sslmode=allow', 'allow'],
      ['postgresql://user:pass@[2001:db8::1]/db_test?sslmode=prefer', 'prefer'],
      ['postgresql://user:pass@localhost/db_test?sslmode=require', 'require'],
      ['postgresql://user:pass@localhost/db_test?sslmode=verify-ca', 'verify-ca'],
      ['postgresql://user:pass@localhost/db_test?sslmode=verify-full', 'verify-full'],
  ];
  foreach ($cases as [$url, $sslmode]) {
      $profile = PostgresConnectionProfile::fromUrl($url);
      if ($profile->sslmode !== $sslmode) exit(1);
      $resolved = (new ConfigurationUrlParser)->parseConfiguration([
          'url' => $url, 'driver' => 'pgsql', 'host' => 'ignored.test', 'port' => 5432,
          'database' => 'ignored_test', 'username' => 'ignored', 'password' => 'ignored',
          'charset' => 'utf8', 'prefix' => '', 'prefix_indexes' => true,
          'search_path' => 'public', 'name' => 'pgsql',
      ]);
      $connection = new PostgresConnection(
          static fn () => throw new RuntimeException('PDO must not be resolved.'),
          $resolved['database'], '', $resolved,
      );
      $profile->assertLaravelConfiguration($connection, 'pgsql');
  }
  PHP
  mkdir -p ../.artifacts/p00
  wrong_nonce="$(printf '0%.0s' {1..64})"
  wrong_attestation="$(cd ../.artifacts/p00 && pwd -P)/postgresql-wrong-live-nonce.json"
  jq --arg nonce "$wrong_nonce" '.instanceNonceSha256 = $nonce' "$P00_PG_ATTESTATION_PATH" > "$wrong_attestation"
  set +e
  ../scripts/quality/run-qualified-postgres wrong-live-nonce \
    "$wrong_nonce" "$wrong_attestation" \
    "$(shasum -a 256 "$wrong_attestation" | awk '{print $1}')"
  wrong_nonce_status="$?"
  set -e
  test "$wrong_nonce_status" = 2
  php artisan test tests/LivePostgres/PostgresQualificationLiveGuardTest.php
  ../scripts/quality/run-qualified-postgres environment
  ```

  Expected: the arbitrary `printenv` mode exits `64` with the exact nonsecret message before provisioning. Both direct PHPUnit bypass attempts exit `2` because no runner marker/activation is present, before a Feature application or mutation. The closed parser proof independently rejects each unsafe URL for its intended branch, accepts all six `sslmode` values, and proves Laravel-resolved configuration for an omitted default port plus bracketed IPv6. The wrong-nonce mode provisions, migrates and activates normally, then gives only the fixed guarded PHPUnit child a matching test attestation with the wrong expected live nonce. The default-bootstrap live orchestrator proves endpoint substitution, automatic reconnect and raw-PDO replacement all fail before mutation and preserve every canary. The fresh approved PostgreSQL 16 packet reports one pass.

  ```bash
  git add -- backend/phpunit.pgsql.xml backend/app/Support/PostgresConnectionProfile.php \
    backend/app/Support/PostgresQualificationGuard.php \
    backend/app/Providers/AppServiceProvider.php backend/tests/Support/postgres-bootstrap.php \
    backend/tests/Support/PostgresSubstitutionHarness.php \
    backend/tests/Postgres/PostgresEnvironmentTest.php \
    backend/tests/LivePostgres/PostgresQualificationLiveGuardTest.php \
    backend/tests/LivePostgres/QualificationBootstrapProbeTest.php \
    scripts/quality/run-qualified-postgres
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 10
  git commit -m "test(backend): add guarded PostgreSQL 16 lane"
  ```

- [ ] **Run the two known search tests red on PostgreSQL.**

  ```bash
  cd backend
  ../scripts/quality/run-qualified-postgres search
  ```

  Expected: lowercase `hoodie` and `sarah` each return count `0` where current tests require `1`; PostgreSQL `LIKE` is case-sensitive.

- [ ] **Replace only the two search scopes with portable Laravel 13 query-builder calls.**

  Product closure:

  ```php
  return $query->where(function (Builder $q) use ($like) {
      $q->whereLike('name', $like, caseSensitive: false)
          ->orWhereLike('sku', $like, caseSensitive: false)
          ->orWhereHas(
              'category',
              fn (Builder $category) => $category->whereLike('name', $like, caseSensitive: false),
          );
  });
  ```

  Customer closure:

  ```php
  return $query->where(fn (Builder $q) => $q
      ->whereLike('name', $like, caseSensitive: false)
      ->orWhereLike('email', $like, caseSensitive: false)
      ->orWhereLike('phone', $like, caseSensitive: false));
  ```

- [ ] **Verify search on both databases and the complete PostgreSQL lane.**

  ```bash
  cd backend
  php artisan test tests/Feature/Catalog/ProductApiTest.php tests/Feature/Customer/CustomerApiTest.php --filter='/test_(search_matches_name_and_sku|search_matches_name_email_phone)/'
  ../scripts/quality/run-qualified-postgres search
  ../scripts/quality/run-qualified-postgres full
  ```

  Expected: before the model patch SQLite reports `2 passed` and PostgreSQL reports exactly `2 failed`; after the patch both focused runs report `2 passed`. Full PostgreSQL reports `447 passed` (the 446 SQLite-visible tests plus the PostgreSQL environment test).

- [ ] **Stage only the two models and commit the contract-preserving fix.**

  ```bash
  git add -- backend/app/Models/Product.php backend/app/Models/Customer.php
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 2
  git commit -m "fix: preserve case-insensitive search on PostgreSQL"
  ```

  Run the global writer-boundary checks.

### Task 12: Prove PostgreSQL order, stock, and wallet locking with process barriers

**Files:**
- Create: `backend/tests/Support/ProcessBarrier.php`
- Create: `backend/tests/Support/concurrency-worker.php`
- Create: `backend/tests/Postgres/OrderAndStockConcurrencyTest.php`

**Interfaces:** `ProcessBarrier::run(string $operation, array $payloads): array` starts independent PHP processes with the closed inherited qualification environment, waits for every actor to flush `READY`, sends `GO` to all input streams, applies a 15-second timeout, and returns JSON outcomes. Each child runs the same attestation/bootstrap authority binding before its first Laravel application, validates and seals its own exact PDO, and cannot reconnect. No actor uses timing sleeps. Operations are `create-order` and `redeem-wallet`. The parent uses `DatabaseTruncation` on the runner's already migrated unique candidate: setup rows are committed and visible to children, while no path wipes, disconnects or uses a parent transaction.

- [ ] **Write three PostgreSQL tests against the missing barrier.**

  `backend/tests/Postgres/OrderAndStockConcurrencyTest.php`:

  ```php
  <?php

  namespace Tests\Postgres;

  use App\Enums\StockMovementType;
  use App\Models\Customer;
  use App\Models\Order;
  use App\Models\Product;
  use App\Models\StockMovement;
  use App\Models\Store;
  use App\Models\WalletAccount;
  use App\Models\WalletEntry;
  use App\Services\WalletService;
  use App\Support\StoreContext;
  use Illuminate\Foundation\Testing\DatabaseTruncation;
  use Tests\Support\ProcessBarrier;
  use Tests\TestCase;

  final class OrderAndStockConcurrencyTest extends TestCase
  {
      use DatabaseTruncation;

      protected array $exceptTables = ['p00_qualification_activation'];

      public function test_two_completed_orders_receive_distinct_numbers_and_atomic_stock_deductions(): void
      {
          $store = $this->store();
          $product = $this->product($store, 10);
          $payload = ['store_id' => $store->id, 'product_id' => $product->id, 'quantity' => 1];

          $results = ProcessBarrier::run('create-order', [$payload, $payload]);

          self::assertCount(2, $results);
          self::assertCount(2, array_filter($results, fn (array $result) => $result['ok'] === true));
          $numbers = array_column($results, 'order_number');
          sort($numbers, SORT_STRING);
          self::assertSame(['ORD-1000', 'ORD-1001'], $numbers);
          self::assertSame(2, Order::query()->count());
          self::assertSame(8, Product::query()->findOrFail($product->id)->stock);
          $movements = StockMovement::query()
              ->where('product_id', $product->id)
              ->where('type', StockMovementType::SALE->value)
              ->get();
          self::assertCount(2, $movements);
          self::assertSame(-2, $movements->sum('quantity_change'));
      }

      public function test_only_one_order_can_consume_the_last_stock_unit(): void
      {
          $store = $this->store();
          $product = $this->product($store, 1);
          $payload = ['store_id' => $store->id, 'product_id' => $product->id, 'quantity' => 1];

          $results = ProcessBarrier::run('create-order', [$payload, $payload]);

          self::assertCount(1, array_filter($results, fn (array $result) => $result['ok'] === true));
          $failures = array_values(array_filter($results, fn (array $result) => $result['ok'] === false));
          self::assertCount(1, $failures);
          self::assertSame('INSUFFICIENT_STOCK', $failures[0]['error_code']);
          self::assertSame(1, Order::query()->count());
          self::assertSame(0, Product::query()->findOrFail($product->id)->stock);
          $movements = StockMovement::query()
              ->where('product_id', $product->id)
              ->where('type', StockMovementType::SALE->value)
              ->get();
          self::assertCount(1, $movements);
          self::assertSame(-1, $movements->sum('quantity_change'));
      }

      public function test_only_one_concurrent_wallet_redemption_can_spend_available_credit(): void
      {
          $store = $this->store();
          $customer = Customer::factory()->for($store)->create();
          app(WalletService::class)->credit($customer, 10, 'P00 seed');
          $payload = ['store_id' => $store->id, 'customer_id' => $customer->id, 'amount' => 8];

          $results = ProcessBarrier::run('redeem-wallet', [$payload, $payload]);

          self::assertCount(1, array_filter($results, fn (array $result) => $result['ok'] === true));
          $failures = array_values(array_filter($results, fn (array $result) => $result['ok'] === false));
          self::assertCount(1, $failures);
          self::assertSame('INSUFFICIENT_CREDIT', $failures[0]['error_code']);
          $account = WalletAccount::query()->where('customer_id', $customer->id)->sole();
          self::assertSame('2.00', (string) $account->balance);
          $debits = WalletEntry::query()
              ->where('customer_id', $customer->id)
              ->where('amount', '<', 0)
              ->get();
          self::assertCount(1, $debits);
          self::assertSame('-8.00', (string) $debits->sole()->amount);
      }

      private function store(): Store
      {
          $store = Store::factory()->create([
              'currency' => 'QAR',
              'charge_sales_tax' => false,
              'tax_rate' => 0,
          ]);
          app(StoreContext::class)->setStore($store);

          return $store;
      }

      private function product(Store $store, int $stock): Product
      {
          return Product::factory()->for($store)->create([
              'price' => 10,
              'cost' => 4,
              'taxable' => false,
              'track_stock' => true,
              'stock' => $stock,
              'min_stock' => 0,
              'is_active' => true,
          ]);
      }
  }
  ```

- [ ] **Run red on PostgreSQL.**

  ```bash
  cd backend
  ../scripts/quality/run-qualified-postgres concurrency
  ```

  Expected failure: `Class "Tests\Support\ProcessBarrier" not found`.

- [ ] **Create the complete process barrier.**

  ```php
  <?php

  namespace Tests\Support;

  use RuntimeException;
  use Symfony\Component\Process\Exception\ProcessFailedException;
  use Symfony\Component\Process\InputStream;
  use Symfony\Component\Process\Process;

  final class ProcessBarrier
  {
      /** @return list<array<string, mixed>> */
      public static function run(string $operation, array $payloads): array
      {
          $qualification = [];
          foreach ([
              'DB_URL', 'P00_PG_IDENTITY', 'P00_PG_ATTESTATION_PATH',
              'P00_PG_ATTESTATION_SHA256', 'P00_PG_INSTANCE_NONCE_SHA256',
              'P00_PG_QUALIFICATION_NONCE_SHA256',
              'P00_PG_QUALIFICATION_PHASE', 'P00_PG_SCHEMA_READY',
              'P00_PG_QUALIFIED_CANDIDATE',
          ] as $name) {
              $value = getenv($name);
              if (! is_string($value) || $value === '') {
                  throw new RuntimeException('Concurrency qualification environment is incomplete.');
              }
              $qualification[$name] = $value;
          }
          if ($qualification['P00_PG_QUALIFICATION_PHASE'] !== 'qualification'
              || $qualification['P00_PG_SCHEMA_READY'] !== '1'
              || $qualification['P00_PG_QUALIFIED_CANDIDATE'] !== '1'
              || getenv('P00_PG_TEST_SUBSTITUTE_URL_PATH') !== false) {
              throw new RuntimeException('Concurrency qualification environment is unsafe.');
          }
          $actors = [];
          foreach ($payloads as $payload) {
              $input = new InputStream;
              $process = new Process([
                  PHP_BINARY,
                  __DIR__.'/concurrency-worker.php',
                  $operation,
                  base64_encode(json_encode($payload, JSON_THROW_ON_ERROR)),
              ], dirname(__DIR__, 2), array_merge($_SERVER, $_ENV, $qualification), null, 15);
              $process->setInput($input);
              $process->start();
              $actors[] = [$process, $input];
          }

          foreach ($actors as [$process]) {
              $readyOutput = '';
              $ready = $process->waitUntil(
                  static function (string $type, string $buffer) use (&$readyOutput): bool {
                      if ($type === Process::OUT) {
                          $readyOutput .= $buffer;
                      }
                      return str_contains($readyOutput, "READY\n");
                  },
              );
              if (! $ready) {
                  throw new RuntimeException('Concurrency actor exited before READY.');
              }
          }
          foreach ($actors as [, $input]) {
              $input->write("GO\n");
              $input->close();
          }

          $results = [];
          foreach ($actors as [$process]) {
              $process->wait();
              if (! $process->isSuccessful()) {
                  throw new ProcessFailedException($process);
              }
              $lines = array_values(array_filter(explode("\n", trim($process->getOutput()))));
              $results[] = json_decode((string) end($lines), true, flags: JSON_THROW_ON_ERROR);
          }

          return $results;
      }
  }
  ```

- [ ] **Create the complete worker.**

  ```php
  <?php

  use App\Exceptions\DomainConflictException;
  use App\Models\Customer;
  use App\Models\Store;
  use App\Services\OrderService;
  use App\Services\WalletService;
  use App\Support\StoreContext;
  use Illuminate\Contracts\Console\Kernel;

  if ((getenv('P00_PG_QUALIFICATION_PHASE') ?: '') !== 'qualification'
      || getenv('P00_PG_SCHEMA_READY') !== '1'
      || getenv('P00_PG_QUALIFIED_CANDIDATE') !== '1') {
      fwrite(STDERR, "P00_CONCURRENCY_WORKER_REFUSED\n");
      exit(2);
  }
  require __DIR__.'/postgres-bootstrap.php';
  $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
  $app->make(Kernel::class)->bootstrap();

  $operation = (string) ($argv[1] ?? '');
  $payload = json_decode(base64_decode((string) ($argv[2] ?? ''), true), true, flags: JSON_THROW_ON_ERROR);
  $store = Store::findOrFail($payload['store_id']);
  app(StoreContext::class)->setStore($store);
  fwrite(STDOUT, "READY\n");
  fflush(STDOUT);
  if (trim((string) fgets(STDIN)) !== 'GO') {
      fwrite(STDERR, "Barrier command was not GO.\n");
      exit(2);
  }

  try {
      $result = match ($operation) {
          'create-order' => (static function () use ($store, $payload): array {
              $order = app(OrderService::class)->create($store, [
                  'items' => [['product_id' => $payload['product_id'], 'quantity' => $payload['quantity']]],
                  'payment_method' => 'CASH',
                  'status' => 'COMPLETE',
              ]);
              return ['ok' => true, 'order_id' => $order->id, 'order_number' => $order->order_number];
          })(),
          'redeem-wallet' => (static function () use ($payload): array {
              $customer = Customer::findOrFail($payload['customer_id']);
              app(WalletService::class)->redeem($customer, (float) $payload['amount'], 'P00 concurrent checkout');
              return ['ok' => true];
          })(),
          default => throw new InvalidArgumentException("Unknown operation {$operation}"),
      };
  } catch (DomainConflictException $exception) {
      $result = ['ok' => false, 'error_code' => $exception->errorCode];
  }

  fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR)."\n");
  ```

- [ ] **Run the complete concurrency file and both database aggregates.**

  The complete test uses `DatabaseTruncation` and excludes only `p00_qualification_activation`; losing that row would make every child fail closed. The qualification bootstrap has already set `RefreshDatabaseState::$migrated = true`, so Laravel truncates application tables through the same sealed PDO and never enters `migrate:fresh`; fixtures are then committed and visible to child processes. Every focused or aggregate invocation gets a separate freshly provisioned candidate. Do not replace this with `DatabaseMigrations`, `RefreshDatabase`, an HTTP wrapper, a parent transaction, clock delays, retry loops, `migrate:fresh` or `db:wipe`.

  ```bash
  cd backend
  ../scripts/quality/run-qualified-postgres concurrency
  ../scripts/quality/run-qualified-postgres full
  php artisan test
  ```

  Expected: concurrency file reports `3 passed`; full PostgreSQL reports `450 passed`; SQLite remains `446 passed`.

- [ ] **Stage only the three concurrency files and commit.**

  ```bash
  git add -- backend/tests/Support/ProcessBarrier.php backend/tests/Support/concurrency-worker.php backend/tests/Postgres/OrderAndStockConcurrencyTest.php
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 3
  git commit -m "test: prove PostgreSQL locking invariants"
  ```

  Run the global writer-boundary checks.

### Task 13: Add provider-neutral quality, bundle, aggregation, and evidence interfaces

**Files:**
- Modify: `.gitignore:8`
- Create: `scripts/quality/p00-contract.json`
- Create: `scripts/quality/p00.mjs`
- Create: `scripts/quality/p00.test.mjs`
- Create: `scripts/quality/run-p00`
- Create: `scripts/quality/test-run-p00.sh`
- Create: `scripts/quality/run-postgres-16`

**Interfaces:** `p00-contract.json` is the sole versioned provenance for six ordered jobs, exact test totals, the 216,700-byte gzip ceiling, and the accepted-open Vite large-chunk warning. `scripts/quality/run-p00 --list` emits exactly those jobs; invoking one requires an exact control-bound `P00_RUNNER_ROLE`/`P00_RUNNER_CLASS` pair and writes `$P00_ARTIFACT_DIR/<job>/result.json` plus that job's closed raw-artifact set. The PostgreSQL job always enters through Task 11's `run-qualified-postgres`, so every local/CI packet gets a distinct empty create-only candidate and `run-postgres-16` refuses direct or reusable URLs. `node scripts/quality/p00.mjs aggregate <dir>` accepts exactly six passing same-SHA/same-complete-portable-input/same-role results and refuses a role/class swap. The same module measures bundles, records portable PostgreSQL identity/policy plus per-run observations, builds Task 17's seven sibling evidence payloads plus manifest/schema through failure-cleaned atomic publication, and validates hashes, identities, counts, debt, reviews, CI runs, closed schemas and whole-value secret rejection.

- [ ] **Write the dispatcher, bundle, aggregate, and evidence tests first.**

  Create `scripts/quality/p00-contract.json` exactly:

  ~~~json
  {
    "schemaVersion": 1,
    "jobs": [
      { "name": "composer-validation", "command": "scripts/quality/run-p00 composer-validation", "testCount": 11 },
      { "name": "php-style-static", "command": "scripts/quality/run-p00 php-style-static", "testCount": 0 },
      { "name": "sqlite", "command": "scripts/quality/run-p00 sqlite", "testCount": 446 },
      { "name": "postgresql-16", "command": "scripts/quality/run-p00 postgresql-16", "testCount": 450 },
      { "name": "frontend", "command": "scripts/quality/run-p00 frontend", "testCount": 8 },
      { "name": "playwright", "command": "scripts/quality/run-p00 playwright", "testCount": 9 }
    ],
    "bundle": {
      "initialGzipLimitBytes": 216700,
      "largeChunkThresholdBytes": 500000,
      "debtStatus": "accepted-open",
      "warning": "(!) Some chunks are larger than 500 kB after minification. Consider:\n- Using dynamic import() to code-split the application\n- Use build.rollupOptions.output.manualChunks to improve chunking: https://rollupjs.org/configuration-options/#output-manualchunks\n- Adjust chunk size limit for this warning via build.chunkSizeWarningLimit.",
      "expectedOccurrences": 1
    },
    "qualitySelfTests": { "dispatcher": 2, "node": 9, "total": 11 }
  }
  ~~~

  Create `scripts/quality/p00.test.mjs` completely:

  ~~~js
  import assert from 'node:assert/strict';
  import { randomBytes } from 'node:crypto';
  import { existsSync, mkdtempSync, mkdirSync, readFileSync, readdirSync, realpathSync, symlinkSync, writeFileSync } from 'node:fs';
  import { tmpdir } from 'node:os';
  import { join } from 'node:path';
  import test from 'node:test';
  import {
    aggregateRequiredGates,
    assertAggregate,
    assertExactKeys,
    buildEvidence,
    contract,
    contractSha256,
    measureBundle,
    sha256,
    stableJson,
    validateSchema,
    validateEvidence,
  } from './p00.mjs';

  const sha40 = '0123456789abcdef0123456789abcdef01234567';
  const sha64 = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
  const temp = (name) => realpathSync(mkdtempSync(join(tmpdir(), 'dorzak-p00-' + name + '-')));
  const json = (path, value) => writeFileSync(path, stableJson(value));
  const inputs = () => ({
    contractSha256,
    runtime: { php: '8.5.1', composer: '2.8.0', node: process.versions.node, npm: '10.9.0' },
    lockfileSha256: { composer: sha64, npm: sha64 },
    postgresql: {
      kind: 'oci', identity: 'registry.test/postgres@sha256:' + sha64,
      policy: 'postgresql-16-test-closed-transport-v1',
    },
    playwright: { packageVersion: '1.57.0', chromiumRevision: '1234567' },
    bundleAlgorithms: { assetSelection: 'html-module-entry-and-modulepreload-v1', gzip: 'node-zlib-level-9' },
    runnerClasses: { local: 'local-linux-x64', ci: 'ci-linux-x64' },
  });
  const platformObservation = (runnerRole = 'local') => ({
    os: 'linux', arch: 'x64', osRelease: 'test-kernel', runnerRole,
    runnerClass: inputs().runnerClasses[runnerRole],
    zlib: process.versions.zlib, chromiumExecutableSha256: sha64,
  });
  const record = (job, root, runnerRole = 'local') => {
    const declared = inputs();
    const artifactNames = {
      'composer-validation': ['dispatcherTap', 'p00NodeTap'],
      'php-style-static': [],
      sqlite: ['junit'],
      'postgresql-16': ['junit', 'postgresqlIdentity'],
      frontend: ['vitest', 'bundle', 'viteBuildLog'],
      playwright: ['playwrightJson'],
    };
    const artifactPaths = {
      dispatcherTap: 'dispatcher.tap', p00NodeTap: 'p00-node.tap', junit: job.name + '.junit.xml',
      postgresqlIdentity: 'postgresql-identity.json', vitest: 'vitest.json', bundle: 'bundle.json',
      viteBuildLog: 'vite-build.log', playwrightJson: 'playwright.json',
    };
    const directory = join(root, job.name);
    mkdirSync(directory);
    const log = 'log for ' + job.name + '\n';
    writeFileSync(join(directory, 'job.log'), log);
    const artifacts = Object.fromEntries(artifactNames[job.name].map((name) => {
      const path = artifactPaths[name];
      const bytes = name === 'dispatcherTap'
        ? 'TAP version 13\nok 1 - list\nok 2 - invalid\n1..2\n# dispatcher reporter diagnostic\n'
        : name === 'p00NodeTap'
          ? 'TAP version 13\n' + Array.from({ length: 9 }, (_, index) => `ok ${index + 1} - node`).join('\n') + '\n1..9\n# node reporter diagnostic\n'
          : name === 'junit'
            ? `<testsuite tests="${job.testCount}" failures="0" errors="0" skipped="0"></testsuite>\n`
            : name === 'vitest'
              ? stableJson({ numTotalTests: 8, numFailedTests: 0, numPendingTests: 0 })
              : name === 'playwrightJson'
                ? stableJson({ stats: { expected: 9, unexpected: 0, flaky: 0, skipped: 0 } })
                : name === 'postgresqlIdentity'
                  ? stableJson({ placeholder: true })
                  : name === 'bundle'
                    ? stableJson({ placeholder: true })
                    : 'artifact ' + job.name + ' ' + name + '\n';
      writeFileSync(join(directory, path), bytes);
      return [name, { path, sha256: sha256(bytes) }];
    }));
    return {
      schemaVersion: 1, job: job.name, integratedSha: sha40, status: 'passed',
      command: job.command,
      exitCode: 0, retryAttempt: 1, testCount: job.testCount, failureCount: 0,
      unexplainedSkipCount: 0, durationMs: 10, contractSha256,
      inputFingerprintSha256: sha256(stableJson(declared)), inputs: declared, logSha256: sha256(log),
      platformObservationFingerprintSha256: sha256(stableJson(platformObservation(runnerRole))),
      platformObservation: platformObservation(runnerRole),
      artifacts,
    };
  };
  const resultSet = (directory, mutate = () => {}) => {
    const records = contract.jobs.map((job) => record(job, directory));
    mutate(records);
    for (const value of records) json(join(directory, value.job, 'result.json'), value);
    return records;
  };

  test('contract freezes ordered jobs, counts, budget, and open Vite debt', () => {
    assert.deepEqual(contract.jobs.map((job) => [job.name, job.testCount]), [
      ['composer-validation', 11], ['php-style-static', 0], ['sqlite', 446],
      ['postgresql-16', 450], ['frontend', 8], ['playwright', 9],
    ]);
    assert.equal(contract.bundle.initialGzipLimitBytes, 216700);
    assert.equal(contract.bundle.debtStatus, 'accepted-open');
    assert.equal(contract.bundle.expectedOccurrences, 1);
  });

  test('bundle gate measures entry/preload and rejects escape, absent debt, and growth', () => {
    const root = temp('bundle');
    mkdirSync(join(root, 'dist/assets'), { recursive: true });
    writeFileSync(join(root, 'dist/index.html'), '<script type="module" src="/assets/entry.js"></script><link rel="modulepreload" href="/assets/vendor.js">');
    writeFileSync(join(root, 'dist/assets/entry.js'), 'A'.repeat(500001));
    writeFileSync(join(root, 'dist/assets/vendor.js'), 'export const value = 1;');
    writeFileSync(join(root, 'vite.log'), contract.bundle.warning + '\n');
    const measured = measureBundle(join(root, 'dist'), 216700, join(root, 'vite.log'), join(root, 'artifacts'));
    assert.deepEqual(measured.files.map((file) => file.path), ['assets/entry.js', 'assets/vendor.js']);
    assert.deepEqual(measured.largeChunkDebt.affectedFiles, ['assets/entry.js']);
    assert.equal(measured.largeChunkDebt.messageSha256, sha256(contract.bundle.warning));
    assert.deepEqual(JSON.parse(readFileSync(join(root, 'artifacts/bundle.json'))), measured);

    writeFileSync(join(root, 'dist/index.html'), '<script type="module" src="../escape.js"></script>');
    assert.throws(() => measureBundle(join(root, 'dist'), 216700, null, join(root, 'escape-artifacts')));
    writeFileSync(join(root, 'dist/assets/entry.js'), 'export {};');
    writeFileSync(join(root, 'dist/assets/vendor.js'), 'export const value = 1;');
    writeFileSync(join(root, 'dist/index.html'), '<script type="module" src="/assets/vendor.js"></script>');
    assert.throws(() => measureBundle(join(root, 'dist'), 216700, null, join(root, 'no-debt-artifacts')));
    writeFileSync(join(root, 'dist/assets/entry.js'), randomBytes(600000));
    writeFileSync(join(root, 'dist/index.html'), '<script type="module" src="/assets/entry.js"></script>');
    assert.throws(() => measureBundle(join(root, 'dist'), 216700, null, join(root, 'over-artifacts')));
  });

  test('aggregate accepts only six same-SHA same-input exact-count attempt-one records', () => {
    const pass = temp('aggregate-pass');
    resultSet(pass);
    assert.equal(aggregateRequiredGates(pass).status, 'passed');
    const mutations = [
      (values) => values.pop(),
      (values) => { values[0].integratedSha = 'f'.repeat(40); },
      (values) => { values[1].status = 'failed'; values[1].exitCode = 1; },
      (values) => { values[2].retryAttempt = 2; },
      (values) => { values[3].unexplainedSkipCount = 1; },
      (values) => { values[4].testCount = 7; },
      (values) => { values[5].inputFingerprintSha256 = 'f'.repeat(64); },
    ];
    for (const [index, mutate] of mutations.entries()) {
      const directory = temp('aggregate-fail-' + index);
      resultSet(directory, mutate);
      assert.throws(() => aggregateRequiredGates(directory));
    }
    const aggregate = aggregateRequiredGates(pass);
    for (const mutate of [
      (value) => { value.retryAttempt = 2; },
      (value) => { value.testCount -= 1; },
      (value) => { value.failureCount = 1; },
      (value) => { value.unexplainedSkipCount = 1; },
    ]) {
      const changed = structuredClone(aggregate);
      mutate(changed);
      assert.throws(() => assertAggregate(changed, 'local'));
    }
  });

  test('evidence builder validates hashes, review, two runs, debt, and secret rejection', () => {
    const root = temp('evidence');
    const local = join(root, 'local');
    mkdirSync(local);
    resultSet(local);
    let aggregate = aggregateRequiredGates(local);
    const postgresqlObservation = {
      kind: inputs().postgresql.kind,
      identity: inputs().postgresql.identity,
      attestationSha256: sha64,
      instanceNonceSha256: sha64,
      endpointSha256: sha64,
      serverVersionNum: 160000,
      databaseName: 'dorzak_test',
    };
    json(join(local, 'postgresql-16/postgresql-identity.json'), postgresqlObservation);
    json(join(local, 'frontend/bundle.json'), {
      schemaVersion: 1, gzipBytes: 210000, minifiedBytes: 500001,
      limitBytes: 216700, nodeVersion: process.versions.node, zlibVersion: process.versions.zlib,
      files: [{ path: 'assets/index.js', minifiedBytes: 500001, gzipBytes: 210000 }],
      largeChunkDebt: {
        status: 'accepted-open', thresholdBytes: 500000, affectedFiles: ['assets/index.js'],
        message: contract.bundle.warning, messageSha256: sha256(contract.bundle.warning),
        occurrenceCount: 1,
      },
    });
    for (const [job, artifact] of [['postgresql-16', 'postgresqlIdentity'], ['frontend', 'bundle']]) {
      const resultPath = join(local, job, 'result.json');
      const result = JSON.parse(readFileSync(resultPath, 'utf8'));
      const reference = result.artifacts[artifact];
      reference.sha256 = sha256(readFileSync(join(local, job, reference.path)));
      json(resultPath, result);
    }
    aggregate = aggregateRequiredGates(local);
    const reviewJson = join(root, 'review.json');
    const reviewMarkdown = join(root, 'review.md');
    json(reviewJson, { schemaVersion: 1, baseSha: sha40, codeSha: sha40, critical: 0, important: 0, minor: [] });
    writeFileSync(reviewMarkdown, [
      '# Independent P00 Review', '', 'BASE_SHA: ' + sha40, 'CODE_SHA: ' + sha40,
      'Critical: 0', 'Important: 0', '', '## Minor', '', '- None.', '',
    ].join('\n'));
    const ci = (runId, observation) => {
      const ciPlatform = platformObservation('ci');
      const jobs = structuredClone(aggregate.jobs).map((job) => ({
        ...job,
        platformObservation: ciPlatform,
        platformObservationFingerprintSha256: sha256(stableJson(ciPlatform)),
      }));
      jobs.find((job) => job.job === 'postgresql-16').artifacts.postgresqlIdentity.sha256 = sha256(stableJson(observation));
      return {
        schemaVersion: 1, provider: 'approved-provider', runId, attempt: 1,
        integratedSha: sha40, contractSha256,
        inputFingerprintSha256: aggregate.inputFingerprintSha256,
        inputs: structuredClone(aggregate.inputs),
        platformObservationFingerprintSha256: sha256(stableJson(ciPlatform)),
        platformObservation: ciPlatform,
        postgresqlObservation: observation,
        requiredGate: { status: 'passed', jobs: 6 }, jobs,
      };
    };
    const ci1 = join(root, 'ci-1.json');
    const ci2 = join(root, 'ci-2.json');
    const secondPostgresqlObservation = {
      ...postgresqlObservation,
      attestationSha256: '1'.repeat(64), instanceNonceSha256: '2'.repeat(64),
      endpointSha256: '3'.repeat(64), databaseName: 'dorzak_second_test',
    };
    json(ci1, ci('run-1', postgresqlObservation));
    json(ci2, ci('run-2', secondPostgresqlObservation));
    const manifest = buildEvidence({
      outputDirectory: join(root, 'output'), baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    });
    assert.equal(validateEvidence(manifest).files.length, 7);
    const manifestPath = join(root, 'output', 'manifest.json');
    const originalManifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
    for (const mutate of [
      (value) => { value.local.platformObservation.os = 'different-os'; },
      (value) => { value.bundle.gzipBytes -= 1; },
      (value) => { value.review.minor = ['tampered summary']; },
      (value) => { value.ciRuns[0].provider = 'different-provider'; },
    ]) {
      const changedSummary = structuredClone(originalManifest);
      mutate(changedSummary);
      json(manifestPath, changedSummary);
      assert.throws(() => validateEvidence(manifestPath));
    }
    json(manifestPath, originalManifest);
    assert.notDeepEqual(JSON.parse(readFileSync(ci1)).postgresqlObservation, JSON.parse(readFileSync(ci2)).postgresqlObservation);
    assert.throws(() => stableJson({ authorization: 'Bearer unsafe-value' }));
    const crossBinding = ci('run-2', secondPostgresqlObservation);
    crossBinding.jobs[0].inputs = { ...crossBinding.jobs[0].inputs, runtime: { ...crossBinding.jobs[0].inputs.runtime, php: '9.9.9' } };
    crossBinding.jobs[0].inputFingerprintSha256 = sha256(stableJson(crossBinding.jobs[0].inputs));
    json(ci2, crossBinding);
    assert.throws(() => buildEvidence({
      outputDirectory: join(root, 'cross-binding-rejected'), baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    }));
    json(ci2, ci('run-2', secondPostgresqlObservation));
    const unboundPostgresql = ci('run-2', secondPostgresqlObservation);
    unboundPostgresql.postgresqlObservation.databaseName = 'unbound_test';
    json(ci2, unboundPostgresql);
    assert.throws(() => buildEvidence({
      outputDirectory: join(root, 'postgresql-unbound-rejected'), baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    }));
    json(ci2, ci('run-2', secondPostgresqlObservation));
    json(reviewJson, { schemaVersion: 1, baseSha: sha40, codeSha: sha40, critical: 1, important: 0, minor: [] });
    const rejected = join(root, 'rejected');
    assert.throws(() => buildEvidence({
      outputDirectory: rejected, baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    }));
    assert.equal(existsSync(rejected), false);
    json(reviewJson, { schemaVersion: 1, baseSha: sha40, codeSha: sha40, critical: 0, important: 0, minor: [] });
    for (const faultPoint of ['after-temp', 'after-rename']) {
      const name = 'fault-' + faultPoint;
      const path = join(root, name);
      assert.throws(() => buildEvidence({
        outputDirectory: path, baseSha: sha40, codeSha: sha40,
        integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
        reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown, faultPoint,
      }));
      assert.equal(existsSync(path), false);
      assert.equal(readdirSync(root).some((entry) => entry.startsWith('.' + name + '.tmp-')), false);
    }
  });

  test('aggregate rejects changed raw bytes, extra roots, and symlinks', () => {
    const changed = temp('raw-changed');
    const records = resultSet(changed);
    const reference = Object.values(records.find((value) => value.job === 'sqlite').artifacts)[0];
    writeFileSync(join(changed, 'sqlite', reference.path), 'tampered\n');
    assert.throws(() => aggregateRequiredGates(changed));
    const extra = temp('raw-extra');
    resultSet(extra);
    writeFileSync(join(extra, 'unexpected'), 'extra\n');
    assert.throws(() => aggregateRequiredGates(extra));
    const linked = temp('raw-link');
    resultSet(linked);
    symlinkSync(join(linked, 'sqlite'), join(linked, 'sqlite-alias'));
    assert.throws(() => aggregateRequiredGates(linked));
  });

  test('aggregate cross-binds portable inputs while preserving per-job platform observations', () => {
    const varied = temp('platform-varied');
    resultSet(varied, (values) => {
      values[5].platformObservation.osRelease = 'different-ephemeral-image-release';
      values[5].platformObservationFingerprintSha256 = sha256(stableJson(values[5].platformObservation));
    });
    const variedAggregate = aggregateRequiredGates(varied);
    assert.notEqual(
      variedAggregate.jobs[0].platformObservationFingerprintSha256,
      variedAggregate.jobs[5].platformObservationFingerprintSha256,
    );
    assertAggregate(variedAggregate, 'local');
    const directory = temp('runner-mismatch');
    resultSet(directory, (values) => {
      values[5].platformObservation.runnerClass = 'different-runner';
      values[5].platformObservationFingerprintSha256 = sha256(stableJson(values[5].platformObservation));
    });
    assert.throws(() => aggregateRequiredGates(directory));
    const swapped = temp('runner-role-swapped');
    resultSet(swapped, (values) => {
      for (const value of values) {
        value.platformObservation = platformObservation('ci');
        value.platformObservationFingerprintSha256 = sha256(stableJson(value.platformObservation));
      }
    });
    assert.throws(() => assertAggregate(aggregateRequiredGates(swapped), 'local'));
  });

  test('secret rejection covers keys, headers, query strings, URLs and token families', () => {
    for (const value of [
      { apiKey: 'x' }, { clientSecret: 'x' }, { accessToken: 'x' }, { dbUrl: 'x' },
      'Cookie: sid=x', 'Authorization: Basic dXNlcjpwYXNz', '?token=x',
      'postgresql://user:pass@example.test/db', 'AKIA1234567890ABCDEF',
      'ghp_abcdefghijklmnopqrstuvwxyz', 'eyJabc.def.ghi',
    ]) assert.throws(() => stableJson(value));
  });

  test('closed schema validator rejects missing, extra, type and pattern mutations', () => {
    const schema = {
      type: 'object', additionalProperties: false, required: ['sha'],
      properties: { sha: { type: 'string', pattern: '^[0-9a-f]{4}$' } },
    };
    validateSchema(schema, { sha: 'abcd' });
    assert.throws(() => validateSchema(schema, {}));
    assert.throws(() => validateSchema(schema, { sha: 'abcd', extra: true }));
    assert.throws(() => validateSchema(schema, { sha: 1 }));
    assert.throws(() => validateSchema(schema, { sha: 'zzzz' }));
  });

  test('exact-key helper rejects unknown evidence fields', () => {
    assertExactKeys({ schemaVersion: 1, state: 'passed' }, ['schemaVersion', 'state'], '$.sample');
    assert.throws(() => assertExactKeys(
      { schemaVersion: 1, state: 'passed', credential: 'x' },
      ['schemaVersion', 'state'], '$.sample',
    ));
  });
  ~~~

  Run both test entry points before their implementations exist:

  ~~~bash
  sh scripts/quality/test-run-p00.sh
  node --test scripts/quality/p00.test.mjs
  ~~~

  Expected failure: the dispatcher and `p00.mjs` are absent.

- [ ] **Create the exact six-job dispatcher.**

  `scripts/quality/run-p00`:

  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  ARTIFACTS="${P00_ARTIFACT_DIR:-$ROOT/.artifacts/p00}"
  export P00_ARTIFACT_DIR="$ARTIFACTS"
  mkdir -p "$ARTIFACTS"
  jobs=(composer-validation php-style-static sqlite postgresql-16 frontend playwright)

  if [[ "${1:-}" == "--list" ]]; then
    printf '%s\n' "${jobs[@]}"
    exit 0
  fi
  job="${1:-}"
  case "$job" in
    composer-validation|php-style-static|sqlite|postgresql-16|frontend|playwright) ;;
    *) echo "Unknown P00 job: $job" >&2; exit 64 ;;
  esac
  : "${P00_RUNNER_ROLE:?P00_RUNNER_ROLE is required}"
  : "${P00_RUNNER_CLASS:?P00_RUNNER_CLASS is required}"
  node --input-type=module - "$ROOT/$P00_CONTROL_RECORD" "$P00_RUNNER_ROLE" "$P00_RUNNER_CLASS" <<'NODE'
  import { readFileSync } from 'node:fs';
  const record = JSON.parse(readFileSync(process.argv[2], 'utf8'));
  const role = process.argv[3];
  if (!['local', 'ci'].includes(role)
      || process.argv[4] !== record.execution?.runnerClasses?.[role]) process.exit(1);
  NODE
  JOB_ARTIFACTS="$ARTIFACTS/$job"
  test ! -L "$JOB_ARTIFACTS"
  mkdir -p "$JOB_ARTIFACTS"
  export P00_JOB_ARTIFACT_DIR="$JOB_ARTIFACTS"
  export P00_JOB="$job"
  if [[ -n "$(git -C "$ROOT" status --short --untracked-files=normal)" ]]; then
    echo 'P00 job refused: checkout is not clean.' >&2
    exit 65
  fi
  started="$(node -p 'Date.now()')"
  log="$JOB_ARTIFACTS/job.log"

  run_job() {
    case "$job" in
      composer-validation)
        (cd "$ROOT" && sh scripts/quality/test-run-p00.sh | tee "$JOB_ARTIFACTS/dispatcher.tap")
        (cd "$ROOT" && node --test --test-reporter=tap scripts/quality/p00.test.mjs | tee "$JOB_ARTIFACTS/p00-node.tap")
        (cd "$ROOT/backend" && composer validate --strict --no-check-publish)
        ;;
      php-style-static)
        (cd "$ROOT/backend" && vendor/bin/pint --test && composer analyse)
        ;;
      sqlite)
        (cd "$ROOT/backend" && vendor/bin/phpunit -c phpunit.xml --log-junit "$JOB_ARTIFACTS/sqlite.junit.xml")
        ;;
      postgresql-16)
        "$ROOT/scripts/quality/run-qualified-postgres" quality-job
        ;;
      frontend)
        (
          cd "$ROOT"
          npm run format:check
          npm run lint
          npm run typecheck
          npm run test:unit -- --reporter=json --outputFile="$JOB_ARTIFACTS/vitest.json"
          npm run build 2>&1 | tee "$JOB_ARTIFACTS/vite-build.log"
          node scripts/quality/p00.mjs bundle dist 216700 "$JOB_ARTIFACTS/vite-build.log"
        )
        ;;
      playwright)
        (cd "$ROOT" && npm run test:e2e)
        cp -- "$ROOT/test-results/results.json" "$JOB_ARTIFACTS/playwright.json"
        ;;
    esac
  }

  set +e
  run_job 2>&1 | tee "$log"
  command_status="${PIPESTATUS[0]}"
  set -e
  finished="$(node -p 'Date.now()')"
  set +e
  node "$ROOT/scripts/quality/p00.mjs" write-result "$job" "$command_status" "$((finished-started))" "$log"
  writer_status="$?"
  set -e
  if [[ "$command_status" -ne 0 ]]; then exit "$command_status"; fi
  exit "$writer_status"
  ~~~

  `scripts/quality/test-run-p00.sh`:

  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  expected=$'composer-validation\nphp-style-static\nsqlite\npostgresql-16\nfrontend\nplaywright'
  actual="$("$ROOT/scripts/quality/run-p00" --list)"
  [[ "$actual" == "$expected" ]]
  set +e
  output="$("$ROOT/scripts/quality/run-p00" invalid-job 2>&1)"
  result="$?"
  set -e
  [[ "$result" -eq 64 ]]
  [[ "$output" == 'Unknown P00 job: invalid-job' ]]
  printf '%s\n' \
    'TAP version 13' \
    'ok 1 - lists six ordered jobs' \
    'ok 2 - invalid job exits 64' \
    '1..2'
  ~~~

  `scripts/quality/run-postgres-16`:

  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail
  if [[ "${P00_PG_QUALIFIED_CANDIDATE:-}" != 1 || "${P00_PG_SCHEMA_READY:-}" != 1 \
    || ! "${P00_PG_QUALIFICATION_NONCE_SHA256:-}" =~ ^[0-9a-f]{64}$ \
    || -z "${DB_URL:-}" || -n "${P00_PG_TEST_SUBSTITUTE_URL_PATH:-}" ]]; then
    printf '%s\n' 'P00_POSTGRES_JOB_REFUSED unqualified candidate' >&2
    exit 65
  fi
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  ARTIFACTS="${P00_JOB_ARTIFACT_DIR:?P00_JOB_ARTIFACT_DIR is required}"
  test ! -L "$ARTIFACTS"
  observation="$(P00_ROOT="$ROOT" php -r '
    require getenv("P00_ROOT")."/backend/vendor/autoload.php";
    $url = getenv("DB_URL") ?: "";
    $profile = App\Support\PostgresConnectionProfile::fromUrl($url);
    $pdo = $profile->pdo();
    $row = App\Support\PostgresQualificationGuard::assertPdo(
      $pdo, $profile, getenv("P00_PG_INSTANCE_NONCE_SHA256") ?: "",
    );
    $endpoint = hash("sha256", $profile->host.":".$profile->port."?sslmode=".$profile->sslmode);
    echo $row["server_version_num"], "\n", $row["database"], "\n",
      $row["instance_nonce_sha256"], "\n", $endpoint, "\n";
  ')"
  server_version_num="$(printf '%s\n' "$observation" | sed -n '1p')"
  database_name="$(printf '%s\n' "$observation" | sed -n '2p')"
  instance_nonce_sha256="$(printf '%s\n' "$observation" | sed -n '3p')"
  endpoint_sha256="$(printf '%s\n' "$observation" | sed -n '4p')"
  node "$ROOT/scripts/quality/p00.mjs" postgres-identity \
    "$server_version_num" "$database_name" "$instance_nonce_sha256" "$endpoint_sha256"
  exec "$ROOT/backend/vendor/bin/phpunit" \
    --configuration "$ROOT/backend/phpunit.pgsql.xml" \
    --log-junit "$ARTIFACTS/postgresql-16.junit.xml"
  ~~~

  Make all three shell scripts executable. Append `.artifacts/` to `.gitignore` with this idempotent deterministic edit:

  ~~~bash
  chmod +x scripts/quality/run-p00 scripts/quality/test-run-p00.sh scripts/quality/run-postgres-16
  node --input-type=module <<'NODE'
  import { readFileSync, writeFileSync } from 'node:fs';
  const path = '.gitignore';
  const current = readFileSync(path, 'utf8').replace(/\n*$/, '\n');
  if (current.split('\n').includes('.artifacts/')) throw new Error('.artifacts/ already exists');
  writeFileSync(path, current + '.artifacts/\n');
  NODE
  ~~~

- [ ] **Create the complete deterministic quality and evidence module.**

  Create scripts/quality/p00.mjs exactly:

  ~~~js
  import { createHash } from 'node:crypto';
  import { execFileSync } from 'node:child_process';
  import {
    copyFileSync,
    existsSync,
    lstatSync,
    mkdtempSync,
    mkdirSync,
    readFileSync,
    readdirSync,
    realpathSync,
    renameSync,
    rmSync,
    writeFileSync,
  } from 'node:fs';
  import { basename, dirname, join, relative, resolve } from 'node:path';
  import { arch, platform, release } from 'node:os';
  import { fileURLToPath } from 'node:url';
  import { gzipSync } from 'node:zlib';

  const MODULE = fileURLToPath(import.meta.url);
  const ROOT = resolve(dirname(MODULE), '../..');
  const ARTIFACTS = () => resolve(process.env.P00_ARTIFACT_DIR || join(ROOT, '.artifacts/p00'));
  const JOB_ARTIFACTS = () => resolve(process.env.P00_JOB_ARTIFACT_DIR || join(ARTIFACTS(), requiredEnvironment('P00_JOB')));
  const SHA40 = /^[0-9a-f]{40}$/;
  const SHA64 = /^[0-9a-f]{64}$/;
  const origins = new WeakMap();

  function invariant(condition, message) {
    if (!condition) throw new Error(message);
  }

  export function sha256(value) {
    return createHash('sha256').update(value).digest('hex');
  }

  function canonical(value) {
    if (Array.isArray(value)) return value.map(canonical);
    if (value && typeof value === 'object') {
      return Object.fromEntries(
        Object.keys(value).sort().map((key) => [key, canonical(value[key])]),
      );
    }
    return value;
  }

  function rejectSecrets(value, path = '$') {
    const blockedKeys = new Set([
      'authorization', 'proxyauthorization', 'cookie', 'setcookie', 'apikey',
      'clientsecret', 'accesstoken', 'refreshtoken', 'idtoken', 'password',
      'passwd', 'secret', 'token', 'dburl', 'databaseurl', 'dsn', 'credential',
      'privatekey', 'connectionstring',
    ]);
    if (typeof value === 'string') {
      invariant(!/-----BEGIN [A-Z ]*PRIVATE KEY-----/i.test(value), 'Private key at ' + path);
      invariant(!/\b(?:bearer|basic)\s+[A-Za-z0-9._~+\/=-]+/i.test(value), 'Authorization value at ' + path);
      invariant(!/^(?:cookie|set-cookie)\s*:/im.test(value), 'Cookie header at ' + path);
      invariant(!/\b(?:api[_-]?key|client[_-]?secret|access[_-]?token|refresh[_-]?token|id[_-]?token|password|passwd|secret|token|db[_-]?url|database[_-]?url|dsn|credential|private[_-]?key|connection[_-]?string)\s*[=:]\s*\S+/i.test(value), 'Secret assignment at ' + path);
      invariant(!/[?&](?:api[_-]?key|token|secret|password|credential)=/i.test(value), 'Secret query key at ' + path);
      invariant(!/[a-z][a-z0-9+.-]*:\/\/[^\/\s:@]+:[^\/\s@]+@/i.test(value), 'Credential URL at ' + path);
      invariant(!/\bAKIA[0-9A-Z]{16}\b/.test(value), 'AWS access key at ' + path);
      invariant(!/\b(?:gh[pousr]_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,})\b/.test(value), 'GitHub token at ' + path);
      invariant(!/\beyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\b/.test(value), 'JWT at ' + path);
      return;
    }
    if (Array.isArray(value)) {
      value.forEach((item, index) => rejectSecrets(item, path + '[' + index + ']'));
      return;
    }
    if (value && typeof value === 'object') {
      for (const [key, item] of Object.entries(value)) {
        const normalized = key.toLowerCase().replace(/[^a-z0-9]/g, '');
        invariant(!blockedKeys.has(normalized), 'Secret key at ' + path + '.' + key);
        rejectSecrets(item, path + '.' + key);
      }
    }
  }

  export function stableJson(value) {
    rejectSecrets(value);
    return JSON.stringify(canonical(value), null, 2) + '\n';
  }

  function readJson(path) {
    return JSON.parse(readFileSync(path, 'utf8'));
  }

  function writeJson(path, value) {
    mkdirSync(dirname(path), { recursive: true });
    writeFileSync(path, stableJson(value), { flag: 'w' });
  }

  const contractBytes = readFileSync(join(ROOT, 'scripts/quality/p00-contract.json'));
  export const contract = JSON.parse(contractBytes);
  export const contractSha256 = sha256(contractBytes);
  invariant(contract.schemaVersion === 1, 'Unsupported P00 contract');
  invariant(contract.jobs.length === 6, 'P00 contract must have six jobs');

  function command(file, args, cwd = ROOT) {
    return execFileSync(file, args, {
      cwd,
      encoding: 'utf8',
      stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
  }

  function version(file, args, cwd = ROOT) {
    const match = command(file, args, cwd).match(/\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?/);
    invariant(match, 'Cannot parse version for ' + file);
    return match[0];
  }

  function requiredEnvironment(name) {
    const value = process.env[name];
    invariant(typeof value === 'string' && value.length > 0, name + ' is required');
    return value;
  }

  function expectedPostgresqlIdentity() {
    const kind = requiredEnvironment('P00_PG_IDENTITY_KIND');
    const identity = requiredEnvironment('P00_PG_IDENTITY');
    const attestationSha256 = requiredEnvironment('P00_PG_ATTESTATION_SHA256');
    const attestationPath = requiredEnvironment('P00_PG_ATTESTATION_PATH');
    const instanceNonceSha256 = requiredEnvironment('P00_PG_INSTANCE_NONCE_SHA256');
    invariant(kind === 'oci' || kind === 'external-attestation', 'Unapproved PostgreSQL identity kind');
    invariant(SHA64.test(attestationSha256), 'PostgreSQL attestation hash is invalid');
    invariant(SHA64.test(instanceNonceSha256), 'PostgreSQL instance nonce hash is invalid');
    invariant(sha256(readFileSync(attestationPath)) === attestationSha256, 'PostgreSQL attestation bytes changed');
    const attestation = readJson(attestationPath);
    invariant(stableJson(Object.keys(attestation)) === stableJson([
      'schemaVersion', 'kind', 'identity', 'serverMajor', 'immutable', 'instanceNonceSha256',
    ]), 'PostgreSQL attestation keys are not closed');
    invariant(attestation.schemaVersion === 2 && attestation.kind === kind
      && attestation.identity === identity && attestation.serverMajor === 16
      && attestation.immutable === true && attestation.instanceNonceSha256 === instanceNonceSha256,
    'PostgreSQL attestation content mismatch');
    if (kind === 'oci') {
      invariant(/@sha256:[0-9a-f]{64}$/.test(identity), 'OCI PostgreSQL identity is mutable');
    } else {
      invariant(/^external:[A-Za-z0-9._/-]+@[A-Za-z0-9._:-]+#sha256:[0-9a-f]{64}$/.test(identity),
        'External PostgreSQL attestation identity is mutable');
    }
    rejectSecrets(identity, 'postgresql.identity');
    return { kind, identity, attestationSha256, instanceNonceSha256 };
  }

  function collectInputs() {
    const chromiumPath = command(process.execPath, [
      '-e',
      "process.stdout.write(require('playwright').chromium.executablePath())",
    ]);
    const playwrightPackage = readJson(join(ROOT, 'node_modules/@playwright/test/package.json'));
    const browsers = readJson(join(ROOT, 'node_modules/playwright-core/browsers.json'));
    const chromium = browsers.browsers.find((browser) => browser.name === 'chromium');
    invariant(chromium && /^\d+$/.test(chromium.revision), 'Playwright Chromium revision is missing');
    const control = readJson(resolve(ROOT, requiredEnvironment('P00_CONTROL_RECORD')));
    const runnerClasses = control.execution?.runnerClasses;
    assertExactKeys(runnerClasses, ['local', 'ci'], '$.control.execution.runnerClasses');
    invariant(Object.values(runnerClasses).every((value) =>
      typeof value === 'string' && /^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/.test(value)),
    'Runner classes are invalid');
    invariant(runnerClasses.local !== runnerClasses.ci, 'Runner classes must be distinct');
    const runnerRole = requiredEnvironment('P00_RUNNER_ROLE');
    invariant(['local', 'ci'].includes(runnerRole), 'Runner role is invalid');
    invariant(requiredEnvironment('P00_RUNNER_CLASS') === runnerClasses[runnerRole],
      'Runner role/class binding mismatch');
    const portableInputs = {
      contractSha256,
      runtime: {
        php: command('php', ['-r', 'echo PHP_VERSION;']),
        composer: version('composer', ['--version'], join(ROOT, 'backend')),
        node: process.versions.node,
        npm: version('npm', ['--version']),
      },
      lockfileSha256: {
        composer: sha256(readFileSync(join(ROOT, 'backend/composer.lock'))),
        npm: sha256(readFileSync(join(ROOT, 'package-lock.json'))),
      },
      postgresql: (() => {
        const value = expectedPostgresqlIdentity();
        return {
          kind: value.kind,
          identity: value.identity,
          policy: 'postgresql-16-test-closed-transport-v1',
        };
      })(),
      playwright: {
        packageVersion: playwrightPackage.version,
        chromiumRevision: chromium.revision,
      },
      bundleAlgorithms: { assetSelection: 'html-module-entry-and-modulepreload-v1', gzip: 'node-zlib-level-9' },
      runnerClasses,
    };
    invariant(portableInputs.runtime.php === requiredEnvironment('P00_PHP_VERSION'), 'PHP pin mismatch');
    invariant(portableInputs.runtime.composer === requiredEnvironment('P00_COMPOSER_VERSION'), 'Composer pin mismatch');
    invariant(portableInputs.runtime.node === requiredEnvironment('P00_NODE_VERSION'), 'Node pin mismatch');
    invariant(portableInputs.runtime.npm === requiredEnvironment('P00_NPM_VERSION'), 'npm pin mismatch');
    return {
      portableInputs,
      platformObservation: {
        os: platform(), arch: arch(), osRelease: release(),
        runnerRole,
        runnerClass: requiredEnvironment('P00_RUNNER_CLASS'),
        zlib: process.versions.zlib,
        chromiumExecutableSha256: sha256(readFileSync(chromiumPath)),
      },
    };
  }

  function safeAssetPath(distDirectory, url) {
    invariant(!/[?#]/.test(url), 'Bundle URL query/fragment is prohibited: ' + url);
    invariant(!/^[a-z][a-z0-9+.-]*:/i.test(url), 'External bundle URL is prohibited: ' + url);
    const decoded = decodeURIComponent(url).replace(/^\/+/, '');
    invariant(decoded.length > 0 && !decoded.includes('\\'), 'Invalid bundle URL: ' + url);
    invariant(!decoded.split('/').includes('..'), 'Bundle URL escapes dist: ' + url);
    const root = realpathSync(distDirectory);
    const candidate = resolve(root, decoded);
    invariant(candidate.startsWith(root + '/'), 'Bundle path escapes dist: ' + url);
    const stat = lstatSync(candidate);
    invariant(stat.isFile() && !stat.isSymbolicLink(), 'Bundle asset is not a regular file: ' + decoded);
    invariant(realpathSync(candidate) === candidate, 'Bundle asset aliases another path: ' + decoded);
    return { path: decoded, absolute: candidate };
  }

  function javascriptFiles(directory, root = directory) {
    const files = [];
    for (const name of readdirSync(directory).sort()) {
      const path = join(directory, name);
      const stat = lstatSync(path);
      invariant(!stat.isSymbolicLink(), 'Symlink in bundle output: ' + relative(root, path));
      if (stat.isDirectory()) files.push(...javascriptFiles(path, root));
      else if (stat.isFile() && name.endsWith('.js')) files.push(path);
      else invariant(stat.isFile(), 'Special file in bundle output: ' + relative(root, path));
    }
    return files;
  }

  function warningOccurrences(log, warning) {
    return log.split(warning).length - 1;
  }

  function assertBundle(value) {
    rejectSecrets(value, '$.bundle');
    assertExactKeys(value, [
      'schemaVersion', 'files', 'minifiedBytes', 'gzipBytes', 'limitBytes',
      'nodeVersion', 'zlibVersion', 'largeChunkDebt',
    ], '$.bundle');
    value.files?.forEach((file, index) => assertExactKeys(
      file, ['path', 'minifiedBytes', 'gzipBytes'], '$.bundle.files[' + index + ']',
    ));
    assertExactKeys(value.largeChunkDebt, [
      'status', 'thresholdBytes', 'affectedFiles', 'message', 'messageSha256', 'occurrenceCount',
    ], '$.bundle.largeChunkDebt');
    invariant(value.schemaVersion === 1, 'Bundle schema mismatch');
    invariant(typeof value.nodeVersion === 'string' && value.nodeVersion.length > 0, 'Bundle Node identity is missing');
    invariant(typeof value.zlibVersion === 'string' && value.zlibVersion.length > 0, 'Bundle zlib identity is missing');
    invariant(Array.isArray(value.files) && value.files.length > 0, 'Bundle file measurements are missing');
    invariant(value.files.every((file) => typeof file.path === 'string'
      && Number.isInteger(file.minifiedBytes) && file.minifiedBytes >= 0
      && Number.isInteger(file.gzipBytes) && file.gzipBytes >= 0), 'Bundle file measurement is invalid');
    invariant(new Set(value.files.map((file) => file.path)).size === value.files.length, 'Bundle file is duplicated');
    invariant(value.minifiedBytes === value.files.reduce((total, file) => total + file.minifiedBytes, 0), 'Bundle raw total mismatch');
    invariant(value.gzipBytes === value.files.reduce((total, file) => total + file.gzipBytes, 0), 'Bundle gzip total mismatch');
    invariant(Number.isInteger(value.gzipBytes) && value.gzipBytes >= 0, 'Bundle gzip is invalid');
    invariant(value.limitBytes === contract.bundle.initialGzipLimitBytes, 'Bundle limit changed');
    invariant(value.gzipBytes <= value.limitBytes, 'Initial JavaScript gzip budget exceeded');
    invariant(value.largeChunkDebt.status === contract.bundle.debtStatus, 'Vite debt status changed');
    invariant(value.largeChunkDebt.thresholdBytes === contract.bundle.largeChunkThresholdBytes, 'Vite threshold changed');
    invariant(value.largeChunkDebt.message === contract.bundle.warning, 'Vite warning text changed');
    invariant(value.largeChunkDebt.messageSha256 === sha256(contract.bundle.warning), 'Vite warning hash changed');
    invariant(value.largeChunkDebt.occurrenceCount === contract.bundle.expectedOccurrences, 'Vite warning occurrence changed');
    invariant(value.largeChunkDebt.affectedFiles.length > 0, 'Accepted Vite large-chunk debt disappeared without an approved task');
  }

  export function measureBundle(
    distDirectory,
    limitBytes = contract.bundle.initialGzipLimitBytes,
    buildLogPath = null,
    artifactDirectory = JOB_ARTIFACTS(),
  ) {
    invariant(limitBytes === contract.bundle.initialGzipLimitBytes, 'Bundle limit cannot be changed');
    const pinnedNode = readFileSync(join(ROOT, '.node-version'), 'utf8').trim().replace(/^v/, '');
    invariant(process.versions.node === pinnedNode, 'Node runtime differs from .node-version');
    const html = readFileSync(join(distDirectory, 'index.html'), 'utf8');
    const urls = [];
    for (const tag of html.match(/<(?:script|link)\b[^>]*>/gi) || []) {
      const type = tag.match(/\btype=["']([^"']+)["']/i)?.[1];
      const rel = tag.match(/\brel=["']([^"']+)["']/i)?.[1];
      const src = tag.match(/\bsrc=["']([^"']+)["']/i)?.[1];
      const href = tag.match(/\bhref=["']([^"']+)["']/i)?.[1];
      const value = type === 'module' && src ? src : rel === 'modulepreload' && href ? href : null;
      if (value && value.endsWith('.js') && !urls.includes(value)) urls.push(value);
    }
    invariant(urls.length > 0, 'No initial JavaScript assets found');
    const files = urls.map((url) => {
      const asset = safeAssetPath(distDirectory, url);
      const bytes = readFileSync(asset.absolute);
      return {
        path: asset.path,
        minifiedBytes: bytes.length,
        gzipBytes: gzipSync(bytes, { level: 9 }).length,
      };
    });
    const root = realpathSync(distDirectory);
    const affectedFiles = javascriptFiles(root)
      .filter((path) => readFileSync(path).length > contract.bundle.largeChunkThresholdBytes)
      .map((path) => relative(root, path))
      .sort();
    const logOccurrences = buildLogPath
      ? warningOccurrences(readFileSync(buildLogPath, 'utf8'), contract.bundle.warning)
      : (affectedFiles.length > 0 ? 1 : 0);
    invariant(logOccurrences === contract.bundle.expectedOccurrences, 'Vite warning occurrence count changed');
    const result = {
      schemaVersion: 1,
      files,
      minifiedBytes: files.reduce((total, file) => total + file.minifiedBytes, 0),
      gzipBytes: files.reduce((total, file) => total + file.gzipBytes, 0),
      limitBytes,
      nodeVersion: process.versions.node,
      zlibVersion: process.versions.zlib,
      largeChunkDebt: {
        status: contract.bundle.debtStatus,
        thresholdBytes: contract.bundle.largeChunkThresholdBytes,
        affectedFiles,
        message: contract.bundle.warning,
        messageSha256: sha256(contract.bundle.warning),
        occurrenceCount: logOccurrences,
      },
    };
    assertBundle(result);
    writeJson(join(artifactDirectory, 'bundle.json'), result);
    return result;
  }

  function xmlAttribute(attributes, name) {
    const expression = new RegExp("\\b" + name + "=[\"'](\\d+)[\"']");
    const value = attributes.match(expression)?.[1];
    invariant(value !== undefined, 'JUnit attribute missing: ' + name);
    return Number(value);
  }

  function junitCounts(path) {
    const xml = readFileSync(path, 'utf8');
    const root = xml.match(/<(?:testsuites|testsuite)\b([^>]*)>/);
    invariant(root, 'JUnit root is missing');
    const failures = xmlAttribute(root[1], 'failures')
      + (/\berrors=/.test(root[1]) ? xmlAttribute(root[1], 'errors') : 0);
    return {
      testCount: xmlAttribute(root[1], 'tests'),
      failureCount: failures,
      unexplainedSkipCount: /\bskipped=/.test(root[1]) ? xmlAttribute(root[1], 'skipped') : 0,
    };
  }

  function tapCount(path) {
    const lines = readFileSync(path, 'utf8').trimEnd().split('\n');
    const plans = lines.map((line) => line.match(/^1\.\.(\d+)(?:\s+#.*)?$/)).filter(Boolean);
    invariant(plans.length === 1, 'TAP requires one unique top-level plan: ' + path);
    const count = Number(plans[0][1]);
    invariant(lines.filter((line) => /^ok \d+\b/.test(line)).length === count, 'TAP pass count mismatch');
    invariant(lines.every((line) => !/^not ok \d+\b/.test(line)), 'TAP failure present');
    return count;
  }

  function measuredCounts(job, directory) {
    if (job === 'composer-validation') return {
      testCount: tapCount(join(directory, 'dispatcher.tap')) + tapCount(join(directory, 'p00-node.tap')),
      failureCount: 0,
      unexplainedSkipCount: 0,
    };
    if (job === 'sqlite') return junitCounts(join(directory, 'sqlite.junit.xml'));
    if (job === 'postgresql-16') return junitCounts(join(directory, 'postgresql-16.junit.xml'));
    if (job === 'frontend') {
      const report = readJson(join(directory, 'vitest.json'));
      return {
        testCount: report.numTotalTests,
        failureCount: report.numFailedTests,
        unexplainedSkipCount: report.numPendingTests,
      };
    }
    if (job === 'playwright') {
      const report = readJson(join(directory, 'playwright.json'));
      invariant(report.stats && Number.isInteger(report.stats.expected), 'Playwright stats are missing');
      return {
        testCount: report.stats.expected + report.stats.unexpected + report.stats.flaky + report.stats.skipped,
        failureCount: report.stats.unexpected + report.stats.flaky,
        unexplainedSkipCount: report.stats.skipped,
      };
    }
    return { testCount: 0, failureCount: 0, unexplainedSkipCount: 0 };
  }

  const artifactNames = {
    'composer-validation': ['dispatcherTap', 'p00NodeTap'],
    'php-style-static': [],
    sqlite: ['junit'],
    'postgresql-16': ['junit', 'postgresqlIdentity'],
    frontend: ['vitest', 'bundle', 'viteBuildLog'],
    playwright: ['playwrightJson'],
  };

  function artifactHashes(job, directory) {
    const paths = {
      'composer-validation': {
        dispatcherTap: join(directory, 'dispatcher.tap'),
        p00NodeTap: join(directory, 'p00-node.tap'),
      },
      sqlite: { junit: join(directory, 'sqlite.junit.xml') },
      'postgresql-16': {
        junit: join(directory, 'postgresql-16.junit.xml'),
        postgresqlIdentity: join(directory, 'postgresql-identity.json'),
      },
      frontend: {
        vitest: join(directory, 'vitest.json'),
        bundle: join(directory, 'bundle.json'),
        viteBuildLog: join(directory, 'vite-build.log'),
      },
      playwright: { playwrightJson: join(directory, 'playwright.json') },
    };
    return Object.fromEntries(
      Object.entries(paths[job] || {}).map(([name, path]) => [name, {
        path: relative(directory, path), sha256: sha256(readFileSync(path)),
      }]),
    );
  }

  function assertRecord(record, expectedJob, directory = null) {
    rejectSecrets(record, 'job[' + expectedJob + ']');
    assertExactKeys(record, [
      'schemaVersion', 'job', 'command', 'integratedSha', 'status', 'exitCode',
      'retryAttempt', 'testCount', 'failureCount', 'unexplainedSkipCount', 'durationMs',
      'contractSha256', 'inputFingerprintSha256', 'inputs',
      'platformObservationFingerprintSha256', 'platformObservation', 'logSha256', 'artifacts',
    ], 'job[' + expectedJob + ']');
    const declared = contract.jobs.find((job) => job.name === expectedJob);
    invariant(declared, 'Unknown P00 job: ' + expectedJob);
    invariant(record.schemaVersion === 1 && record.job === expectedJob, 'Job record identity mismatch');
    invariant(record.command === declared.command, 'Job command provenance mismatch');
    invariant(SHA40.test(record.integratedSha), 'Job SHA is invalid');
    invariant(record.status === 'passed' && record.exitCode === 0, 'Job did not pass: ' + expectedJob);
    invariant(record.retryAttempt === 1, 'Job retry is prohibited: ' + expectedJob);
    invariant(record.testCount === declared.testCount, 'Job count mismatch: ' + expectedJob);
    invariant(record.failureCount === 0 && record.unexplainedSkipCount === 0, 'Job has failures/skips: ' + expectedJob);
    invariant(Number.isInteger(record.durationMs) && record.durationMs >= 0, 'Job duration is invalid');
    invariant(record.contractSha256 === contractSha256, 'Job contract hash mismatch');
    invariant(SHA64.test(record.logSha256), 'Job log hash is invalid');
    invariant(Array.isArray(artifactNames[expectedJob]), 'Job artifact contract is missing');
    invariant(stableJson(Object.keys(record.artifacts).sort())
      === stableJson([...artifactNames[expectedJob]].sort()), 'Job artifact names mismatch');
    invariant(Object.values(record.artifacts).every((reference) =>
      reference && /^[A-Za-z0-9][A-Za-z0-9._-]*$/.test(reference.path)
      && SHA64.test(reference.sha256)), 'Job artifact reference is invalid');
    if (directory !== null) {
      const measured = measuredCounts(expectedJob, directory);
      invariant(measured.testCount === record.testCount
        && measured.failureCount === record.failureCount
        && measured.unexplainedSkipCount === record.unexplainedSkipCount,
      'Reporter-derived counts differ from job record');
      invariant(sha256(readFileSync(join(directory, 'job.log'))) === record.logSha256, 'Job log bytes changed');
      for (const reference of Object.values(record.artifacts)) {
        const path = safeSibling(directory, reference.path);
        const stat = lstatSync(path);
        invariant(stat.isFile() && !stat.isSymbolicLink() && realpathSync(path) === path, 'Raw artifact is unsafe');
        invariant(sha256(readFileSync(path)) === reference.sha256, 'Raw artifact bytes changed');
      }
    }
    invariant(record.inputFingerprintSha256 === sha256(stableJson(record.inputs)), 'Job input fingerprint mismatch');
    validateSchema(inputsSchema, record.inputs, 'job[' + expectedJob + '].inputs');
    invariant(record.inputs.runnerClasses.local !== record.inputs.runnerClasses.ci,
      'Job runner classes must be distinct');
    invariant(record.platformObservationFingerprintSha256
      === sha256(stableJson(record.platformObservation)), 'Job platform fingerprint mismatch');
    validateSchema(platformObservationSchema, record.platformObservation, 'job[' + expectedJob + '].platformObservation');
    invariant(record.platformObservation.runnerClass
      === record.inputs.runnerClasses[record.platformObservation.runnerRole],
    'Job runner role/class binding mismatch: ' + expectedJob);
  }

  function writeGateResult(job, exitCode, durationMs, logPath) {
    const declared = contract.jobs.find((item) => item.name === job);
    invariant(declared, 'Unknown P00 job: ' + job);
    const { portableInputs: inputs, platformObservation } = collectInputs();
    const counts = measuredCounts(job, JOB_ARTIFACTS());
    if (job === 'frontend') assertBundle(readJson(join(JOB_ARTIFACTS(), 'bundle.json')));
    const record = {
      schemaVersion: 1,
      job,
      command: declared.command,
      integratedSha: command('git', ['rev-parse', 'HEAD']),
      status: exitCode === 0 && counts.testCount === declared.testCount
        && counts.failureCount === 0 && counts.unexplainedSkipCount === 0 ? 'passed' : 'failed',
      exitCode,
      retryAttempt: Number(process.env.P00_ATTEMPT || '1'),
      testCount: counts.testCount,
      failureCount: counts.failureCount,
      unexplainedSkipCount: counts.unexplainedSkipCount,
      durationMs,
      contractSha256,
      inputFingerprintSha256: sha256(stableJson(inputs)),
      inputs,
      platformObservationFingerprintSha256: sha256(stableJson(platformObservation)),
      platformObservation,
      logSha256: sha256(readFileSync(logPath)),
      artifacts: artifactHashes(job, JOB_ARTIFACTS()),
    };
    writeJson(join(JOB_ARTIFACTS(), 'result.json'), record);
    if (record.status !== 'passed') throw new Error('P00 job result failed validation: ' + job);
    return record;
  }

  export function aggregateRequiredGates(resultsDirectory) {
    const rootEntries = readdirSync(resultsDirectory).sort();
    const jobNames = contract.jobs.map((job) => job.name).sort();
    invariant(stableJson(rootEntries) === stableJson(jobNames), 'Aggregate root must contain exactly six job directories');
    for (const name of rootEntries) {
      const path = join(resultsDirectory, name);
      const stat = lstatSync(path);
      invariant(stat.isDirectory() && !stat.isSymbolicLink() && realpathSync(path) === path,
        'Aggregate job directory is unsafe: ' + name);
    }
    const records = contract.jobs.map((job) => {
      const record = readJson(join(resultsDirectory, job.name, 'result.json'));
      const directory = join(resultsDirectory, job.name);
      const entries = readdirSync(directory).sort();
      const expected = ['job.log', 'result.json', ...Object.values(record.artifacts).map((value) => value.path)].sort();
      invariant(stableJson(entries) === stableJson(expected), 'Job directory has missing or extra files: ' + job.name);
      assertRecord(record, job.name, directory);
      return record;
    });
    const shas = new Set(records.map((record) => record.integratedSha));
    const fingerprints = new Set(records.map((record) => record.inputFingerprintSha256));
    const completeInputs = new Set(records.map((record) => stableJson(record.inputs)));
    invariant(shas.size === 1, 'P00 jobs used different SHAs');
    invariant(fingerprints.size === 1, 'P00 jobs used different declared inputs');
    invariant(completeInputs.size === 1, 'P00 jobs used different complete portable inputs');
    return {
      schemaVersion: 1,
      status: 'passed',
      integratedSha: records[0].integratedSha,
      retryAttempt: 1,
      contractSha256,
      inputFingerprintSha256: records[0].inputFingerprintSha256,
      inputs: records[0].inputs,
      platformObservationFingerprintSha256: records[0].platformObservationFingerprintSha256,
      platformObservation: records[0].platformObservation,
      testCount: records.reduce((total, record) => total + record.testCount, 0),
      failureCount: 0,
      unexplainedSkipCount: 0,
      jobs: records,
    };
  }

  function postgresqlObservation(serverVersionNum, databaseName, instanceNonceSha256, endpointSha256) {
    const expected = expectedPostgresqlIdentity();
    const numericVersion = Number(serverVersionNum);
    invariant(Number.isInteger(numericVersion) && numericVersion >= 160000 && numericVersion < 170000, 'PostgreSQL is not major 16');
    invariant(/_test$/.test(databaseName), 'PostgreSQL database is not a test database');
    invariant(instanceNonceSha256 === expected.instanceNonceSha256, 'Live PostgreSQL nonce mismatch');
    invariant(SHA64.test(endpointSha256), 'PostgreSQL endpoint observation is invalid');
    return { ...expected, endpointSha256, serverVersionNum: numericVersion, databaseName };
  }

  function assertPostgresqlObservation(value, expectedInputs) {
    rejectSecrets(value, '$.postgresqlObservation');
    assertExactKeys(value, [
      'kind', 'identity', 'attestationSha256', 'instanceNonceSha256',
      'endpointSha256', 'serverVersionNum', 'databaseName',
    ], '$.postgresqlObservation');
    invariant(value.kind === expectedInputs.postgresql.kind, 'PostgreSQL identity kind mismatch');
    invariant(value.identity === expectedInputs.postgresql.identity, 'PostgreSQL immutable identity mismatch');
    invariant(expectedInputs.postgresql.policy === 'postgresql-16-test-closed-transport-v1', 'PostgreSQL portable policy mismatch');
    invariant(SHA64.test(value.attestationSha256), 'PostgreSQL attestation hash is invalid');
    invariant(SHA64.test(value.instanceNonceSha256), 'PostgreSQL nonce is invalid');
    invariant(SHA64.test(value.endpointSha256), 'PostgreSQL endpoint observation is invalid');
    invariant(Number.isInteger(value.serverVersionNum)
      && value.serverVersionNum >= 160000 && value.serverVersionNum < 170000, 'PostgreSQL major is not 16');
    invariant(typeof value.databaseName === 'string' && /_test$/.test(value.databaseName), 'PostgreSQL test database is invalid');
  }

  export function assertAggregate(value, expectedRunnerRole = 'local') {
    rejectSecrets(value, '$.aggregate');
    const aggregateKeys = [
      'schemaVersion', 'status', 'integratedSha', 'retryAttempt', 'contractSha256',
      'inputFingerprintSha256', 'inputs', 'platformObservationFingerprintSha256',
      'platformObservation', 'testCount', 'failureCount', 'unexplainedSkipCount', 'jobs',
    ];
    assertExactKeys(value, Object.hasOwn(value, 'postgresqlObservation')
      ? [...aggregateKeys, 'postgresqlObservation'] : aggregateKeys, '$.aggregate');
    invariant(value.schemaVersion === 1 && value.status === 'passed', 'Aggregate did not pass');
    invariant(value.retryAttempt === 1, 'Aggregate retry is prohibited');
    invariant(value.contractSha256 === contractSha256, 'Aggregate contract mismatch');
    invariant(Array.isArray(value.jobs) && value.jobs.length === contract.jobs.length, 'Aggregate job count mismatch');
    value.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
    invariant(value.testCount === value.jobs.reduce((total, record) => total + record.testCount, 0),
      'Aggregate test count mismatch');
    invariant(value.failureCount === 0 && value.unexplainedSkipCount === 0,
      'Aggregate has failures or unexplained skips');
    invariant(value.jobs.every((record) => record.integratedSha === value.integratedSha), 'Aggregate SHA mismatch');
    invariant(value.jobs.every((record) => record.inputFingerprintSha256 === value.inputFingerprintSha256), 'Aggregate input mismatch');
    invariant(value.inputFingerprintSha256 === sha256(stableJson(value.inputs)), 'Aggregate input fingerprint is not self-bound');
    invariant(value.jobs.every((record) => stableJson(record.inputs) === stableJson(value.inputs)), 'Aggregate complete inputs mismatch');
    validateSchema(inputsSchema, value.inputs, '$.aggregate.inputs');
    invariant(value.inputs.runnerClasses.local !== value.inputs.runnerClasses.ci,
      'Aggregate runner classes must be distinct');
    invariant(value.platformObservationFingerprintSha256 === sha256(stableJson(value.platformObservation)),
      'Aggregate platform fingerprint is not self-bound');
    validateSchema(platformObservationSchema, value.platformObservation, '$.aggregate.platformObservation');
    invariant(value.platformObservation.runnerRole === expectedRunnerRole
      && value.platformObservation.runnerClass === value.inputs.runnerClasses[expectedRunnerRole],
    'Aggregate runner role/class mismatch');
    if (Object.hasOwn(value, 'postgresqlObservation')) {
      assertPostgresqlObservation(value.postgresqlObservation, value.inputs);
      const postgresqlJob = value.jobs.find((record) => record.job === 'postgresql-16');
      invariant(postgresqlJob?.artifacts?.postgresqlIdentity?.sha256
        === sha256(stableJson(value.postgresqlObservation)),
      'Aggregate PostgreSQL observation is not bound to its canonical job artifact');
    }
    invariant(value.jobs.every((record) => record.platformObservation.runnerRole === expectedRunnerRole
      && record.platformObservation.runnerClass === value.inputs.runnerClasses[expectedRunnerRole]),
    'Aggregate contains a job from the wrong runner role/class');
  }

  const fileReferenceSchema = {
    type: 'object',
    additionalProperties: false,
    required: ['path', 'sha256'],
    properties: {
      path: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]*$' },
      sha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
    },
  };

  const platformObservationSchema = {
    type: 'object', additionalProperties: false,
    required: ['os', 'arch', 'osRelease', 'runnerRole', 'runnerClass', 'zlib', 'chromiumExecutableSha256'],
    properties: {
      os: { type: 'string', minLength: 1 }, arch: { type: 'string', minLength: 1 },
      osRelease: { type: 'string', minLength: 1 }, runnerRole: { enum: ['local', 'ci'] },
      runnerClass: { type: 'string', minLength: 1 },
      zlib: { type: 'string', minLength: 1 },
      chromiumExecutableSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
    },
  };

  const inputsSchema = {
    type: 'object',
    additionalProperties: false,
    required: ['contractSha256', 'runtime', 'lockfileSha256', 'postgresql', 'playwright', 'bundleAlgorithms', 'runnerClasses'],
    properties: {
      contractSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
      runtime: {
        type: 'object',
        additionalProperties: false,
        required: ['php', 'composer', 'node', 'npm'],
        properties: Object.fromEntries(
          ['php', 'composer', 'node', 'npm'].map((name) => [name, { type: 'string', minLength: 1 }]),
        ),
      },
      lockfileSha256: {
        type: 'object',
        additionalProperties: false,
        required: ['composer', 'npm'],
        properties: {
          composer: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          npm: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        },
      },
      postgresql: {
        type: 'object',
        additionalProperties: false,
        required: ['kind', 'identity', 'policy'],
        properties: {
          kind: { enum: ['oci', 'external-attestation'] },
          identity: { type: 'string', minLength: 64 },
          policy: { const: 'postgresql-16-test-closed-transport-v1' },
        },
      },
      playwright: {
        type: 'object',
        additionalProperties: false,
        required: ['packageVersion', 'chromiumRevision'],
        properties: {
          packageVersion: { type: 'string', minLength: 1 },
          chromiumRevision: { type: 'string', pattern: '^\\d+$' },
        },
      },
      bundleAlgorithms: {
        type: 'object', additionalProperties: false,
        required: ['assetSelection', 'gzip'],
        properties: {
          assetSelection: { const: 'html-module-entry-and-modulepreload-v1' },
          gzip: { const: 'node-zlib-level-9' },
        },
      },
      runnerClasses: {
        type: 'object', additionalProperties: false,
        required: ['local', 'ci'],
        properties: {
          local: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$' },
          ci: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$' },
        },
      },
    },
  };

  export const evidenceSchema = {
    '$schema': 'https://json-schema.org/draft/2020-12/schema',
    '$id': 'dorzak-p00-evidence-manifest-v1',
    type: 'object',
    additionalProperties: false,
    required: [
      'schemaVersion', 'BASE_SHA', 'CODE_SHA', 'INTEGRATED_SHA', 'contractSha256',
      'inputs', 'postgresqlObservation', 'counts', 'local', 'bundle', 'review',
      'ciRuns', 'files',
    ],
    properties: {
      schemaVersion: { const: 1 },
      BASE_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
      CODE_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
      INTEGRATED_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
      contractSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
      inputs: inputsSchema,
      postgresqlObservation: {
        type: 'object',
        additionalProperties: false,
        required: ['kind', 'identity', 'attestationSha256', 'instanceNonceSha256', 'endpointSha256', 'serverVersionNum', 'databaseName'],
        properties: {
          kind: { enum: ['oci', 'external-attestation'] },
          identity: { type: 'string', minLength: 64 },
          attestationSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          instanceNonceSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          endpointSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          serverVersionNum: { type: 'integer', minimum: 160000, maximum: 169999 },
          databaseName: { type: 'string', pattern: '_test$' },
        },
      },
      counts: {
        type: 'object',
        additionalProperties: false,
        required: ['source', 'jobs'],
        properties: {
          source: { const: 'scripts/quality/p00-contract.json' },
          jobs: {
            type: 'object', additionalProperties: false,
            required: ['composer-validation', 'php-style-static', 'sqlite', 'postgresql-16', 'frontend', 'playwright'],
            properties: Object.fromEntries(contract.jobs.map((job) => [
              job.name, { type: 'integer', minimum: 0, maximum: job.testCount },
            ])),
          },
        },
      },
      local: {
        type: 'object',
        additionalProperties: false,
        required: ['path', 'sha256', 'status', 'integratedSha', 'inputFingerprintSha256', 'platformObservationFingerprintSha256', 'platformObservation'],
        properties: {
          ...fileReferenceSchema.properties,
          status: { const: 'passed' },
          integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
          inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          platformObservationFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          platformObservation: platformObservationSchema,
        },
      },
      bundle: {
        type: 'object',
        additionalProperties: false,
        required: ['path', 'sha256', 'gzipBytes', 'limitBytes', 'debtStatus', 'warningSha256'],
        properties: {
          ...fileReferenceSchema.properties,
          gzipBytes: { type: 'integer', minimum: 0, maximum: 216700 },
          limitBytes: { const: 216700 },
          debtStatus: { const: 'accepted-open' },
          warningSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        },
      },
      review: {
        type: 'object',
        additionalProperties: false,
        required: ['path', 'sha256', 'baseSha', 'codeSha', 'critical', 'important', 'minor'],
        properties: {
          ...fileReferenceSchema.properties,
          baseSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
          codeSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
          critical: { const: 0 },
          important: { const: 0 },
          minor: { type: 'array', items: { type: 'string' } },
        },
      },
      ciRuns: {
        type: 'array',
        minItems: 2,
        maxItems: 2,
        items: {
          type: 'object',
          additionalProperties: false,
          required: ['path', 'sha256', 'provider', 'runId', 'attempt', 'integratedSha', 'inputFingerprintSha256', 'platformObservationFingerprintSha256', 'platformObservation'],
          properties: {
            ...fileReferenceSchema.properties,
            provider: { type: 'string', minLength: 1 },
            runId: { type: 'string', minLength: 1 },
            attempt: { const: 1 },
            integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
            inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
            platformObservationFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
            platformObservation: platformObservationSchema,
          },
        },
      },
      files: {
        type: 'array',
        minItems: 7,
        maxItems: 7,
        uniqueItems: true,
        items: fileReferenceSchema,
      },
    },
  };

  export function assertExactKeys(value, keys, path) {
    invariant(value !== null && typeof value === 'object' && !Array.isArray(value), path + ' must be an object');
    invariant(stableJson(Object.keys(value).sort()) === stableJson([...keys].sort()), path + ' keys are not closed');
  }

  export function validateSchema(schema, value, path = '$') {
    if ('const' in schema) invariant(Object.is(value, schema.const), path + ' const mismatch');
    if (schema.enum) invariant(schema.enum.includes(value), path + ' enum mismatch');
    if (schema.type === 'object') {
      invariant(value !== null && typeof value === 'object' && !Array.isArray(value), path + ' type mismatch');
      for (const key of schema.required || []) invariant(Object.hasOwn(value, key), path + '.' + key + ' is required');
      if (schema.additionalProperties === false) {
        invariant(Object.keys(value).every((key) => Object.hasOwn(schema.properties || {}, key)), path + ' has extra keys');
      }
      for (const [key, item] of Object.entries(value)) {
        if (schema.properties?.[key]) validateSchema(schema.properties[key], item, path + '.' + key);
        else if (schema.additionalProperties && typeof schema.additionalProperties === 'object') {
          validateSchema(schema.additionalProperties, item, path + '.' + key);
        }
      }
    } else if (schema.type === 'array') {
      invariant(Array.isArray(value), path + ' type mismatch');
      if (schema.minItems !== undefined) invariant(value.length >= schema.minItems, path + ' too short');
      if (schema.maxItems !== undefined) invariant(value.length <= schema.maxItems, path + ' too long');
      if (schema.uniqueItems) invariant(new Set(value.map(stableJson)).size === value.length, path + ' duplicates');
      value.forEach((item, index) => validateSchema(schema.items, item, path + '[' + index + ']'));
    } else if (schema.type === 'string') {
      invariant(typeof value === 'string', path + ' type mismatch');
      if (schema.minLength !== undefined) invariant(value.length >= schema.minLength, path + ' too short');
      if (schema.pattern) invariant(new RegExp(schema.pattern).test(value), path + ' pattern mismatch');
    } else if (schema.type === 'integer') {
      invariant(Number.isInteger(value), path + ' type mismatch');
      if (schema.minimum !== undefined) invariant(value >= schema.minimum, path + ' below minimum');
      if (schema.maximum !== undefined) invariant(value <= schema.maximum, path + ' above maximum');
    }
  }

  function validateCiRun(run, aggregate) {
    rejectSecrets(run, '$.ciRun');
    assertExactKeys(run, [
      'schemaVersion', 'provider', 'runId', 'attempt', 'integratedSha', 'contractSha256',
      'inputFingerprintSha256', 'inputs', 'platformObservationFingerprintSha256',
      'platformObservation', 'postgresqlObservation', 'requiredGate', 'jobs',
    ], '$.ciRun');
    assertExactKeys(run.requiredGate, ['status', 'jobs'], '$.ciRun.requiredGate');
    invariant(run.schemaVersion === 1, 'CI schema mismatch');
    invariant(typeof run.provider === 'string' && run.provider.length > 0, 'CI provider is missing');
    invariant(typeof run.runId === 'string' && run.runId.length > 0, 'CI run ID is missing');
    invariant(run.attempt === 1, 'CI retry is prohibited');
    invariant(run.integratedSha === aggregate.integratedSha, 'CI SHA mismatch');
    invariant(run.contractSha256 === contractSha256, 'CI contract mismatch');
    invariant(run.inputFingerprintSha256 === aggregate.inputFingerprintSha256, 'CI input mismatch');
    invariant(run.inputFingerprintSha256 === sha256(stableJson(run.inputs)), 'CI input fingerprint is not self-bound');
    invariant(stableJson(run.inputs) === stableJson(aggregate.inputs), 'CI complete portable inputs differ from aggregate');
    validateSchema(inputsSchema, run.inputs, '$.ciRun.inputs');
    invariant(run.platformObservationFingerprintSha256
      === sha256(stableJson(run.platformObservation)), 'CI platform fingerprint mismatch');
    validateSchema(platformObservationSchema, run.platformObservation, '$.ciRun.platformObservation');
    invariant(run.platformObservation.runnerRole === 'ci'
      && run.platformObservation.runnerClass === run.inputs.runnerClasses.ci,
    'CI runner role/class mismatch');
    invariant(run.requiredGate?.status === 'passed' && run.requiredGate?.jobs === 6, 'CI required gate failed');
    assertPostgresqlObservation(run.postgresqlObservation, run.inputs);
    invariant(Array.isArray(run.jobs) && run.jobs.length === 6, 'CI jobs missing');
    run.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
    const postgresqlJob = run.jobs.find((record) => record.job === 'postgresql-16');
    invariant(postgresqlJob?.artifacts?.postgresqlIdentity?.sha256
      === sha256(stableJson(run.postgresqlObservation)),
    'CI PostgreSQL observation is not bound to its canonical job artifact');
    invariant(run.jobs.every((record) => record.integratedSha === run.integratedSha
      && record.integratedSha === aggregate.integratedSha
      && record.inputFingerprintSha256 === run.inputFingerprintSha256
      && stableJson(record.inputs) === stableJson(run.inputs)
      && record.platformObservation.runnerRole === 'ci'
      && record.platformObservation.runnerClass === run.inputs.runnerClasses.ci),
    'CI jobs do not cross-bind SHA, complete portable inputs, and the approved CI runner class');
  }

  function safeSibling(directory, path) {
    invariant(typeof path === 'string' && /^[A-Za-z0-9][A-Za-z0-9._-]*$/.test(path), 'Unsafe evidence path');
    const full = resolve(directory, path);
    invariant(dirname(full) === resolve(directory), 'Evidence path escapes output');
    return full;
  }

  function readSanitizedJson(path, label) {
    const value = readJson(path);
    rejectSecrets(value, label);
    return value;
  }

  function readSanitizedText(path, label) {
    const value = readFileSync(path, 'utf8');
    rejectSecrets(value, label);
    return value;
  }

  function normalizedReviewMarkdown(review) {
    return [
      '# Independent P00 Review',
      '',
      'BASE_SHA: ' + review.baseSha,
      'CODE_SHA: ' + review.codeSha,
      'Critical: ' + review.critical,
      'Important: ' + review.important,
      '',
      '## Minor',
      '',
      ...(review.minor.length > 0 ? review.minor.map((finding) => '- ' + finding) : ['- None.']),
      '',
    ].join('\n');
  }

  function reviewFromMarkdown(markdown) {
    const lines = markdown.split('\n');
    invariant(lines[0] === '# Independent P00 Review' && lines[1] === ''
      && lines[6] === '' && lines[7] === '## Minor' && lines[8] === ''
      && lines.at(-1) === '', 'Independent review Markdown shape changed');
    const field = (index, prefix) => {
      invariant(lines[index].startsWith(prefix), 'Independent review field is missing: ' + prefix);
      return lines[index].slice(prefix.length);
    };
    const minorLines = lines.slice(9, -1);
    invariant(minorLines.length > 0 && minorLines.every((line) => line.startsWith('- ')),
      'Independent review minor section is invalid');
    const minor = minorLines.length === 1 && minorLines[0] === '- None.'
      ? [] : minorLines.map((line) => line.slice(2));
    const review = {
      baseSha: field(2, 'BASE_SHA: '),
      codeSha: field(3, 'CODE_SHA: '),
      critical: Number(field(4, 'Critical: ')),
      important: Number(field(5, 'Important: ')),
      minor,
    };
    invariant(markdown === normalizedReviewMarkdown(review), 'Independent review Markdown is not canonical');
    return review;
  }

  export function buildEvidence({
    outputDirectory,
    baseSha,
    codeSha,
    integratedSha,
    localDirectory,
    ciRunPaths,
    reviewJsonPath,
    reviewMarkdownPath,
    faultPoint = null,
  }) {
    invariant(SHA40.test(baseSha) && SHA40.test(codeSha) && SHA40.test(integratedSha), 'Evidence SHA is invalid');
    invariant(codeSha === integratedSha, 'P00 evidence requires CODE_SHA equal INTEGRATED_SHA');
    invariant(Array.isArray(ciRunPaths) && ciRunPaths.length === 2, 'Exactly two CI runs are required');
    invariant([null, 'after-temp', 'after-rename'].includes(faultPoint), 'Unknown evidence fault point');
    const localAggregate = aggregateRequiredGates(localDirectory);
    assertAggregate(localAggregate);
    invariant(localAggregate.integratedSha === integratedSha, 'Local matrix SHA mismatch');
    const observation = readSanitizedJson(
      join(localDirectory, 'postgresql-16', 'postgresql-identity.json'), '$.local.postgresqlObservation',
    );
    assertPostgresqlObservation(observation, localAggregate.inputs);
    const localPostgresqlReference = localAggregate.jobs
      .find((record) => record.job === 'postgresql-16')?.artifacts?.postgresqlIdentity;
    invariant(localPostgresqlReference?.sha256 === sha256(stableJson(observation)),
      'Local PostgreSQL observation is not bound to its canonical job artifact');
    const bundle = readSanitizedJson(join(localDirectory, 'frontend', 'bundle.json'), '$.local.bundle');
    assertBundle(bundle);
    invariant(bundle.nodeVersion === localAggregate.inputs.runtime.node
      && bundle.zlibVersion === localAggregate.platformObservation.zlib, 'Bundle runtime identity mismatch');
    const review = readSanitizedJson(reviewJsonPath, '$.reviewJson');
    assertExactKeys(review, ['schemaVersion', 'baseSha', 'codeSha', 'critical', 'important', 'minor'], '$.reviewJson');
    invariant(review.schemaVersion === 1 && review.baseSha === baseSha && review.codeSha === codeSha, 'Review range mismatch');
    invariant(review.critical === 0 && review.important === 0 && Array.isArray(review.minor), 'Review is not acceptable');
    invariant(review.minor.every((finding) => typeof finding === 'string' && finding.length > 0), 'Review minor finding is invalid');
    const reviewMarkdown = readSanitizedText(reviewMarkdownPath, '$.reviewMarkdown');
    invariant(reviewMarkdown === normalizedReviewMarkdown(review), 'Review Markdown does not normalize the JSON verdict');
    const runs = ciRunPaths.map((path, index) => readSanitizedJson(path, '$.ciRun[' + index + ']'));
    runs.forEach((run) => validateCiRun(run, localAggregate));
    invariant(runs[0].runId !== runs[1].runId, 'CI run IDs must be distinct');

    const canonicalDirectory = resolve(outputDirectory);
    invariant(!existsSync(canonicalDirectory), 'Evidence output directory already exists');
    mkdirSync(dirname(canonicalDirectory), { recursive: true });
    const publicationDirectory = mkdtempSync(
      join(dirname(canonicalDirectory), '.' + basename(canonicalDirectory) + '.tmp-'),
    );
    let published = false;
    try {
    const readme = [
      '# P00 Verification Evidence',
      '',
      'This directory contains deterministic, sanitized evidence for one exact P00 range.',
      'BASE_SHA is the approved integration base, CODE_SHA is the independently reviewed clean code identity,',
      'and INTEGRATED_SHA is the exact identity exercised locally and by both new attempt-one CI runs.',
      'The later evidence-only commit is deliberately absent from these payloads and is recorded by Control Room.',
      'Counts come only from scripts/quality/p00-contract.json. Raw logs remain ignored under .artifacts/p00.',
      'The Vite large-chunk warning is accepted-open measured debt, not a waived or hidden gate.',
      '',
    ].join('\n');
    const localPayload = { ...localAggregate, postgresqlObservation: observation };
    writeFileSync(join(publicationDirectory, 'README.md'), readme);
    writeJson(join(publicationDirectory, 'manifest.schema.json'), evidenceSchema);
    writeJson(join(publicationDirectory, 'local-full-matrix.json'), localPayload);
    writeJson(join(publicationDirectory, 'ci-run-1.json'), runs[0]);
    writeJson(join(publicationDirectory, 'ci-run-2.json'), runs[1]);
    writeJson(join(publicationDirectory, 'bundle.json'), bundle);
    copyFileSync(reviewMarkdownPath, join(publicationDirectory, 'independent-review.md'));

    const names = [
      'README.md', 'manifest.schema.json', 'local-full-matrix.json', 'ci-run-1.json',
      'ci-run-2.json', 'bundle.json', 'independent-review.md',
    ];
    const files = names.map((path) => ({
      path,
      sha256: sha256(readFileSync(join(publicationDirectory, path))),
    }));
    const file = (path) => files.find((item) => item.path === path);
    const manifest = {
      schemaVersion: 1,
      BASE_SHA: baseSha,
      CODE_SHA: codeSha,
      INTEGRATED_SHA: integratedSha,
      contractSha256,
      inputs: localAggregate.inputs,
      postgresqlObservation: observation,
      counts: {
        source: 'scripts/quality/p00-contract.json',
        jobs: Object.fromEntries(contract.jobs.map((job) => [job.name, job.testCount])),
      },
      local: {
        ...file('local-full-matrix.json'),
        status: 'passed',
        integratedSha,
        inputFingerprintSha256: localAggregate.inputFingerprintSha256,
        platformObservationFingerprintSha256: localAggregate.platformObservationFingerprintSha256,
        platformObservation: localAggregate.platformObservation,
      },
      bundle: {
        ...file('bundle.json'),
        gzipBytes: bundle.gzipBytes,
        limitBytes: bundle.limitBytes,
        debtStatus: bundle.largeChunkDebt.status,
        warningSha256: bundle.largeChunkDebt.messageSha256,
      },
      review: {
        ...file('independent-review.md'),
        baseSha,
        codeSha,
        critical: review.critical,
        important: review.important,
        minor: review.minor,
      },
      ciRuns: runs.map((run, index) => ({
        ...file('ci-run-' + (index + 1) + '.json'),
        provider: run.provider,
        runId: run.runId,
        attempt: run.attempt,
        integratedSha: run.integratedSha,
        inputFingerprintSha256: run.inputFingerprintSha256,
        platformObservationFingerprintSha256: run.platformObservationFingerprintSha256,
        platformObservation: run.platformObservation,
      })),
      files,
    };
    writeJson(join(publicationDirectory, 'manifest.json'), manifest);
    if (faultPoint === 'after-temp') throw new Error('P00_TEST_FAULT_AFTER_TEMP');
    origins.set(manifest, publicationDirectory);
    validateEvidence(join(publicationDirectory, 'manifest.json'));
    renameSync(publicationDirectory, canonicalDirectory);
    published = true;
    if (faultPoint === 'after-rename') throw new Error('P00_TEST_FAULT_AFTER_RENAME');
    origins.set(manifest, canonicalDirectory);
    validateEvidence(join(canonicalDirectory, 'manifest.json'));
    return manifest;
    } catch (error) {
      if (published && existsSync(canonicalDirectory)) {
        rmSync(canonicalDirectory, { recursive: true, force: true });
      }
      throw error;
    } finally {
      if (existsSync(publicationDirectory)) {
        rmSync(publicationDirectory, { recursive: true, force: true });
      }
    }
  }

  export function validateEvidence(input) {
    const manifestPath = typeof input === 'string' ? resolve(input) : null;
    if (manifestPath) {
      const stat = lstatSync(manifestPath);
      invariant(stat.isFile() && !stat.isSymbolicLink() && realpathSync(manifestPath) === manifestPath,
        'Evidence manifest is not one canonical regular file');
    }
    const manifest = manifestPath ? readSanitizedJson(manifestPath, '$.manifest') : input;
    const directory = manifestPath ? dirname(manifestPath) : origins.get(manifest);
    invariant(directory, 'Evidence directory is unknown');
    rejectSecrets(manifest);
    validateSchema(evidenceSchema, manifest);
    invariant(manifest.schemaVersion === 1, 'Evidence schema mismatch');
    invariant(SHA40.test(manifest.BASE_SHA)
      && SHA40.test(manifest.CODE_SHA) && SHA40.test(manifest.INTEGRATED_SHA), 'Evidence SHA invalid');
    invariant(manifest.CODE_SHA === manifest.INTEGRATED_SHA, 'Evidence code/integrated identity mismatch');
    invariant(manifest.contractSha256 === contractSha256
      && manifest.inputs.contractSha256 === contractSha256, 'Evidence contract mismatch');
    invariant(manifest.counts.source === 'scripts/quality/p00-contract.json', 'Count provenance mismatch');
    const expectedCounts = Object.fromEntries(contract.jobs.map((job) => [job.name, job.testCount]));
    invariant(stableJson(manifest.counts.jobs) === stableJson(expectedCounts), 'Evidence counts changed');
    invariant(Array.isArray(manifest.files) && manifest.files.length === 7, 'Evidence must reference seven siblings');
    const expectedNames = [
      'README.md', 'manifest.schema.json', 'local-full-matrix.json', 'ci-run-1.json',
      'ci-run-2.json', 'bundle.json', 'independent-review.md',
    ];
    const directoryEntries = readdirSync(directory).sort();
    invariant(stableJson(directoryEntries) === stableJson([...expectedNames, 'manifest.json'].sort()),
      'Evidence directory must contain exactly eight canonical paths');
    invariant(stableJson(manifest.files.map((item) => item.path)) === stableJson(expectedNames), 'Evidence file list mismatch');
    for (const reference of manifest.files) {
      invariant(SHA64.test(reference.sha256), 'Evidence file hash invalid');
      const path = safeSibling(directory, reference.path);
      const stat = lstatSync(path);
      invariant(stat.isFile() && !stat.isSymbolicLink() && realpathSync(path) === path,
        'Evidence sibling is not one canonical regular file: ' + reference.path);
      invariant(sha256(readFileSync(path)) === reference.sha256, 'Evidence file hash mismatch: ' + reference.path);
    }
    const referenceFor = (path) => manifest.files.find((item) => item.path === path);
    for (const [summary, path] of [
      [manifest.local, 'local-full-matrix.json'], [manifest.bundle, 'bundle.json'],
      [manifest.review, 'independent-review.md'], [manifest.ciRuns[0], 'ci-run-1.json'],
      [manifest.ciRuns[1], 'ci-run-2.json'],
    ]) invariant(summary.path === path && summary.sha256 === referenceFor(path).sha256,
      'Evidence summary/reference cross-binding mismatch: ' + path);
    rejectSecrets(readSanitizedText(join(directory, 'README.md'), '$.README.md'), '$.README.md');
    invariant(stableJson(readSanitizedJson(join(directory, 'manifest.schema.json'), '$.manifestSchema'))
      === stableJson(evidenceSchema), 'Evidence schema artifact changed');
    const local = readSanitizedJson(join(directory, 'local-full-matrix.json'), '$.local');
    assertAggregate(local, 'local');
    invariant(local.integratedSha === manifest.INTEGRATED_SHA, 'Local evidence SHA mismatch');
    invariant(local.inputFingerprintSha256 === manifest.local.inputFingerprintSha256, 'Local evidence input mismatch');
    invariant(stableJson(local.inputs) === stableJson(manifest.inputs), 'Local inputs mismatch');
    assertPostgresqlObservation(local.postgresqlObservation, manifest.inputs);
    invariant(stableJson(local.postgresqlObservation)
      === stableJson(manifest.postgresqlObservation), 'Local PostgreSQL observation mismatch');
    const bundle = readSanitizedJson(join(directory, 'bundle.json'), '$.bundle');
    assertBundle(bundle);
    invariant(bundle.nodeVersion === manifest.inputs.runtime.node
      && bundle.zlibVersion === local.platformObservation.zlib, 'Bundle evidence runtime mismatch');
    invariant(bundle.gzipBytes === manifest.bundle.gzipBytes, 'Bundle measurement mismatch');
    const runs = [
      readSanitizedJson(join(directory, 'ci-run-1.json'), '$.ciRun[0]'),
      readSanitizedJson(join(directory, 'ci-run-2.json'), '$.ciRun[1]'),
    ];
    runs.forEach((run) => validateCiRun(run, local));
    invariant(runs[0].runId !== runs[1].runId, 'CI run IDs are not distinct');
    invariant(runs.every((run) => run.integratedSha === manifest.INTEGRATED_SHA), 'CI integrated SHA mismatch');
    invariant(manifest.review.baseSha === manifest.BASE_SHA
      && manifest.review.codeSha === manifest.CODE_SHA, 'Review range mismatch');
    invariant(manifest.review.critical === 0 && manifest.review.important === 0, 'Review has blocking findings');
    const reviewMarkdown = readSanitizedText(join(directory, 'independent-review.md'), '$.independent-review.md');
    const parsedReview = reviewFromMarkdown(reviewMarkdown);
    const expectedLocalSummary = {
      ...referenceFor('local-full-matrix.json'),
      status: local.status,
      integratedSha: local.integratedSha,
      inputFingerprintSha256: local.inputFingerprintSha256,
      platformObservationFingerprintSha256: local.platformObservationFingerprintSha256,
      platformObservation: local.platformObservation,
    };
    const expectedBundleSummary = {
      ...referenceFor('bundle.json'),
      gzipBytes: bundle.gzipBytes,
      limitBytes: bundle.limitBytes,
      debtStatus: bundle.largeChunkDebt.status,
      warningSha256: bundle.largeChunkDebt.messageSha256,
    };
    const expectedReviewSummary = {
      ...referenceFor('independent-review.md'),
      ...parsedReview,
    };
    const expectedCiSummaries = runs.map((run, index) => ({
      ...referenceFor('ci-run-' + (index + 1) + '.json'),
      provider: run.provider,
      runId: run.runId,
      attempt: run.attempt,
      integratedSha: run.integratedSha,
      inputFingerprintSha256: run.inputFingerprintSha256,
      platformObservationFingerprintSha256: run.platformObservationFingerprintSha256,
      platformObservation: run.platformObservation,
    }));
    invariant(stableJson(manifest.local) === stableJson(expectedLocalSummary),
      'Local manifest summary differs from its sibling');
    invariant(stableJson(manifest.bundle) === stableJson(expectedBundleSummary),
      'Bundle manifest summary differs from its sibling');
    invariant(stableJson(manifest.review) === stableJson(expectedReviewSummary),
      'Review manifest summary differs from its sibling');
    invariant(stableJson(manifest.ciRuns) === stableJson(expectedCiSummaries),
      'CI manifest summaries differ from their siblings');
    return { files: manifest.files };
  }

  function print(value) {
    process.stdout.write(value + '\n');
  }

  async function main(argv) {
    const [action, ...args] = argv;
    if (action === 'bundle') {
      const value = measureBundle(resolve(args[0]), Number(args[1]), args[2] ? resolve(args[2]) : null);
      print('P00_BUNDLE PASS gzip=' + value.gzipBytes + ' limit=' + value.limitBytes
        + ' node=' + value.nodeVersion + ' zlib=' + value.zlibVersion);
      return;
    }
    if (action === 'write-result') {
      writeGateResult(args[0], Number(args[1]), Number(args[2]), resolve(args[3]));
      print('P00_JOB PASS job=' + args[0]);
      return;
    }
    if (action === 'postgres-identity') {
      const value = postgresqlObservation(args[0], args[1], args[2], args[3]);
      writeJson(join(JOB_ARTIFACTS(), 'postgresql-identity.json'), value);
      print('P00_POSTGRESQL PASS major=16 database=' + value.databaseName
        + ' identity_sha256=' + sha256(value.identity));
      return;
    }
    if (action === 'aggregate') {
      const directory = resolve(args[0]);
      const value = aggregateRequiredGates(directory);
      assertAggregate(value, requiredEnvironment('P00_RUNNER_ROLE'));
      writeJson(directory + '.required-gates.json', value);
      print('P00_REQUIRED_GATES PASS jobs=6 sha=' + value.integratedSha);
      return;
    }
    if (action === 'build-evidence') {
      const value = buildEvidence({
        outputDirectory: resolve(args[0]),
        baseSha: args[1],
        codeSha: args[2],
        integratedSha: args[3],
        localDirectory: resolve(args[4]),
        ciRunPaths: [resolve(args[5]), resolve(args[6])],
        reviewJsonPath: resolve(args[7]),
        reviewMarkdownPath: resolve(args[8]),
      });
      print('P00_EVIDENCE_BUILT files=' + value.files.length);
      return;
    }
    if (action === 'validate-evidence') {
      const value = validateEvidence(resolve(args[0]));
      const manifest = readJson(resolve(args[0]));
      print('P00_EVIDENCE PASS base=' + manifest.BASE_SHA + ' code=' + manifest.CODE_SHA
        + ' integrated=' + manifest.INTEGRATED_SHA + ' runs=2 files=' + value.files.length);
      return;
    }
    throw new Error('Usage: p00.mjs bundle|write-result|postgres-identity|aggregate|build-evidence|validate-evidence');
  }

  if (process.argv[1] && resolve(process.argv[1]) === MODULE) {
    main(process.argv.slice(2)).catch((error) => {
      process.stderr.write('P00_FAIL ' + error.message + '\n');
      process.exitCode = 1;
    });
  }
  ~~~

  This single module uses execFileSync without a shell for repository/runtime identity; parses both JUnit reports, Vitest JSON and Playwright JSON; writes canonical JSON with trailing newlines; and records hashes, never environment values or credential-bearing URLs. Test totals come only from p00-contract.json. The immutable PostgreSQL identity is owner-provided and attested; the mutable external-postgresql-16 sentinel is rejected.

- [ ] **Run focused red/green verification before the Task 13 commit.**

  ~~~bash
  sh scripts/quality/test-run-p00.sh
  node --test scripts/quality/p00.test.mjs
  set +e
  node scripts/quality/p00.mjs validate-evidence /definitely/absent/manifest.json
  absent_status="$?"
  set -e
  test "$absent_status" -ne 0
  git diff --check -- .gitignore scripts/quality
  ~~~

  Expected: dispatcher emits two passing TAP tests; all nine Node subtests pass, for eleven quality self-tests total; reporter diagnostics after each unique top-level TAP plan are accepted; absent evidence fails closed; diff check passes. The tests prove path/symlink/extra-file rejection, raw-byte recomputation, the fixed 216700-byte budget, exact accepted-open large-chunk warning, closed schemas and whole-payload secret scans, count provenance, complete SHA/input/platform cross-binding, attempt one, atomic publication, root bundle validation, sibling hashes, and two distinct CI runs whose portable PostgreSQL identity/policy matches while their attestation, nonce, endpoint and database observations differ.

- [ ] **Stage only the seven quality-interface paths and commit.**

  ~~~bash
  git add -- .gitignore \
    scripts/quality/p00-contract.json scripts/quality/p00.mjs scripts/quality/p00.test.mjs \
    scripts/quality/run-p00 scripts/quality/test-run-p00.sh scripts/quality/run-postgres-16
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 7
  git diff --cached --check
  git commit -m "build: add provider-neutral P00 quality gates"
  ~~~

  Run the global writer-boundary checks. If the focused commit changes during correction, discard all noncanonical raw Task 13 output and restart the next step on the new clean SHA. Raw .artifacts/ remains ignored and is never committed.

- [ ] **Produce canonical Task 13 evidence only after the focused commit.**

  ~~~bash
  test -z "$(git status --short --untracked-files=normal)"
  TASK13_SHA="$(git rev-parse HEAD)"
  TASK13_ARTIFACTS="$PWD/.artifacts/p00/task13-$TASK13_SHA"
  test ! -e "$TASK13_ARTIFACTS"
  mkdir -p "$TASK13_ARTIFACTS"
  export P00_RUNNER_ROLE=local
  export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"
  for job in composer-validation php-style-static sqlite postgresql-16 frontend playwright; do
    P00_ARTIFACT_DIR="$TASK13_ARTIFACTS" P00_ATTEMPT=1 scripts/quality/run-p00 "$job"
  done
  node scripts/quality/p00.mjs aggregate "$TASK13_ARTIFACTS"
  CONTRACT_SHA="$(shasum -a 256 scripts/quality/p00-contract.json | awk '{print $1}')"
  jq -e --arg sha "$TASK13_SHA" --arg contract "$CONTRACT_SHA" \
    '.status == "passed" and .integratedSha == $sha and .contractSha256 == $contract
     and .retryAttempt == 1 and .failureCount == 0 and .unexplainedSkipCount == 0
     and (.jobs | length) == 6
     and all(.jobs[]; .integratedSha == $sha and .retryAttempt == 1)' \
    "$TASK13_ARTIFACTS.required-gates.json"
  WARNING_SHA="$(node -e "const c=require('./scripts/quality/p00-contract.json');const h=require('node:crypto').createHash('sha256').update(c.bundle.warning).digest('hex');process.stdout.write(h)")"
  jq -e --arg warning_sha "$WARNING_SHA" \
    '.limitBytes == 216700 and .gzipBytes <= .limitBytes
     and .largeChunkDebt.status == "accepted-open"
     and .largeChunkDebt.messageSha256 == $warning_sha
     and .largeChunkDebt.occurrenceCount == 1
     and (.largeChunkDebt.affectedFiles | length) > 0' \
    "$TASK13_ARTIFACTS/frontend/bundle.json"
  test -z "$(git status --short --untracked-files=normal)"
  ~~~

  Expected: all six jobs and aggregate exit 0 at the clean Task 13 commit; SQLite 446, PostgreSQL 450, frontend unit 8, Playwright 9, zero retries/skips/failures, one immutable PostgreSQL 16 identity, bundle at or below 216700, and the exact Vite warning retained as accepted-open debt. Only this post-commit directory is canonical Task 13 evidence. Run the global writer-boundary checks once more.

### Task 14: Add the exact GitHub Actions adapter and freeze provider commands

**Files:**
- Create: `.github/workflows/p00-quality.yml`
- Create: `scripts/quality/github-actions-postgres-service`
- Create: `scripts/quality/test-github-actions-postgres-service.sh`
- Create: `scripts/quality/github-actions-normalize.mjs`
- Create: `scripts/quality/github-actions-normalize.test.mjs`
- External provider state, later in Task 17 only: the repository remains private and must first gain owner-authorized GitHub Pro capability for private-repository branch protection; `origin/main`; its push-triggered verification run (or the exact successful existing run reused after an interrupted attempt); repository default branch `main`; zero active repository or inherited ruleset rules applying to `main`; the exact classic `main` protection configuration (strict app-scoped `required-gates`, admins enforced, linear history and conversation resolution required, force pushes/deletions denied, branch creation allowed, branch unlocked, fork syncing denied, and no review or actor restrictions); and two new `workflow_dispatch` runs

**Interfaces:** GitHub Actions runs on the standard GitHub-hosted `ubuntu-24.04` x64 class. Producer jobs are exactly `composer-validation`, `php-style-static`, `sqlite`, `postgresql-16`, `frontend`, and `playwright`; dependent job `required-gates` is the sole aggregate status. Each producer checks out one exact SHA, installs from committed lockfiles, gives Composer only the job-scoped read-only GitHub token needed for authenticated dist downloads, runs PHP through one immutable Linux/x64 Laravel CI image, proves PHP `8.5.6`, Node `24.18.0`, npm `11.16.0`, Composer `2.9.8`, and every required PHP extension, attests the immutable PostgreSQL 16 image, uses `P00_RUNNER_ROLE=ci` and `P00_RUNNER_CLASS=github-hosted-ubuntu-24.04-x64`, and invokes only its matching Task 13 dispatcher job at attempt one. Every PHP-container invocation pre-records a deterministic unique name, uses a CID file, and carries exact kind/run/job/attempt labels; an `always()` step removes only a still-live lifecycle found by that pre-recorded name or all four exact labels, including a browser server whose process group was killed before the Docker client could write the CID or forward a signal. The aggregate preserves every job's closed platform observation instead of requiring byte-identical observations from separate ephemeral VMs. Local commands remain bound to `P00_RUNNER_ROLE=local` and `P00_RUNNER_CLASS=local-macos-arm64`. Provider mutation fails closed before any push unless the private repository exposes protection and effective-rules readback APIs.

GitHub classic protection binds a check context and GitHub App, not a workflow path. Ruleset workflows are organization/enterprise controls and are unavailable on this user-owned repository. Therefore P00 does not claim provider-enforced resistance to a malicious authorized contributor replacing the workflow with another GitHub Actions job named `required-gates`; exact-byte Control Room review remains the governing workflow-identity control. The owner must explicitly accept this bounded limitation with the amended plan. Transferring the repository or buying an organization/enterprise plan is outside P00 and remains unauthorized.

The sole machine-readable decision record in this Task 14 section is:

```json
{
  "adapterPaths": [
    ".github/workflows/p00-quality.yml",
    "scripts/quality/github-actions-postgres-service",
    "scripts/quality/test-github-actions-postgres-service.sh",
    "scripts/quality/github-actions-normalize.mjs",
    "scripts/quality/github-actions-normalize.test.mjs"
  ],
  "ciRunnerClass": "github-hosted-ubuntu-24.04-x64",
  "credentialCapability": "github-classic-oauth-workflow-scope-required",
  "immutableDependencyIdentities": [
    "actions/checkout@9c091bb21b7c1c1d1991bb908d89e4e9dddfe3e0",
    "actions/setup-node@820762786026740c76f36085b0efc47a31fe5020",
    "docker.io/kirschbaumdevelopment/laravel-test-runner@sha256:571f0ea5098c32a534eeca499708d4edeb2f8874a76c32ef546a523a311660c6",
    "https://getcomposer.org/download/2.9.8/composer.phar#sha256=59b2c50e10cafa0d8efc19ede9a326d782f096c674a26baf98cf042ce23de890",
    "actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a",
    "actions/download-artifact@3e5f45b2cfb9172054b4087a40e8e0b5a5461e7c",
    "docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28"
  ],
  "localRunnerClass": "local-macos-arm64",
  "normalizerSha256": "8ce8f6d66634c02f9097d6327c5ead941774c0aa56306be19cee588b15a93341",
  "normalizerTestSha256": "12720cc4df3315650ab2ec51b253a3a8e22f02f454f4bfff3187c665575a77a7",
  "provider": "github-actions",
  "providerCapability": "github-pro-private-branch-protection-required",
  "pushAndRunCommandsSha256": "ff1262226efdbd227f813151f4803b8a1f654038e21af01f426eb306d166af9e",
  "repositoryVisibility": "private",
  "requiredStatus": "required-gates",
  "requiredStatusCommandsSha256": "f16e139b1513bf4d043b22cb470aa60313a680fdb1c0227c0fa95a97c3bbbf70",
  "schemaVersion": 1,
  "state": "approved",
  "twoRunCommandsSha256": "2ca2c27375e1e25a19478e3c918091ee53056f7cb644fe9ad871dbafbebc724e",
  "workflowIdentityEnforcement": "control-room-exact-byte-review",
  "workflowIdentityLimitation": "classic-check-context-does-not-bind-workflow-path"
}
```

Each of the five hash fields is SHA-256 over the exact bytes between its named `BEGIN`/`END` markers after removing the Markdown-only two-space prefix from every non-empty line and appending one LF. Task 0 performs that extraction, requires exactly one marker pair and exact record equality, and runs negative in-memory fixtures for a pending state, wrong provider, reordered path list, missing immutable identity, and changed block hash.

The full-SHA action identities resolve respectively to checkout `v7.0.0`, setup-node `v7.0.0`, upload-artifact `v7.0.1`, and download-artifact `v8.0.1`. PHP runs through the immutable Linux/x64 Laravel test-runner child manifest shown above; its package database binds `php8.5-cli` and every declared extension to `8.5.6-1+ubuntu24.04.1+deb.sury.org+1`. The workflow bypasses the image entrypoint, proves `/usr/bin/php8.5` reports exactly `8.5.6`, and invokes the separately hash-pinned Composer `2.9.8` PHAR. The PostgreSQL identity is the official `16.14-bookworm` Linux/x64 child manifest; its parent OCI index is `sha256:53acb9d8524ff2f66d4fb81a964365027ccc275411d4d83d71e9266aa48535d9`. Mutable tags never appear in executable configuration.

- [ ] **Prove the five adapter paths are absent before creation.**

  ```bash
  test ! -e .github/workflows/p00-quality.yml
  test ! -e scripts/quality/github-actions-postgres-service
  test ! -e scripts/quality/test-github-actions-postgres-service.sh
  test ! -e scripts/quality/github-actions-normalize.mjs
  test ! -e scripts/quality/github-actions-normalize.test.mjs
  ```

  Expected: all checks exit `0`. An existing path is an unreviewed collision and stops this task.

- [ ] **Create and test the isolated PostgreSQL service helper.**

  The helper accepts only `start` and `stop`. `start` launches exactly the digest-pinned Linux/x64 image on loopback, creates a unique no-real-data supervisor and nonce, verifies the live server major and custom nonce, and writes the two closed sanitized attestations required by Tasks 0, 4, 11, and 13. It masks the credential URL before appending it to `GITHUB_ENV`. `stop` removes only the exact recorded container lifecycle ID after checking its run/job labels; absence, substitution, or cleanup failure is fatal and never broadens cleanup. Create the helper and its fake-Docker shell tests from the complete blocks below.

  `scripts/quality/github-actions-postgres-service`:

  <!-- TASK14_POSTGRES_HELPER_BEGIN -->
  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail

  readonly IMAGE='docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28'
  readonly IDENTITY="$IMAGE"
  readonly ACTION="${1:-}"

  require() {
    test -n "${!1:-}" || { printf 'P00_GHA_POSTGRES FAIL missing=%s\n' "$1" >&2; exit 2; }
  }

  for name in RUNNER_TEMP GITHUB_RUN_ID GITHUB_JOB GITHUB_RUN_ATTEMPT; do require "$name"; done
  test "$GITHUB_RUN_ATTEMPT" = 1 || { printf 'P00_GHA_POSTGRES FAIL retry\n' >&2; exit 2; }
  case "$GITHUB_RUN_ID" in (*[!0-9]*|'') exit 2;; esac
  case "$GITHUB_JOB" in (*[!A-Za-z0-9_-]*|'') exit 2;; esac
  readonly STATE_ROOT="$RUNNER_TEMP/dorzak-p00-postgres/$GITHUB_RUN_ID/$GITHUB_JOB/$GITHUB_RUN_ATTEMPT"
  readonly STATE_FILE="$STATE_ROOT/state.json"

  sha256_file() { shasum -a 256 "$1" | awk '{print $1}'; }
  sha256_text() { printf '%s' "$1" | shasum -a 256 | awk '{print $1}'; }

  cleanup_recorded_container() {
    test -f "$STATE_FILE" || return 0
    local id
    id="$(jq -er '.lifecycleId | select(test("^[0-9a-f]{64}$"))' "$STATE_FILE")"
    docker rm -f "$id" >/dev/null
  }

  start() {
    require GITHUB_ENV
    test "${RUNNER_OS:-Linux}" = Linux
    test "${RUNNER_ARCH:-X64}" = X64
    test ! -e "$STATE_ROOT"
    umask 077
    mkdir -p "$STATE_ROOT"

    local nonce nonce_sha suffix supervisor password id arch os port url version observed_nonce
    nonce="$(openssl rand -hex 32)"
    nonce_sha="$(sha256_text "$nonce")"
    suffix="$(openssl rand -hex 6)"
    supervisor="p00_${GITHUB_RUN_ID}_${suffix}"
    supervisor="${supervisor:0:50}"
    password="$(openssl rand -hex 32)"
    printf '::add-mask::%s\n' "$password"

    docker pull "$IMAGE" >/dev/null
    arch="$(docker image inspect --format '{{.Architecture}}' "$IMAGE")"
    os="$(docker image inspect --format '{{.Os}}' "$IMAGE")"
    test "$arch" = amd64
    test "$os" = linux

    id="$(docker run -d --rm --pull=never --platform linux/amd64 \
      --label "dorzak.p00.run=$GITHUB_RUN_ID" \
      --label "dorzak.p00.job=$GITHUB_JOB" \
      --label 'dorzak.p00.no-real-data=true' \
      -e "POSTGRES_USER=$supervisor" \
      -e "POSTGRES_PASSWORD=$password" \
      -e POSTGRES_DB=postgres \
      -p 127.0.0.1::5432 \
      "$IMAGE" -c "dorzak.instance_nonce_sha256=$nonce_sha")"
    printf '%s\n' "$id" | rg -x '[0-9a-f]{64}' >/dev/null
    jq -n --arg id "$id" --arg run "$GITHUB_RUN_ID" --arg job "$GITHUB_JOB" \
      '{lifecycleId:$id,runId:$run,job:$job}' > "$STATE_FILE"
    trap cleanup_recorded_container ERR INT TERM

    for _ in $(seq 1 60); do
      docker exec "$id" pg_isready -U "$supervisor" -d postgres >/dev/null 2>&1 && break
      sleep 1
    done
    docker exec "$id" pg_isready -U "$supervisor" -d postgres >/dev/null
    docker exec -e "PGPASSWORD=$password" "$id" psql -v ON_ERROR_STOP=1 -U "$supervisor" -d postgres -Atqc \
      'REVOKE CONNECT ON DATABASE postgres FROM PUBLIC; REVOKE CONNECT ON DATABASE template1 FROM PUBLIC;' >/dev/null
    version="$(docker exec -e "PGPASSWORD=$password" "$id" psql -v ON_ERROR_STOP=1 -U "$supervisor" -d postgres -Atqc 'SHOW server_version_num')"
    observed_nonce="$(docker exec -e "PGPASSWORD=$password" "$id" psql -v ON_ERROR_STOP=1 -U "$supervisor" -d postgres -Atqc "SELECT current_setting('dorzak.instance_nonce_sha256')")"
    case "$version" in (16[0-9][0-9][0-9][0-9]) ;; (*) exit 2;; esac
    test "$observed_nonce" = "$nonce_sha"
    port="$(docker port "$id" 5432/tcp | sed -n 's/^127\.0\.0\.1:\([0-9][0-9]*\)$/\1/p')"
    case "$port" in (*[!0-9]*|'') exit 2;; esac
    url="postgresql://${supervisor}:${password}@127.0.0.1:${port}/postgres?sslmode=disable"
    printf '::add-mask::%s\n' "$url"

    local pg_attestation service_attestation
    pg_attestation="$STATE_ROOT/postgresql-attestation.json"
    service_attestation="$STATE_ROOT/e2e-service-attestation.json"
    jq -n --arg identity "$IDENTITY" --arg nonce "$nonce_sha" \
      '{schemaVersion:2,kind:"oci",identity:$identity,serverMajor:16,immutable:true,instanceNonceSha256:$nonce}' \
      > "$pg_attestation"
    jq -n --arg identity "$IDENTITY" --arg nonce "$nonce_sha" --arg lifecycle "$id" --arg role "$supervisor" \
      '{schemaVersion:1,identity:$identity,serverMajor:16,immutable:true,instanceNonceSha256:$nonce,lifecycleId:$lifecycle,supervisorDatabase:"postgres",supervisorRole:$role,containsRealData:false,canIssueIsolatedCredentials:true,noncandidateAccessDenied:true}' \
      > "$service_attestation"
    chmod 600 "$STATE_FILE" "$pg_attestation" "$service_attestation"

    {
      printf 'P00_PG_IDENTITY_KIND=oci\n'
      printf 'P00_PG_IDENTITY=%s\n' "$IDENTITY"
      printf 'P00_PG_ATTESTATION_PATH=%s\n' "$pg_attestation"
      printf 'P00_PG_ATTESTATION_SHA256=%s\n' "$(sha256_file "$pg_attestation")"
      printf 'P00_PG_INSTANCE_NONCE_SHA256=%s\n' "$nonce_sha"
      printf 'P00_E2E_SUPERVISOR_DB_URL=%s\n' "$url"
      printf 'P00_E2E_SERVICE_LIFECYCLE_ID=%s\n' "$id"
      printf 'P00_E2E_SERVICE_ATTESTATION_PATH=%s\n' "$service_attestation"
      printf 'P00_E2E_SERVICE_ATTESTATION_SHA256=%s\n' "$(sha256_file "$service_attestation")"
    } >> "$GITHUB_ENV"
    trap - ERR INT TERM
    printf 'P00_GHA_POSTGRES PASS action=start identity=%s lifecycle=%s\n' "$IDENTITY" "$id"
  }

  stop() {
    test -f "$STATE_FILE"
    local id run_label job_label
    id="$(jq -er '.lifecycleId | select(test("^[0-9a-f]{64}$"))' "$STATE_FILE")"
    run_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.run"}}' "$id")"
    job_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.job"}}' "$id")"
    test "$run_label" = "$GITHUB_RUN_ID"
    test "$job_label" = "$GITHUB_JOB"
    docker rm -f "$id" >/dev/null
    if docker inspect "$id" >/dev/null 2>&1; then exit 2; fi
    rm -f "$STATE_FILE" "$STATE_ROOT/postgresql-attestation.json" "$STATE_ROOT/e2e-service-attestation.json"
    rmdir "$STATE_ROOT"
    printf 'P00_GHA_POSTGRES PASS action=stop lifecycle=%s\n' "$id"
  }

  case "$ACTION" in
    start) start ;;
    stop) stop ;;
    *) printf 'usage: %s start|stop\n' "$0" >&2; exit 2 ;;
  esac
  ~~~
  <!-- TASK14_POSTGRES_HELPER_END -->

  `scripts/quality/test-github-actions-postgres-service.sh`:

  <!-- TASK14_POSTGRES_TEST_BEGIN -->
  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail

  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  HELPER="$ROOT/scripts/quality/github-actions-postgres-service"
  test "$(rg -c 'docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28' "$HELPER")" = 1
  test -z "$(rg -n 'postgres:[0-9]' "$HELPER" || true)"
  test -z "$(rg -n 'docker (rm|stop).*(\*|\$\()' "$HELPER" || true)"
  bash -n "$HELPER"

  TEMP="$(mktemp -d)"
  trap 'rm -rf "$TEMP"' EXIT
  mkdir -p "$TEMP/bin" "$TEMP/runner"
  export FAKE_DOCKER_STATE="$TEMP/docker"
  mkdir -p "$FAKE_DOCKER_STATE"
  cat > "$TEMP/bin/docker" <<'DOCKER'
  #!/usr/bin/env bash
  set -euo pipefail
  printf '%q ' "$@" >> "$FAKE_DOCKER_STATE/calls"
  printf '\n' >> "$FAKE_DOCKER_STATE/calls"
  case "$1 $2" in
    'pull docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28') exit 0 ;;
    'image inspect')
      case "$4" in ('{{.Architecture}}') printf '%s\n' "${FAKE_ARCH:-amd64}" ;; ('{{.Os}}') printf 'linux\n' ;; (*) exit 2 ;; esac ;;
    'run -d')
      nonce="$(printf '%s\n' "$*" | sed -n 's/.*dorzak\.instance_nonce_sha256=\([0-9a-f]\{64\}\).*/\1/p')"
      printf '%s' "$nonce" > "$FAKE_DOCKER_STATE/nonce"
      printf '%064d\n' 0 | tr 0 a ;;
    exec\ *)
      if printf '%s\n' "$*" | rg -q 'pg_isready|REVOKE CONNECT'; then exit 0; fi
      if printf '%s\n' "$*" | rg -q 'server_version_num'; then printf '160014\n'; exit 0; fi
      if printf '%s\n' "$*" | rg -q 'current_setting'; then cat "$FAKE_DOCKER_STATE/nonce"; printf '\n'; exit 0; fi
      exit 2 ;;
    'port aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') printf '127.0.0.1:54321\n' ;;
    'inspect --format')
      if printf '%s\n' "$3" | rg -q 'dorzak.p00.run'; then printf '7001\n'; else printf 'postgresql-16\n'; fi ;;
    'rm -f') printf '%s' removed > "$FAKE_DOCKER_STATE/removed" ;;
    'inspect aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') test ! -f "$FAKE_DOCKER_STATE/removed" ;;
    *) exit 2 ;;
  esac
  DOCKER
  chmod +x "$TEMP/bin/docker"

  export PATH="$TEMP/bin:$PATH"
  export RUNNER_TEMP="$TEMP/runner"
  export RUNNER_OS=Linux
  export RUNNER_ARCH=X64
  export GITHUB_RUN_ID=7001
  export GITHUB_JOB=postgresql-16
  export GITHUB_RUN_ATTEMPT=1
  export GITHUB_ENV="$TEMP/github-env"
  start_output="$($HELPER start)"
  test -z "$(printf '%s\n' "$start_output" | rg -v '^::add-mask::' | rg 'postgresql://|POSTGRES_PASSWORD' || true)"
  rg -x 'P00_PG_IDENTITY=docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28' "$GITHUB_ENV"
  rg -x 'P00_PG_ATTESTATION_SHA256=[0-9a-f]{64}' "$GITHUB_ENV"
  rg -x 'P00_E2E_SERVICE_LIFECYCLE_ID=[0-9a-f]{64}' "$GITHUB_ENV"
  $HELPER stop
  test "$(rg -c '^rm -f a{64} $' "$FAKE_DOCKER_STATE/calls")" = 1
  test -z "$(rg 'rm -f .*(\*|dorzak-p00-postgres)' "$FAKE_DOCKER_STATE/calls" || true)"

  rm -rf "$RUNNER_TEMP/dorzak-p00-postgres" "$FAKE_DOCKER_STATE"
  mkdir -p "$FAKE_DOCKER_STATE"
  : > "$GITHUB_ENV"
  export FAKE_ARCH=arm64
  if "$HELPER" start >"$TEMP/wrong-arch.log" 2>&1; then exit 1; fi
  test -z "$(rg 'postgresql://|POSTGRES_PASSWORD' "$TEMP/wrong-arch.log" || true)"

  export GITHUB_RUN_ATTEMPT=2
  if "$HELPER" start >"$TEMP/retry.log" 2>&1; then exit 1; fi
  rg -q 'retry' "$TEMP/retry.log"
  printf 'P00_GHA_POSTGRES_TEST PASS\n'
  ~~~
  <!-- TASK14_POSTGRES_TEST_END -->

- [ ] **Create the complete GitHub Actions workflow.**

  The workflow has `pull_request` and `push` triggers for `main` plus `workflow_dispatch` inputs `integrated_sha` and `evidence_nonce` for the two canonical evidence runs. It never uses `pull_request_target`, caches, mutable action tags, retries, `continue-on-error`, artifact overwrite, or cancellation-to-green. Global token permissions are read-only. Every producer uploads one uniquely named closed artifact; `required-gates` downloads all six by exact name, rejects any failed dependency, recomputes the Task 13 aggregate, and uploads exactly one aggregate artifact.

  Run this deterministic generator once from the repository root. It exclusively creates the complete workflow and fails if the path already exists:

  <!-- TASK14_WORKFLOW_GENERATOR_BEGIN -->
  ~~~js
  node --input-type=module <<'NODE'
  import { mkdirSync, writeFileSync } from 'node:fs';

  const jobs = [
    'composer-validation',
    'php-style-static',
    'sqlite',
    'postgresql-16',
    'frontend',
    'playwright',
  ];
  const checkout = 'actions/checkout@9c091bb21b7c1c1d1991bb908d89e4e9dddfe3e0';
  const setupNode = 'actions/setup-node@820762786026740c76f36085b0efc47a31fe5020';
  const phpImage = 'docker.io/kirschbaumdevelopment/laravel-test-runner@sha256:571f0ea5098c32a534eeca499708d4edeb2f8874a76c32ef546a523a311660c6';
  const composerUrl = 'https://getcomposer.org/download/2.9.8/composer.phar';
  const composerSha256 = '59b2c50e10cafa0d8efc19ede9a326d782f096c674a26baf98cf042ce23de890';
  const upload = 'actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a';
  const download = 'actions/download-artifact@3e5f45b2cfb9172054b4087a40e8e0b5a5461e7c';

  const indentContinuation = (value) => value.split('\n').map((line, index) => (
    index === 0 || line === '' ? line : '  ' + line
  )).join('\n');
  const restoreWorkflowIndentation = (value) => {
    let section = 'root';
    let requiredGates = false;
    return value.split('\n').map((line) => {
      const root = /^(on|permissions|env|jobs):$/.exec(line);
      if (root) {
        section = root[1];
        requiredGates = false;
        return line;
      }
      if (section === 'jobs' && line === 'required-gates:') requiredGates = true;
      if (line === '') return line;
      return ['on', 'permissions', 'env'].includes(section) || requiredGates ? '  ' + line : line;
    }).join('\n');
  };

  const producer = (job) => indentContinuation(`  ${job}:
    name: ${job}
    runs-on: ubuntu-24.04
    timeout-minutes: 60
    steps:
      - name: Validate immutable run identity
        shell: bash
        run: |
          set -euo pipefail
          test "$GITHUB_RUN_ATTEMPT" = 1
          printf '%s\\n' "$P00_INTEGRATED_SHA" | grep -Eq '^[0-9a-f]{40}$'
          test "$P00_INTEGRATED_SHA" = "$GITHUB_SHA"
          case "$GITHUB_EVENT_NAME" in
            workflow_dispatch) printf '%s\\n' "$P00_EVIDENCE_NONCE" | grep -Eq '^[0-9a-f]{64}$' ;;
            push|pull_request) printf '%s\\n' "$P00_EVIDENCE_NONCE" | grep -Eq '^[0-9]+$' ;;
            *) exit 2 ;;
          esac
      - name: Checkout exact integrated SHA
        uses: ${checkout}
        with:
          ref: \${{ env.P00_INTEGRATED_SHA }}
          fetch-depth: 1
          persist-credentials: false
      - name: Verify checkout
        shell: bash
        run: |
          set -euo pipefail
          test "$(git rev-parse HEAD)" = "$P00_INTEGRATED_SHA"
          test -z "$(git status --short --untracked-files=normal)"
          test -f "$P00_CONTROL_RECORD"
      - name: Install exact containerized PHP 8.5.6 and Composer 2.9.8
        shell: bash
        run: |
          set -euo pipefail
          test "$RUNNER_OS" = Linux
          test "$RUNNER_ARCH" = X64
          runtime="$RUNNER_TEMP/dorzak-p00-php"
          test ! -e "$runtime"
          mkdir -p "$runtime/bin" "$runtime/home" "$runtime/containers"
          chmod 700 "$runtime" "$runtime/bin" "$runtime/home" "$runtime/containers"
          curl --fail --location --proto '=https' --tlsv1.2 --silent --show-error \\
            '${composerUrl}' --output "$runtime/composer.phar"
          printf '%s  %s\\n' '${composerSha256}' "$runtime/composer.phar" | sha256sum --check --strict -
          chmod 0555 "$runtime/composer.phar"
          docker pull '${phpImage}' >/dev/null
          test "$(docker image inspect --format '{{.Architecture}}' '${phpImage}')" = amd64
          test "$(docker image inspect --format '{{.Os}}' '${phpImage}')" = linux
          cat > "$runtime/bin/p00-php-container" <<'BASH'
          #!/usr/bin/env bash
          set -euo pipefail
          readonly IMAGE='${phpImage}'
          readonly WORKSPACE="\${GITHUB_WORKSPACE:?GITHUB_WORKSPACE is required}"
          readonly TEMP="\${RUNNER_TEMP:?RUNNER_TEMP is required}"
          readonly RUNTIME="$TEMP/dorzak-p00-php"
          readonly CONTAINERS="$RUNTIME/containers"
          readonly RUN_ID="\${GITHUB_RUN_ID:?GITHUB_RUN_ID is required}"
          readonly JOB="\${GITHUB_JOB:?GITHUB_JOB is required}"
          readonly ATTEMPT="\${GITHUB_RUN_ATTEMPT:?GITHUB_RUN_ATTEMPT is required}"
          readonly KIND=php-runner
          test "$WORKSPACE" = "$(realpath "$WORKSPACE")"
          test "$TEMP" = "$(realpath "$TEMP")"
          test -d "$CONTAINERS" && test ! -L "$CONTAINERS"
          case "$RUN_ID" in (*[!0-9]*|'') exit 2;; esac
          case "$JOB" in (*[!A-Za-z0-9_-]*|'') exit 2;; esac
          case "$ATTEMPT" in (*[!0-9]*|'') exit 2;; esac
          case "$PWD/" in "$WORKSPACE/"*|"$TEMP/"*) ;; *) exit 2 ;; esac
          env_args=()
          while IFS='=' read -r name _; do
            printf '%s\\n' "$name" | grep -Eq '^[A-Za-z_][A-Za-z0-9_]*$' || continue
            case "$name" in
              CI|GITHUB_ACTIONS|GITHUB_EVENT_NAME|GITHUB_JOB|GITHUB_RUN_ATTEMPT|GITHUB_RUN_ID|GITHUB_SHA|P00_*|APP_*|DB_*|CACHE_*|QUEUE_*|SESSION_*|MAIL_*|LOG_*|FILESYSTEM_*|BROADCAST_*|REDIS_*|FRONTEND_*|COMPOSER_AUTH|COMPOSER_*) ;;
              *) continue ;;
            esac
            env_args+=(--env "$name")
          done < <(env)
          remove_exact_container() {
            local target="$1" expected_id="$2" listed id kind_label run_label job_label attempt_label remaining
            listed="$(docker ps -aq --no-trunc --filter "name=^/$target$")"
            test "$(printf '%s\\n' "$listed" | sed '/^$/d' | wc -l | tr -d ' ')" -le 1
            if test -z "$listed"; then return 0; fi
            id="$(printf '%s\\n' "$listed" | sed -n '1p')"
            printf '%s\\n' "$id" | grep -Eq '^[0-9a-f]{64}$'
            if test -n "$expected_id"; then test "$id" = "$expected_id"; fi
            kind_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.kind"}}' "$id")"
            run_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.run"}}' "$id")"
            job_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.job"}}' "$id")"
            attempt_label="$(docker inspect --format '{{index .Config.Labels "dorzak.p00.attempt"}}' "$id")"
            test "$kind_label" = "$KIND"
            test "$run_label" = "$RUN_ID"
            test "$job_label" = "$JOB"
            test "$attempt_label" = "$ATTEMPT"
            docker rm -f "$id" >/dev/null
            remaining="$(docker ps -aq --no-trunc --filter "name=^/$target$")"
            test -z "$remaining"
          }
          run_php() {
            local invocation suffix cidfile namefile container_name status id
            invocation="$(mktemp -d "$CONTAINERS/invocation-XXXXXX")"
            suffix="\${invocation##*-}"
            cidfile="$invocation/container.id"
            namefile="$invocation/container.name"
            container_name="dorzak-p00-php-$RUN_ID-$JOB-$ATTEMPT-$suffix"
            printf '%s\\n' "$container_name" > "$namefile"
            chmod 600 "$namefile"
            set +e
            docker run --rm --init --network host \\
              --name "$container_name" \\
              --cidfile "$cidfile" \\
              --label "dorzak.p00.kind=$KIND" \\
              --label "dorzak.p00.run=$RUN_ID" \\
              --label "dorzak.p00.job=$JOB" \\
              --label "dorzak.p00.attempt=$ATTEMPT" \\
              --user "$(id -u):$(id -g)" \\
              --workdir "$PWD" \\
              --volume "$WORKSPACE:$WORKSPACE" \\
              --volume "$TEMP:$TEMP" \\
              --volume "$RUNTIME/composer.phar:/p00/composer.phar:ro" \\
              --volume "$RUNTIME/home:/p00-home" \\
              --env "GITHUB_WORKSPACE=$WORKSPACE" \\
              --env "RUNNER_TEMP=$TEMP" \\
              --env 'HOME=/p00-home' \\
              --env 'COMPOSER_HOME=/p00-home/composer' \\
              --env 'PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin' \\
              --env 'PHP_MEMORY_LIMIT=-1' \\
              "\${env_args[@]}" \\
              --entrypoint /usr/bin/php8.5 \\
              "$IMAGE" "$@"
            status="$?"
            set -e
            id=''
            if test -e "$cidfile"; then
              test -f "$cidfile" && test ! -L "$cidfile"
              id="$(cat "$cidfile")"
              if test -n "$id"; then printf '%s\\n' "$id" | grep -Eq '^[0-9a-f]{64}$'; fi
            fi
            remove_exact_container "$container_name" "$id"
            if test -e "$cidfile"; then
              rm -f "$cidfile"
            fi
            rm -f "$namefile"
            rmdir "$invocation"
            return "$status"
          }
          case "$(basename "$0")" in
            php) run_php "$@" ;;
            composer) run_php /p00/composer.phar "$@" ;;
            *) exit 2 ;;
          esac
          BASH
          chmod 0555 "$runtime/bin/p00-php-container"
          ln -s p00-php-container "$runtime/bin/php"
          ln -s p00-php-container "$runtime/bin/composer"
          printf '%s\\n' "$runtime/bin" >> "$GITHUB_PATH"
          test "$("$runtime/bin/php" -r 'echo PHP_VERSION;')" = 8.5.6
          test "$("$runtime/bin/composer" --version --no-ansi | awk '{print $3}')" = 2.9.8
          "$runtime/bin/php" -r 'foreach (array_slice($argv, 1) as $extension) {
            if (!extension_loaded($extension)) { fwrite(STDERR, "missing extension: $extension\\n"); exit(1); }
          }' bcmath ctype curl dom fileinfo filter gd intl mbstring openssl pcntl pdo pdo_pgsql pdo_sqlite session simplexml tokenizer xml zip
      - name: Set up Node 24.18.0
        uses: ${setupNode}
        with:
          node-version: 24.18.0
          architecture: x64
          check-latest: false
          package-manager-cache: false
      - name: Install exact npm and verify every runtime
        shell: bash
        run: |
          set -euo pipefail
          npm install --global npm@11.16.0 --ignore-scripts
          test "$(php -r 'echo PHP_VERSION;')" = 8.5.6
          test "$(node -p 'process.versions.node')" = 24.18.0
          test "$(npm --version)" = 11.16.0
          composer_version="$(composer --version --no-ansi | sed -n 's/^Composer version \\([^ ]*\\).*/\\1/p')"
          test "$composer_version" = 2.9.8
      - name: Install committed dependencies only
        shell: bash
        env:
          COMPOSER_AUTH: '{"github-oauth":{"github.com":"\${{ github.token }}"}}'
        run: |
          set -euo pipefail
          (cd backend && composer install --no-interaction --prefer-dist --no-progress)
          npm ci
          npx playwright install --with-deps chromium
          test -z "$(git status --short --untracked-files=normal)"
      - id: postgres
        name: Start and attest isolated PostgreSQL 16
        shell: bash
        run: scripts/quality/github-actions-postgres-service start
      - name: Run ${job}
        shell: bash
        run: |
          set -euo pipefail
          export P00_ARTIFACT_DIR="$RUNNER_TEMP/p00"
          scripts/quality/run-p00 ${job}
          test -d "$P00_ARTIFACT_DIR/${job}"
          test ! -L "$P00_ARTIFACT_DIR/${job}"
      - name: Upload ${job} artifact
        uses: ${upload}
        with:
          name: ${job}
          path: \${{ runner.temp }}/p00/${job}/
          if-no-files-found: error
          retention-days: 14
          compression-level: 0
          overwrite: false
          include-hidden-files: true
      - name: Remove exact PHP runner lifecycles
        if: \${{ always() }}
        shell: bash
        run: |
          set -euo pipefail
          state="$RUNNER_TEMP/dorzak-p00-php/containers"
          kind=php-runner
          case "$GITHUB_RUN_ID" in (*[!0-9]*|'') exit 2;; esac
          case "$GITHUB_JOB" in (*[!A-Za-z0-9_-]*|'') exit 2;; esac
          case "$GITHUB_RUN_ATTEMPT" in (*[!0-9]*|'') exit 2;; esac
          remove_exact() {
            local id="$1"
            printf '%s\\n' "$id" | grep -Eq '^[0-9a-f]{64}$'
            test "$(docker inspect --format '{{index .Config.Labels "dorzak.p00.kind"}}' "$id")" = "$kind"
            test "$(docker inspect --format '{{index .Config.Labels "dorzak.p00.run"}}' "$id")" = "$GITHUB_RUN_ID"
            test "$(docker inspect --format '{{index .Config.Labels "dorzak.p00.job"}}' "$id")" = "$GITHUB_JOB"
            test "$(docker inspect --format '{{index .Config.Labels "dorzak.p00.attempt"}}' "$id")" = "$GITHUB_RUN_ATTEMPT"
            docker rm -f "$id" >/dev/null
          }
          sweep_exact_labels() {
            local listed id
            listed="$(docker ps -aq --no-trunc \\
              --filter "label=dorzak.p00.kind=$kind" \\
              --filter "label=dorzak.p00.run=$GITHUB_RUN_ID" \\
              --filter "label=dorzak.p00.job=$GITHUB_JOB" \\
              --filter "label=dorzak.p00.attempt=$GITHUB_RUN_ATTEMPT")"
            printf '%s\\n' "$listed" | while IFS= read -r id; do
              if test -z "$id"; then continue; fi
              remove_exact "$id"
            done
          }
          sweep_exact_labels
          sweep_exact_labels
          remaining="$(docker ps -aq --no-trunc \\
            --filter "label=dorzak.p00.kind=$kind" \\
            --filter "label=dorzak.p00.run=$GITHUB_RUN_ID" \\
            --filter "label=dorzak.p00.job=$GITHUB_JOB" \\
            --filter "label=dorzak.p00.attempt=$GITHUB_RUN_ATTEMPT")"
          test -z "$remaining"
          if test -d "$state"; then
            test ! -L "$state"
            for invocation in "$state"/invocation-*; do
              if test ! -e "$invocation"; then continue; fi
              test -d "$invocation" && test ! -L "$invocation"
              cidfile="$invocation/container.id"
              namefile="$invocation/container.name"
              unexpected="$(find "$invocation" -mindepth 1 -maxdepth 1 ! -name container.id ! -name container.name -print -quit)"
              test -z "$unexpected"
              if test -e "$cidfile"; then
                test -f "$cidfile" && test ! -L "$cidfile"
                id="$(cat "$cidfile")"
                if test -n "$id"; then
                  printf '%s\\n' "$id" | grep -Eq '^[0-9a-f]{64}$'
                fi
                rm -f "$cidfile"
              fi
              if test -e "$namefile"; then
                test -f "$namefile" && test ! -L "$namefile"
                container_name="$(cat "$namefile")"
                printf '%s\\n' "$container_name" | grep -Eq '^dorzak-p00-php-[0-9]+-[A-Za-z0-9_-]+-[0-9]+-[A-Za-z0-9]+$'
                rm -f "$namefile"
              fi
              rmdir "$invocation"
            done
            remaining_state="$(find "$state" -mindepth 1 -print -quit)"
            test -z "$remaining_state"
          fi
      - name: Stop exact PostgreSQL lifecycle
        if: \${{ always() && steps.postgres.outcome == 'success' }}
        shell: bash
        run: scripts/quality/github-actions-postgres-service stop
  `);

  const downloads = jobs.map((job) => `    - name: Download ${job}
        uses: ${download}
        with:
          name: ${job}
          path: \${{ runner.temp }}/p00/${job}
`).join('');

  const workflow = restoreWorkflowIndentation(`name: P00 Quality
run-name: p00-\${{ github.event_name == 'workflow_dispatch' && inputs.evidence_nonce || github.run_id }}

on:
  pull_request:
    branches: [main]
  push:
    branches: [main]
  workflow_dispatch:
    inputs:
      integrated_sha:
        description: Exact 40-hex commit already at refs/heads/main
        required: true
        type: string
      evidence_nonce:
        description: Unique 64-hex nonce identifying one new evidence run
        required: true
        type: string

permissions:
  actions: read
  contents: read

env:
  P00_CONTROL_RECORD: docs/superpowers/control/execution/p00-execution-entry.json
  P00_PHP_VERSION: 8.5.6
  P00_NODE_VERSION: 24.18.0
  P00_NPM_VERSION: 11.16.0
  P00_COMPOSER_VERSION: 2.9.8
  P00_RUNNER_ROLE: ci
  P00_RUNNER_CLASS: github-hosted-ubuntu-24.04-x64
  P00_ATTEMPT: \${{ github.run_attempt }}
  P00_INTEGRATED_SHA: \${{ github.event_name == 'workflow_dispatch' && inputs.integrated_sha || github.sha }}
  P00_EVIDENCE_NONCE: \${{ github.event_name == 'workflow_dispatch' && inputs.evidence_nonce || github.run_id }}

jobs:
${jobs.map(producer).join('')}
  required-gates:
    name: required-gates
    if: \${{ always() }}
    needs: [composer-validation, php-style-static, sqlite, postgresql-16, frontend, playwright]
    runs-on: ubuntu-24.04
    timeout-minutes: 20
    steps:
      - name: Require all six producer jobs
        shell: bash
        run: |
          set -euo pipefail
          test '\${{ needs.composer-validation.result }}' = success
          test '\${{ needs.php-style-static.result }}' = success
          test '\${{ needs.sqlite.result }}' = success
          test '\${{ needs.postgresql-16.result }}' = success
          test '\${{ needs.frontend.result }}' = success
          test '\${{ needs.playwright.result }}' = success
          test "$GITHUB_RUN_ATTEMPT" = 1
          test "$P00_INTEGRATED_SHA" = "$GITHUB_SHA"
      - name: Checkout exact integrated SHA
        uses: ${checkout}
        with:
          ref: \${{ env.P00_INTEGRATED_SHA }}
          fetch-depth: 1
          persist-credentials: false
      - name: Set up Node 24.18.0
        uses: ${setupNode}
        with:
          node-version: 24.18.0
          architecture: x64
          check-latest: false
          package-manager-cache: false
  ${downloads}    - name: Recompute required aggregate
        shell: bash
        run: |
          set -euo pipefail
          test "$(git rev-parse HEAD)" = "$P00_INTEGRATED_SHA"
          test "$(find "$RUNNER_TEMP/p00" -mindepth 1 -maxdepth 1 -type d | wc -l | tr -d ' ')" = 6
          node scripts/quality/p00.mjs aggregate "$RUNNER_TEMP/p00"
          mkdir "$RUNNER_TEMP/required-gates"
          mv "$RUNNER_TEMP/p00.required-gates.json" "$RUNNER_TEMP/required-gates/required-gates.json"
      - name: Upload required-gates artifact
        uses: ${upload}
        with:
          name: required-gates
          path: \${{ runner.temp }}/required-gates/
          if-no-files-found: error
          retention-days: 14
          compression-level: 0
          overwrite: false
          include-hidden-files: true
  `);

  mkdirSync('.github/workflows', { recursive: true });
  writeFileSync('.github/workflows/p00-quality.yml', workflow, { flag: 'wx' });
  NODE
  ~~~
  <!-- TASK14_WORKFLOW_GENERATOR_END -->

  Expected: `.github/workflows/p00-quality.yml` is created once; each producer contains the same immutable setup and runs one distinct Task 13 job; the aggregate has six exact dependencies and no provider write permission.

- [ ] **Create the complete provider normalizer and fail-closed tests.**

  The normalizer consumes one canonical GitHub metadata document plus exactly seven downloaded provider ZIP files. It verifies provider digests, safely parses and extracts only regular ZIP entries, rejects ZIP64/encryption/path escapes/duplicates/symlinks/special files/oversize payloads, reconstructs exactly six canonical job directories, calls Task 13 aggregation to recompute reporter counts and every file hash, requires byte-equivalence with the downloaded aggregate, and atomically emits one Task 13 CI-run document. Its tests cover valid input and every registered provider, archive, count, identity, retry, extra-entry, escape, symlink, and partial-publication refusal.

  `scripts/quality/github-actions-normalize.mjs`:

  <!-- TASK14_NORMALIZER_BEGIN -->
  ~~~js
  import { createHash } from 'node:crypto';
  import {
    existsSync,
    lstatSync,
    mkdirSync,
    mkdtempSync,
    readFileSync,
    readdirSync,
    realpathSync,
    renameSync,
    rmSync,
    writeFileSync,
  } from 'node:fs';
  import { basename, dirname, join, resolve } from 'node:path';
  import { fileURLToPath } from 'node:url';
  import { inflateRawSync } from 'node:zlib';
  import {
    aggregateRequiredGates,
    assertAggregate,
    contract,
    contractSha256,
    sha256,
    stableJson,
  } from './p00.mjs';

  const SHA40 = /^[0-9a-f]{40}$/;
  const SHA64 = /^[0-9a-f]{64}$/;
  const JOBS = contract.jobs.map((job) => job.name);
  const JOB_SET = new Set(JOBS);
  const ARTIFACTS = [...JOBS, 'required-gates'];
  const MAX_ENTRIES = 128;
  const MAX_ENTRY_BYTES = 64 * 1024 * 1024;
  const MAX_TOTAL_BYTES = 128 * 1024 * 1024;
  const MAX_METADATA_BYTES = 1024 * 1024;

  function invariant(condition, message) {
    if (!condition) throw new Error(message);
  }

  function exactKeys(value, keys, label) {
    invariant(value !== null && typeof value === 'object' && !Array.isArray(value), label + ' must be an object');
    invariant(JSON.stringify(Object.keys(value).sort()) === JSON.stringify([...keys].sort()), label + ' keys are not closed');
  }

  function readJson(path) {
    return JSON.parse(readFileSync(path, 'utf8'));
  }

  const crcTable = Array.from({ length: 256 }, (_, value) => {
    let crc = value;
    for (let bit = 0; bit < 8; bit += 1) crc = (crc >>> 1) ^ ((crc & 1) ? 0xedb88320 : 0);
    return crc >>> 0;
  });

  export function crc32(bytes) {
    let crc = 0xffffffff;
    for (const byte of bytes) crc = (crc >>> 8) ^ crcTable[(crc ^ byte) & 0xff];
    return (crc ^ 0xffffffff) >>> 0;
  }

  function safeArchivePath(name) {
    invariant(typeof name === 'string' && name.length > 0 && name.length <= 240, 'Unsafe ZIP entry length');
    invariant(!name.includes('\\') && !name.includes('\0') && !name.startsWith('/'), 'Unsafe ZIP entry path');
    invariant(/^[A-Za-z0-9._/-]+$/.test(name), 'ZIP entry has unsupported characters');
    const directory = name.endsWith('/');
    const body = directory ? name.slice(0, -1) : name;
    const parts = body.split('/');
    invariant(parts.length > 0 && parts.every((part) => part !== '' && part !== '.' && part !== '..'), 'ZIP entry escapes root');
    return { directory, path: parts.join('/') };
  }

  function findEndOfCentralDirectory(bytes) {
    const minimum = Math.max(0, bytes.length - 22 - 65535);
    for (let offset = bytes.length - 22; offset >= minimum; offset -= 1) {
      if (bytes.readUInt32LE(offset) !== 0x06054b50) continue;
      const commentLength = bytes.readUInt16LE(offset + 20);
      if (offset + 22 + commentLength === bytes.length) return offset;
    }
    throw new Error('ZIP end-of-central-directory is missing');
  }

  function containsZip64Extra(bytes, offset, length) {
    const end = offset + length;
    invariant(end <= bytes.length, 'ZIP extra-field boundary is invalid');
    let cursor = offset;
    while (cursor < end) {
      invariant(cursor + 4 <= end, 'ZIP extra-field header is truncated');
      const id = bytes.readUInt16LE(cursor);
      const size = bytes.readUInt16LE(cursor + 2);
      cursor += 4;
      invariant(cursor + size <= end, 'ZIP extra-field payload is truncated');
      if (id === 0x0001) return true;
      cursor += size;
    }
    invariant(cursor === end, 'ZIP extra-field boundary is invalid');
    return false;
  }

  export function inspectZip(bytes) {
    invariant(Buffer.isBuffer(bytes) && bytes.length >= 22, 'ZIP bytes are invalid');
    const eocd = findEndOfCentralDirectory(bytes);
    const disk = bytes.readUInt16LE(eocd + 4);
    const centralDisk = bytes.readUInt16LE(eocd + 6);
    const diskEntries = bytes.readUInt16LE(eocd + 8);
    const totalEntries = bytes.readUInt16LE(eocd + 10);
    const centralSize = bytes.readUInt32LE(eocd + 12);
    const centralOffset = bytes.readUInt32LE(eocd + 16);
    invariant(disk === 0 && centralDisk === 0 && diskEntries === totalEntries, 'Multi-disk ZIP is prohibited');
    invariant(totalEntries > 0 && totalEntries <= MAX_ENTRIES, 'ZIP entry count is invalid');
    invariant(totalEntries !== 0xffff && centralSize !== 0xffffffff && centralOffset !== 0xffffffff, 'ZIP64 is prohibited');
    invariant(centralOffset + centralSize === eocd, 'ZIP central directory boundary is invalid');
    const entries = [];
    const names = new Set();
    let cursor = centralOffset;
    let totalUncompressed = 0;
    for (let index = 0; index < totalEntries; index += 1) {
      invariant(bytes.readUInt32LE(cursor) === 0x02014b50, 'ZIP central entry signature is invalid');
      const madeBy = bytes.readUInt16LE(cursor + 4);
      const flags = bytes.readUInt16LE(cursor + 8);
      const method = bytes.readUInt16LE(cursor + 10);
      const expectedCrc32 = bytes.readUInt32LE(cursor + 16);
      const compressedSize = bytes.readUInt32LE(cursor + 20);
      const uncompressedSize = bytes.readUInt32LE(cursor + 24);
      const nameLength = bytes.readUInt16LE(cursor + 28);
      const extraLength = bytes.readUInt16LE(cursor + 30);
      const commentLength = bytes.readUInt16LE(cursor + 32);
      const startDisk = bytes.readUInt16LE(cursor + 34);
      const externalAttributes = bytes.readUInt32LE(cursor + 38);
      const localOffset = bytes.readUInt32LE(cursor + 42);
      const end = cursor + 46 + nameLength + extraLength + commentLength;
      invariant(end <= eocd && startDisk === 0, 'ZIP central entry boundary is invalid');
      invariant((flags & 0x0001) === 0, 'Encrypted ZIP entries are prohibited');
      invariant(method === 0 || method === 8, 'ZIP compression method is unsupported');
      invariant(compressedSize !== 0xffffffff && uncompressedSize !== 0xffffffff && localOffset !== 0xffffffff, 'ZIP64 entry is prohibited');
      invariant(!containsZip64Extra(bytes, cursor + 46 + nameLength, extraLength), 'ZIP64 extra field is prohibited');
      invariant(uncompressedSize <= MAX_ENTRY_BYTES, 'ZIP entry is too large');
      totalUncompressed += uncompressedSize;
      invariant(totalUncompressed <= MAX_TOTAL_BYTES, 'ZIP payload is too large');
      const name = bytes.subarray(cursor + 46, cursor + 46 + nameLength).toString('utf8');
      const safe = safeArchivePath(name);
      invariant(!names.has(safe.path), 'Duplicate ZIP path is prohibited');
      names.add(safe.path);
      const host = madeBy >>> 8;
      const mode = externalAttributes >>> 16;
      if (host === 3 && mode !== 0) {
        const type = mode & 0o170000;
        invariant(safe.directory ? type === 0o040000 : type === 0o100000, 'ZIP symlink or special file is prohibited');
      } else {
        const dosDirectory = (externalAttributes & 0x10) !== 0;
        invariant(dosDirectory === safe.directory, 'ZIP file type is ambiguous');
      }
      entries.push({
        ...safe,
        compressedSize,
        uncompressedSize,
        expectedCrc32,
        flags,
        method,
        localOffset,
        centralOffset,
      });
      cursor = end;
    }
    invariant(cursor === eocd, 'ZIP central directory has trailing data');
    return entries;
  }

  export function extractZip(zipPath, destination) {
    const zipStat = lstatSync(zipPath);
    invariant(zipStat.isFile() && !zipStat.isSymbolicLink() && realpathSync(zipPath) === resolve(zipPath), 'ZIP path is unsafe');
    invariant(!existsSync(destination), 'ZIP destination already exists');
    const bytes = readFileSync(zipPath);
    const entries = inspectZip(bytes);
    mkdirSync(destination, { recursive: false, mode: 0o700 });
    for (const entry of entries) {
      invariant(entry.localOffset + 30 <= entry.centralOffset, 'ZIP local header boundary is invalid');
      invariant(bytes.readUInt32LE(entry.localOffset) === 0x04034b50, 'ZIP local entry signature is invalid');
      const localFlags = bytes.readUInt16LE(entry.localOffset + 6);
      const localMethod = bytes.readUInt16LE(entry.localOffset + 8);
      const localCrc32 = bytes.readUInt32LE(entry.localOffset + 14);
      const localCompressedSize = bytes.readUInt32LE(entry.localOffset + 18);
      const localUncompressedSize = bytes.readUInt32LE(entry.localOffset + 22);
      const localNameLength = bytes.readUInt16LE(entry.localOffset + 26);
      const localExtraLength = bytes.readUInt16LE(entry.localOffset + 28);
      const localHeaderEnd = entry.localOffset + 30 + localNameLength + localExtraLength;
      invariant(localHeaderEnd <= entry.centralOffset, 'ZIP local metadata boundary is invalid');
      const localName = bytes.subarray(entry.localOffset + 30, entry.localOffset + 30 + localNameLength).toString('utf8');
      invariant(localName === (entry.directory ? entry.path + '/' : entry.path), 'ZIP local/central name mismatch');
      invariant(localFlags === entry.flags && localMethod === entry.method, 'ZIP local/central metadata mismatch');
      invariant(!containsZip64Extra(bytes, entry.localOffset + 30 + localNameLength, localExtraLength), 'ZIP64 local extra field is prohibited');
      if ((localFlags & 0x0008) === 0) {
        invariant(
          localCrc32 === entry.expectedCrc32
            && localCompressedSize === entry.compressedSize
            && localUncompressedSize === entry.uncompressedSize,
          'ZIP local/central size or CRC mismatch',
        );
      }
      const dataStart = localHeaderEnd;
      const dataEnd = dataStart + entry.compressedSize;
      invariant(dataEnd <= entry.centralOffset, 'ZIP compressed data boundary is invalid');
      const output = resolve(destination, entry.path);
      invariant(output.startsWith(resolve(destination) + '/'), 'ZIP output escapes destination');
      if (entry.directory) {
        mkdirSync(output, { recursive: true, mode: 0o700 });
        continue;
      }
      mkdirSync(dirname(output), { recursive: true, mode: 0o700 });
      const compressed = bytes.subarray(dataStart, dataEnd);
      const content = entry.method === 0
        ? Buffer.from(compressed)
        : inflateRawSync(compressed, { maxOutputLength: Math.max(1, entry.uncompressedSize) });
      invariant(content.length === entry.uncompressedSize, 'ZIP uncompressed size mismatch');
      invariant(crc32(content) === entry.expectedCrc32, 'ZIP CRC mismatch');
      writeFileSync(output, content, { flag: 'wx', mode: 0o600 });
    }
    return entries;
  }

  function assertClosedTree(root) {
    const canonicalRoot = realpathSync(root);
    const visit = (directory) => {
      for (const name of readdirSync(directory)) {
        const path = join(directory, name);
        const stat = lstatSync(path);
        invariant(!stat.isSymbolicLink(), 'Extracted symlink is prohibited');
        invariant(realpathSync(path).startsWith(canonicalRoot + '/') || realpathSync(path) === canonicalRoot, 'Extracted path escapes root');
        if (stat.isDirectory()) visit(path);
        else invariant(stat.isFile(), 'Extracted special file is prohibited');
      }
    };
    visit(canonicalRoot);
  }

  function validateMetadata(metadata, expectedSha, expectedNonce) {
    exactKeys(metadata, ['schemaVersion', 'repository', 'integrationBranch', 'workflowPath', 'evidenceNonce', 'run', 'jobs', 'artifacts', 'checks'], '$.metadata');
    invariant(metadata.schemaVersion === 1, 'Metadata schema mismatch');
    invariant(metadata.repository === 'Zakariagattouchi/dorzak', 'Repository mismatch');
    invariant(metadata.integrationBranch === 'main', 'Integration branch mismatch');
    invariant(metadata.workflowPath === '.github/workflows/p00-quality.yml', 'Workflow path mismatch');
    invariant(metadata.evidenceNonce === expectedNonce && SHA64.test(expectedNonce), 'Evidence nonce mismatch');
    exactKeys(metadata.run, ['id', 'attempt', 'event', 'status', 'conclusion', 'headSha', 'headBranch', 'path', 'displayTitle'], '$.metadata.run');
    invariant(Number.isSafeInteger(metadata.run.id) && metadata.run.id > 0, 'Run ID is invalid');
    invariant(metadata.run.attempt === 1 && metadata.run.event === 'workflow_dispatch', 'Run is not a new workflow dispatch');
    invariant(metadata.run.status === 'completed' && metadata.run.conclusion === 'success', 'Run did not pass');
    invariant(metadata.run.headSha === expectedSha && SHA40.test(expectedSha), 'Run SHA mismatch');
    invariant(metadata.run.headBranch === 'main', 'Run branch mismatch');
    invariant(metadata.run.path === '.github/workflows/p00-quality.yml', 'Run workflow path mismatch');
    invariant(metadata.run.displayTitle === 'p00-' + expectedNonce, 'Run title/nonce mismatch');

    invariant(Array.isArray(metadata.jobs) && metadata.jobs.length === 7, 'Provider job count mismatch');
    const jobNames = metadata.jobs.map((job) => job.name);
    invariant(JSON.stringify([...jobNames].sort()) === JSON.stringify([...ARTIFACTS].sort()), 'Provider job names mismatch');
    invariant(new Set(jobNames).size === jobNames.length, 'Provider jobs are duplicated');
    invariant(new Set(metadata.jobs.map((job) => job.id)).size === metadata.jobs.length, 'Provider job IDs are duplicated');
    for (const job of metadata.jobs) {
      exactKeys(job, ['id', 'name', 'status', 'conclusion', 'headSha', 'runAttempt', 'runnerName', 'labels'], '$.metadata.jobs[]');
      invariant(Number.isSafeInteger(job.id) && job.id > 0, 'Provider job ID is invalid');
      invariant(job.status === 'completed' && job.conclusion === 'success', 'Provider job did not pass: ' + job.name);
      invariant(job.headSha === expectedSha && job.runAttempt === 1, 'Provider job identity mismatch: ' + job.name);
      invariant(typeof job.runnerName === 'string' && job.runnerName.length > 0, 'Provider runner name is missing');
      invariant(Array.isArray(job.labels) && job.labels.includes('ubuntu-24.04'), 'Provider runner label mismatch');
    }

    invariant(Array.isArray(metadata.artifacts) && metadata.artifacts.length === 7, 'Provider artifact count mismatch');
    const artifactNames = metadata.artifacts.map((artifact) => artifact.name);
    invariant(JSON.stringify([...artifactNames].sort()) === JSON.stringify([...ARTIFACTS].sort()), 'Provider artifact names mismatch');
    invariant(new Set(artifactNames).size === artifactNames.length, 'Provider artifacts are duplicated');
    invariant(new Set(metadata.artifacts.map((artifact) => artifact.id)).size === metadata.artifacts.length, 'Provider artifact IDs are duplicated');
    for (const artifact of metadata.artifacts) {
      exactKeys(artifact, ['id', 'name', 'sizeInBytes', 'expired', 'digest', 'runId', 'headSha'], '$.metadata.artifacts[]');
      invariant(Number.isSafeInteger(artifact.id) && artifact.id > 0, 'Artifact ID is invalid');
      invariant(Number.isSafeInteger(artifact.sizeInBytes) && artifact.sizeInBytes > 0 && artifact.sizeInBytes <= MAX_TOTAL_BYTES, 'Artifact size is invalid');
      invariant(artifact.expired === false && /^sha256:[0-9a-f]{64}$/.test(artifact.digest), 'Artifact digest/expiry is invalid');
      invariant(artifact.runId === metadata.run.id && artifact.headSha === expectedSha, 'Artifact run identity mismatch');
    }

    invariant(Array.isArray(metadata.checks) && metadata.checks.length === 1, 'Required check is ambiguous');
    const check = metadata.checks[0];
    exactKeys(check, ['id', 'name', 'status', 'conclusion', 'headSha', 'appId', 'appSlug'], '$.metadata.checks[0]');
    invariant(Number.isSafeInteger(check.id) && check.id > 0 && Number.isSafeInteger(check.appId) && check.appId > 0, 'Required check identity is invalid');
    invariant(check.name === 'required-gates' && check.status === 'completed' && check.conclusion === 'success', 'Required check did not pass');
    invariant(check.headSha === expectedSha && check.appSlug === 'github-actions', 'Required check app/SHA mismatch');
  }

  function validatePostgresqlObservation(observation, aggregate) {
    exactKeys(observation, ['kind', 'identity', 'attestationSha256', 'instanceNonceSha256', 'endpointSha256', 'serverVersionNum', 'databaseName'], '$.postgresqlObservation');
    invariant(observation.kind === 'oci' && observation.identity === aggregate.inputs.postgresql.identity, 'PostgreSQL identity mismatch');
    invariant(/^docker\.io\/library\/postgres@sha256:[0-9a-f]{64}$/.test(observation.identity), 'PostgreSQL identity is mutable');
    invariant(SHA64.test(observation.attestationSha256) && SHA64.test(observation.instanceNonceSha256) && SHA64.test(observation.endpointSha256), 'PostgreSQL observation hash is invalid');
    invariant(Number.isInteger(observation.serverVersionNum) && observation.serverVersionNum >= 160000 && observation.serverVersionNum < 170000, 'PostgreSQL major is not 16');
    invariant(typeof observation.databaseName === 'string' && /_test$/.test(observation.databaseName), 'PostgreSQL database is not a test candidate');
    const reference = aggregate.jobs.find((job) => job.job === 'postgresql-16')?.artifacts?.postgresqlIdentity;
    invariant(reference?.sha256 === sha256(stableJson(observation)), 'PostgreSQL observation is not bound to its job artifact');
  }

  export function normalize({ metadataPath, zipDirectory, outputPath, expectedSha, expectedNonce, faultPoint = null }) {
    invariant([null, 'after-temp', 'after-rename'].includes(faultPoint), 'Unknown fault point');
    invariant(resolve(outputPath) === outputPath, 'Normalizer output path must be absolute');
    invariant(!existsSync(outputPath), 'Normalizer output already exists');
    const metadataStat = lstatSync(metadataPath);
    invariant(metadataStat.isFile() && !metadataStat.isSymbolicLink() && realpathSync(metadataPath) === resolve(metadataPath), 'Metadata path is unsafe');
    invariant(metadataStat.size > 0 && metadataStat.size <= MAX_METADATA_BYTES, 'Metadata file size is invalid');
    const metadata = readJson(metadataPath);
    validateMetadata(metadata, expectedSha, expectedNonce);
    const zipDirectoryStat = lstatSync(zipDirectory);
    invariant(zipDirectoryStat.isDirectory() && !zipDirectoryStat.isSymbolicLink(), 'ZIP directory is unsafe');
    const canonicalZipDirectory = realpathSync(zipDirectory);
    invariant(canonicalZipDirectory === resolve(zipDirectory), 'ZIP directory is unsafe');
    const zipNames = readdirSync(canonicalZipDirectory).sort();
    invariant(JSON.stringify(zipNames) === JSON.stringify(ARTIFACTS.map((name) => name + '.zip').sort()), 'ZIP directory is not closed');
    const parent = resolve(dirname(outputPath));
    mkdirSync(parent, { recursive: true });
    invariant(realpathSync(parent) === parent, 'Normalizer output parent is unsafe');
    const publication = mkdtempSync(join(parent, '.' + basename(outputPath) + '.tmp-'));
    let published = false;
    try {
      const extractedRoot = join(publication, 'artifacts');
      mkdirSync(extractedRoot, { mode: 0o700 });
      for (const artifact of metadata.artifacts) {
        const zipPath = join(canonicalZipDirectory, artifact.name + '.zip');
        const stat = lstatSync(zipPath);
        invariant(stat.isFile() && !stat.isSymbolicLink() && realpathSync(zipPath) === zipPath, 'Artifact ZIP is unsafe');
        invariant(stat.size === artifact.sizeInBytes, 'Provider artifact size mismatch: ' + artifact.name);
        invariant('sha256:' + createHash('sha256').update(readFileSync(zipPath)).digest('hex') === artifact.digest, 'Provider artifact digest mismatch: ' + artifact.name);
        extractZip(zipPath, join(extractedRoot, artifact.name));
        assertClosedTree(join(extractedRoot, artifact.name));
      }
      const jobsRoot = join(publication, 'jobs');
      mkdirSync(jobsRoot, { mode: 0o700 });
      for (const job of JOBS) renameSync(join(extractedRoot, job), join(jobsRoot, job));
      const requiredRoot = join(extractedRoot, 'required-gates');
      invariant(JSON.stringify(readdirSync(requiredRoot)) === JSON.stringify(['required-gates.json']), 'Required aggregate artifact is not closed');
      const aggregate = aggregateRequiredGates(jobsRoot);
      assertAggregate(aggregate, 'ci');
      invariant(readFileSync(join(requiredRoot, 'required-gates.json'), 'utf8') === stableJson(aggregate), 'Downloaded aggregate differs from recomputation');
      const observation = readJson(join(jobsRoot, 'postgresql-16', 'postgresql-identity.json'));
      validatePostgresqlObservation(observation, aggregate);
      const normalized = {
        schemaVersion: 1,
        provider: 'github-actions',
        runId: String(metadata.run.id),
        attempt: 1,
        integratedSha: expectedSha,
        contractSha256,
        inputFingerprintSha256: aggregate.inputFingerprintSha256,
        inputs: aggregate.inputs,
        platformObservationFingerprintSha256: aggregate.platformObservationFingerprintSha256,
        platformObservation: aggregate.platformObservation,
        postgresqlObservation: observation,
        requiredGate: { status: 'passed', jobs: 6 },
        jobs: aggregate.jobs,
      };
      const temporaryOutput = join(publication, basename(outputPath));
      writeFileSync(temporaryOutput, stableJson(normalized), { flag: 'wx', mode: 0o600 });
      if (faultPoint === 'after-temp') throw new Error('Injected normalizer fault after temp');
      renameSync(temporaryOutput, outputPath);
      published = true;
      if (faultPoint === 'after-rename') throw new Error('Injected normalizer fault after rename');
      return normalized;
    } finally {
      if (faultPoint === 'after-rename' && existsSync(outputPath)) rmSync(outputPath, { force: true });
      rmSync(publication, { recursive: true, force: true });
      if (!published && existsSync(outputPath)) rmSync(outputPath, { force: true });
    }
  }

  if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
    const [metadataPath, zipDirectory, outputPath, expectedSha, expectedNonce] = process.argv.slice(2);
    invariant(metadataPath && zipDirectory && outputPath && expectedSha && expectedNonce, 'usage: github-actions-normalize.mjs METADATA ZIP_DIR OUTPUT SHA NONCE');
    const value = normalize({
      metadataPath: resolve(metadataPath),
      zipDirectory: resolve(zipDirectory),
      outputPath: resolve(outputPath),
      expectedSha,
      expectedNonce,
    });
    process.stdout.write('P00_GITHUB_NORMALIZE PASS run=' + value.runId + ' sha=' + value.integratedSha + '\n');
  }
  ~~~
  <!-- TASK14_NORMALIZER_END -->

  `scripts/quality/github-actions-normalize.test.mjs`:

  <!-- TASK14_NORMALIZER_TEST_BEGIN -->
  ~~~js
  import assert from 'node:assert/strict';
  import {
    existsSync,
    mkdtempSync,
    mkdirSync,
    readFileSync,
    readdirSync,
    realpathSync,
    rmSync,
    writeFileSync,
  } from 'node:fs';
  import { tmpdir } from 'node:os';
  import { join } from 'node:path';
  import test from 'node:test';
  import {
    aggregateRequiredGates,
    contract,
    contractSha256,
    sha256,
    stableJson,
  } from './p00.mjs';
  import { crc32, inspectZip, normalize } from './github-actions-normalize.mjs';

  const sha40 = '0123456789abcdef0123456789abcdef01234567';
  const sha64a = 'a'.repeat(64);
  const sha64b = 'b'.repeat(64);
  const sha64c = 'c'.repeat(64);
  const nonce = 'd'.repeat(64);
  const identity = 'docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28';
  const jobs = contract.jobs.map((job) => job.name);
  const artifactNames = [...jobs, 'required-gates'];
  const temp = (name) => realpathSync(mkdtempSync(join(tmpdir(), 'dorzak-p00-gha-' + name + '-')));

  function storedZip(entries) {
    const locals = [];
    const centrals = [];
    let offset = 0;
    for (const entry of entries) {
      const name = Buffer.from(entry.name);
      const content = Buffer.from(entry.content);
      const crc = crc32(content);
      const local = Buffer.alloc(30);
      local.writeUInt32LE(0x04034b50, 0);
      local.writeUInt16LE(20, 4);
      local.writeUInt16LE(0x0800, 6);
      local.writeUInt16LE(0, 8);
      local.writeUInt32LE(crc, 14);
      local.writeUInt32LE(content.length, 18);
      local.writeUInt32LE(content.length, 22);
      local.writeUInt16LE(name.length, 26);
      const central = Buffer.alloc(46);
      central.writeUInt32LE(0x02014b50, 0);
      central.writeUInt16LE((3 << 8) | 20, 4);
      central.writeUInt16LE(20, 6);
      central.writeUInt16LE(0x0800, 8);
      central.writeUInt16LE(0, 10);
      central.writeUInt32LE(crc, 16);
      central.writeUInt32LE(content.length, 20);
      central.writeUInt32LE(content.length, 24);
      central.writeUInt16LE(name.length, 28);
      central.writeUInt32LE((((entry.mode ?? 0o100644) & 0xffff) * 0x10000) >>> 0, 38);
      central.writeUInt32LE(offset, 42);
      locals.push(local, name, content);
      centrals.push(central, name);
      offset += local.length + name.length + content.length;
    }
    const centralBytes = Buffer.concat(centrals);
    const end = Buffer.alloc(22);
    end.writeUInt32LE(0x06054b50, 0);
    end.writeUInt16LE(entries.length, 8);
    end.writeUInt16LE(entries.length, 10);
    end.writeUInt32LE(centralBytes.length, 12);
    end.writeUInt32LE(offset, 16);
    return Buffer.concat([...locals, centralBytes, end]);
  }

  function inputs() {
    return {
      contractSha256,
      runtime: { php: '8.5.6', composer: '2.9.8', node: '24.18.0', npm: '11.16.0' },
      lockfileSha256: { composer: sha64a, npm: sha64b },
      postgresql: { kind: 'oci', identity, policy: 'postgresql-16-test-closed-transport-v1' },
      playwright: { packageVersion: '1.58.0', chromiumRevision: '1234567' },
      bundleAlgorithms: { assetSelection: 'html-module-entry-and-modulepreload-v1', gzip: 'node-zlib-level-9' },
      runnerClasses: { local: 'local-macos-arm64', ci: 'github-hosted-ubuntu-24.04-x64' },
    };
  }

  function platform(index) {
    return {
      os: 'linux', arch: 'x64', osRelease: 'ephemeral-' + index,
      runnerRole: 'ci', runnerClass: 'github-hosted-ubuntu-24.04-x64',
      zlib: '1.3.1', chromiumExecutableSha256: sha64c,
    };
  }

  function rawArtifacts(job) {
    if (job.name === 'composer-validation') return {
      'dispatcher.tap': 'TAP version 13\nok 1 - list\nok 2 - invalid\n1..2\n',
      'p00-node.tap': 'TAP version 13\n' + Array.from({ length: 9 }, (_, index) => `ok ${index + 1} - node`).join('\n') + '\n1..9\n',
    };
    if (job.name === 'sqlite' || job.name === 'postgresql-16') return {
      [job.name + '.junit.xml']: `<testsuite tests="${job.testCount}" failures="0" errors="0" skipped="0"></testsuite>\n`,
    };
    if (job.name === 'frontend') return {
      'vitest.json': stableJson({ numTotalTests: 8, numFailedTests: 0, numPendingTests: 0 }),
      'bundle.json': stableJson({ fixture: 'bundle' }),
      'vite-build.log': 'accepted fixture warning\n',
    };
    if (job.name === 'playwright') return {
      'playwright.json': stableJson({ stats: { expected: 9, unexpected: 0, flaky: 0, skipped: 0 } }),
    };
    return {};
  }

  function fixture(name) {
    const root = temp(name);
    const jobRoot = join(root, 'jobs');
    const zipRoot = join(root, 'zips');
    mkdirSync(jobRoot);
    mkdirSync(zipRoot);
    const declaredInputs = inputs();
    const pgObservation = {
      kind: 'oci', identity, attestationSha256: sha64a,
      instanceNonceSha256: sha64b, endpointSha256: sha64c,
      serverVersionNum: 160014, databaseName: 'dorzak_fixture_test',
    };
    const records = contract.jobs.map((job, index) => {
      const directory = join(jobRoot, job.name);
      mkdirSync(directory);
      const raw = rawArtifacts(job);
      if (job.name === 'postgresql-16') raw['postgresql-identity.json'] = stableJson(pgObservation);
      for (const [path, content] of Object.entries(raw)) writeFileSync(join(directory, path), content);
      const mapping = {
        'composer-validation': { dispatcherTap: 'dispatcher.tap', p00NodeTap: 'p00-node.tap' },
        'php-style-static': {},
        sqlite: { junit: 'sqlite.junit.xml' },
        'postgresql-16': { junit: 'postgresql-16.junit.xml', postgresqlIdentity: 'postgresql-identity.json' },
        frontend: { vitest: 'vitest.json', bundle: 'bundle.json', viteBuildLog: 'vite-build.log' },
        playwright: { playwrightJson: 'playwright.json' },
      };
      const artifacts = Object.fromEntries(Object.entries(mapping[job.name]).map(([key, path]) => [
        key, { path, sha256: sha256(readFileSync(join(directory, path))) },
      ]));
      const log = 'log for ' + job.name + '\n';
      writeFileSync(join(directory, 'job.log'), log);
      const observation = platform(index);
      const record = {
        schemaVersion: 1,
        job: job.name,
        command: job.command,
        integratedSha: sha40,
        status: 'passed',
        exitCode: 0,
        retryAttempt: 1,
        testCount: job.testCount,
        failureCount: 0,
        unexplainedSkipCount: 0,
        durationMs: 10,
        contractSha256,
        inputFingerprintSha256: sha256(stableJson(declaredInputs)),
        inputs: declaredInputs,
        platformObservationFingerprintSha256: sha256(stableJson(observation)),
        platformObservation: observation,
        logSha256: sha256(log),
        artifacts,
      };
      writeFileSync(join(directory, 'result.json'), stableJson(record));
      return record;
    });
    const aggregate = aggregateRequiredGates(jobRoot);
    const zipBytes = {};
    for (const job of jobs) {
      const directory = join(jobRoot, job);
      zipBytes[job] = storedZip(readdirSync(directory).sort().map((path) => ({
        name: path, content: readFileSync(join(directory, path)),
      })));
    }
    zipBytes['required-gates'] = storedZip([{ name: 'required-gates.json', content: stableJson(aggregate) }]);
    const artifacts = artifactNames.map((artifact, index) => {
      const path = join(zipRoot, artifact + '.zip');
      writeFileSync(path, zipBytes[artifact]);
      return {
        id: 2000 + index,
        name: artifact,
        sizeInBytes: zipBytes[artifact].length,
        expired: false,
        digest: 'sha256:' + sha256(zipBytes[artifact]),
        runId: 1001,
        headSha: sha40,
      };
    });
    const metadata = {
      schemaVersion: 1,
      repository: 'Zakariagattouchi/dorzak',
      integrationBranch: 'main',
      workflowPath: '.github/workflows/p00-quality.yml',
      evidenceNonce: nonce,
      run: {
        id: 1001, attempt: 1, event: 'workflow_dispatch', status: 'completed', conclusion: 'success',
        headSha: sha40, headBranch: 'main', path: '.github/workflows/p00-quality.yml', displayTitle: 'p00-' + nonce,
      },
      jobs: artifactNames.map((job, index) => ({
        id: 3000 + index, name: job, status: 'completed', conclusion: 'success',
        headSha: sha40, runAttempt: 1, runnerName: 'GitHub Actions ' + index, labels: ['ubuntu-24.04'],
      })),
      artifacts,
      checks: [{
        id: 4001, name: 'required-gates', status: 'completed', conclusion: 'success',
        headSha: sha40, appId: 15368, appSlug: 'github-actions',
      }],
    };
    const metadataPath = join(root, 'metadata.json');
    writeFileSync(metadataPath, stableJson(metadata));
    return { root, jobRoot, zipRoot, metadataPath, metadata, records, aggregate };
  }

  function writeMetadata(value, path) {
    writeFileSync(path, stableJson(value));
  }

  test('normalizes one exact successful attempt-one run and preserves per-job observations', () => {
    const value = fixture('valid');
    const output = join(value.root, 'ci-run.json');
    const normalized = normalize({
      metadataPath: value.metadataPath,
      zipDirectory: value.zipRoot,
      outputPath: output,
      expectedSha: sha40,
      expectedNonce: nonce,
    });
    assert.equal(normalized.provider, 'github-actions');
    assert.equal(normalized.runId, '1001');
    assert.equal(normalized.jobs.length, 6);
    assert.equal(new Set(normalized.jobs.map((job) => job.platformObservation.osRelease)).size, 6);
    assert.deepEqual(JSON.parse(readFileSync(output, 'utf8')), normalized);
  });

  test('rejects retry, wrong event/SHA/nonce, failed or extra provider records, and wrong digest', () => {
    const mutations = [
      (value) => { value.run.attempt = 2; },
      (value) => { value.run.event = 'push'; },
      (value) => { value.run.headSha = 'f'.repeat(40); },
      (value) => { value.evidenceNonce = 'e'.repeat(64); },
      (value) => { value.jobs[0].conclusion = 'failure'; },
      (value) => { value.jobs.push(structuredClone(value.jobs[0])); },
      (value) => { value.jobs[1].id = value.jobs[0].id; },
      (value) => { value.artifacts[1].id = value.artifacts[0].id; },
      (value) => { value.artifacts[0].sizeInBytes += 1; },
      (value) => { value.artifacts[0].digest = 'sha256:' + 'f'.repeat(64); },
      (value) => { value.checks[0].appSlug = 'untrusted-app'; },
    ];
    for (const [index, mutate] of mutations.entries()) {
      const item = fixture('metadata-' + index);
      const changed = structuredClone(item.metadata);
      mutate(changed);
      writeMetadata(changed, item.metadataPath);
      assert.throws(() => normalize({
        metadataPath: item.metadataPath,
        zipDirectory: item.zipRoot,
        outputPath: join(item.root, 'rejected.json'),
        expectedSha: sha40,
        expectedNonce: nonce,
      }));
      assert.equal(existsSync(join(item.root, 'rejected.json')), false);
    }
  });

  test('rejects escape, duplicate, symlink, encryption, ZIP64, and oversized metadata', () => {
    assert.throws(() => inspectZip(storedZip([{ name: '../escape', content: 'x' }])));
    assert.throws(() => inspectZip(storedZip([{ name: 'same', content: 'a' }, { name: 'same', content: 'b' }])));
    assert.throws(() => inspectZip(storedZip([{ name: 'link', content: 'target', mode: 0o120777 }])));
    const encrypted = storedZip([{ name: 'file', content: 'x' }]);
    encrypted.writeUInt16LE(encrypted.readUInt16LE(6) | 1, 6);
    encrypted.writeUInt16LE(encrypted.readUInt16LE(encrypted.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02])) + 8) | 1,
      encrypted.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02])) + 8);
    assert.throws(() => inspectZip(encrypted));
    const zip64 = storedZip([{ name: 'file', content: 'x' }]);
    const end = zip64.length - 22;
    zip64.writeUInt16LE(0xffff, end + 10);
    assert.throws(() => inspectZip(zip64));
    const oversized = storedZip([{ name: 'file', content: 'x' }]);
    const central = oversized.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02]));
    oversized.writeUInt32LE(64 * 1024 * 1024 + 1, central + 24);
    assert.throws(() => inspectZip(oversized));
  });

  test('rejects changed reporter bytes and wrong runner class', () => {
    const reporter = fixture('reporter');
    writeFileSync(join(reporter.jobRoot, 'sqlite', 'sqlite.junit.xml'), '<testsuite tests="445" failures="0" errors="0" skipped="0"></testsuite>\n');
    const reporterZip = storedZip(readdirSync(join(reporter.jobRoot, 'sqlite')).sort().map((path) => ({
      name: path, content: readFileSync(join(reporter.jobRoot, 'sqlite', path)),
    })));
    writeFileSync(join(reporter.zipRoot, 'sqlite.zip'), reporterZip);
    reporter.metadata.artifacts.find((artifact) => artifact.name === 'sqlite').digest = 'sha256:' + sha256(reporterZip);
    reporter.metadata.artifacts.find((artifact) => artifact.name === 'sqlite').sizeInBytes = reporterZip.length;
    writeMetadata(reporter.metadata, reporter.metadataPath);
    assert.throws(() => normalize({
      metadataPath: reporter.metadataPath,
      zipDirectory: reporter.zipRoot,
      outputPath: join(reporter.root, 'rejected.json'),
      expectedSha: sha40,
      expectedNonce: nonce,
    }));

    const runner = fixture('runner');
    const resultPath = join(runner.jobRoot, 'playwright', 'result.json');
    const result = JSON.parse(readFileSync(resultPath, 'utf8'));
    result.platformObservation.runnerClass = 'wrong-runner';
    result.platformObservationFingerprintSha256 = sha256(stableJson(result.platformObservation));
    writeFileSync(resultPath, stableJson(result));
    const runnerZip = storedZip(readdirSync(join(runner.jobRoot, 'playwright')).sort().map((path) => ({
      name: path, content: readFileSync(join(runner.jobRoot, 'playwright', path)),
    })));
    writeFileSync(join(runner.zipRoot, 'playwright.zip'), runnerZip);
    runner.metadata.artifacts.find((artifact) => artifact.name === 'playwright').digest = 'sha256:' + sha256(runnerZip);
    runner.metadata.artifacts.find((artifact) => artifact.name === 'playwright').sizeInBytes = runnerZip.length;
    writeMetadata(runner.metadata, runner.metadataPath);
    assert.throws(() => normalize({
      metadataPath: runner.metadataPath,
      zipDirectory: runner.zipRoot,
      outputPath: join(runner.root, 'rejected.json'),
      expectedSha: sha40,
      expectedNonce: nonce,
    }));
  });

  test('cleans partial publication after injected faults', () => {
    for (const faultPoint of ['after-temp', 'after-rename']) {
      const value = fixture('fault-' + faultPoint);
      const output = join(value.root, 'ci-run.json');
      assert.throws(() => normalize({
        metadataPath: value.metadataPath,
        zipDirectory: value.zipRoot,
        outputPath: output,
        expectedSha: sha40,
        expectedNonce: nonce,
        faultPoint,
      }));
      assert.equal(existsSync(output), false);
      assert.equal(readdirSync(value.root).some((entry) => entry.startsWith('.ci-run.json.tmp-')), false);
      rmSync(value.root, { recursive: true, force: true });
    }
  });
  ~~~
  <!-- TASK14_NORMALIZER_TEST_END -->

- [ ] **Freeze but do not execute the provider-state command blocks.**

  Task 14 creates repository files only. The three marked command blocks below are executed later by Task 17 after the adapter commit, exact owner approval, and execution lease. They pin `github.com` and the supported GitHub REST API version `2026-03-10`, preserve private visibility, require an owner-authorized GitHub credential with the `workflow` scope, fail before any push if the account cannot expose private-repository protection and effective-rules readback APIs, verify the sole fetch and push URLs, use that exact GitHub CLI credential through a one-shot helper, push the exact integration SHA without force only from an unprotected bootstrap state, set/read back default branch `main`, require zero active ruleset rules, create or validate the exact classic policy, dispatch two distinct new attempt-one runs by unique nonce, poll exact run IDs, download exact artifact IDs, and normalize each run. If `main` is behind while classic protection or any active ruleset rule already exists, execution stops for a separately reviewed integration amendment; policy is never weakened. No rerun endpoint is present.

  Exact push and push-run block:

  <!-- TASK14_PUSH_AND_RUN_COMMANDS_BEGIN -->
  ~~~bash
  set -euo pipefail
  REPO='Zakariagattouchi/dorzak'
  WORKFLOW='.github/workflows/p00-quality.yml'
  API_VERSION='2026-03-10'
  INTEGRATED_SHA="${CODE_SHA:?CODE_SHA is required}"
  export GH_HOST=github.com
  test "$GH_HOST" = github.com
  test "$P00_REMOTE_NAME" = origin
  test "$P00_REMOTE_URL" = https://github.com/Zakariagattouchi/dorzak.git
  test "$P00_INTEGRATION_BRANCH" = main
  test "$P00_CI_PROVIDER" = github-actions
  printf '%s\n' "$INTEGRATED_SHA" | rg -x '[0-9a-f]{40}'
  test "$(git rev-parse HEAD)" = "$INTEGRATED_SHA"
  test -z "$(git status --short --untracked-files=normal)"
  auth_status="$(gh auth status --active --hostname github.com 2>&1)"
  printf '%s\n' "$auth_status" | rg -q "Token scopes:.*'workflow'"
  repository="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO")"
  test "$(printf '%s' "$repository" | jq -r .full_name)" = "$REPO"
  test "$(printf '%s' "$repository" | jq -r .visibility)" = private
  test "$(printf '%s' "$repository" | jq -r .private)" = true
  test "$(printf '%s' "$repository" | jq -r '.permissions.admin // false')" = true
  test "$(printf '%s' "$repository" | jq -r '.permissions.push // false')" = true
  git_gh() {
    git -c credential.helper= -c credential.helper='!gh auth git-credential' "$@"
  }
  mkdir -p .artifacts/p00/github
  chmod 700 .artifacts/p00/github
  if git remote get-url origin >/dev/null 2>&1; then
    :
  else
    git remote add origin "$P00_REMOTE_URL"
  fi
  test "$(git remote get-url --all origin | wc -l | tr -d ' ')" = 1
  test "$(git remote get-url --all origin)" = "$P00_REMOTE_URL"
  test "$(git remote get-url --push --all origin | wc -l | tr -d ' ')" = 1
  test "$(git remote get-url --push --all origin)" = "$P00_REMOTE_URL"
  remote_lines="$(git_gh ls-remote "$P00_REMOTE_URL" refs/heads/main)"
  test "$(printf '%s\n' "$remote_lines" | sed '/^$/d' | wc -l | tr -d ' ')" -le 1
  remote_before="$(printf '%s\n' "$remote_lines" | awk 'NR==1 {print $1}')"
  if test -n "$remote_before"; then
    printf '%s\n' "$remote_before" | rg -x '[0-9a-f]{40}'
    git_gh fetch --no-tags "$P00_REMOTE_URL" "$remote_before"
    git merge-base --is-ancestor "$remote_before" "$INTEGRATED_SHA"
  fi
  gh api --paginate --slurp -H "X-GitHub-Api-Version: $API_VERSION" \
    "repos/$REPO/rules/branches/main?per_page=100" \
    | jq 'add' > .artifacts/p00/github/effective-rules-before.json
  protection_path="repos/$REPO/branches/main/protection"
  protection_before=.artifacts/p00/github/protection-before-push.json
  protection_error=.artifacts/p00/github/protection-before-push.err
  if gh api -H "X-GitHub-Api-Version: $API_VERSION" "$protection_path" \
    >"$protection_before" 2>"$protection_error"; then
    protection_present=true
  else
    rg -q 'HTTP 404' "$protection_error"
    protection_present=false
  fi
  PUSH_MARKER=''
  if test "$remote_before" != "$INTEGRATED_SHA"; then
    test "$protection_present" = false
    test "$(jq 'length' .artifacts/p00/github/effective-rules-before.json)" = 0
    PUSH_MARKER="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
    git_gh push --porcelain "$P00_REMOTE_URL" "$INTEGRATED_SHA:refs/heads/main"
  fi
  test "$(git_gh ls-remote "$P00_REMOTE_URL" refs/heads/main | awk 'NR==1 {print $1}')" = "$INTEGRATED_SHA"
  gh api --method PATCH -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO" -f default_branch=main >/dev/null
  test "$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO" --jq .default_branch)" = main
  PUSH_RUN_ID=''
  if test -n "$PUSH_MARKER"; then
    for _ in $(seq 1 120); do
      runs="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
        "repos/$REPO/actions/workflows/p00-quality.yml/runs?branch=main&event=push&per_page=100")"
      matches="$(printf '%s' "$runs" | jq -c --arg sha "$INTEGRATED_SHA" --arg marker "$PUSH_MARKER" \
        '[.workflow_runs[] | select(.head_sha==$sha and .head_branch=="main" and .event=="push" and .run_attempt==1 and .created_at >= $marker)]')"
      count="$(printf '%s' "$matches" | jq 'length')"
      test "$count" -le 1
      if test "$count" = 1; then PUSH_RUN_ID="$(printf '%s' "$matches" | jq -r '.[0].id')"; break; fi
      sleep 5
    done
  else
    runs="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
      "repos/$REPO/actions/workflows/p00-quality.yml/runs?branch=main&event=push&per_page=100")"
    matches="$(printf '%s' "$runs" | jq -c --arg sha "$INTEGRATED_SHA" \
      '[.workflow_runs[] | select(.head_sha==$sha and .head_branch=="main" and .event=="push" and .run_attempt==1)] | sort_by(.created_at,.id) | reverse')"
    count="$(printf '%s' "$matches" | jq 'length')"
    test "$count" -ge 1
    PUSH_RUN_ID="$(printf '%s' "$matches" | jq -r '.[0].id')"
  fi
  printf '%s\n' "$PUSH_RUN_ID" | rg -x '[1-9][0-9]*'
  for _ in $(seq 1 180); do
    run="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/runs/$PUSH_RUN_ID")"
    status="$(printf '%s' "$run" | jq -r .status)"
    test "$(printf '%s' "$run" | jq -r .head_sha)" = "$INTEGRATED_SHA"
    test "$(printf '%s' "$run" | jq -r .head_branch)" = main
    test "$(printf '%s' "$run" | jq -r .event)" = push
    test "$(printf '%s' "$run" | jq -r .path)" = "$WORKFLOW"
    test "$(printf '%s' "$run" | jq -r .run_attempt)" = 1
    if test "$status" = completed; then test "$(printf '%s' "$run" | jq -r .conclusion)" = success; break; fi
    sleep 10
  done
  test "$status" = completed
  printf '%s' "$run" | jq -S '{schemaVersion:1,runId:.id,headSha:.head_sha,event,attempt:.run_attempt,status,conclusion}' \
    > .artifacts/p00/github/push-run.json
  ~~~
  <!-- TASK14_PUSH_AND_RUN_COMMANDS_END -->

  Exact required-status configuration/read-back block:

  <!-- TASK14_REQUIRED_STATUS_COMMANDS_BEGIN -->
  ~~~bash
  set -euo pipefail
  REPO='Zakariagattouchi/dorzak'
  API_VERSION='2026-03-10'
  INTEGRATED_SHA="${CODE_SHA:?CODE_SHA is required}"
  export GH_HOST=github.com
  test "$GH_HOST" = github.com
  repository="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO")"
  test "$(printf '%s' "$repository" | jq -r .visibility)" = private
  test "$(printf '%s' "$repository" | jq -r '.permissions.admin // false')" = true
  test "$(jq 'length' .artifacts/p00/github/effective-rules-before.json)" = 0
  PUSH_RUN_ID="$(jq -er '.runId | select(type=="number" and .>0)' .artifacts/p00/github/push-run.json)"
  jobs="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
    "repos/$REPO/actions/runs/$PUSH_RUN_ID/jobs?filter=latest&per_page=100")"
  required_job="$(printf '%s' "$jobs" | jq -c '[.jobs[] | select(.name=="required-gates" and .status=="completed" and .conclusion=="success")]')"
  test "$(printf '%s' "$required_job" | jq 'length')" = 1
  REQUIRED_JOB_ID="$(printf '%s' "$required_job" | jq -r '.[0].id')"
  checks="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
    -H 'Accept: application/vnd.github+json' "repos/$REPO/commits/$INTEGRATED_SHA/check-runs?filter=latest&per_page=100")"
  required_check="$(printf '%s' "$checks" | jq -c --arg run "$PUSH_RUN_ID" --arg job "$REQUIRED_JOB_ID" \
    '[.check_runs[] | select(.name=="required-gates" and .app.slug=="github-actions" and .status=="completed" and .conclusion=="success" and (.details_url | contains("/actions/runs/"+$run+"/job/"+$job)))]')"
  test "$(printf '%s' "$required_check" | jq 'length')" = 1
  ACTIONS_APP_ID="$(printf '%s' "$required_check" | jq -r '.[0].app.id')"
  printf '%s\n' "$ACTIONS_APP_ID" | rg -x '[1-9][0-9]*'
  protection_path="repos/$REPO/branches/main/protection"
  current=.artifacts/p00/github/protection-before.json
  error=.artifacts/p00/github/protection-before.err
  if gh api -H "X-GitHub-Api-Version: $API_VERSION" "$protection_path" >"$current" 2>"$error"; then
    jq -e --argjson app "$ACTIONS_APP_ID" '
      .required_status_checks.strict==true
      and (.required_status_checks.checks|length)==1
      and .required_status_checks.checks[0].context=="required-gates"
      and .required_status_checks.checks[0].app_id==$app
      and .enforce_admins.enabled==true
      and .required_pull_request_reviews==null
      and .restrictions==null
      and .required_linear_history.enabled==true
      and .allow_force_pushes.enabled==false
      and .allow_deletions.enabled==false
      and .block_creations.enabled==false
      and .required_conversation_resolution.enabled==true
      and .lock_branch.enabled==false
      and .allow_fork_syncing.enabled==false
    ' "$current"
  else
    rg -q 'HTTP 404' "$error"
    jq -n --argjson app "$ACTIONS_APP_ID" '{
      required_status_checks:{strict:true,checks:[{context:"required-gates",app_id:$app}]},
      enforce_admins:true,
      required_pull_request_reviews:null,
      restrictions:null,
      required_linear_history:true,
      allow_force_pushes:false,
      allow_deletions:false,
      block_creations:false,
      required_conversation_resolution:true,
      lock_branch:false,
      allow_fork_syncing:false
    }' | gh api --method PUT -H "X-GitHub-Api-Version: $API_VERSION" "$protection_path" --input - >/dev/null
  fi
  gh api -H "X-GitHub-Api-Version: $API_VERSION" "$protection_path" > .artifacts/p00/github/protection-after.json
  jq -e --argjson app "$ACTIONS_APP_ID" '
    .required_status_checks.strict==true
    and (.required_status_checks.checks|length)==1
    and .required_status_checks.checks[0].context=="required-gates"
    and .required_status_checks.checks[0].app_id==$app
    and .enforce_admins.enabled==true
    and .required_pull_request_reviews==null
    and .restrictions==null
    and .required_linear_history.enabled==true
    and .allow_force_pushes.enabled==false
    and .allow_deletions.enabled==false
    and .block_creations.enabled==false
    and .required_conversation_resolution.enabled==true
    and .lock_branch.enabled==false
    and .allow_fork_syncing.enabled==false
  ' .artifacts/p00/github/protection-after.json
  gh api --paginate --slurp -H "X-GitHub-Api-Version: $API_VERSION" \
    "repos/$REPO/rules/branches/main?per_page=100" \
    | jq 'add' > .artifacts/p00/github/effective-rules-after.json
  test "$(jq 'length' .artifacts/p00/github/effective-rules-after.json)" = 0
  ~~~
  <!-- TASK14_REQUIRED_STATUS_COMMANDS_END -->

  Exact two-new-run, metadata, ZIP-download, and normalization block:

  <!-- TASK14_TWO_RUN_COMMANDS_BEGIN -->
  ~~~bash
  set -euo pipefail
  REPO='Zakariagattouchi/dorzak'
  WORKFLOW='.github/workflows/p00-quality.yml'
  API_VERSION='2026-03-10'
  INTEGRATED_SHA="${CODE_SHA:?CODE_SHA is required}"
  export GH_HOST=github.com
  test "$GH_HOST" = github.com
  repository="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO")"
  test "$(printf '%s' "$repository" | jq -r .visibility)" = private
  test "$(printf '%s' "$repository" | jq -r '.permissions.push // false')" = true
  git_gh() {
    git -c credential.helper= -c credential.helper='!gh auth git-credential' "$@"
  }
  test "$(printf '%s' "$repository" | jq -r .default_branch)" = main
  workflow="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/workflows/p00-quality.yml")"
  test "$(printf '%s' "$workflow" | jq -r .path)" = "$WORKFLOW"
  test "$(printf '%s' "$workflow" | jq -r .state)" = active
  test "$(git remote get-url --push --all origin | wc -l | tr -d ' ')" = 1
  test "$(git remote get-url --push --all origin)" = "$P00_REMOTE_URL"
  test "$(git_gh ls-remote "$P00_REMOTE_URL" refs/heads/main | awk 'NR==1 {print $1}')" = "$INTEGRATED_SHA"
  test "$(jq 'length' .artifacts/p00/github/effective-rules-after.json)" = 0
  mkdir -p .artifacts/p00/ci
  chmod 700 .artifacts/p00/ci
  NONCE_1="$(openssl rand -hex 32)"
  NONCE_2="$(openssl rand -hex 32)"
  test "$NONCE_1" != "$NONCE_2"
  for nonce in "$NONCE_1" "$NONCE_2"; do
    existing="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
      "repos/$REPO/actions/workflows/p00-quality.yml/runs?branch=main&event=workflow_dispatch&per_page=100" \
      --jq ".workflow_runs | map(select(.display_title==\"p00-$nonce\")) | length")"
    test "$existing" = 0
    jq -n --arg sha "$INTEGRATED_SHA" --arg nonce "$nonce" \
      '{ref:"main",inputs:{integrated_sha:$sha,evidence_nonce:$nonce}}' \
      | gh api --method POST -H "X-GitHub-Api-Version: $API_VERSION" \
          "repos/$REPO/actions/workflows/p00-quality.yml/dispatches" --input - >/dev/null
  done
  find_run() {
    local nonce="$1" matches count
    for _ in $(seq 1 120); do
      matches="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" \
        "repos/$REPO/actions/workflows/p00-quality.yml/runs?branch=main&event=workflow_dispatch&per_page=100" \
        | jq -c --arg title "p00-$nonce" --arg sha "$INTEGRATED_SHA" \
          '[.workflow_runs[] | select(.display_title==$title and .head_sha==$sha and .event=="workflow_dispatch" and .run_attempt==1)]')"
      count="$(printf '%s' "$matches" | jq 'length')"
      test "$count" -le 1
      if test "$count" = 1; then printf '%s' "$matches" | jq -r '.[0].id'; return 0; fi
      sleep 5
    done
    return 2
  }
  RUN_1="$(find_run "$NONCE_1")"
  RUN_2="$(find_run "$NONCE_2")"
  test "$RUN_1" != "$RUN_2"
  for run_id in "$RUN_1" "$RUN_2"; do
    for _ in $(seq 1 180); do
      run="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/runs/$run_id")"
      test "$(printf '%s' "$run" | jq -r .head_sha)" = "$INTEGRATED_SHA"
      test "$(printf '%s' "$run" | jq -r .head_branch)" = main
      test "$(printf '%s' "$run" | jq -r .event)" = workflow_dispatch
      test "$(printf '%s' "$run" | jq -r .run_attempt)" = 1
      status="$(printf '%s' "$run" | jq -r .status)"
      if test "$status" = completed; then test "$(printf '%s' "$run" | jq -r .conclusion)" = success; break; fi
      sleep 10
    done
    test "$status" = completed
  done
  collect_run() {
    local ordinal="$1" run_id="$2" nonce="$3" raw run_json jobs_json artifacts_json checks_json
    raw=".artifacts/p00/ci/raw-$ordinal"
    mkdir "$raw" "$raw/zips"
    run_json="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/runs/$run_id")"
    jobs_json="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/runs/$run_id/jobs?filter=latest&per_page=100")"
    artifacts_json="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/runs/$run_id/artifacts?per_page=100")"
    checks_json="$(gh api -H "X-GitHub-Api-Version: $API_VERSION" -H 'Accept: application/vnd.github+json' \
      "repos/$REPO/commits/$INTEGRATED_SHA/check-runs?filter=all&per_page=100")"
    jq -n --arg repo "$REPO" --arg nonce "$nonce" --argjson run "$run_json" --argjson jobs "$jobs_json" \
      --argjson artifacts "$artifacts_json" --argjson checks "$checks_json" --arg run_id "$run_id" '{
        schemaVersion:1,
        repository:$repo,
        integrationBranch:"main",
        workflowPath:".github/workflows/p00-quality.yml",
        evidenceNonce:$nonce,
        run:{id:$run.id,attempt:$run.run_attempt,event:$run.event,status:$run.status,conclusion:$run.conclusion,headSha:$run.head_sha,headBranch:$run.head_branch,path:$run.path,displayTitle:$run.display_title},
        jobs:($jobs.jobs | map({id,name,status,conclusion,headSha:.head_sha,runAttempt:.run_attempt,runnerName:.runner_name,labels}) | sort_by(.name)),
        artifacts:($artifacts.artifacts | map({id,name,sizeInBytes:.size_in_bytes,expired,digest,runId:.workflow_run.id,headSha:.workflow_run.head_sha}) | sort_by(.name)),
        checks:($checks.check_runs | map(select(.name=="required-gates" and .app.slug=="github-actions" and (.details_url|contains("/actions/runs/"+$run_id+"/")))) | map({id,name,status,conclusion,headSha:.head_sha,appId:.app.id,appSlug:.app.slug}))
      }' > "$raw/metadata.json"
    test "$(jq '.artifacts|length' "$raw/metadata.json")" = 7
    while IFS=$'\t' read -r artifact_id artifact_name digest; do
      printf '%s\n' "$artifact_name" | rg -x 'composer-validation|php-style-static|sqlite|postgresql-16|frontend|playwright|required-gates'
      gh api -H "X-GitHub-Api-Version: $API_VERSION" "repos/$REPO/actions/artifacts/$artifact_id/zip" > "$raw/zips/$artifact_name.zip"
      test "sha256:$(shasum -a 256 "$raw/zips/$artifact_name.zip" | awk '{print $1}')" = "$digest"
    done < <(jq -r '.artifacts[] | [.id,.name,.digest] | @tsv' "$raw/metadata.json")
    node scripts/quality/github-actions-normalize.mjs \
      "$raw/metadata.json" "$raw/zips" ".artifacts/p00/ci/ci-run-$ordinal.json" "$INTEGRATED_SHA" "$nonce"
  }
  collect_run 1 "$RUN_1" "$NONCE_1"
  collect_run 2 "$RUN_2" "$NONCE_2"
  test "$(jq -r .runId .artifacts/p00/ci/ci-run-1.json)" != "$(jq -r .runId .artifacts/p00/ci/ci-run-2.json)"
  test "$(jq -r .attempt .artifacts/p00/ci/ci-run-1.json)" = 1
  test "$(jq -r .attempt .artifacts/p00/ci/ci-run-2.json)" = 1
  ~~~
  <!-- TASK14_TWO_RUN_COMMANDS_END -->

- [ ] **Run Task 14 verification and commit exactly five files.**

  ```bash
  chmod +x scripts/quality/github-actions-postgres-service \
    scripts/quality/test-github-actions-postgres-service.sh
  bash scripts/quality/test-github-actions-postgres-service.sh
  node --test scripts/quality/github-actions-normalize.test.mjs
  node --check scripts/quality/github-actions-normalize.mjs
  npx prettier --check .github/workflows/p00-quality.yml
  rg -n 'runs-on: ubuntu-24.04|P00_RUNNER_ROLE: ci|P00_RUNNER_CLASS: github-hosted-ubuntu-24.04-x64|required-gates' .github/workflows/p00-quality.yml
  test -z "$(rg -n 'uses: [^#[:space:]]+@(v[0-9]+|main|master|[0-9]+\.[0-9]+)' .github/workflows/p00-quality.yml || true)"
  test -z "$(rg -n 'pull_request_target|continue-on-error|retry|^[[:space:]]+cache:' .github/workflows/p00-quality.yml || true)"
  test "$(rg -c '^  (composer-validation|php-style-static|sqlite|postgresql-16|frontend|playwright|required-gates):$' .github/workflows/p00-quality.yml)" = 7
  test "$(rg -c 'docker pull .*laravel-test-runner@sha256:571f0ea5098c32a534eeca499708d4edeb2f8874a76c32ef546a523a311660c6' .github/workflows/p00-quality.yml)" = 6
  test "$(rg -c 'getcomposer.org/download/2.9.8/composer.phar' .github/workflows/p00-quality.yml)" = 6
  test -z "$(rg -n 'setup-php|laravel-test-runner:' .github/workflows/p00-quality.yml || true)"
  git diff --check
  git add -- .github/workflows/p00-quality.yml \
    scripts/quality/github-actions-postgres-service \
    scripts/quality/test-github-actions-postgres-service.sh \
    scripts/quality/github-actions-normalize.mjs \
    scripts/quality/github-actions-normalize.test.mjs
  expected_paths="$(printf '%s\n' \
    .github/workflows/p00-quality.yml \
    scripts/quality/github-actions-normalize.mjs \
    scripts/quality/github-actions-normalize.test.mjs \
    scripts/quality/github-actions-postgres-service \
    scripts/quality/test-github-actions-postgres-service.sh | LC_ALL=C sort)"
  test "$(git diff --cached --name-only | LC_ALL=C sort)" = "$expected_paths"
  git diff --cached --check
  git commit -m "ci: add immutable P00 GitHub gates"
  ```

  Expected: both test suites pass, the workflow contains exactly seven jobs and only full-SHA action references, the staged allowlist is exact, and one focused commit is created. Stop after the commit and rerun every writer-boundary and Task 0 check. Tasks 15–17 remain blocked until the exact implementation commit is independently reviewed and the later execution gate authorizes provider state.

### Task 15: Record repository context and the seven initial architecture decisions

**Files:**
- Create: `CONTEXT.md`
- Create: `docs/adr/0001-system-of-record-authority.md`
- Create: `docs/adr/0002-organization-location-and-isolated-erpnext-tenancy.md`
- Create: `docs/adr/0003-modular-monolith-and-external-adapters.md`
- Create: `docs/adr/0004-one-complete-public-launch.md`
- Create: `docs/adr/0005-immutable-plan-publication.md`
- Create: `docs/adr/0006-commerce-cutover-and-no-dual-write.md`
- Create: `docs/adr/0007-frontend-surface-boundaries.md`

**Interfaces:** `CONTEXT.md` is the repository entry point for domain language and authority. Every ADR has exactly `Status`, `Context`, `Decision`, `Consequences`, `Verification`, and `References` headings. These records document approved future boundaries; they do not implement P01–P19.

- [ ] **Prove the architecture records are absent.**

  ```bash
  test -f CONTEXT.md
  test "$( { rg --files docs/adr 2>/dev/null || true; } \
    | rg -c '^docs/adr/[0-9]{4}-[^/]+\.md$' || true)" = 7
  ```

  Expected: both checks fail at the approved baseline.

- [ ] **Create `CONTEXT.md` with this complete structure and content.**

  ```markdown
  # Dorzak Context

  ## Product and release boundary

  Dorzak is one branded multi-vertical business operating platform. It has one complete public launch gate; internal packets are not partial public releases. P00 stabilizes the recovered React/Laravel starting point and does not advertise later roadmap capability.

  ## Current P00 system

  The merchant management surface is React 18, TypeScript and Vite. Laravel 13 is the current modular monolith and API. SQLite is fast feedback; PostgreSQL 16 is qualification. The public media contract is origin-relative `/storage/<disk-relative-key>`. Canonical demo and browser commerce use Qatar/QAR.

  ## Target authority

  Dorzak owns identity, plans and immutable entitlements, experience, orchestration, public content, vertical-native domains, consent and governed support. ERPNext is the operational and financial core for every paid organization. Each paid organization has one isolated Frappe site/data boundary; one or many locations belong to that organization and Enterprise has no location minimum. Each field and business fact has one writer.

  ## Bounded contexts

  - Execution context resolves actor, organization, authorized location, plan version, country pack and correlation ID and fails closed.
  - The current Vite/React app is the merchant desktop, POS and builder-editor surface; it is not the P17 Superadmin target.
  - The internal Superadmin target is a separate Frappe-native site/database using Frappe Desk with minimal Dorzak branding and only a versioned approved application manifest.
  - Superadmin cross-merchant visibility and intervention use tenant-bound Dorzak gateway and `dorzak_core` APIs plus explicit reason-bound, time-limited, audited grants. The internal site never queries merchant databases directly, shares merchant credentials, or stores uncontrolled raw merchant records.
  - Laravel owns Dorzak platform authority and native-domain rules and exposes governed Dorzak DTO/API contracts.
  - P17 remains Not started and Not authorized; P00 records this boundary only.
  - ERPNext owns paid operational/financial records after their approved cutovers.
  - Payments, storage, messaging and future ERP commands sit behind narrow versioned adapters.
  - The public/customer surface is a separate server-rendered React deployment; its final framework waits for the measured P05 decision.

  ## Invariants

  - No dual-write stock, invoice, payment, customer-account, plan or workflow truth.
  - No database transaction remains open across ERPNext/provider HTTP.
  - UI consumes Dorzak DTOs and never raw provider/ERPNext shapes.
  - Tenant/location/plan authority is server-side and never inferred from a request body.
  - Publication and plan versions are immutable after activation.
  - P00 uses zero browser retries, explicit destructive-database guards and evidence tied to exact SHAs.

  ## Decision index

  ADRs 0001–0007 record authority, tenancy, modularity, launch policy, immutable plans, cutover and frontend surface boundaries. The approved product baseline and technical roadmap remain the higher-level sources when a later plan conflicts.
  ```

- [ ] **Create all seven ADRs with exact decisions.**

  Run this complete deterministic generator from the repository root:

  ~~~js
  node --input-type=module <<'NODE'
  import { mkdirSync, writeFileSync } from 'node:fs';

  const references = [
    '- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)',
    '- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)',
    '- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)',
  ];
  const records = [
    {
      id: '0001',
      slug: 'system-of-record-authority',
      title: 'System of record authority',
      context: 'The recovered Laravel commerce baseline and the target ERPNext operating core need an explicit, non-overlapping authority boundary.',
      decision: 'Dorzak owns identity, plans, experience, orchestration, public content, vertical-native truth and governed support. ERPNext owns paid operational/financial facts after approved cutover. A field/fact has one authority. P00 keeps current Laravel commerce only as the pre-cutover recovered baseline.',
      benefit: 'Each business fact has one accountable writer before and after cutover.',
      cost: 'Later packets must map and reconcile every field before changing authority.',
      verification: 'The P01 execution-context gate and P04 ERP commerce cutover gate verify this boundary. P00 performs documentation only.',
    },
    {
      id: '0002',
      slug: 'organization-location-and-isolated-erpnext-tenancy',
      title: 'Organization, location, and isolated ERPNext tenancy',
      context: 'Paid tenancy must separate organization authority from location cardinality without creating shared financial data boundaries.',
      decision: 'One paid Organization maps to one isolated Frappe site/database boundary. A site may serve one or many Locations of that Organization. Enterprise never requires a minimum Location count. Organization/Location migration begins in P01/P02, not P00.',
      benefit: 'Financial and operational isolation is explicit for every paid organization.',
      cost: 'Provisioning and migrations must operate one isolated site boundary per organization.',
      verification: 'The P01 organization-context and P02 location-qualification gates verify this model. P00 performs documentation only.',
    },
    {
      id: '0003',
      slug: 'modular-monolith-and-external-adapters',
      title: 'Modular monolith and external adapters',
      context: 'The product needs clear module boundaries while keeping deployment and transaction ownership understandable.',
      decision: 'Dorzak remains a Laravel modular monolith plus external systems behind narrow versioned interfaces. ERPNext, payment, storage and messaging credentials/shapes never reach UI/domain modules. A local transaction never spans a remote call.',
      benefit: 'Domain code depends on stable Dorzak contracts instead of provider shapes.',
      cost: 'Every integration requires an owned adapter and explicit failure/reconciliation path.',
      verification: 'The P04 adapter and commerce-cutover gates verify these boundaries. P00 performs documentation only.',
    },
    {
      id: '0004',
      slug: 'one-complete-public-launch',
      title: 'One complete public launch',
      context: 'Internal packet completion must not be mistaken for a partially marketable public product.',
      decision: 'Dorzak has one complete public launch. P00–P19 are internal verified milestones. No tier/category is publicly sold before every advertised journey passes the global gate.',
      benefit: 'Public claims remain aligned with complete verified customer journeys.',
      cost: 'Individual packet completion cannot independently trigger a public release.',
      verification: 'The complete-launch global gate verifies all advertised journeys. P00 performs documentation only.',
    },
    {
      id: '0005',
      slug: 'immutable-plan-publication',
      title: 'Immutable plan publication',
      context: 'Commercial claims and runtime authorization must not drift after a plan version becomes active.',
      decision: 'Published plan versions and entitlement matrices are immutable. A new commercial change creates a new version and explicit transition; runtime/server/worker/ERP enforcement and public claims resolve the same version. P03 owns implementation.',
      benefit: 'One version explains both customer claims and enforced entitlement behavior.',
      cost: 'Commercial changes require version creation and an explicit migration transition.',
      verification: 'The P03 entitlement-publication gate verifies immutability and cross-surface parity. P00 performs documentation only.',
    },
    {
      id: '0006',
      slug: 'commerce-cutover-and-no-dual-write',
      title: 'Commerce cutover and no dual write',
      context: 'Moving commerce authority to ERPNext must preserve reconciliation without leaving two writers active.',
      decision: 'Every commerce domain uses an explicit expand/backfill/parity/cutover/contract sequence. At cutover the new authority becomes the sole writer; rollback uses recorded reconciliation and never long-lived dual writes. P04 owns ERP commerce migration.',
      benefit: 'Cutover has measurable parity and one authoritative writer.',
      cost: 'Each commerce domain needs a staged migration and recorded rollback reconciliation.',
      verification: 'The P04 parity, cutover and contract gates verify the sequence. P00 performs documentation only.',
    },
    {
      id: '0007',
      slug: 'frontend-surface-boundaries',
      title: 'Frontend and internal Superadmin surface boundaries',
      context: 'Merchant administration, public/customer rendering and Dorzak-internal Superadmin operations have different deployment, branding, isolation and authority needs.',
      decision: 'The current Vite/React app remains the P00 merchant-management surface. The public/customer surface is a separate server-rendered React deployment; Next.js remains only a preferred candidate pending the measured P05 spike and ADR. The internal Superadmin target is a separate Frappe-native site/database using Frappe Desk with minimal Dorzak branding. It uses only governed tenant-bound Dorzak gateway and dorzak_core APIs plus explicit audited grants for merchant visibility or intervention; it never queries merchant databases directly, shares merchant credentials, or stores uncontrolled raw merchant records. Its versioned application manifest is selected only by a later approved P17 design. P17 remains Not started and Not authorized; P00 records this boundary only.',
      benefit: 'Merchant, public/customer and internal operational surfaces remain explicit while merchant data and authority stay isolated.',
      cost: 'A later separately authorized P17 design must select the exact application manifest and prove its API, grant and isolation boundaries.',
      verification: 'The measured P05 spike verifies the public framework. Separately approved P17 design, plan and isolation/source-of-truth/grant tests verify Superadmin. P00 performs documentation only and grants no P17 authorization.',
    },
  ];

  mkdirSync('docs/adr', { recursive: true });
  for (const record of records) {
    const document = [
      '# ADR ' + record.id + ': ' + record.title,
      '',
      '## Status',
      '',
      'Accepted',
      '',
      '## Context',
      '',
      record.context,
      '',
      '## Decision',
      '',
      record.decision,
      '',
      '## Consequences',
      '',
      '- Benefit: ' + record.benefit,
      '- Cost: ' + record.cost,
      '',
      '## Verification',
      '',
      record.verification,
      '',
      '## References',
      '',
      ...references,
      '',
    ].join('\n');
    writeFileSync('docs/adr/' + record.id + '-' + record.slug + '.md', document, { flag: 'wx' });
  }
  NODE
  ~~~

  Expected: seven new complete ADRs, each with one exact decision, an explicit benefit and cost, a future verification gate, the P00 documentation-only boundary, and all three repository-relative governing references. Exclusive creation fails if an unexpected pre-existing ADR would be overwritten.

- [ ] **Verify filenames, headings, and the deferred frontend decision.**

  ```bash
  test "$(rg --files docs/adr | rg -c '^docs/adr/[0-9]{4}-[^/]+\.md$')" = 7
  for file in docs/adr/*.md; do
    for heading in Status Context Decision Consequences Verification References; do rg -x "## $heading" "$file"; done
  done
  rg -F 'Next.js remains only a preferred candidate' docs/adr/0007-frontend-surface-boundaries.md
  for file in CONTEXT.md docs/adr/0007-frontend-surface-boundaries.md; do
    rg -F 'separate Frappe-native site/database' "$file"
    rg -F 'minimal Dorzak branding' "$file"
    rg -F 'tenant-bound Dorzak gateway' "$file"
    rg -n 'versioned (approved )?application manifest' "$file"
    rg -F 'never queries merchant databases directly' "$file"
    rg -F 'P17 remains Not started and Not authorized' "$file"
  done
  rg -n 'No dual-write|one authority|Qatar/QAR|PostgreSQL 16' CONTEXT.md docs/adr
  ```

  Expected: every check exits `0`; no ADR claims a later packet is implemented.

- [ ] **Stage exactly eight documents and commit.**

  ```bash
  git add -- CONTEXT.md docs/adr/0001-system-of-record-authority.md \
    docs/adr/0002-organization-location-and-isolated-erpnext-tenancy.md \
    docs/adr/0003-modular-monolith-and-external-adapters.md \
    docs/adr/0004-one-complete-public-launch.md \
    docs/adr/0005-immutable-plan-publication.md \
    docs/adr/0006-commerce-cutover-and-no-dual-write.md \
    docs/adr/0007-frontend-surface-boundaries.md
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 8
  git commit -m "docs: record P00 architecture context"
  ```

  Run the global writer-boundary checks.

### Task 16: Align setup, run, storage, PostgreSQL, and recovery guidance

**Files:**
- Create: `README.md`
- Modify: `RUN.md`
- Modify: `backend/README.md`
- Modify: `backend/.env.example:27-35`

**Interfaces:** Root README is the canonical setup/quality entry. `RUN.md` documents manual development and guarded E2E. Backend README delegates measured counts to evidence. Environment guidance distinguishes SQLite fast feedback from PostgreSQL 16 qualification. Recovery never deletes evidence or user state.

- [ ] **Run the stale-guidance checks before editing.**

  ```bash
  test -f README.md
  rg -n 'npm ci|composer install.*no-progress|scripts/quality/run-p00|e2e:serve|PostgreSQL 16' README.md RUN.md backend/README.md
  rg -n '179 tests|30 passing|tests always use SQLite' backend/README.md backend/.env.example
  ```

  Expected: root README and required current commands are absent; stale hard-coded claims are found.

- [ ] **Write all four complete setup and recovery artifacts deterministically.**

  Run this complete generator from the repository root:

  ~~~js
  node --input-type=module <<'NODE'
  import { readFileSync, writeFileSync } from 'node:fs';

  const document = (lines) => lines.join('\n') + '\n';
  const rootReadme = document([
    '# Dorzak',
    '',
    'Dorzak is one branded multi-vertical business operating platform. P00 stabilizes the recovered React/Laravel baseline; it does not publish later roadmap capability.',
    '',
    '## Authority and scope',
    '',
    '- [Domain context](CONTEXT.md)',
    '- [P00 approved design](docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)',
    '- [P00 implementation plan](docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md)',
    '- [Run and recovery guide](RUN.md)',
    '',
    '## Prerequisites',
    '',
    'Use exactly the PHP version in .php-version and the Node version in .node-version. Composer and npm must consume the committed lockfiles. PostgreSQL qualification requires the owner-approved immutable PostgreSQL 16 identity.',
    '',
    '## Install',
    '',
    '    cd backend && composer install --no-interaction --prefer-dist --no-progress',
    '    cd .. && npm ci',
    '    npx playwright install chromium',
    '',
    '## Canonical quality entry points',
    '',
    '    export P00_RUNNER_ROLE=local',
    '    export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"',
    '    scripts/quality/run-p00 --list',
    '    scripts/quality/run-p00 composer-validation',
    '    scripts/quality/run-p00 php-style-static',
    '    scripts/quality/run-p00 sqlite',
    '    scripts/quality/run-p00 postgresql-16',
    '    scripts/quality/run-p00 frontend',
    '    scripts/quality/run-p00 playwright',
    '    node scripts/quality/p00.mjs aggregate .artifacts/p00',
    '',
    'The PostgreSQL job obtains a unique create-only candidate from the approved attested supervisor and never accepts, commits or prints a reusable database URL. Measured versions, counts, hashes, immutable PostgreSQL identity, bundle result and CI identities live in [P00 evidence](docs/superpowers/evidence/p00/manifest.json); this README does not duplicate measured values.',
    '',
    'The frontend media contract is origin-relative /storage/<disk-relative-key>. The serving frontend origin must route /storage/* to Laravel.',
  ]);

  const runGuide = document([
    '# Running Dorzak',
    '',
    'Use the exact runtimes in .php-version and .node-version and install only from composer.lock and package-lock.json.',
    '',
    '## Manual development',
    '',
    'Terminal 1:',
    '',
    '    cd backend',
    '    composer install --no-interaction --prefer-dist --no-progress',
    '    php artisan serve --host=127.0.0.1 --port=8000',
    '',
    'Terminal 2:',
    '',
    '    npm ci',
    '    npx playwright install chromium',
    '    npm run dev -- --host 127.0.0.1 --strictPort',
    '',
    'Vite proxies both /api and /storage to Laravel. Every production frontend origin must likewise serve or proxy origin-relative /storage/* to Laravel; do not rewrite stored disk-relative keys into backend-origin URLs.',
    '',
    '## Guarded browser matrix',
    '',
    'Run only:',
    '',
    '    npm run test:e2e',
    '',
    'Playwright invokes php artisan e2e:serve only with the owner-approved P00_E2E service inputs. The attested PostgreSQL 16 service must contain no real data, expose the approved provisioner nonce, and issue a unique least-privilege database/role capability for every run. The command migrates and seeds only that new candidate, activates it last, and never resets, drops, renames, unlinks or reuses a database. Cleanup may address only P00_E2E_SERVICE_LIFECYCLE_ID; cleanup failure records an orphan.',
    '',
    '## PostgreSQL 16 qualification',
    '',
    'The approved attested supervisor creates a unique empty _test database and least-privilege role for every invocation. P00_PG_IDENTITY, the closed attestation hash and P00_PG_INSTANCE_NONCE_SHA256 selected in Task 0 must match the live connection:',
    '',
    '    export P00_RUNNER_ROLE=local',
    '    export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"',
    '    scripts/quality/run-p00 postgresql-16',
    '',
    'The provisioning guard rejects a non-PostgreSQL candidate, non-test database, mismatched live nonce or wrong server major before migration. The PHPUnit bootstrap then independently rebinds the exact attestation, authority and live fingerprint before any test application or mutation.',
    '',
    '## Quality evidence',
    '',
    'Run scripts/quality/run-p00 --list for the six canonical jobs. Counts and versions come from scripts/quality/p00-contract.json and docs/superpowers/evidence/p00/manifest.json. The Vite warning for chunks larger than 500 kB is explicit accepted-open debt: its exact text, occurrence, affected files and hash are measured; it is not hidden, waived or grounds to raise the 216700-byte initial gzip limit.',
    '',
    '## Recovery after any failed writer boundary',
    '',
    '1. Stop all new writers.',
    '2. Retain .artifacts/p00 and every failure log.',
    '3. Record the failing SHA, exact command and runtime/lock/browser/PostgreSQL identities.',
    '4. Re-run the protected-state checks and compare the registered 16-entry user manifest and reviewed MediaUrl diff/artifact relationships.',
    '5. Return to the single owning task and add or correct only the narrow regression.',
    '6. Commit through that task allowlist, then rerun its boundary and every downstream gate on the new clean SHA.',
    '',
    'Never reset or clean the user checkout, delete failure evidence, reduce a gate, retry CI into green, reuse a stale execution worktree, or start P01.',
  ]);

  const backendReadme = document([
    '# Dorzak Laravel backend',
    '',
    'Laravel is the P00 modular monolith and API. SQLite in-memory is the fast feedback lane; the complete qualification lane uses the owner-approved immutable PostgreSQL 16 identity. Public media is served through origin-relative /storage/*.',
    '',
    '## Install',
    '',
    '    composer install --no-interaction --prefer-dist --no-progress',
    '',
    '## Canonical checks',
    '',
    '    composer validate --strict --no-check-publish',
    '    vendor/bin/pint --test',
    '    composer analyse',
    '    composer test:sqlite',
    '    composer test:postgres',
    '',
    'The PostgreSQL database name must end _test. The guarded runner provisions a new candidate and passes its credential URL only in process memory; callers may not supply or reuse DB_URL. Supply the approved immutable identity, closed attestation hash, supervisor credentials and provisioner nonce separately; tests/Support/postgres-bootstrap.php verifies the actual candidate before any test mutation.',
    '',
    'Measured test counts, runtimes, lockfile hashes and database identity are recorded in [P00 evidence](../docs/superpowers/evidence/p00/manifest.json), not hard-coded here.',
  ]);

  writeFileSync('README.md', rootReadme, { flag: 'wx' });
  writeFileSync('RUN.md', runGuide);
  writeFileSync('backend/README.md', backendReadme);

  const environmentPath = 'backend/.env.example';
  const environment = readFileSync(environmentPath, 'utf8');
  const stale = document([
    '# Production target is PostgreSQL 16 (docs 04 — partial unique indexes, reporting).',
    '# Local dev without a Postgres server may use sqlite; the test suite always runs on',
    '# in-memory sqlite (see phpunit.xml). Uncomment the pgsql block for production/CI.',
  ]);
  const qualified = document([
    '# Production target is PostgreSQL 16 (docs 04 — partial unique indexes, reporting).',
    '# SQLite in-memory is the fast PHPUnit lane; PostgreSQL 16 is the complete qualification lane.',
    '# Every qualification invocation provisions a new empty database whose name ends _test.',
    '# DB_URL is internal guarded-runner state; callers must not supply, print, persist or reuse it.',
    '# P00_PG_IDENTITY, attestation hash and instance nonce are separate approved inputs.',
    '# Provisioning rejects unsafe candidates before migration; PHPUnit bootstrap rebinds the exact live authority before tests.',
  ]);
  if (environment.split(stale).length !== 2) {
    throw new Error('Expected exactly one stale backend database guidance block');
  }
  writeFileSync(environmentPath, environment.replace(stale, qualified));
  NODE
  ~~~

  Expected: README.md is exclusively created; RUN.md and backend/README.md become complete canonical guides; the exact stale environment paragraph is replaced once. The documents name the origin-relative storage contract, guarded E2E identity, immutable PostgreSQL qualification, exact quality commands, measured Vite debt, evidence source and fail-closed recovery without hard-coded measured test totals.

- [ ] **Verify all documented commands and stage only four paths.**

  ```bash
  rg -n 'npm ci|npx playwright install chromium|composer install --no-interaction --prefer-dist --no-progress|/storage|APP_ENV=e2e|e2e:serve|ends.*_test|provisioner nonce|never resets' README.md RUN.md backend/README.md backend/.env.example
  test -z "$(rg -n '179 tests|30 passing|tests always use SQLite' backend/README.md backend/.env.example || true)"
  scripts/quality/run-p00 --list
  git diff --check -- README.md RUN.md backend/README.md backend/.env.example
  git add -- README.md RUN.md backend/README.md backend/.env.example
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 4
  git commit -m "docs: align P00 setup and recovery runbooks"
  ```

  Run the global writer-boundary checks.

### Task 17: Freeze, independently review, verify fresh, run CI twice, and commit sanitized evidence

**Files:**
- Create: `docs/superpowers/evidence/p00/README.md`
- Create: `docs/superpowers/evidence/p00/manifest.schema.json`
- Create: `docs/superpowers/evidence/p00/manifest.json`
- Create: `docs/superpowers/evidence/p00/local-full-matrix.json`
- Create: `docs/superpowers/evidence/p00/ci-run-1.json`
- Create: `docs/superpowers/evidence/p00/ci-run-2.json`
- Create: `docs/superpowers/evidence/p00/bundle.json`
- Create: `docs/superpowers/evidence/p00/independent-review.md`

**Interfaces:** `CODE_SHA` and `INTEGRATED_SHA` are one identity: the clean, independently reviewed implementation/docs commit pushed by exact SHA and tested locally and in both new attempt-one CI runs. They must always be equal. A merge, rebase, queue result, or any other code-identity change creates a new `CODE_SHA` and restarts independent review, fresh verification, push verification, and both CI runs; it is never recorded as a different `INTEGRATED_SHA`. The later evidence-only commit has a distinct `EVIDENCE_SHA` recorded by Control Room after commit and never embedded self-referentially in its payload.

- [ ] **Require Task 14's approved/implemented adapter before closure.**

  This task cannot start merely because this plan exists. First verify this exact amended plan and its later Control Room execution record, independent review with zero Critical/Important findings, exact owner approval, updated P00_APPROVED_PLAN_COMMIT/P00_APPROVED_PLAN_SHA256, provider-native CI commit, canonical remote, sole required-gates status, immutable provider dependencies, exact approved runtimes, immutable PostgreSQL identity, complete provider normalizer, and exact two-run commands.

  ~~~bash
  # First rerun every Task 0 command, including the scoped Task 14 decision-record parser.
  test "$(git rev-parse "$P00_APPROVED_PLAN_COMMIT^{commit}")" = "$P00_APPROVED_PLAN_COMMIT"
  test "$(git show "$P00_APPROVED_PLAN_COMMIT:docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md" \
    | shasum -a 256 | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  test "$(shasum -a 256 docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md \
    | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  ~~~

  Expected: the complete Task 0 gate and every command above exit 0. A pending/malformed Task 14 record, hash, approval, review, CI-adapter or immutable-input mismatch stops Tasks 15–17 and all P00 execution.

- [ ] **Freeze the clean code identity.**

  ```bash
  cd "$P00_EXECUTION_WORKTREE"
  test -z "$(git status --short --untracked-files=normal)"
  CODE_SHA="$(git rev-parse HEAD)"
  printf '%s\n' "$CODE_SHA" | rg -x '[0-9a-f]{40}'
  INTEGRATED_SHA="$CODE_SHA"
  test "$INTEGRATED_SHA" = "$CODE_SHA"
  git diff --check "$P00_BASE_SHA..$CODE_SHA"
  test "$(git branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test "$(git rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  git worktree list --porcelain | rg -Fx "worktree $P00_EXECUTION_WORKTREE"
  ```

  Expected: clean named execution worktree on the approved branch, one valid code/integrated SHA, and no whitespace errors. Rerun the complete Task 0 protected checkout/content/MediaUrl checks here. Any later commit or merge invalidates this identity and restarts review, fresh verification and both CI runs.

- [ ] **Dispatch an independent read-only review of the exact range.**

  The reviewer receives the approved P00 design, this plan, `P00_BASE_SHA`, `CODE_SHA`, and this exact request:

  ```text
  Review P00_BASE_SHA..CODE_SHA read-only. Check every approved P00 contract, task order, test safety, MediaUrl preservation boundary, Qatar/QAR, zero-retry browser isolation, frontend quality, Pint/Larastan debt, PostgreSQL 16/full-suite/concurrency behavior, provider-neutral commands, CI aggregate semantics, bundle budget, docs/runbook accuracy, and dirty-state recovery. Report findings by Critical, Important, Minor with exact file/line evidence. Do not edit. Approval requires zero Critical and zero Important findings.
  ```

  The independent reviewer must return both files with these exact interfaces. P00_REVIEW_JSON is canonical JSON with schemaVersion 1, literal baseSha/codeSha, integer critical/important, and a string array minor. P00_REVIEW_MD begins with # Independent P00 Review, then exact BASE_SHA, CODE_SHA, Critical and Important lines, followed by ## Minor and either one bullet per identical JSON minor string or - None.

  ~~~bash
  jq -e --arg base "$P00_BASE_SHA" --arg code "$CODE_SHA" \
    '.schemaVersion == 1 and .baseSha == $base and .codeSha == $code
     and .critical == 0 and .important == 0
     and (.minor | type) == "array" and all(.minor[]; type == "string")' \
    "$P00_REVIEW_JSON"
  rg -Fx '# Independent P00 Review' "$P00_REVIEW_MD"
  rg -Fx "BASE_SHA: $P00_BASE_SHA" "$P00_REVIEW_MD"
  rg -Fx "CODE_SHA: $CODE_SHA" "$P00_REVIEW_MD"
  rg -Fx 'Critical: 0' "$P00_REVIEW_MD"
  rg -Fx 'Important: 0' "$P00_REVIEW_MD"
  rg -Fx '## Minor' "$P00_REVIEW_MD"
  ~~~

  Expected: every validation exits 0. Any Critical/Important finding returns to its single owning task for the narrow fix, focused commit, downstream reruns, new CODE_SHA and entirely new review. Minor findings remain verbatim in both outputs and may not silently expand scope.

- [ ] **Run the complete matrix from a new checkout, never a linked stale worktree.**

  ```bash
  test ! -e "$P00_FRESH_CHECKOUT"
  git -C "$P00_EXECUTION_WORKTREE" cat-file -e "$CODE_SHA^{commit}"
  git clone --no-local --no-checkout "$P00_EXECUTION_WORKTREE" "$P00_FRESH_CHECKOUT"
  git -C "$P00_FRESH_CHECKOUT" checkout --detach "$CODE_SHA"
  test "$(git -C "$P00_FRESH_CHECKOUT" rev-parse HEAD)" = "$CODE_SHA"
  test "$(git -C "$P00_FRESH_CHECKOUT" rev-parse --show-toplevel)" = "$P00_FRESH_CHECKOUT"
  test -z "$(git -C "$P00_FRESH_CHECKOUT" branch --show-current)"
  test -z "$(git -C "$P00_FRESH_CHECKOUT" status --short --untracked-files=normal)"
  test "$(cat "$P00_FRESH_CHECKOUT/.php-version")" = "$P00_PHP_VERSION"
  test "$(cat "$P00_FRESH_CHECKOUT/.node-version")" = "$P00_NODE_VERSION"
  test "$(php -r 'echo PHP_VERSION;')" = "$P00_PHP_VERSION"
  test "$(node -p 'process.versions.node')" = "$P00_NODE_VERSION"
  (cd "$P00_FRESH_CHECKOUT/backend" && composer install --no-interaction --prefer-dist --no-progress)
  (cd "$P00_FRESH_CHECKOUT" && npm ci && npx playwright install chromium)
  ```

  The source is the clean execution worktree, so an unpushed CODE_SHA is locally obtainable without selecting or trusting a remote. `--no-local` forces independent Git objects and rejects hard-link optimization. Provision the approved immutable PostgreSQL 16 service and its attested create-only supervisor inputs; do not export a reusable `DB_URL`. The guarded runner issues the fresh qualification candidate internally:

  ```bash
  cd "$P00_FRESH_CHECKOUT"
  P00_LOCAL_ARTIFACTS="$PWD/.artifacts/p00/fresh-$CODE_SHA"
  test ! -e "$P00_LOCAL_ARTIFACTS"
  export P00_RUNNER_ROLE=local
  export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"
  for job in composer-validation php-style-static sqlite postgresql-16 frontend playwright; do
    P00_ARTIFACT_DIR="$P00_LOCAL_ARTIFACTS" P00_ATTEMPT=1 scripts/quality/run-p00 "$job"
  done
  node scripts/quality/p00.mjs aggregate "$P00_LOCAL_ARTIFACTS"
  jq -e --arg sha "$CODE_SHA" \
    '.status == "passed" and .integratedSha == $sha and .retryAttempt == 1
     and .failureCount == 0 and .unexplainedSkipCount == 0
     and (.jobs | length) == 6
     and all(.jobs[]; .integratedSha == $sha and .retryAttempt == 1)' \
    "$P00_LOCAL_ARTIFACTS.required-gates.json"
  jq -e \
    '.serverVersionNum >= 160000 and .serverVersionNum < 170000
     and (.databaseName | endswith("_test"))
     and (.identity | length) >= 64
     and (.attestationSha256 | test("^[0-9a-f]{64}$"))' \
    "$P00_LOCAL_ARTIFACTS/postgresql-16/postgresql-identity.json"
  jq -e \
    '.gzipBytes <= 216700 and .limitBytes == 216700
     and .largeChunkDebt.status == "accepted-open"
     and .largeChunkDebt.occurrenceCount == 1
     and (.largeChunkDebt.affectedFiles | length) > 0' \
    "$P00_LOCAL_ARTIFACTS/frontend/bundle.json"
  test -z "$(git status --short --untracked-files=normal)"
  cd "$P00_EXECUTION_WORKTREE"
  test "$(pwd -P)" = "$P00_EXECUTION_WORKTREE"
  test "$(git rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  test "$(git branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test "$(git rev-parse HEAD)" = "$CODE_SHA"
  test "$INTEGRATED_SHA" = "$CODE_SHA"
  test -z "$(git status --short --untracked-files=normal)"
  ```

  Expected: six jobs and aggregate pass at CODE_SHA; counts are read from the versioned contract (SQLite 446, PostgreSQL 450, frontend unit 8, browser 9); zero failures/retries/skips; the observed PostgreSQL server is major 16 under the exact immutable identity/attestation; bundle gzip is at most 216700 while the exact large-chunk warning remains measured accepted-open debt; the detached checkout stays clean because raw outputs are ignored. `P00_LOCAL_ARTIFACTS` remains an absolute path to that fresh result, but the shell returns to the clean named execution worktree at exact CODE_SHA before any push, CI normalization, evidence publication or commit. Rerun all Task 0 protected-state checks at this writer boundary.

- [ ] **Push the exact integrated SHA and obtain two new CI runs.**

  This provider phase has separate material prerequisites: the owner must authorize and enable GitHub Pro for the still-private user-owned repository, and authorize a GitHub CLI credential that can add/update workflow files. The read-only 2026-07-14 preflight returned HTTP `403` for both branch-protection and effective-rules endpoints, and the current classic OAuth token exposes only `gist`, `read:org`, and `repo` rather than the required `workflow` scope. Provider execution is therefore currently blocked; never make the repository public or silently refresh credentials as a substitute for owner authorization. The owner approval must also explicitly accept the documented classic-check workflow-identity limitation. Local Tasks 0–16 do not require either provider capability.

  After that prerequisite is registered, execute the exact canonical Task 14 blocks `TASK14_PUSH_AND_RUN_COMMANDS`, `TASK14_REQUIRED_STATUS_COMMANDS`, and `TASK14_TWO_RUN_COMMANDS`, in that order and without alteration. They push `INTEGRATED_SHA` only to the owner-approved `origin/main`, verify the resulting push run, require zero applicable active ruleset rules, install or validate the exact app-scoped `required-gates` policy, and obtain two new workflow-dispatch runs. Do not substitute a generic branch push or target `P00_EXECUTION_BRANCH`.

  Trigger run 1 and run 2 as distinct new runs, never reruns/retries. The amended normalizer writes exactly `.artifacts/p00/ci/ci-run-1.json` and `.artifacts/p00/ci/ci-run-2.json` in the Task 13 schema. Then require:

  ```bash
  CI_RUN_1=".artifacts/p00/ci/ci-run-1.json"
  CI_RUN_2=".artifacts/p00/ci/ci-run-2.json"
  test -f "$CI_RUN_1" && test -f "$CI_RUN_2"
  for run in "$CI_RUN_1" "$CI_RUN_2"; do
    jq -e --arg sha "$INTEGRATED_SHA" \
      '. as $run | .schemaVersion == 1 and .integratedSha == $sha and .attempt == 1
       and .requiredGate.status == "passed" and .requiredGate.jobs == 6
       and (.jobs | length) == 6
       and all(.jobs[]; .integratedSha == $sha and .status == "passed"
         and .retryAttempt == 1 and .failureCount == 0 and .unexplainedSkipCount == 0
         and .inputFingerprintSha256 == $run.inputFingerprintSha256
         and .inputs == $run.inputs)' \
      "$run"
  done
  test "$(jq -r .runId "$CI_RUN_1")" != "$(jq -r .runId "$CI_RUN_2")"
  test "$(jq -Sc '{contractSha256,inputFingerprintSha256,inputs,jobs:[.jobs[]|{job,integratedSha,inputFingerprintSha256,inputs,testCount}]}' "$CI_RUN_1" | shasum -a 256 | awk '{print $1}')" \
    = "$(jq -Sc '{contractSha256,inputFingerprintSha256,inputs,jobs:[.jobs[]|{job,integratedSha,inputFingerprintSha256,inputs,testCount}]}' "$CI_RUN_2" | shasum -a 256 | awk '{print $1}')"
  ```

  Expected: distinct provider run IDs and attempt 1; exact same integrated SHA, contract, complete portable inputs, PHP/Node/Composer/npm/Playwright identities, Composer/npm lock hashes, portable immutable PostgreSQL identity/policy, ordered job names/counts and passing aggregate. Each run separately binds its closed platform observation and its own PostgreSQL attestation hash, live nonce, sanitized endpoint hash, `_test` database and observed major; those per-run service observations may differ and are not compared across distinct ephemeral runs. Any retry metadata, provider-normalizer ambiguity or portable identity mismatch invalidates that run and returns to the Task 14 amendment; it may not be repaired in Task 17.

- [ ] **Create the schema and eight sanitized evidence files from measured artifacts.**

  Task 13's complete evidenceSchema, buildEvidence, aggregate and validation implementation is the sole schema/builder/normalizer authority; do not hand-edit generated JSON. Its versioned contract is the sole count source. Run:

  ~~~bash
  EVIDENCE_DIRECTORY="docs/superpowers/evidence/p00"
  test ! -e "$EVIDENCE_DIRECTORY"
  set +e
  node scripts/quality/p00.mjs validate-evidence "$EVIDENCE_DIRECTORY/manifest.json"
  absent_status="$?"
  set -e
  test "$absent_status" -ne 0
  node scripts/quality/p00.mjs build-evidence \
    "$EVIDENCE_DIRECTORY" \
    "$P00_BASE_SHA" "$CODE_SHA" "$INTEGRATED_SHA" \
    "$P00_LOCAL_ARTIFACTS" \
    "$CI_RUN_1" "$CI_RUN_2" \
    "$P00_REVIEW_JSON" "$P00_REVIEW_MD"
  EVIDENCE_FILES="$(rg --files "$EVIDENCE_DIRECTORY" | sed "s#^$EVIDENCE_DIRECTORY/##" | LC_ALL=C sort)"
  test "$(printf '%s\n' "$EVIDENCE_FILES" | wc -l | tr -d ' ')" = 8
  test "$EVIDENCE_FILES" \
    = "$(printf '%s\n' README.md bundle.json ci-run-1.json ci-run-2.json independent-review.md \
      local-full-matrix.json manifest.json manifest.schema.json | LC_ALL=C sort)"
  node scripts/quality/p00.mjs validate-evidence "$EVIDENCE_DIRECTORY/manifest.json"
  jq -e --slurpfile contract scripts/quality/p00-contract.json \
    '($contract[0].jobs | map({key: .name, value: .testCount}) | from_entries) as $counts
     | .INTEGRATED_SHA as $sha
     | .counts.source == "scripts/quality/p00-contract.json"
       and .counts.jobs == $counts
       and .CODE_SHA == .INTEGRATED_SHA
       and .postgresqlObservation.serverVersionNum >= 160000
       and .postgresqlObservation.serverVersionNum < 170000
       and (.ciRuns | length) == 2
       and .ciRuns[0].runId != .ciRuns[1].runId
       and all(.ciRuns[]; .attempt == 1 and .integratedSha == $sha)' \
    "$EVIDENCE_DIRECTORY/manifest.json"
  jq -e \
    '.limitBytes == 216700 and .gzipBytes <= .limitBytes
     and .largeChunkDebt.status == "accepted-open"
     and .largeChunkDebt.occurrenceCount == 1
     and (.largeChunkDebt.affectedFiles | length) > 0' \
    "$EVIDENCE_DIRECTORY/bundle.json"
  ~~~

  Expected: the absent manifest fails closed, then the deterministic builder creates and validates all eight files in a new sibling temporary directory and publishes the canonical directory with one atomic rename. Any write or validation failure leaves the canonical path absent, never partial. It prints P00_EVIDENCE_BUILT files=7 (seven manifest-referenced siblings plus manifest). Validation prints P00_EVIDENCE PASS with the literal base/code/integrated identities, two runs and seven sibling references. The generated schema has additionalProperties=false at every manifest object boundary; the validator enforces exactly eight canonical regular paths, cross-binds every summary to its sibling path/hash, applies closed top-level checks and whole-value secret scans to every loaded local/CI payload, reads the generated root `bundle.json`, and verifies exact contract counts, portable PostgreSQL identity/policy plus separately bound per-run observations, browser identity, bundle limit and warning debt, review range/verdict, and two distinct attempt-one CI runs. The manifest never embeds the later EVIDENCE_SHA.

- [ ] **Run final sanitization and design coverage checks.**

  ```bash
  test -z "$(rg -n --hidden -i \
    'authorization:|bearer[[:space:]]|password[=:]|token[=:]|secret[=:]|private key|database_url=' \
    docs/superpowers/evidence/p00 || true)"
  jq -e --arg base "$P00_BASE_SHA" --arg code "$CODE_SHA" --arg integrated "$INTEGRATED_SHA" \
    '.BASE_SHA == $base and .CODE_SHA == $code and .INTEGRATED_SHA == $integrated' \
    docs/superpowers/evidence/p00/manifest.json
  node scripts/quality/p00.mjs validate-evidence docs/superpowers/evidence/p00/manifest.json
  git diff --check -- docs/superpowers/evidence/p00
  ```

  Expected: secret scan has no matches; all other commands exit `0`.

- [ ] **Commit only the eight final evidence paths.**

  ```bash
  test "$(pwd -P)" = "$P00_EXECUTION_WORKTREE"
  test "$(git rev-parse HEAD)" = "$CODE_SHA"
  test "$(git branch --show-current)" = "$P00_EXECUTION_BRANCH"
  git add -- docs/superpowers/evidence/p00/README.md \
    docs/superpowers/evidence/p00/manifest.schema.json \
    docs/superpowers/evidence/p00/manifest.json \
    docs/superpowers/evidence/p00/local-full-matrix.json \
    docs/superpowers/evidence/p00/ci-run-1.json \
    docs/superpowers/evidence/p00/ci-run-2.json \
    docs/superpowers/evidence/p00/bundle.json \
    docs/superpowers/evidence/p00/independent-review.md
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 8
  git diff --cached --check
  git commit -m "docs: record P00 verification evidence"
  EVIDENCE_SHA="$(git rev-parse HEAD)"
  printf '%s\n' "$EVIDENCE_SHA" | rg -x '[0-9a-f]{40}'
  test "$(git rev-parse "$EVIDENCE_SHA^")" = "$CODE_SHA"
  EVIDENCE_CHANGED="$(git diff-tree --no-commit-id --name-only -r "$EVIDENCE_SHA" | LC_ALL=C sort)"
  test "$(printf '%s\n' "$EVIDENCE_CHANGED" | wc -l | tr -d ' ')" = 8
  test "$EVIDENCE_CHANGED" \
    = "$(printf '%s\n' \
      docs/superpowers/evidence/p00/README.md \
      docs/superpowers/evidence/p00/manifest.schema.json \
      docs/superpowers/evidence/p00/manifest.json \
      docs/superpowers/evidence/p00/local-full-matrix.json \
      docs/superpowers/evidence/p00/ci-run-1.json \
      docs/superpowers/evidence/p00/ci-run-2.json \
      docs/superpowers/evidence/p00/bundle.json \
      docs/superpowers/evidence/p00/independent-review.md | LC_ALL=C sort)"
  test -z "$(git diff --cached --name-only)"
  ```

  The Control Room durably records `BASE_SHA`, `CODE_SHA`, `INTEGRATED_SHA`, `EVIDENCE_SHA`, evidence payload SHA-256, the two CI run IDs, and the owner acceptance decision. `EVIDENCE_SHA^` must be exactly `CODE_SHA`, and its tree delta must be exactly the eight allowlisted evidence paths above. Do not run CI on the evidence-only commit and mislabel it as the tested code SHA.

- [ ] **Perform the terminal repository and protected-state check, then stop.**

  ```bash
  media=backend/app/Support/MediaUrl.php
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" status --short --untracked-files=normal)"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" diff --cached --name-only)"
  test "$(realpath "$P00_EXECUTION_WORKTREE")" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse HEAD)" = "$EVIDENCE_SHA"
  test "$(git -C "$P00_USER_WORKTREE" branch --show-current)" = "$P00_USER_BRANCH"
  test "$(git -C "$P00_USER_WORKTREE" rev-parse HEAD)" = "$P00_USER_HEAD"
  test "$(git -C "$P00_USER_WORKTREE" worktree list --porcelain \
    | awk '/^worktree / || /^branch /' | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" \
    = "$P00_WORKTREE_IDENTITY_SHA256"
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | wc -l | tr -d ' ')" = 16
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal \
    | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = "$P00_PROTECTED_STATUS_SHA256"
  test "$(shasum -a 256 "$P00_PROTECTED_CONTENT_MANIFEST" | awk '{print $1}')" \
    = "$P00_PROTECTED_CONTENT_MANIFEST_SHA256"
  test "$(git -C "$P00_USER_WORKTREE" diff --binary --full-index "$P00_USER_HEAD" -- "$media" \
    | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  test "$(git -C "$P00_EXECUTION_WORKTREE" diff --binary --full-index \
    "$P00_USER_HEAD" "$P00_BASE_SHA" -- "$media" | shasum -a 256 | awk '{print $1}')" \
    = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  case "$P00_MEDIA_METHOD" in
    dedicated_commit)
      test -z "$P00_MEDIA_ARTIFACT_PATH"
      test "$(git -C "$P00_EXECUTION_WORKTREE" diff --binary --full-index \
        "$P00_MEDIA_ARTIFACT_ID^" "$P00_MEDIA_ARTIFACT_ID" -- "$media" \
        | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
      git -C "$P00_EXECUTION_WORKTREE" merge-base --is-ancestor "$P00_MEDIA_ARTIFACT_ID" "$P00_BASE_SHA"
      ;;
    reviewed_patch)
      test "$(shasum -a 256 "$P00_MEDIA_ARTIFACT_PATH" | awk '{print $1}')" = "$P00_MEDIA_ARTIFACT_ID"
      test "$P00_MEDIA_ARTIFACT_ID" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
      ;;
    *) exit 1 ;;
  esac
  ```

  Repeat the complete inline 16-entry content-manifest verifier from Task 0 without alteration; it must print P00_PROTECTED_CONTENT PASS entries=16. Expected: clean execution worktree/index; exact protected branch, HEAD and worktree identity; unchanged status and safe content manifests; and exact reviewed MediaUrl diff/artifact/base relationships. Stop after reporting identities, contract-derived counts, versions, immutable PostgreSQL identity, bundle warning debt, two run IDs, review verdict and any Minor findings. Do not start P01, release publicly, or modify any other repository.

## Requirement-to-Task Coverage

| Approved design requirement | Plan coverage |
|---|---|
| Completed/evidenced preservation, approved base, clean named worktree, formal authorities | Task 0 |
| Exact runtime pins and lockfile reproducibility | Tasks 1, 6, 10, 13, 17 |
| Public media contract and origin `/storage` obligation | Tasks 2, 5, 16 |
| Qatar/QAR demo/order/browser contract | Tasks 3–5, 7, 17 |
| Create-only capability-isolated PostgreSQL fixture, exact live-PDO migration/seed/serve boot guards and substitution canaries, Laravel+Vite health, auth setup, real login, seven journeys, one worker/zero retries | Tasks 4–5 |
| DTO/API/auth/settings/money/protected shell/accessible form unit coverage | Tasks 6–8 |
| Frontend format/lint/type/unit/build/bundle | Tasks 6–8, 13, 17 |
| Independent 16-file Pint cleanup and bounded Larastan debt | Tasks 9–10 |
| Full PostgreSQL 16 suite, closed transport parser, every-boot default-PDO guard, wrong-nonce/substitution refusal and portable existing contract | Task 11 |
| Process-level order/stock/wallet concurrency with barriers | Task 12 |
| Provider-neutral jobs, control-bound runner class, aggregate, counts/versions/hashes, closed/atomic evidence interfaces | Task 13 |
| Provider-native thin wrapper, immutable dependencies, exact provider commands and app-scoped required status | Task 14; provider state only in gated Task 17 |
| CONTEXT, seven ADRs, accurate setup/runbook/recovery | Tasks 15–16 |
| Clean `CODE_SHA`, independent review, fresh checkout, two same-SHA CI runs with portable/per-run PostgreSQL boundaries, sanitized atomic evidence and exact parent/path SHA identities | Task 17 |

## Plan Self-Review Gate

Before plan approval and again after any approved amendment:

- [ ] Trace every design Section 6–12 clause through the coverage table and exact task acceptance command.
- [ ] Scan this file for banned indefinite markers, ellipses used as instructions, vague comparison phrases, broad staging, unbounded error repair, retries/quarantines/skips, and provider assumptions; require no matches.
- [ ] Check every named path exists now or has one explicit creation task before first use.
- [ ] Check interface names and cross-bindings across PHP, TypeScript, shell, JSON and evidence (`e2e:serve`, all three E2E phases, `PostgresQualificationGuard::assertPdo`, `ProcessBarrier::run`, the exact `P00_RUNNER_ROLE`/`P00_RUNNER_CLASS` pair, six job names, root evidence paths, portable/per-run PostgreSQL identities, and SHA/parent identities) for exact consistency; rerun both registered endpoint-substitution canaries and the valid-PG16 wrong-nonce refusal.
- [ ] Check tasks are numbered 0–17 exactly once and retain mandatory order.
- [ ] Check each writer has one staging allowlist, shared manifests/lockfiles/config/evidence have one serialized owner, and no command stages the original user-owned paths accidentally.
- [ ] Check Task 14 is a complete approved design but not execution authority, all five canonical marked-block hashes match its closed record, the Control JSON still binds every approval/input by exact commit/hash, and Task 17 alone owns later provider-state changes.

Execution handoff occurs only after this exact plan is independently reviewed, owner-approved, and durably authorized by the Control Room. Until then, stop at plan review.

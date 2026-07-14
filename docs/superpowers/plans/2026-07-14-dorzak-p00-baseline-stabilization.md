# Dorzak P00 Baseline Stabilization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a reproducible, reviewable P00 baseline in which the current Dorzak merchant application has explicit media and Qatar/QAR contracts, deterministic full-stack browser coverage, frontend and backend quality lanes, PostgreSQL 16 qualification, provider-neutral CI commands, architecture/runbook evidence, and a fail-closed final gate.

**Architecture:** Preserve the current React/Vite merchant surface and Laravel 13 modular monolith. Keep SQLite as fast feedback, add PostgreSQL 16 as the qualification database, run Playwright against a dedicated destructive-safe E2E fixture, and expose one provider-neutral quality dispatcher that a later owner-selected CI adapter wraps. P00 documents the future Organization/ERPNext authority boundaries but does not implement P01 or later roadmap work.

**Tech Stack:** PHP at the exact owner-approved production pin; Laravel 13.18.1; Composer 2; PHPUnit 12; Pint 1.29.3; Larastan 3.10; PHPStan 2.2; PostgreSQL 16; Node at the exact owner-approved production pin; npm lockfile v3; React 18; TypeScript 5; Vite 5; Vitest 2; Testing Library; axe-core; ESLint 8; Prettier 3; Playwright Chromium.

## Global Constraints

- This document is an implementation plan, not implementation or execution authority. Do not run Task 1 or any later task until Task 0 passes in full under a separate Control Room execution lease.
- Approved design authority is `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md` at commit `ea7b8258083231c6a9b7aa7c00d89009e29e696e`, SHA-256 `861dc58732d304d45837785d9ac74ff13dd3c44d46e467d531dbb55b408115e8`.
- The planning-only product input is commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`, SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2`. The planning-only roadmap input is artifact commit `069f4833190c75866494e7ba51bff3021070c0bf`, SHA-256 `e9aa2c7970f9edf08f03177458cb496f979a30dbf3cf7fd96480c0c3b9a5cc60`. The current plan-writing exception is not execution authority and does not approve either input program-wide.
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
- P00 does not choose or implement subscription currency redesign, Next.js, ERPNext migration, Organization tenancy, a remote, a CI provider, production runtime versions, a MediaUrl preservation method, an integration base, or an execution worktree.

## Execution Input Interface and Stop Rules

The future execution lease must bind the following shell variables to literal values copied from one durable Control Room execution-entry record. Empty values, ranges for runtime versions, mutable container tags, or inferred values fail Task 0.

| Variable | Exact meaning |
|---|---|
| `P00_CONTROL_RECORD` | Repository-relative path to the durable execution-entry approval |
| `P00_APPROVED_PLAN_COMMIT` | Full commit containing the independently reviewed, exactly owner-approved, provider-complete plan revision |
| `P00_APPROVED_PLAN_SHA256` | SHA-256 of this separately reviewed, owner-approved plan |
| `P00_REMOTE_NAME` / `P00_REMOTE_URL` | Owner-approved canonical Git remote and its exact URL |
| `P00_CI_PROVIDER` | Owner-approved provider identifier |
| `P00_PHP_VERSION` / `P00_NODE_VERSION` | Exact production versions, each with three numeric components |
| `P00_MEDIA_METHOD` | Exactly `dedicated_commit` or `reviewed_patch` |
| `P00_MEDIA_REVIEWED_DIFF_SHA256` | Hash of the reviewed full-index MediaUrl diff |
| `P00_MEDIA_ARTIFACT_ID` | Preserved commit SHA or durable patch SHA-256 |
| `P00_MEDIA_ARTIFACT_PATH` | Empty for a commit; approved durable path for a patch |
| `P00_MEDIA_VERIFICATION_RESULT` | Exactly `verified` after equality to the reviewed diff was proved |
| `P00_BASE_SHA` | Owner-approved 40-character clean integration base |
| `P00_EXECUTION_WORKTREE` / `P00_EXECUTION_BRANCH` | Owner-approved new worktree absolute path and branch |
| `P00_USER_WORKTREE` | Original user checkout whose 16-entry manifest is protected |
| `P00_USER_BRANCH` / `P00_USER_HEAD` | Exact protected checkout branch and 40-character HEAD captured before preservation/execution |
| `P00_WORKTREE_IDENTITY_SHA256` | SHA-256 of sorted `worktree` and `branch` records from `git worktree list --porcelain` after the approved execution worktree is created |
| `P00_PROTECTED_STATUS_SHA256` | Registered sorted status hash; it must remain `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa` |
| `P00_PROTECTED_CONTENT_MANIFEST` / `P00_PROTECTED_CONTENT_MANIFEST_SHA256` | Durable owner-approved non-secret content manifest and its 64-hex file hash; unsafe-to-attest paths stop execution |
| `P00_PG_IDENTITY_KIND` | Exactly `oci` or `external-attestation` |
| `P00_PG_IDENTITY` | Immutable OCI reference with `@sha256:<64-hex>`, or approved external resource/revision/fingerprint identity |
| `P00_PG_ATTESTATION_PATH` / `P00_PG_ATTESTATION_SHA256` | Sanitized immutable-service attestation and its 64-hex hash; never a mutable sentinel |
| `P00_PG_DB_URL` | Secret runtime connection URL for the approved PostgreSQL 16 test service; never committed, printed, or embedded in evidence |
| `P00_FRESH_CHECKOUT` | New absolute path used only by final fresh-checkout verification |

Guarded decisions are not substitute values. If the provider is GitHub, a later approved amendment may add a GitHub Actions file. If it is GitLab or another provider, that provider's native exact file and API commands must be approved instead. Task 14 stops until that amendment exists; no current task names a provider-native file.

## File Responsibility Map

| Serialized owner | Files and responsibility | May start after |
|---|---|---|
| Control Room / preservation lease | Execution record; MediaUrl preserved commit or patch evidence; canonical remote/provider/pins/base/worktree decisions | Plan approval |
| Runtime owner | `.php-version`, `.node-version` | Task 0 |
| Contract owner | `backend/tests/Unit/Support/MediaUrlTest.php`, `backend/tests/Feature/Commerce/CommerceImprovementsTest.php`, `backend/tests/Feature/DemoSeederParityTest.php`; conditional `MediaUrl.php` branch only under Task 2's guard | Task 1 |
| Browser fixture owner | `ResetE2eDatabase.php`, `E2ESeeder.php`, two E2E PHP tests | Task 3 |
| Browser harness/journey owner | `playwright.config.ts`, `vite.config.ts`, `tests/e2e/**`, `TextInput.tsx`, `SelectInput.tsx`, `POSPage.tsx` | Task 4 |
| Frontend manifest owner | Task 6 owns `package.json`, `package-lock.json`, `tsconfig.json`, Vitest/ESLint/Prettier config, and test setup; Task 8 is the sole later mechanical formatter and must leave both npm manifests byte-identical | Task 5 |
| Frontend behavior owner | Focused unit/component tests and production seams explicitly named in Tasks 7–8 | Task 6 |
| PHP style owner | Exactly the 16 paths enumerated in Task 9; never MediaUrl | Task 8 |
| Composer/static owner | `backend/composer.json`, `backend/composer.lock`, `backend/phpstan.neon.dist`, `backend/phpstan-baseline.neon` | Task 9 |
| PostgreSQL owner | `backend/phpunit.pgsql.xml`, PostgreSQL bootstrap, portable search models, process barrier/worker/tests | Task 10 |
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

**Interface:** This gate consumes the variables defined above and emits only `P00_EXECUTION_GATE PASS base=$P00_BASE_SHA worktree=$P00_EXECUTION_WORKTREE`. It never creates a branch, worktree, patch, commit, or repository file. The current correction candidate intentionally contains the Task 14 stop marker and therefore cannot pass; the owner-selected provider amendment must replace that marker, be independently re-reviewed, and receive exact owner approval before any P00 execution.

- [ ] **Verify formal authorities, not the planning exception.**

  The durable record must separately approve: this exact plan/hash; the product baseline at `cc4085cbca11e89257ae8535438db6cfe3dd75cc`; the roadmap with `069f4833190c75866494e7ba51bff3021070c0bf`; canonical remote; provider; exact PHP/Node pins; completed MediaUrl preservation; resulting base/worktree; and P00 execution. Search the record and inspect it manually:

  ```bash
  plan=docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md
  test -f "$P00_CONTROL_RECORD"
  printf '%s\n' "$P00_APPROVED_PLAN_COMMIT" | rg -x '[0-9a-f]{40}'
  test "$(git log -1 --format=%H -- "$plan")" = "$P00_APPROVED_PLAN_COMMIT"
  test "$(shasum -a 256 "$plan" | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  test "$(git show "$P00_APPROVED_PLAN_COMMIT:$plan" | shasum -a 256 | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  test -z "$(rg -n 'BLOCKED_BY_OWNER_DECISION ci_provider_adapter' "$plan" || true)"
  rg -n 'cc4085cbca11e89257ae8535438db6cfe3dd75cc|069f4833190c75866494e7ba51bff3021070c0bf|formal owner approval|P00 execution' "$P00_CONTROL_RECORD"
  ```

  Expected: the first command exits `0`; the plan hash equals `P00_APPROVED_PLAN_SHA256`; all four authority patterns are present in explicit approval clauses. Presence without explicit approval fails the gate.

- [ ] **Validate decision-bound values.**

  ```bash
  case "$P00_MEDIA_METHOD" in dedicated_commit|reviewed_patch) ;; *) exit 1 ;; esac
  test "$P00_MEDIA_VERIFICATION_RESULT" = verified
  printf '%s\n' "$P00_BASE_SHA" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_MEDIA_REVIEWED_DIFF_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PHP_VERSION" | rg -x '[0-9]+\.[0-9]+\.[0-9]+'
  printf '%s\n' "$P00_NODE_VERSION" | rg -x '[0-9]+\.[0-9]+\.[0-9]+'
  test -n "$P00_REMOTE_NAME"
  test -n "$P00_REMOTE_URL"
  test -n "$P00_CI_PROVIDER"
  test -n "$P00_EXECUTION_BRANCH"
  test "$P00_PROTECTED_STATUS_SHA256" = a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa
  printf '%s\n' "$P00_USER_HEAD" | rg -x '[0-9a-f]{40}'
  printf '%s\n' "$P00_WORKTREE_IDENTITY_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PROTECTED_CONTENT_MANIFEST_SHA256" | rg -x '[0-9a-f]{64}'
  printf '%s\n' "$P00_PG_ATTESTATION_SHA256" | rg -x '[0-9a-f]{64}'
  case "$P00_PG_IDENTITY_KIND" in
    oci) printf '%s\n' "$P00_PG_IDENTITY" | rg -x '[^[:space:]@]+@sha256:[0-9a-f]{64}' ;;
    external-attestation) printf '%s\n' "$P00_PG_IDENTITY" | rg -x 'external:[A-Za-z0-9._/-]+@[A-Za-z0-9._:-]+#sha256:[0-9a-f]{64}' ;;
    *) exit 1 ;;
  esac
  test -f "$P00_PG_ATTESTATION_PATH"
  test "$(shasum -a 256 "$P00_PG_ATTESTATION_PATH" | awk '{print $1}')" = "$P00_PG_ATTESTATION_SHA256"
  php -r '$a=json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR); if (($a["schemaVersion"] ?? null) !== 1 || ($a["kind"] ?? null) !== $argv[2] || ($a["identity"] ?? null) !== $argv[3] || ($a["serverMajor"] ?? null) !== 16 || ($a["immutable"] ?? null) !== true) { exit(1); }' "$P00_PG_ATTESTATION_PATH" "$P00_PG_IDENTITY_KIND" "$P00_PG_IDENTITY"
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
  rg -n 'disposable verification checkout|applied successfully|resulting diff matched' "$P00_CONTROL_RECORD"
  ```

  Expected: the selected branch exits `0`. The patch branch verifies a separately approved disposable application target recorded by Control Room; it never applies the patch to the named clean execution worktree.

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
  $statusLines = array_values(array_filter(explode("\n", trim($git(['status', '--short', '--untracked-files=normal'])))));
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

  Expected: every command exits `0`. If a stale worktree is named, a protected identity differs, a content entry is unsafe, or the plan still contains the Task 14 stop marker, stop.

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

  Expected: focused tests pass; full suite reports `444 passed` at this boundary (the registered 443 plus one new test).

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

- [ ] **Replace the stale assertion and extend order parity.**

  Use this exact store assertion:

  ```php
  $this->assertSame('QAR', $store->currency);
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

### Task 4: Add a destructive-safe, dedicated E2E database fixture

**Files:**
- Create: `backend/app/Console/Commands/ResetE2eDatabase.php`
- Create: `backend/database/seeders/E2ESeeder.php`
- Create: `backend/tests/Feature/E2E/E2EResetGuardTest.php`
- Create: `backend/tests/Feature/E2E/E2ESeederTest.php`

**Interfaces:** `php artisan e2e:reset` exits `1` unless `APP_ENV=e2e`, the default connection is SQLite, and the configured database is the one lexical value returned by `database_path('dorzak-e2e.sqlite')`. `E2eDatabaseLease::acquire()` rejects a non-canonical parent, symlink, directory/special file, multiply linked file, configuration alias, unmarked pre-existing file, failed exclusive create, lock conflict, or any device/inode/link/realpath change. A missing file is opened with exclusive-create mode; a safe existing regular single-link SQLite file must contain the exact command-owned identity marker. The same open descriptor remains locked and is revalidated immediately before migration, before seeding, and after seeding. The fixture owns `owner@e2e.dorzak.test / e2e-password`, one Qatar/QAR PRO store, and one deterministic variant product. It calls `PlanSeeder`, never `DemoSeeder`.

- [ ] **Write both tests before the command and seeder.**

  `E2EResetGuardTest.php`:

  ```php
  <?php

  namespace Tests\Feature\E2E;

  use App\Console\Commands\E2eDatabaseLease;
  use App\Console\Commands\ResetE2eDatabase;
  use Closure;
  use RuntimeException;
  use Tests\TestCase;

  final class E2EResetGuardTest extends TestCase
  {
      private string $temporaryRoot;

      protected function setUp(): void
      {
          parent::setUp();
          $this->temporaryRoot = sys_get_temp_dir().'/dorzak-e2e-'.bin2hex(random_bytes(8));
          self::assertTrue(mkdir($this->temporaryRoot, 0700));
      }

      protected function tearDown(): void
      {
          $this->removeTree($this->temporaryRoot);
          parent::tearDown();
      }

      public function test_database_lease_creates_only_the_exact_regular_single_link_and_detects_aliases_and_races(): void
      {
          $target = $this->temporaryRoot.'/dorzak-e2e.sqlite';

          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('testing', 'sqlite', $target, $target));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'pgsql', $target, $target));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', ':memory:', $target));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $this->temporaryRoot.'/alias.sqlite', $target));

          $lease = ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target);
          self::assertFileExists($target);
          self::assertSame(1, lstat($target)['nlink']);
          $lease->assertUnchanged();
          $lease->close();

          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          self::assertTrue(unlink($target));
          $database = new \PDO('sqlite:'.$target);
          $database->exec('CREATE TABLE p00_e2e_identity (marker TEXT PRIMARY KEY)');
          $statement = $database->prepare('INSERT INTO p00_e2e_identity (marker) VALUES (?)');
          self::assertTrue($statement->execute([E2eDatabaseLease::MARKER]));
          unset($statement, $database);

          $lease = ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target);
          $lease->assertUnchanged();
          $lease->close();

          $lease = ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target);
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          $lease->assertUnchanged();
          $lease->close();

          self::assertTrue(unlink($target));
          file_put_contents($target, 'ordinary single-link file');
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          self::assertTrue(unlink($target));

          $victim = $this->temporaryRoot.'/victim.sqlite';
          file_put_contents($victim, 'never mutate this file');
          self::assertTrue(symlink($victim, $target));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          self::assertSame('never mutate this file', file_get_contents($victim));
          self::assertTrue(unlink($target));

          self::assertTrue(link($victim, $target));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          self::assertSame('never mutate this file', file_get_contents($victim));
          self::assertTrue(unlink($target));

          self::assertTrue(mkdir($target, 0700));
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          self::assertTrue(rmdir($target));

          $socket = stream_socket_server('unix://'.$target, $errorCode, $errorMessage);
          self::assertIsResource($socket, $errorMessage);
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target));
          fclose($socket);
          self::assertTrue(unlink($target));

          $realParent = $this->temporaryRoot.'/real';
          $aliasParent = $this->temporaryRoot.'/alias';
          self::assertTrue(mkdir($realParent, 0700));
          self::assertTrue(symlink($realParent, $aliasParent));
          $aliasedTarget = $aliasParent.'/dorzak-e2e.sqlite';
          $this->assertRefused(fn () => ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $aliasedTarget, $aliasedTarget));

          $raceParent = $this->temporaryRoot.'/race-parent';
          $movedParent = $this->temporaryRoot.'/race-parent-moved';
          self::assertTrue(mkdir($raceParent, 0700));
          $parentTarget = $raceParent.'/dorzak-e2e.sqlite';
          $parentLease = ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $parentTarget, $parentTarget);
          self::assertTrue(rename($raceParent, $movedParent));
          self::assertTrue(mkdir($raceParent, 0700));
          file_put_contents($parentTarget, 'parent replacement');
          $this->assertRefused(fn () => $parentLease->assertUnchanged());
          $parentLease->close();

          $lease = ResetE2eDatabase::acquireDatabase('e2e', 'sqlite', $target, $target);
          $moved = $this->temporaryRoot.'/moved.sqlite';
          self::assertTrue(rename($target, $moved));
          file_put_contents($target, 'replacement');
          $this->assertRefused(fn () => $lease->assertUnchanged());
          $lease->close();
      }

      private function assertRefused(Closure $attempt): void
      {
          try {
              $result = $attempt();
              if ($result instanceof E2eDatabaseLease) {
                  $result->close();
              }
              self::fail('Unsafe E2E database identity was accepted.');
          } catch (RuntimeException) {
              self::assertTrue(true);
          }
      }

      private function removeTree(string $path): void
      {
          $stat = @lstat($path);
          if (! is_array($stat)) {
              return;
          }
          if (is_link($path) || is_file($path)) {
              unlink($path);
              return;
          }
          foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $child) {
              $this->removeTree($path.'/'.$child);
          }
          rmdir($path);
      }
  }
  ```

  `E2ESeederTest.php`:

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
  php artisan test tests/Feature/E2E/E2EResetGuardTest.php tests/Feature/E2E/E2ESeederTest.php
  ```

  Expected failure: both referenced classes are absent.

- [ ] **Create the fail-closed command.**

  ```php
  <?php

  namespace App\Console\Commands;

  use Database\Seeders\E2ESeeder;
  use Illuminate\Console\Command;
  use Illuminate\Support\Facades\DB;
  use RuntimeException;

  final class E2eDatabaseLease
  {
      public const MARKER = 'DORZAK_P00_E2E_DATABASE_V1';

      /** @param resource $handle */
      public function __construct(
          private $handle,
          private readonly string $path,
          private readonly string $canonicalParent,
          /** @var array{dev:int, ino:int} */
          private readonly array $identity,
          /** @var array{dev:int, ino:int} */
          private readonly array $parentIdentity,
      ) {}

      public function assertUnchanged(): void
      {
          clearstatcache(true, $this->path);
          $pathStat = @lstat($this->path);
          $parentStat = @lstat(dirname($this->path));
          $handleStat = is_resource($this->handle) ? @fstat($this->handle) : false;
          if (! is_array($pathStat) || ! is_array($parentStat) || ! is_array($handleStat)
              || ! self::isRegular($pathStat) || ! self::isRegular($handleStat)
              || ! self::isDirectory($parentStat)
              || $pathStat['nlink'] !== 1 || $handleStat['nlink'] !== 1
              || $pathStat['dev'] !== $this->identity['dev'] || $pathStat['ino'] !== $this->identity['ino']
              || $handleStat['dev'] !== $this->identity['dev'] || $handleStat['ino'] !== $this->identity['ino']
              || $parentStat['dev'] !== $this->parentIdentity['dev']
              || $parentStat['ino'] !== $this->parentIdentity['ino']
              || realpath(dirname($this->path)) !== $this->canonicalParent
              || realpath($this->path) !== $this->path) {
              throw new RuntimeException('E2E database identity changed or became unsafe.');
          }
      }

      public function close(): void
      {
          if (is_resource($this->handle)) {
              flock($this->handle, LOCK_UN);
              fclose($this->handle);
          }
      }

      public function __destruct()
      {
          $this->close();
      }

      /** @param array<string|int, mixed> $stat */
      public static function isRegular(array $stat): bool
      {
          return (($stat['mode'] ?? 0) & 0170000) === 0100000;
      }

      /** @param array<string|int, mixed> $stat */
      public static function isDirectory(array $stat): bool
      {
          return (($stat['mode'] ?? 0) & 0170000) === 0040000;
      }

      /** @param resource $handle */
      public static function containsOwnershipMarker($handle, int $size): bool
      {
          if ($size < 16 || $size > 134217728 || fseek($handle, 0) !== 0) {
              return false;
          }
          if (fread($handle, 16) !== "SQLite format 3\0" || fseek($handle, 0) !== 0) {
              return false;
          }
          $overlap = '';
          while (! feof($handle)) {
              $chunk = fread($handle, 65536);
              if ($chunk === false) {
                  return false;
              }
              $window = $overlap.$chunk;
              if (str_contains($window, self::MARKER)) {
                  return fseek($handle, 0) === 0;
              }
              $overlap = substr($window, -strlen(self::MARKER));
          }

          return false;
      }
  }

  final class ResetE2eDatabase extends Command
  {
      protected $signature = 'e2e:reset';

      protected $description = 'Reset only the named Dorzak E2E SQLite database';

      public static function acquireDatabase(
          string $environment,
          string $connection,
          string $configuredDatabase,
          string $lexicalTarget,
      ): E2eDatabaseLease
      {
          if ($environment !== 'e2e' || $connection !== 'sqlite' || $configuredDatabase !== $lexicalTarget) {
              throw new RuntimeException('E2E environment, connection, or lexical database target is invalid.');
          }
          $parent = dirname($lexicalTarget);
          $canonicalParent = realpath($parent);
          $parentBefore = @lstat($parent);
          if ($canonicalParent === false || $canonicalParent !== $parent
              || ! is_array($parentBefore) || ! E2eDatabaseLease::isDirectory($parentBefore)
              || basename($lexicalTarget) !== 'dorzak-e2e.sqlite') {
              throw new RuntimeException('E2E database parent or basename is not canonical.');
          }

          clearstatcache(true, $lexicalTarget);
          $before = @lstat($lexicalTarget);
          if (is_array($before)) {
              if (! E2eDatabaseLease::isRegular($before) || $before['nlink'] !== 1) {
                  throw new RuntimeException('Pre-existing E2E target is not a regular single-link file.');
              }
              $handle = @fopen($lexicalTarget, 'r+b');
          } else {
              $handle = @fopen($lexicalTarget, 'x+b');
          }
          if (! is_resource($handle) || ! flock($handle, LOCK_EX | LOCK_NB)) {
              if (is_resource($handle)) {
                  fclose($handle);
              }
              throw new RuntimeException('E2E database could not be exclusively opened and locked.');
          }
          $opened = fstat($handle);
          $parentAfter = @lstat($parent);
          if (! is_array($opened) || ! E2eDatabaseLease::isRegular($opened) || $opened['nlink'] !== 1) {
              flock($handle, LOCK_UN);
              fclose($handle);
              throw new RuntimeException('Opened E2E target is not a regular single-link file.');
          }
          if (is_array($before) && ! E2eDatabaseLease::containsOwnershipMarker($handle, (int) $opened['size'])) {
              flock($handle, LOCK_UN);
              fclose($handle);
              throw new RuntimeException('Pre-existing E2E target is not command-owned.');
          }
          if (is_array($before) && ($before['dev'] !== $opened['dev'] || $before['ino'] !== $opened['ino'])) {
              flock($handle, LOCK_UN);
              fclose($handle);
              throw new RuntimeException('E2E target changed during inspection/open.');
          }
          if (! is_array($parentAfter) || ! E2eDatabaseLease::isDirectory($parentAfter)
              || $parentBefore['dev'] !== $parentAfter['dev']
              || $parentBefore['ino'] !== $parentAfter['ino']) {
              flock($handle, LOCK_UN);
              fclose($handle);
              throw new RuntimeException('E2E parent changed during inspection/open.');
          }
          $lease = new E2eDatabaseLease(
              $handle,
              $lexicalTarget,
              $canonicalParent,
              ['dev' => $opened['dev'], 'ino' => $opened['ino']],
              ['dev' => $parentAfter['dev'], 'ino' => $parentAfter['ino']],
          );
          $lease->assertUnchanged();

          return $lease;
      }

      public function handle(): int
      {
          $connection = (string) config('database.default');
          $configuredDatabase = (string) config("database.connections.{$connection}.database");
          $target = database_path('dorzak-e2e.sqlite');
          try {
              $lease = self::acquireDatabase(
                  $this->laravel->environment(),
                  $connection,
                  $configuredDatabase,
                  $target,
              );
          } catch (RuntimeException $exception) {
              $this->error('E2E reset refused: environment or database identity is unsafe.');
              return self::FAILURE;
          }

          try {
              $lease->assertUnchanged();
              if ($this->call('migrate:fresh', ['--force' => true]) !== self::SUCCESS) {
                  return self::FAILURE;
              }
              $lease->assertUnchanged();
              if ($this->call('db:seed', ['--class' => E2ESeeder::class, '--force' => true]) !== self::SUCCESS) {
                  return self::FAILURE;
              }
              DB::statement('CREATE TABLE p00_e2e_identity (marker TEXT PRIMARY KEY)');
              DB::table('p00_e2e_identity')->insert(['marker' => E2eDatabaseLease::MARKER]);
              $lease->assertUnchanged();
              $this->info('E2E_RESET PASS database=dorzak-e2e.sqlite');

              return self::SUCCESS;
          } catch (RuntimeException $exception) {
              $this->error('E2E reset refused: database identity changed.');
              return self::FAILURE;
          } finally {
              $lease->close();
          }
      }
  }
  ```

- [ ] **Create the dedicated seeder.**

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

- [ ] **Verify the guard, fixture, repeatability, and SQLite aggregate.**

  ```bash
  cd backend
  php artisan test tests/Feature/E2E/E2EResetGuardTest.php tests/Feature/E2E/E2ESeederTest.php
  APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan e2e:reset --no-interaction; test "$?" = 1
  php artisan test
  ```

  Expected: `2 passed`; the guard test proves exclusive safe creation and refusal of wrong environment/connection/path, an unmarked ordinary file, lock conflict, symlink, hard link, directory, Unix socket, parent alias, parent replacement, and target replacement without invoking destructive work on those targets. The unsafe command prints the refusal and exits `1`; full SQLite reports `446 passed`.

- [ ] **Stage only the four files and commit.**

  ```bash
  git add -- \
    backend/app/Console/Commands/ResetE2eDatabase.php \
    backend/database/seeders/E2ESeeder.php \
    backend/tests/Feature/E2E/E2EResetGuardTest.php \
    backend/tests/Feature/E2E/E2ESeederTest.php
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 4
  git commit -m "test(e2e): add guarded Qatar fixture"
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

**Interfaces:** Playwright starts the guarded Laravel E2E reset/server and strict-port Vite server, uses one worker and zero retries, separates API authentication setup from a real UI login smoke, retains failure artifacts, and restores English/QAR after every journey. The existing seven journeys remain seven tests; setup and login smoke make the full run nine passing tests.

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
  export const e2eDatabasePath = resolve('backend/database/dorzak-e2e.sqlite');
  ```

- [ ] **Replace `playwright.config.ts` completely.**

  ```ts
  import { defineConfig, devices } from '@playwright/test';
  import {
    backendUrl,
    e2eDatabasePath,
    frontendUrl,
    storageStatePath,
  } from './tests/e2e/support/e2e';

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
        command: 'php artisan e2e:reset --no-interaction && php artisan serve --host=127.0.0.1 --port=8000',
        cwd: './backend',
        url: `${backendUrl}/up`,
        reuseExistingServer: false,
        env: {
          APP_ENV: 'e2e',
          APP_KEY: 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
          APP_URL: backendUrl,
          FRONTEND_URL: frontendUrl,
          DB_CONNECTION: 'sqlite',
          DB_DATABASE: e2eDatabasePath,
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
    await page.getByRole('button', { name: 'Add Product' }).click();
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
  "test:postgres": "@php vendor/bin/phpunit --configuration=phpunit.pgsql.xml"
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
- Create: `backend/tests/Support/postgres-bootstrap.php`
- Create: `backend/tests/Postgres/PostgresEnvironmentTest.php`
- Modify: `backend/app/Models/Product.php:96-107`
- Modify: `backend/app/Models/Customer.php:47-58`

**Interfaces:** The qualification lane consumes `DB_URL` with `postgres` or `postgresql` scheme, requires a database name ending `_test`, requires `pdo_pgsql`, and rejects any server outside major 16 before migrations. It runs Unit, Feature, and PostgreSQL suites. Search remains case-insensitive on SQLite and PostgreSQL.

- [ ] **Create the guard test/config seam and prove it is absent.**

  ```bash
  cd backend
  DB_URL=postgresql://dorzak_p00:dorzak_p00@127.0.0.1:55432/dorzak_p00_test vendor/bin/phpunit --configuration=phpunit.pgsql.xml --testsuite PostgreSQL
  ```

  Expected failure: `phpunit.pgsql.xml` is absent.

- [ ] **Create `postgres-bootstrap.php` completely.**

  ```php
  <?php

  declare(strict_types=1);

  require dirname(__DIR__, 2).'/vendor/autoload.php';

  $fail = static function (string $message): never {
      fwrite(STDERR, "P00_POSTGRES_GUARD FAIL {$message}\n");
      exit(2);
  };

  $url = getenv('DB_URL') ?: '';
  $parts = parse_url($url);
  if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['postgres', 'postgresql'], true)) {
      $fail('DB_URL scheme must be postgres or postgresql');
  }
  $database = ltrim((string) ($parts['path'] ?? ''), '/');
  if ($database === '' || ! str_ends_with($database, '_test')) {
      $fail('database name must end in _test');
  }
  if (! extension_loaded('pdo_pgsql')) {
      $fail('pdo_pgsql is unavailable');
  }

  $host = (string) ($parts['host'] ?? '');
  $port = (int) ($parts['port'] ?? 5432);
  $user = rawurldecode((string) ($parts['user'] ?? ''));
  $password = rawurldecode((string) ($parts['pass'] ?? ''));
  $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $version = (int) $pdo->query('SHOW server_version_num')->fetchColumn();
  if ($version < 160000 || $version >= 170000) {
      $fail('server major version must be 16');
  }

  fwrite(STDOUT, "P00_POSTGRES_GUARD PASS database={$database} server_version_num={$version}\n");
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

  use Illuminate\Support\Facades\DB;
  use Tests\TestCase;

  final class PostgresEnvironmentTest extends TestCase
  {
      public function test_lane_is_postgresql_16(): void
      {
          self::assertSame('pgsql', DB::connection()->getDriverName());
          $version = (int) DB::selectOne('SHOW server_version_num')->server_version_num;
          self::assertGreaterThanOrEqual(160000, $version);
          self::assertLessThan(170000, $version);
      }
  }
  ```

  Before using the service, rerun Task 0's immutable-attestation checks. Provision the service outside the repository from the exact `P00_PG_IDENTITY`, export its `DB_URL` without logging it, and record only the sanitized attestation hash, identity, `SELECT current_database()`, and `current_setting('server_version_num')`. A mutable tag, service alias, or `external-postgresql-16` sentinel is rejected.

- [ ] **Verify the safety failures and commit the lane separately.**

  ```bash
  cd backend
  DB_URL=sqlite://unsafe vendor/bin/phpunit -c phpunit.pgsql.xml --testsuite PostgreSQL; test "$?" = 2
  DB_URL=postgresql://dorzak_p00:dorzak_p00@127.0.0.1:55432/dorzak_p00 vendor/bin/phpunit -c phpunit.pgsql.xml --testsuite PostgreSQL; test "$?" = 2
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml --testsuite PostgreSQL
  ```

  Expected: both unsafe invocations exit `2`; approved PostgreSQL 16 reports one pass.

  ```bash
  git add -- backend/phpunit.pgsql.xml backend/tests/Support/postgres-bootstrap.php backend/tests/Postgres/PostgresEnvironmentTest.php
  git commit -m "test(backend): add guarded PostgreSQL 16 lane"
  ```

- [ ] **Run the two known search tests red on PostgreSQL.**

  ```bash
  cd backend
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml \
    tests/Feature/Product/ProductApiTest.php tests/Feature/Customer/CustomerApiTest.php \
    --filter='/test.*search/'
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
  php artisan test tests/Feature/Product/ProductApiTest.php tests/Feature/Customer/CustomerApiTest.php --filter=search
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml tests/Feature/Product/ProductApiTest.php tests/Feature/Customer/CustomerApiTest.php --filter=search
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml
  ```

  Expected: both focused database runs pass; full PostgreSQL reports `447 passed` (the 446 SQLite-visible tests plus the PostgreSQL environment test).

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

**Interfaces:** `ProcessBarrier::run(string $operation, array $payloads): array` starts independent PHP processes, waits for every actor to flush `READY`, sends `GO` to all input streams, applies a 15-second timeout, and returns JSON outcomes. No actor uses timing sleeps. Operations are `create-order` and `redeem-wallet`.

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
  use Illuminate\Foundation\Testing\DatabaseMigrations;
  use Tests\Support\ProcessBarrier;
  use Tests\TestCase;

  final class OrderAndStockConcurrencyTest extends TestCase
  {
      use DatabaseMigrations;

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
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml tests/Postgres/OrderAndStockConcurrencyTest.php
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
          $actors = [];
          foreach ($payloads as $payload) {
              $input = new InputStream;
              $process = new Process([
                  PHP_BINARY,
                  __DIR__.'/concurrency-worker.php',
                  $operation,
                  base64_encode(json_encode($payload, JSON_THROW_ON_ERROR)),
              ], dirname(__DIR__, 2), null, null, 15);
              $process->setInput($input);
              $process->start();
              $actors[] = [$process, $input];
          }

          foreach ($actors as [$process]) {
              $ready = $process->waitUntil(
                  static fn (string $type, string $buffer): bool => str_contains($buffer, "READY\n"),
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

  require dirname(__DIR__, 2).'/vendor/autoload.php';
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

  The complete test above uses `DatabaseMigrations` so committed rows are visible to child processes. Do not replace it with `RefreshDatabase`, an HTTP wrapper, a parent transaction, clock delays, or retry loops.

  ```bash
  cd backend
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml tests/Postgres/OrderAndStockConcurrencyTest.php
  DB_URL="$P00_PG_DB_URL" vendor/bin/phpunit -c phpunit.pgsql.xml
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

**Interfaces:** `p00-contract.json` is the sole versioned provenance for six ordered jobs, exact test totals, the 216,700-byte gzip ceiling, and the accepted-open Vite large-chunk warning. `scripts/quality/run-p00 --list` emits exactly those jobs; invoking one writes `$P00_ARTIFACT_DIR/<job>.json`. `node scripts/quality/p00.mjs aggregate <dir>` accepts exactly six passing same-SHA/same-input results. The same module measures bundles, records immutable PostgreSQL identity, builds Task 17's seven sibling evidence payloads plus manifest/schema, and validates hashes, identities, counts, debt, reviews, CI runs, and secret rejection.

- [ ] **Write the dispatcher, bundle, aggregate, and evidence tests first.**

  Create `scripts/quality/p00-contract.json` exactly:

  ~~~json
  {
    "schemaVersion": 1,
    "jobs": [
      { "name": "composer-validation", "command": "scripts/quality/run-p00 composer-validation", "testCount": 0 },
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
    }
  }
  ~~~

  Create `scripts/quality/p00.test.mjs` completely:

  ~~~js
  import assert from 'node:assert/strict';
  import { randomBytes } from 'node:crypto';
  import { mkdtempSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
  import { tmpdir } from 'node:os';
  import { join } from 'node:path';
  import test from 'node:test';
  import {
    aggregateRequiredGates,
    buildEvidence,
    contract,
    contractSha256,
    measureBundle,
    sha256,
    stableJson,
    validateEvidence,
  } from './p00.mjs';

  const sha40 = '0123456789abcdef0123456789abcdef01234567';
  const sha64 = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
  const temp = (name) => mkdtempSync(join(tmpdir(), 'dorzak-p00-' + name + '-'));
  const json = (path, value) => writeFileSync(path, stableJson(value));
  const inputs = () => ({
    contractSha256,
    runtime: { php: '8.5.1', composer: '2.8.0', node: process.versions.node, npm: '10.9.0', zlib: process.versions.zlib },
    lockfileSha256: { composer: sha64, npm: sha64 },
    postgresql: { kind: 'oci', identity: 'registry.test/postgres@sha256:' + sha64, attestationSha256: sha64 },
    playwright: { packageVersion: '1.57.0', chromiumSha256: sha64 },
  });
  const record = (job) => {
    const declared = inputs();
    const artifactNames = {
      'composer-validation': [],
      'php-style-static': [],
      sqlite: ['junit'],
      'postgresql-16': ['junit', 'postgresqlIdentity'],
      frontend: ['vitest', 'bundle', 'viteBuildLog'],
      playwright: ['playwrightJson'],
    };
    return {
      schemaVersion: 1, job: job.name, integratedSha: sha40, status: 'passed',
      command: job.command,
      exitCode: 0, retryAttempt: 1, testCount: job.testCount, failureCount: 0,
      unexplainedSkipCount: 0, durationMs: 10, contractSha256,
      inputFingerprintSha256: sha256(stableJson(declared)), inputs: declared, logSha256: sha64,
      artifacts: Object.fromEntries(artifactNames[job.name].map((name) => [name, sha64])),
    };
  };
  const resultSet = (directory, mutate = () => {}) => {
    const records = contract.jobs.map(record);
    mutate(records);
    for (const value of records) json(join(directory, value.job + '.json'), value);
    return records;
  };

  test('contract freezes ordered jobs, counts, budget, and open Vite debt', () => {
    assert.deepEqual(contract.jobs.map((job) => [job.name, job.testCount]), [
      ['composer-validation', 0], ['php-style-static', 0], ['sqlite', 446],
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
  });

  test('evidence builder validates hashes, review, two runs, debt, and secret rejection', () => {
    const root = temp('evidence');
    const local = join(root, 'local');
    mkdirSync(local);
    resultSet(local);
    const aggregate = aggregateRequiredGates(local);
    const postgresqlObservation = {
      kind: inputs().postgresql.kind,
      identity: inputs().postgresql.identity,
      attestationSha256: inputs().postgresql.attestationSha256,
      serverVersionNum: 160000,
      databaseName: 'dorzak_test',
    };
    json(join(local, 'postgresql-identity.json'), postgresqlObservation);
    json(join(local, 'bundle.json'), {
      schemaVersion: 1, gzipBytes: 210000, minifiedBytes: 500001,
      limitBytes: 216700, nodeVersion: process.versions.node, zlibVersion: process.versions.zlib,
      files: [{ path: 'assets/index.js', minifiedBytes: 500001, gzipBytes: 210000 }],
      largeChunkDebt: {
        status: 'accepted-open', thresholdBytes: 500000, affectedFiles: ['assets/index.js'],
        message: contract.bundle.warning, messageSha256: sha256(contract.bundle.warning),
        occurrenceCount: 1,
      },
    });
    const reviewJson = join(root, 'review.json');
    const reviewMarkdown = join(root, 'review.md');
    json(reviewJson, { schemaVersion: 1, baseSha: sha40, codeSha: sha40, critical: 0, important: 0, minor: [] });
    writeFileSync(reviewMarkdown, [
      '# Independent P00 Review', '', 'BASE_SHA: ' + sha40, 'CODE_SHA: ' + sha40,
      'Critical: 0', 'Important: 0', '', '## Minor', '', '- None.', '',
    ].join('\n'));
    const ci = (runId) => ({
      schemaVersion: 1, provider: 'approved-provider', runId, attempt: 1,
      integratedSha: sha40, contractSha256,
      inputFingerprintSha256: aggregate.inputFingerprintSha256,
      postgresqlObservation,
      requiredGate: { status: 'passed', jobs: 6 }, jobs: aggregate.jobs,
    });
    const ci1 = join(root, 'ci-1.json');
    const ci2 = join(root, 'ci-2.json');
    json(ci1, ci('run-1'));
    json(ci2, ci('run-2'));
    const manifest = buildEvidence({
      outputDirectory: join(root, 'output'), baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    });
    assert.equal(validateEvidence(manifest).files.length, 7);
    assert.throws(() => stableJson({ authorization: 'Bearer unsafe-value' }));
    json(reviewJson, { schemaVersion: 1, baseSha: sha40, codeSha: sha40, critical: 1, important: 0, minor: [] });
    assert.throws(() => buildEvidence({
      outputDirectory: join(root, 'rejected'), baseSha: sha40, codeSha: sha40,
      integratedSha: sha40, localDirectory: local, ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson, reviewMarkdownPath: reviewMarkdown,
    }));
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
  if [[ -n "$(git -C "$ROOT" status --short --untracked-files=normal)" ]]; then
    echo 'P00 job refused: checkout is not clean.' >&2
    exit 65
  fi
  started="$(node -p 'Date.now()')"
  log="$ARTIFACTS/$job.log"

  run_job() {
    case "$job" in
      composer-validation)
        (cd "$ROOT/backend" && composer validate --strict --no-check-publish)
        ;;
      php-style-static)
        (cd "$ROOT/backend" && vendor/bin/pint --test && composer analyse)
        ;;
      sqlite)
        (cd "$ROOT/backend" && vendor/bin/phpunit -c phpunit.xml --log-junit "$ARTIFACTS/sqlite.junit.xml")
        ;;
      postgresql-16)
        "$ROOT/scripts/quality/run-postgres-16"
        ;;
      frontend)
        (
          cd "$ROOT"
          npm run format:check
          npm run lint
          npm run typecheck
          npm run test:unit -- --reporter=json --outputFile="$ARTIFACTS/vitest.json"
          npm run build 2>&1 | tee "$ARTIFACTS/vite-build.log"
          node scripts/quality/p00.mjs bundle dist 216700 "$ARTIFACTS/vite-build.log"
        )
        ;;
      playwright)
        (cd "$ROOT" && npm run test:e2e)
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
  printf 'P00_DISPATCHER_TEST PASS jobs=6 invalid_exit=64\n'
  ~~~

  `scripts/quality/run-postgres-16`:

  ~~~bash
  #!/usr/bin/env bash
  set -euo pipefail
  : "${DB_URL:?DB_URL is required}"
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  ARTIFACTS="${P00_ARTIFACT_DIR:-$ROOT/.artifacts/p00}"
  export P00_ARTIFACT_DIR="$ARTIFACTS"
  mkdir -p "$ARTIFACTS"
  observation="$(php -r '
    $parts = parse_url(getenv("DB_URL") ?: "");
    if (!is_array($parts) || !in_array($parts["scheme"] ?? "", ["postgres", "postgresql"], true)) exit(2);
    $database = ltrim((string) ($parts["path"] ?? ""), "/");
    if ($database === "" || !str_ends_with($database, "_test")) exit(2);
    $pdo = new PDO(
      "pgsql:host=".($parts["host"] ?? "").";port=".($parts["port"] ?? 5432).";dbname=".$database,
      rawurldecode((string) ($parts["user"] ?? "")),
      rawurldecode((string) ($parts["pass"] ?? "")),
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
    echo $pdo->query("SHOW server_version_num")->fetchColumn(), "\n", $pdo->query("SELECT current_database()")->fetchColumn(), "\n";
  ')"
  server_version_num="$(printf '%s\n' "$observation" | sed -n '1p')"
  database_name="$(printf '%s\n' "$observation" | sed -n '2p')"
  node "$ROOT/scripts/quality/p00.mjs" postgres-identity "$server_version_num" "$database_name"
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
    mkdirSync,
    readFileSync,
    readdirSync,
    realpathSync,
    writeFileSync,
  } from 'node:fs';
  import { dirname, join, relative, resolve } from 'node:path';
  import { fileURLToPath } from 'node:url';
  import { gzipSync } from 'node:zlib';

  const MODULE = fileURLToPath(import.meta.url);
  const ROOT = resolve(dirname(MODULE), '../..');
  const ARTIFACTS = () => resolve(process.env.P00_ARTIFACT_DIR || join(ROOT, '.artifacts/p00'));
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
    if (typeof value === 'string') {
      invariant(!/-----BEGIN [A-Z ]*PRIVATE KEY-----/i.test(value), 'Private key at ' + path);
      invariant(!/\bbearer\s+[A-Za-z0-9._~+\/=-]+/i.test(value), 'Bearer value at ' + path);
      invariant(!/\b(?:password|token|secret|database_url)\s*[=:]\s*\S+/i.test(value), 'Secret assignment at ' + path);
      invariant(!/[a-z][a-z0-9+.-]*:\/\/[^\/\s:@]+:[^\/\s@]+@/i.test(value), 'Credential URL at ' + path);
      return;
    }
    if (Array.isArray(value)) {
      value.forEach((item, index) => rejectSecrets(item, path + '[' + index + ']'));
      return;
    }
    if (value && typeof value === 'object') {
      for (const [key, item] of Object.entries(value)) {
        invariant(!/^(authorization|password|token|secret|database_url)$/i.test(key), 'Secret key at ' + path + '.' + key);
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
    invariant(kind === 'oci' || kind === 'external-attestation', 'Unapproved PostgreSQL identity kind');
    invariant(SHA64.test(attestationSha256), 'PostgreSQL attestation hash is invalid');
    if (kind === 'oci') {
      invariant(/@sha256:[0-9a-f]{64}$/.test(identity), 'OCI PostgreSQL identity is mutable');
    } else {
      invariant(/^external:[A-Za-z0-9._/-]+@[A-Za-z0-9._:-]+#sha256:[0-9a-f]{64}$/.test(identity),
        'External PostgreSQL attestation identity is mutable');
    }
    rejectSecrets(identity, 'postgresql.identity');
    return { kind, identity, attestationSha256 };
  }

  function collectInputs() {
    const chromiumPath = command(process.execPath, [
      '-e',
      "process.stdout.write(require('playwright').chromium.executablePath())",
    ]);
    const playwrightPackage = readJson(join(ROOT, 'node_modules/@playwright/test/package.json'));
    return {
      contractSha256,
      runtime: {
        php: command('php', ['-r', 'echo PHP_VERSION;']),
        composer: version('composer', ['--version'], join(ROOT, 'backend')),
        node: process.versions.node,
        npm: version('npm', ['--version']),
        zlib: process.versions.zlib,
      },
      lockfileSha256: {
        composer: sha256(readFileSync(join(ROOT, 'backend/composer.lock'))),
        npm: sha256(readFileSync(join(ROOT, 'package-lock.json'))),
      },
      postgresql: expectedPostgresqlIdentity(),
      playwright: {
        packageVersion: playwrightPackage.version,
        chromiumSha256: sha256(readFileSync(chromiumPath)),
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
    artifactDirectory = ARTIFACTS(),
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

  function measuredCounts(job, directory) {
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
      const report = readJson(join(ROOT, 'test-results/results.json'));
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
    'composer-validation': [],
    'php-style-static': [],
    sqlite: ['junit'],
    'postgresql-16': ['junit', 'postgresqlIdentity'],
    frontend: ['vitest', 'bundle', 'viteBuildLog'],
    playwright: ['playwrightJson'],
  };

  function artifactHashes(job, directory) {
    const paths = {
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
      playwright: { playwrightJson: join(ROOT, 'test-results/results.json') },
    };
    return Object.fromEntries(
      Object.entries(paths[job] || {}).map(([name, path]) => [name, sha256(readFileSync(path))]),
    );
  }

  function assertRecord(record, expectedJob) {
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
    invariant(Object.values(record.artifacts).every((hash) => SHA64.test(hash)), 'Job artifact hash is invalid');
    invariant(record.inputFingerprintSha256 === sha256(stableJson(record.inputs)), 'Job input fingerprint mismatch');
  }

  function writeGateResult(job, exitCode, durationMs, logPath) {
    const declared = contract.jobs.find((item) => item.name === job);
    invariant(declared, 'Unknown P00 job: ' + job);
    const inputs = collectInputs();
    const counts = measuredCounts(job, ARTIFACTS());
    if (job === 'frontend') assertBundle(readJson(join(ARTIFACTS(), 'bundle.json')));
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
      logSha256: sha256(readFileSync(logPath)),
      artifacts: artifactHashes(job, ARTIFACTS()),
    };
    writeJson(join(ARTIFACTS(), job + '.json'), record);
    if (record.status !== 'passed') throw new Error('P00 job result failed validation: ' + job);
    return record;
  }

  export function aggregateRequiredGates(resultsDirectory) {
    const records = contract.jobs.map((job) => {
      const record = readJson(join(resultsDirectory, job.name + '.json'));
      assertRecord(record, job.name);
      return record;
    });
    const shas = new Set(records.map((record) => record.integratedSha));
    const fingerprints = new Set(records.map((record) => record.inputFingerprintSha256));
    invariant(shas.size === 1, 'P00 jobs used different SHAs');
    invariant(fingerprints.size === 1, 'P00 jobs used different declared inputs');
    return {
      schemaVersion: 1,
      status: 'passed',
      integratedSha: records[0].integratedSha,
      retryAttempt: 1,
      contractSha256,
      inputFingerprintSha256: records[0].inputFingerprintSha256,
      inputs: records[0].inputs,
      testCount: records.reduce((total, record) => total + record.testCount, 0),
      failureCount: 0,
      unexplainedSkipCount: 0,
      jobs: records,
    };
  }

  function postgresqlObservation(serverVersionNum, databaseName) {
    const expected = expectedPostgresqlIdentity();
    const numericVersion = Number(serverVersionNum);
    invariant(Number.isInteger(numericVersion) && numericVersion >= 160000 && numericVersion < 170000, 'PostgreSQL is not major 16');
    invariant(/_test$/.test(databaseName), 'PostgreSQL database is not a test database');
    return { ...expected, serverVersionNum: numericVersion, databaseName };
  }

  function assertPostgresqlObservation(value, expectedInputs) {
    invariant(value.kind === expectedInputs.postgresql.kind, 'PostgreSQL identity kind mismatch');
    invariant(value.identity === expectedInputs.postgresql.identity, 'PostgreSQL immutable identity mismatch');
    invariant(value.attestationSha256 === expectedInputs.postgresql.attestationSha256, 'PostgreSQL attestation mismatch');
    invariant(Number.isInteger(value.serverVersionNum)
      && value.serverVersionNum >= 160000 && value.serverVersionNum < 170000, 'PostgreSQL major is not 16');
    invariant(typeof value.databaseName === 'string' && /_test$/.test(value.databaseName), 'PostgreSQL test database is invalid');
  }

  function assertAggregate(value) {
    invariant(value.schemaVersion === 1 && value.status === 'passed', 'Aggregate did not pass');
    invariant(value.contractSha256 === contractSha256, 'Aggregate contract mismatch');
    invariant(Array.isArray(value.jobs) && value.jobs.length === contract.jobs.length, 'Aggregate job count mismatch');
    value.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
    invariant(value.jobs.every((record) => record.integratedSha === value.integratedSha), 'Aggregate SHA mismatch');
    invariant(value.jobs.every((record) => record.inputFingerprintSha256 === value.inputFingerprintSha256), 'Aggregate input mismatch');
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

  const inputsSchema = {
    type: 'object',
    additionalProperties: false,
    required: ['contractSha256', 'runtime', 'lockfileSha256', 'postgresql', 'playwright'],
    properties: {
      contractSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
      runtime: {
        type: 'object',
        additionalProperties: false,
        required: ['php', 'composer', 'node', 'npm', 'zlib'],
        properties: Object.fromEntries(
          ['php', 'composer', 'node', 'npm', 'zlib'].map((name) => [name, { type: 'string', minLength: 1 }]),
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
        required: ['kind', 'identity', 'attestationSha256'],
        properties: {
          kind: { enum: ['oci', 'external-attestation'] },
          identity: { type: 'string', minLength: 64 },
          attestationSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        },
      },
      playwright: {
        type: 'object',
        additionalProperties: false,
        required: ['packageVersion', 'chromiumSha256'],
        properties: {
          packageVersion: { type: 'string', minLength: 1 },
          chromiumSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
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
        required: ['kind', 'identity', 'attestationSha256', 'serverVersionNum', 'databaseName'],
        properties: {
          kind: { enum: ['oci', 'external-attestation'] },
          identity: { type: 'string', minLength: 64 },
          attestationSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
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
          jobs: { type: 'object', additionalProperties: { type: 'integer', minimum: 0 } },
        },
      },
      local: {
        type: 'object',
        additionalProperties: false,
        required: ['path', 'sha256', 'status', 'integratedSha', 'inputFingerprintSha256'],
        properties: {
          ...fileReferenceSchema.properties,
          status: { const: 'passed' },
          integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
          inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
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
          required: ['path', 'sha256', 'provider', 'runId', 'attempt', 'integratedSha', 'inputFingerprintSha256'],
          properties: {
            ...fileReferenceSchema.properties,
            provider: { type: 'string', minLength: 1 },
            runId: { type: 'string', minLength: 1 },
            attempt: { const: 1 },
            integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
            inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
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

  function validateCiRun(run, aggregate, observation) {
    invariant(run.schemaVersion === 1, 'CI schema mismatch');
    invariant(typeof run.provider === 'string' && run.provider.length > 0, 'CI provider is missing');
    invariant(typeof run.runId === 'string' && run.runId.length > 0, 'CI run ID is missing');
    invariant(run.attempt === 1, 'CI retry is prohibited');
    invariant(run.integratedSha === aggregate.integratedSha, 'CI SHA mismatch');
    invariant(run.contractSha256 === contractSha256, 'CI contract mismatch');
    invariant(run.inputFingerprintSha256 === aggregate.inputFingerprintSha256, 'CI input mismatch');
    invariant(run.requiredGate?.status === 'passed' && run.requiredGate?.jobs === 6, 'CI required gate failed');
    assertPostgresqlObservation(run.postgresqlObservation, aggregate.inputs);
    invariant(stableJson(run.postgresqlObservation) === stableJson(observation), 'CI PostgreSQL observation mismatch');
    invariant(Array.isArray(run.jobs) && run.jobs.length === 6, 'CI jobs missing');
    run.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
  }

  function safeSibling(directory, path) {
    invariant(typeof path === 'string' && /^[A-Za-z0-9][A-Za-z0-9._-]*$/.test(path), 'Unsafe evidence path');
    const full = resolve(directory, path);
    invariant(dirname(full) === resolve(directory), 'Evidence path escapes output');
    return full;
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

  export function buildEvidence({
    outputDirectory,
    baseSha,
    codeSha,
    integratedSha,
    localDirectory,
    ciRunPaths,
    reviewJsonPath,
    reviewMarkdownPath,
  }) {
    invariant(SHA40.test(baseSha) && SHA40.test(codeSha) && SHA40.test(integratedSha), 'Evidence SHA is invalid');
    invariant(codeSha === integratedSha, 'P00 evidence requires CODE_SHA equal INTEGRATED_SHA');
    invariant(Array.isArray(ciRunPaths) && ciRunPaths.length === 2, 'Exactly two CI runs are required');
    const localAggregate = aggregateRequiredGates(localDirectory);
    assertAggregate(localAggregate);
    invariant(localAggregate.integratedSha === integratedSha, 'Local matrix SHA mismatch');
    const observation = readJson(join(localDirectory, 'postgresql-identity.json'));
    assertPostgresqlObservation(observation, localAggregate.inputs);
    const bundle = readJson(join(localDirectory, 'bundle.json'));
    assertBundle(bundle);
    invariant(bundle.nodeVersion === localAggregate.inputs.runtime.node
      && bundle.zlibVersion === localAggregate.inputs.runtime.zlib, 'Bundle runtime identity mismatch');
    const review = readJson(reviewJsonPath);
    invariant(review.schemaVersion === 1 && review.baseSha === baseSha && review.codeSha === codeSha, 'Review range mismatch');
    invariant(review.critical === 0 && review.important === 0 && Array.isArray(review.minor), 'Review is not acceptable');
    invariant(review.minor.every((finding) => typeof finding === 'string' && finding.length > 0), 'Review minor finding is invalid');
    const reviewMarkdown = readFileSync(reviewMarkdownPath, 'utf8');
    invariant(reviewMarkdown === normalizedReviewMarkdown(review), 'Review Markdown does not normalize the JSON verdict');
    const runs = ciRunPaths.map(readJson);
    runs.forEach((run) => validateCiRun(run, localAggregate, observation));
    invariant(runs[0].runId !== runs[1].runId, 'CI run IDs must be distinct');

    invariant(!existsSync(outputDirectory), 'Evidence output directory already exists');
    mkdirSync(outputDirectory, { recursive: true });
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
    writeFileSync(join(outputDirectory, 'README.md'), readme);
    writeJson(join(outputDirectory, 'manifest.schema.json'), evidenceSchema);
    writeJson(join(outputDirectory, 'local-full-matrix.json'), localPayload);
    writeJson(join(outputDirectory, 'ci-run-1.json'), runs[0]);
    writeJson(join(outputDirectory, 'ci-run-2.json'), runs[1]);
    writeJson(join(outputDirectory, 'bundle.json'), bundle);
    copyFileSync(reviewMarkdownPath, join(outputDirectory, 'independent-review.md'));

    const names = [
      'README.md', 'manifest.schema.json', 'local-full-matrix.json', 'ci-run-1.json',
      'ci-run-2.json', 'bundle.json', 'independent-review.md',
    ];
    const files = names.map((path) => ({
      path,
      sha256: sha256(readFileSync(join(outputDirectory, path))),
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
      })),
      files,
    };
    writeJson(join(outputDirectory, 'manifest.json'), manifest);
    origins.set(manifest, resolve(outputDirectory));
    validateEvidence(join(outputDirectory, 'manifest.json'));
    return manifest;
  }

  export function validateEvidence(input) {
    const manifestPath = typeof input === 'string' ? resolve(input) : null;
    const manifest = manifestPath ? readJson(manifestPath) : input;
    const directory = manifestPath ? dirname(manifestPath) : origins.get(manifest);
    invariant(directory, 'Evidence directory is unknown');
    rejectSecrets(manifest);
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
    invariant(stableJson(manifest.files.map((item) => item.path)) === stableJson(expectedNames), 'Evidence file list mismatch');
    for (const reference of manifest.files) {
      invariant(SHA64.test(reference.sha256), 'Evidence file hash invalid');
      const path = safeSibling(directory, reference.path);
      invariant(sha256(readFileSync(path)) === reference.sha256, 'Evidence file hash mismatch: ' + reference.path);
    }
    invariant(stableJson(readJson(join(directory, 'manifest.schema.json')))
      === stableJson(evidenceSchema), 'Evidence schema artifact changed');
    const local = readJson(join(directory, 'local-full-matrix.json'));
    assertAggregate(local);
    invariant(local.integratedSha === manifest.INTEGRATED_SHA, 'Local evidence SHA mismatch');
    invariant(local.inputFingerprintSha256 === manifest.local.inputFingerprintSha256, 'Local evidence input mismatch');
    invariant(stableJson(local.inputs) === stableJson(manifest.inputs), 'Local inputs mismatch');
    assertPostgresqlObservation(local.postgresqlObservation, manifest.inputs);
    invariant(stableJson(local.postgresqlObservation)
      === stableJson(manifest.postgresqlObservation), 'Local PostgreSQL observation mismatch');
    const bundle = readJson(join(directory, 'bundle.json'));
    assertBundle(bundle);
    invariant(bundle.nodeVersion === manifest.inputs.runtime.node
      && bundle.zlibVersion === manifest.inputs.runtime.zlib, 'Bundle evidence runtime mismatch');
    invariant(bundle.gzipBytes === manifest.bundle.gzipBytes, 'Bundle measurement mismatch');
    const runs = [
      readJson(join(directory, 'ci-run-1.json')),
      readJson(join(directory, 'ci-run-2.json')),
    ];
    runs.forEach((run) => validateCiRun(run, local, manifest.postgresqlObservation));
    invariant(runs[0].runId !== runs[1].runId, 'CI run IDs are not distinct');
    invariant(runs.every((run) => run.integratedSha === manifest.INTEGRATED_SHA), 'CI integrated SHA mismatch');
    invariant(manifest.review.baseSha === manifest.BASE_SHA
      && manifest.review.codeSha === manifest.CODE_SHA, 'Review range mismatch');
    invariant(manifest.review.critical === 0 && manifest.review.important === 0, 'Review has blocking findings');
    const reviewMarkdown = readFileSync(join(directory, 'independent-review.md'), 'utf8');
    rejectSecrets(reviewMarkdown, '$.independent-review.md');
    invariant(reviewMarkdown === normalizedReviewMarkdown({
      baseSha: manifest.review.baseSha,
      codeSha: manifest.review.codeSha,
      critical: manifest.review.critical,
      important: manifest.review.important,
      minor: manifest.review.minor,
    }), 'Review Markdown and manifest verdict differ');
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
      const value = postgresqlObservation(args[0], args[1]);
      writeJson(join(ARTIFACTS(), 'postgresql-identity.json'), value);
      print('P00_POSTGRESQL PASS major=16 database=' + value.databaseName
        + ' identity_sha256=' + sha256(value.identity));
      return;
    }
    if (action === 'aggregate') {
      const directory = resolve(args[0]);
      const value = aggregateRequiredGates(directory);
      writeJson(join(directory, 'required-gates.json'), value);
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

  Expected: dispatcher prints P00_DISPATCHER_TEST PASS; all four Node subtests pass; absent evidence fails closed; diff check passes. The tests prove path escape rejection, the fixed 216700-byte budget, exact accepted-open large-chunk warning, count provenance, same-SHA/input identity, attempt one, secret and review rejection, sibling hashes, and two distinct CI runs.

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
    "$TASK13_ARTIFACTS/required-gates.json"
  WARNING_SHA="$(node -e "const c=require('./scripts/quality/p00-contract.json');const h=require('node:crypto').createHash('sha256').update(c.bundle.warning).digest('hex');process.stdout.write(h)")"
  jq -e --arg warning_sha "$WARNING_SHA" \
    '.limitBytes == 216700 and .gzipBytes <= .limitBytes
     and .largeChunkDebt.status == "accepted-open"
     and .largeChunkDebt.messageSha256 == $warning_sha
     and .largeChunkDebt.occurrenceCount == 1
     and (.largeChunkDebt.affectedFiles | length) > 0' \
    "$TASK13_ARTIFACTS/bundle.json"
  test -z "$(git status --short --untracked-files=normal)"
  ~~~

  Expected: all six jobs and aggregate exit 0 at the clean Task 13 commit; SQLite 446, PostgreSQL 450, frontend unit 8, Playwright 9, zero retries/skips/failures, one immutable PostgreSQL 16 identity, bundle at or below 216700, and the exact Vite warning retained as accepted-open debt. Only this post-commit directory is canonical Task 13 evidence. Run the global writer-boundary checks once more.

### Task 14: Stop for the owner-selected CI adapter and required-status amendment

**Files:**
- Modify: exactly one provider-native CI definition named by a later Control Room plan amendment
- External provider state: aggregate required-status configuration and two-run trigger/download commands

**Interfaces:** Logical jobs are exactly `composer-validation`, `php-style-static`, `sqlite`, `postgresql-16`, `frontend`, `playwright`, and dependent `required-gates`. Every job uses a lockfile-only install, approved exact runtimes, immutable PostgreSQL 16 image, same checkout SHA, and provider-neutral scripts from Task 13. `required-gates` is the sole required aggregate status.

- [ ] **Enforce the unresolved-input stop.**

  The current approved inputs do not select a CI provider, provider-native file, immutable action/plugin references, branch-protection API, or two-run API. Therefore no exact non-assumptive code/config snippet can be written in this plan. Stop this affected task until the Control Room durably records the provider decision and approves a focused amendment containing:

  - exact provider-native path and complete configuration;
  - immutable action/plugin/image identities;
  - exact mapping of all six jobs plus aggregate dependency;
  - exact remote push/ref semantics;
  - exact required-status API command and verification output;
  - exact commands to trigger two new runs, not retries, and download their artifacts;
  - a complete provider-output normalizer that emits the Task 13 CI-run schema
    (schemaVersion, provider, runId, attempt, integratedSha, contractSha256,
    inputFingerprintSha256, immutable PostgreSQL observation, requiredGate, and
    the six canonical job records), plus complete fixtures and fail-closed tests;
  - exact staging allowlist and focused CI commit.

  A GitHub decision may authorize a `.github/workflows/...` file; absent that decision, creating one is prohibited. A different provider requires its own native artifact.

  This is a hard serialized stop for Tasks 15, 16, and 17. Before any P00 execution starts, the exact current plan must be amended with the complete provider-specific content above, independently reviewed with zero Critical and zero Important findings, exactly owner-approved, and durably authorized by Control Room. That amendment must replace the unresolved marker, update P00_APPROVED_PLAN_COMMIT and P00_APPROVED_PLAN_SHA256 in Task 0, and retain every execution-entry prerequisite. Approval of the present correction or of plan writing does not authorize Task 0 or any execution task.

- [ ] **Record no repository change at this gate.**

  Expected current outcome: `BLOCKED_BY_OWNER_DECISION ci_provider_adapter`. There is no staging or commit before the amendment, and Tasks 15–17 must not start. No documentation-only exception bypasses this stop.

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
  - Merchant/Superadmin React owns desktop management, POS/editor interaction and governed platform operations; it never owns server authorization or provider credentials.
  - Laravel owns Dorzak control-plane and native-domain rules and exposes Dorzak DTOs.
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
      title: 'Frontend surface boundaries',
      context: 'Merchant administration and public customer rendering have different deployment, performance and authority needs.',
      decision: 'The current Vite/React app remains the P00 merchant/Superadmin surface. The public/customer surface is a separate server-rendered React deployment. Next.js is a preferred candidate only; final selection is deferred until the measured P05 spike and its ADR pass.',
      benefit: 'The recovered management surface remains stable while public rendering is measured independently.',
      cost: 'The repository will ultimately operate two explicit frontend deployment surfaces.',
      verification: 'The measured P05 frontend spike and its ADR gate verify the final public framework. P00 performs documentation only.',
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
  rg -n 'Next.js is a preferred candidate only|deferred until the measured P05 spike' docs/adr/0007-frontend-surface-boundaries.md
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
  rg -n 'npm ci|composer install.*no-progress|scripts/quality/run-p00|e2e:reset|PostgreSQL 16' README.md RUN.md backend/README.md
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
    '',
    '## Canonical quality entry points',
    '',
    '    scripts/quality/run-p00 --list',
    '    scripts/quality/run-p00 composer-validation',
    '    scripts/quality/run-p00 php-style-static',
    '    scripts/quality/run-p00 sqlite',
    '    DB_URL="$P00_PG_DB_URL" scripts/quality/run-p00 postgresql-16',
    '    scripts/quality/run-p00 frontend',
    '    scripts/quality/run-p00 playwright',
    '    node scripts/quality/p00.mjs aggregate .artifacts/p00',
    '',
    'P00_PG_DB_URL comes from the approved secret store. Never commit or print it. Measured versions, counts, hashes, immutable PostgreSQL identity, bundle result and CI identities live in [P00 evidence](docs/superpowers/evidence/p00/manifest.json); this README does not duplicate measured values.',
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
    'Playwright sets APP_ENV=e2e. The Laravel reset command accepts only backend/database/dorzak-e2e.sqlite after canonical-path, regular-file, single-link, locked-file and repeated device/inode checks, then resets and seeds Qatar/QAR fixtures. Every other database or filesystem identity is refused. Do not invoke the destructive reset manually in a production-like environment.',
    '',
    '## PostgreSQL 16 qualification',
    '',
    'Obtain DB_URL from the approved secret store without echoing it. Its database name must end _test, and the immutable identity/attestation selected in Task 0 must match:',
    '',
    '    DB_URL="$P00_PG_DB_URL" scripts/quality/run-p00 postgresql-16',
    '',
    'The bootstrap rejects a non-PostgreSQL URL, a non-test database and any server major other than 16 before migrations.',
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
    '    DB_URL="$P00_PG_DB_URL" composer test:postgres',
    '',
    'The PostgreSQL database name must end _test. Supply DB_URL at runtime from the approved secret store and never commit or print it. tests/Support/postgres-bootstrap.php rejects the wrong scheme, database identity or server major before migrations.',
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
    '# The qualification database name must end _test.',
    '# DB_URL is supplied at runtime from the approved secret store and is never committed.',
    '# tests/Support/postgres-bootstrap.php rejects a wrong scheme, database or major before migrations.',
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
  rg -n 'npm ci|composer install --no-interaction --prefer-dist --no-progress|/storage|APP_ENV=e2e|ends.*_test|Never reset' README.md RUN.md backend/README.md backend/.env.example
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

**Interfaces:** `CODE_SHA` is the clean reviewed implementation/docs commit before evidence. `INTEGRATED_SHA` is the exact code SHA pushed and tested twice; without an integration merge it equals `CODE_SHA`. The later evidence-only commit has a distinct `EVIDENCE_SHA` recorded by Control Room after commit, never embedded self-referentially in its payload. Both CI runs are new attempt-1 runs of the same `INTEGRATED_SHA` and declared inputs.

- [ ] **Require Task 14's approved/implemented adapter before closure.**

  This task cannot start under the present plan. First verify the later exact plan amendment and its Control Room record, independent review with zero Critical/Important findings, exact owner approval, updated P00_APPROVED_PLAN_COMMIT/P00_APPROVED_PLAN_SHA256, provider-native CI commit, canonical remote, sole required-gates status, immutable provider dependencies, exact approved runtimes, immutable PostgreSQL identity, complete provider normalizer, and exact two-run commands.

  ~~~bash
  test -z "$(rg -n 'BLOCKED_BY_OWNER_DECISION ci_provider_adapter' \
    docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md || true)"
  test "$(git rev-parse "$P00_APPROVED_PLAN_COMMIT^{commit}")" = "$P00_APPROVED_PLAN_COMMIT"
  test "$(git show "$P00_APPROVED_PLAN_COMMIT:docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md" \
    | shasum -a 256 | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  test "$(shasum -a 256 docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md \
    | awk '{print $1}')" = "$P00_APPROVED_PLAN_SHA256"
  ~~~

  Expected: every command exits 0. Any marker, hash, approval, review, CI-adapter or immutable-input mismatch stops Tasks 15–17 and all P00 execution. Rerun every Task 0 protected-state, MediaUrl relationship, approved-base and execution-worktree check before continuing.

- [ ] **Freeze the clean code identity.**

  ```bash
  cd "$P00_EXECUTION_WORKTREE"
  test -z "$(git status --short --untracked-files=normal)"
  CODE_SHA="$(git rev-parse HEAD)"
  printf '%s\n' "$CODE_SHA" | rg -x '[0-9a-f]{40}'
  INTEGRATED_SHA="$CODE_SHA"
  git diff --check "$P00_BASE_SHA..$CODE_SHA"
  test "$(git branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test "$(git rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  git worktree list --porcelain | rg -Fx "worktree $P00_EXECUTION_WORKTREE"
  ```

  Expected: clean named execution worktree on the approved branch, valid SHA, and no whitespace errors. Rerun the complete Task 0 protected checkout/content/MediaUrl checks here. Any later code, config, test, CI or runbook correction invalidates this identity and restarts review, fresh verification and both CI runs.

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

  The source is the clean execution worktree, so an unpushed CODE_SHA is locally obtainable without selecting or trusting a remote. --no-local forces independent Git objects and rejects hard-link optimization. Provision the approved immutable PostgreSQL 16 input and export DB_URL without logging it, then:

  ```bash
  cd "$P00_FRESH_CHECKOUT"
  P00_LOCAL_ARTIFACTS="$PWD/.artifacts/p00/fresh-$CODE_SHA"
  test ! -e "$P00_LOCAL_ARTIFACTS"
  for job in composer-validation php-style-static sqlite postgresql-16 frontend playwright; do
    P00_ARTIFACT_DIR="$P00_LOCAL_ARTIFACTS" P00_ATTEMPT=1 scripts/quality/run-p00 "$job"
  done
  node scripts/quality/p00.mjs aggregate "$P00_LOCAL_ARTIFACTS"
  jq -e --arg sha "$CODE_SHA" \
    '.status == "passed" and .integratedSha == $sha and .retryAttempt == 1
     and .failureCount == 0 and .unexplainedSkipCount == 0
     and (.jobs | length) == 6
     and all(.jobs[]; .integratedSha == $sha and .retryAttempt == 1)' \
    "$P00_LOCAL_ARTIFACTS/required-gates.json"
  jq -e \
    '.serverVersionNum >= 160000 and .serverVersionNum < 170000
     and (.databaseName | endswith("_test"))
     and (.identity | length) >= 64
     and (.attestationSha256 | test("^[0-9a-f]{64}$"))' \
    "$P00_LOCAL_ARTIFACTS/postgresql-identity.json"
  jq -e \
    '.gzipBytes <= 216700 and .limitBytes == 216700
     and .largeChunkDebt.status == "accepted-open"
     and .largeChunkDebt.occurrenceCount == 1
     and (.largeChunkDebt.affectedFiles | length) > 0' \
    "$P00_LOCAL_ARTIFACTS/bundle.json"
  test -z "$(git status --short --untracked-files=normal)"
  ```

  Expected: six jobs and aggregate pass at CODE_SHA; counts are read from the versioned contract (SQLite 446, PostgreSQL 450, frontend unit 8, browser 9); zero failures/retries/skips; the observed PostgreSQL server is major 16 under the exact immutable identity/attestation; bundle gzip is at most 216700 while the exact large-chunk warning remains measured accepted-open debt; checkout stays clean because raw outputs are ignored. Rerun all Task 0 protected-state checks at this writer boundary.

- [ ] **Push the exact integrated SHA and obtain two new CI runs.**

  ```bash
  git -C "$P00_EXECUTION_WORKTREE" push "$P00_REMOTE_NAME" "$INTEGRATED_SHA:refs/heads/$P00_EXECUTION_BRANCH"
  test "$(git ls-remote "$P00_REMOTE_NAME" "refs/heads/$P00_EXECUTION_BRANCH" | awk '{print $1}')" = "$INTEGRATED_SHA"
  ```

  Use only the complete trigger/download/normalizer commands embedded by the approved Task 14 amendment. Trigger run 1 and run 2 as distinct new runs, never reruns/retries. The amended normalizer writes exactly .artifacts/p00/ci/ci-run-1.json and ci-run-2.json in the Task 13 schema. Then require:

  ```bash
  CI_RUN_1=".artifacts/p00/ci/ci-run-1.json"
  CI_RUN_2=".artifacts/p00/ci/ci-run-2.json"
  test -f "$CI_RUN_1" && test -f "$CI_RUN_2"
  for run in "$CI_RUN_1" "$CI_RUN_2"; do
    jq -e --arg sha "$INTEGRATED_SHA" \
      '.schemaVersion == 1 and .integratedSha == $sha and .attempt == 1
       and .requiredGate.status == "passed" and .requiredGate.jobs == 6
       and (.jobs | length) == 6
       and all(.jobs[]; .integratedSha == $sha and .status == "passed"
         and .retryAttempt == 1 and .failureCount == 0 and .unexplainedSkipCount == 0)' \
      "$run"
  done
  test "$(jq -r .runId "$CI_RUN_1")" != "$(jq -r .runId "$CI_RUN_2")"
  test "$(jq -Sc '{contractSha256,inputFingerprintSha256,postgresqlObservation,jobs:[.jobs[]|{job,inputs,testCount}]}' "$CI_RUN_1" | shasum -a 256 | awk '{print $1}')" \
    = "$(jq -Sc '{contractSha256,inputFingerprintSha256,postgresqlObservation,jobs:[.jobs[]|{job,inputs,testCount}]}' "$CI_RUN_2" | shasum -a 256 | awk '{print $1}')"
  ```

  Expected: distinct provider run IDs and attempt 1; exact same integrated SHA, contract, PHP/Node/Composer/npm/zlib/Playwright/Chromium identities, Composer/npm lock hashes, immutable PostgreSQL identity/attestation/observed major, ordered job names/counts and passing aggregate. Any retry metadata, provider-normalizer ambiguity or identity mismatch invalidates that run and returns to the Task 14 amendment; it may not be repaired in Task 17.

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

  Expected: the absent manifest fails closed, then the deterministic builder creates exactly the eight declared files and prints P00_EVIDENCE_BUILT files=7 (seven manifest-referenced siblings plus manifest). Validation prints P00_EVIDENCE PASS with the literal base/code/integrated identities, two runs and seven sibling references. The generated schema has additionalProperties=false at every manifest object boundary; the validator verifies each sibling path/hash, stable input identity, exact contract counts, immutable PostgreSQL observation, browser identity, bundle limit and warning debt, review range/verdict, two distinct attempt-one CI runs, and recursive secret rejection. The manifest never embeds the later EVIDENCE_SHA.

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
  ```

  The Control Room durably records `BASE_SHA`, `CODE_SHA`, `INTEGRATED_SHA`, `EVIDENCE_SHA`, evidence payload SHA-256, the two CI run IDs, and the owner acceptance decision. Do not run CI on the evidence-only commit and mislabel it as the tested code SHA.

- [ ] **Perform the terminal repository and protected-state check, then stop.**

  ```bash
  media=backend/app/Support/MediaUrl.php
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" status --short --untracked-files=normal)"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" diff --cached --name-only)"
  test "$(realpath "$P00_EXECUTION_WORKTREE")" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" branch --show-current)" = "$P00_EXECUTION_BRANCH"
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
| Guarded resettable fixture, Laravel+Vite health, auth setup, real login, seven journeys, one worker/zero retries | Tasks 4–5 |
| DTO/API/auth/settings/money/protected shell/accessible form unit coverage | Tasks 6–8 |
| Frontend format/lint/type/unit/build/bundle | Tasks 6–8, 13, 17 |
| Independent 16-file Pint cleanup and bounded Larastan debt | Tasks 9–10 |
| Full PostgreSQL 16 suite and portable existing contract | Task 11 |
| Process-level order/stock/wallet concurrency with barriers | Task 12 |
| Provider-neutral jobs, aggregate, counts/versions/hashes | Task 13 |
| Provider-native thin wrapper and required status without assumption | Task 14 stop gate and later approved amendment |
| CONTEXT, seven ADRs, accurate setup/runbook/recovery | Tasks 15–16 |
| Clean `CODE_SHA`, independent review, fresh checkout, two same-SHA CI runs, sanitized evidence and distinct SHA identities | Task 17 |

## Plan Self-Review Gate

Before plan approval and again after any approved amendment:

- [ ] Trace every design Section 6–12 clause through the coverage table and exact task acceptance command.
- [ ] Scan this file for banned indefinite markers, ellipses used as instructions, vague comparison phrases, broad staging, unbounded error repair, retries/quarantines/skips, and provider assumptions; require no matches.
- [ ] Check every named path exists now or has one explicit creation task before first use.
- [ ] Check interface names across PHP, TypeScript, shell, JSON and evidence (`e2e:reset`, `ProcessBarrier::run`, six job names, SHA identities) for exact consistency.
- [ ] Check tasks are numbered 0–17 exactly once and retain mandatory order.
- [ ] Check each writer has one staging allowlist, shared manifests/lockfiles/config/evidence have one serialized owner, and no command stages the original user-owned paths accidentally.
- [ ] Check Task 14 remains a hard stop and Task 0/17 state that plan approval is not execution authority.

Execution handoff occurs only after this exact plan is independently reviewed, owner-approved, and durably authorized by the Control Room. Until then, stop at plan review.

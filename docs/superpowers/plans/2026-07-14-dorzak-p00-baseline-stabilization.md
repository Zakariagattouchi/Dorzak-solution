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
- The original user checkout must retain exactly the registered 16-entry path/status manifest. At every writer boundary run:

  ```bash
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | wc -l | tr -d ' ')" = "16"
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = "a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa"
  ```

  Expected: both commands exit `0`. Any mismatch stops the task without staging.
- After every focused commit, require an empty index and clean execution worktree:

  ```bash
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
| `P00_PG_IMAGE` | Immutable PostgreSQL 16 image reference including digest, or `external-postgresql-16` |
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
- Read: `docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md:401`
- Read: the approved plan at this path
- Modify: none

**Interface:** This gate consumes the variables defined above and emits only `P00_EXECUTION_GATE PASS base=<sha> worktree=<path>`. It never creates a branch, worktree, patch, commit, or repository file.

- [ ] **Verify formal authorities, not the planning exception.**

  The durable record must separately approve: this exact plan/hash; the product baseline at `cc4085cbca11e89257ae8535438db6cfe3dd75cc`; the roadmap with `069f4833190c75866494e7ba51bff3021070c0bf`; canonical remote; provider; exact PHP/Node pins; completed MediaUrl preservation; resulting base/worktree; and P00 execution. Search the record and inspect it manually:

  ```bash
  test -f "$P00_CONTROL_RECORD"
  shasum -a 256 docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md
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
  test -n "$P00_PG_IMAGE"
  ```

  Expected: every command exits `0`. No semantic version range is accepted.

- [ ] **Verify the completed preservation artifact against the reviewed diff.**

  For `dedicated_commit`:

  ```bash
  test -z "$P00_MEDIA_ARTIFACT_PATH"
  git cat-file -e "$P00_MEDIA_ARTIFACT_ID^{commit}"
  test "$(git show --format= --binary --full-index "$P00_MEDIA_ARTIFACT_ID" -- backend/app/Support/MediaUrl.php | shasum -a 256 | awk '{print $1}')" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  test "$(git diff-tree --no-commit-id --name-only -r "$P00_MEDIA_ARTIFACT_ID")" = "backend/app/Support/MediaUrl.php"
  git merge-base --is-ancestor "$P00_MEDIA_ARTIFACT_ID" "$P00_BASE_SHA"
  ```

  For `reviewed_patch`:

  ```bash
  test -f "$P00_MEDIA_ARTIFACT_PATH"
  test "$(shasum -a 256 "$P00_MEDIA_ARTIFACT_PATH" | awk '{print $1}')" = "$P00_MEDIA_ARTIFACT_ID"
  test "$P00_MEDIA_ARTIFACT_ID" = "$P00_MEDIA_REVIEWED_DIFF_SHA256"
  rg -n 'disposable verification checkout|applied successfully|resulting diff matched' "$P00_CONTROL_RECORD"
  ```

  Expected: the selected branch exits `0`. The patch branch verifies a separately approved disposable application target recorded by Control Room; it never applies the patch to the named clean execution worktree.

- [ ] **Verify remote, base, named worktree, and protected user state.**

  ```bash
  test "$(git remote get-url "$P00_REMOTE_NAME")" = "$P00_REMOTE_URL"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse --show-toplevel)" = "$P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_EXECUTION_WORKTREE" rev-parse HEAD)" = "$P00_BASE_SHA"
  test "$(git -C "$P00_EXECUTION_WORKTREE" branch --show-current)" = "$P00_EXECUTION_BRANCH"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" status --short --untracked-files=normal)"
  git worktree list --porcelain | rg -F "worktree $P00_EXECUTION_WORKTREE"
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | wc -l | tr -d ' ')" = 16
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa
  ```

  Expected: every command exits `0`. If an existing or stale worktree is named, stop.

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

**Interfaces:** `php artisan e2e:reset` exits `1` unless `APP_ENV=e2e`, the default connection is SQLite, and the resolved database is exactly `backend/database/dorzak-e2e.sqlite`. Only then may it create the file, run `migrate:fresh --force`, and seed `E2ESeeder`. The fixture owns `owner@e2e.dorzak.test / e2e-password`, one Qatar/QAR PRO store, and one deterministic variant product. It calls `PlanSeeder`, never `DemoSeeder`.

- [ ] **Write both tests before the command and seeder.**

  `E2EResetGuardTest.php`:

  ```php
  <?php

  namespace Tests\Feature\E2E;

  use App\Console\Commands\ResetE2eDatabase;
  use Tests\TestCase;

  final class E2EResetGuardTest extends TestCase
  {
      public function test_only_the_named_e2e_sqlite_database_is_accepted(): void
      {
          $safe = database_path('dorzak-e2e.sqlite');

          self::assertFalse(ResetE2eDatabase::isSafe('testing', 'sqlite', $safe));
          self::assertFalse(ResetE2eDatabase::isSafe('e2e', 'pgsql', 'dorzak_e2e'));
          self::assertFalse(ResetE2eDatabase::isSafe('e2e', 'sqlite', ':memory:'));
          self::assertFalse(ResetE2eDatabase::isSafe('e2e', 'sqlite', database_path('database.sqlite')));
          self::assertTrue(ResetE2eDatabase::isSafe('e2e', 'sqlite', $safe));
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
          self::assertSame('PRO', $store->subscription->plan->value);
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

  final class ResetE2eDatabase extends Command
  {
      protected $signature = 'e2e:reset';

      protected $description = 'Reset only the named Dorzak E2E SQLite database';

      public static function isSafe(string $environment, string $connection, string $database): bool
      {
          return $environment === 'e2e'
              && $connection === 'sqlite'
              && realpath(dirname($database)) === realpath(database_path())
              && basename($database) === 'dorzak-e2e.sqlite';
      }

      public function handle(): int
      {
          $connection = (string) config('database.default');
          $database = (string) config("database.connections.{$connection}.database");

          if (! self::isSafe($this->laravel->environment(), $connection, $database)) {
              $this->error('E2E reset refused: environment or database identity is unsafe.');

              return self::FAILURE;
          }

          if (! is_file($database) && touch($database) === false) {
              $this->error('E2E reset refused: database file could not be created.');

              return self::FAILURE;
          }

          if ($this->call('migrate:fresh', ['--force' => true]) !== self::SUCCESS) {
              return self::FAILURE;
          }

          if ($this->call('db:seed', ['--class' => E2ESeeder::class, '--force' => true]) !== self::SUCCESS) {
              return self::FAILURE;
          }

          $this->info('E2E_RESET PASS database=dorzak-e2e.sqlite');

          return self::SUCCESS;
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

  Expected: `2 passed`; the unsafe command prints the refusal and exits `1`; full SQLite reports `446 passed`.

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
  npm run test:e2e -- --project=setup --project=login-smoke
  npm run test:e2e -- --project=chromium
  npm run test:e2e
  node -e "const r=require('./test-results/results.json'); if(r.stats.unexpected!==0||r.stats.skipped!==0) process.exit(1)"
  ```

  Expected: setup plus smoke pass; Chromium reports `7 passed`; full run reports nine passes, one worker, zero retries, zero skipped/unexpected tests. Laravel and Vite health failures remain distinct from auth and journey failures.

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
    "bundle:check": "node scripts/quality/check-initial-bundle.mjs dist 216700",
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

- [ ] **Create focused failing tests before the pure money export.**

  The five files each contain one named test with these exact assertions:

  ```ts
  // adapters.test.ts
  expect(toProduct({ id: 7, name: 'Hoodie', price: '49.99', cost: null, stock: '10', min_stock: 2, track_stock: true, image_url: '/storage/a.jpg' })).toMatchObject({
    id: '7', price: 49.99, cost: 0, stock: 10, imageUrl: '/storage/a.jpg',
  });
  expect(toOrder({ id: 2, order_number: 'ORD-1000', customer_name: 'Walk-in', subtotal: '49.99', discount: 0, tax_amount: 0, total: '49.99', status: 'COMPLETE', payment_method: 'CASH', currency_code: 'QAR', items: [] })).toMatchObject({
    id: 'ORD-1000', total: 49.99, currencyCode: 'QAR',
  });
  expect(settingsGroupPayloads({ ...initialAccountInfo, currency: 'QAR', symbolPlacement: 'BEFORE' }, { currency: 'QAR' })).toEqual([
    ['currency', { currency: 'QAR', symbol_placement: 'BEFORE' }],
  ]);
  ```

  ```ts
  // apiClient.test.ts; jsdom URL is /login so no navigation branch runs.
  window.history.replaceState({}, '', '/login');
  setToken('expired');
  vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(JSON.stringify({ message: 'Unauthenticated.', code: 'AUTH_REQUIRED' }), {
    status: 401, headers: { 'Content-Type': 'application/json' },
  })));
  await expect(request('/auth/me')).rejects.toMatchObject({ status: 401, message: 'Unauthenticated.', code: 'AUTH_REQUIRED' });
  expect(getToken()).toBeNull();
  ```

  ```ts
  // authStore.test.ts; authApi is a vi.mocked module.
  setToken(null);
  await useAuthStore.getState().bootstrap();
  expect(useAuthStore.getState().status).toBe('guest');
  setToken('valid');
  vi.mocked(authApi.me).mockResolvedValue({ data: { user, store, role: 'OWNER', abilities: ['orders.create'] } });
  await useAuthStore.getState().bootstrap();
  expect(useAuthStore.getState()).toMatchObject({ status: 'authenticated', role: 'OWNER' });
  vi.mocked(authApi.login).mockRejectedValue({ status: 422, message: 'Invalid', errors: { email: ['Bad credentials'] } });
  await expect(useAuthStore.getState().login('bad@example.test', 'bad')).rejects.toBeTruthy();
  expect(useAuthStore.getState().error).toBe('Bad credentials');
  ```

  ```ts
  // settingsStore.test.ts; settingsApi is a vi.mocked module.
  vi.mocked(settingsApi.get).mockResolvedValue({ data: { general: { business_name: 'Dorzak', language: 'ar' }, currency: { currency: 'QAR', currency_symbol: 'QAR', symbol_placement: 'BEFORE' } } });
  await useSettingsStore.getState().fetchSettings();
  expect(useSettingsStore.getState().accountInfo.currency).toBe('QAR');
  expect(document.documentElement).toHaveAttribute('lang', 'ar');
  expect(document.documentElement).toHaveAttribute('dir', 'rtl');
  vi.mocked(settingsApi.update).mockResolvedValue({ data: { general: { business_name: 'Dorzak', language: 'en' }, currency: { currency: 'QAR', currency_symbol: 'QAR', symbol_placement: 'AFTER' } } });
  await useSettingsStore.getState().updateSettings({ currency: 'QAR', symbolPlacement: 'AFTER' });
  expect(settingsApi.update).toHaveBeenCalledWith('currency', { currency: 'QAR', symbol_placement: 'AFTER' });
  ```

  ```ts
  // useMoney.test.ts
  const before = { currency: 'QAR', currencySymbol: 'QAR', symbolPlacement: 'BEFORE' } as const;
  expect(formatMoney(49.99, before)).toBe('QAR 49.99');
  expect(formatMoney(49.99, { ...before, symbolPlacement: 'AFTER' })).toBe('49.99 QAR');
  expect(formatMoney(49.99, before, 2, 'USD')).toBe('$49.99');
  expect(formatMoney(49.99, before, 0)).toBe('QAR 50');
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

  `AppShell.test.tsx` must mock the five stores and `useOrderPolling`, render under `MemoryRouter`, and assert four states in one test: idle/loading exposes `getByRole('status', { name: 'Loading your store…' })`; guest reaches `/login`; store-less platform admin reaches `/platform`; authenticated merchant calls `fetchSettings`, `fetchProducts`, `fetchCategories`, `fetchCustomers`, and `fetchOrders` once.

  `AccessibleForm.test.tsx` renders `TextInput label="Name"`, `SelectInput label="Currency"` with QAR/USD, `ToggleSwitch label="Online store"`, and `CheckboxInput label="Email receipts"`. It asserts `getByLabelText` finds every control, Space toggles both boolean controls, error text is referenced through `aria-describedby`, and `await expectNoA11yViolations(container)` passes.

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

  Toggle control, preserving `checked`, `onChange`, `label`, and `description` props:

  ```tsx
  <button
    type="button"
    role="switch"
    aria-checked={checked}
    aria-label={label}
    onClick={() => onChange(!checked)}
    className={`toggle-switch ${checked ? 'active' : ''}`}
  >
    <span aria-hidden="true" className="toggle-thumb" />
  </button>
  ```

  Checkbox control, preserving its public props:

  ```tsx
  <label
    className={`checkbox-wrapper ${className}`}
    onClick={(event) => event.stopPropagation()}
  >
    <input
      type="checkbox"
      checked={checked}
      onChange={(event) => onChange(event.target.checked)}
      style={{ position: 'absolute', width: 1, height: 1, opacity: 0 }}
    />
    <div aria-hidden="true" className={`checkbox-custom ${checked ? 'checked' : ''}`}>
      {checked && <AppIcon name="check" size={12} color="#ffffff" />}
    </div>
    {label && <span className="form-label" style={{ margin: 0 }}>{label}</span>}
  </label>
  ```

  Retain the surrounding toggle label/description markup and the current `toggle-wrapper`, `flex flex-col`, and `form-label` classes. The replacements above retain the current control-class tokens and the `(checked: boolean) => void` interfaces.

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

  Create `phpunit.pgsql.xml` from the tracked `phpunit.xml` using only these deterministic transformations: change `bootstrap` to `tests/Support/postgres-bootstrap.php`; add `<directory>tests/Postgres</directory>` as suite `PostgreSQL`; force `DB_CONNECTION=pgsql`; remove the SQLite `DB_DATABASE` and blank `DB_URL` entries. Preserve every other Unit/Feature suite and PHP environment entry byte-for-byte.

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

  `P00_PG_IMAGE` must be an approved immutable digest or `external-postgresql-16`. Provision the service outside the repository, export its exact `DB_URL`, and record `SELECT current_database(), current_setting('server_version_num')` plus image digest where applicable. Never print the password.

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

  The test class uses `DatabaseMigrations`, sets `StoreContext`, and contains exactly:

  1. stock 10 plus two simultaneous quantity-one completed orders: both `ok=true`; sorted numbers are `ORD-1000`, `ORD-1001`; two orders; stock 8; two `SALE` movements totaling `-2`;
  2. stock 1 plus the same actors: one success; one `INSUFFICIENT_STOCK`; one order; stock 0; one `SALE` movement of `-1`;
  3. wallet credit 10 plus two simultaneous redemptions of 8: one success; one `INSUFFICIENT_CREDIT`; balance `2.00`; exactly one negative ledger entry of `-8.00`.

  Each order payload is exactly `store_id`, `product_id`, `quantity=1`; each wallet payload is `store_id`, `customer_id`, `amount=8`. Assertions query `Order`, `Product`, `StockMovement`, `WalletAccount`, and `WalletEntry` after both child processes exit.

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

- [ ] **Implement the test fixture/assertions exactly as enumerated, then verify.**

  Use `Product::factory()->for($store)` and `Customer::factory()->for($store)`; call `WalletService::credit($customer, 10, 'P00 seed')` before the wallet actors. Do not use HTTP, transactions in the parent test, clock delays, or retry loops.

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
- Create: `scripts/quality/run-p00`
- Create: `scripts/quality/test-run-p00.sh`
- Create: `scripts/quality/run-postgres-16`
- Create: `scripts/quality/write-gate-result.mjs`
- Create: `scripts/quality/check-initial-bundle.mjs`
- Create: `scripts/quality/check-initial-bundle.test.mjs`
- Create: `scripts/quality/required-gates.mjs`
- Create: `scripts/quality/required-gates.test.mjs`
- Create: `scripts/quality/validate-p00-evidence.mjs`
- Create: `scripts/quality/validate-p00-evidence.test.mjs`

**Interfaces:** `scripts/quality/run-p00 --list` emits exactly six provider-neutral jobs; invoking one runs the same local/CI command and writes `.artifacts/p00/<job>.json`. `required-gates.mjs` accepts exactly those six passing results at one SHA and writes `required-gates.json`. Bundle measurement parses initial module scripts/modulepreloads, uses pinned Node zlib level 9, and enforces 216,700 gzip bytes. The evidence validator enforces Task 17's schema and sanitized identities.

- [ ] **Write the dispatcher, bundle, aggregate, and evidence tests first.**

  `test-run-p00.sh` asserts exact `--list` output and invalid job exit `64`. Bundle tests create temporary dist trees for one entry, entry plus modulepreload, path escape/missing asset, and over-budget. Required-gates tests create six result JSON files and prove rejection of a missing job, duplicate job, mixed SHA, nonzero exit, attempt other than `1`, skip count above `0`, and non-passing status. Evidence tests prove missing SHA/runtime/lock/test/bundle/review/CI fields and secret patterns fail.

  ```bash
  sh scripts/quality/test-run-p00.sh
  node --test scripts/quality/check-initial-bundle.test.mjs scripts/quality/required-gates.test.mjs scripts/quality/validate-p00-evidence.test.mjs
  ```

  Expected failure: implementation modules/scripts are absent.

- [ ] **Create the exact six-job dispatcher.**

  ```bash
  #!/usr/bin/env bash
  set -euo pipefail
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  ARTIFACTS="$ROOT/.artifacts/p00"
  mkdir -p "$ARTIFACTS"
  jobs=(composer-validation php-style-static sqlite postgresql-16 frontend playwright)

  if [[ "${1:-}" == "--list" ]]; then printf '%s\n' "${jobs[@]}"; exit 0; fi
  job="${1:-}"
  if [[ ! " ${jobs[*]} " =~ " ${job} " ]]; then echo "Unknown P00 job: ${job}" >&2; exit 64; fi
  started="$(node -p 'Date.now()')"
  log="$ARTIFACTS/$job.log"
  set +e
  case "$job" in
    composer-validation)
      (cd "$ROOT/backend" && composer validate --strict --no-check-publish) 2>&1 | tee "$log" ;;
    php-style-static)
      (cd "$ROOT/backend" && vendor/bin/pint --test && composer analyse) 2>&1 | tee "$log" ;;
    sqlite)
      (cd "$ROOT/backend" && vendor/bin/phpunit -c phpunit.xml --log-junit "$ARTIFACTS/sqlite.junit.xml") 2>&1 | tee "$log" ;;
    postgresql-16)
      "$ROOT/scripts/quality/run-postgres-16" 2>&1 | tee "$log" ;;
    frontend)
      (cd "$ROOT" && npm run format:check && npm run lint && npm run typecheck && npm run test:unit -- --reporter=json --outputFile=.artifacts/p00/vitest.json && npm run build && npm run bundle:check) 2>&1 | tee "$log" ;;
    playwright)
      (cd "$ROOT" && npm run test:e2e) 2>&1 | tee "$log" ;;
  esac
  status="${PIPESTATUS[0]}"
  set -e
  finished="$(node -p 'Date.now()')"
  node "$ROOT/scripts/quality/write-gate-result.mjs" "$job" "$status" "$((finished-started))" "$log"
  exit "$status"
  ```

  `run-postgres-16`:

  ```bash
  #!/usr/bin/env bash
  set -euo pipefail
  : "${DB_URL:?DB_URL is required}"
  ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
  exec "$ROOT/backend/vendor/bin/phpunit" \
    --configuration "$ROOT/backend/phpunit.pgsql.xml" \
    --log-junit "$ROOT/.artifacts/p00/postgresql-16.junit.xml"
  ```

  Make both scripts executable. Add `.artifacts/` to `.gitignore`.

- [ ] **Implement deterministic job evidence.**

  `write-gate-result.mjs` must use `execFileSync` without a shell to obtain `git rev-parse HEAD`, PHP/Composer/Node/npm/zlib versions, Playwright package version, and SHA-256 for `backend/composer.lock` and `package-lock.json`. Parse:

  - `sqlite.junit.xml` and `postgresql-16.junit.xml` root `tests`, `failures`, `errors`, `skipped` attributes;
  - `vitest.json` fields `numTotalTests`, `numFailedTests`, `numPendingTests`;
  - `test-results/results.json` fields `stats.expected`, `unexpected`, `flaky`, `skipped`;
  - `.artifacts/p00/bundle.json` for the frontend job.

  Write this exact shape using stable key order and a trailing newline:

  ```json
  {
    "schemaVersion": 1,
    "job": "sqlite",
    "integratedSha": "0123456789abcdef0123456789abcdef01234567",
    "status": "passed",
    "exitCode": 0,
    "retryAttempt": 1,
    "testCount": 446,
    "failureCount": 0,
    "unexplainedSkipCount": 0,
    "durationMs": 1,
    "runtime": {},
    "lockfileSha256": {},
    "logSha256": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"
  }
  ```

  The illustrative SHA/duration strings above are schema examples only; the script writes measured literals. `retryAttempt` is `Number(process.env.P00_ATTEMPT ?? '1')`. Non-test jobs use zero for test/failure/skip counts. Never record environment variables, URLs containing credentials, tokens, or passwords.

- [ ] **Implement and test the bundle gate.**

  `check-initial-bundle.mjs` exports:

  ```ts
  measureInitialJavaScript(distDirectory: string): {
    files: Array<{ path: string; minifiedBytes: number; gzipBytes: number }>;
    minifiedBytes: number;
    gzipBytes: number;
    nodeVersion: string;
    zlibVersion: string;
  };
  assertBundleBudget(result, budgetGzipBytes = 216700): void;
  ```

  Implementation rules are exact: read `dist/index.html`; collect unique `.js` URLs from `<script type="module" src>` and `<link rel="modulepreload" href>` in document order; strip a leading slash only; reject `..`, an empty set, or missing/non-file assets; gzip each file independently with `gzipSync(bytes, { level: 9 })`; sum raw and gzip sizes; require `process.versions.node === readFileSync('.node-version','utf8').trim()`; write `.artifacts/p00/bundle.json`; print `P00_BUNDLE PASS gzip=<n> limit=216700 node=<version> zlib=<version>` or exit `1` without changing the limit.

  ```bash
  node --test scripts/quality/check-initial-bundle.test.mjs
  npm run build
  npm run bundle:check
  ```

  Expected: unit tests pass; measured gzip is at most `216700` under the approved Node/zlib pin. If it is larger, preserve the JSON/log and stop; do not raise the limit or start broad route splitting.

- [ ] **Implement the required-gates and evidence validators.**

  `required-gates.mjs <results-directory>` reads JSON files, requires exactly one of each six job names, one 40-hex SHA, `status=passed`, `exitCode=0`, `retryAttempt=1`, `failureCount=0`, `unexplainedSkipCount=0`, and writes/prints:

  ```text
  P00_REQUIRED_GATES PASS jobs=6 sha=$INTEGRATED_SHA
  ```

  `validate-p00-evidence.mjs <manifest>` validates the Task 17 schema, verifies every referenced relative file/hash, requires two distinct CI run IDs at attempt 1 on one integrated SHA and identical declared inputs, requires review Critical/Important counts both zero, and recursively rejects case-insensitive secret patterns for authorization headers, bearer values, password assignments, token assignments, secret assignments, private keys, and database URLs.

- [ ] **Run every provider-neutral job on the same clean SHA.**

  ```bash
  sh scripts/quality/test-run-p00.sh
  node --test scripts/quality/check-initial-bundle.test.mjs scripts/quality/required-gates.test.mjs scripts/quality/validate-p00-evidence.test.mjs
  for job in composer-validation php-style-static sqlite postgresql-16 frontend playwright; do P00_ATTEMPT=1 scripts/quality/run-p00 "$job"; done
  node scripts/quality/required-gates.mjs .artifacts/p00
  ```

  Expected: all six jobs and aggregate exit `0`; SQLite `446`, PostgreSQL `450`, frontend unit `8`, Playwright `9`, zero retries/skips/failures, bundle at or below budget, all result SHAs identical.

- [ ] **Stage only the quality-interface files and commit.**

  ```bash
  git add -- .gitignore \
    scripts/quality/run-p00 scripts/quality/test-run-p00.sh scripts/quality/run-postgres-16 \
    scripts/quality/write-gate-result.mjs \
    scripts/quality/check-initial-bundle.mjs scripts/quality/check-initial-bundle.test.mjs \
    scripts/quality/required-gates.mjs scripts/quality/required-gates.test.mjs \
    scripts/quality/validate-p00-evidence.mjs scripts/quality/validate-p00-evidence.test.mjs
  test "$(git diff --cached --name-only | wc -l | tr -d ' ')" = 11
  git commit -m "build: add provider-neutral P00 quality gates"
  ```

  Run the global writer-boundary checks. Raw `.artifacts/` remains ignored and is never committed.

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
  - exact staging allowlist and focused CI commit.

  A GitHub decision may authorize a `.github/workflows/...` file; absent that decision, creating one is prohibited. A different provider requires its own native artifact. Task 15 may proceed for local documentation work, but Task 17 cannot declare P00 complete until this task is amended, reapproved, implemented, and verified.

- [ ] **Record no repository change at this gate.**

  Expected current outcome: `BLOCKED_BY_OWNER_DECISION ci_provider_adapter`. There is no staging or commit before the amendment.

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
  test "$(find docs/adr -maxdepth 1 -name '*.md' 2>/dev/null | wc -l | tr -d ' ')" = 7
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

  Every file begins `# ADR NNNN: <title>`, then `## Status` with `Accepted`, followed by the required headings. The `Decision` sections contain these exact clauses:

  | ADR | Required decision clauses |
  |---|---|
  | 0001 | Dorzak owns identity, plans, experience, orchestration, public content, vertical-native truth and governed support. ERPNext owns paid operational/financial facts after approved cutover. A field/fact has one authority. P00 keeps current Laravel commerce only as the pre-cutover recovered baseline. |
  | 0002 | One paid Organization maps to one isolated Frappe site/database boundary. A site may serve one or many Locations of that Organization. Enterprise never requires a minimum Location count. Organization/Location migration begins in P01/P02, not P00. |
  | 0003 | Dorzak remains a Laravel modular monolith plus external systems behind narrow versioned interfaces. ERPNext, payment, storage and messaging credentials/shapes never reach UI/domain modules. A local transaction never spans a remote call. |
  | 0004 | Dorzak has one complete public launch. P00–P19 are internal verified milestones. No tier/category is publicly sold before every advertised journey passes the global gate. |
  | 0005 | Published plan versions and entitlement matrices are immutable. A new commercial change creates a new version and explicit transition; runtime/server/worker/ERP enforcement and public claims resolve the same version. P03 owns implementation. |
  | 0006 | Every commerce domain uses an explicit expand/backfill/parity/cutover/contract sequence. At cutover the new authority becomes the sole writer; rollback uses recorded reconciliation and never long-lived dual writes. P04 owns ERP commerce migration. |
  | 0007 | The current Vite/React app remains the P00 merchant/Superadmin surface. The public/customer surface is a separate server-rendered React deployment. Next.js is a preferred candidate only; final selection is deferred until the measured P05 spike and its ADR pass. |

  `Consequences` names one benefit and one cost. `Verification` names the future packet/gate above and states P00 performs documentation only. `References` links the approved complete-launch baseline, technical roadmap, and P00 design by repository-relative path.

- [ ] **Verify filenames, headings, and the deferred frontend decision.**

  ```bash
  test "$(find docs/adr -maxdepth 1 -name '*.md' | wc -l | tr -d ' ')" = 7
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

- [ ] **Create the root README with exact entry commands.**

  It must identify `CONTEXT.md`, the P00 design/plan, prerequisites from `.php-version`/`.node-version`, and these command blocks:

  ```bash
  cd backend && composer install --no-interaction --prefer-dist --no-progress
  cd .. && npm ci
  scripts/quality/run-p00 --list
  scripts/quality/run-p00 composer-validation
  scripts/quality/run-p00 php-style-static
  scripts/quality/run-p00 sqlite
  DB_URL="$P00_PG_DB_URL" scripts/quality/run-p00 postgresql-16
  scripts/quality/run-p00 frontend
  scripts/quality/run-p00 playwright
  node scripts/quality/required-gates.mjs .artifacts/p00
  ```

  Explain that the PostgreSQL URL is supplied through a secret store and must never be committed or printed. Link measured counts and versions to `docs/superpowers/evidence/p00/manifest.json` rather than hard-coding them.

- [ ] **Replace RUN guidance with verified manual and E2E flows.**

  Include:

  ```bash
  cd backend
  composer install --no-interaction --prefer-dist --no-progress
  php artisan serve --host=127.0.0.1 --port=8000

  cd ..
  npm ci
  npm run dev -- --host 127.0.0.1 --strictPort
  ```

  State that Vite proxies both `/api` and `/storage`; every production frontend origin must serve/proxy origin-relative `/storage/*` to Laravel. The E2E section uses only `npm run test:e2e`; it explains that Playwright sets `APP_ENV=e2e`, validates `backend/database/dorzak-e2e.sqlite`, resets/seeds, and refuses any other identity. Do not recommend manually running the destructive command with a production-like environment.

- [ ] **Correct backend README and environment wording.**

  Remove all stale test counts/version rationales. Document:

  ```bash
  composer validate --strict --no-check-publish
  vendor/bin/pint --test
  composer analyse
  composer test:sqlite
  DB_URL="$P00_PG_DB_URL" composer test:postgres
  ```

  Replace the `.env.example` statement that tests always use SQLite with: SQLite in-memory is the fast PHPUnit lane; PostgreSQL 16 is the complete qualification lane; its database must end `_test`; `DB_URL` is supplied at runtime and never committed; `tests/Support/postgres-bootstrap.php` rejects a wrong database/major before migrations.

- [ ] **Add the exact recovery protocol to RUN.md.**

  On any failed boundary: stop new writers; retain `.artifacts/p00` and logs; record failing SHA/command/runtime; compare the original 16-entry manifest; return to the owning task; add/fix the narrow regression; rerun that boundary and downstream gates. Never reset/clean the user checkout, delete failure evidence, reduce a gate, retry CI into green, repurpose the stale worktree, or start P01.

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

  Verify the amended plan/approval, provider-native CI commit, canonical remote, required `required-gates` status, immutable provider dependencies, exact approved runtimes, and immutable PostgreSQL 16 image. If Task 14 still emits `BLOCKED_BY_OWNER_DECISION ci_provider_adapter`, stop Task 17 without writing evidence.

- [ ] **Freeze the clean code identity.**

  ```bash
  cd "$P00_EXECUTION_WORKTREE"
  test -z "$(git status --short --untracked-files=normal)"
  CODE_SHA="$(git rev-parse HEAD)"
  printf '%s\n' "$CODE_SHA" | rg -x '[0-9a-f]{40}'
  INTEGRATED_SHA="$CODE_SHA"
  git diff --check "$P00_BASE_SHA..$CODE_SHA"
  ```

  Expected: clean worktree, valid SHA, no whitespace errors. Any later code/config/runbook correction invalidates this identity and restarts review/fresh verification.

- [ ] **Dispatch an independent read-only review of the exact range.**

  The reviewer receives the approved P00 design, this plan, `P00_BASE_SHA`, `CODE_SHA`, and this exact request:

  ```text
  Review P00_BASE_SHA..CODE_SHA read-only. Check every approved P00 contract, task order, test safety, MediaUrl preservation boundary, Qatar/QAR, zero-retry browser isolation, frontend quality, Pint/Larastan debt, PostgreSQL 16/full-suite/concurrency behavior, provider-neutral commands, CI aggregate semantics, bundle budget, docs/runbook accuracy, and dirty-state recovery. Report findings by Critical, Important, Minor with exact file/line evidence. Do not edit. Approval requires zero Critical and zero Important findings.
  ```

  Expected: written verdict names both SHAs and reports `Critical: 0` and `Important: 0`. Any Critical/Important finding returns to its owning task for the narrow fix, focused commit, downstream reruns, new `CODE_SHA`, and a new independent review. Minor findings are recorded and may not be silently converted into scope growth.

- [ ] **Run the complete matrix from a new checkout, never a linked stale worktree.**

  ```bash
  test ! -e "$P00_FRESH_CHECKOUT"
  git clone --no-checkout "$P00_REMOTE_URL" "$P00_FRESH_CHECKOUT"
  git -C "$P00_FRESH_CHECKOUT" checkout --detach "$CODE_SHA"
  test "$(git -C "$P00_FRESH_CHECKOUT" rev-parse HEAD)" = "$CODE_SHA"
  test -z "$(git -C "$P00_FRESH_CHECKOUT" status --short --untracked-files=normal)"
  test "$(cat "$P00_FRESH_CHECKOUT/.php-version")" = "$P00_PHP_VERSION"
  test "$(cat "$P00_FRESH_CHECKOUT/.node-version")" = "$P00_NODE_VERSION"
  test "$(php -r 'echo PHP_VERSION;')" = "$P00_PHP_VERSION"
  test "$(node -p 'process.versions.node')" = "$P00_NODE_VERSION"
  (cd "$P00_FRESH_CHECKOUT/backend" && composer install --no-interaction --prefer-dist --no-progress)
  (cd "$P00_FRESH_CHECKOUT" && npm ci && npx playwright install chromium)
  ```

  Provision the approved PostgreSQL 16 input, export `DB_URL` without logging it, then:

  ```bash
  cd "$P00_FRESH_CHECKOUT"
  for job in composer-validation php-style-static sqlite postgresql-16 frontend playwright; do
    P00_ATTEMPT=1 scripts/quality/run-p00 "$job"
  done
  node scripts/quality/required-gates.mjs .artifacts/p00
  test -z "$(git status --short --untracked-files=normal)"
  ```

  Expected: six jobs and aggregate pass at `CODE_SHA`; SQLite `446`, PostgreSQL `450`, frontend unit `8`, browser `9`; zero failures/retries/skips; bundle gzip at most `216700`; checkout remains clean because raw outputs are ignored.

- [ ] **Push the exact integrated SHA and obtain two new CI runs.**

  ```bash
  git -C "$P00_EXECUTION_WORKTREE" push "$P00_REMOTE_NAME" "$INTEGRATED_SHA:refs/heads/$P00_EXECUTION_BRANCH"
  test "$(git ls-remote "$P00_REMOTE_NAME" "refs/heads/$P00_EXECUTION_BRANCH" | awk '{print $1}')" = "$INTEGRATED_SHA"
  ```

  Use only the exact provider trigger/download commands approved in Task 14. Trigger run 1 and run 2 as distinct new runs, never reruns/retries. For each downloaded `required-gates.json`, require:

  ```bash
  jq -e --arg sha "$INTEGRATED_SHA" '.integratedSha == $sha and .status == "passed" and .retryAttempt == 1 and .unexplainedSkipCount == 0' required-gates.json
  ```

  Require distinct run IDs; identical PHP/Node/Composer/npm/zlib/Playwright/PostgreSQL image and lockfile hashes; same six job names; aggregate pass. Any provider retry metadata above attempt 1 invalidates that run.

- [ ] **Create the schema and eight sanitized evidence files from measured artifacts.**

  `manifest.schema.json` requires:

  - `schemaVersion=1`, 40-hex `BASE_SHA`, `CODE_SHA`, `INTEGRATED_SHA`;
  - exact PHP, Composer, Node, npm and zlib versions;
  - 64-hex Composer/npm lockfile hashes;
  - PostgreSQL `server_version_num` in `[160000,170000)` and immutable image identity;
  - Playwright package and Chromium browser identities;
  - six canonical local job records plus aggregate with commands, exit, counts, duration and artifact hashes;
  - bundle files/raw/gzip/limit with `gzipBytes <= 216700`;
  - independent review range/verdict and zero Critical/Important;
  - exactly two distinct CI run records, attempt `1`, same `INTEGRATED_SHA`, same inputs and passing aggregate;
  - path/hash references for every sibling evidence payload.

  `README.md` explains sanitization and the three identities. `local-full-matrix.json` is the fresh-checkout aggregate plus six result hashes. `ci-run-1.json` and `ci-run-2.json` are sanitized provider downloads. `bundle.json` is the exact fresh-checkout bundle artifact. `independent-review.md` is the read-only verdict. `manifest.json` contains measured literal values and sets `INTEGRATED_SHA` to the tested `CODE_SHA`; it does not contain the evidence commit SHA.

  ```bash
  node scripts/quality/validate-p00-evidence.mjs docs/superpowers/evidence/p00/manifest.json
  ```

  Expected before creation: nonzero because the manifest is absent. Expected after creation: `P00_EVIDENCE PASS base=$P00_BASE_SHA code=$CODE_SHA integrated=$INTEGRATED_SHA runs=2`.

- [ ] **Run final sanitization and design coverage checks.**

  ```bash
  rg -n --hidden -i 'authorization:|bearer[[:space:]]|password[=:]|token[=:]|secret[=:]|private key|database_url=' docs/superpowers/evidence/p00; test "$?" = 1
  jq -e --arg base "$P00_BASE_SHA" --arg code "$CODE_SHA" --arg integrated "$INTEGRATED_SHA" \
    '.BASE_SHA == $base and .CODE_SHA == $code and .INTEGRATED_SHA == $integrated' \
    docs/superpowers/evidence/p00/manifest.json
  node scripts/quality/validate-p00-evidence.mjs docs/superpowers/evidence/p00/manifest.json
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
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" status --short --untracked-files=normal)"
  test -z "$(git -C "$P00_EXECUTION_WORKTREE" diff --cached --name-only)"
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | wc -l | tr -d ' ')" = 16
  test "$(git -C "$P00_USER_WORKTREE" status --short --untracked-files=normal | LC_ALL=C sort | shasum -a 256 | awk '{print $1}')" = a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa
  ```

  Expected: clean execution worktree/index and unchanged registered user manifest. Stop after reporting identities, counts, versions, two run IDs, review verdict, and any Minor findings. Do not start P01, release publicly, or modify any other repository.

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

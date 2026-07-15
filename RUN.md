# Running Dorzak

Use the exact runtimes in .php-version and .node-version and install only from composer.lock and package-lock.json.

## Manual development

Terminal 1:

    cd backend
    composer install --no-interaction --prefer-dist --no-progress
    php artisan serve --host=127.0.0.1 --port=8000

Terminal 2:

    npm ci
    npx playwright install chromium
    npm run dev -- --host 127.0.0.1 --strictPort

Vite proxies both /api and /storage to Laravel. Every production frontend origin must likewise serve or proxy origin-relative /storage/* to Laravel; do not rewrite stored disk-relative keys into backend-origin URLs.

## Guarded browser matrix

Run only:

    npm run test:e2e

Playwright invokes php artisan e2e:serve only with the owner-approved P00_E2E service inputs. The attested PostgreSQL 16 service must contain no real data, expose the approved provisioner nonce, and issue a unique least-privilege database/role capability for every run. The command migrates and seeds only that new candidate, activates it last, and never resets, drops, renames, unlinks or reuses a database. Cleanup may address only P00_E2E_SERVICE_LIFECYCLE_ID; cleanup failure records an orphan.

## PostgreSQL 16 qualification

The approved attested supervisor creates a unique empty _test database and least-privilege role for every invocation. P00_PG_IDENTITY, the closed attestation hash and P00_PG_INSTANCE_NONCE_SHA256 selected in Task 0 must match the live connection:

    export P00_RUNNER_ROLE=local
    export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"
    scripts/quality/run-p00 postgresql-16

The provisioning guard rejects a non-PostgreSQL candidate, non-test database, mismatched live nonce or wrong server major before migration. The PHPUnit bootstrap then independently rebinds the exact attestation, authority and live fingerprint before any test application or mutation.

## Quality evidence

Run scripts/quality/run-p00 --list for the six canonical jobs. Counts and versions come from scripts/quality/p00-contract.json and docs/superpowers/evidence/p00/manifest.json. The Vite warning for chunks larger than 500 kB is explicit accepted-open debt: its exact text, occurrence, affected files and hash are measured; it is not hidden, waived or grounds to raise the 216797-byte initial gzip limit.

## Recovery after any failed writer boundary

1. Stop all new writers.
2. Retain .artifacts/p00 and every failure log.
3. Record the failing SHA, exact command and runtime/lock/browser/PostgreSQL identities.
4. Re-run the protected-state checks and compare the registered 16-entry user manifest and reviewed MediaUrl diff/artifact relationships.
5. Return to the single owning task and add or correct only the narrow regression.
6. Commit through that task allowlist, then rerun its boundary and every downstream gate on the new clean SHA.

Never reset or clean the user checkout, delete failure evidence, reduce a gate, retry CI into green, reuse a stale execution worktree, or start P01.

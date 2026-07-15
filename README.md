# Dorzak

Dorzak is one branded multi-vertical business operating platform. P00 stabilizes the recovered React/Laravel baseline; it does not publish later roadmap capability.

## Authority and scope

- [Domain context](CONTEXT.md)
- [P00 approved design](docs/superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)
- [P00 implementation plan](docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md)
- [Run and recovery guide](RUN.md)

## Prerequisites

Use exactly the PHP version in .php-version and the Node version in .node-version. Composer and npm must consume the committed lockfiles. PostgreSQL qualification requires the owner-approved immutable PostgreSQL 16 identity.

## Install

    cd backend && composer install --no-interaction --prefer-dist --no-progress
    cd .. && npm ci
    npx playwright install chromium

## Canonical quality entry points

    export P00_RUNNER_ROLE=local
    export P00_RUNNER_CLASS="$(jq -r .execution.runnerClasses.local "$P00_CONTROL_RECORD")"
    scripts/quality/run-p00 --list
    scripts/quality/run-p00 composer-validation
    scripts/quality/run-p00 php-style-static
    scripts/quality/run-p00 sqlite
    scripts/quality/run-p00 postgresql-16
    scripts/quality/run-p00 frontend
    scripts/quality/run-p00 playwright
    node scripts/quality/p00.mjs aggregate .artifacts/p00

The PostgreSQL job obtains a unique create-only candidate from the approved attested supervisor and never accepts, commits or prints a reusable database URL. Measured versions, counts, hashes, immutable PostgreSQL identity, bundle result and CI identities live in [P00 evidence](docs/superpowers/evidence/p00/manifest.json); this README does not duplicate measured values.

The frontend media contract is origin-relative /storage/<disk-relative-key>. The serving frontend origin must route /storage/* to Laravel.

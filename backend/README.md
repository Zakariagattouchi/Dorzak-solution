# Dorzak Laravel backend

Laravel is the P00 modular monolith and API. SQLite in-memory is the fast feedback lane; the complete qualification lane uses the owner-approved immutable PostgreSQL 16 identity. Public media is served through origin-relative /storage/*.

## Install

    composer install --no-interaction --prefer-dist --no-progress

## Canonical checks

    composer validate --strict --no-check-publish
    vendor/bin/pint --test
    composer analyse
    composer test:sqlite
    composer test:postgres

The PostgreSQL database name must end _test. The guarded runner provisions a new candidate and passes its credential URL only in process memory; callers may not supply or reuse DB_URL. Supply the approved immutable identity, closed attestation hash, supervisor credentials and provisioner nonce separately; tests/Support/postgres-bootstrap.php verifies the actual candidate before any test mutation.

Measured test counts, runtimes, lockfile hashes and database identity are recorded in [P00 evidence](../docs/superpowers/evidence/p00/manifest.json), not hard-coded here.

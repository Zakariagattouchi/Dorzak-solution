# Dorzak P00 — Database Safety Erratum

**Date:** 14 July 2026

**Status:** Proposed; awaiting exact owner approval

**Applies to:** [Approved P00 design](./2026-07-14-dorzak-p00-baseline-stabilization-design.md)

## Purpose

This erratum supersedes only the destructive/resettable browser-fixture language in P00 design sections 6.2, 7.3 and 8.1 and strengthens the general PostgreSQL qualification guard. It does not change P00 product scope, merchant behavior, public-release policy or production architecture.

## Replacement contract

Every browser-suite run receives a cryptographically unique PostgreSQL 16 database and login role on an immutable, attested, no-real-data P00 service. P00 never reuses, resets, drops, renames or unlinks a database. The role is least-privileged and can connect only to its candidate database.

Every process capable of database mutation must verify the exact PDO connection it will mutate during application boot and before migration, seeding, `RefreshDatabase` or other test mutation. It must prove the expected driver, database, role, PostgreSQL major, live service nonce, transport contract and phase-specific activation state. Parent-process or earlier-connection verification is insufficient.

Browser migration and seeding each run only after their own live candidate connection passes the provisioning guard. Activation is the final mutation. Laravel serves only an activated candidate whose database, role, server major, service nonce, activation nonce and fixture contract are reverified on its live connection.

The full PostgreSQL qualification lane performs the same live default-connection guard at every Laravel application boot before any Feature test, migration or `RefreshDatabase` work. Its preliminary bootstrap uses the same closed transport options as Laravel and cannot substitute for the in-application guard.

Any mismatch stops before mutation, preserves prior fixtures and every noncandidate database, and records an unactivated candidate as an orphan. Cleanup may address only the attested external service lifecycle identifier and may not introduce database-level destructive primitives.

## Mandatory proof

- Substitute the browser candidate endpoint after acquisition and before migration; the child refuses before mutation and a noncandidate canary remains unchanged.
- Substitute it again between migration and seeding; seeding refuses before mutation and the canary remains unchanged.
- Substitute the PostgreSQL qualification endpoint after bootstrap and before the first Feature test; application boot refuses before migration or `RefreshDatabase`.
- Use a valid PostgreSQL 16 `_test` endpoint with the wrong live nonce; both browser and qualification guards refuse.
- Reject unsupported or changed transport options rather than silently weakening or changing the connection.
- Retain one Playwright worker, zero retries, separate authentication setup and the real-login smoke test.

## Approval and implementation gate

This erratum becomes authority only when the Control Room records the owner's approval against its exact commit and SHA-256. P00 execution remains prohibited until the implementation plan matches this contract, receives zero Critical/Important findings, and is separately approved.


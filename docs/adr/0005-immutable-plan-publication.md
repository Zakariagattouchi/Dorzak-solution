# ADR 0005: Immutable plan publication

## Status

Accepted

## Context

Commercial claims and runtime authorization must not drift after a plan version becomes active.

## Decision

Published plan versions and entitlement matrices are immutable. A new commercial change creates a new version and explicit transition; runtime/server/worker/ERP enforcement and public claims resolve the same version. P03 owns implementation.

## Consequences

- Benefit: One version explains both customer claims and enforced entitlement behavior.
- Cost: Commercial changes require version creation and an explicit migration transition.

## Verification

The P03 entitlement-publication gate verifies immutability and cross-surface parity. P00 performs documentation only.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

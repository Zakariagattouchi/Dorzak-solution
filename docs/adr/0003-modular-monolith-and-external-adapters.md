# ADR 0003: Modular monolith and external adapters

## Status

Accepted

## Context

The product needs clear module boundaries while keeping deployment and transaction ownership understandable.

## Decision

Dorzak remains a Laravel modular monolith plus external systems behind narrow versioned interfaces. ERPNext, payment, storage and messaging credentials/shapes never reach UI/domain modules. A local transaction never spans a remote call.

## Consequences

- Benefit: Domain code depends on stable Dorzak contracts instead of provider shapes.
- Cost: Every integration requires an owned adapter and explicit failure/reconciliation path.

## Verification

The P04 adapter and commerce-cutover gates verify these boundaries. P00 performs documentation only.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

# ADR 0001: System of record authority

## Status

Accepted

## Context

The recovered Laravel commerce baseline and the target ERPNext operating core need an explicit, non-overlapping authority boundary.

## Decision

Dorzak owns identity, plans, experience, orchestration, public content, vertical-native truth and governed support. ERPNext owns paid operational/financial facts after approved cutover. A field/fact has one authority. P00 keeps current Laravel commerce only as the pre-cutover recovered baseline.

## Consequences

- Benefit: Each business fact has one accountable writer before and after cutover.
- Cost: Later packets must map and reconcile every field before changing authority.

## Verification

The P01 execution-context gate and P04 ERP commerce cutover gate verify this boundary. P00 performs documentation only.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

# ADR 0006: Commerce cutover and no dual write

## Status

Accepted

## Context

Moving commerce authority to ERPNext must preserve reconciliation without leaving two writers active.

## Decision

Every commerce domain uses an explicit expand/backfill/parity/cutover/contract sequence. At cutover the new authority becomes the sole writer; rollback uses recorded reconciliation and never long-lived dual writes. P04 owns ERP commerce migration.

## Consequences

- Benefit: Cutover has measurable parity and one authoritative writer.
- Cost: Each commerce domain needs a staged migration and recorded rollback reconciliation.

## Verification

The P04 parity, cutover and contract gates verify the sequence. P00 performs documentation only.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

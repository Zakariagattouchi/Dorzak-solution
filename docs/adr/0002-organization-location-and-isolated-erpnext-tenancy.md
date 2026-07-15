# ADR 0002: Organization, location, and isolated ERPNext tenancy

## Status

Accepted

## Context

Paid tenancy must separate organization authority from location cardinality without creating shared financial data boundaries.

## Decision

One paid Organization maps to one isolated Frappe site/database boundary. A site may serve one or many Locations of that Organization. Enterprise never requires a minimum Location count. Organization/Location migration begins in P01/P02, not P00.

## Consequences

- Benefit: Financial and operational isolation is explicit for every paid organization.
- Cost: Provisioning and migrations must operate one isolated site boundary per organization.

## Verification

The P01 organization-context and P02 location-qualification gates verify this model. P00 performs documentation only.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

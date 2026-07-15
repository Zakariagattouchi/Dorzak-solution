# ADR 0007: Frontend and internal Superadmin surface boundaries

## Status

Accepted

## Context

Merchant administration, public/customer rendering and Dorzak-internal Superadmin operations have different deployment, branding, isolation and authority needs.

## Decision

The current Vite/React app remains the P00 merchant-management surface. The public/customer surface is a separate server-rendered React deployment; Next.js remains only a preferred candidate pending the measured P05 spike and ADR. The internal Superadmin target is a separate Frappe-native site/database using Frappe Desk with minimal Dorzak branding. It uses only governed tenant-bound Dorzak gateway and dorzak_core APIs plus explicit audited grants for merchant visibility or intervention; it never queries merchant databases directly, shares merchant credentials, or stores uncontrolled raw merchant records. Its versioned application manifest is selected only by a later approved P17 design. P17 remains Not started and Not authorized; P00 records this boundary only.

## Consequences

- Benefit: Merchant, public/customer and internal operational surfaces remain explicit while merchant data and authority stay isolated.
- Cost: A later separately authorized P17 design must select the exact application manifest and prove its API, grant and isolation boundaries.

## Verification

The measured P05 spike verifies the public framework. Separately approved P17 design, plan and isolation/source-of-truth/grant tests verify Superadmin. P00 performs documentation only and grants no P17 authorization.

## References

- [Complete-launch baseline](../superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md)
- [Technical execution roadmap](../superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md)
- [P00 baseline stabilization design](../superpowers/specs/2026-07-14-dorzak-p00-baseline-stabilization-design.md)

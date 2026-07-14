# P00 Owner Gate and Focused Amendment Approval Record

## Decision metadata

- Approver: Dorzak owner, in the fresh `Control Room` task `019f6010-88bb-7653-ab98-15ef5d75c7e0`
- Owner decision: `approved`
- Recorded at: 2026-07-14 13:13:31 +03 (Asia/Qatar)
- Control base before this record: `a610130822f94c47b961d3317d0455194370a21a`
- Decision stage: P00 owner gate and one focused documentation-only amendment
- Protected-state boundary: 16 path-status entries, SHA-256 `a797825ef1c504e70abec3dd1a82694cf4fddd76be1544ed716067a9c95d9ffa`

## Preserved owner-approved packet

The Control Room presented the following exact decision packet, and the owner replied `approved`:

> - Approve the safety erratum `59defd5…` / `7af33dd4…`.
> - Approve the launch baseline `cc4085c…` / `7ae650f4…`.
> - Remote: `origin` → `https://github.com/Zakariagattouchi/dorzak.git`.
> - CI: GitHub Actions; integration branch: `main`.
> - Versions: PHP 8.5.6, Node 24.18.0, npm 11.16.0, Composer 2.9.8.
> - Authorize the focused documentation-only Task 14/P17 amendment. No implementation yet.

The owner's exact response was:

> approved

## Exact approved artifact identities

1. The owner formally approves `docs/superpowers/specs/2026-07-14-dorzak-p00-e2e-safety-erratum.md` at commit `59defd5dd36410d487679250c05d2be1d828c094`, SHA-256 `7af33dd41be2dca4490d512118d86dc14aa48f822a5051215927c88a66cd6024`.
2. The owner formally approves `docs/superpowers/specs/2026-07-14-dorzak-complete-launch-baseline-v1.md` at commit `cc4085cbca11e89257ae8535438db6cfe3dd75cc`, SHA-256 `7ae650f4b04c0fe1e234d4fa41cd4fac673abe1139853f4397f2286da24992f2`.

Approval of these artifacts does not approve the current technical roadmap, the provider-neutral P00 plan base, an amended plan, or execution.

## Approved provider, remote and runtime inputs

- Canonical remote name: `origin`
- Canonical remote URL: `https://github.com/Zakariagattouchi/dorzak.git`
- Future canonical integration branch: `main`
- CI provider identifier: `github-actions`
- PHP production pin: `8.5.6`
- Node.js production pin: `24.18.0`
- npm pin: `11.16.0`
- Composer pin: `2.9.8`
- Database major already fixed by the approved P00 contract: PostgreSQL 16

At approval time, read-only `git ls-remote` reached the repository and returned no refs. The local checkout had no configured remote. This approval selects the future remote and integration target; it does not authorize adding the remote, pushing a branch, creating the `main` ref, changing Git configuration or configuring GitHub.

The exact GitHub-hosted runner class, local runner-class literal, immutable GitHub Action SHAs, immutable PostgreSQL 16 image digest and provider-output normalization design remain bounded outputs of the focused amendment. They must be explicit in the amended bytes and later pass fresh independent review and exact owner approval.

## Authorization granted

After this approval record and matching Control Register transition are committed, the fresh Control Room may create one focused documentation-only amendment that changes exactly:

- `docs/superpowers/specs/2026-07-14-dorzak-technical-execution-roadmap-design.md`; and
- `docs/superpowers/plans/2026-07-14-dorzak-p00-baseline-stabilization.md`.

The amendment may only:

1. replace the pending Task 14 provider stop with a complete GitHub Actions adapter design, exact required aggregate status, exact new-run/two-run commands, artifact download/normalization flow, immutable dependency identities, runner classes, staging boundary and verification commands; and
2. reconcile the roadmap and P00 plan's superseded shared React Superadmin assumptions to the approved separate Frappe-native P17 control-plane direction without authorizing P17 planning or execution.

The two amended files must be the only paths in their focused commit. The writer must stop after the commit. Fresh independent exact-byte review must report zero Critical and zero Important findings before the owner is asked to approve the amended roadmap and exact amended plan.

## Explicit exclusions

This decision does not authorize:

- creating or modifying `.github/workflows/*` or any other CI/configuration file;
- adding a Git remote, pushing, creating `main`, configuring required checks or changing provider state;
- application, test, dependency, lockfile or infrastructure changes;
- dependency installation or command execution from the P00 implementation plan;
- `backend/app/Support/MediaUrl.php` inspection, preservation, staging or modification;
- an execution branch or worktree, `BASE_SHA`, P00 implementation or execution;
- P01–P19 planning or execution; or
- public release.

## Next gate

Commit this approval/control transition, complete the exact two-path amendment, independently review the committed amended bytes to zero Critical and zero Important findings, then return the amended commit and both content hashes for exact owner approval. No approval in this record implies the later amendment approval or execution approval.

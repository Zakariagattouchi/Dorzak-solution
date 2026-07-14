# Dorzak Launch — Lean Working Method

## Objective

Finish the complete launch quickly without losing safety, context or ownership.

## Session shape

- One Control Room keeps the source of truth.
- One short writer session owns one bounded outcome and one non-overlapping file lease.
- Up to three read-only agents may investigate or review independent concerns in parallel.
- A second writer is allowed only in a separate worktree with a disjoint lease recorded by the Control Room.
- Every fresh session receives a compact packet: objective, authority, current SHA, scope, non-goals, allowed files, tests, blockers and required return package.
- A session stops after its bounded result, evidence and handoff. It does not carry the next milestone automatically.

## Planning depth

- The master roadmap contains outcomes, dependencies and measurable gates; it does not embed full implementation code.
- Detailed plans cover only the next dependency-ready vertical slice.
- If one implementation packet crosses more than one authority boundary or cannot be reviewed comfortably in one short session, split it before coding.
- Risky unknowns receive a small read-only spike before the implementation packet is approved.

## Review economy

1. Consolidate parallel findings once against one exact commit.
2. One writer corrects only that consolidated list.
3. One final parallel review checks the corrected exact commit.
4. Critical and launch-blocking Important findings must be corrected. Non-blocking improvements enter the backlog and do not reopen the whole plan.
5. A newly discovered blocker receives one narrow correction packet; it may not expand unrelated scope.

Payments, tenant isolation, destructive data operations, authentication, healthcare/privacy and release controls always remain blocking when unsafe.

## Completion rule

Every implementation slice ends with:

- focused automated tests;
- relevant shared regression tests;
- one browser or API smoke path where applicable;
- exact BASE..HEAD and changed-file evidence;
- independent review of the committed SHA;
- a compact handoff and Control Register update.

Internal milestones may be demonstrated and verified, but Dorzak is not released publicly until M9 and every required plan and merchant category is complete.


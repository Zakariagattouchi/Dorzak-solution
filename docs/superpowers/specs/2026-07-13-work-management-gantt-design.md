# Dorzak Work Management and Gantt Design

**Status:** Approved product design

**Approved:** 2026-07-13

**Scope:** Merchant work management, Gantt timelines, operational optimization, Dorzak-managed delivery, and related Superadmin controls

**Source reviewed:** `/Users/barsha/Documents/build website /gantt` at commit `5c83708d300a99d9142edea0d4d52f421e3a2e68`, package version `1.2.2`

## 1. Decision Summary

Dorzak will add a native Work Management module. Frappe Gantt will be reused only as a pinned, internally hardened client-side timeline renderer. Dorzak will own the project and task domain, APIs, tenant isolation, permissions, subscriptions, audit history, collaboration, resource capacity, and optimization logic.

The approved commercial progression is:

- **Free:** no work-management application. Free Tools include downloadable project plans, checklists, RFQ, quotation, invoice, and operational document templates, and the catalogue can expand without becoming a merchant workspace.
- **Pro:** entry work management with projects, task lists, checklists, due dates, one accountable task owner plus followers, reminders, recurring checklists, task comments, a standard status board/calendar, and Dorzak starter templates.
- **Business:** full collaborative project management with interactive Gantt timelines, milestones, dependencies, comments, files, calendar and board views, reusable workflows, and workload warnings.
- **Enterprise:** portfolio management, baselines, critical-path analysis, resource capacity and leveling, scenario planning, advanced audit/export, custom workflows, ERPNext integration, and a governed collaboration room for Dorzak-managed delivery.

Enterprise eligibility is based on operational complexity, team size, governance, integrations, or service requirements. It never requires a minimum of three locations. A one-location organization with a large team or complex operation is a valid Enterprise customer.

## 2. Goals

1. Give merchants one Dorzak interface for planning and executing operational work.
2. Make Business a visibly stronger management product than Pro.
3. Justify Enterprise through measurable scheduling, capacity, governance, integration, and managed-service value.
4. Support Dorzak-led website creation, onboarding, migration, configuration, and custom implementation without using external project tools.
5. Preserve strict merchant data isolation while allowing controlled Superadmin intervention and explicitly delegated Dorzak teammates.
6. Support English, French, and Arabic throughout the module, including Qatar and Tunisia business calendars.
7. Keep interactive management desktop-first while providing a compact mobile experience when a merchant or customer only needs status visibility.
8. Keep the timeline renderer replaceable so Dorzak is not locked into Frappe Gantt internals.

## 3. Non-Goals

The Work module does not replace:

- appointment booking for doctors, clinics, salons, coiffeurs, or beauty centers;
- provider, treatment-room, classroom, or school timetable scheduling;
- restaurant order dispatch or delivery-driver routing;
- employee shift planning, payroll, or attendance;
- clinical decision support or storage of patient health information in task titles;
- a full ERP system; or
- real-time simultaneous editing of the same timeline.

Those capabilities may exchange data with Work Management through stable APIs, but each keeps its own domain rules and source of truth.

## 4. Users and Access Model

### 4.1 Merchant users

- **Owner:** controls all projects, templates, permissions, exports, integrations, and managed-service requests within the merchant organization.
- **Manager:** creates and manages authorized projects and teams. Access can be constrained by department, project, or location.
- **Project editor:** edits assigned projects and timelines. Dorzak assumes one responsible editor per project at a time, as agreed, but still protects against second tabs and stale sessions.
- **Contributor:** updates assigned tasks, checklists, progress, comments, and files without changing protected project settings.
- **Viewer/client approver:** uses the merchant-scoped customer account created from the customer's name and mobile number, reads only explicitly published project status, and approves assigned deliverables without changing schedules. The account belongs only to that merchant's customer database and has no Dorzak-wide identity or cross-business visibility.

### 4.2 Dorzak users

- **Superadmin:** can inspect and intervene in any merchant project through the platform control plane. Every access and mutation is audited.
- **Delegated Dorzak teammate:** has no ambient merchant access. A Superadmin grants a tenant-scoped, project-scoped, purpose-bound, time-limited permission. The grant records who approved it, why it exists, its access level, and its expiry.
- **Support observer:** can receive a read-only grant for diagnosis. Write access is granted separately and visibly.

The merchant management interface displays an intervention banner when a Dorzak teammate is actively operating in the merchant workspace. Customer-facing branded sites never display Dorzak platform administration or reveal unrelated businesses under the Dorzak umbrella.

## 5. Plan Entitlements

Plan behavior is enforced by Laravel, not by hiding React controls and not by Frappe Gantt's `readonly` option.

| Capability | Free | Pro | Business | Enterprise |
|---|---:|---:|---:|---:|
| Downloadable planning/document templates | Yes | Yes | Yes | Yes |
| Projects and task lists | No | Yes | Yes | Yes |
| Checklists, due dates, reminders | No | Yes | Yes | Yes |
| Assignees and recurring checklists | No | Yes | Yes | Yes |
| Board and calendar views | No | Standard status board plus month/agenda calendar | Saved views, filters, grouping, and drag updates | Business plus portfolio grouping |
| Interactive Gantt timeline | No | No | Yes | Yes |
| Dependencies and milestones | No | No | Yes | Yes |
| Comments, attachments, approvals | No | Task comments; no file or approval workflow | Comments, files, and configured approvals | Business plus governed/custom approvals |
| Reusable project/workflow templates | No | Instantiate Dorzak starter templates | Create and reuse merchant templates | Versioned and governed templates |
| Workload warnings | No | No | Yes | Yes |
| Cross-project portfolio | No | No | No | Yes |
| Critical path and schedule float | No | No | No | Yes |
| Baselines and variance | No | No | No | Yes |
| Resource capacity and leveling suggestions | No | No | No | Yes |
| What-if scenarios | No | No | No | Yes |
| Advanced audit and portfolio export | No | No | No | Yes |
| ERPNext project synchronization | No | No | No | Available when enabled for the organization |
| Dorzak-managed delivery room | No | No | No | Yes |

The Work feature keys for the Superadmin plan catalogue are:

- `WORK_BASIC`
- `WORK_BOARD_CALENDAR`
- `WORK_COLLABORATION`
- `PROJECT_GANTT`
- `TASK_DEPENDENCIES`
- `PROJECT_TEMPLATES`
- `TEAM_WORKLOAD_WARNINGS`
- `PROJECT_PORTFOLIO`
- `PROJECT_CRITICAL_PATH`
- `PROJECT_BASELINES`
- `RESOURCE_CAPACITY`
- `PROJECT_SCENARIOS`
- `PROJECT_AUDIT_EXPORT`
- `EXTERNAL_PROJECT_SYNC`
- `MANAGED_DELIVERY_ROOM`
- `ACTIVE_PROJECTS_LIMIT`
- `PROJECT_STORAGE_LIMIT`

Superadmin may change plan composition and numeric limits without code changes. Brand-new capability types still require a reviewed enum/catalogue addition and a server enforcement point.

This sub-spec defines capability boundaries, not commercial quantities or prices. Initial values for `ACTIVE_PROJECTS_LIMIT` and `PROJECT_STORAGE_LIMIT` belong to the master subscription/pricing PRD and remain data-configurable in Superadmin; the Work module must not hard-code them.

### 5.1 Safe plan changes

- Active subscriptions reference an immutable `plan_version_id`; editing a live plan creates a draft version instead of changing subscribers silently.
- Publishing requires an effective date, dependency validation, affected-subscriber count, revenue impact, over-limit count, and a human-readable merchant change summary.
- `PROJECT_GANTT` requires `WORK_BASIC`; `TASK_DEPENDENCIES` requires `PROJECT_GANTT`; portfolio, critical path, baselines, capacity, and scenarios require `PROJECT_GANTT`; managed delivery requires `WORK_COLLABORATION`.
- Upgrades apply immediately after confirmed payment. Downgrades and plan consolidations apply at the next renewal unless the merchant explicitly accepts an earlier effective date.
- Superadmin chooses a published migration policy: grandfather the current version, migrate selected subscriptions at renewal, or migrate all subscriptions at renewal. The choice and affected subscriptions are audited.
- Existing data is never deleted by a plan change. If a merchant exceeds a new limit, existing records remain readable and exportable, while creation is blocked until the merchant reduces usage or upgrades. If Work access is removed, projects remain read-only and exportable.
- A published version cannot be edited or deleted while referenced. Rollback creates a new version cloned from the prior definition and follows the same impact-preview and effective-date process.

### 5.2 Education plans

- **Education Core:** academic and operational project lists, tasks, checklists, reminders, and education templates.
- **Education Plus:** interactive Gantt, dependencies, milestones, staff assignments, collaboration, and workload warnings.
- **Education Enterprise:** institution/campus portfolio, baselines, critical path, capacity, advanced audit, managed delivery, and ERP/LMS/SIS connectors.

Education Gantt use cases include semester preparation, curriculum rollout, assessments, accreditation, research/capstone projects, events, and campus implementation. It is not the school timetable engine.

### 5.3 Vertical examples

- **Retail, shops, suppliers:** store opening, supplier onboarding, catalogue migration, seasonal readiness, purchasing initiatives, and product launches.
- **Restaurants:** new menu rollout, catering projects, compliance preparation, equipment installation, and new-location opening.
- **Appointments and beauty:** service launch, staff training, equipment setup, campaign delivery, and branch preparation; appointment slots remain in the booking engine.
- **Healthcare:** accreditation, equipment maintenance, clinic onboarding, and non-clinical operational projects. Task labels must not contain patient health information.
- **Education:** academic operations and implementation projects; timetables, attendance, and grading remain in their dedicated domains.
- **Dorzak internal delivery:** merchant onboarding, website build, content migration, integration, QA, approval, launch, escalation, and remediation.

## 6. Product Surfaces and Navigation

### 6.1 Merchant desktop

The merchant sidebar gains a **Work** entry under Operations when `WORK_BASIC` is enabled.

- `/work` — personalized overview: due soon, blocked, overdue, awaiting approval, assigned to me, and project health.
- `/work/projects` — searchable project directory with status, owner, dates, progress, risk, and plan-aware creation action.
- `/work/projects/:projectId` — project workspace with Overview, Tasks, Board, Timeline, Calendar, Files, and Activity tabs. Tabs appear only when the plan and role permit them.
- `/work/templates` — starter, vertical, merchant-created, and Dorzak-managed templates.
- `/work/workload` — team workload and warnings for Business; capacity planning for Enterprise.
- `/work/portfolio` — Enterprise project portfolio, filters, rollups, variance, and scenario comparison.
- `/work/managed-services` — Enterprise requests and shared delivery rooms with Dorzak.

The default project workspace opens to Tasks for Pro and to the merchant's last-used eligible view for Business or Enterprise. Timeline is never the only way to manage tasks.

### 6.2 Superadmin desktop

- `/platform/work` — portfolio of active merchant implementations, at-risk projects, overdue approvals, integration failures, and delegated access.
- `/platform/organizations/:organizationId/projects` — authorized merchant project inspection.
- `/platform/access-grants` — create, review, revoke, and audit Dorzak teammate grants.
- Plan editor — exposes the Work feature keys and limits with clear enforcement status.

### 6.3 Mobile

Merchant contributors receive a mobile task experience optimized for viewing assignments, updating status/progress, completing checklists, commenting, attaching a photo when the plan and privacy policy permit it, and approving a deliverable. Interactive timeline editing is desktop-only.

When a merchant chooses to expose project progress to its own customer, the branded customer portal shows a compact milestone/status list. It does not expose internal tasks, staff workload, Dorzak administration, other merchants, or the full Gantt canvas.

## 7. UX and Interaction Design

### 7.1 Project workspace

- A persistent project header shows name, status, owner, date range, progress, health, and the next decision needed.
- View tabs preserve filters and scroll position per user.
- Global filters cover assignee, status, priority, date window, location, department, and labels.
- Empty states explain the first useful action and offer plan-appropriate templates.
- Upgrade prompts explain the business outcome unlocked; they never imply that hidden client-side functionality is authorization.

### 7.2 Timeline

- Business and Enterprise users can change Day, Week, Month, Quarter, and Year views.
- Dragging a task shows a ghost bar, snapped target dates, affected dependency preview, and validation state.
- Resizing shows proposed start/end and duration before release.
- A successful save settles within 180 milliseconds after server confirmation. A rejected save animates back within 220 milliseconds and explains the exact conflict.
- `prefers-reduced-motion` removes translation animations and uses immediate state changes plus status text.
- Milestones use diamond markers; critical tasks use a non-color-only indicator and accessible label.
- Dependency creation uses an explicit dialog as well as pointer interaction so keyboard and touch users are not excluded.
- Undo is available for the current user's most recent accepted schedule change when no later dependent mutation prevents it.

### 7.3 Accessibility

- Every timeline task is focusable and exposes task name, dates, progress, status, and dependency count.
- Keyboard commands support selection, opening details, moving by the configured snap interval, resizing, and changing progress, subject to permissions.
- A synchronized accessible task table provides equivalent editing and is the fallback when the chart cannot be used.
- Focus is retained after refresh, rollback, or view change.
- Controls meet WCAG 2.2 AA contrast, and interactive targets are at least 24 by 24 CSS pixels or have equivalent spacing.

### 7.4 Localization and calendars

- All UI, validation, notification, popup, export, and audit strings exist in English, French, and Arabic.
- Arabic uses an RTL application shell. The time axis remains chronologically left-to-right in all languages, while task lists, controls, forms, and popovers mirror for RTL. This avoids reversing the meaning of time while giving Arabic users a native interface.
- Qatar defaults to Friday/Saturday weekends and `Asia/Qatar`.
- Tunisia defaults to Saturday/Sunday weekends and `Africa/Tunis`.
- Country packs supply public holidays; organization calendars can add closures without changing country defaults.
- Dates are stored in UTC with an explicit organization/project timezone. All-day task dates use date-only semantics and are not shifted by timezone conversion.

## 8. Architecture

```text
Dorzak React Work UI
  -> typed DorzakTimeline adapter
     -> pinned, patched Frappe Gantt renderer

Dorzak Laravel Work API
  -> projects/tasks/dependencies/collaboration
  -> tenant scope, RBAC, plan gates, row versions, audit
  -> scheduling and capacity services
  -> notifications and exports
  -> optional server-to-server ERPNext/HRMS/Helpdesk/Insights adapters
```

The browser never communicates directly with ERPNext. Frappe Gantt requires no Frappe account, SSO, database, or additional server. It is bundled with Dorzak's frontend. All merchants execute the same renderer code, while their data is returned only through authenticated, tenant-scoped Dorzak APIs.

### 8.1 Tenant migration and effective authorization

The current application treats `Store` as the tenant boundary. The scale architecture introduces an `Organization` parent while retaining `Store` as an operating location:

1. Every existing store receives a new organization in a one-to-one, non-destructive backfill.
2. Existing commerce records keep `store_id`; new Work records use `organization_id` and an optional `location_id` that references a store belonging to that organization.
3. The authenticated context resolves both organization and current location. Cross-location Work queries require an organization-level ability; ordinary store-scoped APIs remain unchanged.
4. After migration parity and cross-tenant tests pass, Work uses the organization as its canonical tenant boundary. `store_id` is never overloaded to mean both organization and location.

Effective Work permission is the intersection of platform identity, organization role, project membership, location/department scope, plan entitlement, and the requested action:

- organization owners can access every merchant project;
- managers can access projects within their authorized organization/location/department scope and any project explicitly assigned to them;
- members require project membership or task assignment and cannot elevate themselves;
- customer approvers use merchant-scoped external portal principals and can access only published deliverables assigned to them;
- Superadmin uses an audited platform policy rather than merchant membership; and
- delegated Dorzak teammates can access only the organization, project, action, and time window in the grant, even if they hold another Dorzak staff role.

### 8.2 Renderer boundary

The adapter receives an authorized Dorzak timeline DTO and creates cloned renderer objects. The renderer must never receive ORM/domain entities or determine plan access. It emits proposed changes; the adapter persists them through Laravel and applies only the canonical server response.

The internal fork is limited to:

- replacing unsafe `innerHTML` task rendering with safe text nodes or explicitly sanitized content;
- disabling arbitrary thumbnail/custom-class input unless whitelisted;
- correcting dependency traversal, sub-day duration parsing, progress calculations, and invalid task indexing;
- adding deterministic destruction and event unbinding;
- adding pointer and keyboard behavior, ARIA semantics, focus recovery, and reduced-motion support;
- exposing translation hooks for every built-in string;
- supporting the agreed RTL shell/LTR time-axis strategy; and
- adding renderer unit, browser, security, accessibility, and performance tests.

The MIT copyright and permission notice must remain in the internal copy and third-party notices. Upstream updates are reviewed and cherry-picked; Dorzak never follows an unpinned branch in production.

### 8.3 Canonical domain

The minimum domain contains:

- `projects`: organization, optional location, type, owner, status, dates, timezone, visibility, health, source, row version;
- `tasks`: project, optional parent, name, description, status, priority, dates, effort, progress, milestone flag, ordering, row version;
- `task_dependencies`: predecessor, successor, type, lag, and tenant scope;
- `task_assignments`: task, user/resource, responsibility, allocation, and assignment dates;
- `project_members`: user, role, and project scope;
- `task_checklist_items`, `task_comments`, and `task_attachments`;
- `task_recurrence_rules`: daily, weekly, or monthly frequency; interval; weekdays; organization timezone; start; and either occurrence count or end date;
- `task_reminders`: recipient, channel, scheduled time, delivery state, idempotency key, and source task version;
- `project_templates` and template versions;
- `project_workflows`, `workflow_states`, and `workflow_transitions` for Business/Enterprise custom processes;
- `project_deliverables` and `deliverable_approvals` for governed review and customer approval;
- `managed_service_requests` linked to a `DORZAK_MANAGED` project rather than a separate chat product;
- `task_schedule_changes` containing the accepted change, inverse change, actor, affected versions, and undo eligibility;
- `project_baselines` and immutable baseline task snapshots;
- `resource_calendars` and capacity periods;
- `project_scenarios` and scenario task overrides;
- `project_access_grants` for delegated Dorzak access;
- `project_audit_events` for human and system changes; and
- `integration_mappings`, outbox events, cursors, and conflict records.

Every record is tenant-scoped. Location is an optional operational dimension, not the tenant boundary and not an Enterprise requirement.

Canonical states are:

- **Project:** `DRAFT`, `PLANNED`, `ACTIVE`, `ON_HOLD`, `COMPLETED`, `CANCELLED`, `ARCHIVED`.
- **Task:** `BACKLOG`, `TODO`, `IN_PROGRESS`, `BLOCKED`, `IN_REVIEW`, `DONE`, `CANCELLED`. Custom workflows map their states to one of these reporting states.
- **Deliverable approval:** `PENDING`, `APPROVED`, `CHANGES_REQUESTED`, `CANCELLED`.
- **Managed-service request:** `DRAFT`, `SUBMITTED`, `QUALIFYING`, `ACCEPTED`, `IN_DELIVERY`, `WAITING_MERCHANT`, `COMPLETED`, `CANCELLED`.
- **Deliverable:** `DRAFT`, `SUBMITTED`, `APPROVED`, `CHANGES_REQUESTED`, `SUPERSEDED`.

Recurring task generation is idempotent and creates the next occurrence only after the prior generation key is committed. In-app and email are the default Work notification channels; WhatsApp requires its separate channel entitlement and merchant/user consent. Undo calls the stored inverse schedule command and succeeds only when all affected task versions still match and no later dependent schedule change exists.

### 8.4 API boundary

Required resource groups include:

- project and task CRUD;
- timeline reads constrained by project and date window;
- narrow schedule and progress mutations using `If-Match`/row versions;
- dependency, assignment, checklist, comment, attachment, and approval operations;
- templates and template instantiation;
- workload, portfolio, baseline, critical-path, and scenario resources;
- audit and export endpoints;
- managed-service request and access-grant endpoints; and
- integration status, retry, and conflict-resolution endpoints.

Workflow transitions, deliverable submission/approval, service-request transitions, recurrence changes, reminder preferences, and undo are explicit commands. Generic task update endpoints cannot bypass their state rules.

The API derives organization context from the authenticated session and membership. A request body can filter by an authorized location but can never nominate a tenant as authority.

## 9. Change, Conflict, and Error Flow

1. The user drags or edits a task.
2. The React adapter displays a proposed state without overwriting the last confirmed snapshot.
3. Laravel verifies tenant membership, role, plan entitlement, row version, dependency cycle rules, date rules, and resource constraints.
4. On success, Laravel commits transactionally, writes an audit event, dispatches notifications/outbox events, and returns canonical task data with a new version.
5. On `409`, the UI restores confirmed data and offers comparison with the newer version.
6. On `422`, the UI restores confirmed data and identifies the exact dependency, calendar, date, or capacity rule that failed.
7. On `403`, the UI restores confirmed data and explains the missing role or project permission.
8. On `402 PLAN_UPGRADE_REQUIRED`, the UI restores confirmed data and shows the exact plan capability required.
9. On network failure, the UI keeps the proposal visibly unsaved, retries only idempotent requests, and lets the user discard or retry. Schedule mutations are never silently queued as successful.

Although one editor is expected to own a project page, row versions remain mandatory because multiple tabs, mobile updates, integrations, delegated Dorzak staff, and background automation can still conflict.

## 10. Scheduling and Optimization

### 10.1 Business

Business optimization is transparent and rule-based:

- overdue and due-soon detection;
- blocked task and missing-predecessor warnings;
- actual versus expected progress;
- unassigned or unscheduled work;
- assignee workload warnings based on known working calendars; and
- project health derived from overdue critical milestones, blockers, and progress variance.

### 10.2 Enterprise

Enterprise adds deterministic planning services:

- directed-acyclic dependency validation;
- finish-to-start, start-to-start, finish-to-finish, and start-to-finish dependencies with lag;
- critical-path method, total float, and near-critical warnings;
- immutable baselines and planned-versus-current variance;
- capacity aggregation by user, role, team, department, and location;
- resource-overload detection and leveling suggestions;
- portfolio risk rollups;
- private what-if scenarios that do not change production dates until approved; and
- schedule proposals with an impact summary and audited human approval.

AI may summarize risks or explain a deterministic recommendation, but it may not invent capacity, silently reschedule work, or make clinical/academic decisions. Applying a proposal always requires an authorized human action.

## 11. Source Reuse

| Source | Approved reuse |
|---|---|
| Frappe Gantt | Timeline rendering only, through the hardened internal fork and Dorzak adapter |
| Existing Dorzak React application | Shell, authentication context, design tokens, notifications, routing patterns, locale bridge |
| Existing Dorzak Laravel backend | Tenant context, Sanctum session, role enforcement patterns, PlanGate, Superadmin plan editor, audit patterns |
| ERPNext Projects | Optional Enterprise connector and domain reference; not a mandatory backend for Pro or Business |
| HRMS | Optional staff, leave, and availability input for capacity calculations through APIs |
| Helpdesk | Support/escalation/SLA and managed-service workflow reference or connector |
| Insights | Portfolio and operational analytics connector/reference; not live scheduling authority |
| Website builder | Deliverables managed by Dorzak projects; builder content remains in the builder domain |

No source repository supplied by the owner is edited in place. Reuse occurs through licensed copies, documented adapters, or server-to-server APIs.

## 12. Security and Privacy

- Server authorization is required for every read and mutation; renderer read-only flags are convenience controls only.
- Cross-tenant identifiers return `404` and never disclose resource existence.
- Project exports, attachments, and signed URLs are tenant-scoped and expire.
- Task text and popup content are escaped by default. Rich content uses an allowlist sanitizer and never executes scripts, event handlers, or arbitrary URLs.
- Superadmin and delegation access records reason, actor, tenant, project, permission, start, expiry, revocation, and all activity.
- Delegated grants expire automatically and are revocable immediately.
- Healthcare operational templates prohibit patient identifiers and clinical notes. Sensitive healthcare scheduling remains outside this module.
- The entire Work module is classified as non-clinical. It has no patient, appointment, treatment, diagnosis, prescription, or health-record foreign key and exposes none of those entities in selectors or APIs.
- For healthcare organizations, free-text fields pass a tenant-language DLP check before persistence; likely patient identifiers or clinical content are rejected with corrective guidance. Attachments are disabled until their document category is approved and both malware and DLP scans pass. Notifications and search indexes use only the sanitized task title, and audit payloads redact detected identifiers.
- A future need to store or schedule patient-linked work requires a separate regulated clinical-work design and compliance approval; this module cannot be repurposed by configuration.
- Integration credentials are encrypted server-side per tenant. Browsers never receive ERPNext service credentials or shared cross-tenant tokens.
- Audit records are append-only through application behavior and include before/after schedule values for privileged changes.

## 13. Performance and Reliability

- The API requires date windows and supports filters so the renderer never loads an organization's entire portfolio by default.
- A single interactive timeline renders at most 500 visible tasks and 1,000 visible dependencies. Larger results require filtering, grouping, or portfolio aggregation before opening the detailed chart.
- After the timeline API response arrives, the chart must become interactive within 2 seconds at the 500-task limit on the CI reference desktop (4 vCPU, 8 GB RAM, supported Chromium). During drag, at least 95% of rendered frames must complete within 33 milliseconds, and network confirmation cannot block pointer feedback.
- Long operations such as portfolio export, baseline generation, and connector synchronization run as observable jobs with idempotency and retry rules.
- Project/task writes are transactional. Notifications and integrations use an outbox so external failures cannot roll back committed merchant work.
- Renderer errors are contained by a React error boundary and fall back to the accessible task table.
- Integration outages never prevent local Dorzak task reads or unrelated merchant operations.

## 14. Testing and Launch Gates

### 14.1 Required test layers

- Unit tests for task dates, cycles, dependencies, milestones, progress, critical path, float, baselines, calendars, capacity, leveling, and entitlements.
- Laravel feature tests for every role, plan gate, limit, delegated grant, row-version conflict, audit record, export, attachment, and integration boundary.
- Cross-tenant probe tests for every Work resource and public/status projection.
- Renderer tests for safe text handling, URL allowlists, destruction, pointer and keyboard editing, focus retention, localization, RTL shell behavior, and reduced motion.
- React integration tests for optimistic proposals, rollback, conflicts, upgrade prompts, accessible table parity, and route protection.
- End-to-end tests for Pro, Business, Enterprise, Education Core, Education Plus, Education Enterprise, Superadmin, delegated Dorzak teammate, and revoked/expired access.
- Security tests for stored/reflected XSS, IDOR, privilege escalation, signed URL leakage, malicious imports, and integration credential exposure.
- Performance tests at the 500-task/1,000-dependency chart limit and portfolio query limits.

### 14.2 Launch acceptance

The module is not publicly released until:

1. All enabled plan entitlements are server-enforced and match the pricing/landing-page comparison.
2. English, French, and Arabic journeys pass, including Qatar and Tunisia calendars.
3. Desktop management and mobile contributor/status journeys pass accessibility and end-to-end tests.
4. Cross-tenant, Superadmin, delegated access, XSS, audit, and conflict tests pass.
5. Business Gantt and Enterprise optimization are complete rather than marketed as future features.
6. Every approved merchant category and the Education plans have validated templates and domain boundaries.
7. Source licenses and third-party notices are present.
8. No release-blocking defect remains, and rollback/backup/monitoring procedures have been exercised.

These statements are measured as follows:

- Supported desktop browsers are the current and previous major releases of Chrome, Edge, Firefox, and Safari at release time. Mobile qualification covers iOS Safari at 390 by 844 CSS pixels and Android Chrome at 360 by 800 CSS pixels.
- For the 500-task/1,000-dependency fixture in the Qatar deployment region, authenticated timeline reads have API p95 at or below 750 milliseconds and accepted schedule mutations have API p95 at or below 500 milliseconds, excluding the separately measured browser-render time in Section 13.
- There are zero open Severity 1 defects (security breach, cross-tenant exposure, irreversible loss, or complete outage) and zero open Severity 2 defects (core paid journey unavailable, incorrect billing/authorization, or materially incorrect schedule). Severity 3 defects require a named owner, documented workaround, and Product/Engineering acceptance.
- Every vertical pack has a seeded acceptance fixture with its workflow states, calendar, permissions, and expected output. Critical-path/capacity fixtures include exact expected task dates, float, overloads, and recommended moves.
- Launch sign-off is recorded from the product owner, engineering lead, security/privacy owner, English/French/Arabic localization reviewers, and designated SMEs for retail/supplier, restaurant, appointment/beauty, healthcare, and education.
- Backup restore, job retry, integration outage, renderer fallback, plan rollback, delegated-access revocation, and release rollback are exercised in a production-like environment with recorded evidence.

This module follows the project's one-complete-launch rule: it is not used to justify a publicly available plan until the corresponding Pro, Business, Enterprise, Education, vertical, Superadmin, localization, and quality requirements are ready together.

## 15. Rejected Approaches

### 15.1 Drop the upstream package directly into React

Rejected for production because the supplied version contains unsafe HTML sinks, incomplete dependency behavior, partial localization, no lifecycle cleanup, limited touch support, inaccessible editing, and insufficient tests.

### 15.2 Use ERPNext Projects as the mandatory source of truth

Rejected because it would force every Pro/Business merchant through Frappe provisioning, availability, identity, latency, and upgrade coupling. ERPNext remains an optional Enterprise integration.

### 15.3 Embed or iframe ERPNext project screens

Rejected because it would fragment Dorzak UX, authentication, branding, permissions, mobile behavior, and customer trust.

### 15.4 Build the timeline renderer from zero

Rejected because the existing MIT renderer can save meaningful chart work once isolated and hardened. Dorzak engineering should focus on the task domain, collaboration, optimization, security, and vertical value.

## 16. Implementation Decomposition

This design is intentionally broader than one implementation plan. It is divided into independently reviewable tracks that share the same domain contracts and are completed before the one public launch:

1. **Work foundation and Pro:** organization/store migration, immutable plan versions, tenant-scoped projects, tasks, checklists, comments, reminders, templates, task/calendar/board UI, plan enforcement, and mobile contributor view.
2. **Business collaboration and timeline:** attachments, approvals, dependency domain, milestones, Business views, hardened renderer fork, React adapter, accessible table parity, and workload warnings.
3. **Superadmin and managed delivery:** platform portfolio, full Superadmin intervention, delegated Dorzak grants, managed-service request/delivery room, access banners, and audit.
4. **Enterprise planning:** portfolios, baselines, critical path, resource calendars/capacity, leveling suggestions, scenarios, governed templates, and advanced exports.
5. **Enterprise integrations:** ERPNext/HRMS/Helpdesk/Insights adapters, credential isolation, mappings, outbox/webhooks, retries, and conflict resolution.
6. **Vertical, localization, and launch qualification:** English/French/Arabic, Qatar/Tunisia calendars, vertical templates, customer status projection, security/accessibility/performance qualification, observability, backup, and rollback rehearsal.

Each track receives its own detailed implementation plan and test gates. Tracks may be developed in parallel only after their shared API, tenancy, entitlement, and audit contracts are fixed. Passing an earlier track does not authorize a partial public launch.

## 17. Definition of Done

This design is complete when a merchant can create and operate plan-appropriate projects entirely inside Dorzak; Business users can safely manage collaborative Gantt timelines; Enterprise users can manage portfolios, capacity, baselines, risks, integrations, and Dorzak-delivered work; Superadmin can intervene and delegate access with complete accountability; and customer/mobile surfaces expose only deliberately published branded status information.

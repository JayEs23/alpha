# Sprint-by-Sprint Engineering Plan

## Planning Assumptions
- Team: 1 tech lead, 2 backend engineers, 1 QA, 1 product owner.
- Sprint length: 2 weeks.
- Delivery model: incremental, production-safe, migration-first.
- Priority order: data integrity -> tenancy safety -> work management -> UX and scale.

## Phase 1 - Stabilization (Sprints 1-2)

### Sprint 1: Schema Integrity Foundation
Goals:
- Establish migration safety and enforce critical FKs.
- Start canonical naming migration path.

Deliverables:
- migration run log table and data-quality command.
- FK additions for `users`, `companies`, memberships, sessions.
- canonical table creation (`providers`, `hardware_assets`, `software_assets`, `peripheral_assets`).

Acceptance Criteria:
- Orphan detection script passes in staging.
- New canonical tables populated from legacy data.
- No tenant leakage on read/write smoke tests.

Risks:
- orphaned legacy records block FK migration.
Mitigation:
- pre-migration cleanup scripts with auditable report.

### Sprint 2: Naming and Resource Stability
Goals:
- Fix misspelled model/resource references.
- Stabilize Filament resource page/action wiring.

Deliverables:
- canonical model aliases, then direct canonical references.
- consistent pages across resources: List/Create/View/Edit.
- relation manager schema corrections and runtime smoke tests.

Acceptance Criteria:
- All admin resources load without runtime errors.
- CRUD works on canonical tables for assets/providers.

---

## Phase 2 - Core Work Management (Sprints 3-5)

### Sprint 3: Work Domain Schema
Goals:
- Introduce projects/tasks/workflow core tables.

Deliverables:
- tables: `projects`, `workflows`, `workflow_statuses`, `workflow_transitions`, `tasks`.
- lookup seeding for priorities and statuses.
- uniqueness constraints for project key and task number.

Acceptance Criteria:
- create/read/update tasks and projects through seed scripts.
- database constraints prevent invalid duplicates.

### Sprint 4: Service Layer Core
Goals:
- Implement `TaskService` and `CompanyService` contracts.

Deliverables:
- service contracts + implementations.
- transaction boundaries and domain exceptions.
- policy checks integrated into services.

Acceptance Criteria:
- all task writes go through `TaskService`.
- unit and feature tests for main task flows are green.

### Sprint 5: Task UX Baseline
Goals:
- Deliver first usable task management in Filament.

Deliverables:
- `ProjectResource` and `TaskResource`.
- list view with filters (status, priority, assignee, overdue).
- create/edit/view task pages.

Acceptance Criteria:
- operations team can manage backlog and task assignment end-to-end.

---

## Phase 3 - Workflow, Comments, and Observability (Sprints 6-8)

### Sprint 6: Workflow Engine Rules
Goals:
- enforce transition guards and completion conditions.

Deliverables:
- workflow transition validator service.
- rules: resolution required, subtasks complete, role-based transitions.
- task activity event emission.

Acceptance Criteria:
- invalid transitions are blocked with domain error responses.
- all transitions produce activity events.

### Sprint 7: Collaboration Features
Goals:
- comments, watchers, links, and asset-task relationships.

Deliverables:
- tables and services for comments/watchers/links.
- in-app notifications for assignment and status changes.

Acceptance Criteria:
- comments and watcher notifications functional.
- task-to-asset linking visible in task and asset views.

### Sprint 8: Dashboards and Reports v1
Goals:
- restore and improve operational visibility.

Deliverables:
- workload, overdue, throughput, asset lifecycle dashboard widgets.
- exportable filtered reports.

Acceptance Criteria:
- dashboard performance meets baseline target.
- report exports validated by QA on representative datasets.

---

## Phase 4 - Scale and Integration (Sprints 9-10)

### Sprint 9: API v1 and Notification Hardening
Goals:
- provide stable API surface and robust async notifications.

Deliverables:
- versioned API routes for projects/tasks/assets.
- policy-protected API controllers using service layer.
- queued notification pipeline with retries and dead-letter handling.

Acceptance Criteria:
- API contract tests pass.
- retry and failure handling validated in staging.

### Sprint 10: Production Readiness
Goals:
- finalize observability, security, and rollout playbooks.

Deliverables:
- logging/tracing standards.
- deployment and rollback runbooks.
- final migration cutover checklist and post-cutover validation scripts.

Acceptance Criteria:
- go-live checklist fully passed.
- no severity-1 defects in pre-production UAT.

---

## Cross-Sprint Definition of Done
- code reviewed and merged with tests,
- migration scripts idempotent and rollback-tested,
- policies implemented and validated for every new action,
- documentation updated (`README`, architecture docs, endpoint docs),
- no direct business logic in Filament resources.

## Dependency Map
- Phase 1 must complete before Phase 2 service cutover.
- Workflow engine depends on tasks/projects schema.
- API v1 depends on service-layer completion and policy matrix.

## Release Strategy
- release every sprint behind feature flags when needed,
- dual-write/dual-read only during migration windows,
- production rollout by tenant cohorts (small -> medium -> full).

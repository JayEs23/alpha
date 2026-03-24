# ERD and Migration Checklist

## Scope
This document defines the target entity relationship model and a safe migration path from the current Laravel + Filament schema to the redesigned Operations Management Platform.

## Target ERD (Textual)
- `companies` 1---* `company_memberships` *---1 `users`
- `companies` 1---* `providers`
- `companies` 1---* `projects`
- `projects` 1---* `tasks`
- `tasks` 1---* `task_comments`
- `tasks` *---* `users` via `task_watchers`
- `tasks` *---* `tasks` via `task_links`
- `tasks` *---* assets via `task_asset_links`
- `companies` 1---* `hardware_assets` / `software_assets` / `peripheral_assets`
- assets 1---* `asset_assignments`
- `workflows` 1---* `workflow_transitions`
- `projects` *---* `workflow_statuses` via `project_workflow_statuses`

## Migration Strategy
Use expand-and-contract migrations:
1. Expand with new canonical tables and FK columns.
2. Backfill and dual-write where required.
3. Cut reads/writes to canonical schema.
4. Contract by dropping legacy columns/tables.

Always perform data snapshots and rollback validation before production cutover.

## Wave 0 - Preparation
- [ ] Enable maintenance strategy (window or limited write mode).
- [ ] Full database backup + restore test in staging.
- [ ] Add migration run log table for idempotent scripts.
- [ ] Add data quality audit script:
  - orphan references,
  - invalid status/type strings,
  - duplicate tenant keys (`asset_tag`, `project key`).

## Wave 1 - Canonical Naming (Non-destructive)
- [ ] Create canonical tables:
  - `providers` (from `provaiders`)
  - `peripheral_assets` (from `periphels`)
  - `hardware_assets` (from `hardware`)
  - `software_assets` (from `software`)
- [ ] Add compatibility mapping layer in repository/services.
- [ ] Backfill from legacy tables with deterministic ID mapping.
- [ ] Validate row counts and sampled checksums.

## Wave 2 - Foreign Keys and Integrity
- [ ] Add FK `companies.owner_user_id -> users.id` (from old `companies.user_id`).
- [ ] Add FK `users.current_company_id -> companies.id`.
- [ ] Replace `company_user` with `company_memberships` and enforce FKs.
- [ ] Add FK `sessions.user_id -> users.id`.
- [ ] Add indexes on every FK and frequently filtered tenant column.
- [ ] Block release if integrity audit detects unresolved orphans.

## Wave 3 - Normalize Lookup Values
- [ ] Create lookup tables:
  - `asset_statuses`
  - `asset_categories`
  - `license_periods`
  - `membership_statuses`
  - `workflow_statuses`
  - `task_priorities`
  - `task_resolutions`
- [ ] Map legacy strings to lookup IDs.
- [ ] Backfill all asset and membership references.
- [ ] Mark old string columns read-only during transition.

## Wave 4 - Introduce Work Domain
- [ ] Create work domain tables:
  - `projects`
  - `workflows`
  - `workflow_transitions`
  - `project_workflow_statuses`
  - `tasks`
  - `task_comments`
  - `task_watchers`
  - `task_links`
  - `task_asset_links`
  - `task_activity_log`
- [ ] Seed default workflow templates and priorities.
- [ ] Add uniqueness constraints:
  - `projects(company_id, key)`
  - `tasks(project_id, task_number)`.

## Wave 5 - Service-Layer Cutover
- [ ] Move all write logic from Filament resources into services.
- [ ] Ensure transactions for multi-table writes.
- [ ] Emit domain events after successful commit only.

## Wave 6 - Admin and API Cutover
- [ ] Switch Filament resources to canonical models.
- [ ] Ensure all resources expose consistent pages: List/Create/View/Edit.
- [ ] Release API v1 routes for projects/tasks/assets.

## Wave 7 - Decommission Legacy
- [ ] Remove compatibility alias paths.
- [ ] Drop legacy misspelled tables after validation window.
- [ ] Drop deprecated string enum columns replaced by lookup FKs.

## Per-Table Checklist

### `provaiders` -> `providers`
- [ ] Create `providers` with `company_id`, unique (`company_id`, `name`).
- [ ] Copy rows and persist mapping IDs.
- [ ] Re-point all asset provider FKs to `providers`.
- [ ] Rename model/resource/service references.

### `periphels` -> `peripheral_assets`
- [ ] Create canonical table with `status_id`, `category_id`.
- [ ] Map legacy `type` strings to `asset_categories`.
- [ ] Backfill assignment history where only direct `user_id` existed.

### `hardware` -> `hardware_assets`
- [ ] Add normalized `status_id` and `category_id`.
- [ ] Add tenant-local unique `asset_tag`.
- [ ] Preserve soft-delete and purchase metadata.

### `software` -> `software_assets`
- [ ] Normalize `status`, `type`, `license_period` to lookup tables.
- [ ] Migrate license-related metadata safely.

### `company_user` -> `company_memberships`
- [ ] Convert role strings to `company_roles`.
- [ ] Add `status_id` for active/inactive membership lifecycle.
- [ ] Enforce unique active membership per company/user.

## Validation Gates Before Production
- [ ] All FK checks pass.
- [ ] Tenant leakage tests pass (cross-company access blocked).
- [ ] Counts match between old and new tables.
- [ ] Service-layer write paths fully enabled.
- [ ] Rollback scripts tested.

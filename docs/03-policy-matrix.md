# Authorization Policy Matrix

## Purpose
Define a strict, auditable access model for multi-tenant operations and work management.

## Baseline Roles
- `company_owner`
- `company_admin`
- `operations_manager`
- `agent`
- `viewer`

Legend:
- `Y` = allowed
- `C` = conditional
- `N` = denied

## Role x Action Matrix

| Entity / Action | company_owner | company_admin | operations_manager | agent | viewer |
|---|---:|---:|---:|---:|---:|
| Company view/update | Y | C | N | N | N |
| Membership invite/remove | Y | Y | N | N | N |
| Membership role assignment | Y | C | N | N | N |
| Provider create/update/delete | Y | Y | Y | C | N |
| Asset create/update | Y | Y | Y | C | N |
| Asset assign/unassign | Y | Y | Y | C | N |
| Asset retire/dispose | Y | Y | C | N | N |
| Service plan create/update | Y | Y | Y | C | N |
| Service task assign/reassign | Y | Y | Y | C | N |
| Service task complete | Y | Y | Y | Y | N |
| Service reminder configuration | Y | Y | C | N | N |
| Project create/update/archive | Y | Y | Y | N | N |
| Task create | Y | Y | Y | Y | N |
| Task edit | Y | Y | Y | C | N |
| Task transition | Y | Y | Y | C | N |
| Task delete (soft delete) | Y | C | C | N | N |
| Comment create | Y | Y | Y | Y | N |
| Comment delete others' comments | Y | C | C | N | N |
| Workflow configuration | Y | Y | C | N | N |
| Audit log view | Y | Y | C | N | N |
| Dashboard reporting | Y | Y | Y | Y | C |

## Conditional Rules (`C`)
- `company_admin` cannot:
  - transfer ownership,
  - demote/remove owner,
  - edit global security settings.
- `agent` can:
  - edit only tasks they reported or are assigned to,
  - transition tasks only via workflow-allowed transitions,
  - assign assets only if explicit capability is granted.
- `viewer` can:
  - read only non-sensitive fields,
  - never execute write actions.

## Tenant Isolation Rules
- All policy checks must include company scope validation.
- If `resource.company_id != actor.current_company_id`, deny by default.
- Super-admin bypass must be explicit and auditable.

## Policy Class Map
- `CompanyPolicy`
- `MembershipPolicy`
- `InvitationPolicy`
- `ProviderPolicy`
- `HardwareAssetPolicy`
- `SoftwareAssetPolicy`
- `PeripheralAssetPolicy`
- `ProjectPolicy`
- `TaskPolicy`
- `TaskCommentPolicy`
- `WorkflowPolicy`
- `AuditLogPolicy`

## Required Abilities (Permission Keys)
- `company.view`, `company.update`, `company.transfer_ownership`
- `membership.invite`, `membership.remove`, `membership.change_role`
- `providers.view`, `providers.create`, `providers.update`, `providers.delete`
- `assets.view`, `assets.create`, `assets.update`, `assets.assign`, `assets.retire`
- `asset_service_plans.view`, `asset_service_plans.create`, `asset_service_plans.update`
- `asset_service_tasks.view`, `asset_service_tasks.assign`, `asset_service_tasks.complete`
- `asset_service_reminders.view`, `asset_service_reminders.manage`
- `projects.view`, `projects.create`, `projects.update`, `projects.archive`
- `tasks.view`, `tasks.create`, `tasks.update`, `tasks.transition`, `tasks.delete`
- `comments.create`, `comments.delete`
- `workflow.view`, `workflow.update`
- `audit.view`

## Enforcement Requirements
- UI visibility checks are not sufficient; policies are mandatory on server actions.
- Filament resource actions must call policy gates explicitly.
- Service methods must perform capability checks even if controller already checked.
- Every denied action must be traceable in logs for audit review.

## Testing Matrix (Minimum)
- One positive and one negative test per role/action pair for high-risk actions:
  - ownership transfer,
  - membership role change,
  - task transition,
  - asset assignment,
  - workflow update.
- Cross-tenant denial tests for all mutable entities.

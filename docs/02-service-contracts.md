# Service Contracts and Application Layer Design

## Objective
Define clear, testable service interfaces so all business logic is centralized outside Filament resources and controllers.

## Directory Convention
- Contracts: `app/Domain/*/Contracts`
- Implementations: `app/Domain/*/Services`
- DTOs/Value Objects: `app/Domain/*/DTOs`
- Exceptions: `app/Domain/*/Exceptions`

## Core Service Interfaces

```php
<?php
// app/Domain/Task/Contracts/TaskServiceInterface.php

namespace App\Domain\Task\Contracts;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface TaskServiceInterface
{
    public function createTask(array $payload, User $actor): Task;

    public function updateTask(int $taskId, array $payload, User $actor): Task;

    public function transitionTask(
        int $taskId,
        int $toStatusId,
        User $actor,
        ?string $resolutionCode = null,
        ?string $comment = null
    ): Task;

    public function assignTask(int $taskId, ?int $assigneeUserId, User $actor): Task;

    public function addComment(int $taskId, string $body, User $actor, bool $isInternal = false): void;

    public function addWatcher(int $taskId, int $userId, User $actor): void;

    public function removeWatcher(int $taskId, int $userId, User $actor): void;

    public function linkAsset(int $taskId, string $assetType, int $assetId, int $relationshipTypeId, User $actor): void;

    public function unlinkAsset(int $taskId, string $assetType, int $assetId, User $actor): void;

    public function reorderBacklog(int $projectId, array $orderedTaskIds, User $actor): void;

    public function bulkTransition(Collection $taskIds, int $toStatusId, User $actor): int;

    public function markOverdueTasks(Carbon $asOf): int;
}
```

```php
<?php
// app/Domain/Asset/Contracts/AssetServiceInterface.php

namespace App\Domain\Asset\Contracts;

use App\Models\User;

interface AssetServiceInterface
{
    public function createHardware(array $payload, User $actor): object;
    public function updateHardware(int $assetId, array $payload, User $actor): object;
    public function transitionHardwareStatus(int $assetId, int $statusId, User $actor): object;

    public function createSoftware(array $payload, User $actor): object;
    public function updateSoftware(int $assetId, array $payload, User $actor): object;
    public function transitionSoftwareStatus(int $assetId, int $statusId, User $actor): object;

    public function createPeripheral(array $payload, User $actor): object;
    public function updatePeripheral(int $assetId, array $payload, User $actor): object;
    public function transitionPeripheralStatus(int $assetId, int $statusId, User $actor): object;

    public function assignAsset(string $assetType, int $assetId, int $assigneeUserId, User $actor, ?string $notes = null): void;
    public function unassignAsset(string $assetType, int $assetId, User $actor, ?string $notes = null): void;

    public function createServicePlan(int $assetId, array $payload, User $actor): object;
    public function updateServicePlan(int $servicePlanId, array $payload, User $actor): object;
    public function generateDueServiceTasks(\DateTimeInterface $asOf, User $actor): int;
    public function completeServiceTask(int $serviceTaskId, array $payload, User $actor): object;
    public function scheduleServiceReminders(\DateTimeInterface $asOf, User $actor): int;
}
```

```php
<?php
// app/Domain/Company/Contracts/CompanyServiceInterface.php

namespace App\Domain\Company\Contracts;

use App\Models\Company;
use App\Models\User;

interface CompanyServiceInterface
{
    public function createCompany(array $payload, User $owner): Company;
    public function updateCompany(int $companyId, array $payload, User $actor): Company;
    public function transferOwnership(int $companyId, int $newOwnerUserId, User $actor): Company;

    public function inviteMember(int $companyId, string $email, int $roleId, User $actor): void;
    public function acceptInvitation(string $token, User $invitee): void;
    public function revokeInvitation(int $invitationId, User $actor): void;

    public function addMember(int $companyId, int $userId, int $roleId, User $actor): void;
    public function changeMemberRole(int $companyId, int $userId, int $roleId, User $actor): void;
    public function removeMember(int $companyId, int $userId, User $actor): void;
}
```

```php
<?php
// app/Domain/Notification/Contracts/NotificationServiceInterface.php

namespace App\Domain\Notification\Contracts;

use App\Models\User;

interface NotificationServiceInterface
{
    public function notifyTaskAssigned(int $taskId, int $assigneeUserId, User $actor): void;
    public function notifyTaskStatusChanged(int $taskId, int $fromStatusId, int $toStatusId, User $actor): void;
    public function notifyMentioned(int $taskId, array $mentionedUserIds, User $actor): void;
    public function sendDigest(int $companyId, string $frequency): int;
}
```

## Cross-Cutting Rules (Mandatory)
- Every public service method must:
  - authorize actor capability,
  - validate company boundary,
  - enforce domain rules,
  - execute in transaction for multi-table changes,
  - emit events after commit.

## Required Domain Events
- `TaskCreated`
- `TaskAssigned`
- `TaskTransitioned`
- `TaskCommentAdded`
- `AssetAssigned`
- `AssetStatusChanged`
- `AssetServicePlanCreated`
- `AssetServiceTaskGenerated`
- `AssetServiceTaskCompleted`
- `AssetServiceReminderScheduled`
- `AssetServiceReminderSent`
- `CompanyMemberInvited`
- `CompanyMemberRoleChanged`

## Exception Taxonomy
- `DomainValidationException`
- `ForbiddenOperationException`
- `InvalidTransitionException`
- `TenantBoundaryViolationException`
- `ConflictStateException`

## Injection and Binding
In `AppServiceProvider` (or dedicated `DomainServiceProvider`), bind contracts to implementations:
- `TaskServiceInterface` -> `TaskService`
- `AssetServiceInterface` -> `AssetService`
- `CompanyServiceInterface` -> `CompanyService`
- `NotificationServiceInterface` -> `NotificationService`

## Test Requirements Per Service
- unit tests for rule branches,
- feature tests for full write flows,
- authorization tests per method and role,
- tenancy leakage tests (cannot mutate another company data),
- event dispatch assertions.

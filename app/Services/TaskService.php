<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Events\TaskCommentAdded;
use App\Events\TaskCreated;
use App\Events\TaskTransitioned;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectWorkflowStatus;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskPriority;
use App\Models\User;
use App\Models\WorkflowStatus;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\WorkflowServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class TaskService implements TaskServiceInterface
{
    public function __construct(private readonly WorkflowServiceInterface $workflowService) {}

    public function createTask(array $data, User $actor): Task
    {
        Gate::forUser($actor)->authorize('create', Task::class);

        $companyId = $this->requiredCompanyId($actor);
        $project = Project::query()->whereKey($data['project_id'])->where('company_id', $companyId)->firstOrFail();
        $this->assertUserBelongsToCompanyIfProvided($data['assignee_user_id'] ?? null, $companyId);
        $this->assertPriorityBelongsToCompany((int) $data['priority_id'], $companyId);
        $this->assertStatusAllowedForProject((int) $project->id, (int) $data['status_id'], $companyId);
        $this->assertWorkflowStatusMatchesCompany((int) $data['status_id'], $companyId);

        return DB::transaction(function () use ($data, $actor, $companyId, $project): Task {
            $nextTaskNumber = ((int) Task::query()->where('project_id', $project->id)->max('task_number')) + 1;

            $task = new Task;
            $task->fill(array_merge($data, [
                'company_id' => $companyId,
                'task_number' => $nextTaskNumber,
                'reporter_user_id' => $actor->id,
            ]));
            $task->save();

            $this->logTaskActivity($companyId, (int) $task->id, (int) $actor->id, 'task.created', 'Task created.', [
                'project_id' => $project->id,
                'task_number' => $task->task_number,
            ]);

            event(new TaskCreated((int) $task->id, $companyId));

            return $task;
        });
    }

    public function updateTask(int $taskId, array $data, User $actor): Task
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $task);

        $this->assertUserBelongsToCompanyIfProvided($data['assignee_user_id'] ?? $task->assignee_user_id, $companyId);
        if (isset($data['priority_id'])) {
            $this->assertPriorityBelongsToCompany((int) $data['priority_id'], $companyId);
        }
        if (isset($data['status_id'])) {
            $this->assertStatusAllowedForProject((int) $task->project_id, (int) $data['status_id'], $companyId);
            $this->assertWorkflowStatusMatchesCompany((int) $data['status_id'], $companyId);
        }

        $previousAssignee = $task->assignee_user_id;

        return DB::transaction(function () use ($task, $data, $actor, $companyId, $previousAssignee): Task {
            $task->fill($data);
            $task->save();

            $this->logTaskActivity($companyId, (int) $task->id, (int) $actor->id, 'task.updated', 'Task updated.', [
                'changed' => array_keys($data),
            ]);

            if (array_key_exists('assignee_user_id', $data) && (int) $previousAssignee !== (int) $task->assignee_user_id) {
                event(new TaskAssigned(
                    (int) $task->id,
                    $previousAssignee !== null ? (int) $previousAssignee : null,
                    $task->assignee_user_id !== null ? (int) $task->assignee_user_id : null,
                    $companyId
                ));
                $this->logTaskActivity($companyId, (int) $task->id, (int) $actor->id, 'task.assignee_changed', 'Assignee changed.', [
                    'from' => $previousAssignee,
                    'to' => $task->assignee_user_id,
                ]);
            }

            return $task;
        });
    }

    public function transitionTask(int $taskId, int $toStatusId, User $actor): Task
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()
            ->with('project')
            ->whereKey($taskId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        Gate::forUser($actor)->authorize('transition', $task);

        $workflowId = (int) $task->project?->default_workflow_id;
        if (! $workflowId) {
            throw new InvalidArgumentException('Project workflow is not configured.');
        }

        $fromStatusId = (int) $task->status_id;
        $allowed = $this->workflowService->validateTransition($workflowId, $fromStatusId, $toStatusId, $actor);
        if (! $allowed) {
            throw new InvalidArgumentException('Invalid workflow transition.');
        }

        $this->assertStatusAllowedForProject((int) $task->project_id, $toStatusId, $companyId);
        $this->assertWorkflowStatusMatchesCompany($toStatusId, $companyId);

        return DB::transaction(function () use ($task, $toStatusId, $fromStatusId, $companyId, $actor): Task {
            $task->status_id = $toStatusId;
            if ($fromStatusId !== $toStatusId && ! $task->started_at) {
                $task->started_at = now();
            }
            $task->save();

            $this->logTaskActivity($companyId, (int) $task->id, (int) $actor->id, 'task.transitioned', 'Status changed.', [
                'from_status_id' => $fromStatusId,
                'to_status_id' => $toStatusId,
            ]);

            event(new TaskTransitioned((int) $task->id, $fromStatusId, $toStatusId, $companyId));

            return $task;
        });
    }

    public function addWatcher(int $taskId, int $userId, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $task);
        $this->assertUserBelongsToCompanyIfProvided($userId, $companyId);

        DB::transaction(function () use ($companyId, $taskId, $userId, $actor): void {
            DB::table('task_watchers')->updateOrInsert(
                [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                ],
                [
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->logTaskActivity($companyId, $taskId, (int) $actor->id, 'task.watcher_added', 'Watcher added.', [
                'user_id' => $userId,
            ]);
        });
    }

    public function removeWatcher(int $taskId, int $userId, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $task);

        DB::transaction(function () use ($companyId, $taskId, $userId, $actor): void {
            DB::table('task_watchers')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('company_id', $companyId)
                ->delete();

            $this->logTaskActivity($companyId, $taskId, (int) $actor->id, 'task.watcher_removed', 'Watcher removed.', [
                'user_id' => $userId,
            ]);
        });
    }

    public function linkTasks(int $taskIdFrom, int $taskIdTo, string $relationshipType, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $from = Task::query()->whereKey($taskIdFrom)->where('company_id', $companyId)->firstOrFail();
        $to = Task::query()->whereKey($taskIdTo)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $from);

        DB::transaction(function () use ($companyId, $from, $to, $relationshipType, $actor): void {
            DB::table('task_links')->updateOrInsert(
                [
                    'task_id_from' => $from->id,
                    'task_id_to' => $to->id,
                    'relationship_type' => $relationshipType,
                ],
                [
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->logTaskActivity($companyId, (int) $from->id, (int) $actor->id, 'task.linked', 'Linked to another task.', [
                'task_id_to' => $to->id,
                'relationship_type' => $relationshipType,
            ]);
        });
    }

    public function unlinkTasks(int $taskIdFrom, int $taskIdTo, string $relationshipType, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $from = Task::query()->whereKey($taskIdFrom)->where('company_id', $companyId)->firstOrFail();
        Task::query()->whereKey($taskIdTo)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $from);

        DB::transaction(function () use ($companyId, $taskIdFrom, $taskIdTo, $relationshipType, $actor): void {
            DB::table('task_links')
                ->where('company_id', $companyId)
                ->where('task_id_from', $taskIdFrom)
                ->where('task_id_to', $taskIdTo)
                ->where('relationship_type', $relationshipType)
                ->delete();

            $this->logTaskActivity($companyId, $taskIdFrom, (int) $actor->id, 'task.unlinked', 'Task link removed.', [
                'task_id_to' => $taskIdTo,
                'relationship_type' => $relationshipType,
            ]);
        });
    }

    public function linkAsset(int $taskId, string $assetType, int $assetId, string $relationshipType, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $task);
        $this->assertAssetExistsForCompany($assetType, $assetId, $companyId);

        DB::transaction(function () use ($companyId, $taskId, $assetType, $assetId, $relationshipType, $actor): void {
            DB::table('task_asset_links')->updateOrInsert(
                [
                    'task_id' => $taskId,
                    'asset_type' => $assetType,
                    'asset_id' => $assetId,
                    'relationship_type' => $relationshipType,
                ],
                [
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->logTaskActivity($companyId, $taskId, (int) $actor->id, 'task.asset_linked', 'Asset linked.', [
                'asset_type' => $assetType,
                'asset_id' => $assetId,
                'relationship_type' => $relationshipType,
            ]);
        });
    }

    public function unlinkAsset(int $taskId, string $assetType, int $assetId, string $relationshipType, User $actor): void
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('update', $task);

        DB::transaction(function () use ($companyId, $taskId, $assetType, $assetId, $relationshipType, $actor): void {
            DB::table('task_asset_links')
                ->where('company_id', $companyId)
                ->where('task_id', $taskId)
                ->where('asset_type', $assetType)
                ->where('asset_id', $assetId)
                ->where('relationship_type', $relationshipType)
                ->delete();

            $this->logTaskActivity($companyId, $taskId, (int) $actor->id, 'task.asset_unlinked', 'Asset unlinked.', [
                'asset_type' => $assetType,
                'asset_id' => $assetId,
            ]);
        });
    }

    public function addComment(int $taskId, string $body, User $actor, bool $isInternal = false): TaskComment
    {
        $companyId = $this->requiredCompanyId($actor);
        $task = Task::query()->whereKey($taskId)->where('company_id', $companyId)->firstOrFail();
        Gate::forUser($actor)->authorize('comment', $task);

        return DB::transaction(function () use ($body, $actor, $isInternal, $companyId, $taskId): TaskComment {
            $comment = new TaskComment;
            $comment->fill([
                'company_id' => $companyId,
                'task_id' => $taskId,
                'author_user_id' => $actor->id,
                'body' => $body,
                'is_internal' => $isInternal,
            ]);
            $comment->save();

            $this->logTaskActivity($companyId, $taskId, (int) $actor->id, 'task.comment_added', 'Comment added.', [
                'comment_id' => $comment->id,
            ]);

            event(new TaskCommentAdded($taskId, (int) $comment->id, $companyId));

            return $comment;
        });
    }

    private function logTaskActivity(
        int $companyId,
        int $taskId,
        int $actorUserId,
        string $eventType,
        ?string $message,
        ?array $metadata
    ): void {
        DB::table('task_activity_log')->insert([
            'company_id' => $companyId,
            'task_id' => $taskId,
            'actor_user_id' => $actorUserId,
            'event_type' => $eventType,
            'message' => $message,
            'metadata' => $metadata !== null ? json_encode($metadata) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertStatusAllowedForProject(int $projectId, int $statusId, int $companyId): void
    {
        if (! ProjectWorkflowStatus::query()->where('project_id', $projectId)->exists()) {
            return;
        }

        $ok = ProjectWorkflowStatus::query()
            ->where('project_id', $projectId)
            ->where('workflow_status_id', $statusId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $ok) {
            throw new InvalidArgumentException('That status is not enabled for this project.');
        }
    }

    private function assertWorkflowStatusMatchesCompany(int $statusId, int $companyId): void
    {
        $status = WorkflowStatus::query()->withoutGlobalScopes()->whereKey($statusId)->first();
        if (! $status) {
            throw new InvalidArgumentException('Invalid workflow status.');
        }
        if ($status->company_id !== null && (int) $status->company_id !== $companyId) {
            throw new InvalidArgumentException('Workflow status belongs to another company.');
        }
    }

    private function assertPriorityBelongsToCompany(int $priorityId, int $companyId): void
    {
        $priority = TaskPriority::query()->withoutGlobalScopes()->whereKey($priorityId)->first();
        if (! $priority) {
            throw new InvalidArgumentException('Invalid task priority.');
        }
        if ($priority->company_id !== null && (int) $priority->company_id !== $companyId) {
            throw new InvalidArgumentException('Task priority belongs to another company.');
        }
    }

    private function assertAssetExistsForCompany(string $assetType, int $assetId, int $companyId): void
    {
        if ($assetType === 'asset' || $assetType === Asset::class) {
            $exists = Asset::query()->whereKey($assetId)->where('company_id', $companyId)->exists();
            if (! $exists) {
                throw new InvalidArgumentException('Asset not found in current company.');
            }

            return;
        }

        throw new InvalidArgumentException('Unsupported asset type for linking.');
    }

    private function requiredCompanyId(User $actor): int
    {
        if (empty($actor->current_company_id)) {
            throw new InvalidArgumentException('A selected company is required for tenant-owned write operations.');
        }

        return (int) $actor->current_company_id;
    }

    private function assertUserBelongsToCompanyIfProvided(?int $userId, int $companyId): void
    {
        if (is_null($userId)) {
            return;
        }

        $exists = DB::table('company_user')
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('User must belong to current company.');
        }
    }
}

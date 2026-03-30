<?php

namespace App\Services\Contracts;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

interface TaskServiceInterface
{
    public function createTask(array $data, User $actor): Task;

    public function updateTask(int $taskId, array $data, User $actor): Task;

    public function transitionTask(int $taskId, int $toStatusId, User $actor): Task;

    public function addWatcher(int $taskId, int $userId, User $actor): void;

    public function removeWatcher(int $taskId, int $userId, User $actor): void;

    public function linkTasks(int $taskIdFrom, int $taskIdTo, string $relationshipType, User $actor): void;

    public function unlinkTasks(int $taskIdFrom, int $taskIdTo, string $relationshipType, User $actor): void;

    public function linkAsset(int $taskId, string $assetType, int $assetId, string $relationshipType, User $actor): void;

    public function unlinkAsset(int $taskId, string $assetType, int $assetId, string $relationshipType, User $actor): void;

    public function addComment(int $taskId, string $body, User $actor, bool $isInternal = false): TaskComment;
}

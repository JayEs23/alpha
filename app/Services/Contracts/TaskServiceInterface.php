<?php

namespace App\Services\Contracts;

use App\Models\User;

interface TaskServiceInterface
{
    public function createTask(array $data, User $actor): array;

    public function updateTask(int $taskId, array $data, User $actor): array;

    public function transitionTask(int $taskId, int $toStatusId, User $actor): array;
}

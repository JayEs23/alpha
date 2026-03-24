<?php

namespace App\Services\Contracts;

use App\Models\User;

interface WorkflowServiceInterface
{
    public function validateTransition(int $workflowId, int $fromStatusId, int $toStatusId, User $actor): bool;
}

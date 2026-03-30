<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkflowTransition;
use App\Services\Contracts\WorkflowServiceInterface;

class WorkflowService implements WorkflowServiceInterface
{
    public function validateTransition(int $workflowId, int $fromStatusId, int $toStatusId, User $actor): bool
    {
        $companyId = (int) $actor->current_company_id;

        return WorkflowTransition::query()
            ->where('workflow_id', $workflowId)
            ->where('from_status_id', $fromStatusId)
            ->where('to_status_id', $toStatusId)
            ->whereHas('workflow', function ($query) use ($companyId): void {
                $query->where(function ($scope) use ($companyId): void {
                    $scope->whereNull('company_id')->orWhere('company_id', $companyId);
                });
            })
            ->exists();
    }
}

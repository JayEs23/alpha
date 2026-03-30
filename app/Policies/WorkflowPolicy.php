<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('workflow.view');
    }

    public function view(User $user, Workflow $workflow): bool
    {
        return $user->can('workflow.view')
            && (is_null($workflow->company_id) || $user->hasCompanyModel($workflow));
    }

    public function create(User $user): bool
    {
        return $user->can('workflow.update') && ! is_null($user->current_company_id);
    }

    public function update(User $user, Workflow $workflow): bool
    {
        return $user->can('workflow.update')
            && (is_null($workflow->company_id) || $user->hasCompanyModel($workflow));
    }
}

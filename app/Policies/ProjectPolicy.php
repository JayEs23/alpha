<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view') && $user->hasCompanyModel($project);
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create') && ! is_null($user->current_company_id);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update') && $user->hasCompanyModel($project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.archive') && $user->hasCompanyModel($project);
    }
}

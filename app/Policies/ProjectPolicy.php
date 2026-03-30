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
        return $user->can('projects.view') || $user->can('view_any_project');
    }

    public function view(User $user, Project $project): bool
    {
        return ($user->can('projects.view') || $user->can('view_project')) && $user->hasCompanyModel($project);
    }

    public function create(User $user): bool
    {
        return ($user->can('projects.create') || $user->can('create_project')) && ! is_null($user->current_company_id);
    }

    public function update(User $user, Project $project): bool
    {
        return ($user->can('projects.update') || $user->can('update_project')) && $user->hasCompanyModel($project);
    }

    public function delete(User $user, Project $project): bool
    {
        return ($user->can('projects.archive') || $user->can('delete_project')) && $user->hasCompanyModel($project);
    }
}

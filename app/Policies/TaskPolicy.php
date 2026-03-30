<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view') || $user->can('view_any_task');
    }

    public function view(User $user, Task $task): bool
    {
        return ($user->can('tasks.view') || $user->can('view_task')) && $user->hasCompanyModel($task);
    }

    public function create(User $user): bool
    {
        return ($user->can('tasks.create') || $user->can('create_task')) && ! is_null($user->current_company_id);
    }

    public function update(User $user, Task $task): bool
    {
        return ($user->can('tasks.update') || $user->can('update_task')) && $user->hasCompanyModel($task);
    }

    public function transition(User $user, Task $task): bool
    {
        return ($user->can('tasks.transition') || $user->can('update_task')) && $user->hasCompanyModel($task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->can('comments.create') && $user->hasCompanyModel($task);
    }

    public function delete(User $user, Task $task): bool
    {
        return ($user->can('tasks.delete') || $user->can('delete_task')) && $user->hasCompanyModel($task);
    }
}

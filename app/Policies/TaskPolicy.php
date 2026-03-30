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
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') && $user->hasCompanyModel($task);
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create') && ! is_null($user->current_company_id);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') && $user->hasCompanyModel($task);
    }

    public function transition(User $user, Task $task): bool
    {
        return $user->can('tasks.transition') && $user->hasCompanyModel($task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->can('comments.create') && $user->hasCompanyModel($task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete') && $user->hasCompanyModel($task);
    }
}

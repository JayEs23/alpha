<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskCommentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('comments.create')
            || $user->can('tasks.view')
            || $user->can('view_any_task');
    }

    public function view(User $user, TaskComment $comment): bool
    {
        return ($user->can('tasks.view') || $user->can('view_task')) && $user->hasCompanyModel($comment);
    }

    public function create(User $user): bool
    {
        return ($user->can('comments.create') || $user->can('create_task')) && ! is_null($user->current_company_id);
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        if (! $user->hasCompanyModel($comment)) {
            return false;
        }

        return $user->can('comments.delete')
            || $user->can('delete_task')
            || (int) $comment->author_user_id === (int) $user->id;
    }
}

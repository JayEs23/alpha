<?php

namespace App\Policies;

use App\Models\Employeeship;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeshipPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_employeeship');
    }

    public function view(User $user, Employeeship $employeeship): bool
    {
        return $user->can('view_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function create(User $user): bool
    {
        return $user->can('create_employeeship') && $user->current_company_id !== null;
    }

    public function update(User $user, Employeeship $employeeship): bool
    {
        return $user->can('update_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function delete(User $user, Employeeship $employeeship): bool
    {
        return $user->can('delete_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_employeeship');
    }

    public function forceDelete(User $user, Employeeship $employeeship): bool
    {
        return $user->can('force_delete_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_employeeship');
    }

    public function restore(User $user, Employeeship $employeeship): bool
    {
        return $user->can('restore_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_employeeship');
    }

    public function replicate(User $user, Employeeship $employeeship): bool
    {
        return $user->can('replicate_employeeship') && $user->hasCompanyModel($employeeship);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_employeeship');
    }
}

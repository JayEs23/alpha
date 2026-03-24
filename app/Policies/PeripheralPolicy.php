<?php

namespace App\Policies;

use App\Models\Peripheral;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PeripheralPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_peripheral') || $user->can('view_any_periphel');
    }

    public function view(User $user, Peripheral $peripheral)
    {
        return
            $user->can('view_peripheral') ||
            $user->can('view_periphel') ||
            $user->hasCompanyModel($peripheral);
    }

    public function create(User $user)
    {
        return
            ($user->can('create_peripheral') || $user->can('create_peripheral')) &&
            ! is_null($user->current_company_id);
    }

    public function update(User $user, Peripheral $peripheral)
    {
        return
            $user->can('update_peripheral') ||
            $user->can('update_periphel') ||
            $user->hasCompanyModel($peripheral);
    }

    public function delete(User $user, Peripheral $peripheral)
    {
        return
            $user->can('delete_peripheral') ||
            $user->can('delete_periphel') ||
            $user->hasCompanyModel($peripheral);
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_peripheral') || $user->can('delete_any_periphel');
    }

    public function forceDelete(User $user, Peripheral $peripheral)
    {
        return
            $user->can('force_delete_peripheral') ||
            $user->can('force_delete_periphel') ||
            $user->hasCompanyModel($peripheral);
    }

    public function forceDeleteAny(User $user)
    {
        return $user->can('force_delete_any_peripheral') || $user->can('force_delete_any_periphel');
    }

    public function restore(User $user, Peripheral $peripheral)
    {
        return
            $user->can('restore_peripheral') ||
            $user->can('restore_periphel') ||
            $user->hasCompanyModel($peripheral);
    }

    public function restoreAny(User $user)
    {
        return $user->can('restore_any_peripheral') || $user->can('restore_any_periphel');
    }

    public function replicate(User $user, Peripheral $peripheral)
    {
        return $user->can('replicate_peripheral') || $user->can('replicate_periphel');
    }

    public function reorder(User $user)
    {
        return $user->can('reorder_peripheral') || $user->can('reorder_periphel');
    }
}

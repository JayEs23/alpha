<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isSuperAdmin()
            || $user->hasRole(config('filament-shield.filament_user.name', 'filament_user'))
            || $user->can('view_any_provider')
            || $user->can('view_any_asset')
            || $user->can('assets.view');
    }

    public function view(User $user, Provider $provider)
    {
        return
            $user->can('view_provider') ||
            $user->hasCompanyModel($provider);
    }

    public function create(User $user)
    {
        return
            $user->can('create_provider') &&
            ! is_null($user->current_company_id);
    }

    public function update(User $user, Provider $provider)
    {
        return
            $user->can('update_provider') ||
            $user->hasCompanyModel($provider);
    }

    public function delete(User $user, Provider $provider)
    {
        return
            $user->can('delete_provider') ||
            $user->hasCompanyModel($provider);
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_provider');
    }

    public function forceDelete(User $user, Provider $provider)
    {
        return $user->can('force_delete_provider');
    }

    public function forceDeleteAny(User $user)
    {
        return $user->can('force_delete_any_provider');
    }

    public function restore(User $user, Provider $provider)
    {
        return $user->can('restore_provider');
    }

    public function restoreAny(User $user)
    {
        return $user->can('restore_any_provider');
    }

    public function replicate(User $user, Provider $provider)
    {
        return $user->can('replicate_provider');
    }

    public function reorder(User $user)
    {
        return $user->can('reorder_provider');
    }
}

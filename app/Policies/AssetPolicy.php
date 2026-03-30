<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    /**
     * Domain permissions (assets.*) and Filament Shield resource perms (e.g. view_any_asset) must both apply.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('assets.view') || $user->can('view_any_asset');
    }

    public function view(User $user, Asset $asset): bool
    {
        return ($user->can('assets.view') || $user->can('view_asset')) && $user->hasCompanyModel($asset);
    }

    public function create(User $user): bool
    {
        return ($user->can('assets.create') || $user->can('create_asset')) && ! is_null($user->current_company_id);
    }

    public function update(User $user, Asset $asset): bool
    {
        return ($user->can('assets.update') || $user->can('update_asset')) && $user->hasCompanyModel($asset);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return ($user->can('assets.retire') || $user->can('delete_asset')) && $user->hasCompanyModel($asset);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('assets.retire') || $user->can('delete_any_asset');
    }

    public function restore(User $user, Asset $asset): bool
    {
        return ($user->can('assets.update') || $user->can('restore_asset')) && $user->hasCompanyModel($asset);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('assets.update') || $user->can('restore_any_asset');
    }

    public function forceDelete(User $user, Asset $asset): bool
    {
        return ($user->can('assets.retire') || $user->can('force_delete_asset')) && $user->hasCompanyModel($asset);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('assets.retire') || $user->can('force_delete_any_asset');
    }

    public function replicate(User $user, Asset $asset): bool
    {
        return ($user->can('assets.create') || $user->can('replicate_asset')) && $user->hasCompanyModel($asset);
    }

    public function reorder(User $user): bool
    {
        return $user->can('assets.update') || $user->can('reorder_asset');
    }
}

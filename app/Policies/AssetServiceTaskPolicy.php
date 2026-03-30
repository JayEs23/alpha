<?php

namespace App\Policies;

use App\Models\AssetServiceTask;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetServiceTaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('asset_service_tasks.view') || $user->can('view_any_asset::service::task');
    }

    public function view(User $user, AssetServiceTask $task): bool
    {
        return ($user->can('asset_service_tasks.view') || $user->can('view_asset::service::task')) && $user->hasCompanyModel($task);
    }

    public function create(User $user): bool
    {
        return ($user->can('asset_service_tasks.assign') || $user->can('create_asset::service::task')) && ! is_null($user->current_company_id);
    }

    public function update(User $user, AssetServiceTask $task): bool
    {
        return ($user->can('asset_service_tasks.assign')
            || $user->can('update_asset::service::task')
            || $user->can('asset_service_tasks.complete')) && $user->hasCompanyModel($task);
    }

    public function delete(User $user, AssetServiceTask $task): bool
    {
        return ($user->can('asset_service_tasks.assign') || $user->can('delete_asset::service::task')) && $user->hasCompanyModel($task);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_asset::service::task');
    }

    public function replicate(User $user, AssetServiceTask $task): bool
    {
        return $user->can('replicate_asset::service::task') && $user->hasCompanyModel($task);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_asset::service::task');
    }

    public function forceDelete(User $user, AssetServiceTask $task): bool
    {
        return $user->can('force_delete_asset::service::task') && $user->hasCompanyModel($task);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_asset::service::task');
    }

    public function restore(User $user, AssetServiceTask $task): bool
    {
        return $user->can('restore_asset::service::task') && $user->hasCompanyModel($task);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_asset::service::task');
    }
}

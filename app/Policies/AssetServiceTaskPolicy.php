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
        return $user->can('asset_service_tasks.view');
    }

    public function view(User $user, AssetServiceTask $task): bool
    {
        return $user->can('asset_service_tasks.view') && $user->hasCompanyModel($task);
    }

    public function create(User $user): bool
    {
        return $user->can('asset_service_tasks.assign') && ! is_null($user->current_company_id);
    }

    public function update(User $user, AssetServiceTask $task): bool
    {
        return $user->can('asset_service_tasks.assign') && $user->hasCompanyModel($task);
    }

    public function delete(User $user, AssetServiceTask $task): bool
    {
        return $user->can('asset_service_tasks.assign') && $user->hasCompanyModel($task);
    }
}

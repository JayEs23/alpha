<?php

namespace App\Policies;

use App\Models\AssetServicePlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetServicePlanPolicy
{
    use HandlesAuthorization;

    /**
     * Shield permission names for nested resources use "::" (e.g. view_any_asset::service::plan), not underscores.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('asset_service_plans.view') || $user->can('view_any_asset::service::plan');
    }

    public function view(User $user, AssetServicePlan $plan): bool
    {
        return ($user->can('asset_service_plans.view') || $user->can('view_asset::service::plan')) && $user->hasCompanyModel($plan);
    }

    public function create(User $user): bool
    {
        return ($user->can('asset_service_plans.create') || $user->can('create_asset::service::plan')) && ! is_null($user->current_company_id);
    }

    public function update(User $user, AssetServicePlan $plan): bool
    {
        return ($user->can('asset_service_plans.update') || $user->can('update_asset::service::plan')) && $user->hasCompanyModel($plan);
    }

    public function delete(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('delete_asset::service::plan') && $user->hasCompanyModel($plan);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_asset::service::plan');
    }

    public function replicate(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('replicate_asset::service::plan') && $user->hasCompanyModel($plan);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_asset::service::plan');
    }

    public function forceDelete(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('force_delete_asset::service::plan') && $user->hasCompanyModel($plan);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_asset::service::plan');
    }

    public function restore(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('restore_asset::service::plan') && $user->hasCompanyModel($plan);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_asset::service::plan');
    }
}

<?php

namespace App\Policies;

use App\Models\AssetServicePlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetServicePlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('asset_service_plans.view');
    }

    public function view(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('asset_service_plans.view') && $user->hasCompanyModel($plan);
    }

    public function create(User $user): bool
    {
        return $user->can('asset_service_plans.create') && ! is_null($user->current_company_id);
    }

    public function update(User $user, AssetServicePlan $plan): bool
    {
        return $user->can('asset_service_plans.update') && $user->hasCompanyModel($plan);
    }
}

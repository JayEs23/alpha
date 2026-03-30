<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->can('assets.view') && $user->hasCompanyModel($asset);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.create') && ! is_null($user->current_company_id);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->can('assets.update') && $user->hasCompanyModel($asset);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->can('assets.retire') && $user->hasCompanyModel($asset);
    }
}

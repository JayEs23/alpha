<?php

namespace App\Policies;

use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyInvitationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_company::invitation');
    }

    public function view(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('view_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function create(User $user): bool
    {
        return $user->can('create_company::invitation') && $user->current_company_id !== null;
    }

    public function update(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('update_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function delete(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('delete_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_company::invitation');
    }

    public function forceDelete(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('force_delete_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_company::invitation');
    }

    public function restore(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('restore_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_company::invitation');
    }

    public function replicate(User $user, CompanyInvitation $invitation): bool
    {
        return $user->can('replicate_company::invitation') && $user->hasCompanyModel($invitation);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_company::invitation');
    }
}

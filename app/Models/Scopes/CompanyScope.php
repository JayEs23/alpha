<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /** @var array<int, bool> */
    private static array $superAdminCache = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $userId = (int) $user->getAuthIdentifier();

        if (! array_key_exists($userId, self::$superAdminCache)) {
            self::$superAdminCache[$userId] = $user->hasRole('super_admin');
        }

        if (self::$superAdminCache[$userId]) {
            return;
        }

        if (! empty($user->current_company_id)) {
            $builder->where('company_id', $user->current_company_id);
            return;
        }

        // Authenticated user without selected company should see no tenant-scoped records.
        $builder->whereRaw('1 = 0');
    }
}

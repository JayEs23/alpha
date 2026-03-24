<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
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

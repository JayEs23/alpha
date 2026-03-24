<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

trait HasCompanyId
{
    protected static function bootHasCompanyId(): void
    {
        static::creating(function (Model $model): void {
            if (auth()->check() && ! empty(auth()->user()->current_company_id)) {
                $model->company_id = auth()->user()->current_company_id;
            }
        });

        static::addGlobalScope(new CompanyScope);
    }
}

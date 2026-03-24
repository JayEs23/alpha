<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

trait HasComapanyId
{
    protected static function bootHasComapanyId()
    {
        static::creating(function (Model $model) {
            if (auth()->check() && ! empty(auth()->user()->current_company_id)) {
                $model->company_id = auth()->user()->current_company_id;
            }
        });

        static::addGlobalScope(new CompanyScope);
    }
}

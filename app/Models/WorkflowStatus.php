<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStatus extends Model
{
    use HasCompanyId;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'category',
        'sort_order',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fromTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'from_status_id');
    }

    public function toTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'to_status_id');
    }
}

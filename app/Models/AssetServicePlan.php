<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetServicePlan extends Model
{
    use HasFactory;
    use HasCompanyId;

    protected $fillable = [
        'company_id',
        'asset_id',
        'name',
        'service_interval_days',
        'reminder_days_before',
        'default_assigned_user_id',
        'next_due_at',
        'last_completed_at',
        'is_active',
        'instructions',
    ];

    protected $casts = [
        'service_interval_days' => 'integer',
        'reminder_days_before' => 'integer',
        'next_due_at' => 'datetime',
        'last_completed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function defaultAssignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_assigned_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AssetServiceTask::class, 'service_plan_id');
    }
}

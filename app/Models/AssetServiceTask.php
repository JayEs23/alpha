<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetServiceTask extends Model
{
    use HasFactory;
    use HasCompanyId;

    protected $fillable = [
        'company_id',
        'asset_id',
        'service_plan_id',
        'status_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'description',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(AssetServicePlan::class, 'service_plan_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetServiceTaskStatus::class, 'status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(AssetServiceReminder::class, 'service_task_id');
    }
}

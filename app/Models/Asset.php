<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasCompanyId;

    protected $fillable = [
        'company_id',
        'category_id',
        'status_id',
        'provider_id',
        'assigned_user_id',
        'name',
        'asset_tag',
        'serial',
        'purchased_at',
        'retired_at',
        'metadata',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'retired_at' => 'date',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'status_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function servicePlans(): HasMany
    {
        return $this->hasMany(AssetServicePlan::class, 'asset_id');
    }

    public function serviceTasks(): HasMany
    {
        return $this->hasMany(AssetServiceTask::class, 'asset_id');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->name;
    }

    public function getDisplaySubtitleAttribute(): ?string
    {
        $office = data_get($this->metadata ?? [], 'office');
        $identifier = $office
            ?: $this->assignedUser?->name
            ?: $this->asset_tag
            ?: $this->serial;

        if (! $identifier) {
            return null;
        }

        $parts = [$identifier];

        if ($this->asset_tag && $identifier !== $this->asset_tag) {
            $parts[] = $this->asset_tag;
        }

        if ($this->serial && $this->serial !== $identifier) {
            $parts[] = $this->serial;
        }

        return implode(' • ', $parts);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->display_subtitle
            ? "{$this->display_label} - {$this->display_subtitle}"
            : $this->display_label;
    }
}

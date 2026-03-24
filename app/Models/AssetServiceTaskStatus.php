<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetServiceTaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_terminal',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
    ];

    public function serviceTasks(): HasMany
    {
        return $this->hasMany(AssetServiceTask::class, 'status_id');
    }
}

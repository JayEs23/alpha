<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use App\Traits\HasUserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hardware extends Model
{
    use HasFactory,
        SoftDeletes,
        HasUserId,
        HasCompanyId;

    protected $table = 'hardware';

    protected $fillable = [
        'make', 'model', 'serial', 'os_name', 'company_id', 'os_version', 'type', 'status', 'ram', 'cpu', 'user_id', 'provider_id', 'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}

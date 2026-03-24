<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use App\Traits\HasUserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peripheral extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUserId;
    use HasCompanyId;

    protected $table = 'periphels';

    protected $fillable = [
        'make',
        'model',
        'serial',
        'company_id',
        'type',
        'user_id',
        'provaider_id',
        'purchased_at',
        'current',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'current' => 'boolean',
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
        return $this->belongsTo(Provider::class, 'provaider_id');
    }

    public function provaider(): BelongsTo
    {
        return $this->provider();
    }
}

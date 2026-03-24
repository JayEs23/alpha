<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;
    use HasCompanyId;

    protected $table = 'provaiders';

    protected $fillable = [
        'name',
        'company_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(Hardware::class, 'provaider_id');
    }

    public function software(): HasMany
    {
        return $this->hasMany(Software::class, 'provaider_id');
    }

    public function peripherals(): HasMany
    {
        return $this->hasMany(Peripheral::class, 'provaider_id');
    }

    public function periphels(): HasMany
    {
        return $this->peripherals();
    }
}

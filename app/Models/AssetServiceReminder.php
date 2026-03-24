<?php

namespace App\Models;

use App\Traits\HasCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetServiceReminder extends Model
{
    use HasFactory;
    use HasCompanyId;

    protected $fillable = [
        'company_id',
        'service_task_id',
        'recipient_user_id',
        'remind_at',
        'sent_at',
        'message',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceTask(): BelongsTo
    {
        return $this->belongsTo(AssetServiceTask::class, 'service_task_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}

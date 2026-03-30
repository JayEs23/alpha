<?php

namespace App\Services;

use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService implements NotificationServiceInterface
{
    public function dispatchDomainNotification(string $event, array $payload): void
    {
        if (empty($payload['notifiable_type']) || empty($payload['notifiable_id'])) {
            return;
        }

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $event,
            'notifiable_type' => $payload['notifiable_type'],
            'notifiable_id' => $payload['notifiable_id'],
            'data' => json_encode($payload['data'] ?? [], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

<?php

namespace App\Services\Contracts;

interface NotificationServiceInterface
{
    public function dispatchDomainNotification(string $event, array $payload): void;
}

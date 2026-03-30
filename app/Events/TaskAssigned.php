<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $taskId,
        public readonly ?int $previousAssigneeUserId,
        public readonly ?int $newAssigneeUserId,
        public readonly int $companyId
    ) {}
}

<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $taskId,
        public readonly int $companyId
    ) {}
}

<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetServiceReminderScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $reminderId,
        public readonly int $serviceTaskId,
        public readonly int $companyId
    ) {
    }
}

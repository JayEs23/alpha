<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProviderUpserted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $providerId,
        public readonly int $companyId,
        public readonly int $actorId,
        public readonly string $action
    ) {
    }
}

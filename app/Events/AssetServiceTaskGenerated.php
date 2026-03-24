<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetServiceTaskGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $serviceTaskId,
        public readonly int $assetId,
        public readonly int $companyId
    ) {
    }
}

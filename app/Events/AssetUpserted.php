<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetUpserted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $assetType,
        public readonly int $assetId,
        public readonly int $companyId,
        public readonly int $actorId,
        public readonly string $action
    ) {
    }
}

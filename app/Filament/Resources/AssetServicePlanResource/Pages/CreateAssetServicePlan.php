<?php

namespace App\Filament\Resources\AssetServicePlanResource\Pages;

use App\Filament\Resources\AssetServicePlanResource;
use App\Models\AssetServicePlan;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetServicePlan extends CreateRecord
{
    protected static string $resource = AssetServicePlanResource::class;

    protected function handleRecordCreation(array $data): AssetServicePlan
    {
        /** @var AssetServiceInterface $assetService */
        $assetService = app(AssetServiceInterface::class);

        $assetId = (int) $data['asset_id'];
        unset($data['asset_id']);

        return $assetService->createServicePlan($assetId, $data, auth()->user());
    }
}

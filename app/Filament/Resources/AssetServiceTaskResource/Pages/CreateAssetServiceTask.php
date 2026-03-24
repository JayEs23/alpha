<?php

namespace App\Filament\Resources\AssetServiceTaskResource\Pages;

use App\Filament\Resources\AssetServiceTaskResource;
use App\Models\AssetServiceTask;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetServiceTask extends CreateRecord
{
    protected static string $resource = AssetServiceTaskResource::class;

    protected function handleRecordCreation(array $data): AssetServiceTask
    {
        /** @var AssetServiceInterface $assetService */
        $assetService = app(AssetServiceInterface::class);

        return $assetService->createServiceTask($data, auth()->user());
    }
}

<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Resources\Pages\CreateRecord;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function handleRecordCreation(array $data): Asset
    {
        /** @var AssetServiceInterface $assetService */
        $assetService = app(AssetServiceInterface::class);

        return $assetService->createAsset($data, auth()->user());
    }
}

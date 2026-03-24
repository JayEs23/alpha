<?php

namespace App\Filament\Resources\AssetServiceTaskResource\Pages;

use App\Filament\Resources\AssetServiceTaskResource;
use App\Models\AssetServiceTask;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetServiceTask extends EditRecord
{
    protected static string $resource = AssetServiceTaskResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): AssetServiceTask
    {
        /** @var AssetServiceInterface $assetService */
        $assetService = app(AssetServiceInterface::class);

        return $assetService->updateServiceTask($record, $data, auth()->user());
    }
}

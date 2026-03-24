<?php

namespace App\Filament\Resources\AssetServicePlanResource\Pages;

use App\Filament\Resources\AssetServicePlanResource;
use App\Models\AssetServicePlan;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetServicePlan extends EditRecord
{
    protected static string $resource = AssetServicePlanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): AssetServicePlan
    {
        /** @var AssetServiceInterface $assetService */
        $assetService = app(AssetServiceInterface::class);

        return $assetService->updateServicePlan($record, $data, auth()->user());
    }
}

<?php

namespace App\Filament\Resources\AssetServicePlanResource\Pages;

use App\Filament\Resources\AssetServicePlanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetServicePlans extends ListRecords
{
    protected static string $resource = AssetServicePlanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

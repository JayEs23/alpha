<?php

namespace App\Filament\Resources\AssetServiceTaskResource\Pages;

use App\Filament\Resources\AssetServiceTaskResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetServiceTasks extends ListRecords
{
    protected static string $resource = AssetServiceTaskResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

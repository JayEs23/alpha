<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use Filament\Resources\Pages\ListRecords;

class ListProviders extends ListRecords
{
    protected static string $resource = ProviderResource::class;

    protected function getActions(): array
    {
        return [
            // Create is intentionally disabled at the resource routing level.
        ];
    }
}

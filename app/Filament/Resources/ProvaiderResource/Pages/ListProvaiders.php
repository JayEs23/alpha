<?php

namespace App\Filament\Resources\ProvaiderResource\Pages;

use App\Filament\Resources\ProvaiderResource;
use Filament\Resources\Pages\ListRecords;

class ListProvaiders extends ListRecords
{
    protected static string $resource = ProvaiderResource::class;

    protected function getActions(): array
    {
        return [
            // Create is intentionally disabled at the resource routing level.
        ];
    }
}

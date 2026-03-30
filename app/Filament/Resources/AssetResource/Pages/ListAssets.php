<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Filters\Layout;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected int $defaultTableRecordsPerPageSelectOption = 25;

    protected function getActions(): array
    {
        return [];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            TableAction::make('create')
                ->label('New asset')
                ->url(static::getResource()::getUrl('create'))
                ->icon('heroicon-o-plus')
                ->button()
                ->color('primary'),
        ];
    }

    protected function getTableFiltersLayout(): ?string
    {
        return Layout::AboveContentCollapsible;
    }

    protected function isTableStriped(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}

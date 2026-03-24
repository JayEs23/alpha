<?php

namespace App\Filament\Resources\PeripheralResource\Pages;

use App\Filament\Resources\PeripheralResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeripheral extends EditRecord
{
    protected static string $resource = PeripheralResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}

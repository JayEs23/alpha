<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $actor = auth()->user();
        $company = $actor?->currentCompany;

        if (! $company instanceof Company || ! $this->record) {
            return;
        }

        if ($this->record->current_company_id !== null) {
            return;
        }

        if (! $company->users()->where('users.id', $this->record->id)->exists()) {
            $company->users()->attach($this->record->id, ['role' => 'editor']);
        }

        $this->record->forceFill(['current_company_id' => $company->id])->save();
    }
}

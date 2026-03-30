<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $actor = auth()->user();
        $company = $actor?->currentCompany;

        if (! $company instanceof Company || ! $this->record) {
            return;
        }

        if (! $company->users()->where('users.id', $this->record->id)->exists()) {
            $company->users()->attach($this->record->id, ['role' => 'editor']);
        }

        if ($this->record->current_company_id === null) {
            $this->record->forceFill(['current_company_id' => $company->id])->save();
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CompanyInvitationResource;
use App\Filament\Resources\UserResource;
use Filament\Pages\Page;
use Wallo\FilamentCompanies\Pages\Companies\CompanySettings;

class ManageTeam extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Team';

    protected static ?string $title = 'Team';

    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.manage-team';

    protected static ?string $navigationGroup = 'Company';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->current_company_id && $user->currentCompany;
    }

    public function getCompanySettingsUrl(): ?string
    {
        $company = auth()->user()?->currentCompany;

        if (! $company) {
            return null;
        }

        return CompanySettings::getUrl(['company' => $company]);
    }

    public function getCreateUserUrl(): string
    {
        return UserResource::getUrl('create');
    }

    public function getInvitationsUrl(): string
    {
        return CompanyInvitationResource::getUrl('index');
    }
}

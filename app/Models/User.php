<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Wallo\FilamentCompanies\HasCompanies;
use Wallo\FilamentCompanies\HasProfilePhoto;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        HasCompanies,
        Notifiable,
        HasRoles,
        TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function canAccessFilament(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Filament Shield: panel access for non–super-admins (assign `filament_user` and/or resource perms).
        if ($this->hasRole(config('filament-shield.filament_user.name', 'filament_user'))) {
            return true;
        }

        // Domain + Shield: allow panel if they can open the Assets area (custom or generated perms).
        // Nested Filament resources use Shield identifiers with "::" (see FilamentShield::getDefaultPermissionIdentifier).
        return $this->can('assets.view')
            || $this->can('view_any_asset')
            || $this->can('view_any_asset::service::plan')
            || $this->can('view_any_asset::service::task')
            || $this->can('view_any_project')
            || $this->can('view_any_task')
            || $this->can('projects.view')
            || $this->can('tasks.view');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(Hardware::class);
    }

    

    public function hasSelectCompanyPermission(string $permission): bool
    {
        return $this->hasSelectedCompanyPermission($permission);
    }

    public function hasSelectedCompanyPermission(string $permission): bool
    {
        $company = $this->currentCompany();

        return $company ? $this->hasCompanyPermission($company, $permission) : false;
    }

    public function hasCompanyModel(Model $model): bool
    {
        return (int) $this->current_company_id === (int) $model->company_id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->isSuperAdmin();
    }
}

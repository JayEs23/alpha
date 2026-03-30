<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Single company (Leapsoft Limited) and one Filament company admin.
     */
    public function run(): void
    {
        $user = User::query()->create([
            'name' => 'Company Admin',
            'email' => 'admin@leapsoft.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super_admin');

        $company = Company::query()->create([
            'name' => 'Leapsoft Limited',
            'user_id' => $user->id,
            'personal_company' => false,
        ]);

        $company->users()->attach($user->id, ['role' => 'admin']);

        $user->forceFill(['current_company_id' => $company->id])->save();
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            RoleHasPermissionsTableSeeder::class,
            WorkDomainPermissionsSeeder::class,
            UserSeeder::class,
            AssetOperationsSeeder::class,
            WorkDomainSeeder::class,
        ]);

        // Inventory for hardware / provider-linked records comes from
        // DevAssetsCsvSeeder via AssetOperationsSeeder (public/devassets CSVs).

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

    }
    // php artisan iseed permissions,roles
}

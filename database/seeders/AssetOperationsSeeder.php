<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetServiceTaskStatus;
use App\Models\AssetStatus;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AssetOperationsSeeder extends Seeder
{
    public function run(): void
    {
        AssetServiceTaskStatus::query()->upsert([
            ['code' => 'open', 'name' => 'Open', 'is_terminal' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'in_progress', 'name' => 'In Progress', 'is_terminal' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'completed', 'name' => 'Completed', 'is_terminal' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code'], ['name', 'is_terminal', 'updated_at']);

        $categories = [
            ['name' => 'IT Device', 'code' => 'it_device'],
            ['name' => 'Office Equipment', 'code' => 'office_equipment'],
            ['name' => 'Facility Equipment', 'code' => 'facility_equipment'],
            ['name' => 'Vehicle', 'code' => 'vehicle'],
            ['name' => 'Tooling', 'code' => 'tooling'],
            ['name' => 'Access Control', 'code' => 'access_control'],
            ['name' => 'Server & Storage', 'code' => 'server_and_storage'],
            ['name' => 'Network Equipment', 'code' => 'network_equipment'],
            ['name' => 'Telco Room', 'code' => 'telco_room'],
            ['name' => 'Audio / Codes', 'code' => 'audio_codes'],
            ['name' => 'PC Workstation', 'code' => 'pc_workstation'],
            ['name' => 'Headset', 'code' => 'headset'],
        ];

        $statuses = [
            ['name' => 'Active', 'code' => 'active', 'is_terminal' => false, 'sort_order' => 10],
            ['name' => 'Idle', 'code' => 'idle', 'is_terminal' => false, 'sort_order' => 15],
            ['name' => 'Deployed', 'code' => 'deployed', 'is_terminal' => false, 'sort_order' => 18],
            ['name' => 'Faulty', 'code' => 'faulty', 'is_terminal' => false, 'sort_order' => 19],
            ['name' => 'Maintenance', 'code' => 'maintenance', 'is_terminal' => false, 'sort_order' => 20],
            ['name' => 'Retired', 'code' => 'retired', 'is_terminal' => true, 'sort_order' => 30],
        ];

        foreach (Company::query()->get() as $company) {
            foreach ($categories as $category) {
                AssetCategory::query()->updateOrCreate(
                    ['company_id' => $company->id, 'code' => $category['code']],
                    ['name' => $category['name'], 'is_active' => true]
                );
            }

            foreach ($statuses as $status) {
                AssetStatus::query()->updateOrCreate(
                    ['company_id' => $company->id, 'code' => $status['code']],
                    $status
                );
            }
        }

        $primary = Company::query()->orderBy('id')->first();
        if ($primary) {
            app(DevAssetsCsvSeeder::class)->import($primary);
        }
    }
}

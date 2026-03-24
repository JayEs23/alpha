<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetServicePlan;
use App\Models\AssetStatus;
use App\Models\AssetServiceTaskStatus;
use App\Models\Company;
use App\Models\Provider;
use App\Models\User;
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

        $companies = Company::query()->get();

        foreach ($companies as $company) {
            $categories = [
                ['name' => 'IT Device', 'code' => 'it_device'],
                ['name' => 'Office Equipment', 'code' => 'office_equipment'],
                ['name' => 'Facility Equipment', 'code' => 'facility_equipment'],
                ['name' => 'Vehicle', 'code' => 'vehicle'],
                ['name' => 'Tooling', 'code' => 'tooling'],
            ];

            foreach ($categories as $category) {
                AssetCategory::query()->updateOrCreate(
                    ['company_id' => $company->id, 'code' => $category['code']],
                    ['name' => $category['name'], 'is_active' => true]
                );
            }

            $statuses = [
                ['name' => 'Active', 'code' => 'active', 'is_terminal' => false, 'sort_order' => 10],
                ['name' => 'Maintenance', 'code' => 'maintenance', 'is_terminal' => false, 'sort_order' => 20],
                ['name' => 'Retired', 'code' => 'retired', 'is_terminal' => true, 'sort_order' => 30],
            ];

            foreach ($statuses as $status) {
                AssetStatus::query()->updateOrCreate(
                    ['company_id' => $company->id, 'code' => $status['code']],
                    $status
                );
            }

            $defaultCategory = AssetCategory::query()->where('company_id', $company->id)->where('code', 'it_device')->first();
            $defaultStatus = AssetStatus::query()->where('company_id', $company->id)->where('code', 'active')->first();
            $defaultProvider = Provider::query()->where('company_id', $company->id)->first();
            $assignee = User::query()->where('current_company_id', $company->id)->first();

            if (! $defaultCategory || ! $defaultStatus) {
                continue;
            }

            $asset = Asset::query()->firstOrCreate(
                ['company_id' => $company->id, 'asset_tag' => 'OPS-'.$company->id.'-001'],
                [
                    'category_id' => $defaultCategory->id,
                    'status_id' => $defaultStatus->id,
                    'provider_id' => $defaultProvider?->id,
                    'assigned_user_id' => $assignee?->id,
                    'name' => 'Primary Operations Asset',
                    'serial' => 'AUTO-'.$company->id.'-001',
                    'purchased_at' => now()->subMonths(6)->toDateString(),
                ]
            );

            AssetServicePlan::query()->firstOrCreate(
                ['company_id' => $company->id, 'asset_id' => $asset->id, 'name' => 'Quarterly Preventive Maintenance'],
                [
                    'service_interval_days' => 90,
                    'reminder_days_before' => 7,
                    'default_assigned_user_id' => $assignee?->id,
                    'next_due_at' => now()->addDays(14),
                    'is_active' => true,
                    'instructions' => 'Perform standard preventive maintenance checklist.',
                ]
            );
        }
    }
}

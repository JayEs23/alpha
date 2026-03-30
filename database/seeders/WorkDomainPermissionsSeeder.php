<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WorkDomainPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'assets.view',
            'assets.create',
            'assets.update',
            'assets.assign',
            'assets.retire',
            'asset_service_plans.view',
            'asset_service_plans.create',
            'asset_service_plans.update',
            'asset_service_tasks.view',
            'asset_service_tasks.assign',
            'asset_service_tasks.complete',
            'asset_service_reminders.view',
            'asset_service_reminders.manage',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.archive',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.transition',
            'tasks.delete',
            'comments.create',
            'comments.delete',
            'workflow.view',
            'workflow.update',
        ];

        foreach ($names as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        $role = Role::query()->where('name', 'super_admin')->where('guard_name', 'web')->first();
        if ($role) {
            $role->syncPermissions(Permission::query()->where('guard_name', 'web')->pluck('name')->all());
        }
    }
}

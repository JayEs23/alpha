<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectWorkflowStatus;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStatus;
use App\Models\WorkflowTransition;
use Illuminate\Database\Seeder;

class WorkDomainSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();

        foreach ($companies as $company) {
            $companyId = (int) $company->id;

            $todo = WorkflowStatus::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'todo'],
                [
                    'name' => 'To Do',
                    'category' => 'open',
                    'sort_order' => 10,
                ]
            );

            $inProgress = WorkflowStatus::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'in_progress'],
                [
                    'name' => 'In Progress',
                    'category' => 'active',
                    'sort_order' => 20,
                ]
            );

            $done = WorkflowStatus::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'done'],
                [
                    'name' => 'Done',
                    'category' => 'done',
                    'sort_order' => 30,
                ]
            );

            $workflow = Workflow::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'default'],
                [
                    'name' => 'Default',
                    'is_default' => true,
                ]
            );

            foreach (
                [
                    [$todo->id, $inProgress->id, 'Start work'],
                    [$inProgress->id, $done->id, 'Complete'],
                    [$todo->id, $done->id, 'Fast complete'],
                ] as $transition
            ) {
                WorkflowTransition::query()->firstOrCreate(
                    [
                        'workflow_id' => $workflow->id,
                        'from_status_id' => $transition[0],
                        'to_status_id' => $transition[1],
                    ],
                    [
                        'name' => $transition[2],
                        'requires_resolution' => false,
                        'requires_all_subtasks_done' => false,
                    ]
                );
            }

            $low = TaskPriority::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'low'],
                ['name' => 'Low', 'weight' => 10]
            );
            $medium = TaskPriority::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'medium'],
                ['name' => 'Medium', 'weight' => 20]
            );
            $high = TaskPriority::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => 'high'],
                ['name' => 'High', 'weight' => 30]
            );

            $lead = User::query()->where('current_company_id', $companyId)->first();

            $project = Project::query()->withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'key' => 'OPS'],
                [
                    'name' => 'Operations',
                    'description' => 'Default operations project',
                    'is_active' => true,
                    'default_workflow_id' => $workflow->id,
                    'lead_user_id' => $lead?->id,
                ]
            );

            foreach ([$todo, $inProgress, $done] as $index => $status) {
                ProjectWorkflowStatus::query()->withoutGlobalScopes()->firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'workflow_status_id' => $status->id,
                    ],
                    [
                        'company_id' => $companyId,
                        'sort_order' => ($index + 1) * 10,
                    ]
                );
            }

            $reporter = User::query()->where('current_company_id', $companyId)->first();
            if (! $reporter) {
                continue;
            }

            Task::query()->withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'project_id' => $project->id,
                    'task_number' => 1,
                ],
                [
                    'title' => 'Welcome: review operations backlog',
                    'description' => 'Sample task seeded for the work management module.',
                    'status_id' => $todo->id,
                    'priority_id' => $medium->id,
                    'reporter_user_id' => $reporter->id,
                    'assignee_user_id' => $lead?->id,
                ]
            );
        }
    }
}

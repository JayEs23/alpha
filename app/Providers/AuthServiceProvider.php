<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Asset;
use App\Models\AssetServicePlan;
use App\Models\AssetServiceTask;
use App\Models\Company;
use App\Models\Hardware;
use App\Models\Peripheral;
use App\Models\Project;
use App\Models\Provider;
use App\Models\Software;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workflow;
use App\Policies\ActivityPolicy;
use App\Policies\AssetPolicy;
use App\Policies\AssetServicePlanPolicy;
use App\Policies\AssetServiceTaskPolicy;
use App\Policies\CompanyPolicyMain;
use App\Policies\HardwarePolicy;
use App\Policies\PeripheralPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\SoftwarePolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkflowPolicy;
use HusamTariq\FilamentDatabaseSchedule\Models\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicyMain::class,
        Hardware::class => HardwarePolicy::class,
        Peripheral::class => PeripheralPolicy::class,
        Provider::class => ProviderPolicy::class,
        Software::class => SoftwarePolicy::class,
        Asset::class => AssetPolicy::class,
        AssetServicePlan::class => AssetServicePlanPolicy::class,
        AssetServiceTask::class => AssetServiceTaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        TaskComment::class => TaskCommentPolicy::class,
        Workflow::class => WorkflowPolicy::class,
        Activity::class => ActivityPolicy::class,
        User::class => UserPolicy::class,
        Schedule::class => SchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

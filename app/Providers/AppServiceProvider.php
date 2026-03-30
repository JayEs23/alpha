<?php

namespace App\Providers;

use App\Services\AssetService;
use App\Services\CompanyService;
use App\Services\Contracts\AssetServiceInterface;
use App\Services\Contracts\CompanyServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\WorkflowServiceInterface;
use App\Services\NotificationService;
use App\Services\TaskService;
use App\Services\WorkflowService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AssetServiceInterface::class, AssetService::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        $this->app->bind(WorkflowServiceInterface::class, WorkflowService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

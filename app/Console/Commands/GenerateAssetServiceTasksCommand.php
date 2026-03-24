<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Services\Contracts\AssetServiceInterface;
use Illuminate\Console\Command;

class GenerateAssetServiceTasksCommand extends Command
{
    protected $signature = 'assets:generate-service-tasks';

    protected $description = 'Generate due recurring service tasks for all companies';

    public function handle(AssetServiceInterface $assetService): int
    {
        $generated = 0;

        Company::query()->select(['id', 'user_id'])->chunkById(100, function ($companies) use (&$generated, $assetService): void {
            foreach ($companies as $company) {
                $actor = User::query()->find($company->user_id);
                if (! $actor) {
                    continue;
                }

                $actor->current_company_id = $company->id;
                $generated += $assetService->generateDueServiceTasks(now(), $actor);
            }
        });

        $this->info("Generated {$generated} service task(s).");

        return self::SUCCESS;
    }
}

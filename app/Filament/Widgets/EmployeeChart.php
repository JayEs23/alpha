<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EmployeeChart extends BaseWidget
{
    protected static ?int $sort = 1;

    public bool $readyToLoad = false;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    protected function getCards(): array
    {
        if (! $this->readyToLoad) {
            return $this->skeletonLoad();
        }

        $companyId = (int) (auth()->user()?->current_company_id ?? 0);
        $counts = Cache::remember(
            "dashboard:employee-chart:{$companyId}",
            now()->addMinutes(1),
            static fn () => [
                'employee' => DB::table('company_user')
                    ->where('company_id', $companyId)
                    ->count(),
                'pending' => DB::table('company_invitations')
                    ->where('company_id', $companyId)
                    ->count(),
            ],
        );

        return [
            Card::make('Employee', $counts['employee']),
            Card::make('Employee Pending', $counts['pending']),
        ];
    }

    protected function skeletonLoad(): array
    {
        return [
            Card::make('Loading Data', 'loading...'),
        ];
    }
}

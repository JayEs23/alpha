<?php

namespace App\Filament\Widgets;

use App\Models\Hardware;
use App\Models\Peripheral;
use App\Models\Provider;
use App\Models\Software;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 6;

    public bool $readyToLoad = false;

    public function loadData()
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
            "dashboard:stats-overview:{$companyId}",
            now()->addMinutes(1),
            static fn (): array => [
                'hardware' => Hardware::count(),
                'software' => Software::count(),
                'providers' => Provider::count(),
                'peripherals' => Peripheral::count(),
            ],
        );

        return [
            Card::make('Hardware', $counts['hardware']),
            Card::make('Software', $counts['software']),
            Card::make('Providers', $counts['providers']),
            Card::make('Peripherals', $counts['peripherals']),
        ];
    }

    protected function skeletonLoad(): array
    {
        return [
            Card::make('Hardware', 'loading...'),
            Card::make('Software', 'loading...'),
            Card::make('Providers', 'loading...'),
            Card::make('Peripherals', 'loading...'),
        ];
    }
}

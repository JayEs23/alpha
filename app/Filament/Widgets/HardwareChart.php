<?php

namespace App\Filament\Widgets;

use App\Actions\Trend;
use App\Models\Hardware;
use Filament\Widgets\LineChartWidget;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Cache;

class HardwareChart extends LineChartWidget
{
    protected static ?string $heading = 'Hardware';

    protected static ?int $sort = 5;

    public ?string $filter = 'this_month';

    public bool $readyToLoad = false;

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    protected function getData(): array
    {
        if (! $this->readyToLoad) {
            return $this->getSkeletonLoad();
        }

        $activeFilter = $this->filter;
        $companyId = (int) (auth()->user()?->current_company_id ?? 0);
        $data = Cache::remember(
            "dashboard:hardware-chart:{$companyId}:{$activeFilter}",
            now()->addMinutes(1),
            function () use ($activeFilter) {
                /** @var Trend $trend */
                $trend = Trend::model(Hardware::class);

                return $trend
                    ->filterBy($activeFilter)
                    ->count();
            },
        );

        return [
            'datasets' => [
                [
                    'label' => 'Hardware Publish',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
                    'borderColor' => 'rgb(255, 205, 86)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getFilters(): ?array
    {
        return Trend::filterType();
    }

    private function getSkeletonLoad()
    {
        return [
            'datasets' => [
                [
                    'label' => 'Loading data',
                    'data' => [],
                    'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
                    'borderColor' => 'rgb(255, 205, 86)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'loading',
                    'data' => [],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgb(255, 159, 64)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [],
        ];
    }
}

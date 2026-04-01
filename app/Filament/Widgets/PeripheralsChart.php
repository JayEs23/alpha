<?php

namespace App\Filament\Widgets;

use App\Actions\Trend;
use App\Models\Peripheral;
use Filament\Widgets\LineChartWidget;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Cache;

class PeripheralsChart extends LineChartWidget
{
    protected static ?string $heading = 'Peripherals';

    protected static ?int $sort = 7;

    public ?string $filter = 'this_month';

    public bool $readyToLoad = false;

    protected int|string|array $columnSpan = 'full';

    public function loadData(): void
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
        $chartData = Cache::remember(
            "dashboard:peripherals-chart:{$companyId}:{$activeFilter}",
            now()->addMinutes(1),
            function () use ($activeFilter): array {
                /** @var Trend $publishedTrend */
                $publishedTrend = Trend::model(Peripheral::class);
                /** @var Trend $purchasedTrend */
                $purchasedTrend = Trend::model(Peripheral::class);

                return [
                    'published' => $publishedTrend
                    ->filterBy($activeFilter)
                    ->count(),
                    'purchased' => $purchasedTrend
                    ->filterBy($activeFilter)
                    ->count('purchased_at'),
                ];
            },
        );
        $data = $chartData['published'];
        $purchased = $chartData['purchased'];

        return [
            'datasets' => [
                [
                    'label' => 'Peripherals Published',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
                    'borderColor' => 'rgb(255, 205, 86)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Purchased ',
                    'data' => $purchased->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
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

    private function getSkeletonLoad(): ?array
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
            ],
            'labels' => [],
        ];
    }
}

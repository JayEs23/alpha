<?php

namespace App\Filament\Widgets;

use App\Models\Hardware;
use App\Models\Periphel;
use App\Models\Provaider;
use App\Models\Software;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

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

        $hardwares = Hardware::count();
        $softwares = Software::count();
        $provaiders = Provaider::count();
        $periphels = Periphel::count();

        // $user = DB::table('users')->where('company_id', auth()->user()->current_company_id)->count();

        return [
            Card::make('Hardware', $hardwares),
            Card::make('Software', $softwares),
            Card::make('Providers', $provaiders),
            Card::make('Peripherals', $periphels),

            // Card::make('Puntor', $user),
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

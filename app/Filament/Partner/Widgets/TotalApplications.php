<?php

namespace App\Filament\Partner\Widgets;

use App\Models\Application;
use App\Models\QuotaTransaction;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalApplications extends BaseWidget
{
    protected function getColumns(): int
    {
        return 1;
    }
    protected function getStats(): array
    {
        $partner = Auth::user();
        $totalApplications = $partner->used_quota;

        return [
            Stat::make('Quota consommé', $totalApplications)
                ->description('Applications que vous avez créées')
                ->icon('heroicon-m-rectangle-stack')
                ->color('primary')
                // ->chart([$totalApplications, $paidApplications, $unpaidApplications])
                ->extraAttributes([
                    'class' => 'w-full',
                ]),
        ];
    }
}

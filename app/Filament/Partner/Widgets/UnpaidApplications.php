<?php

namespace App\Filament\Partner\Widgets;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UnpaidApplications extends BaseWidget
{
    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {

        $partner = Auth::user();

        $unpaid = $partner->total_unpaid;
        $paid = $partner->total_paid;

        return [
            Stat::make('Applications non payées', $unpaid)
                ->description('Applications non réglées')
                ->icon('heroicon-m-exclamation-circle')
                ->color($unpaid > 0 ? 'danger' : 'success')
                ->chart([$paid, $unpaid]),
        ];
    }
}

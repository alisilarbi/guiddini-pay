<?php

namespace App\Filament\Partner\Widgets;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PaidVsUnpaidApplications extends BaseWidget
{
    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {

        $user = Auth::user();

        $totalApplications = Application::where('user_id', $user->id)->count();
        $paidApplications = Application::where('user_id', $user->id)
            ->where('is_paid', true)
            ->count();
        $unpaidApplications = $totalApplications - $paidApplications;


        return [
            Stat::make('Payées vs non payées', "{$paidApplications} / {$unpaidApplications}")
                ->description('Applications payées contre non payées')
                ->descriptionIcon($paidApplications >= $unpaidApplications
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->color($paidApplications >= $unpaidApplications ? 'success' : 'danger')
                ->chart([$paidApplications, $unpaidApplications]),
        ];
    }
}

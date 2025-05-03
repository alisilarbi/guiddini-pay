<?php

namespace App\Filament\Partner\Widgets;

use App\Models\Application;
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
        $user = Auth::user();
        $totalApplications = Application::where('user_id', $user->id)->count();

        $paidApplications = Application::where('user_id', $user->id)
            ->where('is_paid', true)
            ->count();
        $unpaidApplications = $totalApplications - $paidApplications;

        return [
            Stat::make('Applications totales', $totalApplications)
                ->description('Toutes les applications que vous avez créées')
                ->icon('heroicon-m-rectangle-stack')
                ->color('primary')
                ->chart([$totalApplications, $paidApplications, $unpaidApplications])
                ->extraAttributes([
                    'class' => 'w-full',
                ]),
        ];
    }
}

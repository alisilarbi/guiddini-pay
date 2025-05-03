<?php

namespace App\Filament\Partner\Widgets;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ApplicationsOverview extends BaseWidget
{
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
                ->chart([$totalApplications, $paidApplications, $unpaidApplications]),

            Stat::make('Payées vs non payées', "{$paidApplications} / {$unpaidApplications}")
                ->description('Applications payées contre non payées')
                ->descriptionIcon($paidApplications >= $unpaidApplications
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->color($paidApplications >= $unpaidApplications ? 'success' : 'danger')
                ->chart([$paidApplications, $unpaidApplications]),

            Stat::make('Quota restant', $user->partner_mode === 'unlimited' ? '∞' : $user->remaining_allowance)
                ->description($user->partner_mode === 'unlimited'
                    ? 'Mode illimité activé'
                    : 'Applications restantes à créer')
                ->color($user->partner_mode === 'unlimited' || $user->remaining_allowance > 0
                    ? 'info'
                    : 'danger')
                ->icon('heroicon-m-bolt'),
        ];
    }
}

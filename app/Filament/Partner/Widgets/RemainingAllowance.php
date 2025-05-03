<?php

namespace App\Filament\Partner\Widgets;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class RemainingAllowance extends BaseWidget
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

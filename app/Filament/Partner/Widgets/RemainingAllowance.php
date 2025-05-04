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
        $partner = Auth::user();
        $remaining = $partner->available_quota;
        $used = $partner->used_quota;

        $color = 'success';
        if ($partner->partner_mode !== 'unlimited') {
            if ($remaining === 0) {
                $color = 'danger';
            } elseif ($remaining <= 5) {
                $color = 'warning';
            }
        }

        return [
            Stat::make('Quota restant', $partner->partner_mode === 'unlimited' ? '∞' : $remaining)
                ->description($partner->partner_mode === 'unlimited'
                    ? 'Mode illimité activé'
                    : 'Applications restantes à créer')
                ->color($color)
                ->icon('heroicon-m-bolt')
                ->chart([$used, $remaining]),
        ];
    }
}

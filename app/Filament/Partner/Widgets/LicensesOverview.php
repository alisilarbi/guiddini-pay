<?php

namespace App\Filament\Partner\Widgets;

use App\Models\License;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class LicensesOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $partner = Auth::user();
        $licenses = License::where('partner_id', $partner->id)->get();

        $satimCount = $licenses->where('gateway_type', 'satim')->count();
        $posteDzCount = $licenses->where('gateway_type', 'poste_dz')->count();

        return [

            Stat::make('🏦 SATIM Licenses', $satimCount)
                ->description('SATIM gateway licenses for card payments 💳')
                ->icon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('📮 PosteDZ Licenses', $posteDzCount)
                ->description('PosteDZ gateway licenses for Algérie Poste 💸')
                ->icon('heroicon-m-inbox')
                ->color('info'),
        ];
    }
}

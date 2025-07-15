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

            Stat::make('SATIM Licenses', $satimCount)
                ->description('Licenses de paiement en ligne de SATIM')
                // ->icon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),

            Stat::make('PosteDZ Licenses', $posteDzCount)
                ->description('Licenses de paiement en ligne de Algérie Poste')
                // ->icon('heroicon-m-inbox')
                ->chart([12, 3, 10, 2, 7, 1, 9])
                ->color('info'),
        ];
    }
}

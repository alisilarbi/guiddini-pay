<?php

namespace App\Filament\Partner\Widgets;

use Filament\Widgets\ChartWidget;

class LicensesOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Aperçu des licences délivrées (9 derniers mois)';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Licences délivrées',
                    'data' => [7, 8, 13, 9, 11, 16, 4, 14, 15], // Données fictives
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#6ee7b7',
                ],
            ],
            'labels' => collect(range(8, 0))->map(function ($i) {
                return now()->subMonths($i)->locale('fr_FR')->isoFormat('MMM');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

<?php

namespace App\Filament\Partner\Widgets;

use Filament\Widgets\ChartWidget;

class ApplicationsOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Aperçu des applications créées (9 derniers mois)';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Applications créées',
                    'data' => [10, 12, 14, 9, 13, 15, 16, 18, 20], // Données fictives
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#60a5fa',
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

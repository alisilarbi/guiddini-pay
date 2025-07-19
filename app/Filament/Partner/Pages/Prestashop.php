<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;

class Prestashop extends Page
{
    // protected static ?string $navigationIcon = 'prestashop';

    // protected static string $view = 'filament.partner.pages.prestashop';

    // protected static ?string $navigationGroup = 'Integrations';

    // protected static ?int $navigationSort = 6;

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('Prestashop')
                ->icon('prestashop')
                ->group('Integrations')
                ->sort(5)
                ->url('#')
                ->badge('Coming soon')
                ->isActiveWhen(fn() => false),
        ];
    }
}

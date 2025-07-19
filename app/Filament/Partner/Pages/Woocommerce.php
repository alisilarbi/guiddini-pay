<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;

class Woocommerce extends Page
{
    // protected static ?string $navigationIcon = 'woo';

    // protected static string $view = 'filament.partner.pages.woocommerce';

    // protected static ?string $navigationGroup = 'Integrations';

    // protected static ?int $navigationSort = 5;

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('Woocoomerce')
                ->icon('woo')
                ->group('Integrations')
                ->sort(4)
                ->url('#')
                ->badge('Coming soon')
                ->isActiveWhen(fn() => false),
        ];
    }

}

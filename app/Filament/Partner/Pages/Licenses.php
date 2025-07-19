<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;

class Licenses extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.partner.pages.licenses';
    protected static ?string $navigationGroup = 'Certifications';
    protected static ?int $navigationSort = 6;

    // public static function getNavigationItems(): array
    // {
    //     return [
    //         NavigationItem::make()
    //             ->label('Licenses')
    //             ->icon('heroicon-o-key')
    //             ->group('Certifications')
    //             ->sort(4)
    //             ->url('#') // 👈 Non-clickable dummy URL
    //             ->isActiveWhen(fn() => false),
    //     ];
    // }
}

<?php

namespace App\Filament\Partner\Pages;
use Filament\Pages\Page;

class Licenses extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.partner.pages.licenses';
    protected static ?string $navigationGroup = 'Integrations';
    protected static ?int $navigationSort = 4;
}

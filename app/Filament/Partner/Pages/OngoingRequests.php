<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;

class OngoingRequests extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.partner.pages.ongoing-requests';

    protected static ?string $navigationGroup = 'Certifications';

    protected static ?int $navigationSort = 10;
}

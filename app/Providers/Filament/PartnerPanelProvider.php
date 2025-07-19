<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\User;
use Filament\Widgets;
use Livewire\Livewire;
use Filament\PanelProvider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Livewire\Hooks\ShowUserTransactionsButton;
use App\Livewire\Hooks\ApplicationsAllowanceOverview;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class PartnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $this->registerRenderHooks($panel);
        return $panel
            ->id('partner')
            ->path('partner')
            ->login()
            ->passwordReset()
            // ->topNavigation()
            ->profile()
            ->colors([
                // 'primary' => Color::Amber,
                'primary' => Color::Hex('#4f6ff6'),
            ])
            ->discoverResources(in: app_path('Filament/Partner/Resources'), for: 'App\\Filament\\Partner\\Resources')
            ->discoverPages(in: app_path('Filament/Partner/Pages'), for: 'App\\Filament\\Partner\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Partner/Widgets'), for: 'App\\Filament\\Partner\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->darkMode(false)
            ->brandName('Guiddini Pay')
            ->brandLogo(asset('images/logotype_guiddinipay.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/icon_guiddinipay.png'))
            ->viteTheme('resources/css/app.css');
    }

    public function registerRenderHooks(Panel $panel): void
    {
        $panel->renderHook(
            'panels::global-search.after',
            fn() => Livewire::mount(ApplicationsAllowanceOverview::class)
        );

        $panel->renderHook(
            'panels::page.header.widgets.after',
            fn() => Livewire::mount(ShowUserTransactionsButton::class)
        );

    }
}

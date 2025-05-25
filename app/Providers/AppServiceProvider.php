<?php

namespace App\Providers;

use App\Models\Quota;
use App\Models\Application;
use App\Models\Transaction;
use Filament\Facades\Filament;
use App\Observers\QuotaObserver;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Prohibitable;
use App\Observers\ApplicationObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;

class AppServiceProvider extends ServiceProvider
{
    use Prohibitable;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        // DB::prohibitDestructiveCommands($this->app->isProduction());

        FilamentColor::register([
            'guiddini-primary' => Color::hex('#1e3a8a'), // guiddini-primary
            'guiddini-accent' => Color::hex('#3b82f6'), // guiddini-accent
        ]);

        Application::observe(ApplicationObserver::class);
        Quota::observe(QuotaObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}

<x-filament-panels::page>
    {{ $this->form }}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            @livewire(\App\Filament\Partner\Widgets\ApplicationsOverviewChart::class)
        </div>

        <div>
            @livewire(\App\Filament\Partner\Widgets\LicensesOverviewChart::class)
        </div>
    </div>
</x-filament-panels::page>

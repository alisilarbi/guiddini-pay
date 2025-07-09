<x-filament-panels::page>
    @livewire(\App\Filament\Partner\Widgets\LicensesOverview::class)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            @livewire(\App\Livewire\Tables\LatestSatimLicenses::class)
        </div>

        <div>
            @livewire(\App\Livewire\Tables\LatestPosteDzLicenses::class)
        </div>
    </div>

</x-filament-panels::page>

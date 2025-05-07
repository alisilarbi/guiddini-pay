<x-filament-panels::page>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            @livewire(\App\Filament\Partner\Widgets\TotalApplications::class)
            <x-filament::button class="w-full justify-center mt-3" outlined
                x-on:click="window.location.href = '/partner/applications'" size="xs">
                Create Application
            </x-filament::button>
        </div>
        <div>
            @livewire(\App\Filament\Partner\Widgets\UnpaidApplications::class)
            <x-filament::button class="w-full justify-center mt-3" outlined
                x-on:click="$dispatch('open-modal', { id: 'pay-debts' })" size="xs">
                Pay debts
            </x-filament::button>
        </div>

        <div>
            @livewire(\App\Filament\Partner\Widgets\RemainingAllowance::class)

            @php
                $buttonColor =
                    $this->remainingAllowance <= 3
                        ? 'danger'
                        : ($this->remainingAllowance <= 5
                            ? 'warning'
                            : 'guiddini-primary');

                $buttonIcon =
                    $this->remainingAllowance <= 3
                        ? 'heroicon-s-battery-0'
                        : ($this->remainingAllowance <= 5
                            ? 'heroicon-s-battery-50'
                            : 'heroicon-s-battery-100');

            @endphp

            <x-filament::button class="w-full justify-center mt-3 " outlined icon="{{ $buttonIcon }}"
                color="{{ $buttonColor }}" x-on:click="$dispatch('open-modal', { id: 'buy-allowance' })" size="xs">
                Manage
            </x-filament::button>
        </div>
    </div>

    <x-filament::modal id="pay-debts" width="3xl">
        @livewire('filament-tables.unpaid-applications')
        <div class="flex items justify-between">
            <span class="text-md font-bold ">Total amount :
            </span>
            <div class="text-md flex items-center fond-bold">
                {{ number_format(40000, 2) }} DA
            </div>
        </div>
        <x-slot name="footer" style="margin-top: -20px;">
            <x-filament::button wire:click="buyAllowance" size="lg" color="guiddini-primary"
                class="flex justify-center items-center gap-2 mt-4 w-full">
                <span class="flex flex-row gap-2">
                    Procéder au paiement
                    <img src="{{ asset('images/cib_logotype.jpg') }}" alt="CIB" class="h-5 rounded-md">
                </span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>




    {{ $this->table }}

</x-filament-panels::page>

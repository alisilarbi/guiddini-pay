<div>
    @if ($this->partner->partner_mode == 'quota')
        @php
            $bgClass =
                $this->remainingAllowance <= 3
                    ? 'bg-red-50 border-red-300 hover:bg-red-100'
                    : ($this->remainingAllowance <= 5
                        ? 'bg-orange-50 border-orange-300 hover:bg-orange-100'
                        : 'bg-green-50 border-green-300 hover:bg-green-100');

            $dotColor =
                $this->remainingAllowance <= 3
                    ? 'bg-red-500'
                    : ($this->remainingAllowance <= 5
                        ? 'bg-orange-500'
                        : 'bg-green-500');

            $textColor =
                $this->remainingAllowance <= 3
                    ? 'text-red-600'
                    : ($this->remainingAllowance <= 5
                        ? 'text-orange-600'
                        : 'text-green-600');

            $barWidth = $this->remainingAllowance < 10 ? (10 - $this->remainingAllowance) * 10 : 10;
            $barColor = $this->remainingAllowance <= 3 ? 'red' : ($this->remainingAllowance <= 5 ? 'orange' : 'green');

            $restanteText = $this->remainingAllowance > 1 ? 's' : '';
        @endphp

        <div style="margin-bottom: -25px;" x-data x-on:click="$dispatch('open-modal', { id: 'buy-allowance' })"
            class="flex items-center px-3 py-1.5 rounded-full cursor-pointer transition-colors {{ $bgClass }}">
            <span class="h-2 w-2 rounded-full {{ $dotColor }}"></span>
            <span class="ml-2 text-sm font-medium {{ $textColor }}">Applications</span>
            <div class="ml-2 w-16 bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full"
                    style="width: {{ $barWidth }}%; background-color: {{ $barColor }};"
                    x-on:click="$dispatch('open-modal', { id: 'buy-allowance' })">
                </div>
            </div>
            <span class="ml-2 text-xs text-gray-500">{{ $this->remainingAllowance }} restante{{ $restanteText }}</span>
        </div>


        <x-filament::modal id="buy-allowance" width="3xl">
            <div class="flex flex-col">

                @php
                    $bgClass =
                        $this->remainingAllowance <= 3
                            ? 'bg-red-50 border-red-300 hover:bg-red-100'
                            : ($this->remainingAllowance <= 5
                                ? 'bg-orange-50 border-orange-300 hover:bg-orange-100'
                                : 'bg-green-50 border-green-300 hover:bg-green-100');

                    $textColor =
                        $this->remainingAllowance <= 3
                            ? 'text-red-600'
                            : ($this->remainingAllowance <= 5
                                ? 'text-orange-600'
                                : 'text-green-600');

                    $barColor =
                        $this->remainingAllowance <= 3 ? 'red' : ($this->remainingAllowance <= 5 ? 'orange' : 'green');

                    $barWidth = $this->remainingAllowance < 10 ? (10 - $this->remainingAllowance) * 10 : 10;
                @endphp

                <div class="p-4 rounded {{ $bgClass }}">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium {{ $textColor }}">Applications Left</span>
                        <span class="text-sm {{ $textColor }}">{{ $this->remainingAllowance }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full"
                            style="width: {{ $barWidth }}%; background-color: {{ $barColor }}"></div>
                    </div>
                    <div class="mt-2 text-sm {{ $textColor }}">{{ $this->totalApplications }}
                        application{{ $this->totalApplications > 1 ? 's' : '' }} total</div>
                </div>


                <div class="p-4">
                    <h4 class="font-medium mb-3">Purchase Additional Quota</h4>

                    <div class="flex items-center justify-between">
                        <span class="text-sm">Select quantity :</span>
                        <div class="flex items-center">

                            <x-filament::input.wrapper>
                                <x-slot name="prefix">
                                    <x-filament::icon-button icon="heroicon-o-minus" wire:click="downNewAllowance"
                                        label="New label" />
                                </x-slot>

                                <x-filament::input type="text" wire:model="newAllowance" style="width: 40px;" />

                                <x-slot name="suffix">
                                    <x-filament::icon-button icon="heroicon-o-plus" wire:click="upNewAllowance"
                                        label="New label" />

                                </x-slot>
                            </x-filament::input.wrapper>

                            @error('newAllowance')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror

                        </div>
                    </div>

                    <div class="flex items justify-between mt-12 -mb-8">
                        <span class="text-md font-bold ">Total amount :
                        </span>
                        <div class="text-md flex items-center fond-bold">
                            {{ number_format($this->newAllowance * $this->applicationPrice, 2) }} DA
                        </div>
                    </div>

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
    @endif



</div>

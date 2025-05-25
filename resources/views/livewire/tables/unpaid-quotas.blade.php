<div>
    {{ $this->table }}
    <div class="flex items justify-between mt-5">
        <span class="text-md font-bold ">Total amount :
        </span>
        <div class="text-md flex items-center fond-bold">
            {{ number_format($this->total, 2) }} DA
        </div>
    </div>
    <x-filament::button wire:click="payDebts" size="lg" color="guiddini-primary"
        class="flex justify-center items-center gap-2 mt-6 w-full">
        <span class="flex flex-row gap-2">
            Procéder au paiement
            <img src="{{ asset('images/cib_logotype.jpg') }}" alt="CIB" class="h-5 rounded-md">
        </span>
    </x-filament::button>
</div>

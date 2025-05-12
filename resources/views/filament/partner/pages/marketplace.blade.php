<x-filament-panels::page>

    @if (!$this->orderNumber)
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
                    color="{{ $buttonColor }}" x-on:click="$dispatch('open-modal', { id: 'buy-allowance' })"
                    size="xs">
                    Manage
                </x-filament::button>
            </div>
        </div>

        <x-filament::modal id="pay-debts" width="3xl">
            @livewire('tables.unpaid-quotas')
        </x-filament::modal>

        {{ $this->table }}
    @else
        <div style="max-width: 700px; margin: auto;">
            <p
                class="text-center text-xl font-semibold mb-6
            {{ $this->transaction->status === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                {{ $this->transaction->action_code_description }}
            </p>

            @if ($this->transaction->status === 'paid')
                <table class="min-w-full table-auto mb-8">
                    <tbody>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">Méthode de paiement
                            </td>
                            <td class="py-3 px-4 border-b text-gray-900">CIB / Edahabia</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">Numéro de commande</td>
                            <td class="py-3 px-4 border-b text-gray-900">{{ $this->transaction->order_id }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">ID de transaction</td>
                            <td class="py-3 px-4 border-b text-gray-900">
                                {{ $this->transaction->order_number }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">Numéro d'autorisation
                            </td>
                            <td class="py-3 px-4 border-b text-gray-900">
                                {{ $this->transaction->approval_code }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">Montant total</td>
                            <td class="py-3 px-4 border-b text-gray-900">
                                {{ $this->transaction->deposit_amount }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 border-b font-medium text-gray-600">Date et heure</td>
                            <td class="py-3 px-4 border-b text-gray-900">
                                {{ $this->transaction->deposit_amount }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-center flex-wrap gap-4 mt-6">

                    <x-filament::button wire:click="downloadReceipt" size="lg" color="guiddini-primary"
                        class="flex justify-center items-center gap-2 mt-4 " icon="heroicon-o-inbox-arrow-down">
                        <span class="flex flex-row gap-2">
                            Télécharger PDF
                        </span>
                    </x-filament::button>

                    <x-filament::modal id="send-email">
                        <x-slot name="trigger">
                            <x-filament::button size="lg" color="guiddini-accent"
                                class="flex justify-center items-center gap-2 mt-4 " icon="heroicon-o-share">
                                <span class="flex flex-row gap-2">
                                    Envoyer par email
                                </span>
                            </x-filament::button>
                        </x-slot>

                        <form wire:submit.prevent="sendEmail" class="space-y-6">

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email
                                    professionnel</label>



                                <input type="email" id="email" wire:model="email"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                                    placeholder="exemple@entreprise.com">

                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-filament::button type="submit" size="lg" color="guiddini-accent"
                                    class="flex justify-center items-center gap-2 mt-4 w-full" outlined>
                                    <span class="flex flex-row gap-2">
                                        Envoyer
                                    </span>
                                </x-filament::button>
                            </div>
                        </form>
                    </x-filament::modal>


                </div>

                <div class="mt-8 flex justify-center">
                    <x-filament::button wire:click="tryAgain" size="lg" color="guiddini-accent"
                        class="flex justify-center items-center gap-2 mt-4 w-full" icon="heroicon-o-arrow-path"
                        outlined>
                        <span class="flex flex-row gap-2">
                            Effectuer un nouveau paiement
                        </span>
                    </x-filament::button>
                </div>
            @endif

            @if ($this->transaction->status !== 'paid')
                <div class="mt-8 flex justify-center">
                    <x-filament::button wire:click="tryAgain" size="lg" color="guiddini-accent"
                        class="flex justify-center items-center gap-2 mt-4 w-full" icon="heroicon-o-arrow-path"
                        outlined>
                        <span class="flex flex-row gap-2">
                            Réessayer un nouveau paiement
                        </span>
                    </x-filament::button>
                </div>
            @endif


        </div>

        <footer class="clearfix mb-10" style="text-align: center;">
            <h5 style="text-align: center;">Si vous rencontrez un problème avec le paiement, Contactez la SATIM</h5>

            <div style="text-align: center;">
                <img style="width: 100%; max-width: 140px; height: 50px; display: block; margin: 5px auto;"
                    src="{{ url('images/green_number.png') }}">
            </div>
        </footer>
    @endif

</x-filament-panels::page>

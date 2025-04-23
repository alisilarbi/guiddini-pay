<div>

    <body class="bg-white min-h-screen flex flex-col">
        <main class="container mx-auto px-4 py-12 flex-grow pb-16">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-center mb-8">
                    <div
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm
                        {{ $application->license_env === 'production' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        <span class="mr-2">
                            {!! $application->license_env === 'production' ? '✅' : '🚧' !!}
                        </span>
                        <span>
                            {{ $application->license_env === 'production' ? 'Environnement de production' : 'Environnement de test' }}
                        </span>
                    </div>
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-center text-guiddini-text mb-4">
                    Paiement <span class="text-guiddini-accent">Spontané</span>
                </h1>

                <p class="text-center text-guiddini-text mb-12 max-w-2xl mx-auto text-lg md:text-xl">
                    Vous souhaitez nous régler via un <span class="text-guiddini-accent">paiement spontané</span> ?
                    Remplissez simplement le formulaire ci-dessous.
                </p>

                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 mb-10">
                    @if ($application->logo)
                        <div class="flex justify-center mb-6">
                            <img src="{{ url($application->logo) }}" alt="Company Logo" class="h-16">
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise</label>
                            <p class="text-gray-900">{{ $application->name }}</p>
                        </div>

                        @if ($application->user->phone_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone</label>
                                <p class="text-gray-900">{{ $application->user->phone_number }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <p class="text-gray-900">{{ $application->user->email }}</p>
                        </div>
                    </div>
                </div>

                @if ($this->transaction)
                    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 mb-10">
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
                                    class="flex justify-center items-center gap-2 mt-4 "
                                    icon="heroicon-o-inbox-arrow-down">
                                    <span class="flex flex-row gap-2">
                                        Télécharger PDF
                                    </span>
                                </x-filament::button>

                                <x-filament::modal id="send-email">
                                    <x-slot name="trigger">
                                        <x-filament::button size="lg" color="guiddini-accent"
                                            class="flex justify-center items-center gap-2 mt-4 "
                                            icon="heroicon-o-share">
                                            <span class="flex flex-row gap-2">
                                                Envoyer par email
                                            </span>
                                        </x-filament::button>
                                    </x-slot>

                                    <form wire:submit.prevent="sendEmail" class="space-y-6">

                                        <div>
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-700 mb-1">Email
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
                        @endif

                        <div class="mt-8 flex justify-center">
                            <x-filament::button wire:click="tryAgain" size="lg" color="guiddini-accent"
                                class="flex justify-center items-center gap-2 mt-4 w-full" icon="heroicon-o-arrow-path"
                                outlined>
                                <span class="flex flex-row gap-2">
                                    Effectuer un nouveau paiement
                                </span>
                            </x-filament::button>
                        </div>
                    </div>

                    <footer class="clearfix mb-10" style="text-align: center;">
                        <h5 style="text-align: center;">Si vous rencontrez un problème avec le paiement, Contactez la SATIM</h5>

                        <div style="text-align: center;">
                            <img style="width: 100%; max-width: 140px; height: 50px; display: block; margin: 0 auto;"
                                src="{{ url('images/green_number.png') }}">
                        </div>
                    </footer>
                @else
                    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                        <form wire:submit.prevent="pay" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom
                                    complet</label>
                                <input type="text" id="name" name="name"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                                    placeholder="Votre nom et prénom">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email
                                    professionnel</label>
                                <input type="email" id="email" name="email"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                                    placeholder="exemple@entreprise.com">
                            </div>

                            <div>
                                <label for="amount"
                                    class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                                <input type="amount" id="amount" wire:model="amount"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                                    placeholder="DZD">

                                @error('amount')
                                    <p class="text-red-500 text-sm ">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <input type="checkbox" wire:model="acceptedTerms" id="accepted_terms"
                                    class="h-4 w-4 border-guiddini-primary text-guiddini-primary focus:ring-guiddini-primary rounded">
                                <label for="accepted_terms" class="text-sm text-gray-700">
                                    J'accepte les <a href="https://www.guiddini.dz/privacy"
                                        class="text-guiddini-primary hover:underline" target="_blank">conditions
                                        générales</a>
                                </label>
                                @error('acceptedTerms')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>


                            <div>
                                <x-filament::button type="submit" size="lg" color="guiddini-primary"
                                    class="flex justify-center items-center gap-2 mt-4 w-full">
                                    <span class="flex flex-row gap-2">
                                        Payer par
                                        <img src="{{ asset('images/cib_logotype.jpg') }}" alt="CIB"
                                            class="h-5 rounded-md">
                                    </span>
                                </x-filament::button>
                            </div>
                        </form>
                    </div>

                @endif
            </div>
        </main>

        <footer class="fixed bottom-0 left-0 w-full bg-white z-10">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-wrap justify-center items-center gap-2 opacity-80">
                    <img src="{{ asset('images/icon.svg') }}" alt="Icon" class="h-4">
                    <span class="text-xs text-gray-600">Powered by</span>
                    <img src="{{ asset('images/logotype.svg') }}" alt="Partner logo" class="h-5">
                </div>
            </div>
        </footer>
    </body>
    <script>
        window.addEventListener('open-link', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>

    @script
        <script>
            $wire.on('download-receipt', ({
                url
            }) => {
                window.open(url, '_blank');
            });
        </script>
    @endscript
</div>

<div>



    <body class="bg-white min-h-screen">

        <main class="container mx-auto px-4 py-12">
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

                <div class="bg-white p-8 rounded-xl shadow-md border border-gray-200 mb-8">

                    @if ($application->logo)
                        <div class="flex justify-center mb-6">
                            <img src="{{ asset($application->logo) }}" alt="Company Logo" class="h-16">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email </label>
                            <p class="text-gray-900">{{ $application->user->email }}</p>
                        </div>
                    </div>
                </div>

                @if ($this->transaction)
                    <div class="bg-white p-8 rounded-xl shadow-md border border-gray-200 mb-8">
                        <p class="text-center text-green-600 text-xl font-semibold mb-6">Payment status</p>

                        <table class="min-w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">Column 1</th>
                                    <th class="py-2 px-4 border-b">Column 2</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($this->transaction->status === 'paid')
                                    {{-- Payment successful details --}}
                                    <tr>
                                        <td class="py-2 px-4 border-b">Méthode de paiement</td>
                                        <td class="py-2 px-4 border-b">CIB / Edahabia</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Numéro de commande</td>
                                        <td class="py-2 px-4 border-b">{{ $this->transaction->order_id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">ID de transaction</td>
                                        <td class="py-2 px-4 border-b">{{ $this->transaction->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Numéro d'autorisation</td>
                                        <td class="py-2 px-4 border-b">{{ $this->transaction->auth_code }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Montant total</td>
                                        <td class="py-2 px-4 border-b">{{ $this->transaction->deposit_amount }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Date et heure</td>
                                        <td class="py-2 px-4 border-b">{{ $this->transaction->deposit_amount }}</td>
                                    </tr>
                                @else
                                    {{-- Payment failed or pending details --}}
                                    <tr>
                                        <td class="py-2 px-4 border-b">Status</td>
                                        <td class="py-2 px-4 border-b text-red-600">Not Paid</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Error Message</td>
                                        <td class="py-2 px-4 border-b">Payment failed or is pending</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-4 border-b">Suggested Action</td>
                                        <td class="py-2 px-4 border-b">Please try again or contact support</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="mt-6 flex justify-center gap-4">
                            <button class="bg-blue-500 text-white py-2 px-6 rounded-lg">Download PDF</button>
                            <button class="bg-green-500 text-white py-2 px-6 rounded-lg">Send via Email</button>
                        </div>
                    </div>
                @else
                    <div class="bg-white p-8 rounded-xl shadow-md border border-gray-200">
                        <form wire:submit.prevent="submit" class="space-y-6">

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
                                <label for="message"
                                    class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                                <input type="amount" id="amount" wire:model="amount"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                                    placeholder="exemple@entreprise.com">

                                @error('amount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <button type="submit"
                                    class="w-full bg-guiddini-accent text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-500 transition-colors flex justify-center items-center gap-2">
                                    <span>Payer par</span>
                                    <img src="{{ asset('images/cib_logotype.jpg') }}" alt="CIB" class="h-10">
                                </button>
                            </div>
                        </form>
                    </div>

                @endif
            </div>
        </main>

        <div class="absolute bottom-0 left-0 w-full">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-wrap justify-center items-center gap-2 opacity-80">
                    <img src="{{ asset('images/icon.svg') }}" alt="Icon" class="h-4">
                    <span class="text-xs text-gray-600">Powered by</span>
                    <img src="{{ asset('images/logotype.svg') }}" alt="Partner logo" class="h-5">
                </div>
            </div>
        </div>

    </body>


</div>

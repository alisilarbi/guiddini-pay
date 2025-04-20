<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guiddini - Demande de Contact</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'guiddini-primary': '#1e3a8a',
                        'guiddini-accent': '#3b82f6',
                        'guiddini-light': '#f1f5f9',
                        'guiddini-text': '#1f2937',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-white min-h-screen">

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Badge -->
            <div class="flex justify-center mb-8">
                <div
                    class="inline-flex items-center px-4 py-2 rounded-full bg-guiddini-light text-guiddini-text text-sm">
                    <span class="text-yellow-500 mr-2">✨</span>
                    <span>Solutions adaptées à votre secteur</span>
                </div>
            </div>

            <!-- Heading -->
            <h1 class="text-4xl md:text-5xl font-bold text-center text-guiddini-text mb-4">
                Paiement <span class="text-guiddini-accent">Spontané</span>
            </h1>

            <p class="text-center text-guiddini-text mb-12 max-w-2xl mx-auto text-lg md:text-xl">
                Vous souhaitez nous régler via un <span class="text-guiddini-accent">paiement spontané</span> ?
                Remplissez simplement le formulaire ci-dessous.
            </p>

            <!-- Form -->
            <div class="bg-white p-8 rounded-xl shadow-md border border-gray-200">
                <form class="space-y-6" wire:submit.prevent="submit">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
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
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                        <input type="text" id="amount" wire:model="amount"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-guiddini-accent"
                            placeholder="Montant">

                        @error('amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-guiddini-accent text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-500 transition-colors">
                            Payer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Partner Logos -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap justify-center items-center gap-4 opacity-80">
            <img src="{{ asset('images/icon.svg') }}" alt="Icon" class="h-6">
            <span class="text-sm text-gray-600">Powered by</span>
            <img src="{{ asset('images/logotype.svg') }}" alt="Partner logo" class="h-8">
        </div>
    </div>

</body>

</html>

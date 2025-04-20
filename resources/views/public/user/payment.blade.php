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
                        'guiddini-dark': '#0a1428',
                        'guiddini-blue': '#1e3a8a',
                        'guiddini-light': '#3b82f6',
                        'guiddini-nav': '#111827',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-guiddini-blue min-h-screen">

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Badge -->
            <div class="flex justify-center mb-8">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-guiddini-dark/50 text-white text-sm">
                    <span class="text-yellow-400 mr-2">✨</span>
                    <span>Solutions adaptées à votre secteur</span>
                </div>
            </div>

            <!-- Heading -->
            <h1 class="text-4xl md:text-5xl font-bold text-center text-white mb-4">
                Contactez notre équipe <span class="text-guiddini-light">d'experts</span>
            </h1>

            <p class="text-center text-gray-300 mb-12 max-w-2xl mx-auto">
                Vous souhaitez intégrer notre solution de paiement sur votre site web ou application ?
                Remplissez le formulaire ci-dessous et nous vous contacterons rapidement.
            </p>

            <!-- Form -->
            <div class="bg-guiddini-dark/30 backdrop-blur-sm p-8 rounded-xl shadow-lg">
                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nom complet</label>
                        <input type="text" id="name" name="name"
                            class="w-full px-4 py-3 bg-guiddini-dark/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-guiddini-light"
                            placeholder="Votre nom et prénom">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email
                            professionnel</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-3 bg-guiddini-dark/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-guiddini-light"
                            placeholder="exemple@entreprise.com">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-300 mb-1">Votre message</label>
                        <textarea id="message" name="message" rows="4"
                            class="w-full px-4 py-3 bg-guiddini-dark/50 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-guiddini-light"
                            placeholder="Décrivez votre projet ou vos besoins..."></textarea>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-400 text-white font-medium py-3 px-6 rounded-lg hover:opacity-90 transition-opacity">
                            Envoyer ma demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap justify-center items-center gap-8 opacity-70">
            <img src="{{ asset('images/icon.svg') }}" alt="Partner logo" class="h-8">
            @if ($application->logo)
                <img src="{{ $application->logo }}" alt="Partner logo" class="h-8">
            @endif
        </div>
    </div>

</body>

</html>

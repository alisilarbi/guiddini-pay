<x-filament-panels::page>
    <div class="bg-primary-50 border border-primary-200 text-sm text-primary-800 rounded-lg p-4 dark:bg-primary-800/10 dark:border-primary-900 dark:text-primary-500"
        role="alert" tabindex="-1" aria-labelledby="hs-with-description-label">
        <div class="flex">
            <div class="shrink-0">
                <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
            </div>
            <div class="ms-4">
                {{-- <h3 id="hs-with-description-label" class="text-sm font-semibold">
                    N.B :
                </h3> --}}
                <div class="text-sm text-primary-700">
                    Chaque application représente une intégration de paiement prête à être utilisée sur le site web,
                    l'application mobile ou toute autre plateforme numérique de votre client
                </div>
            </div>
        </div>
    </div>
    {{ $this->table }}
</x-filament-panels::page>

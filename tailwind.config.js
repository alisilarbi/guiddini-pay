import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/livewire/hooks/applications-allowance-overview.blade.php',
    ],
    safelist: [
        // Dynamically generated classes in your component
        'bg-red-50', 'bg-orange-50', 'bg-green-50',
        'border-red-300', 'border-orange-300', 'border-green-300',
        'hover:bg-red-100', 'hover:bg-orange-100', 'hover:bg-green-100',
        'text-red-600', 'text-orange-600', 'text-green-600',
        'bg-red-500', 'bg-orange-500', 'bg-green-500',
    ],
    theme: {
        extend: {
            colors: {
                'guiddini-primary': '#1e3a8a',
                'guiddini-accent': '#3b82f6',
                'guiddini-light': '#f1f5f9',
                'guiddini-text': '#1f2937',
            },
        },
    },
}

import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/livewire/hooks/*.blade.php',
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

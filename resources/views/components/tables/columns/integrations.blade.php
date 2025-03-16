<div class="flex justify-center items-center space-x-2">
    @if ($getRecord()->website_integration)
        <x-heroicon-o-globe-alt class="w-6 h-6 text-emerald-500" />
    {{-- @else
        <x-heroicon-o-x-circle class="w-6 h-6 text-rose-500" /> --}}
    @endif

    @if ($getRecord()->mobile_integration)
        <x-heroicon-o-device-phone-mobile class="w-6 h-6 text-sky-500" />
    {{-- @else
        <x-heroicon-o-x-circle class="w-6 h-6 text-gray-400" /> --}}
    @endif
</div>
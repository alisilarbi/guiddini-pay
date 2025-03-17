<div class="flex justify-center items-center space-x-2 px-3">
    @if ($getRecord()->website_integration)
        <x-heroicon-o-globe-alt class="w-6 h-6" color="#10B981"/>
    @else
        <x-heroicon-o-x-circle class="w-6 h-6" color="#F43F5E"/>
    @endif

    @if ($getRecord()->mobile_integration)
        <x-heroicon-o-device-phone-mobile class="w-6 h-6" color="#0EA5E9"/>
    @else
        <x-heroicon-o-x-circle class="w-6 h-6" color="#F43F5E"/>
    @endif
</div>
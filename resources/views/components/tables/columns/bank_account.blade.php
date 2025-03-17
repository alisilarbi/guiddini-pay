<div class="flex justify-start items-center w-full text-center px-3">
    @if ($getRecord()->has_bank_account)
        {{ $getRecord()->bank_name }}
    @else
        <x-heroicon-o-x-circle class="w-6 h-6" color="#F43F5E" />
    @endif
</div>

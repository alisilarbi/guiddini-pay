@component('mail::message')
    # New Prospect Created

    A new prospect has been added.

    **Name:** {{ $prospect->name }}
    **Company Name:** {{ $prospect->company_name }}
    **Phone:** {{ $prospect->phone }}
    **Email:** {{ $prospect->email }}
    **Legal Status:** {{ $prospect->legal_status }}
    **Has Bank Account:** {{ $prospect->has_bank_account ? 'Yes' : 'No' }}
    @if ($prospect->has_bank_account)
        **Bank Name:** {{ $prospect->bank_name }}
    @endif
    **Website URL:** {{ $prospect->website_url }}
    **Programming Languages:** {{ implode(', ', $prospect->programming_languages ?? []) }}
    **Needs Help:** {{ $prospect->needs_help ? 'Yes' : 'No' }}
    **Reference:** {{ $prospect->reference }}
@endcomponent

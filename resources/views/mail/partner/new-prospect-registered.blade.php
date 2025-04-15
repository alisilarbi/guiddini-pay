@component('mail::message')
    # New Prospect Created

    Name: {{ $prospect->name }}
    Company Name: {{ $prospect->company_name }}
    Phone: {{ $prospect->phone }}
    Email: {{ $prospect->email }}
    Legal Status: {{ $prospect->legal_status }}
    Has Bank Account: {{ $prospect->has_bank_account ? 'Yes' : 'No' }}
    @if ($prospect->has_bank_account && $prospect->bank_name)
    Bank Name: {{ $prospect->bank_name }}
    @endif
    @if ($prospect->website_url)
    Website URL: {{ $prospect->website_url }}
    @endif
    @if (!empty($prospect->programming_languages))
    Programming Languages:
        {{-- {{ is_array($prospect->programming_languages) ? implode(', ', $prospect->programming_languages) : $prospect->programming_languages }} --}}
        {{ is_array($prospect->programming_languages) ? implode(', ', $prospect->programming_languages) : implode(', ', json_decode($prospect->programming_languages, true)) }}

    @endif
    Needs Help: {{ $prospect->needs_help ? 'Yes' : 'No' }}
    @if ($prospect->reference)
        Reference: {{ $prospect->reference }}
    @endif
@endcomponent

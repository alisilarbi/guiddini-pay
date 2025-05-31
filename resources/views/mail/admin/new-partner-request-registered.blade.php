@component('mail::message')
    # New Prospect Created

    Name: {{ $partnerRequest->name }}
    Email: {{ $partnerRequest->email }}
    Phone: {{ $partnerRequest->phone }}
    Type: {{ $partnerRequest->business_type }}
    @if ($partnerRequest->company_name)
        Company: {{ $partnerRequest->company_name }}
    @endif
@endcomponent

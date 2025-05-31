<?php

namespace App\Actions\PartnerRequest;

use App\Models\PartnerRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\Partner\NewProspectRegistered;
use App\Mail\Admin\NewPartnerRequestRegistered;

class CreatePartnerRequest
{
    /**
     * Handle the creation of a partner request.
     *
     * @param array $data
     * @return \App\Models\PartnerRequest
     */
    public function handle(array $data): PartnerRequest
    {
        $partnerRequest = PartnerRequest::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'business_type' => $data['business_type'],
            'company_name' => $data['company_name'],
            'converted' => false,
        ]);

        Mail::to('mourad@guiddini.com')->send(new NewPartnerRequestRegistered($partnerRequest));
        Mail::to('nayla@guiddini.com')->send(new NewPartnerRequestRegistered($partnerRequest));
        Mail::to('lisa@guiddini.com')->send(new NewPartnerRequestRegistered($partnerRequest));

        return $partnerRequest;
    }
}

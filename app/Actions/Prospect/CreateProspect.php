<?php

namespace App\Actions\Prospect;

use App\Models\User;
use App\Models\Prospect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\Partner\NewProspectRegistered;

class CreateProspect
{
    public function handle(User $partner, array $data): Prospect
    {
        $prospect = Prospect::create([
            'name' => $data['name'],
            'company_name' => $data['company_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'legal_status' => $data['legal_status'],
            'has_bank_account' => $data['has_bank_account'],
            'bank_name' => $data['bank_name'],
            'converted' => false,
            'website_integration' => $data['website_integration'],
            'mobile_integration' => $data['mobile_integration'],
            'website_url' => $data['website_url'],
            'programming_languages' => $data['programming_languages'],
            'needs_help' => $data['needs_help'],
            'reference' => strtoupper(Str::random(2)) . rand(10, 99),
            'needs_help' => $data['needs_help'],
            'converted' => false,
            'partner_id' => $partner->id,
        ]);

        Mail::to($partner->email)->send(new NewProspectRegistered($prospect));
        Mail::to('nayla@guiddini.com')->send(new NewProspectRegistered($prospect));
        Mail::to('lisa@guiddini.com')->send(new NewProspectRegistered($prospect));


        return $prospect;
    }
}

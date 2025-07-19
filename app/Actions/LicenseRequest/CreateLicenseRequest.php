<?php

namespace App\Actions\LicenseRequest;

use App\Models\User;
use App\Models\LicenseRequest;

class CreateLicenseRequest
{
    public function handle(User $partner, array $data): void
    {

        $licenseRequest = LicenseRequest::create([
            // 'name' => $data['name'],
            // 'application_id' => $data['application_id'],
            // 'bank_document' => $data['bank_document'],
            // 'registration_document' => $data['registration_document'],
            // 'partner_id' => Auth::user()->id,
        ]);

    }
}
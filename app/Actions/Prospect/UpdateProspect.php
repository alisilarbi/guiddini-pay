<?php

namespace App\Actions\Prospect;

use App\Models\Prospect;

class UpdateProspect
{
    public function handle(Prospect $prospect, array $data): Prospect
    {
        $allowedFields = [
            'name',
                'company_name',
                'phone',
                'email',
                'legal_status',
                'has_bank_account',
                'bank_name',
                'converted',
                'website_integration',
                'mobile_integration',
                'needs_help',
                'reference',
                'website_url',
                'programming_languages'
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (!empty($updateData)) {
            $prospect->update($updateData);
        }

        return $prospect;

    }
}

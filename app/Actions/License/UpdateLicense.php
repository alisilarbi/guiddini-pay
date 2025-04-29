<?php

namespace App\Actions\License;

use App\Models\License;

class UpdateLicense
{
    public function handle(License $license, array $data): License
    {
        $allowedFields = [
            'name',
            'satim_development_username',
            'satim_development_password',
            'satim_development_terminal',
            'satim_production_username',
            'satim_production_password',
            'satim_production_terminal',
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (!empty($updateData)) {
            $license->update($updateData);
        }

        return $license;
    }
}
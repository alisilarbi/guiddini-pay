<?php

namespace App\Actions\License;

use App\Models\License;

class UpdateLicense
{
    public function handle(License $license, array $data): License
    {
        $allowedFields = [
            'name',
            'gateway_type',
            'satim_development_username',
            'satim_development_password',
            'satim_development_terminal',
            'satim_production_username',
            'satim_production_password',
            'satim_production_terminal',
            'poste_dz_development_username',
            'poste_dz_development_password',
            'poste_dz_production_username',
            'poste_dz_production_password',
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (!empty($updateData)) {
            $license->update($updateData);
        }

        return $license;
    }
}
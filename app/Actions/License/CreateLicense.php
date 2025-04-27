<?php

namespace App\Actions\License;

use App\Models\User;
use App\Models\License;

class CreateLicense
{
    public function handle(User $user, User $partner, array $data)
    {
        License::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'name' => $data['name'],

            'satim_development_username' => $data['satim_development_username'],
            'satim_development_password' => $data['satim_development_password'],
            'satim_development_terminal' => $data['satim_development_terminal'],

            'satim_production_username' => $data['satim_production_username'],
            'satim_production_password' => $data['satim_production_password'],
            'satim_production_terminal' => $data['satim_production_terminal'],
        ]);
    }
}

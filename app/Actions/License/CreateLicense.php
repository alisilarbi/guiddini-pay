<?php

namespace App\Actions\License;

use App\Models\User;
use App\Models\License;

class CreateLicense
{
    public function handle(User $user, User $partner, array $data): License
    {

        $license = License::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'name' => $data['name'] ?? null,

            'satim_development_username' => $data['satim_development_username'] ?? null,
            'satim_development_password' => $data['satim_development_password'] ?? null,
            'satim_development_terminal' => $data['satim_development_terminal'] ?? null,

            'satim_production_username' => $data['satim_production_username'] ?? null,
            'satim_production_password' => $data['satim_production_password'] ?? null,
            'satim_production_terminal' => $data['satim_production_terminal'] ?? null,
        ]);

        return $license;
    }
}

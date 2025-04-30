<?php

namespace App\Actions\Partner;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreatePartner
{
    public function handle(array $data): User
    {
        $partner = User::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']) ?? null,
            'partner_mode' => $data['partner_mode'] ?? null,
            'default_is_paid' => $data['default_is_paid'] ?? null,
            'application_price' => $data['application_price'] ?? null,
            'is_admin' => false,
            'is_partner' => true,
            'is_user' => false,
        ]);

        return $partner;
    }
}
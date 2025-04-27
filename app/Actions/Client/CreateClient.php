<?php

namespace App\Actions\Client;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateClient
{
    public function handle(User $partner, array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
            'is_partner' => false,
            'is_user' => false,
            'partner_id' => $partner->id,
        ]);

        return $user;

    }
}
<?php

namespace App\Actions\Client;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateClient
{
    public function handle(User $partner, array $data): User
    {
        $client = User::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']) ?? null,
            'is_admin' => false,
            'is_partner' => false,
            'is_user' => true,
            'partner_id' => $partner->id,
        ]);

        return $client;
    }
}
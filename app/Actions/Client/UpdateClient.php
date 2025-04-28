<?php

namespace App\Actions\Client;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateClient
{
    public function handle(User $client, array $data)
    {
        $allowedFields = ['name', 'email', 'password'];

        if (isset($data['new_password'])) {
            $data['password'] = $data['new_password'];
            unset($data['new_password']);
        }

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (isset($updateData['password']) && $updateData['password'] !== null) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        if (!empty($updateData)) {
            $client->update($updateData);
        }

        $client->save();
    }
}

<?php

namespace App\Actions\Client;

use App\Models\User;

class UpdateClient
{
    public function handle(User $client, array $data)
    {
        $allowedFields = ['name', 'email', 'password'];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (isset($updateData['password']) && $updateData['password'] !== null) {
            $updateData['password'] = bcrypt($updateData['password']);
        }

        if (!empty($updateData)) {
            $client->update($updateData);
        }
    }
}

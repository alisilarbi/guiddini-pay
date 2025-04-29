<?php

namespace App\Actions\Client;

use App\Models\User;

class DeleteClient
{
    /**
     * @return Application[]
     */
    public function handle(User $client, User $partner): array
    {
        $apps = [];
        if ($client->applications->isNotEmpty()) {
            foreach ($client->applications as $app) {
                $app->update([
                    'user_id' =>  $partner->id
                ]);

                $apps[] = [
                    'type' => 'application',
                    'id' => $app->id,
                    'attributes' => [
                        'name' => $app->name,
                        'license_id' => $app->license_id,
                        'license_env' => $app->license_env
                    ]
                ];
            }
        }
        $client->delete();

        return $apps;
    }
}

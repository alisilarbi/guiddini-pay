<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\Application;

class RecoverOwernership
{

    public function handle(User $newOwner, Application $application)
    {
        $application->update([
            'user_id' => $newOwner->id,
        ]);
    }
}
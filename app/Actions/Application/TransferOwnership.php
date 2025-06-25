<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\Application;

class TransferOwnership
{
    public function handle(User $newOwner, Application $application): Application
    {
        $application->update([
            'user_id' => $newOwner->id,
        ]);

        Notification::make()
            ->title('Ownership recovered')
            ->success()
            ->send();

        return $application;
    }
}

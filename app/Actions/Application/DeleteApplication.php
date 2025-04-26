<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;

class DeleteApplication
{
    public function handle(User $user, Application $application): void
    {
        if ($application->logo) {
            Storage::disk('logos')->delete(basename($application->logo));
        }

        $application->delete();
    }
}
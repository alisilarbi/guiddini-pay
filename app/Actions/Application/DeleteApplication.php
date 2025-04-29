<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;

class DeleteApplication
{
    public function handle(Application $application): void
    {
        if ($application->logo) {
            Storage::disk('public')->delete(basename($application->logo));
        }

        $application->delete();
    }
}
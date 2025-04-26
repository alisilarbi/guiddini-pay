<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\License;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UpdateApplication
{
    public function handle(User $user, Application $application, array $data): void
    {
        $env = License::where('id', $data['license'])->first();
        $application->update([
            'name' => $data['name'],
            'website_url' => $data['website_url'],
            'redirect_url' => $data['redirect_url'],
            'license_env' => $data['license_env'],
            'license_id' => $env->id,
        ]);

        if ($data['logo'] && $data['logo'] !== $application->logo) {
            $tempPath = Storage::disk('public')->path($data['logo']);
            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);

            Storage::disk('public')->putFileAs('logos', $tempPath, $newFileName);
            Storage::disk('public')->delete($data['logo']);

            $path = 'storage/logos/' . $newFileName;
            $application->update([
                'logo' => $path,
            ]);
        }

        if (is_null($data['logo']) && $application->logo) {
            Storage::disk('public')->delete(basename($application->logo));
            $application->update(['logo' => null]);
        }
    }
}
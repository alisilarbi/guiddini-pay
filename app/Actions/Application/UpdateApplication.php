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
        $updatableFields = ['name', 'website_url', 'redirect_url', 'license_env'];
        $updateData = array_intersect_key($data, array_flip($updatableFields));

        // Handle license assignment
        if (array_key_exists('license', $data)) {
            $updateData['license_id'] = !is_null($data['license']) && ($license = License::find($data['license']))
                ? $license->id
                : null;
        }

        if ($data['logo'] && $data['logo'] !== $application->logo) {
            $tempPath = Storage::disk('public')->path($data['logo']);
            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);

            Storage::disk('public')->putFileAs(null, $tempPath, $newFileName);
            Storage::disk('public')->delete($data['logo']);

            $path = 'storage/' . $newFileName;
            $application->update([
                'logo' => $path,
            ]);
        }

        if (is_null($data['logo']) && $application->logo) {
            Storage::disk('public')->delete(basename($application->logo));
            $application->update(['logo' => null]);
        }

        $application->update($updateData);
    }
}

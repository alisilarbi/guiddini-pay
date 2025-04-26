<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\License;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CreateApplication
{
    public function handle(User $user, User $partner, array $data): void
    {
        $application = Application::create([
            'name' => $data['name'],
            'website_url' => $data['website_url'],
            'redirect_url' => $data['redirect_url'],
            'partner_id' => $partner->id,
            'user_id' => $user->id,
        ]);

        if ($data['logo']) {
            $tempPath = Storage::disk('public')->path($data['logo']);
            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);

            Storage::disk('public')->putFileAs('logos', $tempPath, $newFileName);
            Storage::disk('public')->delete($data['logo']);

            $path = 'storage/logos/' . $newFileName;
            $application->update([
                'logo' => $path,
            ]);
        }

        $env = License::where('id', $data['license'])->first();
        $application->update([
            'license_env' => $data['license_env'],
            'license_id' => $env->id,
        ]);
    }
}

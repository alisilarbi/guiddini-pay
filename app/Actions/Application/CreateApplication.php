<?php

namespace App\Actions\Application;

use App\Models\User;
use App\Models\License;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CreateApplication
{
    public function handle(User $user, User $partner, array $data): Application
    {
        $application = Application::create([
            'name' => $data['name'],
            'website_url' => $data['website_url'],
            'redirect_url' => $data['redirect_url'],
            'partner_id' => $partner->id,
            'user_id' => $user->id,
        ]);

        if (!empty($data['logo'])) {
            $tempPath = Storage::disk('public')->path($data['logo']);
            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);

            Storage::disk('public')->putFileAs(null, $tempPath, $newFileName);
            Storage::disk('public')->delete($data['logo']);

            $path = 'storage/' . $newFileName;
            $application->update([
                'logo' => $path,
            ]);
        }

        if (isset($data['license'])) {
            $env = License::where('id', $data['license'])->first();
            $application->update([
                'license_env' => $data['license_env'],
                'license_id' => $env->id,
            ]);
        }else{
            $license = $partner->licenses()->first();
            $application->update([
                'license_env' => 'development',
                'license_id' => $license->id,
            ]);
        }

        // else{
            //put code where to get default license
        // }



        return $application;
    }

}

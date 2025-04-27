<?php

namespace App\Actions\Prospect;

use App\Models\Prospect;
use App\Models\Application;
use Illuminate\Support\Str;
use App\Actions\Client\CreateClient;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Application\CreateApplication;

class ConvertProspect
{
    protected CreateApplication $createApplication;
    protected CreateClient $createClient;

    public function __construct(CreateApplication $createApplication, CreateClient $createClient)
    {
        $this->createApplication = $createApplication;
        $this->createClient = $createClient;
    }

    public function handle(User $partner, Prospect $prospect): void
    {
        $license = $partner->licenses->first();
        $user = User::where('email', $prospect->email)->first();
        if (!$user) {
            $user = $this->createClient->handle(
                partner: $partner,
                data: [
                    'name' => $prospect->name,
                    'email' => $prospect->email,
                    'password' => Str::random(12),
                    'is_user' => true,
                ]
            );
        }

        $application = $this->createApplication->handle(
            user: new \App\Models\User($user->toArray()),
            partner: $partner,
            data: [
                'name' => $prospect->company_name,
                'website_url' => $prospect->website_url,
                'redirect_url' => $prospect->website_url,
                'license' => $license->id,
                'license_env' => 'development',
            ]
        );

        $prospect->update([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'converted' => true,
        ]);
    }
}

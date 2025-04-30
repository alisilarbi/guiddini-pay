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

        if ($partner->partner_mode === 'quota') {

            if (!$partner->canCreateApplication()) {
                throw new \Exception('ALLOWANCE_DEPLETED', 403);
            }

            $latestTransaction = $partner->quotaTransactions()->latest()->first();
            if (!$latestTransaction) {
                throw new \Exception('NO_QUOTA_TRANSACTION', 400);
            }

            $data['quota_transaction_id'] = $latestTransaction->id;
            $data['is_paid'] = $latestTransaction->is_paid;

        }

        $application = Application::create([
            'name' => $data['name'],
            'website_url' => $data['website_url'],
            'redirect_url' => $data['redirect_url'],
            'partner_id' => $partner->id,
            'user_id' => $user->id,
            'quota_transaction_id' => $data['quota_transaction_id'] ?? null,
            'is_paid' => $data['is_paid'] ?? null,
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
        } else {
            $license = $partner->licenses()->first();
            $application->update([
                'license_env' => 'development',
                'license_id' => $license->id,
            ]);
        }

        $partner->decrement('remaining_allowance');

        // else{
        //put code where to get default license
        // }

        return $application;
    }
}

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
        $quotaTransactionId = null;
        $paymentStatus = $partner->default_is_paid ? 'paid' : 'unpaid';

        if ($partner->partner_mode === 'quota') {
            if ($partner->available_quota <= 0) {
                throw new \Exception('QUOTA_DEPLETED', 403);
            }

            $availableTransaction = $partner->quotaTransactions()
                ->where('remaining_quantity', '>', 0)
                ->where('status', 'active')
                ->orderBy('payment_status', 'asc')
                ->first();

            if (!$availableTransaction) {
                throw new \Exception('NO_QUOTA_AVAILABLE', 400);
            }

            $quotaTransactionId = $availableTransaction->id;
            $paymentStatus = $availableTransaction->payment_status;

            $availableTransaction->decrement('remaining_quantity');
            if ($availableTransaction->remaining_quantity === 0) {
                $availableTransaction->update(['status' => 'exhausted']);
            }

            $partner->decrement('available_quota');
            $partner->increment('used_quota');
        }

        $application = Application::create([
            'name' => $data['name'],
            'website_url' => $data['website_url'],
            'redirect_url' => $data['redirect_url'],
            'partner_id' => $partner->id,
            'user_id' => $user->id,
            'quota_id' => $quotaTransactionId,
            'payment_status' => $paymentStatus,
        ]);

        if (!empty($data['logo'])) {
            $tempPath = Storage::disk('public')->path($data['logo']);
            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);
            Storage::disk('public')->putFileAs('', new \Illuminate\Http\File($tempPath), $newFileName);
            Storage::disk('public')->delete($data['logo']);
            $application->update(['logo' => 'storage/' . $newFileName]);
        }

        if (isset($data['license'])) {
            $license = License::findOrFail($data['license']);
            $application->update([
                'license_env' => $data['license_env'],
                'license_id' => $license->id,
            ]);
        } else {
            $license = $partner->licenses()->firstOrFail();
            $application->update([
                'license_env' => 'development',
                'license_id' => $license->id,
            ]);
        }

        return $application;
    }

    // public function updatePaymentStatus(Application $application, string $newStatus): void
    // {
    //     $oldStatus = $application->payment_status;
    //     if ($oldStatus === $newStatus) {
    //         return;
    //     }

    //     $partner = $application->partner;
    //     $application->update(['payment_status' => $newStatus]);

    //     if ($oldStatus === 'paid' && $newStatus === 'unpaid') {
    //         $partner->decrement('total_paid_applications');
    //         $partner->increment('total_unpaid_applications');
    //     } elseif ($oldStatus === 'unpaid' && $newStatus === 'paid') {
    //         $partner->decrement('total_unpaid_applications');
    //         $partner->increment('total_paid_applications');
    //     }

    //     EventHistory::create([
    //         'event_type' => 'Application',
    //         'event_code' => 'APP_PAYMENT_UPDATE_' . $application->id,
    //         'event_summary' => 'Application payment status updated',
    //         'eventable_id' => $application->id,
    //         'eventable_type' => Application::class,
    //         'action' => 'Payment Status Updated',
    //         'payment_status' => $newStatus,
    //         'price' => $application->quotaTransaction ? $application->quotaTransaction->application_price : 0.00,
    //         'quantity' => 1,
    //         'total' => $application->quotaTransaction ? $application->quotaTransaction->application_price : 0.00,
    //         'details' => [
    //             'old_status' => $oldStatus,
    //             'new_status' => $newStatus,
    //             'application_name' => $application->name,
    //         ],
    //         'user_id' => $application->user_id,
    //         'partner_id' => $partner->id,
    //     ]);
    // }
}




// namespace App\Actions\Application;

// use App\Models\User;
// use App\Models\License;
// use App\Models\Application;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;

// class CreateApplication
// {
//     public function handle(User $user, User $partner, array $data): Application
//     {

//         if ($partner->partner_mode === 'quota') {
//             if ($partner->remaining_allowance <= 0) {
//                 throw new \Exception('ALLOWANCE_DEPLETED', 403);
//             }

//             $availableTransaction = $partner->quotaTransactions()
//                 ->where('remaining_quantity', '>', 0)
//                 ->orderBy('payment_status', 'asc')
//                 ->first();

//             if (!$availableTransaction) {
//                 throw new \Exception('NO_QUOTA_AVAILABLE', 400);
//             }

//             $quotaTransactionId = $availableTransaction->id;
//             $paymentStatus = $availableTransaction->payment_status;

//             $availableTransaction->decrement('remaining_quantity');
//         }

//         $application = Application::create([
//             'name' => $data['name'],
//             'website_url' => $data['website_url'],
//             'redirect_url' => $data['redirect_url'],
//             'partner_id' => $partner->id,
//             'user_id' => $user->id,
//             'quota_transaction_id' => $quotaTransactionId,
//             'payment_status' => $paymentStatus,
//         ]);

//         if (!empty($data['logo'])) {
//             $tempPath = Storage::disk('public')->path($data['logo']);
//             $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);

//             Storage::disk('public')->putFileAs(null, $tempPath, $newFileName);
//             Storage::disk('public')->delete($data['logo']);

//             $path = 'storage/' . $newFileName;
//             $application->update([
//                 'logo' => $path,
//             ]);
//         }

//         if (isset($data['license'])) {
//             $env = License::where('id', $data['license'])->first();
//             $application->update([
//                 'license_env' => $data['license_env'],
//                 'license_id' => $env->id,
//             ]);
//         } else {
//             $license = $partner->licenses()->first();
//             $application->update([
//                 'license_env' => 'development',
//                 'license_id' => $license->id,
//             ]);
//         }

//         if ($partner->partner_mode === 'quota') {
//             $partner->decrement('remaining_allowance');

//         }

//         $partner->increment('total_applications', 1);
//         // else{
//         //put code where to get default license
//         // }

//         return $application;
//     }
// }

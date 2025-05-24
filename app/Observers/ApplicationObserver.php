<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\EventHistory;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {

        EventHistory::create([
            'event_type' => 'application',
            'event_code' => 'application_creation',
            'event_summary' => 'Application created',
            'eventable_id' => $application->id,
            'eventable_type' => Application::class,
            'action' => 'Created',
            'payment_status' => $application->payment_status,
            'price' => $application->quota->application_price,
            'quantity' => 1,
            'total' => $application->quota->application_price,
            'details' => [
                'price' => $application->quota->application_price,
                'quantity' => 1,
                'payment_status' => $application->payment_status,
            ],
            'user_id' => null,
            'partner_id' => $application->partner_id,
        ]);
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        // if ($application->wasChanged('payment_status')) {
        //     if ($application->payment_status === 'paid') {
        //         EventHistory::create([
        //             'event_type' => 'application',
        //             'event_code' => 'application_paid',
        //             'event_summary' => 'Application Paid',
        //             'eventable_id' => $application->id,
        //             'eventable_type' => Application::class,
        //             'action' => 'Payment Successful',
        //             'payment_status' => $application->payment_status,
        //             'price' => $application->quotaTransaction->application_price,
        //             'quantity' => 1,
        //             'total' => $application->quotaTransaction->application_price,
        //             'details' => [
        //                 // 'price' => $application->quotaTransaction->application_price,
        //                 // 'quantity' => 1,
        //             ],
        //             'user_id' => null,
        //             'partner_id' => $application->partner_id,
        //         ]);
        //     } else {
        //         EventHistory::create([
        //             'event_type' => 'application',
        //             'event_code' => 'application_unpaid',
        //             'event_summary' => 'Application Unpaid',
        //             'eventable_id' => $application->id,
        //             'eventable_type' => Application::class,
        //             'action' => 'Payment Successful',
        //             'payment_status' => $application->payment_status,
        //             'price' => $application->quotaTransaction->application_price,
        //             'quantity' => 1,
        //             'total' => $application->quotaTransaction->application_price,
        //             'details' => [
        //                 // 'application_name' => $application->name,
        //                 // 'license_env' => $application->license_env,
        //             ],
        //             'user_id' => null,
        //             'partner_id' => $application->partner_id,
        //         ]);
        //     }
        // }
    }

    /**
     * Handle the Application "deleted" event.
     */
    public function deleted(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "restored" event.
     */
    public function restored(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "force deleted" event.
     */
    public function forceDeleted(Application $application): void
    {
        //
    }
}

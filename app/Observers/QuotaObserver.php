<?php

namespace App\Observers;

use App\Models\Quota;
use App\Models\EventHistory;

class QuotaObserver
{
    /**
     * Handle the QuotaTransaction "created" event.
     */
    public function created(Quota $transaction): void
    {
        EventHistory::create([
            'event_type' => 'quota',
            'event_code' => 'quota_creation',
            'event_summary' => 'Quota Creation',
            'eventable_id' => $transaction->id,
            'eventable_type' => Quota::class,
            'action' => 'Admin Grant',
            'payment_status' => $transaction->payment_status,
            'price' => $transaction->application_price,
            'quantity' => $transaction->quantity,
            'total' => $transaction->total,
            'details' => [
                'quantity' => $transaction->quantity,
                'price' => $transaction->application_price,
                'payment_status' => $transaction->payment_status,
            ],
            'partner_id' => $transaction->partner_id,
        ]);
    }




    /**
     * Handle the QuotaTransaction "updated" event.
     */
    public function updated(Quota $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "deleted" event.
     */
    public function deleted(Quota $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "restored" event.
     */
    public function restored(Quota $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "force deleted" event.
     */
    public function forceDeleted(Quota $quotaTransaction): void
    {
        //
    }
}

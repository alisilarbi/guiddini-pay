<?php

namespace App\Observers;

use App\Models\EventHistory;
use App\Models\QuotaTransaction;

class QuotaTransactionObserver
{
    /**
     * Handle the QuotaTransaction "created" event.
     */
    public function created(QuotaTransaction $transaction): void
    {
        EventHistory::create([
            'event_type' => 'quota',
            'event_code' => 'quota_creation',
            'event_summary' => 'Quota Transaction',
            'eventable_id' => $transaction->id,
            'eventable_type' => QuotaTransaction::class,
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

        // EventHistory::create([
        //     'event_type' => 'quota',
        //     'event_code' => 'quota_creation',
        //     'event_summary' => 'Quota Transaction',
        //     'eventable_id' => $transaction->id,
        //     'eventable_type' => QuotaTransaction::class,
        //     'action' => $transaction->type === 'admin_grant' ? 'Admin Grant' : 'Purchase',
        //     'payment_status' => $transaction->payment_status,
        //     'price' => $transaction->application_price,
        //     'quantity' => $transaction->quantity,
        //     'total' => $transaction->total,
        //     'details' => [
        //         'quantity' => $transaction->quantity,
        //         'price' => $transaction->application_price,
        //     ],
        //     'partner_id' => $transaction->partner_id,
        // ]);
    }




    /**
     * Handle the QuotaTransaction "updated" event.
     */
    public function updated(QuotaTransaction $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "deleted" event.
     */
    public function deleted(QuotaTransaction $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "restored" event.
     */
    public function restored(QuotaTransaction $quotaTransaction): void
    {
        //
    }

    /**
     * Handle the QuotaTransaction "force deleted" event.
     */
    public function forceDeleted(QuotaTransaction $quotaTransaction): void
    {
        //
    }
}

<?php

namespace App\Observers;

use App\Models\Quota;
use App\Models\Transaction;
use App\Models\EventHistory;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->origin === 'Quota Debt') {
            if ($transaction->wasChanged('status') && $transaction->status === 'paid') {

                $quotaTotal = Quota::whereIn('id', $transaction->quota_transactions)->sum('total');
                $totalApps = Quota::whereIn('id', $transaction->quota_transactions)->sum('quantity');

                EventHistory::create([
                    'event_type' => 'transaction',
                    'event_code' => 'quota_paid',
                    'event_summary' => 'Online Payment',
                    'eventable_id' => $transaction->id,
                    'eventable_type' => Transaction::class,
                    'action' => 'Quota Paid',
                    'payment_status' => $transaction->payment_status,
                    'price' => $transaction->amount,
                    'quantity' => 1,
                    'total' => $transaction->amount,
                    'details' => [
                        'total' => $quotaTotal,
                        'quantity' => $totalApps,
                    ],
                    'user_id' => null,
                    'partner_id' => $transaction->partner_id,
                ]);
            }
        }

        if ($transaction->origin === 'Quota Credit') {
            if ($transaction->wasChanged('status') && $transaction->status === 'paid') {

                EventHistory::create([
                    'event_type' => 'transaction',
                    'event_code' => 'quota_bought',
                    'event_summary' => 'Online Payment',
                    'eventable_id' => $transaction->id,
                    'eventable_type' => Transaction::class,
                    'action' => 'Quota Bought',
                    'payment_status' => $transaction->payment_status,
                    'price' => $transaction->amount,
                    'quantity' => 1,
                    'total' => $transaction->amount,
                    'details' => [
                        'total' => $transaction->amount,
                        'quantity' => $transaction->quota_quantity,
                    ],
                    'user_id' => null,
                    'partner_id' => $transaction->partner_id,
                ]);
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}

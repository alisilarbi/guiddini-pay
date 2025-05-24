<?php

namespace App\Actions\Quota;

use Illuminate\Support\Collection;

class MarkAsPaid
{
    /**
     * Mark the quota as paid.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Quota> $quotas
     * @return void
     */
    public function handle(Collection $quotas)
    {
        foreach ($quotas as $quota) {
            if ($quota->payment_status !== 'paid') {
                $quota->update(['payment_status' => 'paid']);

                $partner = $quota->partner;

                $partner->decrement('total_unpaid', $quota->quantity);
                $partner->increment('total_paid', $quota->quantity);
            }
        }
    }
}
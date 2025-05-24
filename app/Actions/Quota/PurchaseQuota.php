<?php

namespace App\Actions\Quota;

use App\Models\User;
use App\Models\Quota;

class PurchaseQuota
{
    public function handle(User $partner, int $quantity): Quota
    {
        $transaction = Quota::create([
            'partner_id' => $partner->id,
            'type' => 'purchase',
            'quantity' => $quantity,
            'is_paid' => true,
            'application_price' => $partner->application_price,
            'amount' => $partner->application_price * $quantity,
        ]);

        $partner->increment('remaining_allowance', $quantity);

        return $transaction;
    }
}
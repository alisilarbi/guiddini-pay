<?php

namespace App\Actions\Quota;

use App\Models\User;
use App\Models\QuotaTransaction;

class GrantQuota
{
    public function handle(User $partner, array $data): QuotaTransaction
    {
        $transaction = QuotaTransaction::create([
            'partner_id' => $partner->id,
            'type' => 'grant',
            'quantity' => $data['quantity'],
            'is_paid' => $data['is_paid'],
            'application_price' => $partner->application_price,
            'total' => $data['application_price'] * $data['quantity'],
        ]);

        $partner->increment('remaining_allowance', $data['quantity']);
        return $transaction;
    }
}
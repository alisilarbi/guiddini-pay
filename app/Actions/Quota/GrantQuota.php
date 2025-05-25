<?php

namespace App\Actions\Quota;

use App\Models\User;
use App\Models\Quota;

class GrantQuota
{
    public function handle(User $partner, array $data): Quota
    {

        $transaction = Quota::create([
            'partner_id' => $partner->id,
            'type' => 'admin_grant',
            'quantity' => $data['quantity'],
            'payment_status' => $data['is_paid'] ? 'paid' : 'unpaid',
            'status' => 'active',
            'application_price' => $partner->application_price,
            'total' => $partner->application_price * $data['quantity'],
            'remaining_quantity' => $data['quantity'],
        ]);

        $partner->increment('available_quota', $data['quantity']);
        $partner->increment('total_apps', $data['quantity']);
        if ($data['is_paid']) {
            $partner->increment('total_paid', $data['quantity']);
        } else {
            $partner->increment('total_unpaid', $data['quantity']);
        }

        return $transaction;
    }
}

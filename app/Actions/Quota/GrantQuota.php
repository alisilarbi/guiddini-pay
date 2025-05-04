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


        // $transaction = QuotaTransaction::create([
        //     'partner_id' => $partner->id,
        //     'type' => 'admin_grant',
        //     'quantity' => $data['quantity'],
        //     'payment_status' => $data['is_paid'] ? 'paid' : 'unpaid',
        //     'application_price' => $partner->application_price,
        //     'total' => $data['application_price'] * $data['quantity'],
        //     'remaining_quantity' => $data['quantity'],
        // ]);

        // if ($data['is_paid']) {
        //     $partner->increment('remaining_paid_applications', $data['quantity']);
        //     $partner->increment('total_paid_applications', $data['quantity']);
        // } else {
        //     $partner->increment('remaining_unpaid_applications', $data['quantity']);
        //     $partner->increment('total_unpaid_applications', $data['quantity']);
        // }

        // $partner->increment('remaining_applications', $data['quantity']);
        // $partner->increment('total_applications', $data['quantity']);

        // return $transaction;
    }
}

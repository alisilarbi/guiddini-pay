<?php

namespace App\Actions\Quota;

use App\Models\User;
use App\Models\Quota;
use App\Models\Transaction;

class PurchaseQuota
{
    public function handle(Transaction $transaction, User $partner): void
    {
        $partner->increment('total_paid', $transaction->quota_quantity);
        $partner->increment('available_quota', $transaction->quota_quantity);
    }


}
